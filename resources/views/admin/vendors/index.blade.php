@extends('layouts.main')
@section('content')
    <section class="space">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 mb-2 text-end">
                    <a href="{{route('vendor.create')}}" class="btn btn-gradient">Add New </a>
                </div>
                <div class="col-md-12">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Vendor</th>
                                <th>Address</th>
                                
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vendor as $i => $v)
                                <tr>
                                    <td>{{$i + 1}}</td>
                                    <td>
                                        <ul class="list-unstyled">
                                            <li>
                                                <b>Owner :</b> {{$v->name}}
                                            </li>
                                            <li>
                                                <b>Business Name :</b> {{$v->business_name}}
                                            </li>
                                            <li>
                                                <b>Bill Prefix :</b> {{$v->bill_prefix}}
                                            </li>
                                        </ul>
                                    </td>
                                   
                                    <td>
                                        <div class="wglg" style="max-width: 300px;max-height:140px;">
                                            {!!$v->google_map!!}
                                        </div>

                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{route('vendor.edit', $v->id)}}" class="btn btn-gradient px-3">Edit</a>
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
