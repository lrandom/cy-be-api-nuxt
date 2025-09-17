<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     summary="Get user's orders",
     *     description="Get all orders for the authenticated user",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="sub_total", type="number", format="float"),
     *                 @OA\Property(property="tax", type="number", format="float"),
     *                 @OA\Property(property="total", type="number", format="float"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="status", type="integer"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="orderItems", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token required"
     *     )
     * )
     */
    function index()
    {
        //return response()->json(Product::all());
        //return with pagination
        $loggedUser = auth()->user();

        return response()->json(Order::where('user_id', $loggedUser->id)->with('orderItems')->orderBy('id', 'desc')->get());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/order",
     *     summary="Create a new order",
     *     description="Place a new order with cart items",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_item","address","phone"},
     *             @OA\Property(
     *                 property="cart_item",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Product Name"),
     *                     @OA\Property(property="price", type="number", format="float", example=29.99),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             ),
     *             @OA\Property(property="address", type="string", example="123 Main St, City, State 12345"),
     *             @OA\Property(property="phone", type="string", example="+1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order placed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order placed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to place order",
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
    function store()
    {
        $cartItem = request('cart_item');
        $subtotal = 0;
        foreach ($cartItem as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        //start transaction
        DB::beginTransaction();
        try {
            $order = new Order();
            $order->user_id = auth()->user()->id;
            $order->sub_total = $subtotal;
            $order->tax = $subtotal * 0.1;
            $order->address = request('address');
            $order->phone = request('phone');
//            $order->note = request('note');
            $order->status = 1;
            $order->total = $subtotal + $order->tax;
            $order->save();

            //save to order_items
            foreach ($cartItem as $item) {
                $orderItem = new OrderProduct();
                $orderItem->order_id = $order->id;
                $orderItem->name = $item['name'];
                $orderItem->product_id = $item['product_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->price = $item['price'];
                $orderItem->total = $item['price'] * $item['quantity'];
                $orderItem->save();

                //update product stock
                $product = Product::find($item['product_id']);
                if ($product->stock >= $item['quantity']) {
                    $product->stock = $product->stock - $item['quantity'];
                    $product->save();
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to place order, Out Of Stock for ' . $item['name']
                    ], 400);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            //dd($e);
            return response()->json([
                'message' => 'Failed to place order'
            ], 400);
        }

        return response()->json([
            'message' => 'Order placed successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}",
     *     summary="Get a specific order",
     *     description="Get a single order by ID for the authenticated user",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="sub_total", type="number", format="float"),
     *             @OA\Property(property="tax", type="number", format="float"),
     *             @OA\Property(property="total", type="number", format="float"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="orderItems",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="product_id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="price", type="number", format="float"),
     *                     @OA\Property(property="total", type="number", format="float")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token required"
     *     )
     * )
     */
    function show($id)
    {
        $loggedUser = auth()->user();
        $order = Order::where('id', $id)->where('user_id',$loggedUser->id)->with('orderItems')->first();
        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }
        return response()->json($order);
    }
}
