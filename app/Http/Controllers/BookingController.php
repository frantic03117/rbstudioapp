<?php

namespace App\Http\Controllers;

use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\ServiceStudio;
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
    public function custom_view(Request $request, $type = null)
    {
        date_default_timezone_set('Asia/kolkata');
        $vendor_id = $_GET['vendor_id'] ?? null;
        $studio_id = $_GET['studio_id'] ?? null;
        $service_id = $_GET['service_id'] ?? null;
        $bdf = $_GET['booking_date_form'] ?? null;
        $bdt = $_GET['booking_date_to'] ?? null;
        $booking_id = $_GET['booking_id'] ?? null;
        $created_by = $_GET['created_by'] ?? null;
        $duration = $_GET['duration'] ?? null;
        $payment_status = $_GET['payment_status'] ?? null;
        $booking_status = $_GET['booking_status'] ?? null;
        $approved_at = $_GET['approved_at'] ?? null;
        $booking_tenure = $_GET['tenure'] ?? null;
        $keyword = $_GET['keyword'] ?? null;
        $vid = Auth::user()->vendor_id;
        $payment_filter = $_GET['payment_filter'] ?? null;
        $now = date('Y-m-d H:i:s');
        $title = "List of Booking";
        $items = Booking::where('id', '>', '0');
        if ($booking_id) {
            $items->where('id', $booking_id);
        } else {
            if ($type == "upcoming") {
                $items->whereDate('booking_start_date', '>=', date('Y-m-d'))->orderBy('booking_start_date', 'ASC');
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
                $items->where(function ($query) use ($keyword) {
                    // Match related user
                    $query->whereIn('user_id', function ($sub) use ($keyword) {
                        $sub->from('users')
                            ->select('id')
                            ->where(function ($q) use ($keyword) {
                                $q->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%")
                                    ->orWhere('mobile', 'like', "%{$keyword}%");
                            });
                    })

                        ->orWhere('id', 'LIKE', "%{$keyword}%");

                    // OR match by studio name
                    $findstudios = Studio::where('name', 'like', "%{$keyword}%")->pluck('id');
                    if ($findstudios->count()) {
                        $query->orWhereIn('studio_id', $findstudios);
                    }

                    // OR match by service name
                    $findServices = Service::where('name', 'like', "%{$keyword}%")->pluck('id');
                    if ($findServices->count()) {
                        $query->orWhereIn('service_id', $findServices);
                    }
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
            }
            if (in_array($booking_status, ['0', '1', '2'])) {
                $items->where('booking_status', $booking_status);
            }
            if ($approved_at == "approved") {
                // if ($booking_status) {
                //     $items->where('booking_status', $booking_status);
                // }
                // if ($booking_status == "0") {
                //     $items->where('booking_status', "0");
                // }

                $items->where('approved_at', '!=', null);
            }
        }
        $items->with('vendor')->with('service:id,name,icon,approval_required')->with('user:id,name,email,mobile')->with('studio:id,name,mobile,address,opens_at,ends_at');
        $items->with('rents')->with('extra_added')->withSum('extra_added', 'amount')->with('transactions')->withSum('transactions', 'amount');
        $items->with('creater:id,name,email');
        if ($booking_tenure == "past") {
            $items->where('booking_start_date', '<', $now);
        }
        $extra_charge_per_hour = 200;
        $bookings = $items->paginate(10)->appends(request()->query());
        // return response()->json($bookings);
        // die;
        $bookings->getCollection()->transform(
            function ($b) use ($extra_charge_per_hour) {

                $extra_hours = 0;

                $start_time = strtotime($b['booking_start_date']);
                $end_time = strtotime($b['booking_end_date']);

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



                // Apply extra charge only if extra hours exist
                $extra_charge = ($extra_hours > 0) ? $extra_hours * $extra_charge_per_hour : 0;

                // Base amount
                $base_amount = $b['duration'] * $b['studio_charge'];
                $extra_added = $b['extra_added_sum_amount'];
                $rents = $b->rents;
                $rent_charge = 0;
                foreach ($rents as $r) {
                    $rent_charge += $r->pivot->charge * $r->pivot->uses_hours;
                }
                $b['rent_charges'] = $rent_charge;
                // Final total calculation including GST (18%)
                $total_amount = ($base_amount + $extra_charge + $extra_added + $rent_charge) * 1.18;
                $b['extra_charge'] = $extra_charge;
                // Add the calculated total to the booking object
                $b['total_amount'] = round($total_amount, 2);

                return $b;
            }
        );
        if ($payment_filter) {
            $bookings->setCollection(
                $bookings->getCollection()->filter(function ($b) use ($payment_filter) {
                    $paid = round($b['transactions_sum_amount'] ?? 0, 2);
                    $total = round($b['total_amount'] ?? 0, 2);

                    if ($payment_filter === 'paid') {
                        return $paid >= $total;
                    } elseif ($payment_filter === 'partial') {
                        return $paid > 0 && $paid < $total;
                    } elseif ($payment_filter === 'unpaid') {
                        return $paid <= 0;
                    }
                    return true;
                })->values()
            );
        }
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
        $services = $svs->get();
        $res = compact('title', 'type', 'bookings', 'keyword', 'vendors', 'vendor_id', 'studio_id', 'service_id', 'approved_at', 'booking_status', 'payment_status', 'duration', 'created_by', 'bdf', 'services', 'bdt', 'studios', 'payment_filter');

        if ($request->expectsJson()) {
            return response()->json(['data' => $bookings, 'success' => 1, 'message' => $title]);
        }

        // die;

        if (isset($_GET['export']) && $_GET['export'] == "excel") {
            return Excel::download(new BookingExport($type), 'bookings.xlsx');
        }
        // return response()->json($res);
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
        $items->with('vendor')->with('user:id,name,email,mobile')->with('studio:id,name,mobile,address');
        $items->with('rents')->withSum('extra_added', 'amount')->with('transactions')->withSum('transactions', 'amount');
        $items->with('creater:id,name,email');
        if ($booking_tenure == "past") {
            $items->where('booking_start_date', '<', $now);
        }
        $bookings = $items->paginate(10)->appends(request()->query());
        $extra_charge_per_hour = 200;
        $bookings->getCollection()->transform(
            function ($b) use ($extra_charge_per_hour) {

                $extra_hours = 0;

                $start_time = strtotime($b['booking_start_date']);
                $end_time = strtotime($b['booking_end_date']);

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


                // Apply extra charge only if extra hours exist
                $extra_charge = ($extra_hours > 0) ? $extra_hours * $extra_charge_per_hour : 0;

                // Base amount
                $base_amount = $b['duration'] * $b['studio_charge'];
                $extra_added = $b['extra_added_sum_amount'];
                // Final total calculation including GST (18%)
                $total_amount = ($base_amount + $extra_charge +  $extra_added) * 1.18;
                $b['extra_charge'] = $extra_charge;
                // Add the calculated total to the booking object
                $b['total_amount'] = round($total_amount, 2);
                $rents = $b->rents;
                $rent_charge = 0;
                foreach ($rents as $r) {
                    $rent_charge += $r->pivot->charge * $r->pivot->uses_hours;
                }
                $b['rent_charges'] = $rent_charge;
                return $b;
            }
        );
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
    public function confirm_booking($id)
    {
        Booking::where('id', $id)->update(['booking_status' => '1', 'approved_at' => date('Y-m-d H:i:s')]);
        $booking =  Booking::where('id', $id)->first();
        $user = User::where('id', $booking->user_id)->first();
        $msg = "Your booking has been reserved with Booking ID {$id}. You can view the details anytime in the Bookings Tab";
        $udata = [
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'studio_id' => $booking->studio_id,
            'vendor_id' => $booking->vendor_id,
            'type' => 'Booking',
            'title' => 'Booking Reserved',
            'message' => $msg
        ];
        RbNotification::insert($udata);
        if ($user->fcm_token) {
            $this->send_notification($user->fcm_token, 'Booking Reserved', $msg, $user->id);
        }
        return redirect()->back()->with('success', 'Booking Reserved successfully');
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
        $creatorRole = auth('sanctum')->user() ? auth('sanctum')->user()->role : auth()->user()->role;
        // Calculate the duration in hours
        $duration = $end_date->diffInHours($start_date);
        if ($creatorRole == "User" && $duration < 2) {
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
        $innerBook = Booking::where('booking_start_date', '<=', $s_d)->where('booking_end_date', '>=', $e_d)->where('studio_id', $studio_id)->whereIn('booking_status', ['1', '0'])->count();
        $outerBook = Booking::where('booking_start_date', '>', $s_d)->where('booking_start_date', '<', $e_d)->where('studio_id', $studio_id)->whereIn('booking_status', ['1', '0'])->count();
        $overlappingBookings = Booking::where('studio_id', $studio_id)
            ->whereIn('booking_status', ['1', '0'])
            ->where(function ($query) use ($s_d, $e_d) {
                $query->where(function ($q) use ($s_d, $e_d) {
                    $q->where('booking_start_date', '<', $e_d) // Overlaps before the new end time
                        ->where('booking_end_date', '>', $s_d); // Overlaps after the new start time
                });
            })
            ->count();
        $bsum = $innerBook +  $outerBook + $overlappingBookings;
        $d = Carbon::parse($b_s_date)->diffInHours(Carbon::parse($b_e_date));
        if ($overlappingBookings > 0) {
            $res = [
                "success" => '0',
                'errors' => [],
                'message' => 'Booking Creation Failed. Incorrect booking dates',
                'data' => []
            ];
            return response()->json($res);
        }
        if ($bsum == 0 && $d < 25) {
            $user = User::where('mobile', $mobile)->first();
            $updata =  ['mobile' => $mobile, 'email' => $email, 'is_verified' => '1', 'otp_verified' => '1'];
            if ($request->name) {
                $updata['name'] = $request->name;
            }
            if ($email) {
                $updata['email'] = $email;
            }
            if ($mobile) {
                $updata['email'] = $mobile;
            }
            $user_id = !$user ? User::insertGetId($updata) :  $user->id;
            $studio = Studio::where('id', $studio_id)->first();
            $vendor_id = $studio['vendor_id'];
            $bsdate = $b_s_date;
            $bedate = $b_e_date;
            $serviceStudio = DB::table('service_studios')->where('service_id', $service_id)->where('studio_id', $studio_id)->first();
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
                "booking_status" =>  $creatorRole == "User" ? "0" : "1",
                "studio_charge" => $serviceStudio->charge,
                'created_by' => auth('sanctum')->user()->id ?? auth()->user()->id,
                "created_at" =>  date('Y-m-d H:i:s')
            ];

            if ($creatorRole == "User") {
                $bdata['approved_at'] = $serviceStudio->is_permissable == "0" ? date('Y-m-d H:i:s') : null;
            } else {
                $bdata['approved_at'] = date('Y-m-d H:i:s');
            }
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
            $message =   $serviceStudio->is_permissable ? "Your booking request is pending for approval. You can track it under the Bookings Tab or contact us for assistance. " : "Please complete the payment within 2 hours to secure your booking. Otherwise, it will be automatically cancelled.";
            $appmessage =  $message;

            $n_tdata = [
                'user_id' => $user_id,
                'booking_id' => $bid,
                'studio_id' => $studio_id,
                'vendor_id' => $vendor_id,
                'type' => 'Booking',
                'title' =>  $serviceStudio->is_permissable ? 'Booking In Progress' : 'Booking Received',
                "message" => $message,
                "created_at" => date('Y-m-d H:i:s')
            ];
            RbNotification::insert($n_tdata);
            if ($user && $user->fcm_token) {
                if ($serviceStudio->is_permissable) {
                    $appmessage =  "A new booking request is waiting for your approval. Review it now in the Bookings Tab.";
                }
                $this->send_notification($user->fcm_token, $serviceStudio->is_permissable ? 'Booking In Progress' : 'Booking Received', $appmessage, $user->id);
            }
            $super = User::where('role', 'Super')->first();
            if ($super && $super?->fcm_token) {
                $this->send_notification($super?->fcm_token, $serviceStudio->is_permissable ? 'Booking Pending for approval' : 'New Booking Received', $appmessage, $super->id);
            }
            if ($request->mode || $request->expectsJson()) {
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
                'errors' => 'Invalid booking slots, try another slot.',
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
        $booking = Booking::where('id', $id)
            ->with('user')
            ->with('vendor')
            ->with('studio')->with('transactions')
            ->withSum('transactions', 'amount')
            ->withSum('extra_added', 'amount')
            ->with('rents')->with('gst')
            ->with('service:id,name')
            ->first();
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
        $rent_charge = 0;
        foreach ($rents as $r) {
            $rent_charge += $r->pivot->charge * $r->pivot->uses_hours;
        }
        $paid = $booking->transactions_sum_amount;
        $rents =  $booking->rents;
        $booking['rents_price'] = $rent_charge;
        $booking['extra_charge'] = $extra_charge;
        $totalPaable = $booking->duration * $booking->studio_charge + $rent_charge + $extra_charge + $booking['extra_added_sum_amount'];
        $withgst = $totalPaable * 1.18;
        $booking['total_to_pay'] = $withgst;
        $booking['paid'] = $paid;
        $booking['net_payable'] = $withgst  - ($paid + $booking->promo_discount_calculated);
        $booking['calculation'] = ['gst' => 18, 'discount' => ['partial' => '0', 'full' => '0', 'type' => 'percent']];
        $data = [
            'data' => $booking,
            'success' => 1,
            'errors' => [],
            'message' => 'Current booking'
        ];
        return response()->json($data);
    }
    public function pre_booking_details(Request $request)
    {
        $request->validate([
            'studio_id' => 'required|exists:studios,id',
            'service_id' => 'required|exists:services,id',
            'start_slot' => 'required',
            'end_time' => 'required',
            'start_time' => 'required'
        ]);
        $paid = 0;
        $booking = [];

        $rentcharge = 0;

        $endtime = $request->end_time;
        $studio_id = $request->studio_id;
        $fslot = Slot::where('id', $request->start_slot)->first();
        $ftime = $fslot->start_at;
        $start_date = date('Y-m-d', strtotime($request->start_time));
        $f_time = date('H:i:s', strtotime($ftime));
        $starttime = $start_date . ' ' . $f_time;
        $studio = Studio::where('id', $studio_id)->with('vendor')->with('images')->first();
        $service_id = $request->service_id;
        $service = Service::where('id', $service_id)->first();
        $service_charge = ServiceStudio::where(['service_id' => $service_id, 'studio_id' => $studio_id])->first();
        $starttime_c = Carbon::parse($starttime);
        $endtime_c = Carbon::parse($request->end_time);
        $e_d = Carbon::parse($endtime_c)->minute(0)->second(0)->format('Y-m-d H:i:s');
        $durationInHours = $starttime_c->diffInHours($endtime_c);

        $formatedstarttime = Carbon::parse($starttime)->minute(0)->second(0)->format('Y-m-d H:i:s');
        $extra_hours = 0;
        $extra_charge_per_hour = 200;
        $start_time = strtotime($formatedstarttime);
        $end_time = strtotime($e_d);

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



        // Apply extra charge only if extra hours exist
        $extra_charge = ($extra_hours > 0) ? $extra_hours * $extra_charge_per_hour : 0;

        // Base amount
        $base_amount = $durationInHours * $service_charge->charge;

        // Final total calculation including GST (18%)
        $total_amount = ($base_amount + $extra_charge) * 1.18;





        if ($request->items) {
            $itemids = $request->items;
            $totalCharges = DB::table('charges')
                ->whereIn('item_id', $itemids)
                ->where('studio_id', $studio_id)
                ->sum('charge');
            $rentcharge  = floatval($totalCharges) * floatval($durationInHours);
        }
        $booking['rents_price'] = $rentcharge;
        $totalpayable =  ($durationInHours * $service_charge->charge + $rentcharge + $extra_charge) * 1.18;
        $booking['total_to_pay'] = $totalpayable;
        $booking['paid'] = $paid;
        $booking['net_payable'] = $totalpayable - $paid - 0;
        $booking['calculation'] = ['gst' => 18, 'discount' => ['partial' => '0', 'full' => '0', 'type' => 'percent']];

        $data = [
            'data' => $booking,
            'studio' => $studio,
            'extra_charge' => $extra_charge,
            'start_time' => Carbon::parse($starttime)->minute(0)->second(0)->format('Y-m-d H:i:s'),
            'end_time' => $e_d,
            'service' => $service,
            'service_charge' => $service_charge,
            'duration' =>  $durationInHours,
            'success' => 1,
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
    public function update(Request $request, $id)
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
        $booking = Booking::where('id', $id)->first();
        if ($validator->fails()) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'List of Services'
            ];
            if ($request->mode || $request->expectsJson()) {
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
        $msg  = "Your booking has been successfully rescheduled. Check the updated details in the Bookings Tab.";
        // $msg  = "Booking Received New booking request has been submitted. Check the Bookings Tab to review.";
        $ndata = [
            'user_id' => $user_id,
            'booking_id' => $bid,
            'studio_id' => $studio_id,
            'vendor_id' => $studio['vendor_id'],
            'title' => 'Booking Rescheduled',
            'message' => $msg,
            "is_read" => "0",
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 'Booking'
        ];
        DB::table('notifications')->insert($ndata);
        if ($user && $user->fcm_token) {

            $this->send_notification($user->fcm_token, 'Booking Rescheduled', $msg, $user->id);
        }
        $super = User::where('role', 'Super')->first();
        if ($super && $super?->fcm_token) {
            $appmessage = "A booking has been rescheduled. View the updated details in the Bookings Tab.";
            $this->send_notification($super?->fcm_token, "Booking Rescheduled", $appmessage, $super->id);
        }
        if ($request->mode || $request->expectsJson()) {
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
    public function destroy(Request $request, Booking $booking, $id)
    {
        $bid = $booking->id ?? $id;
        $booking =  Booking::where('id', $bid)->first();
        if (!$booking) {
            return response()->json(['success' => 0, "message" => 'Booking not found'], 400);
        }
        $user = User::where('id', $booking->user_id)->first();
        $msg = "Your booking ID {$bid} has been cancelled. View details in the Bookings tab or contact us for assistance.";
        // $msg = "Hello {$user->name}, on {$booking->booking_start_date} has been cancelled. Hope to see you again at the studio. Thanks R AND B STUDIOS";
        if ($user->fcm_token) {
            $this->send_notification($user->fcm_token, 'Booking Cancelled', $msg, $user->id, 'Booking Cancelled');
        }
        $super = User::where('role', 'Super')->first();
        if ($super && $super?->fcm_token) {
            $appmessage = "A booking ID {$bid} has been cancelled. View details in the Bookings tab and notify the client";
            $this->send_notification($super?->fcm_token, "Booking Cancelled", $appmessage, $super->id);
        }
        $udata = [
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'studio_id' => $booking->studio_id,
            'vendor_id' => $booking->vendor_id,
            'type' => 'Booking',
            'title' => 'Booking Cancelled',
            'message' => $msg
        ];
        RbNotification::insert($udata);
        Booking::where('id', $booking->id)->update(['booking_status' => '2']);
        BlockedSlot::where('booking_id', $booking->id)->delete();
        if ($request->expectsJson()) {
            return response()->json(['success' => 1, "message" => 'Booking cancelled successfully']);
        }

        return redirect()->back()->with('success', 'Booking Cancelled');
    }
    public function cron_destroy_booking()
    {
        date_default_timezone_set('Asia/kolkata');
        $wp = User::where('id', '1')->first();
        $wt = floatval($wp->remember_token) ?? 120;
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
        $booking = Booking::where('id', $id)->withSum('extra_added', 'amount')->with('gst')->first();
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
        $validated = $request->validate([
            'item_id' => 'required|exists:rents,id',
            'uses_hours' => 'required|numeric|min:0.1',
            'booking_id' => 'required|exists:bookings,id',
            'studio_id' => 'required|exists:studios,id',
        ]);
        $charge = Charge::where('studio_id', $validated['studio_id'])
            ->where('item_id', $validated['item_id'])
            ->first();

        if (!$charge) {
            return response()->json(['success' => 0, 'message' => 'Charge not found for the given item and studio'], 400);
        }
        $data = [
            'item_id' => $request->item_id,
            'booking_id' => $request->booking_id,
            'charge' => $charge->charge,
            'uses_hours' => $request->uses_hours,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (BookingItem::insert($data)) {
            $resp = ['success' => 1, 'message' => 'Booking item added successfully'];
            if ($request->expectsJson()) {
                return response()->json($resp);
            }
            return redirect()->back();
        }
    }
    public function booking_item_delete($id)
    {
        BookingItem::where('id', $id)->delete();
        return redirect()->back();
    }
    public function approve_booking(Request $request, $id)
    {
        date_default_timezone_set('Asia/Kolkata');

        $booking = Booking::where('id', $id)->first();
        if (!$booking) {
            $response = ['success' => 0, "message" => "Booking not found"];
            return $request->wantsJson() ? response()->json($response, 400) : redirect()->back()->with('error', 'Booking not found');
        }

        $user = User::where('id', $booking->user_id)->first();

        // $msg = "Your booking with ID {$id} has been approved. You can now proceed with the payment to confirm your reservation within 2 Hours. Otherwise, it will be automatically canceled.";
        $msg  = "Booking Approved Your booking ID {$id} has been approved. Please complete the payment to confirm. Unpaid bookings will be automatically cancelled.";

        $udata = [
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'studio_id' => $booking->studio_id,
            'vendor_id' => $booking->vendor_id,
            'type' => 'Booking',
            'title' => 'Booking Approved',
            'message' => $msg
        ];

        RbNotification::insert($udata);
        Booking::where('id', $id)->update(['approved_at' => date('Y-m-d H:i:s')]);
        BlockedSlot::where('booking_id', $id)->delete();

        if ($user && $user->fcm_token) {
            $this->send_notification($user->fcm_token, $msg, $booking->user_id);
        }

        $response = ['success' => 1, "message" => "Approved Successfully"];

        return $request->wantsJson() ? response()->json($response) : redirect()->back()->with('success', 'Approved Successfully');
    }
    public function rebook($id)
    {
        $booking =  Booking::where('id', $id)
            ->with('user')->with('studio')->with('service')
            ->with('gst')->with('vendor')->with('rents')->first();
        // return response()->json($booking);
        // die;
        $title = "Duplicate Booking";
        $vendors = Vendor::where('id', $booking->vendor->id)->get();

        $states = State::where('country_id', 19)->get();
        $cities = City::where('state_id', $booking?->gst?->state_id)->get();
        $studios = Studio::where('id', $booking->studio->id)->get();
        $services = Service::where('id', $booking?->service->id)->get();
        $res = compact('booking', 'title', 'vendors', 'states', 'studios', 'services', 'cities');

        return view('admin.bookings.copy-booking', $res);
    }
}
