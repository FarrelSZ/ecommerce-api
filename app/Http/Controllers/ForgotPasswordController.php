<?php

namespace App\Http\Controllers;

use App\Mail\SendForgotPasswordOTP;
use Illuminate\Http\Request;
use App\ResponseFormatter;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ForgotPasswordController extends Controller
{
    public function request()
    {
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(400, $validator->errors());
        }

        $check = DB::table('password_reset_tokens')->where('email', request()->email)->count();
        if ($check > 0) {
            return ResponseFormatter::error(400, null, [
                'You has do this, please resend OTP'
            ]);
        }

        do {
            $otp = rand(100000, 999999);

            $otpCount = DB::table('password_reset_tokens')->where('token', $otp)->count();
        } while ($otpCount > 0);

        DB::table('password_reset_tokens')->insert([
            'email' => request()->email,
            'token' => $otp
        ]);

        $user = User::where('email', request()->email)->firstorfail();

        Mail::to(request()->email)->send(new SendForgotPasswordOTP($user, $otp));

        return ResponseFormatter::success([
            'is_sent' => true
        ]);
    }

    public function resendOtp()
    {
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(400, $validator->errors());
        }

        $otpRecord = DB::table('password_reset_tokens')->where('email', request()->email)->first();

        if (is_null($otpRecord)) {
            return ResponseFormatter::error(400, null, [
                'Request not found!'
            ]);
        }

        $user = User::whereEmail(request()->email)->firstorfail();

        do {
            $otp = rand(100000, 999999);

            $otpCount = DB::table('password_reset_tokens')->where('token', $otp)->count();
        } while ($otpCount > 0);

        DB::table('password_reset_tokens')->where('email', request()->email)->update([
            'token' => $otp
        ]);

        Mail::to(request()->email)->send(new SendForgotPasswordOTP($user, $otp));

        return ResponseFormatter::success([
            'is_sent' => true
        ]);
    }

    public function verifyOtp()
    {
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|exists:password_reset_tokens,token'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(400, $validator->errors());
        }

        $check = DB::table('password_reset_tokens')->where('token', request()->otp)->where('email', request()->email)->count();
        if ($check > 0) {
            return ResponseFormatter::success([
                'is_valid' => true
            ]);
        }

        return ResponseFormatter::error(400, 'Invalid OTP');
    }
    public function resetPassword()
    {
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|exists:password_reset_tokens,token',
            'password' => 'required|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(400, $validator->errors());
        }

        $token = DB::table('password_reset_tokens')->where('token', request()->otp)->where('email', request()->email)->first();
        if (!is_null($token)) {
            $user = User::where('email', request()->email)->first();
            $user->update([
                'password' => bcrypt(request()->password)

            ]);

            DB::table('password_reset_tokens')->where('token', request()->otp)->where('email', request()->email)->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return ResponseFormatter::success([
                'token' => $token
            ]);
        }

        return ResponseFormatter::error(400, 'Invalid OTP');
    }
}
