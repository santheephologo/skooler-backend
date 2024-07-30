<?php

namespace App\Repository;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Hash;

class AdminRepo implements IAdminRepo
{
    public function FetchStats($id)
    {
        $currentDate = now();
        $adminsCount = Admin::where('school_id', $id)->count();
        $productCount = Product::where('school_id', $id)->count();
        $usersCount = User::where('school_id', $id)->count();
        $ordersCount = Order::where('school_id', $id)->count();
        $totalSum = Order::where('school_id', $id)->sum('total_price');
        $upcomingEvents = Event::where('school_id', $id)
            ->where('event_datetime', '>', $currentDate)
            ->orderBy('event_datetime', 'asc')
            ->get();
        return [
            $adminsCount,
            $productCount,
            $usersCount,
            $ordersCount,
            $upcomingEvents,
            $totalSum,

        ];
    }
    public function GetAllAdmins($id)
    {
        $admins = Admin::where('school_id', $id)
        ->orderBy('created_at', 'asc')
        ->skip(1) // This is the same as offset in Eloquent ORM
        ->take(PHP_INT_MAX) // This effectively means "no limit"
        ->get();
    return $admins;
    }
    public function AddAdmin(Request $request)
    {

        $exists = Admin::where('mobile_no', $request->input('mobile_no'))
                    ->where('school_id', $request->input('school_id'))
                    ->exists();
        if($exists){
            return false;
        }            
        $admin = Admin::create([
            'school_id' => $request->input('school_id'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'mobile_no' => $request->input('mobile_no'),
            'address' => null,
            'roles' => $request->input('roles'),
            'profile_pic' => null,
            'password' => Hash::make($request->input('password')),
            'is_active' => $request->input('is_active')

        ]);
        return $admin ? true : false;
    }
    public function AdminLogin(Request $request)
    {
        if (Auth::guard('admin')->attempt($request->only('school_id','mobile_no', 'password'))) {
            $admin = Auth::guard('admin')->user();
            //Auth::login($admin);
            $token = $admin->createToken('token')->plainTextToken;
            //$cookie = cookie('jwt', $token, 60 * 24);
            return [$admin, $token];
        } else {
            return false;
        }
    }
    public function GetAdmin()
    {
        return Auth::user();
    }
    public function AdminLogout(Request $request)
    {
        $response = $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();

        $cookie = Cookie::forget('jwt');
        $response  ? true : false;
    }
    public function ResetPassword(Request $request)
    {
        $user = Auth::user();
        // Verify the current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return false;
        } else {
            $user = Admin::find($request->input('id'));
            $user->password = Hash::make($request->input('new_password'));
            $user->save();
            return true;
        }
    }
    public function ChangeAdminStatus(Request $request)
    {
        $id = (int) ($request->input('id'));
        $isActive = $request->input('isActive');

        $admin = Admin::find($id);

        if ($admin) {
            if ($isActive) {
                $admin->is_active = true;
                $admin->save();
            } else {
                $admin->is_active = false;
                $admin->save();
            }
            //$user->is_active = !$user->is_active;
            return Admin::all();
        }
    }
    public function UpdateRoles(Request $request)
    {
        $admin = Admin::find((int)($request->input('id')));
        if ($admin) {
            // Update the string attribute
            $admin->roles = $request->input('roles');
            $admin->save();
        }
        return $admin ? true : false;
    }
    public function UpdateDetails(Request $request)
    {
        $admin = Admin::find((int)($request->input('id')));
        if ($admin) {
            // Update the string attribute
            $admin->first_name = $request->input('first_name');
            $admin->last_name = $request->input('last_name');
            $admin->save();
        }
        return $admin ? true : false;
    }
    public function DeleteAdmin($id)
    {
        $admin = Admin::where('id', $id)->first();
        if ($admin) {
            $admin->delete();
        }
        return $admin ? true : false;
    }

    //OTP 
    public function ResetPwdOTP(Request $request)
    {
        $sid = env("TWILIO_SID");
        $token = env("TWILIO_TOKEN");
        $verifySid = env("TWILIO_VERIFY_SID");
        $client = new Client($sid, $token);
        $verification = $client->verify->v2->services($verifySid)
            ->verifications
            ->create($request->mobile_no, "sms");

        return $verification->status;
    }
    public function CheckOTP(Request $request)
    {
        $sid = env("TWILIO_SID");
        $token = env("TWILIO_TOKEN");
        $verifySid = env("TWILIO_VERIFY_SID");
        $twilio = new Client($sid, $token);

        $otpCode = $request->input('otp');

        $verificationCheck = $twilio->verify->v2->services($verifySid)
            ->verificationChecks
            ->create([
                'to' => $request->mobile_no,
                'code' => $otpCode,
            ]);

        if ($verificationCheck->valid) {
            return true;
        } else {
            return false;
        }
    }

    public function RecoverAccount(Request $request)
    {
        $admin = Admin::where('mobile_no', request('mobile_no'))
                        ->where('school_id', request('school_id'))
                        ->first();
        if ($admin) {
            $admin->password = Hash::make($request->input('pwd'));
            $admin->save();
            return true;
        } else {
            return false;
        }
    }
}
