<?php

namespace App\Http\Controllers;

use App\Models\Location\City;
use App\Models\Location\Country;
use App\Models\Location\State;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class VendorController extends Controller
{
    use HasRoles;
    function __construct()
    {
        $this->middleware(['permission:vendors-list|vendors-create|vendors-edit|vendors-delete'], ['only' => ['index', 'store']]);
        $this->middleware(['permission:vendors-create'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:vendors-edit'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:vendors-delete'], ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $vendor = Vendor::all();
        $title = "List of Vendors";
        $data = compact("vendor", "title");
        if ($request->expectsJson()) {
            return response()->json(['data' => $vendor, 'message' => $title, 'success' => '1']);
        }
        return view("admin.vendors.index", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): \Illuminate\View\View
    {
        $title = "Create New Vendor";
        $country = Country::all();
        return view("admin.vendors.create", compact("title", "country"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|min:10|max:10|unique:users,mobile',
            'name' => 'required|max:100|min:4',
            'business_name' => 'required|min:4',
            'bill_prefix' => 'required|unique:vendors,bill_prefix'
        ]);
        $data = [
            'name' => $request->name,
            'business_name' => $request->business_name,
            'address' => $request->address,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'district_id' => $request->district_id,
            'bill_prefix' => $request->bill_prefix,
            'city' => $request->city,
            'pincode' => $request->pincode,
            'google_map' => $request->google_map,
            'register_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $vid = Vendor::insertGetId($data);
        $udata = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'remember_token' => $request->password,
            'mobile' => $request->mobile,
            'role' =>   'Admin',
            'vendor_id' => $vid,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = User::create($udata);

        if ($user->assignRole(6)) {
            return redirect()->route('vendor.create')->with('success', 'Created Successfully');
        } else {
            return redirect()->route('vendor.create')->with('error', 'Internal Error occured');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function show(Vendor $vendor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function edit(Vendor $vendor)
    {
        $title = "Edit Vendor Details";
        $countries = Country::all();
        $user = User::where(['vendor_id' => $vendor->id, 'role' => 'Admin'])->first();

        $states = State::where('country_id', $vendor->country_id)->get();
        $cities = City::where('state_id', $vendor->state_id)->get();
        $res = compact("vendor", "countries", "title",  "states", "cities", "user");
        return view("admin.vendors.edit", $res);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vendor $vendor)
    {
        date_default_timezone_set('Asia/kolkata');
        $user = User::where('vendor_id', $vendor->id)->where('role', 'Admin')->first();
        $request->validate([
            'name' => 'required|max:100|min:4',
            'business_name' => 'required|min:4',
            'bill_prefix' => 'required|unique:vendors,bill_prefix,' . $vendor->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'required|unique:users,mobile,' . $user->id
        ]);
        $data = [
            'name' => $request->name,
            'business_name' => $request->business_name,
            'address' => $request->address,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'district_id' => $request->district_id,
            'bill_prefix' => $request->bill_prefix,
            'city' => $request->city,
            'pincode' => $request->pincode,
            'google_map' => $request->google_map,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        Vendor::where('id', $vendor->id)->update($data);
        $user = User::where('vendor_id', $vendor->id)->where('role', 'Admin')->first();
        $isExists = User::where('mobile', $request->mobile)->where('id', '!=', $user->id)->first();

        if ($isExists) {
            return redirect()->back()->with('error', 'Vendor failed to update');
        } else {
            $udata = [
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'role' =>   'Admin',
                'vendor_id' => $vendor->id,
            ];
            if ($request->password) {
                $udata['password'] = Hash::make($request->password);
                $udata['remember_token'] = $request->password;
            }


            // echo json_encode($user);
            // die;
            $user->assignRole('Vendor');
            $user->removeRole('Admin');
            $user->removeRole('Super');


            User::where('vendor_id', $vendor->id)->where('role', 'Admin')->update($udata);
            return redirect()->back()->with('success', 'Vendor Updated Successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vendor $vendor)
    {
        //
    }
}
