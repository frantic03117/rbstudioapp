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
    public function index()
    {
        $title = "Create New Studio";
        $stds = Studio::with("vendor");
        if (Auth::user()->role != "Super") {
            $stds->where('vendor_id', Auth::user()->vendor_id);
        }
        $studio = $stds->with('country')->with('state')->with('district')->with('images')
            ->with('products')
            ->with('charges')
            ->get();
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
            'address' => $request->address,
            'state_id' => $request->state_id,
            'country_id' => $request->country_id,
            'district_id' => $request->district_id,
            'city' => $request->city,
            'pincode' => $request->pincode,
            'google_map' => $request->google_map,
            'description' => $request->description,
            'terms' => $request->terms,
            'opens_at' => $request->opens_at,
            'ends_at' => $request->ends_at,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'created_at' => date('Y-m-d H:i:s')
        ];
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
            'terms' => $request->terms,
            'color' => $request->color,
            'opens_at' => $request->opens_at,
            'ends_at' => $request->ends_at,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'updated_At' => date('Y-m-d H:i:s')
        ];
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
        $item =  Booking::where('id', $id)->where('approved_at', '!=', null)->with('rents')->withSum('transactions', 'amount')->with('studio')->with('vendor')->with('service')->with('user')->first();
        $rents =  $item->rents;
        $arr = [];
        foreach ($rents as $r) {
            array_push($arr, $r->pivot->charge * $r->pivot->uses_hours);
        }
        $isPartial = $request->isPartial;
        $rentcharge = array_sum($arr);
        $amount =  ($item->duration * $item->studio_charge + $rentcharge) * 1.18 - $item->transactions_sum_amount - floatval($item->promo_discount_calculated);

        if ($isPartial) {
            $payment_value =  $amount * $item->partial_percent * 0.01;
        } else {
            $payment_value = $amount;
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
            'studio_id' => $item->studio_id,
            'user_id' => $item->user_id,
            'booking_id' => $id,
            'vendor_id' => $item->vendor_id,
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

            $item =  Booking::where('id', $bid)->with('rents')->withSum('transactions', 'amount')->with('studio')->with('vendor')->with('service')->with('user')->first();
            $rents =  $item->rents;
            $arr = [];
            foreach ($rents as $r) {
                array_push($arr, $r->pivot->charge * $r->pivot->uses_hours);
            }
            $rentcharge = array_sum($arr);
            $discount = floatval($item->promo_discount_calculated);
            $amount =  $item->duration * $item->studio_charge + $rentcharge - $item->transactions_sum_amount - $discount;
            Booking::where('id', $bid)->update(['booking_status' => '1']);
            if (ceil($amount) <= 1) {
                Booking::where('id', $bid)->update(['payment_status' => '1', 'booking_status' => '1']);
            }
            $ndata = [
                'user_id' => $item->user->id,
                'booking_id' => $bid,
                'studio_id' => $item->studio_id,
                'vendor_id' => $item->vendor_id,
                'title' => 'Payment Received',
                'message' => 'Transaction of amount ₹' . $amount,
                "is_read" => "0",
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'Payment'
            ];
            RbNotification::insert($ndata);
            $user = $item->user;
            $appmessage  = "Your booking has been successfully created. Your payment has been received.";
            if ($user && $user->fcm_token) {
                $this->send_notification($user->fcm_token, 'Booking Created', $appmessage, $user->id);
            }
        }
        $transaction = Transaction::where('order_id', $order_id)->first();
        $bitem = Booking::where('id', $bid)->first();
        $res = compact('transaction');
        return response()->json(['data' => $res, 'success' => $data[0]["order_status"]]);
        die;
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
}