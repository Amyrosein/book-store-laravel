<?php

namespace App\Http\Controllers;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Kavenegar\KavenegarApi;

class AuthController extends Controller
{
    public function sms_otp($phone, $otp_token)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln($otp_token . " for " . $phone);
        $api_key       = "4D733741673356794274456D3048694C4D506D476A464A48304E524A564D68676B4635773237736A3141303D";
        $kaveh_negar_url = "https://api.kavenegar.com/v1/{$api_key}/verify/lookup.json";
        $data          = array(
            'receptor' => $phone,
            'token'    => $otp_token,
            'template' => 'otp',
            'type'     => 'sms',
        );

        $res      = Http::get($kaveh_negar_url, $data);
        $res_obj = $res->object()->return;
        if ($res_obj->status == 200) {
            return response()->json([
                'status'  => true,
                'message' => $res_obj->message,
            ], 200);
        }

        return response()->json([
            'status'  => false,
            'message' => $res_obj->message,
            'code'    => $res_obj->status,
        ], 401);
    }

    public function generate_otp($phone)
    {
        $otp = (new Otp())->generate($phone, 'numeric', 4, 5);

        return $this->sms_otp($phone, $otp->token);
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

        return ($this->generate_otp($user->phone));
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

        return ($this->generate_otp($user->phone));
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
                'token'   => $token->plainTextToken,
            ], 200);
        }

        return response()->json([
            'message' => "Login failed",
            'error'   => $otp->message,
        ], 401);
    }
}
