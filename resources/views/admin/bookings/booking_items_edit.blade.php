 @php

     use App\Http\Controllers\BookingController;
     $ritems = BookingController::resource_items($booking->studio->id, $bid);

 @endphp

 <div class="modal fade" id="staticBackdropRent{{ $bid }}{{ $booking->studio->id }}" data-bs-backdrop="static"
     data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Item</h1>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <table class="table">
                     <thead>
                         <tr>
                             <th>Sr No</th>
                             <th>Rentel Item</th>
                             <th>Uses Hours</th>
                             <th>Rent</th>
                             <th>Action</th>
                         </tr>
                     </thead>
                     <tbody>
                         @foreach ($booking->rents as $k => $item)
                             <tr>
                                 <td>
                                     {{ $k + 1 }}
                                 </td>
                                 <td>
                                     {{ $item->name }}
                                 </td>
                                 <td>
                                     <form action="{{ route('update_rental_item_in_booking') }}" method="post">
                                         @csrf
                                         <input type="hidden" name="booking_id" value="{{ $item->pivot->booking_id }}">
                                         <input type="hidden" name="item_id" value="{{ $item->pivot->item_id }}">
                                         <div class="input-group">
                                             <input type="number" name="uses_hours" min="0"
                                                 value="{{ $item->pivot->uses_hours }}" class="form-control"
                                                 id="">
                                             <button class="btn btn-primary">Update</button>
                                         </div>
                                     </form>

                                 </td>
                                 <td>
                                     {{ $item->pivot->uses_hours * $item->pivot->charge }}
                                 </td>
                                 <td>
                                     <form
                                         onsubmit="return confirm('Are you sure you want to remove this item from the booking?');"
                                         action="{{ route('remove_rental_item_from_booking') }}" method="post">
                                         @csrf
                                         <input type="hidden" name="booking_id"
                                             value="{{ $item->pivot->booking_id }}">
                                         <input type="hidden" name="item_id" value="{{ $item->pivot->item_id }}">
                                         <button class="btn btn-danger btn-sm">Delete</button>
                                     </form>
                                 </td>
                             </tr>
                         @endforeach
                     </tbody>
                 </table>
             </div>

         </div>
     </div>
 </div>
