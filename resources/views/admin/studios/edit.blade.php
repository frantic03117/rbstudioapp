@extends('layouts.main')
@section('content')
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">

                    <div class="w-100 mb-2 text-end">
                        <a href="{{ route('studio.index') }}" class="btn btn-gradient">Back</a>
                    </div>
                    <div class="w-100">
                        {!! Form::open(['route' => ['studio.update', $studio->id], 'method' => 'PUT', 'files' => 'true']) !!}
                        <input type="hidden" name="services" class="w-100 d-block" id="services" />
                        <div class="row gy-4 ">
                            <div class="col-md-12 p-1 bg-gradient">
                                Personal Details
                            </div>
                            <div class="col-md-3">
                                <label for="">Select Vendor</label>
                                <select name="vendor_id" id="vendor_id" class="form-select">
                                    <option value="">--Select---</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}" @selected($v->id == $studio->vendor_id)>{{ $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="">Studio name</label>
                                {!! Form::text('name', $studio->name, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-3">
                                <label for="">Studio Mobile</label>
                                {!! Form::text('mobile', $studio->mobile, ['class' => 'form-control']) !!}
                            </div>

                            <div class="col-md-6">
                                <div class="w-100 d-flex justify-content-between mb-2">
                                    <label for="">Enter Charges</label>
                                    <button data-bs-toggle="modal" onclick="addNewServices('{{ $studio->id }}')"
                                        data-bs-target="#staticBackdrop" type="button" class="btn btn-sm btn-warning">Add
                                        New</button>
                                </div>

                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Sr No</th>
                                            <th>Service</th>
                                            <th>Amount</th>
                                            <th>Is Permissable</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="service_amount_box">
                                        @foreach ($s_services as $i => $s)
                                            <tr>
                                                <td>
                                                    {{ $i + 1 }}
                                                </td>
                                                <td>
                                                    {{ $s->name }}
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control"
                                                            name="charge{{ $s->id }}" id="charge{{ $s->id }}"
                                                            value="{{ $s->charge }}" />
                                                        <button type="button" onclick="updateCharge({{ $s->id }})"
                                                            class="btn btn-gradient">Update</button>
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ $s->is_permissable == '1' ? 'Yes' : 'No' }}
                                                </td>
                                                <td>

                                                    <a href="{{ route('delete_s_service', $s->id) }}"
                                                        class="btn btn-sm btn-danger ">Delete</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-2">
                                @php
                                    $stimes = ['05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00'];
                                    $etimes = ['20:00', '21:00', '22:00', '23:00'];
                                @endphp
                                <label for="">Booking Start At</label>

                                <select class="form-select" name="opens_at">
                                    <option value="">---Select---</option>
                                    @foreach ($stimes as $s)
                                        <option value="{{ $s }}" @selected(date('H:i', strtotime($studio->opens_at)) == $s)>
                                            {{ $s }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Booking End At</label>
                                <select class="form-select" name="ends_at">
                                    <option value="">---Select---</option>
                                    @foreach ($etimes as $s)
                                        <option value="{{ $s }}" @selected(date('H:i', strtotime($studio->ends_at)) == $s)>
                                            {{ $s }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-md-12 p-1 bg-gradient">
                                Address Details
                            </div>
                            <div class="col-md-4">
                                <label for="">Address</label>
                                {!! Form::text('address', $studio->address, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-2">
                                <label for="">Select Country</label>
                                <select name="country_id" onchange="getStates(event)" id="country_id" class="form-select">
                                    <option value="" selected @disabled(true)>---Select---</option>
                                    @foreach ($countries as $c)
                                        <option value="{{ $c->id }}" @selected($studio->country_id == $c->id)>
                                            {{ $c->country }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Select State</label>
                                <select name="state_id" onchange="getCity(event)" id="state_id" class="form-select">
                                    <option value="" selected @disabled(true)>---Select---</option>
                                    @foreach ($states as $s)
                                        <option value="{{ $s->id }}" @selected($studio->state_id == $s->id)>
                                            {{ $s->state }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Select City</label>
                                <select name="district_id" id="city_id" class="form-select">
                                    <option value="" selected @disabled(true)>---Select---</option>
                                    @foreach ($cities as $s)
                                        <option value="{{ $s->id }}" @selected($studio->district_id == $s->id)>
                                            {{ $s->city }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter Pincode</label>
                                {!! Form::text('pincode', $studio->pincode, ['class' => 'form-control', 'minlength' => '6', 'maxlength' => '6']) !!}
                            </div>
                            <div class="col-md-12">
                                <label for="">Enter Google Map URL</label>
                                {!! Form::text('google_map', $studio->google_map, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter Longitude </label>
                                {!! Form::text('longitude', $studio->longitude, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter Latitude</label>
                                {!! Form::text('latitude', $studio->latitude, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-2">
                                <label for="">Alert Color</label>
                                {!! Form::color('color', $studio->color, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-6">
                                <label for="">studio Info</label>
                                <textarea name="description" id="editor1" cols="30" rows="10">{!! $studio->description !!}</textarea>
                                <script>
                                    CKEDITOR.replace('editor1')
                                </script>
                            </div>
                            <div class="col-md-6">
                                <label for="">Equipment Info</label>
                                <textarea name="equipment_info" id="equipment_info" cols="30" rows="10">{!! $studio->equipment_info !!}</textarea>
                                <script>
                                    CKEDITOR.replace('equipment_info')
                                </script>
                            </div>
                            <div class="col-md-12 p-1 bg-gradient">
                                Terms & Condition
                            </div>

                            <div class="col-md-12">
                                <textarea name="terms" id="editor" cols="30" rows="10">{!! $studio->terms !!}</textarea>
                                <script>
                                    CKEDITOR.replace('editor')
                                </script>

                            </div>
                            <div class="col-md-12">
                                <label for=""></label>
                                <button class="btn btn-gradient px-lg-5">Submit</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>



    <div class="modal fade" id="staticBackdropEdit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Edit Service Charges</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Add New Service</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('studio.add_studio_service') }}" method="POST">
                        @csrf
                        <div class="form-group mb-2">
                            <input type="hidden" name="studio_id" value="{{ $studio->id }}" />
                            <label for="">Select Service</label>
                            <select class="form-select" name="service_id" id="service_ids">
                                <option value="">---Select----</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label for="">Enter Charge</label>
                            <input type="text" name="charge" class="form-control">
                        </div>
                        <div class="form-group mb-4">
                            <label for="">Is Permissible</label>
                            <select name="is_permissable" class="form-select">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const addNewServices = (id) => {
            $.post("{{ route('get_rest_services') }}", {
                studio_id: id
            }, function(res) {
                $("#service_ids").html(res)
            })
        };
    </script>




    <script>
        const getImagesPreview = (e) => {
            const images = document.getElementById('images');
            const files = e.target.files;
            let reader = new FileReader();
            images.innerHTML = '';
            files.forEach((imageFile) => {
                const reader = new FileReader();
                reader.readAsDataURL(imageFile);
                reader.addEventListener('load', () => {
                    images.innerHTML += `
                                    <div class="image_box inline-block">
                                        <img src='${reader.result}' width="100">
                                    </div>
                                `;
                });
            })
            $("#images").html(output)
        }

        const getStates = (e) => {
            const cid = e.target.value;
            let aurl = "{{ route('ajax_states') }}";
            $.post(`${aurl}`, {
                country_id: cid
            }, function(res) {
                $("#state_id").html(res);
            });
        };
        const getCity = (e) => {
            const cid = e.target.value;
            let aurl = "{{ route('ajax_cities') }}";
            $.post(`${aurl}`, {
                state_id: cid
            }, function(res) {
                $("#city_id").html(res);
            });
        };
        let arr = {!! json_encode($s_services) !!};

        let ahtml = "";


        $(".service_box").each(function(e) {
            $(this).on('click', function() {
                const isChecked = $(this).is(":checked");
                const sval = $(this).val();
                const text = $(this).data('text');

                if (isChecked) {
                    arr.push({
                        'id': sval,
                        'text': text,
                        'amount': "",
                        'isPermissable': "0"
                    });
                } else {
                    let idx = arr.findIndex(obj => obj.id == sval);
                    arr.splice(idx, 1);
                }
                let output = ``
                arr.forEach((ar, i) => {
                    output += ` <tr>
                        <td>${i + 1}</td>
                        <td>${ar.text}</td>
                        <td>
                            <input type="text" width="100" required oninput="addAmount(event, ${i})" class="form-control" value="${ar.amount}" name="service_amount[]" style="width:200px"/>
                        </td>
                        <td>
                            <div class="form-check form-switch form-switch-lg mb-3" dir="ltr">
                                <input type="checkbox" onclick="addToPermissable(event,  ${i})" class="form-check-input" name="isPermissable[]" value="1" id="customSwitchsizelg" />
                            </div>
                        </td>
                        </tr>`;
                });

                $("#service_amount_box").html(output);
            })
        })
        const addAmount = (e, i) => {
            const val = e.target.value;
            arr[i].amount = val;
            $("#services").val(JSON.stringify(arr));
        }
        const addToPermissable = (e, id) => {

            if (e.target.checked) {
                arr[id].isPermissable = "1"
            } else {
                arr[id].isPermissable = "0"
            }
            $("#services").val(JSON.stringify(arr));
        }
        const updateCharge = (id) => {
            const charge = $(`#charge${id}`).val();
            $.post("{{ route('studio.update_s_service') }}", {
                id: id,
                charge: charge
            }, function(res) {
                location.reload();
            })
        }
    </script>
@endsection
