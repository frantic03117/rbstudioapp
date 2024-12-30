@extends('layouts.main')
@section('content')
    <section class="space">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    @can('product-create')
                    <div class="w-100 text-end">
                        <a href="{{route('rents.create')}}" class="btn btn-gradient">Add New</a>
                    </div>
                    @endcan
                    <div class="w-100">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>Title</th>
                                    <th>Icon</th>
                                     @can('product-create')
                                    <th>Action</th>
                                     @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rents as $i => $s)
                                        <tr>
                                            <td>{{$i + 1}}</td>
                                            <td>{{$s->name}}</td>
                                            <td>
                                                <img src="{{url($s->icon)}}" alt="" class="img-fluid" width="100">
                                            </td>
                                             @can('product-create')
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{route('rents.edit', $s->id)}}" class="btn btn-info">Edit</a>
                                                    {!! Form::open(['route' => ['rents.destroy', $s->id], 'method' => 'DELETE']) !!}
                                                    <button class="btn btn-danger">
                                                        Delete
                                                    </button>
                                                    {!! Form::close() !!}

                                                </div>

                                            </td>
                                             @endcan
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
