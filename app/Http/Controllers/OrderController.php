<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //

    function index()
    {
        //return response()->json(Product::all());
        //return with pagination
        $loggedUser = auth()->user();

        return response()->json(Order::where('user_id', $loggedUser->id)->with('orderItems')->orderBy('id', 'desc')->get());
    }

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
}
