<?php

namespace App\Repository;

use App\Models\User;
use App\Models\Student;
use App\Models\Review;
use App\Models\CardInfo;
use App\Models\Complaint;
use App\Models\CartItem;
use App\Models\Booking;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;

class AuthRepo implements IAuthRepo
{
    //user signup
    public function Signup(Request $request)
    {
        $exists = User::where('mobile_no', $request->input('mobile_no'))
                        ->where('school_id', $request->input('school_id'))
                        ->exists();
        if($exists){
            return false;
        }  
        $user = [
            'school_id' => $request->input('school_id'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'student_id' => (int) ($request->input('student_id')),
            'mobile_no' => $request->input('mobile_no'),
            'email' => $request->input('email'),
            'home_address' => null,
            'password' => Hash::make($request->input('password')),
            'profile_pic' => null,
            'is_active' => $request->input('is_active'),
        ];
        return User::create($user);
    }
    //User login
    public function Login(Request $request)
    {
        if (Auth::attempt($request->only('school_id','mobile_no', 'password'))) {
            $user = Auth::user();
            $token = $request->user()->createToken('token')->plainTextToken;

            return [$user, $token];
        } else {
            return false;
        }
    }
    //Fetching user using jwt token
    public function GetUser()
    {
        if (Auth::check()) {

            return Auth::user();
        } else {
            return false;
        }
    }
    //Validating email, mobile no, student id during signup
    public function ValidationCheck($mobile_no, $emailID, $studentID, $schoolID)
    {
        $noCheck = User::where('mobile_no', $mobile_no)
                        ->where('school_id', $schoolID)
                        ->exists();
        $emailCheck = User::where('email', $emailID)
                            ->where('school_id', $schoolID)
                            ->exists();
        $idCheck = Student::where('id', $studentID)
                            ->where('school_id', $schoolID)
                            ->exists();
        $phone = false;
        $email = false;
        $id = false;
        if (!$noCheck) $phone = true;
        if (!$emailCheck) $email = true;
        if (!$idCheck) $id = true;
        return ['phone' => $phone, 'email' => $email, 'id' => $id];
    }
    //user logout
    public function Logout(Request $request)
    {
        $response = $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();
        if ($response) {
            Cookie::forget('jwt');
        }
        $response ? true : false;
    }
    //user pwd reset
    public function ResetPassword(Request $request)
    {
        $user = Auth::user();
        // Verify the current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return false;
        } else {
            $user = User::find($request->input('id'));
            $user->password = Hash::make($request->input('new_password'));
            $user->save();
            return true;
        }
    }

    //delete user
    public function DeleteUser($id)
    {

        $user = User::find($id);
        if ($user) {
            $path = $user->profile_pic;
            if ($path) {
                Storage::disk('s3')->delete($path);
            }
            Review::where('user_id', $user->id)->delete();

            // Delete related cart items
            CartItem::where('user_id', $user->id)->delete();

            // Delete related complaints
            Complaint::where('user_id', $user->id)->delete();

            // Delete related card infos
            CardInfo::where('user_id', $user->id)->delete();

            // Delete related bookings
            Booking::where('user_id', $user->id)->delete();

            // Delete related orders
            Order::where('user_id', $user->id)->delete();
            $user->delete();
            return true;
        } else {
            return false;
        }
    }
}
