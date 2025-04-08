<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
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
    public function index()
    {
        $title = "List of transactions";
        $items = Transaction::where('amount', '>', '0');
        if (Auth::user()->role != "Super") {
            $items->where('vendor_id', Auth::user()->vendor_id);
        }
        $transactions = $items->with('user')->with('booking')->orderBy('id', 'DESC')->paginate(40);
        $res = compact('title', 'transactions');
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
        $data = [
            'transaction_id' => $request->transaction_id,
            'mode' => $request->mode,
            'booking_id' => $bid,
            'user_id' => $booking->user_id,
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
            $notmessage = "Payment Received!! We’ve received your payment of (₹{$amount})/- Your booking is confirmed. See you at the studios. ";
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
            return redirect()->back()->with('success', 'Transaction Created Successfully');
        }
    }
    public function success_page($id)
    {
        $transaction = Transaction::where('id', $id)->first();
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