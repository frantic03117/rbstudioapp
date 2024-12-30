@extends('layouts.main')


@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="text-end">
                <a class="btn btn-gradient" href="{{ route('employee.index') }}"> Back </a>
            </div>
        </div>
    </div>




    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="w-100 bg-light p-4 rounded shadow">


                <div class="row">
                    <div class="col-md-12">


                        {!! Form::open(['route' => 'employee.store', 'method' => 'POST']) !!}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Name:</strong>
                                    {!! Form::text('name', null, ['placeholder' => 'Name', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Mobile:</strong>
                                    {!! Form::text('mobile', null, ['placeholder' => 'mobile', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Email:</strong>
                                    {!! Form::text('email', null, ['placeholder' => 'Email', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Gender:</strong>
                                    <div class="d-flex gap-3">
                                        <label for="male">
                                            <input type="radio" name="gender" id="male" value="Female">
                                            <span>
                                                Male
                                            </span>
                                        </label>
                                        <label for="Female">
                                            <input type="radio" name="gender" id="Female" value="Female">
                                            <span>
                                                Female
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Password:</strong>
                                    {!! Form::password('password', ['placeholder' => 'Password', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Confirm Password:</strong>
                                    {!! Form::password('confirm-password', ['placeholder' => 'Confirm Password', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <strong>Role:</strong>
                                    <select name="roles[]" id="roles" class="form-select">
                                        <option value="">---Select---</option>
                                        @foreach ($roles as $rl)
                                            <option value="{{ $rl['id'] }}">{{ $rl['post'] }}</option>
                                        @endforeach
                                    </select>
                                    {{-- {!! Form::select('roles[]', $roles, [], ['class' => 'form-control', 'multiple']) !!} --}}
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
