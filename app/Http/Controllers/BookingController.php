<?php

namespace App\Http\Controllers;

use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Location\State;
use App\Models\Location\City;
use App\Models\Rent;
use App\Models\Slot;
use App\Models\Studio\Charge;
use App\Models\Studio\Studio;
use App\Models\Studio\Service;
use App\Models\Transaction;
use App\Models\User;
use App\Models\RbNotification;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Exports\BookingExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\RbTrait;


class BookingController extends Controller
{
    use RbTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        return Excel::download(new BookingExport, 'bookings.xlsx');
    }
    public static function resource_items($sid, $id)
    {
        $ritems = Rent::whereIn('id', function ($query) use ($sid) {
            $query->from('charges')->select('item_id')->where('studio_id', $sid)->where('type', 'Item');
        })->whereNotIn('id', function ($q) use ($id) {
            $q->from('booking_items')->where('booking_id', $id)->select('item_id');
        })->get();
        return $ritems;
    }
    public function custom_view($type = null)
    {
        date_default_timezone_set('Asia/kolkata');
        $vendor_id = $_GET['vendor_id'] ?? null;
        $studio_id = $_GET['studio_id'] ?? null;
        $service_id = $_GET['service_id'] ?? null;
        $bdf = $_GET['booking_date_form'] ?? null;
        $bdt = $_GET['booking_date_to'] ?? null;
        $created_by = $_GET['created_by'] ?? null;
        $duration = $_GET['duration'] ?? null;
        $payment_status = $_GET['payment_status'] ?? null;
        $booking_status = $_GET['booking_status'] ?? null;
        $approved_at = $_GET['approved_at'] ?? null;
        $booking_tenure = $_GET['tenure'] ?? null;
        $keyword = $_GET['keyword'] ?? null;
        $vid = Auth::user()->vendor_id;
        $now = date('Y-m-d H:i:s');
        
        $title = "List of Booking";
        $items = Booking::where('id', '>', '0');
        if ($type == "upcoming") {
            $items->whereDate('booking_start_date', '>', $now)->orderBy('booking_start_date', 'ASC');
        }
        if ($type == "today") {
            $items->whereDate('booking_start_date', '=', date('Y-m-d'))->orderBy('booking_start_date', 'ASC');
        }
       
        if ($type == "past") {
            $items->whereDate('booking_start_date', '<', $now)->orderBy('booking_start_date', 'ASC');
        }
        if ($vid > 0) {
            $items->where('vendor_id', $vid);
        }

        if ($keyword) {
            $items->whereIn('user_id', function ($query) use ($keyword) {
                $query->from('users')
                    ->select('id')
                    ->where(function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('mobile', 'like', "%{$keyword}%");
                    });
            });
        }
        if ($studio_id) {
            $items->where('studio_id', $studio_id);
        }
        if ($service_id) {
            $items->where('service_id', $service_id);
        }
        if ($bdf) {
            $items->whereDate('booking_start_date', '=', $bdf);
        }
        if ($bdt) {
            $items->where('booking_end_date', '<=', $bdt);
        }
        if ($created_by && $created_by == '1') {
            $items->where('created_by', '1');
        }
        if ($created_by && $created_by != '1') {
            $items->where('created_by', '!=', '1');
        }

        if ($duration) {
            $items->where('duration', $duration);
        }

        if ($payment_status) {
            $items->where('payment_status', $payment_status);
        }
        
      
        if ($approved_at == "pending") {
          
            $items->where('approved_at', null);
        }else{
            if ($booking_status) {
             $items->where('booking_status', $booking_status);
            }
            if ($booking_status == "0") {
                $items->where('booking_status', "0");
            }
        }

        if ($approved_at == "approved") {
            if ($booking_status) {
             $items->where('booking_status', $booking_status);
            }
            if ($booking_status == "0") {
                $items->where('booking_status', "0");
            }
           
            $items->where('approved_at', '!=', null);
        }
       
        $items->with('vendor')->with('service:id,name,icon,approval_required')->with('user:id,name,email,mobile')->with('studio:id,name,address');
        $items->with('rents')->with('transactions')->withSum('transactions', 'amount');
        $items->with('creater:id,name,email');
        if ($booking_tenure == "past") {
            $items->where('booking_start_date', '<', $now);
        }
        $bookings = $items->paginate(10);
        // return response()->json($bookings);
        // die;
        $stds = Studio::where('id', '>', '0');
        if ($vid > 0) {
            $stds->where('vendor_id', $vid);
        }
        $studios = $stds->get();
        $vends = DB::table('vendors');
        if ($vid) {
            $vends->where('id', $vid);
        }
        $vendors = $vends->orderBy('id', 'DESC')->get();
        $svs = Service::where('id', '>', '0');
        // if($service_id){
        //     $svs->where('id', $service_id);
        // }
        $services = $svs->get();
        $res = compact('title', 'type', 'bookings', 'keyword', 'vendors', 'vendor_id', 'studio_id', 'service_id', 'approved_at', 'booking_status', 'payment_status', 'duration', 'created_by', 'bdf', 'services', 'bdt', 'studios');
        // return response()->json($bookings);
        // die;
        if (isset($_GET['export']) && $_GET['export'] == "excel") {
            return Excel::download(new BookingExport($type), 'bookings.xlsx');
        }
        return view('admin.bookings.index', $res);
    }
    public function index()
    {
        $type = "today";
        date_default_timezone_set('Asia/kolkata');
        $vendor_id = $_GET['vendor_id'] ?? null;
        $studio_id = $_GET['studio_id'] ?? null;
        $service_id = $_GET['service_id'] ?? null;
        $bdf = $_GET['booking_date_form'] ?? null;
        $bdt = $_GET['booking_date_to'] ?? null;
        $created_by = $_GET['created_by'] ?? null;
        $duration = $_GET['duration'] ?? null;
        $payment_status = $_GET['payment_status'] ?? null;
        $booking_status = $_GET['booking_status'] ?? null;
        $approved_at = $_GET['approved_at'] ?? null;
        $booking_tenure = $_GET['tenure'] ?? null;
        $keyword = $_GET['keyword'] ?? null;
        $vid = Auth::user()->vendor_id;
        $now = date('Y-m-d H:i:s');
        $title = "List of Booking";

        $items = Booking::where('id', '>', '0');
        if ($type == "upcoming") {
            $items->whereDate('booking_start_date', '>', $now)->orderBy('booking_start_date', 'ASC');
        }
        if ($type == "today") {
            $items->whereDate('booking_start_date', '=', $now)->orderBy('booking_start_date', 'ASC');
        }
        if ($type == "past") {
            $items->whereDate('booking_start_date', '<', $now)->orderBy('booking_start_date', 'ASC');
        }
        if ($vid > 0) {
            $items->where('vendor_id', $vid);
        }

        if ($keyword) {
            $items->whereIn('user_id', function ($query) use ($keyword) {
                $query->from('users')
                    ->select('id')
                    ->where(function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('mobile', 'like', "%{$keyword}%");
                    });
            });
        }
        if ($studio_id) {
            $items->where('studio_id', $studio_id);
        }
        if ($service_id) {
            $items->where('service_id', $service_id);
        }
        if ($bdf) {
            $items->whereDate('booking_start_date', '=', $bdf);
        }
        if ($bdt) {
            $items->where('booking_end_date', '<=', $bdt);
        }
        if ($created_by && $created_by == '1') {
            $items->where('created_by', '1');
        }
        if ($created_by && $created_by != '1') {
            $items->where('created_by', '!=', '1');
        }

        if ($duration) {
            $items->where('duration', $duration);
        }

        if ($payment_status) {
            $items->where('payment_status', $payment_status);
        }

        if ($booking_status) {
            $items->where('booking_status', $booking_status);
        }
        if ($booking_status == "0") {
            $items->where('booking_status', "0");
        }

        if ($approved_at == "pending") {
            $items->where('approved_at', null);
        }

        if ($approved_at == "approved") {
            $items->where('approved_at', '!=', null);
        }
        $items->with('vendor')->with('user:id,name,email,mobile')->with('studio:id,name,address');
        $items->with('rents')->with('transactions')->withSum('transactions', 'amount');
        $items->with('creater:id,name,email');
        if ($booking_tenure == "past") {
            $items->where('booking_start_date', '<', $now);
        }
        $bookings = $items->paginate(10);
        // return response()->json($bookings);
        // die;
        $stds = Studio::where('id', '>', '0');
        if ($vid > 0) {
            $stds->where('vendor_id', $vid);
        }
        $studios = $stds->get();
        $vends = DB::table('vendors');
        if ($vid) {
            $vends->where('id', $vid);
        }
        $vendors = $vends->orderBy('id', 'DESC')->get();
        $svs = Service::where('id', '>', '0');
        // if($service_id){
        //     $svs->where('id', $service_id);
        // }
        $services = $svs->get();
        $res = compact('title', 'type', 'bookings', 'keyword', 'vendors', 'vendor_id', 'studio_id', 'service_id', 'approved_at', 'booking_status', 'payment_status', 'duration', 'created_by', 'bdf', 'services', 'bdt', 'studios');
        return view('admin.bookings.index', $res);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): \Illuminate\View\View
    {
        $vid = Auth::user()->vendor_id;
        $title = "Create New Booking";
        $items = Vendor::orderBy("business_name", "DESC");
        if ($vid > 1) {
            $items->where('id', $vid);
        }
        $vendors = $items->get();
        $states = State::where('country_id', 19)->get();
        return view("admin.bookings.create", compact("title", "vendors", "states"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        date_default_timezone_set('Asia/kolkata');
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:10|max:10',
            'start_slot' => 'required|exists:slots,id',
            'end_slot' => 'required',
            'booking_start_date' => 'required|date',
            'studio_id' => 'required|exists:studios,id',
            "service_id" => "required|exists:services,id"
        ]);
        if ($validator->fails()) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'List of Services'
            ];
            if ($request->mode) {
                return response()->json($data);
            } else {
                return redirect()->back()->with('error', 'internal error occured')->withErrors($validator)->withInput();
            }
        }
        $rents = $request->rents;
        $mobile = $request->mobile;
        $email = $request->email;
        $studio_id = $request->studio_id;
        $service_id = $request->service_id;
        $slot_id = $request->start_slot;
        $booking_start_date = $request->booking_start_date;
        $slot = Slot::where('id', $slot_id)->first();
        $b_s_date = date('Y-m-d H:i:s', strtotime($booking_start_date . '' . $slot->start_at));
        $b_e_date = $request->end_slot;
        $s_d = Carbon::parse($b_s_date)->minute(0)->second(0)->format('Y-m-d H:i:s');
        $e_d = Carbon::parse($b_e_date)->minute(0)->second(0)->format('Y-m-d H:i:s');
        $start_date = Carbon::parse($s_d);
        $end_date = Carbon::parse($e_d);

        // Calculate the duration in hours
        $duration = $end_date->diffInHours($start_date);
        if ($request->mode && $duration < 2) {
            $res = [
                "success" => '0',
                'errors' => [],
                'message' => 'Booking Creation Failed. minimum 2 hours needed to book',
                'data' => []
            ];
            return response()->json($res);
            die;
        }
        if ($s_d >= $e_d) {
            $res = [
                "success" => '0',
                'errors' => [],
                'message' => 'Booking Creation Failed. Incorrect booking dates',
                'data' => []
            ];
            return response()->json($res);
            die;
        }
        $innerBook = Booking::where('booking_start_date', '<=', $s_d)->where('booking_end_date', '>=', $e_d)->where('studio_id', $studio_id)->where('booking_status', '0')->count();
        $outerBook = Booking::where('booking_start_date', '>', $s_d)->where('booking_start_date', '<', $e_d)->where('studio_id', $studio_id)->where('booking_status', '0')->count();
        $bsum = $innerBook +  $outerBook;
        $d = Carbon::parse($b_s_date)->diffInHours(Carbon::parse($b_e_date));

        if ($bsum == 0 && $d < 25) {
            $user = User::where('mobile', $mobile)->first();
            $updata =  ['name' => $request->name, 'mobile' => $mobile, 'email' => $email, 'is_verified' => '1', 'otp_verified' => '1'];

            // if($user){
            //     User::where(['id' => $user->id])->update($updata);
            // }
            $user_id = !$user ? User::insertGetId($updata) :  $user->id;
            $studio = Studio::where('id', $studio_id)->first();
            $vendor_id = $studio['vendor_id'];
            $vendor = Vendor::where('id', $vendor_id)->first();
            $bsdate = $b_s_date;
            $bedate = $b_e_date;
            $prefix = $vendor->bill_prefix;
            $lastBill = Booking::where('vendor_id', $vendor_id)->orderBy('id', 'DESC')->first();
            $serviceStudio = DB::table('service_studios')->where('service_id', $service_id)->where('studio_id', $studio_id)->first();
            if (!$lastBill) {
                $nexbill = $prefix . '0001';
            } else {
                $bill = str_replace($prefix, '', $lastBill->bill_no);
                $nb = (int) $bill + 1;
                $bc = (int)strlen($nb);
                $zeros = 5 - $bc;
                $nexbill = $prefix . str_pad((string) $nb, 4, '0', STR_PAD_LEFT);
            }

            $bdata = [
                'user_id' => $user_id,
                'studio_id' => $studio_id,
                'vendor_id' => $vendor_id,
                'bill_no' => null,
                'booking_start_date' =>  date('Y-m-d H:0:0', strtotime($bsdate)),
                "booking_end_date" => date('Y-m-d H:0:0', strtotime($bedate)),
                "start_at" => date('H:0:0', strtotime($bsdate)),
                "end_at" => date('H:0:0', strtotime($bedate)),
                "duration" => $d,
                "service_id" => $service_id,
                "booking_status" => $request->mode ? "0" : "1",
                "studio_charge" => $serviceStudio->charge,
                'created_by' => auth('sanctum')->user()->id ?? auth()->user()->id,
                "approved_at" => $request->mode  ? $serviceStudio->is_permissable == "0" ? date('Y-m-d H:i:s') : null : date('Y-m-d H:i:s'),
                "created_at" =>  date('Y-m-d H:i:s')
            ];
            $bid = Booking::insertGetId($bdata);
            if ($request->gst && !$request->gst_id) {
                $gdata = [
                    "booking_id" => $bid,
                    "user_id" => $user_id,
                    "gst" => $request->gst,
                    "address" => $request->address,
                    "country_id" => '19',
                    "state_id" => $request->state_id,
                    "city_id" => $request->city_id,
                    "pincode" => $request->pincode,
                    "created_at" =>  date('Y-m-d H:i:s')
                ];
                $gstid = DB::table('booking_gsts')->insertGetId($gdata);
                Booking::where('id', $bid)->update(['gst_id' => $gstid]);
            }
            if ($request->gst_id) {
                Booking::where('id', $bid)->update(['gst_id' => $request->gst_id]);
            }

            if ($rents) {
                foreach ($rents as $rt) {
                    $s_rn = DB::table('charges')->where('item_id', $rt)->first();
                    $rtdata = [
                        'booking_id' => $bid,
                        'item_id' => $rt,
                        'charge' => $s_rn->charge,
                        'uses_hours' => $d
                    ];
                    DB::table('booking_items')->insert($rtdata);
                }
            }
            for ($a = 0; $a < $d; $a++) {
                $ndate = date('Y-m-d H:0:0', strtotime($bsdate) + $a * 3600);
                $ntime = date('H:0:0', strtotime($ndate));
                $nd = date('Y-m-d', strtotime($ndate));
                $nslt = Slot::where('start_at', $ntime)->first();
                $ndata = [
                    'studio_id' => $request->studio_id,
                    "booking_id" => $bid,
                    "slot_id" => $nslt['id'],
                    "bdate" => $nd,
                    "created_at" =>  date('Y-m-d H:i:s')
                ];
                BlockedSlot::insert($ndata);
            }
            $wp = User::where('id', '1')->first();
            $wt = floatval($wp->remember_token);
            $message = $request->mode ?  "Booking has been created. Please make payment otherwise your request will be cancelled within {$wt} minutes." : "Admin Created a booking";
            $appmessage = $request->mode ?  "Your booking request has been placed. Please make payment within {$wt} minutes otherwise booking will be cancelled." : "Your booking request has been created.";
            $n_tdata = [
                'user_id' => $user_id,
                'booking_id' => $bid,
                'studio_id' => $studio_id,
                'vendor_id' => $vendor_id,
                'type' => 'Booking',
                'title' => 'Booking Created ',
                "message" => $message,
                "created_at" => date('Y-m-d H:i:s')
            ];
            RbNotification::insert($n_tdata);
            if ($user && $user->fcm_token) {
                $this->send_notification($user->fcm_token, 'Booking Created', $appmessage, $user->id);
            }
            if ($request->mode) {
                $res = [
                    "success" => '1',
                    'errors' => [],
                    'message' => 'Booking Created Successfully',
                    'data' => ['booking_id' => $bid]
                ];
                return response()->json($res);
            } else {
                return redirect()->back()->with('success', 'Booking Created Successfully');
            }
        } else {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => ['booking' => 'Invalid Booking Slots'],
                'message' => 'Create bookings'
            ];
            if ($request->mode) {
                return response()->json($data);
            } else {
                return redirect()->back()->with('error', 'internal error occured')->withErrors($validator)->withInput();
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking, $id)
    {
        $booking = Booking::where('id', $id)->with('studio')->with('transactions')->withSum('transactions', 'amount')->with('rents')
            ->with('service:id,name')
            ->first();
        $paid = $booking->transactions_sum_amount;
        $studio_price =
            $rents =  $booking->rents;
        $arr = [];
        foreach ($rents as $r) {
            array_push($arr, $r->pivot->charge * $r->pivot->uses_hours);
        }
        $rentcharge = array_sum($arr);

        $booking['rents_price'] = $rentcharge;
        $booking['total_to_pay'] = ($booking->duration * $booking->studio_charge + $rentcharge) * 1.18;
        $booking['paid'] = $paid;
        $booking['net_payable'] = ($booking->duration * $booking->studio_charge + $rentcharge) * 1.18 - $paid - floatval($booking->promo_discount_calculated);
        $booking['calculation'] = ['gst' => 18, 'discount' => ['partial' => '0', 'full' => '0', 'type' => 'percent']];
        $data = [
            'data' => $booking,
            'success' => 0,
            'errors' => [],
            'message' => 'Current booking'
        ];
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        $title = "Edit Booking";
        $sdate = date('Y-m-d', strtotime($booking->booking_start_date));
        $sid = $booking->studio_id;
        $bid = $booking->id;

        $studios = Studio::where("vendor_id", $booking->vendor_id)->select(['id', 'name'])->get();
        $studio = Studio::where("id", $sid)->first();
        $slots = Slot::whereNotIn('id', function ($q) use ($sdate, $sid, $bid, $studio) {
            $q->from('blocked_slots')->where('bdate', $sdate)->select('slot_id')->where('studio_id', $sid)->where('booking_id', '!=', $bid);
        })->where('start_at', '<=', $studio->ends_at)->where('start_at', '>=', $studio->opens_at)->get();
        $user = User::find($booking->user_id);
        $services = Service::whereIn('id', function ($q) use ($sid) {
            $q->from('service_studios')->where('studio_id', $sid)->select('service_id');
        })->get();
        $dgst = DB::table('booking_gsts')->where('booking_id', $bid)->first();
        $states = State::where('country_id', 19)->get();
        $cities = City::where('state_id', $dgst?->state_id)->get();
        $res = compact('title', 'studios', 'booking', 'user', 'slots', 'dgst', 'states', 'cities', 'services');
        return view('admin.bookings.edit', $res);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Booking $booking)
    {
        date_default_timezone_set('Asia/kolkata');
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:10|max:10',
            'start_slot' => 'required',
            'end_slot' => 'required',
            'booking_start_date' => 'required|date',
            'studio_id' => 'required|exists:studios,id',
            "service_id" => "required|exists:services,id"

        ]);
        if ($validator->fails()) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'List of Services'
            ];
            if ($request->mode) {
                return response()->json($data);
            } else {
                return redirect()->back()->with('error', 'internal error occured')->withErrors($validator)->withInput();
            }
        }

        $mobile = $request->mobile;
        $email = $request->email;
        $studio_id = $request->studio_id;
        $service_id = $request->service_id;
        $slot_id = $request->start_slot;
        $booking_start_date = $request->booking_start_date;
        $slot = Slot::where('id', $slot_id)->first();

        $b_s_date = date('Y-m-d H:i:s', strtotime($booking_start_date . '' . $slot->start_at));
        $b_e_date = $request->end_slot;
        $s_d = Carbon::parse($b_s_date);
        $e_d = Carbon::parse($b_e_date);
        $d = $s_d->diffInHours($e_d);
        $user = User::where('mobile', $mobile)->first();
        if (!$user) {
            $user_id = User::insertGetId(['name' => $request->name, 'mobile' => $mobile, 'email' => $email, 'is_verified' => '1']);
        } else {
            $user_id = $user->id;
        }
        $studio = Studio::where('id', $studio_id)->first();
        $vendor_id = $studio['vendor_id'];
        $vendor = Vendor::where('id', $vendor_id)->first();
        $sts = explode(',', $request->input('slot_time'));
        $bsdate = $b_s_date;
        $bedate = $b_e_date;
        $serviceStudio = DB::table('service_studios')->where('service_id', $service_id)->where('studio_id', $studio_id)->first();
        $bdata = [
            'user_id' => $user_id,
            'studio_id' => $studio_id,
            'booking_start_date' => $bsdate,
            "booking_end_date" => $bedate,
            "start_at" => date('H:i:s', strtotime($bsdate)),
            "end_at" => date('H:i:s', strtotime($bedate)),
            "duration" => $d,
            "service_id" => $service_id,
            "studio_charge" => $serviceStudio->charge,
            "updated_at" =>  date('Y-m-d H:i:s')
        ];

        Booking::where(['id' => $booking->id])->update($bdata);
        $bid = $booking->id;
        BlockedSlot::where('booking_id', $bid)->delete();
        for ($a = 0; $a < $d; $a++) {
            $ndate = date('Y-m-d H:i:s', strtotime($bsdate) + $a * 3600);
            $ntime = date('H:i:s', strtotime($ndate));
            $nd = date('Y-m-d', strtotime($ndate));
            $nslt = Slot::where('start_at', $ntime)->first();
            $ndata = [
                'studio_id' => $request->studio_id,
                "booking_id" => $bid,
                "slot_id" => $nslt['id'],
                "bdate" => $nd,
                "created_at" =>  date('Y-m-d H:i:s')
            ];
            BlockedSlot::insert($ndata);
        }

        if ($request->gst) {
            DB::table('booking_gsts')->where('booking_id', $bid)->delete();
            $gdata = [
                "booking_id" => $bid,
                "user_id" => $user_id,
                "gst" => $request->gst,
                "address" => $request->address,
                "country_id" => '19',
                "state_id" => $request->state_id,
                "city_id" => $request->city_id,
                "pincode" => $request->pincode,
                "created_at" =>  date('Y-m-d H:i:s')
            ];

            DB::table('booking_gsts')->insert($gdata);
        }
        if ($user && $user->fcm_token) {
            $this->send_notification($user->fcm_token, 'Booking Rescheduled', 'Your booking request has been rescheduled. Please make payment if any due.', $user->id);
        }
        if ($request->mode) {
            $res = [
                "success" => '1',
                'errors' => [],
                'message' => 'Booking updated Successfully',
                'data' => []
            ];
            return response()->json($res);
        } else {
            return redirect()->back()->with('success', 'Booking updated Successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Booking $booking)
    {
        Booking::where('id', $booking->id)->update(['booking_status' => '2']);
        BlockedSlot::where('booking_id', $booking->id)->delete();
        return redirect()->back()->with('success', 'Booking Cancelled');
    }
    public function cron_destroy_booking()
    {
        date_default_timezone_set('Asia/kolkata');
        $wp = User::where('id', '1')->first();
        $wt = floatval($wp->remember_token);
        $thirtyMinutesAgo = Carbon::now()->subMinutes($wt)->format('Y-m-d H:i:s');
        $bookings = Booking::where('booking_status', '0')->where('created_at', '<', $thirtyMinutesAgo)->get();
        foreach ($bookings as $booking) {
            $booking->update(['booking_status' => '2']);
            BlockedSlot::where('booking_id', $booking->id)->delete();
        }
        return true;
    }
    public function discount(Request $request)
    {
        $request->validate([
            'booking_id' => 'required',
            'discount' => 'required|numeric'
        ]);
        $bid = $request->booking_id;
        $data = [
            'discount' => $request->discount
        ];
        if (Booking::where('id', $bid)->update($data)) {
            return redirect()->back()->with('success', 'Discount Added Successfully');
        }
    }
    public function generate_bill($id)
    {
        $booking = Booking::where('id', $id)->with('gst')->first();
        $studio = Studio::where('vendor_id', $booking->vendor_id)
            ->with('country')->with('state')->with('district')
            ->first();
        // return response()->json($studio);
        // die;
        $sid = $studio->id;
        $trans = Transaction::where('booking_id', $id)->where('status', 'Success')->get();
        $user = User::where('id', $booking->user_id)->first();
        $items = BookingItem::with('rents')->where('booking_id', $id)->get();

        $title = "Generate Bill";
        $ritems = Rent::whereIn('id', function ($query) use ($sid) {
            $query->from('charges')->select('item_id')->where('studio_id', $sid)->where('type', 'Item');
        })->whereNotIn('id', function ($q) use ($id) {
            $q->from('booking_items')->where('booking_id', $id)->select('item_id');
        })->get();
        $bstatus = ['0' => 'Pending', '1' => 'Confirmed', '2' => 'Cancelled'];
        $pstatus = ['0' => 'Unpaid', '1' => 'Paid', '2' => 'Refunded'];
        $res = compact('title', 'items', 'studio', 'booking', 'user', 'ritems', 'trans', 'bstatus', 'pstatus');
        // return response()->json($booking);
        // die;
        return view('admin.bookings.bill', $res);
    }
    public function download_bill(Request $request, $id)
    {
        $booking = Booking::find($id);
        $studio = Studio::where('vendor_id', $booking->vendor_id)
            ->with('country')->with('state')->with('district')
            ->first();
        $sid = $studio->id;
        // return response()->json($studio);
        // die;
        $user = User::where('id', $booking->user_id)->first();
        $items = BookingItem::with('rents')->where('booking_id', $id)->get();

        $title = "Generate Bill";

        $ritems = Rent::whereIn('id', function ($query) use ($sid) {
            $query->from('charges')->select('item_id')->where('studio_id', $sid)->where('type', 'Item');
        })->whereNotIn('id', function ($q) use ($id) {
            $q->from('booking_items')->where('booking_id', $id)->select('item_id');
        })->get();
        $res = compact('title', 'items', 'studio', 'booking', 'user', 'ritems');
        $pdf = Pdf::loadView('admin.bookings.d-bill', $res)->setOptions(['defaultFont' => 'sans-serif']);

        return $pdf->download('invoice.pdf');
    }
    public function booking_item_add(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
            'uses_hours' => 'required',
            'booking_id' => 'required',
            'studio_id' => 'required'
        ]);
        $charge = Charge::where('studio_id', $request->studio_id)->where('item_id', $request->item_id)->first();

        $data = [
            'item_id' => $request->item_id,
            'booking_id' => $request->booking_id,
            'charge' => $charge->charge,
            'uses_hours' => $request->uses_hours,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (BookingItem::insert($data)) {
            return redirect()->back();
        }
    }
    public function booking_item_delete($id)
    {
        BookingItem::where('id', $id)->delete();
        return redirect()->back();
    }
    public function approve_booking($id)
    {
        date_default_timezone_set('Asia/kolkata');
        $item = Booking::where('id', $id)->first();
        $user = User::where('id', $item->user_id)->first();
        Booking::where('id', $id)->update(['approved_at' => date('Y-m-d H:i:s')]);
        BlockedSlot::where('booking_id', $id)->delete();
        if ($user && $user->fcm_token) {
            $this->send_notification($user->fcm_token, 'Booking Approved', 'Your booking request has been approved. Please make payment', $item->user_id);
        }

        return redirect()->back()->with('success', 'Approved Successfully');
    }
}
