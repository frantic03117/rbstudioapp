<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | B2B Make My Studio</title>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ url('public/css/css-login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>

<body>
    @if (count($errors))
        <script>
            toastr.error('Authorization Failed !', '')
        </script>
    @endif
    @if (session()->has('error'))
        <script>
            toastr.error('Authorization Failed !', '')
        </script>
    @endif


    <section class="auth-bg bg-yellow-100 vw-100 overflow-hidden">
        <div class="container-fluid p-0">
            <div class="login-box">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="logo">
                            <span class="logo-font">R & B</span> Studios
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="w-100">
                            <h3 class="header-title">LOGIN</h3>
                            <form method="POST" action="{{ route('login.store') }}" class="login-form w-100 bg-white">
                                @csrf
                                <div class="form-group mb-4">
                                    <input type="text" class="form-control" name="email"
                                        placeholder="Email or UserName">
                                </div>
                                <div class="form-group mb-4">
                                    <input type="Password" class="form-control" name="password" placeholder="Password">

                                </div>
                                <div class="form-group mb-4">
                                    <button class="btn btn-primary w-100 btn-block">LOGIN</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-sm-6 hide-on-mobile">

                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</body>

</html>
