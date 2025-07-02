<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\RbTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    use RbTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = "List of transactions";
        $items = Transaction::where('amount', '>', 0);

        // Role-based filtering
        if (Auth::user()->role !== "Super") {
            $items->where('vendor_id', Auth::user()->vendor_id);
        }
        // Optional filters
        $user = $request->input('user');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $booking_id = $request->input('booking_id');
        $studio = $request->input('studio');
        $vendor = $request->input('vendor');
        if ($user) {
            $items->whereHas('user', function ($q) use ($user) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('name', 'LIKE', "%{$user}%")
                        ->orWhere('email', 'LIKE', "%{$user}%")
                        ->orWhere('mobile', 'LIKE', "%{$user}%");
                });
            });
        }

        if ($booking_id) {
            $items->where('booking_id', $booking_id);
        }

        if ($from_date) {
            $items->whereDate('transaction_date', '>=', $from_date);
        }

        if ($to_date) {
            $items->whereDate('transaction_date', '<=', $to_date);
        }
        if ($vendor) {
            $items->where(function ($query) use ($vendor) {
                $query->where('vendor_id', 'LIKE', "%{$vendor}%")
                    ->orWhereHas('vendor', function ($q) use ($vendor) {
                        $q->where('name', 'LIKE', "%{$vendor}%");
                    });
            });
        }
        if ($studio) {
            $items->where(function ($query) use ($studio) {
                $query->where('studio_id', 'LIKE', "%{$studio}%")
                    ->orWhereHas('vendor', function ($q) use ($studio) {
                        $q->where('name', 'LIKE', "%{$studio}%");
                    });
            });
        }

        // Eager load relationships
        $transactions = $items->with(['user', 'booking'])
            ->orderBy('id', 'DESC')
            ->paginate(40);

        $res = compact('title', 'transactions');

        if ($request->expectsJson()) {
            return response()->json(['data' => $res, 'success' => 1]);
        }

        return view('admin.reports.transactions', $res);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'booking_id' => 'required',
            'transaction_id' => 'nullable|unique:transactions'
        ]);
        $bid = $request->booking_id;
        $booking = Booking::where('id', $bid)->first();
        if (!$booking) {
            if ($request->wantsJson()) {
                return response()->json(['success' => '0', 'message' => 'Booking not found']);
            } else {
                return redirect()->back()->with('success', 'Transaction not found');
            }
        }
        $data = [
            'transaction_id' => $request->transaction_id,
            'mode' => $request->mode,
            'booking_id' => $bid,
            'user_id' => $booking?->user_id,
            'vendor_id' => $booking->vendor_id,
            'studio_id' => $booking->studio_id,
            'amount' => $request->amount,
            'type' => 'Credit',
            'order_id' => date('Ymdhis'),
            'transaction_date' => $request->transaction_date,
            'status' => 'Success',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $amount = $request->amount;
        if (Transaction::insert($data)) {
            $notmessage = "We’ve received your payment of (₹{$amount})/- Your booking is confirmed. See you at the studios. ";
            $item =  Booking::where('id', $bid)->with('rents')->withSum('transactions', 'amount')->with('studio')->with('vendor')->with('service')->with('user')->first();
            $ndata = [
                'user_id' => $item->user->id,
                'booking_id' => $bid,
                'studio_id' => $item->studio_id,
                'vendor_id' => $item->vendor_id,
                'title' => 'Payment Received',
                'message' => $notmessage,
                "is_read" => "0",
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'Payment'
            ];
            DB::table('notifications')->insert($ndata);
            $rents =  $item->rents;
            $arr = [];
            foreach ($rents as $r) {
                array_push($arr, $r->pivot->charge * $r->pivot->uses_hours);
            }
            $rentcharge = array_sum($arr);
            Booking::where('id', $bid)->update(['booking_status' => '1']);
            $remainamount =  $item->duration * $item->studio_charge + $rentcharge - $item->transactions_sum_amount - floatval($item->promo_discount_calculated);
            if (ceil($remainamount) <= 1) {
                Booking::where('id', $bid)->update(['payment_status' => '1', 'booking_status' => '1']);
            }

            if ($item->user && $item->user->fcm_token) {
                $this->send_notification($item->user->fcm_token, 'Payment Received', $notmessage, $booking->user_id, 'Payment');
            }
            if ($request->wantsJson()) {
                return response()->json(['success' => '1', 'message' => 'Transaction Created Successfully']);
            } else {
                return redirect()->back()->with('success', 'Transaction Created Successfully');
            }
        }
    }
    public function success_page($id)
    {
        $transaction = Transaction::where('order_id', $id)->first();
        $res = compact('transaction');
        return view('admin.bookings.success', $res);
    }
    public function success_page_order_id($type, $order_id)
    {

        $transaction = Transaction::where('order_id', $order_id)->first();
        // return response()->json($transaction);
        // die;
        $res = compact('transaction');
        return view('admin.bookings.success', $res);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}