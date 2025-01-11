<!DOCTYPE html>
<html lang="en">
@php
    use App\Http\Controllers\StaticController;
    $scss = StaticController::studio_css();
@endphp

<head>

    <meta charset="utf-8">
    <title>{{ $title ?? 'Dashboard || Admin Panel' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content=" connects you with expert mental health professionals and coaches to help you overcome challenges and achieve personal and professional goals. Learn to manage yourself and feel good.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('public/favicons') }}/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('public/favicons') }}/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url('public/favicons') }}/favicon-16x16.png">
    <link rel="manifest" href="{{ url('public/favicons') }}/site.webmanifest">
    <meta content="Umesh Upadhayay" name="author">
    <!-- App favicon -->
    <link rel="shortcut icon" href="favicons/images-favicon.ico">
    <script src="{{ url('public/js/jquery-jquery.min.js') }}"></script>
    <!-- plugin css -->
    <link href="{{ url('public/css/jquery.vectormap-jquery-jvectormap-1.2.2.css') }}" rel="stylesheet" type="text/css">

    <!-- preloader css -->
    <link rel="stylesheet" href="{{ url('public/css/css-preloader.min.css') }}" type="text/css">

    <!-- apexcharts -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ url('public/js/apexcharts-apexcharts.min.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ url('public/css/css-bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css">
    <!-- Icons Css -->
    <link href="{{ url('public/css/css-icons.min.css') }}" rel="stylesheet" type="text/css">
    <!-- App Css-->
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-thin-straight/css/uicons-thin-straight.css'>
    <link href="{{ url('public/css/css-app.min.css') }}" id="app-style" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    @yield('script')
    <link rel="stylesheet" href="{{ url('public/css/css-login.css') }}">
    <style>
        @foreach ($scss as $cs)
            .{{ implode('_', explode(' ', $cs->name)) }} {
                background: {{ $cs->color }};
                color: #fff;
            }

            .{{ implode('_', explode(' ', $cs->name)) }}:hover {
                background: {{ $cs->color }};
                color: #fff;
            }

        @endforeach
    </style>
    <style>
        .fc .fc-list-event:hover td {
            background: none;
        }

        #toast-container>.toast-error {
            background-position: left !important;

        }

        .toast-success {
            background: #077773 !important;
            background-position: left !important;
            background-repeat: no-repeat !important;
        }


        .toast-error {
            background: red !important;
            background-position: left !important;
            background-repeat: no-repeat !important;
        }
    </style>
    <script>
        const url = "{{ url('') }}";
    </script>
</head>


<body data-sidebar="brand" class="sidebar-enable" data-sidebar-size="lg">

    @foreach ($errors->all() as $item)
        <script>
            toastr.error('{{ $item }}', 'Errpr Occured')
        </script>
    @endforeach
    @if (session()->has('error'))
        <script>
            toastr.error("{{ session()->get('error') }}", 'Error')
        </script>
    @endif

    @if (session()->has('success'))
        <script>
            toastr.success('{{ session()->get('success') }}', 'Success')
        </script>
    @endif
    <!-- <body data-layout="horizontal"> -->

    <!-- Begin page -->
    <div id="layout-wrapper">


        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="{{ url('') }}" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="{{ url('public/images/logo.png') }}" alt="" height="24">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ url('public/images/logo.png') }}" alt="" height="24">
                                <span class="logo-txt">Wingo </span>
                            </span>
                        </a>

                        <a href="{{ url('') }}" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ url('public/images/logo.png') }}" alt="" height="24">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ url('public/images/logo.png') }}" alt="" height="24">
                                <span class="logo-txt">R & B Studios</span>
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                    <audio id="notificationSound" src="{{ url('public/images/Oneplus.mp3') }}" preload="auto"></audio>


                </div>

                <div class="d-flex">

                    <div class="dropdown d-inline-block d-lg-none ms-2">
                        <button type="button" class="btn header-item" id="page-header-search-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i data-feather="search" class="icon-lg"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                            aria-labelledby="page-header-search-dropdown">

                            <form class="p-3">
                                <div class="form-group m-0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Search ..."
                                            aria-label="Search Result">

                                        <button class="btn btn-primary" type="submit"><i
                                                class="mdi mdi-magnify"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="dropdown d-none d-sm-inline-block">
                        <button type="button" class="btn header-item" id="mode-setting-btn">

                            <i class="fas fa-sun" class="icon-lg layout-mode-light"></i>
                        </button>

                    </div>


                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item noti-icon position-relative"
                            id="page-header-notifications-dropdown" data-bs-auto-close="outside"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger rounded-pill" id="notifCount">5</span>
                        </button>
                        <div data-simplebar style="max-height: 230px;"
                            class="dropdown-menu dropdown-menu-lg dropdown-menu-outside dropdown-menu-end p-0"
                            aria-labelledby="page-header-notifications-dropdown">


                            <div id="notifBox"></div>
                            <div class="p-2 border-top d-grid">
                                <a href="{{ route('notification') }}"
                                    class="btn btn-sm btn-link font-size-14 text-center">
                                    <i class="mdi mdi-arrow-right-circle me-1"></i> <span>View More..</span>
                                </a>
                            </div>
                            <script>
                                let page = 1;
                                const nurl = "{{ route('notification') }}"
                                const audio = document.getElementById('notificationSound');
                                const playSound = () => {
                                    if (audio) {
                                        audio.play().catch(error => {
                                            console.error('Playback failed:', error);
                                            // You may want to show a user message or handle error
                                        });
                                    }
                                    document.addEventListener('click', () => {
                                        playSound(); // This is optional, but can help in cases where interaction is required
                                    });
                                };
                                const getNotification = (pg) => {
                                    const nourl = "{{ route('web_notification') }}?page=" + pg;
                                    let elem = "";
                                    $.get(nourl, function(res) {
                                        const items = res.data;


                                        items.forEach((obj, index) => {
                                            if (index == 0) {
                                                playSound();
                                            }
                                            elem += `   <a href="${nurl}" class="text-reset notification-item p-2 border flex-grow-1">
                                            <h6 class="mb-1">${obj.title}</h6>
                                            <div class="font-size-13 text-muted">
                                                <p class="mb-1">${obj.title} for  ${obj.user.name} </p>
                                            </div>
                                        </a>`;
                                        });
                                        $("#notifBox").append(elem);
                                        $("#notifCount").html(res.data.length)
                                        if (res.data.length == 0) {
                                            $("#loadMoreButton").hide()
                                        }
                                    })
                                }
                                getNotification(page);
                                const loadMoreNotif = () => {
                                    page++;
                                    getNotification(page);
                                };
                            </script>
                        </div>
                    </div>

                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item right-bar-toggle me-2">
                            <i data-feather="settings" class="icon-lg"></i>
                        </button>
                    </div>

                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item bg-light-subtle border-start border-end"
                            id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="{{ url('public/images/logo.png') }}"
                                alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1 fw-medium">
                                R & B
                            </span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <a class="dropdown-item" href="{{ route('profile') }}"><i
                                    class="mdi mdi mdi-face-man font-size-16 align-middle me-1"></i> Profile</a>
                            <a class="dropdown-item" href="{{ route('notification') }}"><i
                                    class="mdi mdi-lock font-size-16 align-middle me-1"></i> Notifications</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}"><i
                                    class="mdi mdi-logout font-size-16 align-middle me-1"></i> Logout</a>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu">

            <div data-simplebar class="h-100">

                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title" data-key="t-menu">Menu</li>

                        <li>
                            <a href="{{ url('admin/dashboard') }}">
                                <i class="fas fa-tv"></i>
                                <span data-key="t-dashboard">Dashboard</span>
                            </a>
                        </li>




                        <li>
                            <a href="javascript: void(0);" class="has-arrow" aria-expanded="false">
                                <i class="fas fa-database"></i>
                                <span data-key="t-maps">Master Data</span>
                            </a>
                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                @can('services-list')
                                    <li><a href="{{ route('services.index') }}" data-key="t-g-maps">Services
                                        </a></li>
                                @endcan
                                @can('product-list')
                                    <li><a href="{{ route('rents.index') }}" data-key="t-v-maps">Rentel Items</a></li>
                                @endcan
                                @can('policies-list')
                                    <li><a href="{{ route('policy.index') }}" data-key="t-v-maps">Policies</a></li>
                                @endcan
                                @can('faq-list')
                                    <li><a href="{{ route('faq.index') }}" data-key="t-v-maps">FAQ</a></li>
                                @endcan
                                @can('banners-list')
                                    <li><a href="{{ route('banners') }}" data-key="t-v-maps">Banners</a></li>
                                @endcan
                                @can('role-list')
                                    <li><a href="{{ route('roles.index') }}" data-key="t-g-maps">Roles</a></li>
                                @endcan
                                @can('employee-list')
                                    <li><a href="{{ route('employee.index') }}" data-key="t-v-maps">Employees</a></li>
                                @endcan
                            </ul>
                        </li>

                        @can('vendors-list')
                            <li>
                                <a href="{{ route('vendor.index') }}">
                                    <i class=" fas fa-store"></i>
                                    <span data-key="t-store">Vendors</span>
                                </a>
                            </li>
                        @endcan

                        @can('studios-list')
                            <li>
                                <a href="{{ route('studio.index') }}">
                                    <i class="fab fa-studiovinari"></i>
                                    <span data-key="t-dashboard">Studio</span>
                                </a>
                            </li>
                        @endcan
                        @can('bookings-list')
                            <li class="">
                                <a href="javascript: void(0);" class="has-arrow" aria-expanded="false">
                                    <i class="fas fa-bookmark"></i>
                                    <span data-key="t-maps">Bookings</span>
                                </a>
                                <ul class="sub-menu mm-collapse" aria-expanded="false">
                                    <li><a href="{{ route('bookingsview', 'today') }}?booking_status=1"
                                            data-key="t-g-maps">Today
                                            Booking</a></li>
                                    <li><a href="{{ route('bookingsview', 'upcoming') }}?booking_status=1"
                                            data-key="t-g-maps">Upcoming
                                            Booking</a></li>
                                    <li><a
                                            href="{{ route('bookingsview', 'past') }}?booking_status=1&approved_at=approved">Past
                                            Booking</a></li>

                                </ul>
                            </li>
                        @endcan
                        <li>
                            <a href="{{ route('notification') }}">
                                <i class="fas fa-bell"></i>
                                <span data-key="t-store">Notifications</span>
                            </a>
                        </li>
                        @can('promocodes-list')
                            <li>
                                <a href="{{ route('promo.index') }}">
                                    <i class="fas fa-code"></i>
                                    <span data-key="t-store">Promo Codes</span>
                                </a>
                            </li>
                        @endcan
                        @can('payments-list')
                            <li>
                                <a href="{{ route('transactions.index') }}">
                                    <i class="fas fa-money-check-alt"></i>
                                    <span data-key="t-store">Payments</span>
                                </a>
                            </li>
                        @endcan
                        @can('payments-list')
                            <li>
                                <a href="{{ route('users') }}">
                                    <i class="fas fa-users"></i>
                                    <span data-key="t-store">Users</span>
                                </a>
                            </li>
                        @endcan
                        <li>
                            <a href="{{ route('gallery.index') }}">
                                <i class="fas fa-images"></i>
                                <span data-key="t-store">Queires</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('queries') }}">
                                <i class="fas fa-id-card-alt"></i>
                                <span data-key="t-store">Queires</span>
                            </a>
                        </li>

                    </ul>
                </div>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="page-title">
                    @if ($title)
                        <h2>{{ $title }}</h2>
                    @endif
                </div>
                @yield('content')
            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> &copy; R&B Studios
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Design &amp; Develop by <a href="#!" class="text-decoration-underline">R & B
                                    Studios</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->




    <!-- JAVASCRIPT -->

    <script src="{{ url('public/js/js-bootstrap.bundle.min.js') }}"></script>
    <script src="{{ url('public/js/metismenu-metisMenu.min.js') }}"></script>
    <script src="{{ url('public/js/simplebar-simplebar.min.js') }}"></script>
    <script src="{{ url('public/js/node-waves-waves.min.js') }}"></script>
    <script src="{{ url('public/js/feather-icons-feather.min.js') }}"></script>
    <!-- pace js -->
    <script src="{{ url('public/js/pace-js-pace.min.js') }}"></script>

    @if (route('dashboard'))
        <!-- Plugins js-->
        <script src="{{ url('public/js/jquery.vectormap-jquery-jvectormap-1.2.2.min.js') }}"></script>
        <script src="{{ url('public/js/maps-jquery-jvectormap-world-mill-en.js') }}"></script>
        <!-- dashboard init -->
    @endif
    <script src="{{ url('public/js/pages-dashboard.init.js') }}"></script>

    <script src="{{ url('public/js/js-app.js') }}"></script>



</body>

</html>
