<?php

namespace App\Http\Controllers;

use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;
use App\Models\Rent;
use App\Models\ServiceStudio;
use App\Models\Studio\Charge;
use App\Models\Studio\Service;
use App\Models\Studio\Studio;

use App\Models\StudioImage;
use App\Models\RbNotification;
use App\Models\Vendor;
use App\Models\Booking;
use App\Models\Transaction;
use Razorpay\Api\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\RbTrait;


class StudioController extends Controller
{
    use RbTrait;
    function __construct()
    {
        $this->middleware(['permission:studios-list|studios-create|studios-edit|studios-delete'], ['only' => ['index', 'store']]);
        $this->middleware(['permission:studios-create'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:studios-edit'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:studios-delete'], ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = "Create New Studio";
        $stds = Studio::with("vendor");
        if (Auth::user()->role != "Super") {
            $stds->where('vendor_id', Auth::user()->vendor_id);
        }
        $vendor_id = $_GET['vendor_id'] ?? null;
        if ($vendor_id) {
            $stds->where('vendor_id', $vendor_id);
        }
        $studio = $stds->with('country')->with('state')->with('district')->with('images')
            ->with('products')
            ->with('charges')
            ->get();
        if ($request->expectsJson()) {
            return response()->json(['data' => $studio, 'success' => '1', 'message' => $title]);
        }
        // return response()->json($studio);
        // die;
        return view("admin.studios.list", compact("title", "studio"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): \Illuminate\View\View
    {
        $title = "Create New Studio";
        $vends = Vendor::where('id', '>', '0');
        if (Auth::user()->role != "Super") {
            $vends->where('id', Auth::user()->vendor_id);
        }
        $vendors = $vends->select(['id', 'name'])->get();
        $countries = Country::all();
        $services = Service::all();
        $res = compact("vendors", "countries", "title", "services");
        return view("admin.studios.create", $res);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            "name" => "required|max:200",
            "address" => 'required|max:200',
            'country_id' => 'required',
            'state_id' => 'required',
            'pincode' => 'required|min:6|max:6',
            'services' => 'required',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'opens_at' => 'required',
            'ends_at' => 'required'
        ]);
        $files = $request->images;
        $imgs = [];
        foreach ($files as $file) {
            $filename = Str::uuid() . strtotime(date('Y-m-d H:i:s')) . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            array_push($imgs, $filename);
        }
        $data = [
            'vendor_id' => $request->vendor_id,
            'name' => $request->name,
            'seat_count' => $request->seat_count ?? 0,
            'address' => $request->address,
            'state_id' => $request->state_id,
            'country_id' => $request->country_id,
            'district_id' => $request->district_id,
            'city' => $request->city,
            'pincode' => $request->pincode,
            'google_map' => $request->google_map,
            'equipment_info' => $request->equipment_info,
            'description' => $request->description,
            'terms' => $request->terms,
            'opens_at' => $request->opens_at,
            'ends_at' => $request->ends_at,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if ($request->mobile) {
            $data['mobile'] = $request->mobile;
        }
        $sid = Studio::insertGetId($data);
        foreach ($imgs as $img) {
            $idata = [
                'image' => 'uploads/' . $img,
                'studio_id' => $sid
            ];
            StudioImage::insert($idata);
        }
        foreach (json_decode($request->services) as $svd) {
            $sdata = [
                'service_id' => $svd->id,
                'charge' => $svd->amount,
                'studio_id' => $sid,
                'is_permissable' => $svd->isPermissable
            ];
            ServiceStudio::insert($sdata);
        }
        if ($sid) {
            return redirect()->back()->with('success', 'Studio Created Successfully');
        } else {
            return redirect()->route('studio.create')->with('error', 'Internal Error occured');
        }
    }
    public function add_resource($id)
    {
        $title = "Studio Rental Resource Management";
        $rents = Rent::whereNotIn('id', function ($q) use ($id) {
            $q->from('charges')->select('item_id')->where('studio_id', $id);
        })->get();
        $items = Charge::where('studio_id', $id)->where('item_id', '!=', null)->with('item')->get();

        return view("admin.studios.add_resource", compact("title", "rents", 'id', 'items'));
    }
    public function save_resource(Request $request, $id)
    {
        $request->validate([
            'item_id' => 'required',
            'charge' => 'required|numeric'
        ]);
        $data = [
            'studio_id' => $id,
            'item_id' => $request->item_id,
            'charge' => $request->charge,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (Charge::insert($data)) {
            return redirect()->back()->with('success', 'Inserted');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Studio\Studio  $studio
     * @return \Illuminate\Http\Response
     */
    public function show(Studio $studio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Studio\Studio  $studio
     * @return \Illuminate\Http\Response
     */
    public function edit(Studio $studio)
    {
        $title = "Edit Studio";
        $vendors = Vendor::all();
        $countries = Country::all();
        $services = Service::where('id', '>', 0)->get();
        $charge = Charge::where('studio_id', $studio->id)->where('type', 'Studio')->first();
        $s_services = ServiceStudio::where('studio_id', $studio->id)->join('services', 'services.id', '=', 'service_studios.service_id')->select(['service_studios.*', 'services.name'])->get();

        $states = State::where('country_id', $studio->country_id)->get();
        $cities = City::where('state_id', $studio->state_id)->get();
        $res = compact("vendors", "countries", "title", "s_services", "studio", "charge", "states", "cities", "services");
        return view("admin.studios.edit", $res);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Studio\Studio  $studio
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Studio $studio)
    {
        $request->validate([
            'vendor_id' => 'required',
            "name" => "required|max:200",
            "address" => 'required|max:200',
            'country_id' => 'required',
            'state_id' => 'required',
            'pincode' => 'required|min:6|max:6'

        ]);

        $data = [
            'vendor_id' => $request->vendor_id,
            'name' => $request->name,
            'address' => $request->address,
            'state_id' => $request->state_id,
            'country_id' => $request->country_id,
            'district_id' => $request->district_id,
            'city' => $request->city,
            'pincode' => $request->pincode,
            'google_map' => $request->google_map,
            'description' => $request->description,
            'equipment_info' => $request->equipment_info,
            'terms' => $request->terms,
            'color' => $request->color,
            'opens_at' => $request->opens_at,
            'ends_at' => $request->ends_at,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'updated_At' => date('Y-m-d H:i:s')
        ];
        if ($request->seat_count) {
            $data['seat_count'] = $request->seat_count;
        }
        if ($request->mobile) {
            $data['mobile'] = $request->mobile;
        }
        Studio::where('id', $studio->id)->update($data);

        return redirect()->back()->with('success', 'Studio Created Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Studio\Studio  $studio
     * @return \Illuminate\Http\Response
     */
    public function destroy(Studio $studio)
    {
        //
    }
    public function add_studio_service(Request $request)
    {
        $request->validate([
            'studio_id' => 'required',
            'service_id' => 'required',
            'charge' => 'required',
            'is_permissable' => 'required'
        ]);
        $data = $request->except('_token');
        $data['created_at'] = date('Y-m-d H:i:s');
        ServiceStudio::insert($data);
        return redirect()->back()->with('success', 'Studio Created Successfully');
    }
    public function update_s_service(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'charge' => 'required'
        ]);
        $data = [
            'charge' => $request->charge
        ];
        $data['created_at'] = date('Y-m-d H:i:s');
        $id = $request->id;
        ServiceStudio::where('id', $id)->update($data);
        return redirect()->back()->with('success', 'Studio Updated Successfully');
    }
    public function delete_s_service($id)
    {
        ServiceStudio::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Studio Created Successfully');
    }
    public function add_payment_online($id)
    {
        if (Booking::where('id', $id)->first()) {
            $is_Partial = $_GET['is_partial'] ?? null;
            if ($is_Partial && $is_Partial == 'true') {
                $isPartial = true;
            } else {
                $isPartial = null;
            }
            $title = "Pay Now";
            $item =  Booking::where('id', $id)->with('rents')->withSum('transactions', 'amount')->with('studio')->with('vendor')->with('service')->with('user')->first();
            $rents =  $item->rents;
            $arr = [];
            foreach ($rents as $r) {
                array_push($arr, $r->pivot->charge * $r->pivot->uses_hours);
            }
            $rentcharge = array_sum($arr);
            $res = compact('item', 'title', 'rentcharge', 'isPartial');

            // return response()->json($res);
            // die;
            return view('admin.bookings.payonline', $res);
        } else {
            abort('403');
        }
    }
    function encrypt($plainText, $key)
    {
        $key = hex2bin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }

    function decrypt($encryptedText, $key)
    {
        $key = hex2bin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = hex2bin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }
    public function delete_studio_resource(Request $request)
    {
        $id = $request->id;
        Charge::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Studio resource deleted Successfully');
    }
    public function update_studio_resource_charge(Request $request)
    {
        $id = $request->id;
        $charge = $request->charge;
        $data = [
            'charge' => $charge
        ];
        Charge::where('id', $id)->update($data);
        return redirect()->back()->with('success', 'Studio resource updated Successfully');
    }
    public function pay_now(Request $request, $id)
    {
        $booking = Booking::where('id', $id)->with('studio')
            ->with('transactions')->withSum('transactions', 'amount')
            ->with('rents')->withSum('extra_added', 'amount')->with('gst')
            ->with('service:id,name')
            ->first();


        $extra_charge_per_hour = 200;
        $extra_hours = 0;

        $start_time = strtotime($booking['booking_start_date']);
        $end_time = strtotime($booking['booking_end_date']);

        // Define extra charge period (11 PM - 8 AM)
        $night_start = strtotime(date('Y-m-d', $start_time) . ' 23:00:00');
        $morning_end = strtotime(date('Y-m-d', $start_time) . ' 08:00:00');

        // Fix: Use next day's 8 AM **only if booking crosses midnight**
        if ($start_time >= $night_start) {
            $morning_end += 86400;
        }

        $extra_added = $booking['extra_added_sum_amount'] ?? 0;
        while ($start_time < $end_time) {
            // Fix: Use AND (`&&`) instead of OR (`||`)
            if ($start_time >= $night_start || $start_time < $morning_end) {
                $extra_hours++;
            }
            $start_time = strtotime('+1 hour', $start_time);
        }
        $extra_charge = ($extra_hours > 0) ? $extra_hours * $extra_charge_per_hour : 0;
        $rents = $booking->rents;
        $rentcharge = 0;
        foreach ($rents as $r) {
            $rentcharge += $r->pivot->charge * $r->pivot->uses_hours;
        }
        $paid = $booking->transactions_sum_amount;
        $totalPaable = $booking->duration * $booking->studio_charge + $rentcharge + $extra_charge + $extra_added;
        $withgst =  $totalPaable * 1.18;
        $netPending = $withgst - $paid - floatval($booking->promo_discount_calculated);



        $isPartial = $request->isPartial;
        if ($isPartial) {
            $payment_value =  $netPending * $booking->partial_percent * 0.01;
        } else {
            $payment_value = $netPending;
        }
        $mid = env('CCA_MID');
        $working_key = env('CCA_KEY'); //Shared by CCAVENUES
        $access_code = env('CCA_AC'); //Shared by CCAVENUES
        $custom_order_id = time() . '_' . $id;
        $fdata = [
            'merchant_id' => $mid,
            'order_id' => $custom_order_id,
            'currency' => "INR",
            'amount' => $payment_value,
            'redirect_url' => route('pay_response'),
            'cancel_url' => route('pay_cancel'),
            'language' => "EN"
        ];
        $i = 0;
        $merchant_data = "";
        foreach ($fdata as $key => $value) {
            if ($i != 6) {
                $merchant_data .= $key . '=' . $value . '&';
            } else {
                $merchant_data .= $key . '=' . $value;
            }
        }
        $encrypted_data = $this->encrypt($merchant_data, $working_key);
        $tdata = [
            'transaction_date' => date('Y-m-d'),
            'type' => 'Credit',
            'amount' => $payment_value,
            'order_id' => $custom_order_id,
            'studio_id' => $booking->studio_id,
            'user_id' => $booking->user_id,
            'booking_id' => $id,
            'vendor_id' => $booking->vendor_id,
            'init_resp' => json_encode($fdata),
            'mode' => 'CCA',
            'created_at' => date('Y-m-d H:i:s')
        ];
        Transaction::insert($tdata);
        $res = compact('encrypted_data', 'working_key', 'access_code');
        return view('admin.bookings.cca_venue', $res);
    }


    public function pay_response(Request $request)
    {
        $order_id = $request->orderNo;
        $bid = explode('_', $order_id)[1];
        date_default_timezone_set('Asia/kolkata');
        $workingKey = env('CCA_KEY');
        $encResponse = $_POST['encResp'];
        $result = $this->decrypt($encResponse, $workingKey);
        $data = [];
        $status = '';
        $information = explode('&', $result);
        $dataSize = sizeof($information);
        $inner_data = [];
        for ($i = 0; $i < $dataSize; $i++) {
            $info_value = explode('=', $information[$i]);
            $inner_data[$info_value[0]] = $info_value[1];
        }
        array_push($data, $inner_data);
        $udata = [
            'ret_resp' => json_encode($inner_data),
            'status' => $data[0]["order_status"]
        ];
        Transaction::where('order_id', $order_id)->update($udata);
        if ($data[0]["order_status"] == "Success") {
            Booking::where('id', $bid)->update(['booking_status' => '1']);
            //$item =  Booking::where('id', $bid)->with('rents')->withSum('transactions', 'amount')->with('studio')->with('vendor')->with('service')->with('user')->first();

            $booking = Booking::where('id', $bid)->with('studio')
                ->with('transactions')->withSum('transactions', 'amount')
                ->with('rents')->withSum('extra_added', 'amount')->with('gst')
                ->with('service:id,name')
                ->first();

            $extra_added = $booking['extra_added_sum_amount'] ?? 0;
            $extra_charge_per_hour = 200;
            $extra_hours = 0;

            $start_time = strtotime($booking['booking_start_date']);
            $end_time = strtotime($booking['booking_end_date']);

            // Define extra charge period (11 PM - 8 AM)
            $night_start = strtotime(date('Y-m-d', $start_time) . ' 23:00:00');
            $morning_end = strtotime(date('Y-m-d', $start_time) . ' 08:00:00');

            // Fix: Use next day's 8 AM **only if booking crosses midnight**
            if ($start_time >= $night_start) {
                $morning_end += 86400;
            }


            while ($start_time < $end_time) {
                // Fix: Use AND (`&&`) instead of OR (`||`)
                if ($start_time >= $night_start || $start_time < $morning_end) {
                    $extra_hours++;
                }
                $start_time = strtotime('+1 hour', $start_time);
            }
            $extra_charge = ($extra_hours > 0) ? $extra_hours * $extra_charge_per_hour : 0;
            $rents = $booking->rents;
            $rentcharge = 0;
            foreach ($rents as $r) {
                $rentcharge += $r->pivot->charge * $r->pivot->uses_hours;
            }
            $paid = $booking->transactions_sum_amount;
            $totalPaable = $booking->duration * $booking->studio_charge + $rentcharge + $extra_charge + $extra_added;
            $withgst =  $totalPaable * 1.18;
            $netPending = $withgst - $paid - floatval($booking->promo_discount_calculated);
            $amount = $netPending;
            if (ceil($amount) <= 1) {
                Booking::where('id', $bid)->update(['payment_status' => '1', 'booking_status' => '1']);
            }
            $ndata = [
                'user_id' => $booking->user->id,
                'booking_id' => $bid,
                'studio_id' => $booking->studio_id,
                'vendor_id' => $booking->vendor_id,
                'title' => 'Payment Received',
                'message' => 'Transaction of amount ₹' . $amount,
                "is_read" => "0",
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'Payment'
            ];
            RbNotification::insert($ndata);
            $user = $booking->user;
            $appmessage  = "Your booking has been reserved with Booking ID {$bid}";
            if ($user && $user->fcm_token) {
                $this->send_notification($user->fcm_token, 'Booking Reserved', $appmessage, $user->id);
            }
        }
        $transaction = Transaction::where('order_id', $order_id)->first();
        $bitem = Booking::where('id', $bid)->first();
        $res = compact('transaction');
        $status = $data[0]["order_status"];
        return redirect()->route('success_page_response', ['type' => $status, 'id' => $order_id]);
        // return response()->json(['data' => $res, 'success' => $data[0]["order_status"]]);
        // die;
        return view('admin.bookings.success', $res);
    }
    public function pay_cancel(Request $request)
    {
        $order_id = $request->orderNo;
        $bid = explode('_', $order_id)[1];
        date_default_timezone_set('Asia/kolkata');
        $workingKey = env('CCA_KEY');
        $encResponse = $_POST['encResp'];
        $result = $this->decrypt($encResponse, $workingKey);
        $data = [];
        $status = '';
        $information = explode('&', $result);
        $dataSize = sizeof($information);
        $inner_data = [];
        for ($i = 0; $i < $dataSize; $i++) {
            $info_value = explode('=', $information[$i]);
            $inner_data[$info_value[0]] = $info_value[1];
        }
        array_push($data, $inner_data);

        $udata = [
            'ret_resp' => json_encode($inner_data),
            'status' => $data[0]["order_status"]
        ];
        Transaction::where('order_id', $order_id)->update($udata);
        $transaction = Transaction::where('order_id', $order_id)->first();
        $bitem = Booking::where('id', $bid)->first();
        $res = compact('transaction');
        return response()->json(['data' => $res, 'success' => 'Cancelled']);
        die;
        return view('admin.bookings.success', $res);
    }
    public function handlePartialPayment(Request $request, $id)
    {
        $studio = Studio::where('id', $id)->first();
        if (!$studio) {
            $message = ['message' => 'Invalid id', 'success' => '0'];
            return $request->expectsJson()
                ? response()->json($message, 404)
                : redirect()->back()->with('error', $message['error']);
        }
        $is_pp_allowed = $studio->is_pp_allowed;
        $npp = $is_pp_allowed == "0" ? "1" : "0";
        Studio::where('id', $id)->update(['is_pp_allowed' => $npp]);
        $message = ['success' => '1', "message" => 'updated successfully'];
        return $request->expectsJson()
            ? response()->json($message)
            : redirect()->back()->with('Success', $message['success']);
    }


    public function checkOrderStatus($order_id)
    {
        $working_key = "7D819C910B5DA518C0636C14A83DD434"; // Shared by CCAvenue
        $access_code = "AVDJ41KL72BL40JDLB";
        $request_string =   $order_id . "|";
        $encrypted_data = $this->encrypt($request_string, $working_key);
        $final_data = http_build_query([
            "status" => '1',
            "enc_request"    => $encrypted_data,
            "access_code"    => $access_code,
            "command"        => "orderStatusTracker",
            "request_type"   => "JSON",
            "response_type"  => "JSON"
        ]);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.ccavenue.com/apis/servlet/DoWebTrans");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $final_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return response()->json($response);
        if (!$response) {
            return null;
        }

        $decrypted_response = $this->decrypt($response, $working_key);
        return json_decode($decrypted_response, true);
    }
    public function pay_now_razorpay(Request $request, $id)
    {
        $mode = $_GET['mode'] ?? null;
        $booking = Booking::where('id', $id)->with('studio')
            ->with('transactions')->withSum('transactions', 'amount')
            ->with('rents')->withSum('extra_added', 'amount')->with('gst')
            ->with('service:id,name')->with('user')
            ->first();


        $extra_charge_per_hour = 200;
        $extra_hours = 0;

        $start_time = strtotime($booking['booking_start_date']);
        $end_time = strtotime($booking['booking_end_date']);

        // Define extra charge period (11 PM - 8 AM)
        $night_start = strtotime(date('Y-m-d', $start_time) . ' 23:00:00');
        $morning_end = strtotime(date('Y-m-d', $start_time) . ' 08:00:00');

        // Fix: Use next day's 8 AM **only if booking crosses midnight**
        if ($start_time >= $night_start) {
            $morning_end += 86400;
        }

        $extra_added = $booking['extra_added_sum_amount'] ?? 0;
        while ($start_time < $end_time) {
            // Fix: Use AND (`&&`) instead of OR (`||`)
            if ($start_time >= $night_start || $start_time < $morning_end) {
                $extra_hours++;
            }
            $start_time = strtotime('+1 hour', $start_time);
        }
        $extra_charge = ($extra_hours > 0) ? $extra_hours * $extra_charge_per_hour : 0;
        $rents = $booking->rents;
        $rentcharge = 0;
        foreach ($rents as $r) {
            $rentcharge += $r->pivot->charge * $r->pivot->uses_hours;
        }
        $paid = $booking->transactions_sum_amount;
        $totalPaable = $booking->duration * $booking->studio_charge + $rentcharge + $extra_charge + $extra_added;
        $withgst =  $totalPaable * 1.18;
        $netPending = $withgst - $paid - floatval($booking->promo_discount_calculated);
        $isPartial = $request->isPartial;

        $checkIsPPAllowdd = Studio::where('id', $booking->studio->id)->first();
        $isPPAllowed = $checkIsPPAllowdd->is_pp_allowed;
        if ($isPartial == true && $isPartial == "true") {

            $payment_value = $isPPAllowed == "1" ?   $netPending * $booking->partial_percent * 0.01 : $netPending;
        } else {

            $payment_value = $netPending;
        }
        die;
        $mid = env('CCA_MID');
        $working_key = env('CCA_KEY');
        $access_code = env('CCA_AC');
        $custom_order_id = time() . '_' . $id;
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        $order = $api->order->create([
            'receipt' => $custom_order_id,
            'amount' =>  ceil($payment_value) * 100,
            'currency' => 'INR',
        ]);
        $orderData = $order->toArray();
        // return response()->json($order);
        $tdata = [
            'transaction_date' => date('Y-m-d'),
            'type' => 'Credit',
            'amount' => $payment_value,
            'order_id' => $custom_order_id,
            'studio_id' => $booking->studio_id,
            'gateway_order_id' => $order['id'],
            'user_id' => $booking->user_id,
            'booking_id' => $id,
            'vendor_id' => $booking->vendor_id,
            'init_resp' => json_encode($order->toArray()),
            'mode' => 'Razorpay',
            'created_at' => date('Y-m-d H:i:s')
        ];
        Transaction::insert($tdata);
        $razorpay_key = env('RAZORPAY_KEY');
        $goi = $order['id'];
        $res = compact('razorpay_key', 'orderData', 'payment_value', 'booking', 'goi', 'custom_order_id');
        if ($mode) {
            return response()->json(['success' => '1', 'data' => $res, 'message' => 'Payment gateway request generated']);
        }
        return view('admin.bookings.razorpay_payment', $res);
    }
    public function paymentCallbackRazorpay(Request $request)
    {
        date_default_timezone_set('Asia/kolkata');
        $input = $request->all();
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        try {
            $attributes = [
                'razorpay_order_id' => $input['razorpay_order_id'],
                'razorpay_payment_id' => $input['razorpay_payment_id'],
                'razorpay_signature' => $input['razorpay_signature']
            ];
            $gateway_id = $input['razorpay_order_id'];
            $api->utility->verifyPaymentSignature($attributes);
            $orderData = $api->order->fetch($gateway_id);
            $transctionfound = Transaction::where('gateway_order_id', $input['razorpay_order_id'])->first();
            if ($transctionfound) {
                if ($orderData['status'] == "paid") {
                    Transaction::where('id', $transctionfound['id'])->update([
                        'status' => 'Success',
                        'ret_resp' => json_encode($orderData->toArray())
                    ]);
                    $bid = $transctionfound->booking_id;
                    Booking::where('id', $bid)->update(['booking_status' => '1']);
                    $booking = Booking::where('id', $bid)->with('studio')
                        ->with('transactions')->withSum('transactions', 'amount')
                        ->with('rents')->withSum('extra_added', 'amount')->with('gst')
                        ->with('service:id,name')
                        ->first();
                    $extra_added = $booking['extra_added_sum_amount'] ?? 0;
                    $extra_charge_per_hour = 200;
                    $extra_hours = 0;
                    $start_time = strtotime($booking['booking_start_date']);
                    $end_time = strtotime($booking['booking_end_date']);
                    $night_start = strtotime(date('Y-m-d', $start_time) . ' 23:00:00');
                    $morning_end = strtotime(date('Y-m-d', $start_time) . ' 08:00:00');
                    if ($start_time >= $night_start) {
                        $morning_end += 86400;
                    }
                    while ($start_time < $end_time) {
                        if ($start_time >= $night_start || $start_time < $morning_end) {
                            $extra_hours++;
                        }
                        $start_time = strtotime('+1 hour', $start_time);
                    }
                    $extra_charge = ($extra_hours > 0) ? $extra_hours * $extra_charge_per_hour : 0;
                    $rents = $booking->rents;
                    $rentcharge = 0;
                    foreach ($rents as $r) {
                        $rentcharge += $r->pivot->charge * $r->pivot->uses_hours;
                    }
                    $paid = $booking->transactions_sum_amount;
                    $totalPaable = $booking->duration * $booking->studio_charge + $rentcharge + $extra_charge + $extra_added;
                    $withgst =  $totalPaable * 1.18;
                    $netPending = $withgst - $paid - floatval($booking->promo_discount_calculated);
                    $amount = $netPending;
                    if (ceil($amount) <= 1) {
                        Booking::where('id', $bid)->update(['payment_status' => '1', 'booking_status' => '1']);
                    }
                    $ndata = [
                        'user_id' => $booking->user->id,
                        'booking_id' => $bid,
                        'studio_id' => $booking->studio_id,
                        'vendor_id' => $booking->vendor_id,
                        'title' => 'Payment Received',
                        'message' => 'Transaction of amount ₹' . $amount,
                        "is_read" => "0",
                        'created_at' => date('Y-m-d H:i:s'),
                        'type' => 'Payment'
                    ];
                    RbNotification::insert($ndata);
                    $user = $booking->user;
                    $appmessage  = "Your booking has been reserved with Booking ID {$bid}";
                    if ($user && $user->fcm_token) {
                        $this->send_notification($user->fcm_token, 'Booking Reserved', $appmessage, $user->id);
                    }
                }
            }
            return response()->json(['message' => 'Payment successful and verified.']);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            return response()->json(['error' => 'Signature verification failed.'], 400);
        }
    }
}
