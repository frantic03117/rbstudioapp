@extends('layouts.main')


@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-right">
                <a class="btn btn-primary" href="{{ route('employee.index') }}"> Back </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="w-100 bg-light p-4 rounded shadow">
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::open(['url' => route('employee.update', $employee['id']), 'method' => 'PUT']) !!}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Name:</strong>
                                    {!! Form::text('name', $employee['name'], ['placeholder' => 'Name', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Mobile:</strong>
                                    {!! Form::text('mobile', $employee['mobile'], ['placeholder' => 'mobile', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Email:</strong>
                                    {!! Form::text('email', $employee['email'], ['placeholder' => 'Email', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Password:</strong>
                                    {!! Form::text('password', null, ['placeholder' => 'Password', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Confirm Password:</strong>
                                    {!! Form::text('confirm-password', null, ['placeholder' => 'Password', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Gender:</strong>
                                    <div class="d-flex gap-3">
                                        <label for="male">
                                            <input type="radio" name="gender" id="male" value="Male"
                                                @checked($employee['gender'] == 'male')>
                                            <span>
                                                Male
                                            </span>
                                        </label>
                                        <label for="Female">
                                            <input type="radio" name="gender" id="Female" value="Female"
                                                @checked($employee['gender'] == 'Female')>
                                            <span>
                                                Female
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <strong>Role:</strong>
                                    <select name="roles[]" id="roles" class="form-select">
                                        <option value="">---Select---</option>
                                        @foreach ($roles as $rl)
                                            <option value="{{ $rl['name'] }}" @selected(in_array($rl['name'], $userRole))>
                                                {{ $rl['name'] }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
