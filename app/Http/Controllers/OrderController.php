<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\IOrderRepo;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    private IOrderRepo $orderRepo;

    public function __construct(IOrderRepo $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    //fetching orders & bookings of a user
    public function getUserOrders($id)
    {
        try {
            $response = $this->orderRepo->FetchUserOrders($id);
            if ($response) {
                return response()->json([
                    'orders' => $response[0], 'bookings' => $response[1],
                    'status' => 200
                ], 200);
            } else {
                return response()->json([
                    'message' => $response,
                    'status' => 500
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    //Placing order 
    public function PlaceOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|exists:schools,id',
                'user_id' => 'required|integer',
                'products' => 'required|json',
                'total_price' => 'required|string',
                'order_type' => 'required|string',
                'bank_slip' => 'nullable|string',
                'payment_method' => 'required|string',
                'order_status' => 'required|string',
                'dispatch_datetime' => "nullable",
                'dispatch_address' => "nullable|string"
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $response = $this->orderRepo->PlaceOrder($request);
                if ($response) {
                    return response()->json([
                        'message' => 'Order placed',
                        'status' => 201
                    ], 201);
                } else {
                    return response()->json([
                        'message' => $response,
                        'status' => 500
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    //Upload bank slips
    public function  uploadBankSlip(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'nullable|exists:sales_history,id',
                'bankSlip' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $response = $this->orderRepo->UploadBankSlip($request);
                if ($response) {
                    return response()->json([
                        'message' => 'Slip uploaded',
                        'path' => $response,
                        'status' => 201
                    ], 201);
                } else {
                    return response()->json([
                        'message' => $response,
                        'status' => 500
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    //Fetching orders for admin side
    public function fetchAllOrders($id)
    {
        try {
            $response = $this->orderRepo->FetchOrders($id);
            if ($response) {
                return response()->json([
                    'orders' => $response,
                    'status' => 200
                ], 200);
            } else {
                return response()->json([
                    'message' => $response,
                    'status' => 500
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    //Updating orders from admin side
    public function updateOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:sales_history,id',
                'order_status' => 'required|string',
                'dispatch_datetime' => "string",
                'dispatch_address' => "string"
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {

                $response = $this->orderRepo->UpdateOrder($request);
                if ($response) {
                    return response()->json(['message' => 'Order updated'], 200);
                } else {
                    return response()->json(['error' => 'Order not found.'], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 500);
        }
    }
    public function changeOrderStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:sales_history,id',
                'status' => 'required|string',

            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {

                $response = $this->orderRepo->ChangeOrderStatus($request);
                if ($response) {
                    return response()->json(['message' => 'Order updated'], 200);
                } else {
                    return response()->json(['error' => 'Order not found.'], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 500);
        }
    }
    public function deleteOrder($id)
    {
        try {
            $response = $this->orderRepo->DeleteOrder($id);
            if ($response) {
                return response()->json([
                    "message" => "deleted"
                ], 200);
            } else {
                return response()->json([
                    "message" => "not found"
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
}
