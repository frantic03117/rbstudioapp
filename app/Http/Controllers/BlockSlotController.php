<?php

namespace App\Http\Controllers;

use App\Models\BlockedSlot;
use App\Models\Slot;
use App\Models\Studio\Studio;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BlockSlotController extends Controller
{
    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        $today = Carbon::now()->format('Y-m-d');

        $studios = Studio::select(['id', 'name'])->get();
        $slots   = Slot::orderBy('start_at', 'asc')->get();

        $query = BlockedSlot::orderBy('bdate', 'DESC')
            ->orderBy('slot_id', 'asc')
            ->whereDate('bdate', '>=', $today)
            ->where('reason', 'other')
            ->with('slot')
            ->with('studio:id,name');


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
        $res   = compact('items', 'title', 'studios', 'slots', 'bdate', 'sid');

        return view('admin.blocked_slot.blocked_slot', $res);
    }

    public function store(Request $request)
    {
        $studioIds = $request->input('studio_id', []);
        $slotIds   = $request->input('slot_id', []);
        $bdate     = $request->input('bdate');

        // If "All" is selected for studios, fetch all studio IDs
        if (in_array('All', $studioIds)) {
            $studioIds = Studio::pluck('id')->toArray();
        }

        // If "All" is selected for slots, fetch all slot IDs
        if (in_array('All', $slotIds)) {
            $slotIds = Slot::pluck('id')->toArray();
        }

        // Store combinations
        foreach ($studioIds as $studioId) {
            foreach ($slotIds as $slotId) {
                BlockedSlot::create([
                    'studio_id' => $studioId,
                    'slot_id'   => $slotId,
                    'booking_id' => 0,
                    'bdate'     => $bdate,
                    'reason'    => 'other',
                ]);
            }
        }
        if ($request->expectsJson()) {
            return response()->json(['success' => 1, 'message' => 'Blocked saved successfully']);
        } else {
            return redirect()->back()->with('success', 'Blocked slots saved successfully!');
        }
    }
}
