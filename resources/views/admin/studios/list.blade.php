@extends('layouts.main')
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/1.5.5/perfect-scrollbar.min.js"
        integrity="sha512-X41/A5OSxoi5uqtS6Krhqz8QyyD8E/ZbN7B4IaBSgqPLRbWVuXJXr9UwOujstj71SoVxh5vxgy7kmtd17xrJRw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/1.5.5/css/perfect-scrollbar.min.css"
        integrity="sha512-ygIxOy3hmN2fzGeNqys7ymuBgwSCet0LVfqQbWY10AszPMn2rB9JY0eoG0m1pySicu+nvORrBmhHVSt7+GI9VA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.css"
        integrity="sha512-WEQNv9d3+sqyHjrqUZobDhFARZDko2wpWdfcpv44lsypsSuMO0kHGd3MQ8rrsBn/Qa39VojphdU6CMkpJUmDVw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"
        integrity="sha512-IsNh5E3eYy3tr/JiX2Yx4vsCujtkhwl7SLqgnwLNgf04Hrt9BT9SXlLlZlWx+OK4ndzAoALhsMNcCmkggjZB1w=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection
@section('content')
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="w-100 mb-3 text-end">
                        <a href="{{ route('studio.create') }}" class="btn btn-gradient">Add New</a>
                    </div>
                    <div class="w-100 table-responsive">
                        <table class="table table-sm  table-bordered border-dark table-warning">
                            <thead>
                                <tr class="bg-gradient text-white">
                                    <th class="bg-gradient text-white">Sr No</th>
                                    <th class="bg-gradient text-white">Studio</th>

                                    <th class="bg-gradient text-white">Images</th>
                                  
                                      <th class="bg-gradient text-white">Charges</th>
                                    <th class="bg-gradient text-white">Rental Resources</th>
                                  
                                    
                                    <th class="bg-gradient text-white">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($studio as $i => $s)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>

                                        <td style="max-width: 300px;" class="text-wrap">
                                            <p>
                                                {{ $s->name }}
                                            </p>
                                            {{ $s->address . ' ' . $s?->country?->country . ' ' . $s?->state?->state . ' ' . $s?->pincode }}
                                             <ul class="list-unstyled">
                                                <li>
                                                    <b>Owner Name : </b> {{ $s->vendor?->name }}
                                                </li>
                                                <li>
                                                    <b>Mobile : </b> {{ $s->vendor?->mobile }}
                                                </li>
                                                <li>
                                                    <b>Email : </b> {{ $s->vendor?->email }}
                                                </li>
                                            </ul>
                                            <div class="google_map">
                                                {!! $s->google_map !!}
                                            </div>
                                        </td>
                                        <td style="width:230px;">

                                            <ul id="images{{ $i }}" class=" d-flex  flex-wrap list-unstyled"
                                                style="width: 230px;height:150px;overflow-y:auto;">
                                                @foreach ($s->images as $img)
                                                    <li>
                                                        <a class="popup-link me-1 mb-1"
                                                            href="{{ url('public/' . $img->image) }}">
                                                            <img src="{{ url('public/' . $img->image) }}" alt=""
                                                                class="img-fluid" style="width:50px;">
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <script>
                                                new PerfectScrollbar(document.getElementById('images{{ $i }}'));
                                            </script>
                                            <button onclick="getImages({{ $s->id }})" class="btn btn-gradient"
                                                data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i
                                                    class="fas fa-file-image"></i></button>
                                        </td>
                                        <td>
                                            <ul class="list-unstyled" style="min-width:150px;">
                                                @foreach($s->charges as $c)
                                                <li>
                                                    <div class="d-flex justify-content-between">
                                                        <span>
                                                            {{$c->name}}
                                                        </span>
                                                        <span>
                                                            {{$c->charge->charge}}
                                                        </span>
                                                        <span>
                                                            <div class="form-check form-switch form-switch-md mb-3" dir="ltr">
                                                                <input type="checkbox" onclick="setIsPermissable({{$c->charge->id}})" class="form-check-input"  @checked($c->charge->is_permissable)>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </li>
                                                 @endforeach
                                            </ul>
                                        </td>
                                        <td style="width: 200px;">
                                            <div class="position-relative" id="rent{{ $i }}">
                                                <ul class="list-unstyled" style="height: 250px;">
                                                    @foreach ($s->products as $p)
                                                        <li class="mb-2">
                                                            <div class="d-flex justify-content-between">
                                                                <span>
                                                                    <img src="{{ url($p->icon) }}" width="50"
                                                                        alt="" class="bg-white img-fluid">
                                                                    {{ $p->name }}
                                                                </span>
                                                                <span>
                                                                    <i class="fas fa-rupee-sign"></i>
                                                                    {{ $p?->resources?->charge }}
                                                                </span>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <script>
                                                new PerfectScrollbar(document.getElementById('rent{{ $i }}'), {
                                                    wheelPropagation: false,
                                                    wheelSpeed: 2,
                                                });
                                            </script>
                                        </td>
                                        
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('add_resource', $s->id) }}"
                                                    class="btn btn-primary btn-sm" data-bs-title="Add Rental Resources"
                                                    data-bs-toggle="tooltip"> Add</a>
                                                <a href="{{ route('studio.edit', $s->id) }}" class="btn btn-warning btn-sm"
                                                    data-bs-title="Edit Studio Details" data-bs-toggle="tooltip"> Edit</a>
                                                <!--{!! Form::open(['route' => ['studio.destroy', $s->id]]) !!}-->
                                                <!--<button class="btn btn-sm btn-danger">Delete</button>-->
                                                <!--{!! Form::close() !!}-->
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        const setIsPermissable = (id) => {
            $.post("{{route('set_permissiable')}}", {id : id}, function(res){
                console.log(res);
            })
        }
    </script>

    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="w mb-3" id="images"></div>
                    <form enctype="multipart/form-data" id="ajaxImage">
                        <div class="input-group">
                            <input type="hidden" name="studio_id" id="studio_id">
                            <input type="file" name="file" id="file" class="form-control">
                            <button class="btn btn-gradient">Add Image</button>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>
    <script>
        const getImages = (id) => {
            $("#studio_id").val(id);
            $.post(`{{ route('ajax_studio_images') }}`, {
                studio_id: id
            }, function(res) {
                $("#images").html(res)
            })
        }
        $(document).ready(function() {
            $('.popup-link').magnificPopup({
                type: 'image'
            });
        });
        $("#ajaxImage").on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: "{{ route('ajax_add_studio_images') }}",
                type: 'POST',
                data: formData,
                success: function(data) {
                    getImages(data.data)
                },
                cache: false,
                contentType: false,
                processData: false
            });
        });
        const deleteImage = (id) => {
            $.post(`{{ route('ajax_studio_image_delete') }}`, {
                image_id: id
            }, function(res) {

                getImages(res.data);
            })
        };
    </script>
@endsection
