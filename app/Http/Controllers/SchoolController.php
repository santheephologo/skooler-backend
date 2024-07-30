<?php

namespace App\Http\Controllers;

use App\Repository\ISchoolRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class SchoolController extends Controller
{
    private ISchoolRepo $schoolRepo;

    public function __construct(ISchoolRepo $schoolRepo)
    {
        $this->schoolRepo = $schoolRepo;
    }

    public function getSchools()
    {
        $schools = $this->schoolRepo->all();
        return response()->json([
            'schools' => $schools,
            'status' => 200
        ], 200);
    }

    public function checkSchoolId($id)
    {
        $response = $this->schoolRepo->CheckSchoolID($id);
        if ($response) {
            return response()->json([
                'available' => true,
                'status' => 200
            ], 200);
        } else {
            return response()->json([
                'available' => false,
                'status' => 200
            ], 200);
        }
    }
    public function addSchool(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required|unique:schools',
                'name' => 'required|string',
                'address' => 'required|string',
                'country' => 'required|string',
                'country_code' => 'required|string',
                'currency' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'ui' => 'required|json',
                'is_active' => 'boolean',
                'subscription_expiry' => 'required|date',
                'delivery' => 'required|boolean',
                'pickup' => 'required|boolean',
                'logo' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $validatedData = $validator->validated();
                $response = $this->schoolRepo->store($validatedData);
                if ($response) {
                    return response()->json([
                        'message' => 'school created',
                        'school' => $response,
                        'status' => 201
                    ], 201);
                } else {
                    return response()->json([
                        "message" => "Error adding school",
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function fetchSchool($id)
    {
        $school = $this->schoolRepo->fetchSchool($id);
        if ($school) {
            return response()->json([
                'school' => $school,
                'status' => 200
            ], 200);
        } else {
            return response()->json(["message" => "Unauthorized access", 'status' => 401], 401);
        }
    }

    public function addSchoolLogo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'logo' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {

                $response = $this->schoolRepo->AddSchoolLogo($request);
                if ($response) {
                    return response()->json([
                        'path' => $response,
                        'message' => 'success',
                        'status' => 201
                    ], 201);
                } else {
                    return response()->json([
                        "message" => "Error adding logo",
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function updateSchoolLogo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:schools,id',
                'logo' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {

                $response = $this->schoolRepo->UpdateSchoolLogo($request);
                if ($response) {
                    return response()->json([
                        'message' => 'updated',
                        'status' => 200
                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Error updating logo",
                    ], 406);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function updateUI(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:schools,id',
                'ui' => 'required|json',

            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        }

        $id = $request->input('id');
        $ui = $request->input('ui');
        $response = $this->schoolRepo->updateUI($ui, $id);
        if ($response) {
            return response()->json([
                'message' => 'updated',
                'status' => 200
            ], 200);
        } else {
            return response()->json(["error" => "couldn't update"], 404);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:schools,id',
                'is_active' => 'required|boolean',

            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        }

        $id = $request->input('id');
        $is_active = $request->input('is_active');
        $response = $this->schoolRepo->updateStatus($is_active, $id);
        if ($response) {
            return response()->json([
                'message' => 'updated',
                'status' => 200
            ], 200);
        } else {
            return response()->json(["error" => "couldn't update"], 404);
        }
    }
    public function updateExpiry(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:schools,id',
                'subscription_expiry' => 'required|date',

            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        }

        $id = $request->input('id');
        $subscription_expiry = $request->input('subscription_expiry');
        $response = $this->schoolRepo->updateExpiry($subscription_expiry, $id);
        if ($response) {
            return response()->json([
                'message' => 'updated',
                'status' => 200
            ], 200);
        } else {
            return response()->json(["error" => "couldn't update"], 404);
        }
    }

    public function updateInfo(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:schools,id',
                'name' => 'required|string',
                'address' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
                'country' => 'required|string',
                'country_code' => 'required|string',
                'currency' => 'required|string',
                'delivery' => 'required|boolean',
                'pickup' => 'required|boolean',

            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        }

        $id = $request->input('id');
        $name = $request->input('name');
        $address = $request->input('address');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $country = $request->input('country');
        $country_code = $request->input('country_code');
        $currency = $request->input('currency');
        $delivery = $request->input('delivery');
        $pickup = $request->input('pickup');
        $response = $this->schoolRepo->updateInfo($id, $name, $address,  $email,  $phone, $country, $country_code,  $currency, $delivery, $pickup);
        if ($response) {
            return response()->json([
                'message' => 'updated',
                'status' => 200
            ], 200);
        } else {
            return response()->json(["error" => "couldn't update"], 404);
        }
    }

    public function updateAdmin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|exists:schools,id',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'mobile_no' => 'required|string',
                'password' => 'nullable|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {

                $response = $this->schoolRepo->updateAdmin($request);
                if ($response) {
                    return response([
                        'message' => 'updated',
                        'status' => 200
                    ], 200);
                } else {
                    return response()->json(["error" => "couldn't update"], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }        
    }

    public function deleteSchool($id)
    {
        $school = $this->schoolRepo->deleteSchool($id);
        if ($school) {
            return response()->json([
                'message' => 'success',
                'status' => 200
            ], 200);
        } else {
            return response()->json(["error" => "No School Found"], 404);
        }
    }
}
