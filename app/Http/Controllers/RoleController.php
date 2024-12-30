<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    function __construct()
    {
        $this->middleware(['permission:role-list|role-create|role-edit|role-delete'], ['only' => ['index', 'store']]);
        $this->middleware(['permission:role-create'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:role-edit'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:role-delete'], ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): \Illuminate\View\View
    {
        $title = "List of Roles";
        $aid = Auth::user()->vendor_id;
        
        $rls = Role::orderBy('id', 'DESC');
        if(Auth::user()->role != 'Super'){
           $rls->where('vendor_id', $aid);
        }
        if(Auth::user()->role == 'Super'){
           $rls->where('vendor_id', '<', '1');
        }
        $roles = $rls->paginate(50);
       
        
        return view('admin.roles.index', compact('roles', 'title'));
    }
   
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): \Illuminate\View\View
    {
        $title = 'Create Role';
        $permission = Permission::get();
        $aid = (Auth::user()->role == 'Super') ? 0 : (Auth::user()->vendor_id);
        $cnames = Permission::distinct('cname')->select('cname')->get();
        $permission = [];
        foreach ($cnames as $cname) {
            $mitems = $cname;
            $items = Permission::where('cname', $cname['cname'])->get();
            $mitems['td'] = $items;
            array_push($permission, $mitems);
        }
        return view('admin.roles.create', compact('permission', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
       
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);
        $aid = (Auth::user()->role == 'Super') ? "0" : (Auth::user()->vendor_id);
        $check = Role::where(['name' => $request->input('name') . $aid, 'vendor_id' => $aid])->first();
        if (!$check) {
            $role = Role::create(['name' => $request->input('name') . $aid, 'post' => $request->input('name'), 'vendor_id' => $aid]);
            $role->syncPermissions($request->input('permission'));
            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully');
        } else {
            return redirect()->route('roles.index')->with('error', 'Role aleady exists');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): \Illuminate\View\View
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $id)
            ->get();

        return view('admin.roles.show', compact('role', 'rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id): \Illuminate\View\View
    {
        $title  = "Edit Role";
        $role = Role::find($id);
        //$permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();
        $aid = (Auth::user()->designation == 'Super') ? (Auth::user()->id) : (Auth::user()->vendor_id);
        $p_for = ($aid == '22') ? ('Sadmin') : ('Admin');
        $cnames = Permission::distinct('cname')->select('cname')->get();
        $permission = [];
        foreach ($cnames as $cname) {
            $mitems = $cname;
            $items = Permission::where('cname', $cname['cname'])->get();
            $mitems['td'] = $items;
            array_push($permission, $mitems);
        }

        return view('admin.roles.edit', compact('role', 'permission', 'rolePermissions', 'title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);
      

        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();

        $role->syncPermissions($request->input('permission'));

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        DB::table("roles")->where('id', $id)->delete();
        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully');
    }
}
