<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    //this method will show product page
    public function index(){
        $products = Product::orderBy('created_at','desc')->paginate(5);

        return view("products.list",[
            'products'=>$products]);
        
    }
    //this method will create product 
    public function create(){
        return view("products.create");
    }
    //this method will store product in db
    public function store(Request $request){
        $rules = [
            "name"=> "required|min:5",
            "sku"=> "required|min:3",
            "price"=> "required|numeric",
        ];

        if($request->image != ""){
            $rules["image"] = "image";
        }
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return redirect()->route("products.create")->withInput()->withErrors($validator);
        }
        //here we will insert product in db
        $product = new Product();
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if($request->image != ""){
        //here we will store image
        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        $imageName = time().".".$ext;  //unique image name

        //save image to products directory
        $image->move(public_path("uploads/products"), $imageName);

        //save image name in database
        $product->image = $imageName;
        $product->save();
        }

        return redirect()->route("products.index")->with("success","Product Added Successfully !!!");
    }
    //this method will edit product in db
    public function edit($id){
        $product = Product::findOrFail($id);
        return view("products.edit",[
            "product"=> $product
        ]);

    }
    //this method will update product in db
    public function update($id, Request $request){
        $product = Product::findOrFail($id);
        $rules = [
            "name"=> "required|min:5",
            "sku"=> "required|min:3",
            "price"=> "required|numeric",
        ];

        if($request->image != ""){
            $rules["image"] = "image";
        }
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return redirect()->route("products.edit",$product->id)->withInput()->withErrors($validator);
        }
        //here we will update product 
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if($request->image != ""){

        //delete old image
        File::delete(public_path("uploads/proudcts/".$product->image));

        //here we will store image
        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        $imageName = time().".".$ext;  //unique image name

        //save image to products directory
        $image->move(public_path("uploads/products"), $imageName);

        //save image name in database
        $product->image = $imageName;
        $product->save();
        }

        return redirect()->route("products.index")->with("success","Product Updated  Successfully !!!");

    }
    //this method will delete product in db
    public function destroy($id){
    $product = Product::findOrFail($id);

    //delete image first from directory
    
    File::delete(public_path("uploads/proudcts/".$product->image));

    //delete product from database
    $product->delete();
    return redirect()->route("products.index")->with("success","Product Deleted  Successfully !!!");

    }
}
