@extends('layouts.main')

@section('content')
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <form action="" method="GET">
                        
                    
                    <div class="w-100">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="">Search</label>
                                <div class="input-group">
                                     <input type="text" value="{{$key}}" name="keyword" class="form-control"/>
                                     <button class="btn btn-primary">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="col-md-12">
                    <div class="w-100 table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <td>Sr</td>
                                    <td>User Id</td>
                                    <td>Name</td>
                                     <td>Email</td>
                                     <td>Mobile</td>
                                     <td>CreatedAt</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $k => $item)
                                    <tr>
                                        
                                        <td>
                                            {{$k+1}}
                                        </td>
                                        <td>
                                            {{$item->id}}
                                        </td>
                                        <td>
                                            {{$item->name}}
                                        </td>
                                        <td>
                                            {{$item->email}}
                                        </td>
                                        <td>
                                            {{$item->mobile}}
                                        </td>
                                        <td>
                                            {{$item->created_at}}
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="{{route('edit_user', $item->id)}}">Edit</a>
                                        </td>
                                    </tr>
                                
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
