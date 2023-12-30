<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\WebManagementFee;
use Yajra\DataTables\Facades\DataTables;

class WebManagementFeeController extends Controller
{
    /**
     * Retorna la vista para el usuario.
     */
    public function index() {
        $html = view('pages.ajax.web_management.fees')->render();
        return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
    }

    /**
     * Obtiene las tarifas registradas y la regresa para su visualización con DataTables
     */
    public function getDataTable(Request $request) {
        if (!User::hasPermission(session('user.email'), 'WMT-RRT'))
            return response()->json(['message' => 'Usted no posee permisos para realizar esta operación.'], 403);

        if (!$request->isMethod('get') && !$request->ajax())
            return response()->json([ 'message' => 'El tipo de petición recibida no está permitida.' ]);

        $filters = $request->input('filter');
        if (!empty($request->search))
            $filters['search'] = $request->search['value'];

        $fees = WebManagementFee::getRowsDatatable($filters);

        return @DataTables::eloquent($fees)->make(true);
    }

    /**
     * Almacena o actualiza la información de una tarifa.
     */
    public function store(Request $request) {
        if (!$request->isMethod('post') && !$request->ajax())
            return response()->json([ 'message' => 'El tipo de petición recibida no está permitida.' ]);
        
        if (!User::hasPermission(session('user.email'), 'WMT-CRT'))
            return response()->json(['message' => 'Usted no posee permisos para realizar esta operación.'], 403);

        // Se valida si se adjunto un documento para cargar en el bucket S3
        $urlS3 = null;
        if ($request->hasFile('attach_file') && $request->parent_id > 0)
            $urlS3 = $this->uploadDocumentS3($request);

        $fee = WebManagementFee::getConnect('W');
        $fee->parent_id         = !empty($request->parent_id) ? $request->parent_id : null;
        $fee->descripcion_web   = $request->description;
        $fee->url_file          = $urlS3;
        $fee->position          = $request->position;
        $fee->etiqueta          = isset($request->label) ? 'Y' : 'N';
        $fee->user_netwey       = session('user')->email;
        $fee->save();

        return $fee;
    }

    /**
     * Obtiene el listado de tarifas en un arreglo con sus opciones anidadas.
     */
    public function getFeesSelect(Request $request) {
        if (!$request->isMethod('get') && !$request->ajax())
            return response()->json([ 'message' => 'El tipo de petición recibida no está permitida.' ]);

        $data = [[ 'value' => 0, 'name' => 'Registro nuevo', 'l2' => [] ]];
        $fees = WebManagementFee::getFeeWithChilds();

        if (!empty($fees))
            foreach ($fees as $fee) {
                $data[$fee->id] = [
                    'value' => $fee->id,
                    'name'  => $fee->descripcion_web,
                    'l2'    => []
                ];

                if (!empty($fee->childs)) {
                    foreach($fee->childs as $child) {
                        array_push($data[$fee->id]['l2'], [
                            'value' => "$child->id",
                            'name'  => $child->descripcion_web
                        ]);
                    }
                }
            }

        return response()->json(array_values($data));
    }

    /**
     * Muestra la información de un recurso especifico.
     */
    public function show(Request $request, $id = null) {
        if (!$request->isMethod('get') && !$request->ajax())
            return response()->json([ 'message' => 'El tipo de petición recibida no está permitida.' ]);
        
        $fee = WebManagementFee::find($id);
        return response()->json([
            'id'                => $fee->id,
            'parent_id'         => !is_null($fee->parent_id) ? $fee->parent_id : 0,
            'descripcion_web'   => $fee->descripcion_web,
            'url_file'          => $fee->url_file,
            'position'          => $fee->position,
            'label'             => $fee->etiqueta === 'Y' ?? false
        ]);
    }

    /**
     * Actualiza la información de un recurso especifico
     */
    public function update(Request $request, $id = null) {
        if (!$request->isMethod('patch') && !$request->ajax())
            return response()->json([ 'message' => 'El tipo de petición recibida no está permitida.' ]);
    
        if (!User::hasPermission(session('user.email'), 'WMT-URT'))
            return response()->json(['message' => 'Usted no posee permisos para realizar esta operación.'], 403);
            
        $statusCode = 200;
        $fee = WebManagementFee::getConnect('W')::with(['childs'])
            ->find($id);

        if (empty($fee))
            $statusCode = 404;

        // Se valida que la tarifa no tenga zonas asociadas
        if (!empty(count($fee->childs)) && $request->hasFile('attach_file'))
            return response()->json([ 'message' => 'La tarifa selecciona tiene zonas asociadas por lo que no está permitido adjuntar documentos.' ], 409);

        // Se carga el nuevo archivo adjunto
        $urlS3 = $request->url_file;
        if ($request->hasFile('attach_file'))
            $urlS3 = $this->uploadDocumentS3($request);

        $fee->parent_id         = !empty($request->parent_id) ? $request->parent_id : $fee->parent_id;
        $fee->descripcion_web   = $request->description;
        $fee->url_file          = $urlS3;
        $fee->position          = $request->position;
        $fee->etiqueta          = isset($request->label) ? 'Y' : 'N';
        $fee->save();
        
        return response()->json($fee, $statusCode);
    }

    /**
     * Elimina de forma logica un recurso especifico
     */
    public function destroy(Request $request, $id = null) {
        if (!$request->isMethod('delete') && !$request->ajax())
            return response()->json([ 'message' => 'El tipo de petición recibida no está permitida.' ]);
    
        if (!User::hasPermission(session('user.email'), 'WMT-DRT'))
            return response()->json(['message' => 'Usted no posee permisos para realizar esta operación.'], 403);
        
        $statusCode = 204;
        $fee = WebManagementFee::getConnect('R')::with(['childs' => function($query) {
            $query->where('status', 'A');
        }])
            ->find($id);

        if (empty($fee) || !empty(count($fee->childs))) {
            return response()->json(['message' => 'La tarifa no puede ser eliminada ya que no existe o está tiene otras tarifas asignadas.'], 409);
        }

        $fee->status = 'T';
        $fee->save();

        return response()->json(null, $statusCode);
    }

    /**
     * Cargar un documento al bucket s3 de la gestión web
     */
    private function uploadDocumentS3(Request $request) {
        $filename = 'fee-' . uniqid('', true);
        $extensionFile = $request->file('attach_file')->guessClientExtension();
        $s3Filepath = "web-management/fees/test/$filename.$extensionFile";
        
        Storage::disk('s3')->put($s3Filepath, file_get_contents($request->file('attach_file')->path()), 'public');
        return Storage::disk('s3')->url($s3Filepath);
    }
}
