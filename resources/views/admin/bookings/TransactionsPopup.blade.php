<!-- Modal -->

<div class="modal fade" id="staticBackdropTransactions{{ $bid }}" data-bs-backdrop="static"
    data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropTransactions{{ $bid }}"
    aria-hidden="true">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropTransactions{{ $bid }}">List of Transactions
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Mode </th>
                            <th>Transaction ID</th>
                            <th>Transaction Date</th>
                            <th>Amount</th>
                            <th>Transaction Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @foreach ($booking->transactions as $k => $tr)
                            <tr>
                                <td>
                                    {{ $k + 1 }}
                                </td>
                                <td>
                                    {{ $tr->mode }}
                                </td>
                                <td>
                                    {{ $tr->transaction_id ?? $tr->gateway_order_id }}
                                </td>
                                <td>
                                    {{ $tr->transaction_date }}
                                </td>
                                <td>
                                    {{ $tr->amount }}
                                </td>
                                <td>
                                    {{ $tr->status }}
                                </td>
                                <td>
                                    @if ($tr->mode != 'Razorpay')
                                        <form
                                            action="{{ route('transactions.destroy', ['transaction' => $tr, 'id' => $tr->id]) }}"
                                            method="post">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
