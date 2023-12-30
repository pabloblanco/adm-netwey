<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProductsCategory;

class ProductsCategoryController extends Controller {
	public function index (){
		$categories = ProductsCategory::all();
        return response()->json($categories);
	}

	public function show ($id){
		$categories = ProductsProvider::find($id);
        return response()->json($categories);
	}

	public function store (Request $request){
		$categories = ProductsCategory::create($request->input());
        $categories->date_reg = date ('Y-m-d H:i:s', time());
        $categories->save();
		return response()->json($categories);
	}

	public function update (Request $request, $id){
		$categories = ProductsCategory::find($id);
        $categories->title = $request->title;
        $categories->description = $request->description;
        $categories->status = $request->status;
        $categories->save();
        return response()->json($categories);
	}

	public function destroy ($id){
		$categories = ProductsCategory::find($id)->update(['status'=>'T']);
		return response()->json($categories);
	}

	public function view (){
		$categories = ProductsCategory::where('status','A')->get();
		$html = view('pages.ajax.productcategories', compact('categories'))->render();
    	return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}
}
