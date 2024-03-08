<?php

namespace App\Http\Controllers;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function sms_otp($phone, $otp_token)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln($otp_token . " for " . $phone);
        $api_key       = config('services.kaveh_negar.api_key');
        $kaveh_negar_url = "https://api.kavenegar.com/v1/{$api_key}/verify/lookup.json";
        $data          = array(
            'receptor' => $phone,
            'token'    => $otp_token,
            'template' => 'otp',
            'type'     => 'sms',
        );

        return $otp_token;

        $res      = Http::get($kaveh_negar_url, $data);
        $res_obj = $res->object()->return;
        return response()->json([
            'status' => $res_obj->status == 200,
            'code'  => $res_obj->status,
            'message' => $res_obj->message,
        ], $res_obj->status == 200 ? 200 : 401);
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

    public function buy_vip(Request $request)
    {
        // check if user is vip
        $user = $request->user();
        if ($user->is_vip && $user->vip_expires_at > now()){
            return response()->json([
                'message' => "User already is VIP",
                "status" => false,
            ], 409);
        }
        // Buying VIP

        // updating user vip status
        $user->is_vip = true;
        $user->vip_expires_at = now()->addMonth();
        $user->save();

        return response()->json([
            'message' => 'user is VIP from now',
            'status' => true
        ], 200);
    }

    public function delete_token(Request $request, String $phone)
    {
        $user = User::where('phone', $phone)->first();
        if (empty($user)){
            return response()->json([
                'message' => 'user not found',
                'status' => false
            ], 404);
        }
        $user->tokens()->delete();
        return response()->json(status: 204);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
        'message' => 'Logged out successfully',
        'status' => true
    ], 200);
    }
}
