<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\Request;
use App\Models\Studio\Studio;
use Illuminate\Support\Facades\Auth;

class PromoCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cods = PromoCode::where('deleted_at', '=', null);
        if (Auth::user()->role != "Super") {
            $cods->where('created_by', Auth::user()->vendor_id);
        }
        $codes = $cods->with('studio')->with('user')->orderBy('id', 'DESC')->get();
        $title = "Promo Code Management";
        $stds = Studio::where('id', '>', '0');
        if (Auth::user()->role != "Super") {
            $stds->where('vendor_id', Auth::user()->vendor_id);
        }
        if (auth('sanctum')->user()->role != "Super") {
            $stds->where('vendor_id', auth('sanctum')->user()->vendor_id);
        }
        $studios = $stds->get();
        $res = compact('codes', 'title', 'studios');
        if ($request->expectsJson()) {
            $data = [
                'data' => $codes,
                'success' => 1,
                'message' => $title
            ];
            return response()->json($data);
        }
        return view('admin.promocodes', $res);
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
            'promo_code' => 'required',
            'start_at' => 'required',
            'end_at' => 'required',
            'studio_id' => 'required'
        ]);
        if ($request->studio_id != "all") {
            $data = $request->except('_token');
            $data['created_by'] = Auth::user()->role != "Super" ? Auth::user()->vendor_id : "0";
            if (auth('sanctum')->user()) {
                $data['created_by'] = auth('sanctum')->user()->role != "Super" ? auth('sanctum')->user()->vendor_id : "0";
            }
            PromoCode::insert($data);
            return redirect()->back()->with('success', 'Promo Code created successfully');
        }
        if ($request->studio_id == "all") {
            $studios = Studio::select('id')->get();
            foreach ($studios as $st) {
                $data = $request->except('_token');
                $data['studio_id'] = $st->id;
                $data['created_by'] = Auth::user()->role != "Super" ? Auth::user()->vendor_id : "0";
                if (auth('sanctum')->user()) {
                    $data['created_by'] = auth('sanctum')->user()->role != "Super" ? auth('sanctum')->user()->vendor_id : "0";
                }
                PromoCode::insert($data);
            }
            return redirect()->back()->with('success', 'Promo Code created successfully');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PromoCode  $promoCode
     * @return \Illuminate\Http\Response
     */
    public function show(PromoCode $promoCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PromoCode  $promoCode
     * @return \Illuminate\Http\Response
     */
    public function edit(PromoCode $promoCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PromoCode  $promoCode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PromoCode $promoCode)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PromoCode  $promoCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        PromoCode::where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        if ($request->expectsJson()) {
            return response()->json(['data' => null, 'success' => 1, 'message' => 'Deleted successfully']);
        } else {
            return redirect()->back()->with('success', 'Promo Code deleted successfully');
        }
    }
}
