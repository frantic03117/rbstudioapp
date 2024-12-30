@extends('layouts.main')
@section('content')
    <section>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="w-100 mb-2 text-end">
                        <a href="{{ route('vendor.index') }}" class="btn btn-gradient">Back</a>
                    </div>
                    <div class="w-100">
                        {!! Form::open(['route' => 'vendor.store', 'files' => 'true']) !!}
                        <div class="row gy-4 ">
                            <div class="col-md-12 p-1 bg-gradient">
                                Personal Details
                            </div>
                            <div class="col-md-3">
                                <label for="">Owner Name</label>
                                {!! Form::text('name', old('name'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-4">
                                <label for="">Business Name</label>
                                {!! Form::text('business_name', old('business_name'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter Mobile</label>
                                {!! Form::text('mobile', old('mobile'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-3">
                                <label for="">Enter Email</label>
                                {!! Form::text('email', old('email'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-3">
                                <label for="">Enter Password</label>
                                {!! Form::text('password', old('password'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-12 p-1 bg-gradient">
                                Address Details
                            </div>
                            <div class="col-md-3">
                                <label for="">Address</label>
                                {!! Form::text('address', old('address'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-2">
                                <label for="">Select Country</label>
                                <select name="country_id" onchange="getStates(event)" id="country_id" class="form-select">
                                    <option value="" selected @disabled(true)>---Select---</option>
                                    @foreach ($country as $c)
                                        <option value="{{ $c->id }}">{{ $c->country }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Select State</label>
                                <select name="state_id" onchange="getCity(event)" id="state_id" class="form-select">
                                    <option value="" selected @disabled(true)>---Select---</option>

                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Select City</label>
                                <select name="district_id" id="city_id" class="form-select">
                                    <option value="" selected @disabled(true)>---Select---</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter Pincode</label>
                                {!! Form::text('pincode', old('pincode'), ['class' => 'form-control', 'minlength' => '6', 'maxlength' => '6']) !!}
                            </div>
                            <div class="col-md-4">
                                <label for="">Enter Google Map URL</label>
                                {!! Form::text('google_map', old('google_map'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-12">
                                <label for=""></label>
                                <button class="btn btn-gradient px-lg-5">Submit</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        const getStates = (e) => {
            const cid = e.target.value;
            let aurl = "{{ route('ajax_states') }}"
            $.post(`${aurl}`, {
                country_id: cid
            }, function(res) {
                $("#state_id").html(res);
            })
        }
        const getCity = (e) => {
            const cid = e.target.value;
            let aurl = "{{ route('ajax_cities') }}"
            $.post(`${aurl}`, {
                state_id: cid
            }, function(res) {
                $("#city_id").html(res);
            })
        }
    </script>
@endsection
