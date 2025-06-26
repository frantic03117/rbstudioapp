@extends('layouts.main')
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/1.5.5/perfect-scrollbar.min.js"
        integrity="sha512-X41/A5OSxoi5uqtS6Krhqz8QyyD8E/ZbN7B4IaBSgqPLRbWVuXJXr9UwOujstj71SoVxh5vxgy7kmtd17xrJRw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/1.5.5/css/perfect-scrollbar.min.css"
        integrity="sha512-ygIxOy3hmN2fzGeNqys7ymuBgwSCet0LVfqQbWY10AszPMn2rB9JY0eoG0m1pySicu+nvORrBmhHVSt7+GI9VA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        #end_slots h4 {
            font-size: 14px;
        }

        #end_slots label {
            border: 1px solid #B92D53;
            padding: 5px 8px;
            font-size: 14px;
        }
    </style>
@endsection
@section('content')
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="w-100 text-end mb-3">
                        <a href="{{ route('booking.index') }}" class="btn btn-gradient">Back</a>
                    </div>
                    <div class="w-100">
                        {!! Form::open(['route' => ['booking.update', $booking->id], 'method' => 'PUT', 'files' => 'true']) !!}
                        <div class="row gy-3">

                            <div class="col-md-3">
                                <label for="">Select Studio</label>
                                <select name="studio_id" onchange="activeSelectDate(event)" id="studio_id"
                                    class="form-select">
                                    <option value="">---Select---</option>
                                    @foreach ($studios as $s)
                                        <option value="{{ $s->id }}" @selected($booking->studio_id == $s->id)>{{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="">Select Service</label>
                                <select class="form-select" name="service_id" id="service_id">
                                    <option value="">---Select---</option>
                                    @foreach ($services as $s)
                                        <option value="{{ $s->id }}" @selected($booking->service_id == $s->id)>{{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="">Booking Start Date</label>
                                <input type="date" onchange="getEndDate(event)" min="{{ date('Y-m-d') }}"
                                    name="booking_start_date"
                                    value="{{ date('Y-m-d', strtotime($booking->booking_start_date)) }}"
                                    id="booking_start_date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="">Select Start Time</label>
                                <select class="form-select" name="start_slot" id="start_slot">
                                    <option value="">---Select---</option>
                                    @foreach ($slots as $s)
                                        <option value="{{ $s->id }}" @selected($booking->start_at == $s->start_at)>{{ $s->start_at }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-3">
                                <label for="">Select End Date</label>
                                <select onchange="get_EndTimes(event)" class="form-select" name="end_date" id="end_date_s">
                                    <option value="">---Select---</option>
                                    <option selected>{{ date('d-M-Y', strtotime($booking->booking_end_date)) }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="">Select End Time</label>
                                <select class="form-select" name="end_slot" id="end_time_s">
                                    <option value="">---Select---</option>
                                    <option value="{{ $booking->booking_end_date }}" selected>
                                        {{ date('h:i A', strtotime($booking->booking_end_date)) }}</option>
                                </select>
                            </div>



                            <div class="col-md-6">
                                <div class="row gy-2">
                                    <div class="col-md-6">
                                        <label for="">Enter Mobile</label>
                                        <input type="text" name="mobile" oninput="getuserData(event)" id="mobile"
                                            class="form-control" value="{{ $user?->mobile }}" required>
                                    </div>
                                    <div class="col-md-6 userdata">
                                        <label for="">Enter Name</label>
                                        {!! Form::text('name', $user?->name, [
                                            'class' => 'form-control',
                                            'id' => 'name',
                                            'required' => 'required',
                                        ]) !!}
                                    </div>
                                    <div class="col-md-12 userdata">
                                        <label for="">Enter Email</label>
                                        {!! Form::email('email', $user?->email, [
                                            'class' => 'form-control',
                                            'id' => 'email',
                                            'required' => 'required',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="w-100 bg-gradient pt-2 ps-2">
                                    <input type="checkbox" id="gst_applicable" @checked($dgst)
                                        onclick="gst_applicable_is(event)" /> <label for="gst_applicable">GST
                                        Applicable</label>
                                </div>
                            </div>
                            <div class="col-md-3 gst_box" @if (!$dgst) style="display:none;" @endif>
                                <label for="">Enter Address</label>
                                <input type="text" name="address" value="{{ $dgst?->address }}" class="form-control" />
                            </div>
                            <div class="col-md-3 gst_box" @if (!$dgst) style="display:none;" @endif>
                                <label for="">Enter GST</label>
                                <input type="text" name="gst" class="form-control" value="{{ $dgst?->gst }}" />
                            </div>

                            <div class="col-md-2 gst_box" @if (!$dgst) style="display:none;" @endif>
                                <label for="">Select State</label>
                                <select name="state_id" onchange="getCity(event)" class="form-select">
                                    <option value="">---Select---</option>
                                    @foreach ($states as $st)
                                        <option value="{{ $st->id }}" @selected($dgst?->state_id == $st->id)>
                                            {{ $st->state }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 gst_box" @if (!$dgst) style="display:none;" @endif>
                                <label for="">Select District</label>
                                <select name="city_id" id="city_id" class="form-select">
                                    <option value="">---Select---</option>
                                    @foreach ($cities as $st)
                                        <option value="{{ $st->id }}" @selected($dgst?->city_id == $st->id)>
                                            {{ $st->city }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 gst_box" @if (!$dgst) style="display:none;" @endif>
                                <label for="">Enter Pincode</label>
                                <input type="text" name="pincode" value="{{ $dgst?->pincode }}"
                                    class="form-control" />
                            </div>

                            <div class="col-md-12">
                                <button class="btn btn-gradient shadow">Submit</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        const gst_applicable_is = (e) => {
            if (e.target.checked == true) {
                $(".gst_box").css('display', 'block');
            } else {
                $(".gst_box").css('display', 'none');
            }
        }
        const getStudiosList = (e) => {
            $("#slots").html('');
            $("#booking_start_date").val('')
            let vid = e.target.value;
            $.post("{{ route('ajax_studios') }}", {
                vendor_id: vid
            }, function(res) {
                $("#studio_id").html(res)
            })
        };
        const getuserData = (e) => {
            const val = e.target.value;
            if (val.length > 9) {
                $.post("{{ route('ajax_user') }}", {
                    mobile: val
                }, function(res) {
                    if (res?.success) {
                        $("#name").val(res.data.name)
                        $("#email").val(res.data.email)
                        $(".userdata").css('display', 'block');
                    } else {
                        $("#name").val('')
                        $("#email").val('')
                        $(".userdata").css('display', 'block');
                    }

                })
            }
        }

        const activeSelectDate = (e) => {
            let drt = parseInt($("#duration").val());
            if (e.target.value) {
                $("#booking_start_date").removeAttr('disabled')
            } else {
                $("#booking_start_date").attr('disabled', 'disabled')
            }
            if ($("#booking_start_date").val()) {
                getSlots(drt);
            }
        };
        const getEndDate = (e) => {
            get_slot_start();
        };
        const get_slot_start = () => {
            let output = " <option value=''>---Select---</option>";
            const sdate = $("#booking_start_date").val();
            let sid = $("#studio_id").val();
            let bid = "{{ $booking->id }}";
            $.post("{{ route('find_start_slot') }}", {
                sdate: sdate,
                studio_id: sid,
                isEdit: true,
                booking_id: bid
            }, function(res) {
                if (res?.success) {
                    res.data.forEach((slt) => {
                        output +=
                            `<option value="${slt.id}">${moment(sdate + ' ' +slt.start_at).format('hh:mm A')}</option>`;
                    });
                    $("#start_slot").html(output);
                }
            });
        }
        const getSlots = (drt) => {
            let output = "";
            let edate = $("#booking_end_date").val();
            let sdate = $("#booking_start_date").val();
            let sid = $("#studio_id").val();
            $.post("{{ route('ajax_slots') }}", {
                sdate: sdate,
                studio_id: sid,
                duration: drt
            }, function(res) {
                if (res?.success) {
                    res.data.forEach((slt, i) => {
                        output += `<label for="labelslot${i}" id="labels${i}" type="button" onclick="addSelected(event, ${i})" class="btn p-1 slotbtn fw-bold  text-center relative  shadow-custom">
                                            <p class="mb-1 pointer-events-none text-nowrap">${moment(slt[0]).format('DD-MMM-YYYY hh:mm A')} </p>
                                            <p class="mb-1  pointer-events-none">To</p>
                                            <p class="mb-0  pointer-events-none">${moment(slt[1]).format('DD-MMM-YYYY hh:mm A')} </p>
                                        <input type="radio" name="slot_time" value="${slt}" id="labelslot${i}" class="position-absolute start-0 top-0 hidden"  />
                                    </label>`
                    });
                    $("#slots").html(output)
                } else {
                    let emsg = `<div class="alert w-100 alert-danger">No Slot availabe. Try again.</div>`;
                    $("#slots").html(emsg)
                }

            })

        };
        const addSelected = (e, i) => {

            $(".slotbtn").removeClass('btn-gradient');
            $(`#labels${i}`).addClass('btn-gradient')

        };

        $("#start_slot").on('change', function() {
            const slot_id = $(this).val();
            const sdate = $("#booking_start_date").val();
            let sid = $("#studio_id").val();
            let output = ``;
            let vd = sid;
            $.post(`{{ route('find_end_slot') }}`, {
                booking_id: "{{ $booking->id }}",
                sdate: sdate,
                studio_id: sid,
                slot_id: slot_id
            }, function(res) {
                if (res?.success) {
                    generate_dates(res.data);
                    res.data.forEach((slt, i) => {
                        if (slt.split(' ')[0] != vd) {
                            vd = slt.split(' ')[0];
                            output += `<h4 class="d-block w-100">${vd}</h4>`;
                        }
                        output += `<li>
                                        <label class="cursor-pointer" for="endslt${i}">
                                                <input type="radio" name="end_slot" id="endslt${i}" value='${slt}'>
                                                ${moment(slt).format('hh:mm A')}
                                        </label>
                                    </li>
                                    `
                    });
                    $("#end_slots").html(output);
                }
            })
        });

        function generate_dates(timestamps) {
            const uniqueDates = [];
            timestamps.forEach(timestamp => {
                const date = moment(timestamp).format('YYYY-MM-DD');
                if (!uniqueDates.includes(date)) {
                    uniqueDates.push(date);
                }
            });
            let output = '<option value="">---Select---</option>';
            uniqueDates.forEach(arr => {
                output += `<option value="${arr}">${moment(arr).format('DD-MM-YYYY')}</option>`;
            });
            $("#end_date_s").html(output)
        }
        const get_EndTimes = (e) => {
            const sdate = $("#booking_start_date").val();
            let sid = $("#studio_id").val();
            const val = e.target.value;
            const slot_id = $("#start_slot").val();
            let output = "";

            $.post(`{{ route('find_end_slot') }}`, {
                booking_id: "{{ $booking->id }}",
                sdate: sdate,
                studio_id: sid,
                slot_id: slot_id
            }, function(res) {
                const istamps = res.data;
                const arr = istamps.filter(tsmp => tsmp.split(' ')[0] == val);
                arr.forEach(ar => {
                    output += `<option value="${ar}">${moment(ar).format('hh:mm A')}</option>`;
                });
                $("#end_time_s").html(output)
            });

        }
        const getCity = (e) => {
            const cid = e.target.value;
            let aurl = "{{ route('ajax_cities') }}"
            $.post(`${aurl}`, {
                state_id: cid
            }, function(res) {
                $("#city_id").html(res);
            })
        };
    </script>
@endsection
