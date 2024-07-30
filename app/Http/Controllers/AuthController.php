<?php

namespace App\Http\Controllers;

use App\Models\Student;

use App\Repository\IAuthRepo;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    private IAuthRepo $authRepo;

    public function __construct(IAuthRepo $authRepo)
    {
        $this->authRepo = $authRepo;
    }
    //user signup
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_id' => 'required|exists:schools,id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'student_id' => 'required|exists:students,id',
            'mobile_no' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'is_active' => "required|boolean",
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        } else {
            //$validatedData = $validator->validated();

            $createdUser = $this->authRepo->Signup($request);
            if (!$createdUser) {
                return response()->json(['error' => "An error occurred"], 403);
            } else {
                return response()->json([
                    'message' => 'created',
                    'status' => 201
                ], 201);
            }
        }
    }
    //user login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_id' => 'required|exists:schools,id',
            'mobile_no' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        } else {

            $response = $this->authRepo->login($request);

            if ($response) {
                $cookie = cookie('jwt', $response[1], 60 * 24);
                return response([
                    'message' => "Login success",
                    'user' => $response[0],
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
    }

    //fetching user data
    public function user()
    {
        $user = $this->authRepo->getUser();
        //$user = Auth::guard('users')->user();
        if ($user) {
            return (response()->json(['user' => $user], 200));
        } else {
            return (response()->json(['message' => 'Unauthorized'], 401));
        }
    }
    //delete user
    public function deleteUser($id)
    {
        try {
            $response = $this->authRepo->DeleteUser($id);

            $cookie = Cookie::forget('jwt');
            if ($response) {
                return response([
                    'message' => "Deleted",
                    'status' => 200
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    //user logout
    public function logout(Request $request)
    {
        try {
            $response = $this->authRepo->Logout($request);
            //$user = Auth::guard('users')->user();
            //$user->tokens()->delete();

            // Clear the authentication for the 'admins' guard
            //Auth::guard('users')->logout();

            // Remove the JWT cookie
            $cookie = Cookie::forget('jwt');
            if ($response) {
                return response([
                    'message' => "logged out",
                    'status' => 200
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    //Adding student 
    public function AddStudent(Request $request)
    {
        $student = Student::create([
            'name' => $request->input('name'),
            'mobile_no' => $request->input('mobile_no')

        ], Response::HTTP_CREATED);
        return $student;
    }
    //Reset pwd
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'current_password' => 'required',
            'new_password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        } else {
            $reponse = $this->authRepo->ResetPassword($request);

            if ($reponse) {
                return response()->json(['message' => 'Password changed successfully'], 200);
            } else {
                return response()->json(['error' => 'Current password is incorrect'], 401);
            }
        }
    }

    //Sign up validation check
    public function ValidationCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_id' => 'required|exists:schools,id',
            'student_id' => 'required|exists:students,id',
            'email' => 'required|email',
            'mobile_no' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        } else {
            $studentId = (int) ($request->input('student_id'));
            $mobile_no = ($request->input('mobile_no'));
            $emailID = ($request->input('email'));
            $schoolID = ($request->input('school_id'));
            $check = $this->authRepo->validationCheck($mobile_no, $emailID, $studentId, $schoolID);
            if ($check) {
                return response([
                    'message' => $check
                ], 200);
            } else {
                return response([
                    'message' => "error"
                ], 404);
            }
        }
    }
}
