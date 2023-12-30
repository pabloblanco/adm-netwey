<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Paysheet;
use App\User;
use DOMDocument;

class nomina extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:nomina';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lee los archivos de nomina subidos por ftp y carga la data en bd.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * NOTA: el metodo se ejecuta teniendo como base que los nombres de los archivos no se van 
     * a  repetir nunca entre usuarios, y que el nombre del archivo xml es igual al del pdf
     * Los arcivos a procesar deben estar en el directorio inbox, una vez procesados 
     * pasaran al directorio process del ftp
     * @return mixed
     */
    public function handle()
    {
        /*$all = Paysheet::whereNull('rel_type')->get();

        foreach ($all as $p){
            if(empty($p->rel_type)){
                if(strpos($p->name_file, 'A ') !== false || strpos($p->name_file, '_A') !== false)
                    $p->type = 'A';
                else
                    $p->type = 'N';
            }

            $p->rel_type = $p->serie.$p->folio.$p->rfc;

            $p->save();
        }
        exit();*/

        $dirs = Storage::disk('s3-nomina')->directories();

        if(in_array('inbox', $dirs)){
            $files = Storage::disk('s3-nomina')->files('inbox');

            $s3path = 'nomina/'.date('Y-m-d').'/';
            $s3pro = 'process/'.date('Y-m-d').'/';

            foreach ($files as $file) {
                //Solo se procesasn los xml del directorio.
                if(strtolower(substr($file, -3)) == 'xml' && (in_array(substr($file, 0, strlen($file) - 3).'pdf', $files) || in_array(substr($file, 0, strlen($file) - 3).'PDF', $files))){
                    //Guardando la extencion del pdf para distinguir entre mayusculas y minusculas
                    if(in_array(substr($file, 0, strlen($file) - 3).'pdf', $files))
                      $extP = 'pdf';

                    if(in_array(substr($file, 0, strlen($file) - 3).'PDF', $files))
                      $extP = 'PDF';

                    //Obteniendo xml desde s3
                    $fileContent = Storage::disk('s3-nomina')->get($file);

                    //Cargando XML en DOMDocument
                    $doc = new DOMDocument();
                    $doc->loadXML($fileContent);

                    //Opteniendo el nombre del arhcivo
                    $ini = strpos($file, '/') + 1;
                    $end = strlen($file) - (strpos($file, '/') + 5);
                    $name_file = substr($file, $ini, $end);

                    if($doc->getElementsByTagName('Comprobante')->length){
                        $ncert = $doc->getElementsByTagName('Comprobante')->item(0)->getAttribute('NoCertificado');
                        $serie = $doc->getElementsByTagName('Comprobante')->item(0)->getAttribute('Serie');
                        $folio = $doc->getElementsByTagName('Comprobante')->item(0)->getAttribute('Folio');
                        $date_doc = $doc->getElementsByTagName('Comprobante')->item(0)->getAttribute('Fecha');

                        if(!empty($date_doc))
                            $date_doc = date('Y-m-d H:i:s', strtotime($date_doc));

                        if($doc->getElementsByTagName('Receptor')->length){
                            $rfc = $doc->getElementsByTagName('Receptor')->item(0)->getAttribute('Rfc');
                        }

                        if($doc->getElementsByTagName('Percepcion')->length){
                            $type = $doc->getElementsByTagName('Percepcion')->item(0)->getAttribute('Concepto');

                            if(strtolower($type) == 'asimilado a salarios')
                                $type = 'A';
                            elseif(strtolower($type) == 'sueldo')
                                $type = 'N';
                            else
                                $type = false;
                        }

                        if($type && !empty($rfc)){
                            $payo = Paysheet::select('id')
                                              ->where([
                                                ['rfc', $rfc],
                                                ['name_file', $name_file],
                                                ['type', $type]
                                              ]);

                            if($payo->count())
                                $payo->update(['status' => 'T']);

                            //Subiendo el xml a s3
                            Storage::disk('s3')->put($s3path.$name_file.$payo->count().'.xml', $fileContent, 'private');

                            Storage::disk('s3-nomina')->put($s3pro.$name_file.'.xml', $fileContent, 'public');

                            //Subiendo el pdf a s3
                            $PDFContent = Storage::disk('s3-nomina')->get(substr($file, 0, strlen($file) - 3).$extP);

                            Storage::disk('s3')->put($s3path.$name_file.$payo->count().'.pdf', $PDFContent, 'private');

                            Storage::disk('s3-nomina')->put($s3pro.$name_file.'.pdf', $PDFContent, 'public');

                            Paysheet::insert([
                                'rfc' => $rfc,
                                'cert_number' => $ncert,
                                'serie' => $serie,
                                'folio' => $folio,
                                'name_file' => $name_file,
                                'url_download' => $s3path.$name_file.$payo->count().'.pdf',
                                'date_nom' => $date_doc,
                                'date_reg' => date('Y-m-d H:i:s'),
                                'type' => $type,
                                'rel_type' => $serie.$folio.$rfc,
                                'status' => 'N'
                            ]);

                            //Eliminando archivos de la carpeta inbox
                            Storage::disk('s3-nomina')->delete(substr($file, 0, strlen($file) - 3).$extP);
                            Storage::disk('s3-nomina')->delete($file);

                            if(Storage::disk('s3-nomina')->exists('inbox/'.$rfc.'.pdf') || Storage::disk('s3-nomina')->exists('inbox/'.$rfc.'.PDF')){
                                //Obteniendo extencion de pdf para diferencias mayusculas y minusculas
                                if(Storage::disk('s3-nomina')->exists('inbox/'.$rfc.'.pdf'))
                                  $extP = '.pdf';
                                
                                if(Storage::disk('s3-nomina')->exists('inbox/'.$rfc.'.PDF'))
                                  $extP = '.PDF';

                                $user = User::select('email', 'url_latter_contract')
                                              ->where('dni', $rfc)
                                              ->first();

                                if(!empty($user) && empty($user->url_latter_contract)){
                                    $contractPath = 'contracts/';

                                    $contract = Storage::disk('s3-nomina')->get('inbox/'.$rfc.$extP);

                                    Storage::disk('s3')->put($contractPath.$rfc.'.pdf',$contract,'private');

                                    $user->url_latter_contract = $rfc.'.pdf';
                                    $user->save();

                                    Storage::disk('s3-nomina')->put($s3pro.$rfc.'.pdf',$contract,'public');
                                }

                                Storage::disk('s3-nomina')->delete('inbox/'.$rfc.$extP);
                            }
                        }
                    }
                }
            }

            //Limpiando la carpeta inbox
            $filesnp = Storage::disk('s3-nomina')->files('inbox');
            $s3npro = 'not_process/'.date('Y-m-d').'/';

            foreach ($filesnp as $file){
                $ini = strpos($file, '/') + 1;
                $end = strlen($file) - (strpos($file, '/') + 1);
                $name_file = substr($file, $ini, $end);

                $content = Storage::disk('s3-nomina')->get($file);

                Storage::disk('s3-nomina')->put($s3npro.$name_file, $content, 'public');
                Storage::disk('s3-nomina')->delete($file);
            }
        }else{
            Storage::disk('s3-nomina')->makeDirectory('inbox');
        }
    }
}
