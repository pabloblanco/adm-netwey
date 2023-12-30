<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SellerInventory;

use Illuminate\Support\Facades\Storage;
use App\Helpers\CommonHelpers;
use \Curl;

use DateTime;

class TestController extends Controller {
	public function test() {
		//value="javier.nava@netwey.com.mx"
		$email = 'javier.nava@netwey.com.mx';
		$checkIds = array('5584615244', '5584615243', '5584615242', '5584615241');
        $totalSended = DB::table('islim_inv_assignments AS ia')->distinct()
            ->join('islim_inv_arti_details AS i', 'i.id', '=', 'ia.inv_arti_details_id')
                ->whereIn('ia.status', ['A','I'])
                ->whereIn('i.status', ['A'])
                ->whereIn('i.msisdn', $checkIds)
                ->where('ia.users_email', $email)->count();
        $totalAssigned = SellerInventory::getTotalInventory($email);
        $totalAllowed = SellerInventory::getTotalPermision($email);
        $canrecievemore = SellerInventory::canRecieveMoreInventory($email);
        $canContinue0 = (($totalAssigned + $totalSended) <= $totalAllowed);
        $canContinue = SellerInventory::canRecieveMoreInventory($email) &&
                    (($totalAssigned + $totalSended) <= $totalAllowed);
        return '<p>email: '.$email.'</p>'.
        	'<p>totalSended: '.$totalSended.'</p>'.
        	'<p>totalAssigned: '.$totalAssigned.'</p>'.
        	'<p>totalAllowed: '.$totalAllowed.'</p>'.
        	'<p>canrecievemore: '.$canrecievemore.'</p>'.
        	'<p>totalAssigned + totalSended: '.($totalAssigned + $totalSended).'</p>'.
        	'<p>(totalAssigned + totalSended) <= totalAllowed: '.$canContinue0.'</p>'.
        	'<p>canRecieveMoreInventory($user->email) && ((totalAssigned + totalSended) <= totalAllowed): '.$canContinue.'</p>';
    }
    public function testbilling() {
        $html="";
        $mode=env('APP_ENV')=='production'?'0':'1';
        $folder=env('APP_ENV')=='production'?'masive/':'masive/test/';

        $fecha = new \DateTime();
        $fechagen=$fecha->format('Y-m-d H:i:s');
        $fecha=$fecha->format('Y-m-d\TH:i:s');

        $serie = "TEST";
        $folio = "99999";


        $total=1800;
        $sub_total=round($total/(1+(env('IVA')/100)),2);
        $tasa=(env('IVA')/100);
        $price_iva=round(($sub_total*$tasa),2);
        $tasat=number_format($tasa,6);

        if($sub_total+$price_iva != $total){
          $price_iva=$total-$sub_total;
        }

        $url_ep = "http://dev2.servidormk.com/___DATA/_scripts/external_cfdi_40.php";


        $data = array(
            'data' => '
            {
                "Comprobante":
                {
                    "Version":4.0,
                    "Serie":'.$serie.',
                    "Folio":'.$folio.',
                    "Fecha":"'.$fecha.'",
                    "Sello":"",
                    "FormaPago":"'.env('BILLING_PAYMETHOD').'",
                    "NoCertificado":"'.env('BILLING_CERTIFIED').'",
                    "Certificado":"",
                    "CondicionesDePago":"",
                    "SubTotal":'.$sub_total.',
                    "Descuento":0,
                    "Moneda":"MXN",
                    "TipoCambio":1,
                    "Total":'.$total.',
                    "TipoDeComprobante":"I",
                    "MetodoPago":"PUE",
                    "LugarExpedicion":"'.env('BILLING_LUGAR').'",
                    "Emisor":
                    {
                        "Rfc":"'.env('BILLING_EMISOR_RFC').'",
                        "Nombre":"'.env('BILLING_EMISOR_NOMBRE').'",
                        "RegimenFiscal":"'.env('BILLING_EMISOR_REGIMEN').'"
                    },
                    "Receptor":
                    {
                        "Rfc":"'.env('BILLING_RECEPTOR_RFC').'",
                        "Nombre":"'.env('BILLING_RECEPTOR_NOMBRE').'",
                        "UsoCFDI":"'.env('BILLING_USOCFDI').'"
                    },
                    "Conceptos":
                    {
                        "Concepto":
                        {
                            "ClaveProdServ":"'.env('BILLING_CONCEPT_PRODUCTKEY').'",
                            "NoIdentificacion":"'.env('BILLING_CONCEPT_NROID').'",
                            "Cantidad":"1",
                            "ClaveUnidad":"'.env('BILLING_CONCEPT_UNITKEY').'",
                            "Unidad":"'.env('BILLING_CONCEPT_UNIT').'",
                            "Descripcion":"'.env('BILLING_CONCEPT_DESCRIPTION').'",
                            "ValorUnitario":"'.$sub_total.'",
                            "Importe":"'.$sub_total.'",
                            "Impuestos":
                            {
                                "Traslados":
                                {
                                    "Traslado":
                                    [
                                        {
                                            "Base":"'.$sub_total.'",
                                            "Impuesto":"002",
                                            "TipoFactor":"Tasa",
                                            "TasaOCuota":"'.$tasat.'",
                                            "Importe":"'.$price_iva.'"
                                        }
                                    ]
                                }
                            }
                        }
                    },
                    "Impuestos":
                    {
                        "TotalImpuestosTrasladados":"'.$price_iva.'",
                        "Traslados":
                        {
                            "Traslado":
                            [
                                {
                                    "Impuesto":"002",
                                    "TipoFactor":"Tasa",
                                    "TasaOCuota":"'.$tasat.'",
                                    "Importe":"'.$price_iva.'"
                                }
                            ]
                        }
                    }
                }
            }',
            'mode' => $mode,
            'excludeXML' => 'false',
            'file' => 'true',
            'debug' => 'false'
        );
        $header = array(
            " : "
        );

        return json_encode($data);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url_ep,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $res=json_decode($response,true);

        if($res['error']==false && !empty($res['idXML']) && !empty($res['pdf']) && !empty($res['uuid'])){
            if($res['uuid'] != null){

                $fileName = $res['uuid'].'.pdf';
                $file_dir_pdf=$folder.$fileName;
                $content = base64_decode($res['pdf']);
                Storage::disk('s3-masive-billing')->put($file_dir_pdf, $content,'public');

                $fileName = $res['uuid'].'.xml';
                $file_dir_xml=$folder.$fileName;
                $content = base64_decode($res['xml']);
                Storage::disk('s3-masive-billing')->put($file_dir_xml, $content,'public');

                $file_pdf = Storage::disk('s3-masive-billing')->url($file_dir_pdf);
                $file_xml = Storage::disk('s3-masive-billing')->url($file_dir_xml);

                $html.= "<h3>Factura Generada</h3>";
                $html.= "<p>Factura #: ".$res['uuid']."</p>";
                $html.= "<p>idXML: ".$res['idXML']."</p>";
                $html.= "<p>url_download_pdf: ".$res['uuid']."</p>";
                $html.= "<p>url_download_xml: ".$res['idXML']."</p>";

            }
            else{
              $html.= "<p> Error Generando Factura. </p>";
            }
        }
        else{
            //$html.= "<p> Error Generando Factura.. </p>";
            dd($res);
            exit;
        }

        // $html.= "<p>".$mode."</p>";
        // $html.= "<p>".$folder."</p>";

        return $html;
    }
}