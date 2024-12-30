@extends('layouts.main')
@section('content')
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="w-100 text-end">
                        <a href="{{ route('faq.index') }}" class="btn btn-success">
                            Back
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    {!! Form::open(['url' => $url, 'method' => $method, 'files' => true]) !!}
                    @method($method)
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            {!! Form::label('', 'Enter FAQ', ['class' => 'form-label']) !!}

                            {!! Form::text('faq', $faq['faq'], ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-md-12 mb-4">
                            {!! Form::label('', 'Enter Explain', ['class' => 'form-label']) !!}
                            {!! Form::textarea('explain', $faq['explain'], ['class' => 'form-control', 'id' => 'explain']) !!}
                            <script>
                                CKEDITOR.replace('explain')
                            </script>
                        </div>
                        <div class="col-md-12">
                            <input type="submit" value="Add FAQ" class="btn btn-success px-md-4">
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
