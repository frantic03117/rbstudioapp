<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Query;
use App\Models\Setting;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Studio\Service;
use App\Models\Studio\Studio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function home()
    {
        #User::where('id', '1')->update(['password' => Hash::make('12345678')]);
        $title = "Book Studio For your events";
        $res = compact('title');
        return view('frontend.index', $res);
    }
    public function terms($url)
    {
        $title = "Book Studio For your events";
        $policy = DB::table('policies')->where('url', $url)->first();

        $res = compact('title', 'policy');

        return view('frontend.terms', $res);
    }
    public function index()
    {
        $title = "Calendar";
        $vendors = Vendor::orderBy('name')->get();
        $vid = $_GET['vendor_id'] ?? null;
        $sid = $_GET['studio_id'] ?? null;
        $service_id = $_GET['service_id'] ?? null;
        $finddview = Setting::where('col_name', 'Default Calender View')->first();
        $defaultView = $finddview ?  $finddview['col_val'] : 'listWeek';


        $arr = [];
        $studios = Studio::where('vendor_id', $vid)->get();
        $services = Service::whereIn('id', function ($q) use ($sid) {
            $q->from('service_studios')->where('studio_id', $sid)->select('service_id');
        })->get();
        $books = Booking::where('booking_status', '!=', '2')->with('user:id,name')->where('approved_at', '!=', null);
        if ($vid) {
            $books->where('vendor_id', $vid);
        }
        if ($sid) {
            $books->where('studio_id', $sid);
        }
        $items = $books->with('studio:id,name')->with('vendor')->get();


        foreach ($items as $item) {
            $urr = [
                "title" => $item->user->name . ' | Studio :' . $item->studio->name . ' | Vendor: ' . $item->vendor->name,
                "start" => $item->booking_start_date,
                "end" => $item->booking_end_date,
                "id" => $item->id,
                "backgroundColor" => "#077773"
            ];
            array_push($arr, $urr);
        }


        $res = compact("title", "vendors", "vid", "sid", "arr", "studios", "services", "service_id", "defaultView");
        return view("admin.reports.calendar", $res);
    }

    public function banners()
    {
        $res['title'] = "List of Banners";
        $res['banners'] = DB::table('banners')->orderBy('id', 'DESC')->get();
        return view('admin.banners', $res);
    }
    public function save_banners(Request $request)
    {
        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $file = $request->file('banner');
        $imageName = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('banners'), $imageName);
        $data = [
            'banner' => 'banners/' . $imageName,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (DB::table('banners')->insert($data)) {
            return redirect()->back()->with('success', 'Banner Uploaded Successfully');
        }
    }
    public function banners_delete($id)
    {
        DB::table('banners')->where('id', $id)->delete();
        return redirect()->back();
    }
    public function queries()
    {
        $title = "List of Queries";
        $items = Query::orderBy('id', 'DESC')->with('user')->whereHas('user')->get();

        $res = compact('title', 'items');
        return view('admin.query.index', $res);
    }
    public function resolve_queries(Request $request)
    {
        // Validate the incoming request
        $validation = Validator::make($request->all(), [
            'id' => 'required|exists:queries,id'
        ]);

        // Handle validation failure
        if ($validation->fails()) {
            return response()->json(['data' => [], 'success' => false, 'message' => 'Validation failed']);
        }

        try {
            // Find the query by ID
            $query = Query::find($request->id);

            // Update the is_resolved field to 1
            $query->is_resolved = '1';
            $query->save();

            // Return a success response
            return response()->json([
                'data' => $query,
                'success' => true,
                'message' => 'Query resolved successfully'
            ]);
        } catch (\Exception $e) {
            // Handle any errors
            return response()->json([
                'data' => [],
                'success' => false,
                'message' => 'An error occurred while resolving the query'
            ]);
        }
    }
}