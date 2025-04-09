<!-- Modal -->

<div class="modal fade" id="staticBackdropExtra{{ $bid }}" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel{{ $bid }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel{{ $bid }}">Add Extra Amount</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('extra-amount.store') }}" method="POST" class="d-block w-100 ">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="booking_id" value="{{ $bid }}">
                        <div class="col-md-12">
                            <label for="">Enter Amount</label>
                            <div class="input-group">
                                <input type="number" name="amount" id="amount" class="form-control">
                                <button type="submit" class="btn d-block  btn-primary">Add Amount</button>
                            </div>
                        </div>

                    </div>
                </form>
                <div class="col-md-12">
                    <table class="table table-sm">
                        @foreach ($items as $i => $item)
                            <tr>
                                <td>
                                    {{ $i + 1 }}
                                </td>
                                <td>
                                    {{ $item->amount }}
                                </td>
                                <td>
                                    <form action="{{ route('extra-amount.destroy', $item['id']) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>

                                </td>
                            </tr>
                        @endforeach
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
