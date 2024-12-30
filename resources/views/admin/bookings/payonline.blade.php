<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    

    <section>
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-md-6">
                   <div class="w-100 " style="opacity: 0">
                       <form action="{{route('pay_now', $item->id)}}" name="myform" method="POST">
                           @csrf
                           <input type="hidden" name="booking_id" value="{{$item->id}}" />
                           <input type="hidden" name="isPartial" value="{{$isPartial}}" />
                           <div class="form-group mb-2">
                               <label for="">Total Amount</label>
                               <div class="form-control">
                                   {{$total =  ($item->duration*$item->studio_charge + $rentcharge)*1.18}}
                               </div>
                           </div>
                           <div class="form-group mb-2">
                               <label for="">Paid Amount</label>
                               <div class="form-control">{{$paid = $item->transactions_sum_amount}}</div>
                           </div>
                           <div class="form-group mb-2">
                               <label for="">Remain Amount</label>
                               <div class="form-control">{{$remain = $total - $paid}}</div>
                           </div>
                           <div class="form-group mb-2 text-center">
                               <button class="btn btn-primary">Submit</button>
                           </div>
                       </form>
                   </div>
                </div>
            </div>
        </div>
    </section>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  
  <script language='javascript'>document.myform.submit();</script>
  
  </body>
</html>

