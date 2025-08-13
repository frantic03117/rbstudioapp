<!-- Modal -->

<div class="modal fade" id="staticBackdropdiscount{{ $bid }}" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel{{ $bid }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel{{ $bid }}">Add Discount Amount</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" class="d-block w-100 ">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="booking_id" value="{{ $bid }}">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="">Enter Discount</label>
                                <div class="input-group">
                                    <input type="number" name="amount" id="amount" value="{{ $discount }}"
                                        class="form-control">
                                    <select name="discount_type" id="">
                                        <option value="Fixed">Fixed</option>
                                        <option value="Percent">Percent</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn d-block  btn-primary">Add Amount</button>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
