<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Validator;

class RegisterController extends Controller
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'c_password' => 'required|same:password',
            ]);
            if ($validator->fails()) {
                return response()->json(["error" => $validator->errors(), "status" => 'error'], 422);
            }
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $token = $user->createToken('MyApp')->accessToken;
            Mail::send('email.emailVerificationEmail', ['token' => $token], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Email Verification Mail');
            });
            Log::info("Email send for user id " . $user->id);
            return response()->json(["data" => $user, "status" => 'success'], 200);
        } catch (\Exception$e) {
            Log::error(
                [
                    "Message" => $e->getMessage(),
                    "function" => 'RegisterController@register',
                ]);
            return response()->json(["error" => $e->getMessage(), "status" => 'error'], 422);
        }
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(["error" => $validator->errors(), "status" => 'error'], 422);
            }
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                if (isset($user->email_verified_at)) {
                    $success['token'] = $user->createToken('MyApp')->accessToken;
                    $success['name'] = $user->name;
                    Log::info("TOken generate for user id " . $user->id);
                    return response()->json(["data" => $success,
                        "status" => 'success', 'message' => 'User login successfully.'], 200);
                } else {
                    return response()->json(["data" => $user,
                        "status" => 'success', 'message' => 'Your Email not Verify please verify email address.'], 200);
                }
            } else {
                return response()->json(["error" => $e->getMessage(),
                    "status" => 'error', 'message' => 'Unauthorised.'], 422);
            }
        } catch (\Exception$e) {
            Log::error(
                [
                    "Message" => $e->getMessage(),
                    "function" => 'RegisterController@login',
                ]);
            return response()->json(["error" => $e->getMessage(), "status" => 'error'], 422);
        }
    }

    public function verifyAccount($token)
    {
        try {
            $user_id = DB::table('oauth_access_tokens')->where('id', $token)->first();
            $user = User::whereId($user_id->id);
            if (!$user->email_verified_at) {
                $user->user->email_verified_at = now();
                $user->save();
                Log::info("email verify for user id " . $user->id);
                $message = "Your e-mail is verified. You can now login.";
            } else {
                $message = "Your e-mail is already verified. You can now login.";
            }
            return response()->json(["data" => $user, "status" => 'success', 'message' => $message], 200);
        } catch (\Exception$e) {
            Log::error(
                [
                    "Message" => $e->getMessage(),
                    "function" => 'RegisterController@verifyAccount',
                ]);
            return response()->json(["error" => $e->getMessage(), "status" => 'error'], 422);
        }
    }
}
