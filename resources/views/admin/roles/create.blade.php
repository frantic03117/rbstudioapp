@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="col-lg-12 text-end">


                <a class="btn btn-gradient" href="{{ route('roles.index') }}"> Back </a>

        </div>
    </div>




    {!! Form::open(['route' => 'roles.store', 'method' => 'POST']) !!}
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                {!! Form::text('name', null, ['placeholder' => 'Name', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <strong>Permission:</strong>
                <div class="w-100 table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            @foreach ($permission as $i => $item)
                                <tr>
                                    <th>
                                        {{ $item['cname'] }}
                                    </th>
                                    @foreach ($item['td'] as $j => $td)
                                        <td>
                                            <label class="btn border " for="permission{{ $i . $j }}">
                                                <input type="checkbox" name="permission[]"
                                                    id="permission{{ $i . $j }}" value="{{ $td['name'] }}"
                                                    class="form-check-input">
                                                <span>{{ ucwords(explode('-', $td['name'])[1]) }}</span>
                                            </label>

                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <button type="submit" class="btn btn-gradient">Submit</button>
        </div>
    </div>
    {!! Form::close() !!}

@endsection
