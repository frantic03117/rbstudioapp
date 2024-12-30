@extends('layouts.main')
@section('content')
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    {!! Form::open(['url' => route('policy.update', $policy['id']), 'method' => 'PUT', 'files' => true]) !!}
                    <div class="row">
                        <div class="col-md-4">
                            <label for="">
                                Select Policy
                            </label>
                            <select name="policy" id="policy" class="form-select">
                                <option value="" selected disabled>---Select---</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item['id'] }}" @selected($pid == $item['id'])>{{ $item['title'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <script>
                            $("#policy").on('change', function() {
                                let val = $(this).val();
                                let curl = "{{ URL::current() }}";
                                let nurl = `${curl}?policy_id=${val}`;
                                window.location.href = nurl;
                            })
                        </script>
                        @if ($policy)
                            <div class="col-md-12 mb-4">
                                <label for="">
                                    Policy Content
                                </label>
                                <textarea name="content" id="content" cols="30" rows="10">{!! $policy['description'] !!}</textarea>
                                <script>
                                    CKEDITOR.replace('content')
                                </script>
                            </div>
                            <div class="col-md-12">
                                <input type="submit" value="Update Policy" class="btn btn-success px-md-4">
                            </div>
                        @endif
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
