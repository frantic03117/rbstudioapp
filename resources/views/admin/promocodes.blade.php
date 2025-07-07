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
                            <li class="breadcrumb-item active">Promo Codes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="w-100">
                    <form action="{{ route('promo.store') }}" method="POST" class="w-100" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-md-2">
                                <label for="">Enter Code</label>
                                <input type="text" name="promo_code" value="{{ old('promo_code') }}" class="form-control"
                                    required />
                            </div>
                            <div class="col-md-2">
                                <label for="">Select Studio</label>
                                <select class="form-control" name="studio_id" required>
                                    <option value="all">---All---</option>
                                    @foreach ($studios as $st)
                                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter User Id</label>
                                <input type="text" name="user_id" value="{{ old('user_id') }}" class="form-control" />
                            </div>
                            <div class="col-md-2">
                                <label for="">Per User Use</label>
                                <input type="number" min="1" value="1" name="user_count"
                                    value="{{ old('user_count') }}" required class="form-control" />
                            </div>
                            <div class="col-md-2">
                                <label for="">Start From</label>
                                <input type="date" name="start_at" min="{{ date('Y-m-d') }}" class="form-control" />
                            </div>
                            <div class="col-md-2">
                                <label for="">End to</label>
                                <input type="date" name="end_at" min="{{ date('Y-m-d') }}" class="form-control" />
                            </div>
                            <div class="col-md-2">
                                <label for="">Discount</label>
                                <input type="number" name="discount" min="1" class="form-control" required />
                            </div>
                            <div class="col-md-2">
                                <label for="">Discount Type</label>
                                <select class="form-select" name="discount_type" required>
                                    <option value="Percent">Percent</option>
                                    <option value="Fixed">Fixed</option>
                                </select>
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
                                <th>Promo Code</th>
                                <th>Studio</th>
                                <th>User</th>
                                <th>Use Allow</th>
                                <th>Start From</th>
                                <th>End At</th>
                                <th>Discount</th>
                                <th>Discount Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($codes as $i => $b)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        {{ $b->promo_code }}
                                    </td>
                                    <td>
                                        {{ $b->studio?->name }}
                                    </td>
                                    <td>
                                        {{ $b->user?->name }}
                                    </td>
                                    <td>
                                        {{ $b->user_count }}
                                    </td>
                                    <td>
                                        {{ $b->start_at }}
                                    </td>
                                    <td>
                                        {{ $b->end_at }}
                                    </td>
                                    <td>
                                        {{ $b->discount }}
                                    </td>
                                    <td>
                                        {{ $b->discount_type }}
                                    </td>
                                    <td>
                                        <form action="{{ route('promo.destroy', $b->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>
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
