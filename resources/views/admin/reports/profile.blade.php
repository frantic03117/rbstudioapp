@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <table class="table table-sm table-bordered">
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <td>
                                {{ auth()->user()->name }}
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                {{ auth()->user()->email }}
                            </td>
                        </tr>
                        <tr>
                            <th>Mobile</th>
                            <td>
                                {{ auth()->user()->mobile }}
                            </td>
                        </tr>
                        <tr>
                            <th>User Id</th>
                            <td>
                                {{ auth()->user()->id }}
                            </td>
                        </tr>
                        <tr>
                            <th>Vendor Id</th>
                            <td>
                                {{ auth()->user()->vendor_id }}
                            </td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>
                                {{ auth()->user()->created_at }}
                            </td>
                        </tr>

                        <tr>
                            <th>Action</th>
                            <td>
                                <form action="{{ route('update_admin_profile') }}" method="POST"style="width:250px;">
                                    @csrf
                                    <div class="form-group mb-2">
                                        <label for="">Enter Email</label>
                                        <input type="email" width="200" name="email" class="form-control"
                                            value="{{ auth()->user()->email }}" required />
                                    </div>
                                    @if (auth()->user()->role == 'Super')
                                        <div class="form-group mb-2">

                                            <label for=""> Waiting Period</label>

                                            <input type="tel" class="form-control form-control-sm" name="remember_token"
                                                min="10" max="720" id=""
                                                value="{{ auth()->user()->remember_token }}">

                                        </div>
                                    @endif
                                    <div class="form-group mb-2">
                                        <label for="">Enter Password</label>
                                        <input type="text" width="200" name="password" class="form-control"
                                            required />
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-primary d-block mt-3">Submit</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
