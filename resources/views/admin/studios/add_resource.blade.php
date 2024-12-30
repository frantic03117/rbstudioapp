@extends('layouts.main')
@section('content')
    <section>

        <div class="container-fluid">
            <div class="row gy-3">
                <div class="col-md-12">
                    <div class="w-100 mb-2 text-end">
                        <a href="{{ route('studio.index') }}" class="btn btn-gradient">Back</a>
                    </div>
                    <div class="w-100">
                        {!! Form::open(['route' => ['studio.add_resource', $id], 'files' => 'true']) !!}
                        <div class="row">
                            <div class="col-md-4">
                                <label for="">Select Item</label>
                                <select name="item_id" id="item_id" class="form-select">
                                    <option value="">---Select---</option>
                                    @foreach ($rents as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="">Enter Charge</label>
                                {!! Form::text('charge', old('charage'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-4">
                                <label for="" class="d-block">&nbsp;</label>
                                <button class="btn btn-gradient">Add Resource</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Item</th>
                                <th>Charge</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $i => $item)
                                <tr>
                                    <td>{{$i + 1}}</td>
                                    <td>
                                        <img width="40" src="{{url($item->item->icon)}}" alt="" class="img-fluid">
                                        <span>
                                            {{$item->item?->name}}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{route('studio.update_studio_resource_charge')}}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="id" value="{{$item->id}}" />
                                             <div class="input-group" style="width:200px;">
                                                 <input type='text' class="form-control" name="charge" value="{{$item->charge}}" />
                                                 <button class="btn btn-sm btn-primary">Update</button>
                                             </div>
                                        </form>
                                        
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                              <form action="{{route('studio.delete__studio_resource')}}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="id" value="{{$item->id}}" />
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                           
                                        </form>
                                        
                                        </div>
                                      
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
