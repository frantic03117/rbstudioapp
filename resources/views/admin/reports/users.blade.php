@extends('layouts.main')

@section('content')
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <form action="" method="GET">


                        <div class="w-100">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="">Search</label>
                                    <div class="input-group">
                                        <input type="text" value="{{ $key }}" name="keyword"
                                            class="form-control" />
                                        <button class="btn btn-primary">Search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-8">
                    <div class="w-100 py-2 bg-warning-subtle rounded-3 shadow-md ps-4 shadow-primary">
                        <form action="{{ route('store_user') }}" method="post">
                            @csrf
                            <div class="row gx-1">
                                <div class="col-md-3">
                                    <label for="">Enter Name</label>
                                    <input type="text" name="name" value="{{ old('name') }}" id="name"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label for="">Enter Email</label>
                                    <input type="text" name="email" value="{{ old('email') }}" id="name"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label for="">Enter Mobile</label>
                                    <input type="text" value="{{ old('mobile') }}" name="mobile" id="name"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label for="">Enter Brand Artist</label>
                                    <input type="text" value="{{ old('brand_artisan') }}" name="brand_artisan"
                                        id="name" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label for="" class="d-block mb-2">Action</label>
                                    <button class="btn btn-sm btn-primary">Create New</button>
                                </div>
                            </div>
                        </form>
                    </div>
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
                                    <th>Brand Artist</th>
                                    <td>CreatedAt</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $k => $item)
                                    <tr>

                                        <td>
                                            {{ $k + 1 }}
                                        </td>
                                        <td>
                                            {{ $item->id }}
                                        </td>
                                        <td>
                                            {{ $item->name }}
                                        </td>
                                        <td>
                                            {{ $item->email }}
                                        </td>
                                        <td>
                                            {{ $item->mobile }}
                                        </td>
                                        <td>
                                            {{ $item->brand_artisan }}
                                        </td>
                                        <td>
                                            {{ $item->created_at }}
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-primary"
                                                href="{{ route('edit_user', $item->id) }}">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {!! $items->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
