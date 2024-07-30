<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Repository\IAdminRepo;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;

class AdminController extends Controller
{
    private IAdminRepo $adminRepo;

    public function __construct(IAdminRepo $adminRepo)
    {
        $this->adminRepo = $adminRepo;
    }
    //Stats for admin dashboard : admin, user, sales counts
    public function fetchStats($id)
    {
        try {
            $stats = $this->adminRepo->FetchStats($id);
            if ($stats) {
                return response([
                    'admins_count' => $stats[0],
                    'products_count' => $stats[1],
                    'users_count' => $stats[2],
                    'orders_count' => $stats[3],
                    'upcoming' => $stats[4],
                    'total' => $stats[5],
                    'status' => 200
                ], 200);
            } else {
                return response(['Error Fetching stats'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function fetchAdmins($id)
    {
        try {
            $admins = $this->adminRepo->GetAllAdmins($id);
            if ($admins) {
                return response([
                    'admins' => $admins,
                    'status' => 200
                ], 200);
            } else {
                return response(['Error Fetching Admins'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function adminSignup(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|exists:schools,id',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'mobile_no' => 'required|string',
                'roles' => 'required|json',
                'password' => 'required|string|min:8',
                'is_active' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {

                $response = $this->adminRepo->AddAdmin($request);
                if ($response) {
                    return response([
                        'message' => "admin created",
                        'status' => 201
                    ], 201);
                } else {
                    return response(['Error creating Admin'], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function adminLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|exists:schools,id',
                'mobile_no' => 'required|string',
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {

                $response = $this->adminRepo->AdminLogin($request);
                $cookie = cookie('jwt', $response[1], 60 * 12, null, null, false, false);
                if ($response) {
                    return response([
                        'message' => "Login success",
                        'admin' => $response[0],
                        'token' => $response[1],
                        'status' => 200
                        //], 200);
                    ], 200)->withCookie($cookie);
                } else {
                    return response([
                        'message' => ['These credentials do not match our records.']
                    ], Response::HTTP_UNAUTHORIZED);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function updateRoles(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:admins,id',
                'roles' => 'required|json',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $response = $this->adminRepo->UpdateRoles($request);
                if ($response) {
                    return response([
                        'message' => "updated success",
                    ], 200);
                } else {
                    return response()->json(['error' => 'Admin not found'], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function updateDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:admins,id',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {

                $response = $this->adminRepo->UpdateDetails($request);
                if ($response) {
                    return response([
                        'message' => "updated success",
                    ], 200);
                } else {
                    return response()->json(['error' => 'Admin not found'], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function admin()
    {
        try {
            $admin = $this->adminRepo->GetAdmin();
            //$admin = Auth::user();
            if ($admin) {
                return (response()->json(['admin' => $admin, 'status' => 200], 200));
            } else {
                return (response()->json(['message' => 'admin not found', 'response' => $admin], 404));
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function adminLogout(Request $request)
    {
        try {
            $admin = $this->adminRepo->AdminLogout($request);
            //$admin = Auth::guard('admins')->user();
            $cookie = Cookie::forget('jwt');
            //Auth::logout();
            return response([
                'message' => "logged out",
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:admins,id',
            'current_password' => 'required',
            'new_password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        } else {
            $reponse = $this->adminRepo->ResetPassword($request);

            if (!$reponse) {
                return response()->json(['error' => 'Current password is incorrect'], 401);
            } else {
                return response()->json(['message' => 'Password changed successfully'], 200);
            }
        }
    }
    public function changeAdminStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:admins,id',
                'isActive' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $response = $this->adminRepo->ChangeAdminStatus($request);
                if ($response) {
                    return response()->json(['message' => 'Status updated', "admins" => $response], 200);
                } else {
                    return response()->json(['message' => 'User not found'], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function deleteAdmin($id)
    {
        try {

            $response = $this->adminRepo->DeleteAdmin($id);
            if ($response) {
                return response([
                    'message' => "Admin deleted",
                ], 200);
            } else {
                return response()->json(['error' => 'Admin not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function resetPwdOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_no' => 'required|exists:admins,mobile_no'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {

                $reponse = $this->adminRepo->ResetPwdOTP($request);

                return response()->json(['message' => $reponse], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function checkOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_no' => 'required|string',
                'otp' => 'required|string'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->error()], 422);
            } else {
                $reponse = $this->adminRepo->CheckOTP($request);

                if ($reponse) {
                    return  response(['verified' => true, 'status' => 200], 200);
                } else {
                    return  response(['verified' => false, 'status' => 200], 200);
                }
            }
        } catch (\Exception $e) {
            return response(['message' => 'Error', $e->getMessage()], 500);
        }
    }
    public function recoverAccount(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_no' => 'required|exists:admins,mobile_no',
                'pwd' => 'required|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {
                $response = $this->adminRepo->RecoverAccount($request);
                if ($response) {
                    return response()->json(['message' => 'updated', 'status' => 200], 200);
                } else {
                    return response()->json(['message' => 'unauthorized', 'status' => 406], 406);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
