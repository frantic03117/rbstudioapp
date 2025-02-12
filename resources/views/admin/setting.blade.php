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
                            <li class="breadcrumb-item active">Setting</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="w-100">
                    <form action="{{ $url }}" method="POST" class="w-100" enctype="multipart/form-data">
                        @csrf
                        @method($method)
                        <div class="row">

                            <div class="col-md-2">
                                <label for="">Enter Setting Name</label>
                                <input type="text" name="col_name"
                                    value="{{ $setting ? $setting['col_name'] : old('col_name') }}" class="form-control"
                                    required />
                            </div>

                            <div class="col-md-2">
                                <label for="">Enter Setting Value</label>
                                <input type="text" name="col_val"
                                    value="{{ $setting ? $setting['col_val'] : old('col_val') }}" class="form-control" />
                            </div>

                            <div class="col-md-12">
                                <p>
                                    Calendar View : "dayGridMonth,timeGridDay,listWeek,timeGridWeek"
                                </p>
                            </div>

                            <div class="col-md-2">
                                <label for="" class="w-100 mb-2 d-block"></label>
                                <button class='btn btn-primary'>Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-12">
                <div class="w-100">
                    <table class="table table-sm table-bordered border table-hover table-stripped">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Setting Name</th>
                                <th>Setting Value</th>

                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $i => $b)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        {{ $b->col_name }}
                                    </td>
                                    <td>
                                        {{ $b->col_val }}
                                    </td>
                                    <td>
                                        <a href="{{ route('setting.edit', $b->id) }}"
                                            class="btn-gradient btn btn-sm">Edit</a>
                                        {{-- <form action="{{ route('setting.destroy', $b->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form> --}}
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
