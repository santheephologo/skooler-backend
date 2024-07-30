<?php

namespace App\Repository;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CardInfo;
use App\Models\CartItem;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Twilio\Rest\Client;

class UserRepo implements IUserRepo
{
    //Fetch all users for admin side
    public function GetUsers($id)
    {
        $users =  User::where('school_id', $id)
                        ->get();
        return $users;
    }
    //User account status change
    public function ChangeUserStatus(Request $request)
    {
        $id = (int) ($request->input('id'));
        $isActive = $request->input('isActive');

        $user = User::find($id);

        if ($user) {
            if ($isActive) {
                $user->is_active = true;
                $user->save();
            } else {
                $user->is_active = false;
                $user->save();
            }
            //$user->is_active = !$user->is_active;
            return User::all();
        }
    }
    //update user address
    public function UpdateAddress(Request $request)
    {
        $user = User::find($request->input('id'));

        if (!$user) {
            return false;
        } else {
            $user->update($request->only('address'));
            return $user;
        }
    }
    //update user fname, surname
    public function UpdateName(Request $request)
    {
        $user = User::find($request->input('id'));

        if (!$user) {
            return false;
        } else {
            $user->update($request->only('first_name', 'last_name'));
            return $user;
        }
    }
    //Update user profile picture
    public function UpdateProfilePic(Request $request)
    {
        $user = User::find($request->input('id'));
        if ($user->profile_pic) {
            Storage::disk('s3')->delete($user->profile_pic);
        }
        $path = $request->file('avatar')->store(
            'public/userpic',
            's3'
        );
        Storage::disk('s3')->setVisibility($path, 'public');
        if ($path) {
            $user->profile_pic = $path;
            $user->save();
            return $user;
        } else {
            return false;
        }
    }
    //Add product to cart
    public function AddToCart(Request $request)
    {
        $user_id = $request->input('user_id');
        $product_id = $request->input('product_id');
        $cartItem = CartItem::where('user_id', $user_id)
            ->where('product_id', $product_id)
            ->first();
        if (!$cartItem) {
            $cartItem = new CartItem();
            $cartItem->school_id = $request->input("school_id");
            $cartItem->user_id = $user_id;
            $cartItem->product_id = $product_id;
            $cartItem->product_name = $request->input("product_name");
            $cartItem->quantity = $request->input("quantity");
            $cartItem->price = $request->input("price");
            $cartItem->totalPrice = $request->input("totalPrice");
            $cartItem->save();
            return true;
        } else {
            if ($cartItem) {
                $cartItem->quantity = $request->input("quantity");
                $cartItem->price = $request->input("price");
                $cartItem->totalPrice = $request->input("totalPrice");
                $cartItem->save();
                return true;
            } else {
                return false;
            }
        }
    }
    //Update cart item
    public function UpdateCartItem($id, $qty, $price)
    {
        $cartItem = CartItem::find($id);
        if ($cartItem) {
            $cartItem->quantity = $qty;
            $cartItem->totalPrice = $qty * $price;
            $cartItem->save();

            $user_id = $cartItem->user_id;
            $subTotal = CartItem::where('user_id', $user_id)
                ->select(CartItem::raw('SUM(totalPrice) as total'))
                ->first()
                ->total;
            return $subTotal;
        } else {
            return false;
        }
    }
    //delete cart item
    public function DeleteFromCart($id)
    {
        $cartItem = CartItem::where('id', $id)->first();

        if ($cartItem) {
            $user_id = $cartItem->user_id;
            $cartItem->delete();
            $subTotal = CartItem::where('user_id', $user_id)
                ->select(CartItem::raw('SUM(totalPrice) as total'))
                ->first()
                ->total;

            return response()->json(['message' => 'deleted', 'subtotal' => $subTotal], 200);
        } else {
            return response()->json(['message' => 'not found'], 404);
        }
    }
    //Fetch user cart
    public function FetchCart()
    {
        $user = Auth::user();
        $cartItems = CartItem::where('user_id', $user->id)->get();
        foreach ($cartItems as &$item) {
            $product = Product::find($item->product_id);
            $item->stock = $product->stock;
            $item->thumbnail = $product->thumbnail;
        }
        $subTotal = CartItem::where('user_id', $user->id)
            ->select(CartItem::raw('SUM(totalPrice) as total'))
            ->first()
            ->total;
        return [$cartItems, $subTotal];
    }
    //fetch user notifications
    public function GetNotifications()
    {
        $user = Auth::user();
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        if ($unreadNotifications > 0) {
            $alerts = Notification::where('user_id', $user->id)->get();
            return $alerts;
        } else {
            Notification::where('user_id', $user->id)->delete();
            return [];
        }
    }
    public function UpdateAlertStatus()
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)->update(['is_read' => true]);
        return true;
    }
    public function AddCard(Request $request)
    {
        $existingCard = CardInfo::where('user_id', (int)$request->input('user_id'))->first();
        $encryptedCardDetails = Crypt::encrypt($request->input('card_details'));
        if ($existingCard) {
            $existingCard->update(['card_details' => $encryptedCardDetails]);
            return ["updated", $existingCard];
        } else {
            $cardInfo = CardInfo::create([
                'user_id' => (int)$request->input('user_id'),
                'card_details' => $encryptedCardDetails,
            ]);
            return ['details added', $cardInfo];
        }
    }
    public function FetchCards($id)
    {
        $cards = CardInfo::where("user_id", $id)->first();

        if ($cards) {
            $decryptedCardDetails = $cards->card_details ? Crypt::decrypt($cards->card_details) : null;

            return $decryptedCardDetails;
        } else {
            return "[]";
        }
    }
    public function GetUserReviews($id)
    {
        $reivewed = Review::where('user_id', $id)->get();
        $toReview = Order::where('user_id', $id)
            ->where('reviewed', false)
            ->get();
        return [$reivewed, $toReview];
    }
    public function DeleteReview($id)
    {
        $review = Review::where('id', $id)->first();
        if ($review) {
            $review->delete();
            return true;
        } else {
            return false;
        }
    }
    public function RateProduct(Request $request)
    {
        $review = Review::where('product_id', $request->product_id)
            ->where('user_id', $request->user_id)
            ->first();
        $name = 'Thank you for your valuable review.';
        $info = 'Happy shopping';
        $type = 'review';
        $is_read = false;
        $user_id = $request->user_id;

        $notification = new Notification();

        $notification->name = $name;
        $notification->info = $info;
        $notification->type = $type;
        $notification->is_read = $is_read;
        $notification->user_id = $user_id;
        if ($review) {
            $review->update(['rating' => $request->rating]);

            if ($request->comment !== null) {
                $review->update(['comment' => $request->comment]);
                $notification->save();
            }

            return $review;
        } else {
            $data = [
                'product_id' => $request->product_id,
                'product_name' => $request->product_name,
                'user_id' => $request->user_id,
                'user_name' => $request->user_name,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ];

            $review = Review::create($data);
            $notification->save();
            return $review;
        }
    }

    public function FetchUserContact($id)
    {
        $user = User::find($id);
        if ($user) {
            return [
                $user->id,
                $user->first_name,
                $user->last_name,
                $user->email,
                $user->mobile_no
            ];
        }
    }
    //OTP
    public function VerifyUserNumber(Request $request)
    {
        $sid = env("TWILIO_SID");
        $token = env("TWILIO_TOKEN");
        $verifySid = env("TWILIO_VERIFY_SID");
        $client = new Client($sid, $token);

        $verification = $client->verify->v2->services($verifySid)
            ->verifications
            ->create($request->mobile_no, "sms");
        if ($verification) {
            return  true;
        } else {
            return  false;;
        }
    }
    public function RecoverAccount(Request $request)
    {
        $user = User::where('school_id', request('school_id'))
                    ->where('mobile_no', request('mobile_no'))
                    ->first();
        if ($user) {
            $user->password = Hash::make($request->input('pwd'));
            $user->save();
            return true;
        } else {
            return false;
        }
    }
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

        return $verificationCheck->valid;
    }
}
