@extends('layouts.main')
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <style>
        .fc-event {
            cursor: pointer;
            margin: 0;
        }
    </style>
@endsection
@section('content')
    @include('admin.reports.calendar_comp')
@endsection
