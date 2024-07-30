<?php

namespace App\Repository;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Models\Booking;
use App\Models\CartItem;
use App\Models\Complaint;
use App\Models\Notification;

class OrderRepo implements IOrderRepo
{
    public function FetchUserOrders($id)
    {
        $user_id = (int) $id;
        $orders = Order::where('user_id', $user_id)->get();
        foreach ($orders as &$order) {
            $complaint = Complaint::where('order_id', $order->id)->first();
            if ($complaint != null) {
                $order->complaint = true;
            } else {
                $order->complaint = false;
            }
        }
        $bookings = Booking::where('user_id', $user_id)->get();
        return [$orders, $bookings];
    }
    public function PlaceOrder(Request $request)
    {
        $order = Order::create([
            'school_id' => $request->input('school_id'),
            'user_id' => $request->input('user_id'),
            'products' => $request->input('products'),
            'total_price' => $request->input('total_price'),
            'order_type' => $request->input('order_type'),
            'bank_slip' => $request->input('bank_slip'),
            'payment_method' => $request->input('payment_method'),
            'order_status' => $request->input('order_status'),
            'dispatch_datetime' => $request->input('dispatch_datetime'),
            'dispatch_address' => $request->input('dispatch_address'),
            'reviewed' => false
        ]);
        if ($order) {
            CartItem::where('user_id', $request->user_id)->delete();
            $notification = Notification::create([
                'school_id' => $request->school_id,
                'name' => 'Your order has been placed',
                'info' => 'Thank you for your purchase',
                'type' => 'order',
                'is_read' => false,
                'user_id' => $request->user_id,
            ]);
        }

        return $order ? true : false;
    }
    public function UploadBankSlip(Request $request)
    {
        $path = $request->file('bankSlip')->store(
            'public/bankslips',
            's3'
        );
        Storage::disk('s3')->setVisibility($path, 'public');

        $order = Order::find($request->input('id'));
        if ($order && $order->bank_slip) {
            Storage::disk('s3')->delete($order->bank_slip);
            $order->bank_slip = $path;
            $order->save();
            return $path;
        } else {
            return $path;
        }
    }
    public function FetchOrders($id)
    {
        $orders = Order::where('school_id', $id)
                        ->reorder('created_at', 'desc')
                        ->get();
        return $orders;
    }
    public function UpdateOrder(Request $request)
    {
        $order = Order::find($request->id);
        if ($order) {
            $notification = Notification::create([
                'school_id' => $order->school_id,
                'name' => "Order ID #$order->id ",
                'info' => 'order status has been updated',
                'type' => 'order',
                'is_read' => false,
                'user_id' => $order->user_id,
            ]);

            $order->order_status = $request->order_status;
            if ($request->has('dispatch_datetime')) {
                $order->dispatch_datetime = $request->dispatch_datetime;
            }

            if ($request->has('dispatch_address')) {
                $order->dispatch_address = $request->dispatch_address;
            }

            $order->save();
        }
        return $order ? true : false;
    }
    public function ChangeOrderStatus(Request $request)
    {
        $order = Order::find($request->id);
        if ($order) {
            $order->order_status = $request->status;
            $order->save();
            return true;
        }
        return false;
    }
    public function DeleteOrder($id)
    {
        $order = Order::where('id', $id)->first();
        if ($order) {
            $order->delete();
        }
        return $order ? true : false;
    }
}
