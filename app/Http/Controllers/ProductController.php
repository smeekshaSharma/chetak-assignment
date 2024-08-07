<?php

namespace App\Http\Controllers;
use App\Models\Products;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //Fetch all records
    public function index()
    {
        $products = Products::all();
        return view('product', ['products' => $products]);
    }
    
    // Add new one
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric',
        ]);

        $product = Products::create($request->all());
        return response()->json($product, 201);
    }

    //Display  a particular record
    public function show($id)
    {
        $product = Products::find($id);
        return response()->json($product);
    }

    // Update a specific record
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric',
        ]);

        $product = Products::find($id);
        $product->update($request->all());
        return response()->json($product);
    }

    //Delete a particular record
    public function destroy($id)
    {
        Products::destroy($id);
        return response()->json(null, 204);
    }
}
