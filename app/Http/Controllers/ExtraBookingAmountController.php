<?php

namespace App\Http\Controllers;

use App\Models\ExtraBookingAmount;
use Illuminate\Http\Request;

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
        $request->validate([
            'amount' => 'required|numeric',
            'booking_id' => 'required|exists:bookings,id'
        ]);
        $data = $request->except('_token');
        ExtraBookingAmount::insert($data);
        return redirect()->back()->with('success', 'Extra booking amount added');
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