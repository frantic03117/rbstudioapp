<?php

namespace App\Http\Controllers;

use App\Models\Studio\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    function __construct()
    {
        $this->middleware(['permission:services-list|services-create|services-edit|services-delete'], ['only' => ['index', 'store']]);
        $this->middleware(['permission:services-create'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:services-edit'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:services-delete'], ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = "List of Services";
        $squery = Service::query();
        $studio_id = $_GET['studio_id'] ?? null;
        if ($studio_id) {
            $squery->whereIn('id', function ($query) use ($studio_id) {
                $query->from('service_studios')->where('studio_id', $studio_id)->select('service_id');
            });
        }
        $service = $squery->get();
        if ($request->expectsJson()) {
            return response()->json(['data' => $service, 'message' => $title, 'success' => 1]);
        }
        //return response()->json($services);
        //dd(auth()->user()->getRoleNames());
        return view("admin.services.index", compact("title", "services"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): \Illuminate\View\View
    {
        $title = "Create New Service";
        return view("admin.services.create", compact("title"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(
            [
                "name" => "required|unique:services,name",
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
        Service::insert($data);
        return redirect()->route('services.create')->with('success', 'Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Studio\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function show(Service $service)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Studio\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function edit(Service $service): \Illuminate\View\View
    {
        $title = "Edit Service";
        return view("admin.services.edit", compact("title", "service"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Studio\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Service $service): RedirectResponse
    {

        $request->validate(
            [
                "name" => "required|unique:services,name," . $service->id,
            ]
        );
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $iconname = date('Ymd-his') . mt_rand(0, 10000) . $icon->getClientOriginalName();
            $icon->move(public_path('uploads/'), $iconname);
        }

        $data = [
            'name' => $request->name,
            'icon' => $request->hasFile('icon') ?  'public/uploads/' . $iconname : $service->icon,
            'description' => $request->description,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($service->update($data)) {
            return redirect()->back()->with('success', 'Created Successfully');
        }
        return redirect()->back()->with('success', 'Created Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Studio\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Deleted Successfully');
    }
}
