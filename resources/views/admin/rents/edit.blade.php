@extends('layouts.main')
@section('content')
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="w-100 mb-3 text-end">
                        <a href="{{ route('rents.index') }}" class="btn btn-gradient">Back
                            <i class="fas fa-arrow"></i>
                        </a>
                    </div>
                    <div class="w-100">
                        {!! Form::open(['route' => ['rents.update', $rent->id], 'method' => 'PUT', 'files' => 'true']) !!}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">

                                    {!! Form::text('name', $rent['name'], ['class' => 'form-control', 'id' => 'title']) !!}
                                    <label for="title">Enter Title</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    {!! Form::file('icon', ['class' => 'form-control', 'id' => 'file']) !!}
                                    <label for="file">Upload Icon</label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="">Enter Description</label>
                                {!! Form::textarea('description', $rent['description'], ['id' => 'editor']) !!}
                                <script>
                                    CKEDITOR.replace('editor')
                                </script>
                            </div>
                            <div class="col-md-12">
                                <button class="btn btn-gradient px-5 py-2">Submit</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
