<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //
    function index()
    {
        //return response()->json(Product::all());
        //return with pagination
        return response()->json(Product::paginate(10));
    }
}
