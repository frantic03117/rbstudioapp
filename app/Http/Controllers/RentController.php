<?php

namespace App\Http\Controllers;

use App\Models\Rent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RentController extends Controller
{
    function __construct()
    {
        $this->middleware(['permission:product-list|product-create|product-edit|product-delete'], ['only' => ['index', 'store']]);
        $this->middleware(['permission:product-create'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:product-edit'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:product-delete'], ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() : \Illuminate\View\View
    {
        $title = "List of Rental Resources";
        $rents = Rent::all();
        //return response()->json($services);
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
    public function store(Request $request) : RedirectResponse
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
    public function edit(Rent $rent) : \Illuminate\View\View
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
    public function update(Request $request, Rent $rent) : RedirectResponse
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rent  $rent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rent $rent) : RedirectResponse
    {
        $rent->delete();
        return redirect()->route('services.index')->with('success', 'Deleted Successfully');
    }
}
