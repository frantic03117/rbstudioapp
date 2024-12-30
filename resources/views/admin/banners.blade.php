@extends('layouts.main')
@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript:%20void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Banners</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="w-100">
                    <form action="{{route('save_banners')}}" method="POST" class="w-100" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <label for="">Upload Image</label>
                                <input type="file" name="banner" class="form-control" />
                            </div>
                            <div class="col-md-4">
                                <label for="" class="w-100 mb-2 d-block"></label>
                                <button class='btn btn-primary'>Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-12">
                <div class="w-100">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Banner</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($banners as $i => $b)
                                <tr>
                                    <td>{{$i + 1}}</td>
                                    <td>
                                        <img class="img-fluid" width="150" src="{{url('public/'.$b->banner)}}" />
                                    </td>
                                    <td>
                                        <a href="{{route('banners_delete', $b->id)}}" class="btn btn-danger">Delete</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection
