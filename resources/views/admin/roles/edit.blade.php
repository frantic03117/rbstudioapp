@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="pull-right">
                <a class="btn btn-primary" href="{{ route('roles.index') }}"> Back </a>
            </div>
        </div>
    </div>


    {!! Form::model($role, ['method' => 'PATCH', 'route' => ['roles.update', $role->id]]) !!}
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                {!! Form::text('name', $role->post, ['placeholder' => 'Name', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
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
                                        {{ Form::checkbox('permission[]', $td->name, in_array($td->id, $rolePermissions) ? true : false, ['class' => 'name', 'id' => 'permission' . $i . $j]) }}
                                        <span>{{ ucwords(explode('-', $td['name'])[1]) }}</span>
                                    </label>

                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </div>
    {!! Form::close() !!}


@endsection
