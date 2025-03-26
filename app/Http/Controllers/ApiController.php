<?php

namespace App\Http\Controllers;

use App\Models\Studio\Service;
use App\Models\Booking;
use App\Models\RbNotification;
use App\Models\User;
use App\Models\Faq;
use App\Models\Studio\Studio;
use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;


class ApiController extends Controller
{
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
        $validator = Validator::make($request->all(), [
            'service_id' => 'required',
        ]);
        if ($validator->fails()) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'List of Services'
            ];
            return response()->json($data);
        }
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
            'errors' => $validator->errors(),
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
        //  \Artisan::call('route:clear');
        $booking_status = $_GET['booking_status'] ?? null;
        $uid = auth('sanctum')->user()->id;
        $items = Booking::orderBy('bookings.id', 'DESC')->where('user_id', $uid);
        if ($booking_status) {
            $items->where('booking_status', $booking_status);
        }
        $items->with('user:id,name,email,mobile')->withSum('transactions', 'amount')->with('studio:id,name,address,longitude,latitude');
        $items->with('rents')->with('vendor')->with('service');
        $bookings = $items->paginate(10);
        return response()->json($bookings);
    }
    public function terms($url)
    {
        $item = DB::table('policies')->where('url', $url)->first();
        $data = [
            'data' => $item,
            'success' => $item ? true : false,
            'message' => 'Policy Fetched'
        ];
        return response()->json($data);
    }
    public function policies()
    {
        $item = DB::table('policies')->select(['url', 'policy'])->get();
        $data = [
            'data' => $item,
            'success' => true,
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
                'success' => true,
                'message' => 'New Query Generated Successfully'
            ];
            return response()->json($data);
        }
    }
    public function cancel_booking()
    {
        date_default_timezone_set('Asia/Kolkata');
        $fdata =  [
            'booking_status' => '0',
            'payment_status' => '0'
        ];
        $gettimelimit = Setting::where('id', '2')->first();
        $minutes =  $gettimelimit ?  floatval($gettimelimit->col_val) > 0 ?  floatval($gettimelimit->col_val) :  30 : 30;
        $timelimit = Carbon::now()->subMinutes($minutes)->format('Y-m-d H:i:s');
        Booking::where($fdata)->where('created_at', '<=',  $timelimit)->update(['booking_status' => '2']);
        return true;
    }
    public function queries()
    {
        $uid = auth('sanctum')->user()->id;
        $items = DB::table('queries')->where('user_id', $uid)->get();
        $data = [
            'data' => $items,
            'success' => $items ? true : false,
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
            'success' => $items ? true : false,
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
            ->with('studio:id,name,address')
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
        }

        // Paginate the results
        $bookings = $items->paginate(10);
        return response()->json(['data' => $bookings, 'success' => '1', 'message' => 'filter bookings']);
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
        $item =  Booking::where('id', $bid)->where('approved_at', '!=', null)->with('rents')->withSum('transactions', 'amount')->with('studio')->with('service')->first();
        if (!$item) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'Invalid Booking .'
            ];
            return response()->json($data);
        }
        if ($item->promo_id) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'Promo Code can be used only once.'
            ];
            return response()->json($data);
        }
        $paid = $item->transactions_sum_amount;
        if ($paid > 0) {
            $data = [
                'data' => [],
                'success' => 0,
                'errors' => $validator->errors(),
                'message' => 'Promo Code can be applied only on new bookings'
            ];
            return response()->json($data);
        }

        $rents =  $item->rents;
        $arr = [];
        foreach ($rents as $r) {
            array_push($arr, $r->pivot->charge * $r->pivot->uses_hours);
        }

        $rentcharge = array_sum($arr);
        $amount =  ($item->duration * $item->studio_charge + $rentcharge) * 1.18 - $item->transactions_sum_amount;
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
        User::where('id', $uid)->update(['deleted_at' => date('Y-m-d H:i:s'), 'email' => '', 'mobile' => '']);
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
        $items = DB::table('booking_gsts')->where('user_id', $uid)->orderBy('id', 'DESC')->get();
        return response()->json(['success' => true, "data" => $items]);
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
        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

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

        return response()->json(['success' => true, "data" => []]);
    }
}
