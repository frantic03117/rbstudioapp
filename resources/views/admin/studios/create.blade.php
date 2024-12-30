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
                        {!! Form::open(['route' => 'studio.store', 'files' => 'true']) !!}
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
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="">Studio name</label>
                                {!! Form::text('name', old('name'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-5">
                                <label for="">Select Services</label>
                                <div class="d-flex flex-wrap gap-2  flex-wrap">
                                    @foreach ($services as $s)
                                        <label class="btn shadow border border-light btn-light"
                                            for="services{{ $s->id }}">
                                            <span class="">{{ $s->name }}</span>
                                            <input  type="checkbox" class="service_box" name="service_id[]" id="services{{ $s->id }}"
                                                value="{{ $s->id }}" data-text="{{$s->name}}">
                                        </label>
                                    @endforeach
                                </div>

                            </div>
                            <div class="col-md-8">
                                <label for="">Enter Charges</label>
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Sr No</th>
                                            <th>Service</th>
                                            <th>Amount</th>
                                            <th>Two Step Verification</th>
                                        </tr>
                                    </thead>
                                    <tbody id="service_amount_box">
                                        
                                    </tbody>
                                </table>
                            </div>
                           
                            <div class="col-md-2">
                                @php
                                    $stimes = ['05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00'];
                                    $etimes = ['20:00', '21:00','22:00', '23:00'];
                                @endphp
                                <label for="">Booking Start At</label>
                              
                                <select class="form-select" name="opens_at">
                                    <option value="">---Select---</option>
                                    @foreach($stimes as $s)
                                        <option value="{{$s}}">{{$s}}</option>
                                    @endforeach
                                    
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Booking End At</label>
                                 <select class="form-select" name="ends_at">
                                    <option value="">---Select---</option>
                                    @foreach($etimes as $s)
                                        <option value="{{$s}}">{{$s}}</option>
                                    @endforeach
                                    
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="w-100 h-100 border border-dashed cursor-pointer  border-danger p-4 rounded-3" for="upload_images">Upload Images
                                <input type="file" name="images[]" onchange="getImagesPreview(event)" id="upload_images"
                                    class="form-control d-none" multiple>
                                <div class="d-flex gap-2 flex-wrap" id="images"></div>
                            </label>
                            </div>



                            <div class="col-md-12 p-1 bg-gradient">
                                Address Details
                            </div>
                            <div class="col-md-2">
                                <label for="">Address</label>
                                {!! Form::text('address', old('address'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-2">
                                <label for="">Select Country</label>
                                <select name="country_id" onchange="getStates(event)" id="country_id" class="form-select">
                                    <option value="" selected @disabled(true)>---Select---</option>
                                    @foreach ($countries as $c)
                                        <option value="{{ $c->id }}">{{ $c->country }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Select State</label>
                                <select name="state_id" onchange="getCity(event)" id="state_id" class="form-select">
                                    <option value="" selected @disabled(true)>---Select---</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Select City</label>
                                <select name="district_id" id="city_id" class="form-select">
                                    <option value="" selected @disabled(true)>---Select---</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter Pincode</label>
                                {!! Form::text('pincode', old('pincode'), ['class' => 'form-control', 'minlength' => '6', 'maxlength' => '6']) !!}
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter Google Map</label>
                                {!! Form::text('google_map', old('google_map'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter Longitude </label>
                                {!! Form::text('longitude', old('longitude'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-2">
                                <label for="">Enter Latitude</label>
                                {!! Form::text('latitude', old('latitude'), ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-12">
                                <label for="">Enter Details</label>
                                <textarea name="description" id="editor1" cols="30" rows="10"></textarea>
                                <script>
                                    CKEDITOR.replace('editor1')
                                </script>
                            </div>
                            <div class="col-md-12 p-1 bg-gradient">
                                Terms & Condition
                            </div>

                            <div class="col-md-12">
                                <textarea name="terms" id="editor" cols="30" rows="10"></textarea>
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
        let arr = [];
        $(".service_box").each(function(e){
            $(this).on('click', function(){
                const isChecked = $(this).is(":checked");
                const sval = $(this).val();
                const text= $(this).data('text');
               
                if(isChecked){
                     arr.push({'id' : sval, 'text': text, 'amount': "", 'isPermissable' : "0"});
                }else{
                   let idx = arr.findIndex(obj => obj.id == sval);
                   arr.splice(idx, 1);
                }
                let output = ``
                arr.forEach((ar,i) => {
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
           
            if(e.target.checked){
                arr[id].isPermissable = "1"
            }else{
                arr[id].isPermissable = "0"
            }
            $("#services").val(JSON.stringify(arr));
        }
    </script>
@endsection
