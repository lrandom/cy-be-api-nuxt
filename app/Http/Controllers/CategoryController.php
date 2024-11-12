<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    //
    function index()
    {
        return response()->json(Category::paginate(10));
    }

    function show($slug)
    {
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json(Category::where('slug', $slug)->first());
    }
}
