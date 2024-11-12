<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //

    function index()
    {
        //return response()->json(Product::all());
        //return with pagination
        return response()->json(Order::paginate(10));
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
            $order->product_id = request('product_id');
            $order->quantity = request('quantity');
            $order->subtotal = $subtotal;
            $order->tax = $subtotal * 0.1;
            $order->address = request('address');
            $order->phone = request('phone');
            $order->note = request('note');
            $order->status = 'pending';
            $order->total = $subtotal + $order->tax;
            $order->save();

            //save to order_items
            foreach ($cartItem as $item) {
                $orderItem = new OrderProduct();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item['product_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->price = $item['price'];
                $orderItem->total = $item['price'] * $item['quantity'];
                $orderItem->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to place order'
            ], 500);
        }

        return response()->json([
            'message' => 'Order placed successfully'
        ]);
    }
}
