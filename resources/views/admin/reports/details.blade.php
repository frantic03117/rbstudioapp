<div class="modal fade" id="staticBackdrop{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">
            {{$item->id}}
        </h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
            @foreach(json_decode($item->ret_resp)  as $key => $val)
                <div class="row">
                    <div class="col-6 border p-1  text-capitalize">
                      {!! implode(' ', explode('_', $key)) !!}
                    </div>
                    <div class="col-6 border p-1">
                        {{$val}}
                    </div>
                </div>
            @endforeach
      </div>
    </div>
  </div>
</div>