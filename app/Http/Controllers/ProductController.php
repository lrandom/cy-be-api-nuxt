<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Get all products",
     *     description="Get a paginated list of products with optional filters",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter products by name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter products by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price filter",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price filter",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="category_slug",
     *         in="query",
     *         description="Filter products by category slug",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token required"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/products/{id}",
     *     summary="Get a specific product",
     *     description="Get a single product by its ID",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token required"
     *     )
     * )
     */
    function show($id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{id}/check-stock",
     *     summary="Check product stock",
     *     description="Check if a product is in stock",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product is in stock",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="In stock")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found or out of stock",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token required"
     *     )
     * )
     */
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
