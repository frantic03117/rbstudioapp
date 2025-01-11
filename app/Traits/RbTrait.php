<?php

namespace App\Traits;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

trait RbTrait
{
    public function send_notification($token, $title, $message, $uid = null, $type = null)
    {
        date_default_timezone_set('Asia/kolkata');
        $u_id = $uid ?? auth('sanctum')->user()->id;
        $payload = json_encode([
            "fcm_token" => $token,
            "title" => $title,
            "message" => $message
        ]);
        
        
        $response = Http::post('http://213.210.36.202:5001/send-notification', [
            'fcm_token' => $token,
            'title' => $title,
            'message' => $message
        ]);
       
        return $response;
        return true;
       
    }
  public function send_otp($mobile, $otp)
{
    $msg = "Hello, {$otp} is your OTP for R and B Studios Booking Login. Please do not share this OTP. See you at the studios !! Regards, R and B Studios RBSTDS";
    $clientId = env('MESG_CLIENT_ID');
    $apiKey = env('MESG_API_KEY');
    
    $url = "http://91.108.105.7/api/v2/SendSMS";

   
    $queryParams = [
        'SenderId' => 'RBSTDS',
        'Is_Unicode' => 'false',
        'Is_Flash' => 'false',
        'Message' => $msg,
        'MobileNumbers' => "91{$mobile}",
        'ApiKey' => $apiKey,
        'ClientId' => $clientId,
    ];

    
    $response = Http::get($url, $queryParams);

   return $response;

}
    
    public function send_booking_msg($booking_date, $ftime, $ttime, $amount){
        $msg = "Hello you have booked the studio on {$booking_date} from {$ftime} Your {$ttime} will be in {$amount} Total amount for {#var#} is {#var#} inclusive of all taxes. Please Note: This is a non-cancellable booking. Cancellation will incur a 50% charge. Setup and packup time will be added to your booking hours. See you at the studios !! R AND B STUDIOS";
    }
    
    
    
    public function run_msg_api($msg, $mobile){
        $ClientId = env('MESG_CLIENT_ID');
        $apikey = env('MESG_API_KEY');
        $url = "http://91.108.105.7/api/v2/SendSMS?SenderId=RBSTDS&Is_Unicode=false&Is_Flash=false&Message={$msg}&MobileNumbers=91{$mobile}&ApiKey={$apikey}&ClientId={$ClientId}";
        Http::get($url);
    }
}