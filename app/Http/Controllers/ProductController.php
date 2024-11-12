<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //
    function index(Request $request)
    {
        //return response()->json(Product::all());
        //return with pagination
        //get data from $request
        $name = $request->query('name') ?? '';
        $category_id = $request->query('category_id') ?? '';
        $maxPrice = $request->query('max_price') ?? '';
        $minPrice = $request->query('min_price') ?? '';
        $categorySlug = $request->query('category_slug') ?? '';
        $products = Product::query();

        if ($name) {
            $products->where('name', 'like', '%' . $name . '%');
        }

        if ($category_id) {
            $products->where('category_id', $category_id);
        }

        if ($maxPrice) {
            $products->where('price', '<=', $maxPrice);
        }

        if ($minPrice) {
            $products->where('price', '>=', $minPrice);
        }

        if ($categorySlug) {
            $products->whereHas('category', function ($query) use ($categorySlug) {
                $query->where('slug', $categorySlug);
            });
        }


        $products = $products->with('category')->paginate(10);
        return response()->json($products);
    }

    function show($id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }


    function checkStock($id)
    {
        $product = Product::where('stock', 0)->where('id', $id)->get();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        if ($product->count() > 0) {
            return response()->json(['message' => 'Out of stock'], 404);
        } else {
            return response()->json(['message' => 'In stock']);
        }
    }
}
