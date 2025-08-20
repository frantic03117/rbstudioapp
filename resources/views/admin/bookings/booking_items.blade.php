 @php

     use App\Http\Controllers\BookingController;
     $ritems = BookingController::resource_items($sid, $bid);

 @endphp

 <div class="modal fade" id="staticBackdrop{{ $bid }}{{ $sid }}" data-bs-backdrop="static"
     data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Item</h1>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 {!! Form::open(['route' => ['booking_item.add', $bid]]) !!}
                 <input type="hidden" name="booking_id" value="{{ $bid }}">
                 <input type="hidden" name="studio_id" value="{{ $sid }}">
                 <div class="form-group mb-2">
                     <label for="">Select Item</label>
                     <select name="item_id" id="" class="form-select">
                         <option value="">---Select---</option>
                         @foreach ($ritems as $item)
                             <option value="{{ $item->id }}">{{ $item->name }}</option>
                         @endforeach
                     </select>
                 </div>
                 <div class="form-group mb-2">
                     <label for="">Enter Hours</label>
                     <input type="number" name="uses_hours" id="uses_hours" class="form-control">
                 </div>
                 <div class="form-group mb-2">
                     <button class="btn w-100 btn-gradient rounded-pill">Add Item</button>
                 </div>
                 {!! Form::close() !!}
             </div>

         </div>
     </div>
 </div>
