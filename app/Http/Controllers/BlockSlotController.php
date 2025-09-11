<?php

namespace App\Http\Controllers;

use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\Slot;
use App\Models\Studio\Studio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlockSlotController extends Controller
{
    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        $today = Carbon::now()->format('Y-m-d');

        $studios = Studio::select(['id', 'name'])->get();
        $slots   = Slot::orderBy('start_at', 'asc')->get();
        $reason = $_GET['reason'] ?? null;

        $query = BlockedSlot::orderBy('bdate', 'DESC')->where('reason', '!=', 'booking')
            ->orderBy('slot_id', 'asc')
            ->whereDate('bdate', '>=', $today)
            ->with('slot')
            ->with('studio:id,name');
        if ($reason) {
            $query->where('reason', $reason);
        }

        if ($request->filled('studio_id') && $request->studio_id !== 'All') {
            $query->where('studio_id', $request->studio_id);
        }


        if ($request->filled('bdate')) {
            $query->whereDate('bdate', $request->bdate);
        }

        $items = $query->paginate(20);

        $title = "List of blocked slots";
        $bdate = $_GET['bdate'] ?? null;
        $sid = $_GET['studio_id'] ?? null;
        $res   = compact('items', 'title', 'studios', 'slots', 'bdate', 'sid', 'reason');
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $items,
                'success' => 1,
                'message' => 'List of blocked slots'
            ]);
        }
        return view('admin.blocked_slot.blocked_slot', $res);
    }

    public function store(Request $request)
    {
        $rules = [
            'studio_id'  => 'required|array',
            'from_date'  => 'required|date',
            'to_date'    => 'required|date|after_or_equal:from_date',
            'start_time' => 'required|integer|exists:slots,id',
            'end_time'   => 'required|integer|exists:slots,id',
        ];

        $validated = $request->validate($rules);

        // Expand studios if "All" is selected
        $studioIds = in_array('All', $validated['studio_id'])
            ? Studio::pluck('id')->toArray()
            : $validated['studio_id'];

        // Get slot objects
        $startSlot = Slot::findOrFail($validated['start_time']);
        $endSlot   = Slot::findOrFail($validated['end_time']);

        // Build full datetime range
        $startDatetime = new \DateTime("{$validated['from_date']} {$startSlot->start_at}");
        $endDatetime   = new \DateTime("{$validated['to_date']} {$endSlot->end_at}");

        // Get all slots
        $allSlots = Slot::all();

        // Loop dates between from_date and to_date
        $period = new \DatePeriod(
            new \DateTime($validated['from_date']),
            new \DateInterval('P1D'),
            (new \DateTime($validated['to_date']))->modify('+1 day')
        );

        foreach ($period as $date) {
            foreach ($studioIds as $studioId) {
                foreach ($allSlots as $slot) {
                    $slotStart = new \DateTime("{$date->format('Y-m-d')} {$slot->start_at}");
                    $slotEnd   = new \DateTime("{$date->format('Y-m-d')} {$slot->end_at}");

                    // Keep slots that overlap with range
                    if ($slotStart >= $startDatetime && $slotEnd <= $endDatetime) {
                        BlockedSlot::firstOrCreate(
                            [
                                'studio_id' => $studioId,
                                'slot_id'   => $slot->id,
                                'bdate'     => $date->format('Y-m-d'),
                            ],
                            [
                                'booking_id' => 0,
                                'reason'     => 'other',
                            ]
                        );
                    }
                }
            }
        }

        return $request->expectsJson()
            ? response()->json(['success' => 1, 'message' => 'Blocked slots saved successfully'])
            : redirect()->back()->with('success', 'Blocked slots saved successfully!');
    }


    public function add_buffer_time(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if (!$booking) {
            return response()->json(['success' => 1, 'message' => "Not found"]);
            // return redirect()->back()->with('error', 'Booking not found');
        }
        return response()->json($booking);
        // get the last booked slot (by date + slot_id)
        $bookedSlot = BlockedSlot::where('booking_id', $id)
            ->orderBy('bdate', 'desc')
            ->orderBy('slot_id', 'desc')
            ->first();
        return response()->json($bookedSlot);
        die;
        if (!$bookedSlot) {
            return redirect()->back()->with('error', 'No blocked slots found');
        }

        // logic: if slot < 25 → next slot on same day, else move to next day slot 1
        $nextSlotToBlock = $bookedSlot->slot_id < 25 ? $bookedSlot->slot_id + 1 : 1;
        $blockDate = $bookedSlot->slot_id < 25
            ? $bookedSlot->bdate
            : date('Y-m-d', strtotime($bookedSlot->bdate . ' +1 day'));

        // create the buffer slot
        BlockedSlot::create([
            'studio_id'  => $booking->studio_id,
            'booking_id' => $booking->id,
            'slot_id'    => $nextSlotToBlock,
            'bdate'      => $blockDate,
            'reason'     => 'buffer',
        ]);
        return $request->expectsJson()
            ? response()->json(['success' => 1, 'message' => 'Buffer time added successfully'])
            : redirect()->back()->with('success', 'Buffer time added successfully!');
    }
    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No slots selected for deletion.');
        }

        BlockedSlot::whereIn('id', $ids)
            ->where('reason', '!=', 'booking')
            ->delete();
        return $request->expectsJson()
            ? response()->json(['success' => 1, 'message' => 'Blocked slots deleted successfully'])
            : redirect()->back()->with('success', 'Blocked slots deleted successfully!');
    }
    public function destroy(Request $request, $id)
    {
        BlockedSlot::where('id', $id)->where('reason', '!=', 'booking')->delete();
        return $request->expectsJson()
            ? response()->json(['success' => 1, 'message' => 'Blocked slots deleted successfully'])
            : redirect()->back()->with('success', 'Blocked slots deleted successfully!');
    }
}
