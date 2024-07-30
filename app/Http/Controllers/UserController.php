<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repository\IUserRepo;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private IUserRepo $userRepo;

    public function __construct(IUserRepo $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function updateProfilePic(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:users,id',
                'avatar' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $response = $this->userRepo->UpdateProfilePic($request);
                if ($response) {
                    return response()->json([
                        'message' => 'updated',
                        'user' => $response,
                        'status' => 200
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'error',
                        'status' => 406
                    ], 406);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function addToCart(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|exists:schools,id',
                'user_id' => 'required|exists:users,id',
                'product_id' => 'required|exists:products,id',
                'product_name' => 'required|string',
                'quantity' => 'required|integer',
                'price' => 'required|numeric',
                'totalPrice' => "required|numeric",
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                //$validatedData = $validator->validated();
                $response = $this->userRepo->AddToCart($request);
                if ($response) {
                    return response()->json([
                        "message" => "Cart item updated",
                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Error updating cart item",
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    function deleteCartItem($id)
    {
        try {
            $response = $this->userRepo->DeleteFromCart($id);
            return $response;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    function updateCartItem($id, $qty, $price)
    {
        try {
            $response = $this->userRepo->UpdateCartItem($id, $qty, $price);
            if ($response) {
                return response()->json(['message' => 'updated', 'subtotal' => $response], 200);
            } else {
                return response()->json(['message' => 'not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function fetchCart()
    {
        try {
            $response = $this->userRepo->FetchCart();
            return response()->json([
                "cart" => $response[0],
                "subtotal" => number_format($response[1], 2, '.', '')
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function fetchNotifications()
    {
        try {
            $response = $this->userRepo->GetNotifications();
            return response()->json([
                "alerts" => $response,
                "status" => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function updateAlertStatus()
    {
        try {
            $response = $this->userRepo->UpdateAlertStatus();
            if ($response) {
                return response()->json([
                    "message" => "done",
                    "status" => 200
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function rateProduct(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'product_name' => 'required|string',
                'user_id' => 'required|integer',
                'user_name' => 'required|string',
                'rating' => 'required|integer',
                'comment' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $response = $this->userRepo->RateProduct($request);
                if ($response) {
                    return response()->json(['message' => 'added', 'review' => $response], 200);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function deleteReview($id)
    {
        try {
            $response = $this->userRepo->DeleteReview($id);
            if ($response) {
                return response()->json([
                    "message" => "deleted",
                ], 200);
            } else {

                return response()->json([
                    "message" => "not found",
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function updateName(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:users,id',
                'first_name' => 'required|string',
                'last_name' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $response = $this->userRepo->UpdateName($request);
                if ($response) {
                    return response()->json(['message' => 'name updated', 'user' => $response], 200);
                } else {
                    response()->json(['error' => 'User not found'], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function updateAddress(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:users,id',
                'address' => 'required|string'
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $response = $this->userRepo->UpdateAddress($request);
                if ($response) {
                    return response()->json(['message' => 'address updated', 'user' => $response], 200);
                } else {
                    response()->json(['error' => 'User not found'], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function getReviews($id)
    {
        try {
            $response = $this->userRepo->GetUserReviews($id);
            if ($response) {
                return response()->json([
                    "reviewed" => $response[0],
                    "toReview" => $response[1]
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function addCard(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'card_details' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                //$validatedData = $validator->validated();
                $response = $this->userRepo->AddCard($request);
                if ($response) {
                    return response()->json(['message' => $response[0], 'data' => $response[1]], 201);
                } else {
                    return response()->json([
                        "message" => "Error updating cart item",
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function getCards($id)
    {
        try {
            $response = $this->userRepo->FetchCards($id);
            if ($response) {
                return response()->json(['cardData' => $response], 200);
            } else {
                return response()->json(['message' => "Not found"], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    

    public function fetchUsers()
    {
        try {
            $response = $this->userRepo->GetUsers();
            if ($response) {
                return response()->json([
                    "users" => $response,
                    "status" => 200
                ], 200);
            } else {

                return response()->json([
                    "message" => "not found",
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }
    public function changeUserStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:users,id',
                'isActive' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                //$validatedData = $validator->validated();
                $response = $this->userRepo->ChangeUserStatus($request);
                if ($response) {
                    return response()->json(['message' => 'Status updated', "users" => $response], 200);
                } else {
                    return response()->json(['message' => 'User not found'], 404);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function fetchUserContact($id)
    {
        try {
            $response = $this->userRepo->FetchUserContact($id);
            if ($response) {
                return response()->json([
                    'user_id' => $response[0],
                    'fname' => $response[1],
                    'lname' => $response[2],
                    'email' => $response[3],
                    'mobile_no' => $response[4]
                ], 200);
            } else {
                return response()->json(['message' => 'User not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error' . $e->getMessage()], 500);
        }
    }

    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_no' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {

                $response = $this->userRepo->VerifyUserNumber($request);
                return response()->json(['message' => $response], 200);
            }
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public  function resetPwdOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_no' => 'required|exists:users,mobile_no'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {
                $response = $this->userRepo->ResetPwdOTP($request);
                return response()->json(['message' => $response], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public  function resetUserPwd(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|exists:schools,id',
                'mobile_no' => 'required|exists:users,mobile_no',
                'pwd' => 'required|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {
                $response = $this->userRepo->RecoverAccount($request);
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
    public function verifyOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_no' => 'required|string',
                'otp' => 'required|string'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->error()], 422);
            } else {
                $response = $this->userRepo->CheckOTP($request);

                return  response(['verified' => $response, 'status' => 200], 200);
            }
        } catch (\Exception $e) {
            return response(['message' => 'Error', $e->getMessage()], 500);
        }
    }
}
