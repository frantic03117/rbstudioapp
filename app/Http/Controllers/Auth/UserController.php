<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;


class UserController extends Controller
{
    use HasRoles;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): \Illuminate\View\View
    {
        date_default_timezone_set('Asia/Kolkata');
        $aid = (Auth::user()->role == 'Super') ? ("0") : (Auth::user()->vendor_id);
        $res['title'] = 'List of Employees';
        $res['users'] = User::where('vendor_id', $aid)->where('role', 'employee')->get();
        // echo json_encode($res['items']);
        // die;
        return view('admin.users.index', $res);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): \Illuminate\View\View
    {
        date_default_timezone_set('Asia/Kolkata');
        $res['title'] = 'Add New Employees';
        $aid = (Auth::user()->role == 'Super') ? ("0") : (Auth::user()->vendor_id);
        // echo $aid;
        // die;
        $res['roles'] = Role::where('vendor_id', $aid)->get();
        return view('admin.users.create', $res);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        date_default_timezone_set('Asia/Kolkata');
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required',
            'mobile' => 'required|min:10|max:10|unique:users,mobile'
        ]);
        $aid = (Auth::user()->role == 'Super') ? ("0") : (Auth::user()->vendor_id);
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $input['role'] = "Employee";
        $input['vendor_id'] = $aid;
        $user = User::create($input);
        $user->assignRole((int)$request->input('roles'));
        return redirect()->route('employee.index')
            ->with('success', 'User created successfully');
    }
    public function store_user(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|min:10|max:10|unique:users,mobile'
        ]);
        $input = $request->except('_token');
        $input['role'] = "User";
        $input['otp_verified'] = "1";
        $input['is_verified'] = "1";
        $user = User::create($input);
        return redirect()->back()
            ->with('success', 'User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user, $id): \Illuminate\View\View
    {
        $user = User::find($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user, $id): \Illuminate\View\View
    {


        date_default_timezone_set('Asia/Kolkata');
        $employee = User::find($id);
        $roles = Role::where('vendor_id', $employee->vendor_id)->get(['id', 'name']);
        $title = "Edit Role";

        $userRole = $employee->roles->pluck('name', 'name')->all();

        return view('admin.users.edit', compact('employee', 'roles', 'userRole', 'title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user, $id): RedirectResponse
    {
        date_default_timezone_set('Asia/Kolkata');
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'mobile' => 'required|unique:users,mobile,' . $id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);
        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }
        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();
        $user->assignRole($request->input('roles'));
        return redirect()->route('employee.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user, $id): RedirectResponse
    {
        date_default_timezone_set('Asia/Kolkata');
        User::where(['id' => $id])->update(['deleted_at' => date('Y-m-d H:i:s')]);
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }
    public function profile()
    {
        $title = "Profile";
        $aid = (Auth::user()->role == 'Super') ? (0) : (Auth::user()->vendor_id);
        $item = Vendor::where('id', $aid)->first();
        $res = compact('item', 'title');
        return view('admin.reports.profile', $res);
    }
    public function users()
    {
        $title = "List of users";
        $key = $_GET['keyword'] ?? null;

        $itms = User::where('role', 'User')->where('otp_verified', '1')->where('email', '!=', null)->where('mobile', '!=', null);
        if ($key) {
            $itms->where('name', 'LIKE', "%{$key}%")->orWhere('email', 'LIKE', "%{$key}%")->orWhere('mobile', 'LIKE', "%{$key}%");
        }
        $items = $itms->orderBy('id', 'DESC')->paginate(20);
        $res = compact('title', 'items', 'key');
        return view('admin.reports.users', $res);
    }
    public function edit_user($id)
    {
        $user = User::where('id', $id)->first();
        $title = "Edit User";
        $res = compact('title', 'user');
        return view('admin.reports.edit_users', $res);
    }
    public function update_edit_user(Request $request, $id)
    {
        $request->validate(['name' => 'required', 'email' => 'required|email']);
        $data = [
            'name' => $request->name,
            'email' => $request->email
        ];
        if ($request->dob) {
            $data['dob'] = $request->dob;
        }
        if ($request->gender) {
            $data['gender'] = $request->gender;
        }
        User::where('id', $id)->update($data);
        return redirect()->back()->with('success', 'user updated successfully');
    }
}
