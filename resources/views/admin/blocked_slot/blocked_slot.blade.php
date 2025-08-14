@extends('layouts.main')
@section('content')
    <section>
        <div class="container">

            <div class="row">
                <div class="col-md-12 p-4 border border-secondary-subtle shadow-secondary shadow-lg rounded-3">
                    <h3>Block Slot Form</h3>
                    {!! Form::open(['url' => route('blocked-slot.store'), 'method' => 'POST', 'files' => true]) !!}
                    @csrf
                    <div class="row gy-3">
                        <div class="col-md-3">
                            <label for="">Select Studio</label>
                            <select name="studio_id[]" class="form-control select2" id="studio_id" multiple>
                                <option value="All">All</option>
                                @foreach ($studios as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="col-md-3">
                            <label for="">Select Slot</label>
                            <select name="slot_id[]" class="form-control" id="slot_id" multiple>
                                <option value="All">All</option>
                                @foreach ($slots as $item)
                                    <option value="{{ $item->id }}">{{ $item->start_at }}</option>
                                @endforeach
                            </select>
                        </div>
                        <script>
                            $(document).ready(function() {
                                $('select').select2({
                                    placeholder: "Select studios",
                                    allowClear: true
                                });
                            });
                        </script>
                        <div class="col-md-3">
                            <label for="">Select Date</label>
                            <input type="date" name="bdate" id="bdate" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <button class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="col-md-12 mt-4">
                    <h5>Filter</h5>
                    <form action="" method="get">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="">Select Studio</label>
                                <select name="studio_id" value="{{ $sid }}" class="w-100 outline-none select2">
                                    <option value="All">All</option>
                                    @foreach ($studios as $item)
                                        <option value="{{ $item->id }}"
                                            {{ ($sid ?? '') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="">Select Date</label>
                                <input type="date" name="bdate" value="{{ $bdate }}" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <button class="mt-2 block btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Date</th>
                                <th>Studio</th>
                                <th>Slot</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        {{ date('d-M-Y', strtotime($item->bdate)) }}
                                    </td>
                                    <td>
                                        {{ $item?->studio?->name }}
                                    </td>
                                    <td>
                                        {{ $item?->slot?->start_at }}
                                    </td>
                                    <td>
                                        {{ $item->reason }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {!! $items->links() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
