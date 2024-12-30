@extends('layouts.main')

@section('content')
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="text-end">
                        <a class="btn btn-primary" href="{{route('users')}}">Back</a>
                    </div>
                </div>
                <div class="col-md-12 mb-4">
                    <form action="{{route('edit_user.update', $user->id)}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-2">
                                <label for="">Enter Name</label>
                                <input type="text" class="form-control" name="name" value="{{$user->name}}" />
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter Email</label>
                                <input type="text" class="form-control" name="email" value="{{$user->email}}" />
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary d-block mt-4">Update</button>
                            </div>
                        </div>
                    
                    
                    </form>
                </div>
              
            </div>
        </div>
    </section>
@endsection
