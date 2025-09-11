<?php

namespace App\Http\Controllers;

use App\Models\BlockedSlot;
use App\Models\Studio\Service;
use App\Models\Booking;
use App\Models\RbNotification;
use App\Models\User;
use App\Models\Faq;
use App\Models\Studio\Studio;
use App\Models\Location\City;
use App\Models\Location\Country;
use App\Traits\RbTrait;
use App\Models\Location\State;
use App\Models\Setting;
use App\Models\Slot;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;


class ApiController extends Controller
{
    use RbTrait;
    public function update_fcm(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);
        if ($validator->fails()) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first()
            ];
            return response()->json($data);
        }
        $data = [
            'fcm_token' => $request->token
        ];
        if (User::where('id',  auth('sanctum')->user()->id)->update($data)) {
            $data = [
                'data' => [],
                'success' => 1,
                'errors' => $validator->errors(),
                'message' => 'FCM token updated'
            ];
            return response()->json($data);
        } else {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'FCM token failed'
            ];
            return response()->json($data);
        }
    }

    public function services()
    {
        $items = Service::all();
        $data = [
            'data' => $items,
            'success' => 1,
            'errors' => [],
            'message' => 'List of Services'
        ];
        return response()->json($data);
    }
    public function studios(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'service_id' => 'required',
        // ]);
        // if ($validator->fails()) {
        //     $data = [
        //         'data' => [],
        //         'success' => 0,
        //         'errors' => $validator->errors(),
        //         'message' => 'List of Services'
        //     ];
        //     return response()->json($data);
        // }
        $sid = $request->service_id;

        $studios = Studio::with('country:id,country')->with('state:id,state')->with('district:id,city')->with('images')->with('vendor')
            ->with('products');
        if ($sid) {
            $studios->with(['charges' => function ($q) use ($sid) {
                $q->where('service_id', $sid);
            }])->whereIn('id', function ($q) use ($sid) {
                $q->from('service_studios')->where('service_id', $sid)->select('studio_id');
            });
        } else {
            $studios->with('charges');
        }

        $items = $studios->with('services')->get();
        $data = [
            'data' => $items,
            'success' => 1,
            // 'errors' => $validator->errors(),
            'message' => 'List of Studios'
        ];
        return response()->json($data);
    }
    public function countries()
    {
        $items = Country::orderBy('country')->where('id', '19')->get();
        $data = [
            'data' => $items,
            'success' => 1,
            'errors' => [],
            'message' => 'List of Countries'
        ];
        return response()->json($data);
    }
    public function states($id)
    {
        $items = State::where(['country_id' => $id])->orderBy('state')->get();
        $data = [
            'data' => $items,
            'success' => 1,
            'errors' => [],
            'message' => 'List of States'
        ];
        return response()->json($data);
    }
    public function cities($id)
    {
        $items = City::where(['state_id' => $id])->orderBy('city')->get();
        $data = [
            'data' => $items,
            'success' => 1,
            'errors' => [],
            'message' => 'List of Cities'
        ];
        return response()->json($data);
    }
    public function bookings()
    {

        $booking_status = $_GET['booking_status'] ?? null;
        $uid = auth('sanctum')->user()->id;
        $items = Booking::where('user_id', $uid);
        if ($booking_status == "0") {
            $items->where('booking_status', "0");
        }
        if ($booking_status) {
            $items->where('booking_status', $booking_status);
        }

        $items->with('user:id,name,email,mobile')->withSum('transactions', 'amount')->with('studio:id,name,mobile,address,longitude,latitude');
        $items->with('rents')->withSum('extra_added', 'amount')->with('vendor')->with('service');
        if (in_array($booking_status, ['0', '1', '2'])) {
            $items->where('booking_start_date', '>=', date('Y-m-d H:i:s'));
            $items->orderBy('bookings.booking_start_date', 'ASC');
        } else {
            $items->where('booking_start_date', '<', date('Y-m-d H:i:s'));
            $items->orderBy('bookings.booking_start_date', 'DESC');
        }

        $bookings = $items->paginate(10);
        $extra_charge_per_hour = 200;
        $bookings->getCollection()->transform(
            function ($b) use ($extra_charge_per_hour) {
                $extra_hours = 0;
                $start_time = strtotime($b['booking_start_date']);
                $end_time = strtotime($b['booking_end_date']);
                $night_start = strtotime(date('Y-m-d', $start_time) . ' 23:00:00');
                $morning_end = strtotime(date('Y-m-d', $start_time) . ' 08:00:00') + 86400;
                $same_date_before_open =   $morning_end = strtotime(date('Y-m-d', $start_time) . ' 08:00:00');
                while ($start_time < $end_time) {
                    if ($start_time > $night_start || $start_time < $same_date_before_open) {
                        if ($start_time <= $morning_end || $start_time < $same_date_before_open) {
                            $extra_hours++;
                        }
                    }
                    $start_time = strtotime('+1 hour', $start_time);
                }
                $extra_charge = ($extra_hours > 0) ? $extra_hours * $extra_charge_per_hour : 0;
                $base_amount = $b['duration'] * $b['studio_charge'];
                $total_amount = ($base_amount + $extra_charge) * 1.18;
                $b['extra_charge'] = $extra_charge;
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
        return response()->json($bookings);
    }
    public function terms($url)
    {
        $item = DB::table('policies')->where('url', $url)->first();
        $data = [
            'data' => $item,
            'success' => $item ? 1 : 0,
            'message' => 'Policy Fetched'
        ];
        return response()->json($data);
    }
    public function policies()
    {
        $item = DB::table('policies')->select(['url', 'policy'])->get();
        $data = [
            'data' => $item,
            'success' => 1,
            'message' => 'List of policies'
        ];
        return response()->json($data);
    }
    public function faqs()
    {
        $items = Faq::orderBy('id', 'DESC')->paginate(10);
        return response()->json($items);
    }
    public function contact_us(Request $request)
    {
        date_default_timezone_set('Asia/kolkata');
        $validator = Validator::make($request->all(), [
            'message' => 'required',
            "subject" => "required",

        ]);
        if ($validator->fails()) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'Customer Support'
            ];
            return response()->json($data);
        }

        $data = [
            'user_id' => auth('sanctum')->user()->id,
            "subject" => $request->subject,
            "message" => $request->message,
            'created_at' => date('Y-m-d H:i:s')

        ];
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = date('ymdhis') . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $data['image'] = $filename;
        }

        if (DB::table('queries')->insert($data)) {
            $data = [
                'data' => [],
                'success' => 1,
                'message' => 'New Query Generated Successfully'
            ];
            return response()->json($data);
        }
    }
    public function cancel_booking()
    {
        date_default_timezone_set('Asia/Kolkata');

        $fdata = [
            'booking_status' => '0',
            'payment_status' => '0'
        ];

        // Get timelimit from settings
        $gettimelimit = Setting::where('id', '2')->first();
        $minutes = $gettimelimit && floatval($gettimelimit->col_val) > 0
            ? floatval($gettimelimit->col_val)
            : 30;

        $timelimit = Carbon::now()->subMinutes($minutes)->format('Y-m-d H:i:s');

        // Get all bookings that need to be auto-cancelled
        $bookings = Booking::where($fdata)
            ->where('created_at', '<=', $timelimit)
            ->get();

        foreach ($bookings as $booking) {
            $bid = $booking->id;
            $user = User::where('id', $booking->user_id)->first();

            // User message
            $msg = "Your booking ID {$bid} has been auto-cancelled due to time limit expiry. Please re-book or contact support for assistance.";

            // Notify Super admin
            $super = User::where('role', 'Super')->first();
            if ($super && $super?->fcm_token) {
                $appmessage = "Booking ID {$bid} has been auto-cancelled due to payment/time limit expiry. View details in the Bookings tab.";

                $n_tdata = [
                    'user_id' => $user?->id ?? 0,
                    'booking_id' => $booking->id,
                    'studio_id' => $booking->studio_id,
                    'vendor_id' => $booking->vendor_id,
                    'shown_to_user' => '0',
                    'type' => 'Booking',
                    'title' => 'Booking Auto-Cancelled',
                    "message" => $appmessage,
                    "created_at" => date('Y-m-d H:i:s')
                ];
                RbNotification::create($n_tdata);

                // Optional push notification
                // $this->send_notification($super?->fcm_token, "Booking Auto-Cancelled", $appmessage, $super->id);
            }

            // Notify User
            $udata = [
                'user_id' => $user?->id ?? 0,
                'booking_id' => $booking->id,
                'studio_id' => $booking->studio_id,
                'vendor_id' => $booking->vendor_id,
                'type' => 'Booking',
                'shown_to_user' => '1',
                'title' => 'Booking Auto-Cancelled',
                'message' => $msg
            ];
            RbNotification::create($udata);

            // Update booking & remove blocked slots
            Booking::where('id', $booking->id)->update(['booking_status' => '2']);
            BlockedSlot::where('booking_id', $booking->id)->delete();
        }

        return true;
    }

    public function payment_notification()
    {
        $fdata = [
            'booking_status' => '0',
            'payment_status' => '0'
        ];

        $gettimelimit = Setting::where('id', 2)->first();
        $minutes = $gettimelimit && floatval($gettimelimit->col_val) > 0
            ? floatval($gettimelimit->col_val)
            : 30;

        $timelimit = Carbon::now()->subMinutes($minutes)->format('Y-m-d H:i:s');

        $items = Booking::where($fdata)
            ->where('created_at', '>=', $timelimit)
            ->whereHas('user', function ($query) {
                $query->whereNotNull('fcm_token')
                    ->where('fcm_token', '!=', '');
            })
            ->with('user')
            ->get();
        $super = User::where('role', 'Super')->first();

        foreach ($items as $booking) {
            $user = $booking->user;

            if (!empty($user?->fcm_token)) {
                $appMessage = " Please complete your payment to secure your booking. Incase of non-payment the booking will be automatically cancelled.";

                $notificationData = [
                    'user_id'        => $user->id,
                    'booking_id'     => $booking->id,
                    'studio_id'      => $booking->studio_id,
                    'vendor_id'      => $booking->vendor_id,
                    'shown_to_user'  => '1',
                    'type'           => 'Booking',
                    'title'          => 'Payment Pending',
                    'message'        => $appMessage,
                    'created_at'     => now(),
                ];
                RbNotification::create($notificationData);

                // $this->send_notification(
                //     $user->fcm_token,
                //     'Payment Pending',
                //     $appMessage,
                //     $user->id
                // );
            }
            if ($super && $super?->fcm_token) {;
                $appmessage = "A confirmed booking is awaiting for client payment. Notify client to avoid Auto-cancellation.";
                $n_tdata = [
                    'user_id'        => $user->id,
                    'booking_id'     => $booking->id,
                    'studio_id'      => $booking->studio_id,
                    'vendor_id'      => $booking->vendor_id,
                    'shown_to_user' => '0',
                    'type' => 'Booking',
                    'title' => 'Payment Pending',
                    "message" => $appmessage,
                    "created_at" => date('Y-m-d H:i:s')
                ];
                RbNotification::create($n_tdata);
                // $this->send_notification($super?->fcm_token,  'Payment Pending', $appmessage, $super->id);
            }
        }
    }


    public function queries()
    {
        $uid = auth('sanctum')->user()->id;
        $items = DB::table('queries')->where('user_id', $uid)->get();
        $data = [
            'data' => $items,
            'success' => $items ? 1 : 0,
            'message' => 'List of Queries'
        ];
        return response()->json($data);
    }
    public function delete_query(Request $request, $id)
    {
        $uid = auth('sanctum')->user()->id;
        $items = DB::table('queries')->where('user_id', $uid)->where(['id' => $id])->delete();
        $data = [
            'data' => $items,
            'success' => $items ? 1 : 0,
            'message' => 'Query deleted successfully'
        ];
        return response()->json($data);
    }
    public function banners()
    {
        $items = DB::table('banners')->orderBy('id', 'DESC')->get();
        $data = [
            'data' => $items,
            'success' => 1,
            'errors' => [],
            'message' => 'List of Banners'
        ];
        return response()->json($data);
    }
    public function search_bookings(Request $request)
    {
        date_default_timezone_set('Asia/kolkata');
        $validator = Validator::make($request->all(), [
            'keyword' => 'required'
        ]);
        if ($validator->fails()) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first()
            ];
            return response()->json($data);
        }
        $keyword = $request->keyword;
        $uid = auth('sanctum')->user()->id;

        // Start building the query
        $items = Booking::orderBy('bookings.id', 'DESC')
            ->where('created_by', $uid);

        // Include relationships and sum of transactions
        $items->with('user:id,name,email,mobile')
            ->withSum('transactions', 'amount')
            ->with('studio:id,name,mobile,address')
            ->with('rents');

        // Apply keyword search across multiple fields
        if (!empty($keyword)) {
            $items->where(function ($query) use ($keyword) {
                $query->whereHas('studio', function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%")
                        ->orWhere('address', 'like', "%$keyword%");
                })
                    ->orWhereDate('created_at', '=', $keyword);
            });
            $items->orWhere('id', $keyword);
        }

        // Paginate the results
        $bookings = $items->paginate(10);
        return response()->json(['data' => $bookings, 'success' => 1, 'message' => 'filter bookings']);
    }
    public function add_promo_code(Request $request)
    {
        date_default_timezone_set('Asia/kolkata');
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'promo_code' => 'required|exists:promo_codes,promo_code',
        ]);
        if ($validator->fails()) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first()
            ];
            return response()->json($data);
        }
        $uid = auth('sanctum')->user()->id;
        $today = date('Y-m-d H:i:s');
        $pcode = $request->promo_code;
        $bid = $request->booking_id;
        $booking = Booking::where('id', $bid)->first();

        $isValid = DB::table('promo_codes')->where('promo_code', $pcode)->where('studio_id', $booking->studio_id)
            ->where('deleted_at', '=', null)->where('start_at', '<=', $today)->where('end_at', '>=', $today)
            ->first();
        if (!$isValid) {

            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'Invalid Promo Code'
            ];
            return response()->json($data);
        }
        if ($isValid) {
            if ($isValid?->user_id) {
                if ($isValid->user_id != $uid) {
                    $data = [
                        'data' => [],
                        'success' => 0,
                        'errors' => $validator->errors(),
                        'message' => 'Invalid Promo Code'
                    ];
                    return response()->json($data);
                }
            }
        }
        $ucount = $isValid->user_count;

        $isUsed = Booking::where('user_id', $uid)->where('promo_id', $isValid->id)->count();
        if ($isUsed >= $ucount) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'This promo code has been alreay already used by the user.'
            ];
            return response()->json($data);
        }
        $booking = Booking::where('id', $bid)->with('studio')->with('transactions')->withSum('transactions', 'amount')->withSum('extra_added', 'amount')->with('rents')->with('gst')
            ->with('service:id,name')
            ->first();
        if (!$booking) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'Invalid Booking .'
            ];
            return response()->json($data);
        }
        if ($booking->promo_id) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'Promo Code can be used only once.'
            ];
            return response()->json($data);
        }
        $paid = $booking->transactions_sum_amount;
        if ($paid > 0) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'Promo Code can be applied only on new bookings'
            ];
            return response()->json($data);
        }
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

        $totalPaable = $booking->duration * $booking->studio_charge + $rent_charge + $extra_charge + $booking['extra_added_sum_amount'];
        $withgst = $totalPaable * 1.18;
        $amount = $withgst  - $paid;


        $promo_discount = $isValid->discount;
        $type = $isValid->discount_type;
        if ($type == "Fixed") {
            $discount = $promo_discount;
        } else {
            $discount = $amount * $promo_discount * 0.01;
        }

        Booking::where('id', $bid)->update(['promo_id' => $isValid->id, 'promo_code' => $pcode,  'promo_discount_calculated' => $discount]);
        return response()->json([
            'data' => [],
            'success' => 0,
            'errors' => $validator->errors(),
            'message' => 'Promo Code applied successfully'
        ]);
    }
    public function delete_account(Request $request)
    {
        $uid = auth('sanctum')->user()->id;
        $finduser =  User::where('id', $uid)->first();
        User::where('id', $uid)->update(['deleted_at' => date('Y-m-d H:i:s'), 'email' =>  $finduser->email . 'd', 'mobile' =>         $finduser->mobile . 'd']);
        return response()->json([
            'data' => [],
            'success' => 1,
            'errors' => [],
            'message' => 'User deleted successfully'
        ]);
    }
    public function my_notifications()
    {
        $uid = auth('sanctum')->user()->id;
        $items = DB::table('notifications')->where('user_id', $uid)->where(['shown_to_user' => '1'])->orderBy('id', 'DESC')->get();
        return response()->json([
            'data' => $items,
            'success' => 1,
            'errors' => [],
            'message' => 'User notification fetched successfully'
        ]);
    }
    public function gst_list()
    {
        $uid = auth('sanctum')->user()->id;
        $items = DB::table('booking_gsts')->where('user_id', $uid)->where('is_delete', '0')->orderBy('id', 'DESC')->get();
        return response()->json(['success' => 1, "data" => $items]);
    }
    public function find_gst_list(Request $request, $id)
    {
        $uid = $id;
        $items = DB::table('booking_gsts')->where('user_id', $uid)->where('is_delete', '0')->orderBy('id', 'DESC')->get();
        return response()->json(['success' => 1, "data" => $items]);
    }
    public function update_profile(Request $request)
    {
        $id = auth('sanctum')->user()->id;

        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,other',
        ]);

        // Prepare data for update
        $data = $request->all();

        if ($request->filled('dob')) {
            $data['dob'] = $request->dob;
        }

        if ($request->filled('gender')) {
            $data['gender'] = $request->gender;
        }

        // Update the user's profile
        $user = User::find($id);
        if ($user) {
            $user->update($data);

            // Return success response with updated data
            return response()->json([
                'data' => $user,
                'success' => 1,
                'errors' => [],
            ]);
        }

        // If user not found, return error response
        return response()->json([
            'data' => [],
            'success' => 0,
            'errors' => ['User not found.'],
        ], 404);
    }
    public function clear_notification(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        $nid = $request->id;
        $uid = auth('sanctum')->user()->id;
        if ($nid == "All") {
            RbNotification::where('user_id', $uid)->update(['shown_to_user' => '0']);
        } else {
            RbNotification::where('user_id', $uid)->where('id', $nid)->update(['shown_to_user' => '0']);
        }

        return response()->json(['success' => 1, "data" => []]);
    }
    public function is_all_notification_read(Request $request)
    {
        $uid = auth('sanctum')->user()->id;
        $role = auth('sanctum')->user()->role;
        $isUserShown = $role == "User" ? "1" : "0";
        $rbns = RbNotification::where('shown_to_user', $isUserShown)->where('is_read', '0');
        if ($role == "User") {
            $rbns->where('user_id', $uid);
        }
        $count =  $rbns->count();
        $data = [
            'is_all_read' =>  $count > 0 ? false : true,
            'pending_to_read' => $count,
            'role' => $role,
            'uid' => $uid
        ];
        return response()->json(['success' => 1, "data" => $data]);
    }
    public function mark_all_read()
    {
        $uid = auth('sanctum')->user()->id;
        $role = auth('sanctum')->user()->role;
        $isUserShown = $role == "User" ? "1" : "0";
        $rbns = RbNotification::where('shown_to_user', $isUserShown)->where('is_read', '0');
        if ($role == "User") {
            $rbns->where('user_id', $uid);
        }
        $data =  $rbns->update(['is_read' => '1']);
        return response()->json(['success' => 1, "data" => $data, 'message' => 'all read successfully']);
    }
    public function all_slots()
    {
        $items = Slot::orderBy('start_at', 'asc')->get();
        return response()->json([
            'data' => $items,
            'success' => 1,
            'message' => 'list of slots'
        ]);
    }
}
