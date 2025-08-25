<!-- Modal -->

<div class="modal fade" id="staticBackdropTransactions{{ $bid }}" data-bs-backdrop="static"
    data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropTransactions{{ $bid }}"
    aria-hidden="true">
    <div class="modal-dialog">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
<script>
    const getBookingTransactions = () => {
        const trnsroute = "route('api_transactions')?booking_id={{ $bid }}";
        const resp = $.get(trnsroute);
        console.log(resp);
    };
</script>
