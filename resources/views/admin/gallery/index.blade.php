@extends('layouts.main')


@section('content')
    <section>
        <div class="container">
            <form action="{{ route('gallery.store') }}" enctype="multipart/form-data" method="post">
                <div class="row">
                    @csrf
                    <div class="col-md-3">
                        <label for="">Category</label>
                        <select name="category" class="form-select form-select-sm" id="">
                            <option value="">Select</option>
                            <option value="Gallery">Gallery</option>
                            <option value="Profile">Profile</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="">Image</label>
                        <div class="w-full input-group ">
                            <input type="file" class="form-control form-control-sm" name="image" id="">
                            <button class="btn btn-sm btn-primary">Submit</button>
                        </div>

                    </div>

                </div>
            </form>
            <div class="col-md-12">
                <table class="w-100 table">
                    <thead>
                        <tr>
                            <th>Sr</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    {{ $item->category }}
                                </td>
                                <td>
                                    <img src="{{ url('' . $item->image) }}" alt="" class="img-fluid">
                                </td>
                                <td>
                                    <form action="{{ route('gallery.destroy', $item->id) }}" method="post">
                                        @method('DELETE')
                                        @csrf
                                        <button class="btn btn-danger btn-sm">Delete</button>

                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </section>
@endsection
