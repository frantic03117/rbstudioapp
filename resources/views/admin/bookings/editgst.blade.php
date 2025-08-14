<!-- Modal -->

<div class="modal fade" id="staticBackdropdisgst{{ $bid }}" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel{{ $bid }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel{{ $bid }}">Edit GST Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                {!! Form::open(['route' => ['update_gst_details']]) !!}
                @csrf
                <div class="row">
                    <input type="hidden" name="booking_id" value="{{ $bid }}">
                    <div class="col-md-12 d-none mb-2">
                        <div class="w-100 bg-gradient pt-2 ps-2">
                            <input type="checkbox" id="gst_applicable" @checked($dgst)
                                onclick="gst_applicable_is(event)" />
                            <label for="gst_applicable">GST
                                Applicable</label>
                        </div>
                    </div>
                    <div class="col-md-12 gst_box"
                        @if (!$dgst) @class(['is-invalid']) @endif>

                        <label for="">Company Name</label>
                        <input type="text" name="company" value="{{ $dgst?->company }}" class="form-control" />
                    </div>
                    <div class="col-md-12 gst_box"
                        @if (!$dgst) @class(['is-invalid']) @endif>
                        <label for="">Enter Address</label>
                        <input type="text" name="address" value="{{ $dgst?->address }}" class="form-control" />
                    </div>
                    <div class="col-md-12 gst_box"
                        @if (!$dgst) @class(['is-invalid']) @endif>
                        <label for="">Enter GST</label>
                        <input type="text" name="gst" class="form-control" value="{{ $dgst?->gst }}" />
                    </div>

                    <div class="col-md-12 gst_box"
                        @if (!$dgst) @class(['is-invalid']) @endif>
                        <label for="">Select State</label>
                        <select name="state_id" onchange="getCity(event)" class="form-select">
                            <option value="">---Select---</option>
                            @foreach ($states as $st)
                                <option value="{{ $st->id }}" @selected($dgst?->state_id == $st->id)>
                                    {{ $st->state }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 gst_box"
                        @if (!$dgst) @class(['is-invalid']) @endif>
                        <label for="">Select District</label>
                        <select name="city_id" id="city_id" class="form-select">
                            <option value="">---Select---</option>
                            @foreach ([$dgst?->city] as $st)
                                <option value="{{ $st?->id }}" @selected($dgst?->city_id == $st?->id)>
                                    {{ $st?->city }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-12 gst_box"
                        @if (!$dgst) @class(['is-invalid']) @endif>
                        <label for="">Enter Pincode</label>
                        <input type="text" name="pincode" value="{{ $dgst?->pincode }}" class="form-control" />
                    </div>

                    <div class="col-md-12">
                        <button class="btn btn-gradient shadow">Submit</button>
                    </div>
                </div>
                {!! Form::close() !!}

            </div>
            <script>
                const getcityauto = () => {
                    const cid = "{{ $dgst?->state_id }}";
                    let aurl = "{{ route('ajax_cities') }}"
                    $.post(`${aurl}`, {
                        state_id: cid
                    }, function(res) {
                        $("#city_id").html(res);
                    })
                };
                // getcityauto();
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
        </div>
    </div>
</div>
