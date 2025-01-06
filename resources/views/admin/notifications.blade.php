@extends('layouts.main')
@section('content')
    <style>
        .readed td {
            background: #ddd;
            color: #000;
        }
    </style>
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="w-100 mb-4 text-end">
                        <form action="{{ route('mark-read') }}" method="POST">
                            @csrf
                            <input type="hidden" name="ids" id="ids" />
                            <button class="btn btn-primary">Read</button>
                        </form>
                    </div>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <td>
                                    Sr No <input type="checkbox" onclick="handleSelectAll(event)" id="selectAll">
                                </td>
                                <td>
                                    User
                                </td>
                                <td>
                                    Title
                                </td>
                                <td>
                                    Message
                                </td>
                                <td>
                                    Booking
                                </td>

                                <td>
                                    Action
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $i => $item)
                                @php
                                    $rowClass = $item->is_read == '1' ? '' : 'readed';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>
                                        {{ $i + 1 }} <input type="checkbox" class="checkbox"
                                            onclick="handleNotId(event)" value="{{ $item->id }}" />
                                    </td>
                                    <td>
                                        <ul>
                                            <li>
                                                Name : {{ $item?->user?->name }}
                                            </li>
                                            <li>
                                                Email : {{ $item?->user?->email }}
                                            </li>
                                            <li>
                                                Mobile : {{ $item?->user?->mobile }}
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        {{ $item->title }}
                                    </td>
                                    <td>
                                        {{ $item->message }}

                                    </td>
                                    <td>
                                        <ul class='text-nowrap'>
                                            <li>
                                                Studio : {{ $item->studio?->name }}
                                            </li>
                                            <li>
                                                Service : {{ $item->booking?->service?->name }}
                                            </li>
                                            <li>
                                                {{ $item->booking ? $item->booking?->duration . ' Hours' : '' }}
                                            </li>



                                        </ul>
                                    </td>

                                    <td>
                                        <div class="d-flex">


                                            <form action="{{ route('mark-read') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="ids" value="{{ $item->id }}" />
                                                <button class="btn btn-sm btn-primary">Read</button>
                                            </form>
                                            <form class="d-none" action="{{ route('delete_notification', $item->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm ms-1 btn-outline-danger">Delete</button>
                                            </form>
                                        </div>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <script>
                        const arr = [];
                        // Function to handle checkbox change events
                        const handleNotId = (e) => {
                            const val = e.target.value;
                            if (e.target.checked) {
                                if (!arr.includes(val)) {
                                    arr.push(val);
                                }
                            } else {
                                const index = arr.indexOf(val);
                                if (index > -1) {
                                    arr.splice(index, 1);
                                }
                            }
                            console.log(arr);
                            if (arr.length > 0) {
                                $("#ids").val(arr.toString())
                            }
                        }
                        const handleSelectAll = (e) => {
                            const isChecked = e.target.checked;
                            document.querySelectorAll('.checkbox').forEach(checkbox => {
                                checkbox.checked = isChecked;
                                if (isChecked) {
                                    if (!arr.includes(checkbox.value)) {
                                        arr.push(checkbox.value);
                                    }
                                } else {
                                    const index = arr.indexOf(checkbox.value);
                                    if (index > -1) {
                                        arr.splice(index, 1);
                                    }
                                }
                            });
                            if (arr.length > 0) {
                                $("#ids").val(arr.toString())
                            }
                            console.log(arr);
                        }
                    </script>
                </div>
            </div>
        </div>
    </section>
@endsection
