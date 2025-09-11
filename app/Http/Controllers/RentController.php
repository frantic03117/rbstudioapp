<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Rent;
use App\Models\Studio\Charge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RentController extends Controller
{
    // function __construct()
    // {
    //     $this->middleware(['permission:product-list|product-create|product-edit|product-delete'], ['only' => ['index', 'store']]);
    //     $this->middleware(['permission:product-create'], ['only' => ['create', 'store']]);
    //     $this->middleware(['permission:product-edit'], ['only' => ['edit', 'update']]);
    //     $this->middleware(['permission:product-delete'], ['only' => ['destroy']]);
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $title = "List of Rental Resources";
        $qrents = Rent::query();
        $studio_id = $_GET['studio_id'] ?? null;
        if ($studio_id) {
            $qrents->whereIn('id', function ($q) use ($studio_id) {
                $q->from('charges')->where('studio_id', $studio_id)->select('item_id');
            });
        }
        $rents = $qrents->get();
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'title' => $title,
                'data' => $rents,
                'success' => 1
            ]);
        }
        return view("admin.rents.index", compact("title", "rents"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = "Create New Resource";
        return view("admin.rents.create", compact("title"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(
            [
                "name" => "required|unique:rents,name",
                "icon" => 'required|image|mimes:png,jpg,jpeg|max:1024'
            ]
        );
        $icon = $request->file('icon');
        $iconname = date('Ymd-his') . mt_rand(0, 10000) . $icon->getClientOriginalName();
        $icon->move(public_path('uploads/'), $iconname);

        $data = [
            'name' => $request->name,
            'icon' => 'public/uploads/' . $iconname,
            'description' => $request->description,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (Rent::insert($data)) {
            return redirect()->route('rents.create')->with('success', 'Created Successfully');
        }
        return redirect()->route('rents.create')->with('success', 'Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rent  $rent
     * @return \Illuminate\Http\Response
     */
    public function show(Rent $rent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rent  $rent
     * @return \Illuminate\Http\Response
     */
    public function edit(Rent $rent): \Illuminate\View\View
    {
        $title = "Edit Resource";
        return view("admin.rents.edit", compact("title", "rent"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rent  $rent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Rent $rent): RedirectResponse
    {
        $request->validate(
            [
                "name" => "required|unique:services,name," . $rent->id,
            ]
        );
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $iconname = date('Ymd-his') . mt_rand(0, 10000) . $icon->getClientOriginalName();
            $icon->move(public_path('uploads/'), $iconname);
        }

        $data = [
            'name' => $request->name,
            'icon' => $request->hasFile('icon') ?  'public/uploads/' . $iconname : $rent->icon,
            'description' => $request->description,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($rent->update($data)) {
            return redirect()->back()->with('success', 'Created Successfully');
        }
        return redirect()->back()->with('success', 'Created Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rent  $rent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rent $rent): RedirectResponse
    {
        $rent->delete();
        return redirect()->route('services.index')->with('success', 'Deleted Successfully');
    }
    public static function findRentalItems($id)
    {


        $bookingId = $id;

        // Get booking start/end date and studio in one query
        $booking = Booking::select('booking_start_date', 'booking_end_date', 'studio_id')
            ->where('id', $bookingId)
            ->first();

        // If booking not found (should not happen due to exists validation)
        if (!$booking) {
            return response()->json(['success' => 0, 'message' => 'Invalid booking id'], 404);
        }


        $overlappingBookings = Booking::where('studio_id', $booking->studio_id)
            ->where('id', '!=', $id) // exclude current booking
            ->where(function ($q) use ($booking) {
                $q->where('booking_start_date', '<', $booking->booking_end_date)
                    ->where('booking_end_date', '>', $booking->booking_start_date);
            })
            ->pluck('id');
        $blockedItems = BookingItem::whereIn('booking_id', $overlappingBookings)
            ->pluck('item_id');

        $items = Charge::where('studio_id', $booking->studio_id)
            ->whereNotIn('item_id', $blockedItems)
            ->whereNotIn('item_id', function ($q) use ($id) {
                $q->from('booking_items')
                    ->where('booking_id', $id)
                    ->select('item_id');
            })
            ->with('item')
            ->get();

        return  $items;
    }
    public function findRentalItemsApi(Request $request, $id)
    {
        $booking = Booking::select('booking_start_date', 'booking_end_date', 'studio_id')
            ->where('id', $id)
            ->first();

        if (!$booking) {
            return response()->json(['success' => 0, 'message' => 'Invalid booking id'], 404);
        }

        // Find overlapping bookings in the same studio
        $overlappingBookings = Booking::where('studio_id', $booking->studio_id)
            ->where('id', '!=', $id) // exclude current booking
            ->where(function ($q) use ($booking) {
                $q->where('booking_start_date', '<', $booking->booking_end_date)
                    ->where('booking_end_date', '>', $booking->booking_start_date);
            })
            ->pluck('id');

        // Get all items booked in those overlapping bookings
        $blockedItems = BookingItem::whereIn('booking_id', $overlappingBookings)
            ->pluck('item_id');

        // Fetch available items for this booking's studio, excluding blocked + already added
        $items = Charge::where('studio_id', $booking->studio_id)
            ->whereNotIn('item_id', $blockedItems)
            ->whereNotIn('item_id', function ($q) use ($id) {
                $q->from('booking_items')
                    ->where('booking_id', $id)
                    ->select('item_id');
            })
            ->with('item')
            ->get();

        return response()->json([
            'data' => $items,
            'success' => 1,
            'message' => 'List of rental items'
        ]);
    }
}
