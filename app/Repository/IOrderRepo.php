<?php

namespace App\Repository;

use Illuminate\Http\Request;

interface IOrderRepo
{
    public function FetchUserOrders($id);
    public function PlaceOrder(Request $request);
    public function UploadBankSlip(Request $request);
    public function FetchOrders($id);
    public function UpdateOrder(Request $request);
    public function ChangeOrderStatus(Request $request);
    public function DeleteOrder($id);
}
