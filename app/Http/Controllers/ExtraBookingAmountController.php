<?php

namespace App\Http\Controllers;

use App\Models\ExtraBookingAmount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExtraBookingAmountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'booking_id' => 'required|exists:bookings,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('_token');
        if (ExtraBookingAmount::insert($data)) {
            $resp = ['success' => 1, 'message' => 'Extra booking amount added'];

            return $request->expectsJson()
                ? response()->json($resp)
                : redirect()->back()->with('success', $resp['message']);
        }
        #return redirect()->back()->with('success', 'Extra booking amount added');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExtraBookingAmount  $extraBookingAmount
     * @return \Illuminate\Http\Response
     */
    public function show(ExtraBookingAmount $extraBookingAmount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExtraBookingAmount  $extraBookingAmount
     * @return \Illuminate\Http\Response
     */
    public function edit(ExtraBookingAmount $extraBookingAmount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExtraBookingAmount  $extraBookingAmount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExtraBookingAmount $extraBookingAmount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExtraBookingAmount  $extraBookingAmount
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExtraBookingAmount $extraBookingAmount, $id)
    {
        ExtraBookingAmount::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Amount removed successfully');
    }
}
