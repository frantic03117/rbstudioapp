<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\RbNotification;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Studio\Service;
use App\Models\Studio\Studio;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index()
    {
        if (!Auth::user()) {
            return view("index");
        }
        return redirect()->route('dashboard');
    }

    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required|min:6',
        ]);


        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->route('dashboard')->with('success', 'Welcome to Dashboard');
        } else {
            return redirect()->back()->with('error', 'Invalid credentials');
        }
    }
    public function admin_profile(Request $request)
    {
        $id = auth('sanctum')->user()->id;
        $user = User::where('id', $id)->first();
        $permissions = $user->getAllPermissions()->pluck('name');
        $data = ['user' => $user, 'permissions' => $permissions];
        return response()->json(['data' => $data, 'success' => 1]);
    }
    public function api_login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'password' => 'required|min:6'
        ]);
        if ($validation->fails()) {
            return response()->json(['success' => 0,  'message' => $validation->errors()->first(), 'errors' => $validation->errors()]);
        }
        $user = User::where('email', $request->email)->where('role', '!=', 'User')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => 0,
                'message' => 'Invalid email or password.'
            ]);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'success' => 1,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user
        ]);
    }
    public function logout(Request $request)
    {
        session()->flush();
        Auth::logout();
        return  redirect()->route('login');
    }

    public function dashboard(Request $request)
    {
        date_default_timezone_set('Asia/kolkata');
        $title = "Welcome to Dashboard";
        $now = Carbon::now()->format('Y-m-d');
        $vid = $_GET['vendor_id'] ?? null;
        $sid = $_GET['studio_id'] ?? null;
        $service_id = $_GET['service_id'] ?? null;
        $studios = Studio::where('vendor_id', $vid)->get();
        $services = Service::whereIn('id', function ($q) use ($sid) {
            $q->from('service_studios')->where('studio_id', $sid)->select('service_id');
        })->get();
        $finddview = Setting::where('col_name', 'Default Calender View')->first();
        $defaultView = $finddview ?  $finddview['col_val'] : 'listWeek';
        // return response()->json($defaultView);
        // die;
        $boks = Booking::where('booking_status', '=', '1');
        if (Auth::user()->role != "Super") {
            $boks->where('vendor_id', Auth::user()->vendor_id);
        }
        $boks->with('user:id,name')->where('approved_at', '!=', null);
        $books = $boks->with('rents')->withSum('transactions', 'amount');

        $tay_booking = Booking::whereDate('booking_start_date', $now)->where('booking_status', '=', '1');
        if (Auth::user()->role != "Super") {
            $tay_booking->where('vendor_id', Auth::user()->vendor_id);
        }
        $today_item = $tay_booking->where('approved_at', '!=', null);
        $today_booking = $today_item->sum('duration');

        $firstdate = Carbon::now()->startOfMonth()->format('Y-m-d');

        $enddate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $tal_booking_month = Booking::where('booking_start_date', '>=',  $firstdate)->where('booking_start_date', '<=', $enddate);
        if (Auth::user()->role != "Super") {
            $tal_booking_month->where('vendor_id', Auth::user()->vendor_id);
        }
        $total_booking_month = $tal_booking_month->where('booking_status', '=', '1')->sum('duration');
        $aproval = Booking::where('booking_start_date', '>=', date('Y-m-d H:i:s'));
        if (Auth::user()->role != "Super") {
            $aproval->where('vendor_id', Auth::user()->vendor_id);
        }
        $aproval->where('booking_status', '0');
        $approval = $aproval->where('approved_at', null)->count();
        $vends = Vendor::orderBy('name');
        if (Auth::user()->role != "Super") {
            $vends->where('id', Auth::user()->vendor_id);
        }
        $vendors = $vends->get();
        $amountgenerated = [];
        $itms = Booking::where('booking_start_date', '>=',  $firstdate)->where('booking_start_date', '<=', $enddate)->where('booking_status', '=', '1')->with('user:id,name');
        if (Auth::user()->role != "Super") {
            $itms->where('id', Auth::user()->vendor_id);
        }
        $items = $itms->with('rents')->withSum('transactions', 'amount')->get();
        foreach ($items as $item) {
            $rents =  $item->rents;
            $arr = [];
            foreach ($rents as $r) {
                array_push($arr, $r->pivot->charge * $r->pivot->uses_hours);
            }
            $rentcharge = array_sum($arr);
            $discount = $item->promo_discount_calculated;
            $amount =  $item->duration * $item->studio_charge * 1.18 + $rentcharge - $item->transactions_sum_amount - $discount;
            $earned = $item->transactions_sum_amount;
            array_push($amountgenerated, $earned);
        }
        $totalamount = array_sum($amountgenerated);
        $payment_received = Transaction::where(['status' => 'Success', 'type' => 'Credit'])->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)->sum('amount');
        $totalamount += $payment_received;
        $nonpaymentBookingCount =  Booking::where('booking_start_date', '>=',  $firstdate)
            ->where('booking_start_date', '<=', $enddate)
            ->where('booking_status', '=', '1')->where('payment_status', '=', '0')->count();
        $res = compact('title', 'today_booking', 'defaultView', 'total_booking_month', 'approval', "vendors", "vid", "sid",  "studios", "services", "service_id", 'totalamount', 'nonpaymentBookingCount');
        if ($request->expectsJson()) {
            return response()->json($res);
        }
        return view('admin.dashboard', $res);
    }
    public function events(Request $request)
    {
        date_default_timezone_set('Asia/kolkata');
        $vid = $_GET['vendor_id'] ?? null;
        $sid = $_GET['studio_id'] ?? null;
        $service_id = $_GET['service_id'] ?? null;
        $arr = [];
        $booking_date = $_GET['booking_date'] ?? null;
        $booking_date_from = $_GET['booking_date_from'] ?? null;
        $booking_date_to = $_GET['booking_date_to'] ?? null;
        $studios = Studio::where('vendor_id', $vid)->get();
        $services = Service::whereIn('id', function ($q) use ($sid) {
            $q->from('service_studios')->where('studio_id', $sid)->select('service_id');
        })->get();
        $books = Booking::where('booking_status', '1')->whereDate('booking_start_date', '>=', date('Y-m-d'))
            ->with('user:id,name')->where('approved_at', '!=', null)
            ->with('rents')->withSum('transactions', 'amount')->with('service');
        if ($vid) {
            $books->where('vendor_id', $vid);
        }
        if ($service_id) {
            $books->where('service_id', $service_id);
        }
        if (Auth::user()->role != "Super") {
            $books->where('vendor_id', Auth::user()->vendor_id);
        }
        if ($sid) {
            $books->where('studio_id', $sid);
        }
        if ($booking_date) {
            $books->whereDate('booking_start_date', $booking_date);
        }
        if ($booking_date_from) {
            $books->whereDate('booking_start_date', '>=', $booking_date_from);
        }
        if ($booking_date_to) {
            $books->whereDate('booking_start_date', '<=', $booking_date_to);
        }
        $items = $books->with('studio:id,name,mobile,color')->with('vendor')->orderBy('booking_start_date', 'ASC')->get();
        //   return response()->json($items);
        //   die;

        foreach ($items as $item) {
            $urr = [
                "title" => $item->id . '|' . $item->user->name . ' | ' . $item->studio->name . ' | ' . $item->service?->name,
                "start" => $item->booking_start_date,
                "end" => $item->booking_end_date,
                "id" => $item->id,
                "booking_id" => $item->id,
                "user" => $item->user->name,
                "studio" => $item->studio->name,
                "service" => $item->service?->name,
                'classNames' => [implode('_', explode(' ', $item->studio->name))],
                "backgroundColor" => $item->studio->color,
            ];
            array_push($arr, $urr);
        }
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $arr,
                'success' => 1,
                "booking_date" => $booking_date,
                'message' => 'List of events'
            ]);
        }
        return $arr;
    }
    public function all_notification(Request $request)
    {
        $itms = RbNotification::whereIn('type', ['Booking', 'Payment'])->with('user:id,name,email,mobile')->with('booking')->with('studio:id,name,mobile,address');
        if (Auth::user()->role != "Super") {
            $itms->where('vendor_id', Auth::user()->vendor_id);
        }

        $itms->orderBy('is_read', 'asc');
        $itms->orderBy('created_at', 'desc');
        $items = $itms->paginate(50);
        $title = "Rb Notifications";
        $data = compact('items', 'title');
        if ($request->expectsJson()) {
            return response()->json(['data' => $items, 'success' => 1, 'message' => $title]);
        }

        // return response()->json($data);
        return view('admin.notifications', $data);
    }
    public function delete_notification(Request $request, $id)
    {
        RbNotification::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Deleted successfuly');
    }
    public function mark_read(Request $request)
    {
        $ids = explode(',', $request->ids);
        RbNotification::whereIn('id', $ids)->update(['is_read' => '1']);
        return redirect()->back()->with('success', 'Deleted successfuly');
    }
    public function setting() {}
    public function users()
    {
        $title = "List of users";
        $items  = User::orderBy('id', 'DESC')->get();
        $res = compact('title', 'items');
        return view('admin.reports.users', $res);
    }
    public function update_admin_profile(Request $request)
    {
        $request->validate(['email' => 'required|email|unique:users,email,' . auth()->user()->id, 'password' => 'required']);
        $input = $request->except('_token');
        $data = [
            'email' => $input['email'],
            'password' => Hash::make($input['password'])
        ];
        if ($request->remember_token) {
            $data['remember_token'] = $request->remember_token;
        }
        User::where('id', auth()->user()->id)->update($data);
        return redirect()->back()->with('success', 'Updated');
    }
}
