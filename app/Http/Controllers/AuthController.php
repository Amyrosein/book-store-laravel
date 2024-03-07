<?php

namespace App\Http\Controllers;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function sms_otp($phone, $otp_token)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln($otp_token . " for " . $phone);

        return response()->json([
            'status'  => true,
            'message' => "sms sent successfully",
        ], 200);
    }

    public function generate_otp($phone)
    {
        $otp      = (new Otp())->generate($phone, 'numeric', 4, 5);
        $sms_sent = $this->sms_otp($phone, $otp->token);
        $sms_sent = $sms_sent->getData();
        if ($sms_sent->status) {
            return response()->json([
                'status'  => true,
                'message' => "otp created successfully",
            ], 200);
        }

        return response()->json([
            'status'  => false,
            'message' => "otp failed to sent",
        ], 401);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:31'],
            'last_name'  => ['required', 'string', 'max:31'],
            'phone'      => ['required', 'string', 'digits:11'],
        ]);

        if (User::where('phone', $validated['phone'])->exists()) {
            return response()->json(['message' => "User already exist, log in"], 409);
        }

        $user = User::create($validated);

        $otp = ($this->generate_otp($user->phone))->getData();
        if ($otp->status) {
            return response()->json([
                'message' => $otp->message,
            ], 200);
        }

        return response()->json([
            'message' => $otp->message,
        ], 401);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'digits:11'],
        ]);
        $user      = User::where('phone', $validated['phone'])->first();
        if (empty($user)) {
            return response()->json([
                'message' => 'user does not exist, register first',
            ], 401);
        }

        $otp = ($this->generate_otp($user->phone))->getData();
        if ($otp->status) {
            return response()->json([
                'message' => $otp->message,
            ], 200);
        }

        return response()->json([
            'message' => $otp->message,
        ], 401);
    }

    public function validate_otp(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'digits:4'],
            'phone' => ['required', 'string', 'digits:11'],
        ]);

        $otp = (new Otp)->validate($validated['phone'], $validated['token']);

        if ($otp->status) {
            $user = User::where('phone', $validated['phone'])->first();
            $user->tokens()->delete();
            $token = $user->createToken('login', expiresAt: now()->addMonth());
            return response()->json([
                'message' => "Logged in successfully",
                'token' => $token->plainTextToken,
            ], 200);
        }
        return response()->json([
            'message' => "Login failed",
            'error' => $otp->message,
        ], 401);
    }
}
