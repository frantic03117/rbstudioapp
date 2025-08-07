<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Traits\RbTrait;

class AuthController extends Controller
{
    use RbTrait;
    public function enter_mobile(Request $request)
    {
        date_default_timezone_set('Asia/kolkata');
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:10|max:10|regex:/[0-9]{10}/',
        ]);
        if ($validator->fails()) {
            $data = [
                'errors' => $validator->errors(),
                'data' => [],
                'success' => 0,
                'message' =>  'Invalid request'
            ];
            return  response()->json($data, 200);
        } else {
            $mobile = $request->mobile;
            $check = User::where(['mobile' =>  $mobile])->first();
            if ($check->deleted_at) {
                $data = [
                    'errors' => $validator->errors(),
                    'data' => [],
                    'success' => 0,
                    'message' =>  'Account deleted. Please contact us to activate your account'
                ];
                return  response()->json($data, 200);
            }
            $otp  = $mobile  == "8888888888" ? '8888' : mt_rand(1111, 9999);
            if ($check) {
                if ($check->role != "User") {
                    $data = [
                        'errors' => ['message' => ['Invalid mobile number']],
                        'data' => [],
                        'success' => 0,
                        'message' =>  'Invalid mobile number'
                    ];
                    return response()->json($data, 200);
                }
                $isSend = $this->send_otp($mobile, $otp);
                User::where('id', $check['id'])->update(['otp' => $otp, 'otp_verified' => '0', 'updated_at' => date('Y-m-d H:i:s'), 'fcm_token' => null]);
            } else {
                User::insert(['mobile' => $mobile, 'otp' => $otp, 'created_at' => date('Y-m-d'), 'updated_at' => date('Y-m-d H:i:s')]);
                $isSend = $this->send_otp($mobile, $otp);
            }

            $data = [
                'fcm' => $check ? $check->fcm_token : "",
                'errors' => $isSend,
                'data' => ['OTP' => $otp, 'mobile' => $mobile],
                'success' => 1,
                'message' =>  'Otp has been sent to your mobile number.'
            ];
            return response()->json($data, 200);
        }
    }
    public function verify_otp(Request $request)
    {
        date_default_timezone_set('Asia/kolkata');
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:10|max:10|regex:/[0-9]{10}/|exists:users,mobile',
            'otp' => 'required|min:4|max:4|exists:users,otp'
        ]);
        if ($validator->fails()) {
            $data = [
                'errors' => $validator->errors(),
                'data' => [],
                'success' => 0,
                'message' =>  'Invalid request'
            ];
            return  response()->json($data, 200);
        }
        $mobile = $request->mobile;
        $otp = $request->otp;
        $stime = Carbon::now()->subMinutes(2);
        $data = [
            'mobile' => $mobile,
            'otp' => $otp,
            'role' => 'User'
        ];
        $user = User::where($data)->where('updated_at', '>=', date('Y-m-d H:i:s', strtotime($stime)))->first();
        if ($user) {
            User::where('id',  $user['id'])->update(['otp_verified' => '1', 'mobile_verified_at' => date('Y-m-d H:i:s'), 'otp' => '']);
            $data = [
                'errors' => [],
                'data' => ['otp' => $otp, 'mobile' => $mobile, 'user_id' => $user->id, 'is_verified' => $user->is_verified],
                'success' => 1,
                'message' =>  'Otp has been Verified'
            ];
            if ($user->is_verified == '1') {
                $token = $user->createToken('auth_token')->plainTextToken;
                $data['data'] = ['token' => $token, 'is_verified' => '1'];
            }
            return  response()->json($data, 200);
        } else {
            $data = [
                'errors' => ['otp' => 'Invalid OTP, Please Resend OTP'],
                'data' => ['otp' => $otp, 'mobile' => $mobile, 'user' => []],
                'success' => 0,
                'message' =>  'Invalid OTP'
            ];
            return  response()->json($data, 200);
        }
    }
    public function register(Request $request)
    {
        date_default_timezone_set('Asia/kolkata');
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:users,id',
            'name' => 'required|min:4|max:40',
            'email' => 'unique:users,email,' . $request->user_id,
            'gender' => 'in:Male,Female',
        ]);
        if ($validator->fails()) {
            $data = [
                'errors' => $validator->errors(),
                'data' => [],
                'success' => 0,
                'message' =>  'Invalid request'
            ];
            return  response()->json($data, 200);
        }
        $user_id = $request->user_id;
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'is_verified' => '1',
            'dob' => $request->dob,
            'gender' => $request->gender,
        ];
        if (User::where('id', $user_id)->update($data)) {
            $user = User::where('id', $user_id)->first();
            $token = $user->createToken('auth_token')->plainTextToken;
            $rdata = [
                'errors' => [],
                'data' => ['token' => $token],
                'success' => 1,
                'message' =>  'User Registered'
            ];
            return response()->json($rdata, 200);
        }
    }
}
