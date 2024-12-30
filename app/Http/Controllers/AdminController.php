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
use Illuminate\Support\Facades\DB;
use App\Mail\CustomMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
        }

    }
    public function logout(Request $request){
        session()->flush();
        Auth::logout();
       return  redirect()->route('login');
    }
    public function dashboard(){
       date_default_timezone_set('Asia/kolkata');
        // $data = [
        //     'subject' => 'test Mail',
        //     'name' => 'John Doe',
        //     'order_id' => '123456'
        // ];
        // Mail::to('khan03117@gmail.com')->send(new CustomMail($data));
        
        
    
        $title = "Welcome to Dashboard";
       $now = Carbon::now()->format('Y-m-d');
        $vid = $_GET['vendor_id'] ?? null;
        $sid = $_GET['studio_id'] ?? null;
        $service_id = $_GET['service_id'] ?? null;
          $studios = Studio::where('vendor_id', $vid)->get();
         $services = Service::whereIn('id', function($q) use ($sid){
             $q->from('service_studios')->where('studio_id', $sid)->select('service_id');
         })->get();
        $boks = Booking::where('booking_status', '=', '1');
        if(Auth::user()->role != "Super"){
            $boks->where('vendor_id', Auth::user()->vendor_id);
        }
        $boks->with('user:id,name')->where('approved_at', '!=', null);
        $books = $boks->with('rents')->withSum('transactions', 'amount');
      
        $tay_booking = Booking::whereDate('booking_start_date', $now)->where('booking_status', '=', '1');
         if(Auth::user()->role != "Super"){
            $tay_booking->where('vendor_id', Auth::user()->vendor_id);
        }
        $today_item = $tay_booking->where('approved_at', '!=', null);
        $today_booking = $today_item->sum('duration');
        
       $firstdate = Carbon::now()->startOfMonth()->format('Y-m-d');
       
        $enddate = Carbon::now()->endOfMonth()->format('Y-m-d');
       
        $tal_booking_month = Booking::where('booking_start_date', '>=',  $firstdate)->where('booking_start_date', '<=', $enddate);
        if(Auth::user()->role != "Super"){
            $tal_booking_month->where('vendor_id', Auth::user()->vendor_id);
        }
        $total_booking_month = $tal_booking_month->where('booking_status', '=', '1')->sum('duration');
        
        $aproval = Booking::whereMonth('booking_start_date', date('m'));
         if(Auth::user()->role != "Super"){
            $aproval->where('vendor_id', Auth::user()->vendor_id);
        }
        $aproval->whereYear('booking_start_date', date('Y'))->where('booking_status', '=', '1');
        $approval = $aproval->where('approved_at', null)->count();
        
        $vends = Vendor::orderBy('name');
        if(Auth::user()->role != "Super"){
            $vends->where('id', Auth::user()->vendor_id);
        }
        $vendors = $vends->get();
        $amountgenerated = [];
        $itms = Booking::where('booking_start_date', '>=',  $firstdate)->where('booking_start_date', '<=', $enddate)->where('booking_status', '=', '1')->with('user:id,name');
         if(Auth::user()->role != "Super"){
            $itms->where('id', Auth::user()->vendor_id);
        }
        $items = $itms->with('rents')->withSum('transactions', 'amount')->get();
        foreach($items as $item){
            $rents =  $item->rents;
            $arr = [];
            foreach($rents as $r){
                array_push($arr, $r->pivot->charge*$r->pivot->uses_hours);
            }
            $rentcharge = array_sum($arr);
            $discount = $item->promo_discount_calculated;
            $amount =  $item->duration*$item->studio_charge*1.18 + $rentcharge - $item->transactions_sum_amount - $discount;
            $earned = $item->transactions_sum_amount;
            array_push($amountgenerated, $earned);
        }
       
        $totalamount = array_sum($amountgenerated);
        
        $res = compact('title', 'today_booking', 'total_booking_month', 'approval', "vendors", "vid", "sid",  "studios", "services", "service_id", 'totalamount');
       
       
        return view('admin.dashboard', $res);
    }
    public function events(){
        $vid = $_GET['vendor_id'] ?? null;
        $sid = $_GET['studio_id'] ?? null;
        $service_id = $_GET['service_id'] ?? null;
        $arr = [];
        
        $studios = Studio::where('vendor_id', $vid)->get();
         $services = Service::whereIn('id', function($q) use ($sid){
             $q->from('service_studios')->where('studio_id', $sid)->select('service_id');
         })->get();
        $books = Booking::where('booking_status', '=', '1')->with('user:id,name')->where('approved_at', '!=', null)->with('rents')->withSum('transactions', 'amount')->with('service');
        if($vid){
            $books->where('vendor_id', $vid);
        }
        if(Auth::user()->role != "Super"){
            $books->where('vendor_id', Auth::user()->vendor_id);
        }
        if($sid){
            $books->where('studio_id', $sid);
        }
        $items = $books->with('studio:id,name,color')->with('vendor')->get();
    //   return response()->json($items);
    //   die;
        
            foreach($items as $item){
                $urr = [
                    "title" => $item->user->name.' | '.$item->studio->name.' | '.$item->service?->name,
                    "start" => $item->booking_start_date,
                    "end" => $item->booking_end_date,
                    "id" => $item->id,
                   
                  'classNames' => [implode('_', explode(' ', $item->studio->name))],
                   "backgroundColor" => $item->studio->color,
                    
                ];
                array_push($arr, $urr);
            }
       return $arr;
    }
    public function all_notification(){
       $itms = RbNotification::whereIn('type', ['Booking', 'Payment'])->with('user');
        if(Auth::user()->role != "Super"){
            $itms->where('vendor_id', Auth::user()->vendor_id);
        }
       $itms->orderBy('is_read', 'asc');   
       $itms->orderBy('created_at', 'desc');
       $items = $itms->paginate(50);
       $title = "Rb Notifications";
       $data = compact('items', 'title');
       return view('admin.notifications', $data);
    }
    public function delete_notification(Request $request, $id){
        RbNotification::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Deleted successfuly');
    }
    public function mark_read(Request $request){
        $ids = explode(',' , $request->ids) ;
       RbNotification::whereIn('id', $ids)->update(['is_read' => '1']);
        return redirect()->back()->with('success', 'Deleted successfuly');
    }
    public function users(){
        $title = "List of users";
        $items  = User::orderBy('id', 'DESC')->get();
        $res = compact('title', 'items');
        return view('admin.reports.users', $res);
    }
    public function update_admin_profile(Request $request){
        $request->validate(['email' => 'required|email|unique:users,email,'.auth()->user()->id, 'password' => 'required']);
        $input = $request->except('_token');
        $data = [
            'email' => $input['email'],
            'password' => Hash::make($input['password'])
            ];
        User::where('id', auth()->user()->id)->update($data);
        return redirect()->back()->with('success', 'Updated');
    }

}
