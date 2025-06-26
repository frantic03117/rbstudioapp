<?php

namespace App\Http\Controllers;

use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\Location\City;
use App\Models\Location\State;
use App\Models\Rent;
use App\Models\Studio\Service;
use App\Models\Slot;
use App\Models\Studio\Studio;
use App\Models\StudioImage;
use App\Models\User;
use App\Models\RbNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AjaxController extends Controller
{
    public function states(Request $request)
    {
        $request->validate([
            "country_id" => "required",
        ]);
        $cid = $request->input("country_id");
        $items = State::where(['country_id' =>  $cid])->get();
        $output = '<option value="" selected @disabled(true)>---Select---</option>';
        foreach ($items as $item) {
            $output .= "<option value={$item->id}>{$item->state}</option>";
        }
        return $output;
    }
    public function cities(Request $request)
    {
        $request->validate([
            "state_id" => "required"
        ]);
        $cid = $request->input("state_id");
        $items = City::where('state_id', '=',  $cid)->get();
        $output = '   <option value="" selected>---Select---</option>';
        foreach ($items as $item) {
            $output .= "<option value={$item->id}>{$item->city}</option>";
        }
        return $output;
    }
    public function getStudios(Request $request)
    {
        // $request->validate([
        //     "vendor_id" => "required"
        // ]);
        $vid = $request->vendor_id;
        if ($vid) {
            $studios = Studio::where("vendor_id", "=", $vid)->select(['name', 'id'])->get();
        } else {
            $studios = Studio::select(['name', 'id'])->get();
        }

        $output = '<option value="" selected>All</option>';
        foreach ($studios as $item) {
            $output .= "<option value={$item->id}>{$item->name}</option>";
        }
        return $output;
    }
    public function getResources(Request $request)
    {
        $request->validate([
            "studio_id" => "required"
        ]);
        $sid = $request->studio_id;
        $items = Rent::where("studio_id", "=", $sid)
            ->join('charges', 'charges.item_id', '=', 'rents.id')
            ->select(['rents.*', 'charges.charge'])
            ->get();
        return response()->json($items);
    }
    public function getStudioCharge(Request $request)
    {
        $request->validate([
            "studio_id" => "required"
        ]);
        $sid = $request->studio_id;
        $items = Rent::where("studio_id", "=", $sid)->where('item_id', '=', null)->first();
        return response()->json(['data' => $items, 'success' => 1]);
    }
    public function get_slots(Request $request)
    {
        date_default_timezone_set('Asia/kolkata');
        $request->validate([
            'sdate' => 'required',
            "studio_id" => "required",
            'duration' => 'required'
        ]);
        $now =  Carbon::now();
        $sid = $request->studio_id;
        $sdate = $request->sdate;
        $duration = $request->duration;
        $slots = Slot::whereNotIn('id', function ($q) use ($sdate, $sid) {
            $q->from('blocked_slots')->where('bdate', $sdate)->select('slot_id')->where('studio_id', $sid);
        })->where('start_at', '<=', '22:00:00')->where('start_at', '>=', '08:00:00')->get();
        $arr = [];
        foreach ($slots as $i => $s) {
            $st = $s->start_at;
            $sdt = date('Y-m-d H:i:s', strtotime($sdate . ' ' . $st));
            $edt = date('Y-m-d H:i:s', strtotime($sdate . ' ' . $st) + $duration * 3600);
            if (strtotime($sdt) >= strtotime($now)) {
                $isBooked = BlockedSlot::where('slot_id', $s->id)->where('bdate', $sdate)->where('studio_id', $sid)->first();
                if (!$isBooked) {
                    $bookings = Booking::where('booking_start_date', '<', $edt)->where('booking_end_date', '>', $edt)->whereDate('booking_start_date', $sdate)->where('studio_id', $sid)->count();
                    $mid_book = Booking::where('booking_start_date', '>=', $sdt)
                        ->where('booking_end_date', '<=', $edt)->where('studio_id', $sid)->count();
                    // if($mid_book){
                    //     echo json_encode([$sdt, $edt]);
                    //     echo json_encode($mid_book);
                    // }
                    if ($bookings == 0 && $mid_book == 0) {
                        array_push($arr, [$sdt, $edt]);
                    }
                }
            }
        }

        $res = [
            'data' => $arr,
            'success' => sizeof($arr) > 0 ? true : false,
            'errors' => [],
            'message' => 'List of Available Slots'
        ];
        return response()->json($res);
    }
    // public function find_start_slot(Request $request)
    // {
    //     date_default_timezone_set('Asia/kolkata');
    //     $request->validate([
    //         'sdate' => 'required',
    //         "studio_id" => "required",
    //     ]);
    //     $now =  Carbon::now();
    //     $sid = $request->studio_id;
    //     $sdate = Carbon::parse($request->sdate)->format('Y-m-d');
    //     $studio = Studio::where('id', $sid)->first();
    //     $opens = $studio->opens_at;
    //     $close = $studio->ends_at;
    //     $bid = $request->booking_id;
    //     $isEdit = $request->isEdit;
    //     $items = Slot::whereNotIn('id', function ($q) use ($sdate, $sid, $isEdit, $bid) {
    //         $q->from('blocked_slots');
    //         $q->where('bdate', $sdate)->select('slot_id')->where('studio_id', $sid);
    //         if ($isEdit && $bid) {
    //             $q->where('booking_id', '!=', $bid);
    //         }
    //     });
    //     if ($request->mode) {
    //         $items->where('start_at', '<=', $close)->where('start_at', '>=', $opens);
    //     }
    //     $slots = $items->get();
    //     $modifiedObjects = collect($slots)->map(function ($object) use ($sdate) {
    //         $object['date'] = $sdate;
    //         return $object;
    //     });
    //     $res = [
    //         'success' => true,
    //         'data' => $modifiedObjects
    //     ];
    //     return response()->json($res);
    // }
    public function find_start_slot(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        $request->validate([
            'sdate' => 'required|date',
            'studio_id' => 'required|exists:studios,id',
        ]);
        $sid = $request->studio_id;
        $sdate = Carbon::parse($request->sdate)->format('Y-m-d');
        $studio = Studio::findOrFail($sid); // Use findOrFail to ensure studio exists
        $opens = Carbon::parse($studio->opens_at);
        $close = Carbon::parse($studio->ends_at);
        $bid = $request->booking_id;
        $isEdit = $request->isEdit;
        $currentTime = now()->format('H:i:s');
        // Start building the Slot query
        $query = Slot::whereNotIn('id', function ($q) use ($sdate, $sid, $isEdit, $bid) {
            $q->from('blocked_slots')
                ->where('bdate', $sdate)
                ->where('studio_id', $sid)
                ->select('slot_id');
            if ($isEdit && $bid) {
                $q->where('booking_id', '!=', $bid);
            }
        })
            ->whereNotExists(function ($q) use ($sdate, $sid, $isEdit, $bid) {
                $q->from('bookings')
                    ->whereIn('booking_status', ['1', '0'])
                    ->whereDate('booking_start_date', $sdate)
                    ->where('studio_id', $sid)
                    ->whereColumn('bookings.start_at', '<', 'slots.end_at')
                    ->whereColumn('bookings.end_at', '>', 'slots.start_at');
                if ($isEdit && $bid) {
                    $q->where('id', '!=', $bid);
                }
            });

        if ($sdate == date('Y-m-d')) {
            $query->where(function ($q) use ($sdate, $currentTime) {
                $q->where('start_at', '>=', $currentTime);
            });
        }
        // Optional: Add time-based constraints if "mode" is passed
        if ($request->has('mode') && $request->mode) {
            $query->whereBetween('start_at', [$opens, $close]);
        }

        // Execute the query and get the slots
        $slots = $query->get();

        // Modify the returned slots, adding the date field
        $modifiedObjects = $slots->map(function ($slot) use ($sdate) {
            $slot->date = $sdate; // Add the 'date' field
            return $slot;
        });

        // Return the response with success and data
        return response()->json([
            'success' => 1,
            'data' => $modifiedObjects,
            $currentTime
        ]);
    }

    public function find_end_slot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sdate' => 'required',
            "studio_id" => "required",
            "slot_id" => "required"
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
        $slot_id = $request->slot_id;
        $sid = $request->studio_id;
        $booking_id = $request->booking_id ?? 0;
        $slot = Slot::where('id', $slot_id)->first();
        $sdate = Carbon::parse($request->sdate)->format('Y-m-d');
        $start_time = $slot->start_at;
        // $bsdate = date('Y-m-d H:i:s', strtotime($sdate.' '.$start_time));
        $bsdate = Carbon::parse($sdate . ' ' . $start_time)->minute(0)->second(0)->format('Y-m-d H:i:s');

        $arr = [];
        $hours = $request->mode || $request->expectsJson() ? 24 : 720;
        for ($i = 1; $i <= $hours; $i++) {
            // $bedate = date('Y-m-d H:0:0', strtotime($bsdate)+$i*3600);
            $bedate = Carbon::parse($bsdate)->addHours($i)->minute(0)->second(0)->format('Y-m-d H:i:s');
            $inndata =  [
                'booking_start_date' => $bsdate,
                'booking_end_date' => $bedate,
                'studio_id' => $sid,
                'booking_status' => '0'
            ];
            $innerBook = Booking::where($inndata)->where('id', '!=', $booking_id)->count();
            $outerBook = Booking::where('booking_start_date', '>', $bsdate)->where('booking_start_date', '<', $bedate)->where('studio_id', $sid)->where('booking_status',  '0')->where('id', '!=', $booking_id)->count();
            #$lcrosBook = Booking::where('booking_end_date', '>', $bsdate)->where('studio_id', $sid)->count();
            #$ucrosBook = Booking::where('booking_start_date', '>', $bedate)->where('studio_id', $sid)->count();
            $overlappingBookings = Booking::where('studio_id', $sid)->where('id', '!=', $booking_id)
                ->whereIn('booking_status', ['1', '0'])
                ->where(function ($query) use ($bsdate, $bedate) {
                    $query->where(function ($q) use ($bsdate, $bedate) {
                        $q->where('booking_start_date', '<', $bedate)
                            ->where('booking_end_date', '>', $bsdate);
                    });
                })
                ->count();
            $sum = $innerBook + $outerBook + $overlappingBookings;
            if ($sum == 0) {
                array_push($arr, $bedate);
            }
        }
        $res = [
            'success' => 1,
            'data' => $arr
        ];
        return response()->json($res);
    }
    public function get_services(Request $request)
    {
        $request->validate([
            'studio_id' => 'required'
        ]);
        $stid = $request->studio_id;
        $services = Service::whereIn('id', function ($q) use ($stid) {
            $q->from('service_studios')->select('service_id')->where('studio_id', $stid);
        })->get();
        $output = "<option value=''>All</option>";
        foreach ($services as $s) {
            $output .= "<option value='{$s->id}'>{$s->name}</option>";
        }
        echo $output;
    }
    public function get_user(Request $request)
    {
        $request->validate([
            'mobile' => 'required'
        ]);
        $mobile = $request->mobile;
        $user = User::where('mobile', 'LIKE', "%{$mobile}%")->first();
        $data = [
            'data' => $user,
            'success' => $user ? 1 : 0
        ];
        return response()->json($data, 200);
    }
    public function get_images(Request $request)
    {
        $request->validate([
            'studio_id' => 'required|exists:studios,id'
        ]);
        $sid = $request->studio_id;
        $images = StudioImage::where('studio_id', $sid)->get();
        $output = "<ul class='list-unstyled d-flex flex-wrap gap-1'>";
        foreach ($images as $img) {
            $irl = url('public/' . $img->image);

            $output .= "<li class='position-relative'>
                            <img src='{$irl}' width='100'>
                            <button onclick='deleteImage({$img->id})' class='position-absolute top-0 end-0 btn btn-close opacity-100'></button>
                        </li>";
        }
        $output .= "</ul>";
        return $output;
    }
    public function delete_images(Request $request)
    {
        $request->validate([
            'image_id' => 'required'
        ]);
        $id = $request->image_id;
        $studio = StudioImage::where('id', $id)->first();
        $data = [
            'data' => $studio->studio_id,
            'success' => 1
        ];
        StudioImage::where('id', $id)->delete();
        return response()->json($data);
    }
    public function add_image(Request $request)
    {
        $request->validate([
            'studio_id' => 'required|exists:studios,id',
        ]);
        $sid = $request->studio_id;
        $file = $request->file('file');
        $filename = Str::uuid() . $file->getClientOriginalName();
        $file->move(public_path('uploads'), $filename);
        $idata  = [
            'image' => 'uploads/' . $filename,
            'studio_id' => $sid
        ];
        StudioImage::insert($idata);
        return response()->json(['data' => $sid, 'success' => 1]);
    }
    public function set_permissiable(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:service_studios,id',
        ]);
        $id = $request->id;
        $item = DB::table('service_studios')->where('id', $id)->first();
        $perms = $item->is_permissable == "0" ? "1" : "0";
        DB::table('service_studios')->where('id', $id)->update(['is_permissable' => "{$perms}"]);
        return true;
    }
    public function get_rest_services(Request $request)
    {
        $request->validate([
            'studio_id' => 'required|exists:studios,id',
        ]);
        $sid = $request->studio_id;
        $itm = DB::table('service_studios')->where('studio_id', $sid)->select('service_id')->get();

        $items = Service::whereNotIn('id', function ($q) use ($sid) {
            $q->from('service_studios')->where('studio_id', $sid)->select('service_id');
        })->get();

        $output = '<option value="">---Select----</option>';
        foreach ($items as $t) {
            $output .= '<option value="' . $t->id . '">' . $t->name . '</option>';
        }
        return $output;
    }
    public function get_rent_items(Request $request)
    {
        $request->validate([
            'sid' => 'required|exists:studios,id',
        ]);
        $sid = $request->sid;
        $items = Rent::whereIn('id', function ($query) use ($sid) {
            $query->from('charges')->select('item_id')->where('studio_id', $sid)->where('type', 'Item');
        })->get();

        $output = '<option value="">---Select----</option>';
        foreach ($items as $t) {
            $output .= '<option value="' . $t->id . '">' . $t->name . '</option>';
        }
        return $output;
    }
    public function web_notification()
    {
        $itms = RbNotification::whereIn('type', ['Booking', 'Payment']);
        if (Auth::user()->role != "Super") {
            $itms->where('vendor_id', Auth::user()->vendor_id);
        }
        $itms->where('is_read', '0')->with('user');
        $itms->orderBy('is_read', 'asc');
        $itms->orderBy('created_at', 'desc');
        $items = $itms->paginate(10);
        return response()->json($items);
    }
}
