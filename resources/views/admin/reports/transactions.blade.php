@extends('layouts.main')

@section('content')
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="w-100 table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <td>Sr</td>
                                    <td>Date</td>
                                     <td>Type</td>
                                     <td>Mode</td>
                                     <td>Amount</td>
                                    <td>Booking</td>
                                    <td>User</td>
                                    <td>Studio</td>
                                    <td>Status</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $k => $item)
                                    <tr>
                                        <td>
                                            {{$k+1}}
                                        </td>
                                        <td>
                                            {{$item->transaction_date}}
                                        </td>
                                        <td>
                                            {{$item->type}}
                                        </td>
                                        <td>
                                            {{$item->mode}}
                                        </td>
                                        <td>
                                            {{$item->amount}}
                                        </td>
                                        <td>
                                            {{$item->booking_id}}
                                        </td>
                                        <td>
                                            {{$item->user_id}}
                                            <ul>
                                            @if($item->user)
                                                <li>
                                                    <p>{{$item->user?->name}}</p>
                                                </li>
                                                <li>
                                                    <p>{{$item->user?->email}}</p>
                                                </li>
                                                <li>
                                                    <p>{{$item->user?->mobile}}</p>
                                                </li>
                                            @endif
                                            </ul>
                                        </td>
                                        <td>
                                            {{$item->studio_id}}
                                        </td>
                                       
                                        <td>
                                            @if($item->status == "Success")
                                                <span class="badge bg-success  px-4 py-2  text-white">Success</span>
                                            @endif
                                            @if($item->status != "Success")
                                                <span class="badge bg-danger px-4 py-2 text-white">Failed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->ret_resp)
                                            <button data-bs-toggle="modal" data-bs-target="#staticBackdrop{{$item->id}}" class="btn btn-sm btn-primary">View Details</button>
                                            @include('admin.reports.details', ['item' => $item])
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
    </section>
@endsection
