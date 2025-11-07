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
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript:%20void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-md-6 my-2">
                <div class="w-100">
                    <a href="{{ route('booking.create') }}" class="btn-gradient btn btn-sm">Add New Booking</a>
                </div>
            </div>
            <div class="col-md-4">

            </div>
            <div class="col-md-2">
                <form action="" method="get">
                    <select name="studio_id" value="{{ $_GET['studio_id'] }}" onchange="this.form.submit()"
                        class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($studios as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </form>

            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-3 col-md-6">
                <!-- card -->
                <a href="{{ route('bookingsview', ['slug' => 'upcoming']) }}" class="card card-h-100">
                    <!-- card body -->
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="text-muted mb-3 lh-1 d-block text-truncates">Today Booked Hours</span>
                                <h4 class="mb-3">
                                    <span class="counter-value  badge bg-success-subtle text-success d-block"
                                        data-target="{{ $today_booking }}">{{ $today_booking }}</span>
                                </h4>
                            </div>
                            <div class="col-6">
                                <div id="mini-chart1" data-colors='["#5156be"]' class="apex-charts mb-2"></div>
                            </div>
                        </div>
                        <div class="text-nowrap">
                            <!--<a href="{{ route('booking.create') }}" class="btn btn-sm btn-gradient">Add New</a>-->
                            <span class="ms-1 text-muted font-size-13">Today </span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6">

                <a href="{{ route('bookingsview', ['slug' => 'upcoming']) }}" class="card card-h-100">

                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="text-muted mb-3 lh-1 d-block text-truncate">All Booked <br>Hours</span>
                                <h4 class="mb-3">
                                    <span class="badge bg-success-subtle text-success counter-value  d-block"
                                        data-target="{{ $total_booking_month }}">{{ $total_booking_month }}</span>
                                </h4>
                            </div>
                            <div class="col-6">
                                <div id="mini-chart2" data-colors='["#5156be"]' class="apex-charts mb-2"></div>
                            </div>
                        </div>
                        <div class="text-nowrap">

                            <span class="ms-1 text-muted font-size-13">In this month</span>
                        </div>
                    </div><!-- end card body -->
                </a><!-- end card -->
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('bookingsview', ['slug' => 'upcoming']) }}" class="card card-h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="text-muted mb-3 lh-1 d-block text-truncate"> FY
                                    {{ date('y', strtotime($fystart)) }}-{{ date('y', strtotime($fyend)) }}
                                    <br>Hours</span>
                                <h4 class="mb-3">
                                    <span class="badge bg-success-subtle text-success counter-value  d-block"
                                        data-target="{{ $total_fy_year_booking }}">{{ $total_fy_year_booking }}</span>
                                </h4>
                            </div>
                            <div class="col-6">
                                <div id="mini-chart2" data-colors='["#5156be"]' class="apex-charts mb-2"></div>
                            </div>
                        </div>
                        <div class="text-nowrap">

                            <span class="ms-1 text-muted font-size-13">In this financial Year</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('bookingsview', ['slug' => 'upcoming']) }}" class="card card-h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="text-muted mb-3 lh-1 d-block text-truncate"> All <br>Hours</span>
                                <h4 class="mb-3">
                                    <span class="badge bg-success-subtle text-success counter-value  d-block"
                                        data-target="{{ $all_booking }}">{{ $all_booking }}</span>
                                </h4>
                            </div>
                            <div class="col-6">
                                <div id="mini-chart2" data-colors='["#5156be"]' class="apex-charts mb-2"></div>
                            </div>
                        </div>
                        <div class="text-nowrap">

                            <span class="ms-1 text-muted font-size-13">Since starting</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6">
                <!-- card -->
                <a href="{{ route('bookingsview', ['slug' => 'upcoming', 'approved_at' => 'pending']) }}"
                    class="card card-h-100">
                    <!-- card body -->
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="text-muted mb-3 lh-1 d-block text-truncates">Today Approval Pending</span>
                                <h4 class="mb-3">
                                    <span class="badge bg-success-subtle text-success counter-value  d-block"
                                        data-target="{{ $approval }}">{{ $approval }}</span>
                                </h4>
                            </div>
                            <div class="col-6">
                                <div id="mini-chart3" data-colors='["#5156be"]' class="apex-charts mb-2"></div>
                            </div>
                        </div>

                    </div><!-- end card body -->
                </a><!-- end card -->
            </div><!-- end col -->

            <div class="col-xl-3 d-none col-md-6">
                <!-- card -->
                <a href="{{ route('bookingsview', ['slug' => 'upcoming']) }}" class="card card-h-100">
                    <!-- card body -->
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="text-muted mb-3 lh-1 d-block text-truncates">Total Revenue Generated</span>
                                <h4 class="mb-3">
                                    <span class="badge bg-success-subtle text-success   d-block">
                                        ₹{{ $totalamount }}</span>
                                </h4>
                            </div>
                            <div class="col-6">
                                <div id="mini-chart4" data-colors='["#5156be"]' class="apex-charts mb-2"></div>
                            </div>
                        </div>
                        <div class="text-nowrap">

                            <span class="ms-1 text-muted font-size-13">In this month</span>
                        </div>
                    </div><!-- end card body -->
                </a><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row-->
        <div class="row">
            <div class="col-md-12">
                <div class="w-100">
                    @include('admin.reports.calendar_comp')
                </div>
            </div>
        </div>


    </div>
@endsection
