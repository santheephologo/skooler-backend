<?php

namespace App\Repository;

use Illuminate\Http\Request;

interface IAuthRepo
{
    public function Signup(Request $request);
    public function Login(Request $request);
    public function ValidationCheck($mobile_no, $email, $studentID,$schoolID);
    public function Logout(Request $request);
    public function ResetPassword(Request $request);
    public function GetUser();
    public function DeleteUser($id);
}
