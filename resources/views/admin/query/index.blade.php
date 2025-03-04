@extends('layouts.main')
@section('content')
    <section class="space">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">

                    <div class="w-100">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>User</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>image</th>
                                    <th>Status</th>
                                    <th>Action</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $i => $s)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $s->user->name }}</td>
                                        <td>{{ $s->subject }}</td>
                                        <td>{{ $s->message }}</td>
                                        <td>
                                            <img src="{{ url('public/uploads/' . $s->image) }}" alt=""
                                                class="img-fluid" width="100">
                                        </td>
                                        <td>
                                            @if ($s->is_resolved == '0')
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                            @if ($s->is_resolved == '1')
                                                <span class="badge bg-success">Resolved</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($s->is_resolved == '0')
                                                <button class="btn btn-primary btn-sm"
                                                    onclick="markResolved({{ $s->id }})">Mark Resolved</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        const markResolved = (id) => {
            if (confirm('Are you sure ?')) {
                const resp = $.post("{{ route('resolve_queries') }}", {
                    id
                }, function(res) {
                    if (res.success) {
                        location.reload();
                    }
                });
            }
        };
    </script>
@endsection
