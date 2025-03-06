<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = "Setting";
        $items = Setting::all();
        $setting = new Setting();
        $method = "POST";
        $url = route('setting.store');
        $res = compact('title', 'items', 'setting', 'url', 'method');
        return view('admin.setting', $res);
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
            'col_name' => 'required|unique:settings,col_name',
            'col_val' => 'required'
        ]);
        $data = $request->except(['_token', '_method']);
        Setting::insert($data);
        return redirect()->back()->with('success', 'Setting created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function show(Setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function edit(Setting $setting)
    {
        $title = "Setting";
        $items = Setting::all();
        $method = "PUT";
        $url = route('setting.update', $setting->id);
        $res = compact('title', 'items', 'setting', 'url', 'method');
        return view('admin.setting', $res);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Setting $setting)
    {

        $request->validate([
            'col_name' => 'required|unique:settings,col_name,' . $setting->id . ',id',
            'col_val' => 'required'
        ]);


        $data = $request->except(['_token', '_method']);

        Setting::where('id', $setting->id)->update($data);
        return redirect()->route('setting.index')->with('success', 'Setting created successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Setting $setting)
    {
        //
    }
}