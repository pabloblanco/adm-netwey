@foreach($servicios as $service)
    <div class="col-md-4 col-xs-12">
        <div class="white-box">
            <h3 class="box-title"> plan {{$service->title}} (${{$service->price_pay}}) </h3>
            <ul class="list-inline two-part">
                <li><i class="icon-people text-success"></i></li>
                <li class="text-right">
                    <span class="counter" id="total">
                        {{empty($service->clients) ? 'N/A' : $service->clients}}
                    </span>
                </li>
            </ul>

            <hr style="border-top: 3px solid #eee;">

            <ul class="list-inline two-part">
                <li><i class="fa fa-percent text-success"></i></li>
                <li class="text-right">
                    <span class="counter" id="por">
                        {{empty($service->porClients) ? 'N/A' : $service->porClients}}
                    </span>
                </li>
            </ul>
        </div>
    </div>
@endforeach

@if(!empty($dataH))
<div class="col-md-4 col-xs-12">
    <div class="white-box">
        <h3 class="box-title"> Hoppers </h3>
        <ul class="list-inline two-part">
            <li><i class="icon-people text-success"></i></li>
            <li class="text-right"><span class="counter" id="total">{{$dataH['hoppers']}}</span></li>
        </ul>

        <hr style="border-top: 3px solid #eee;">

        <ul class="list-inline two-part">
            <li><i class="fa fa-percent text-success"></i></li>
            <li class="text-right"><span class="counter" id="por">{{$dataH['hoppersPorc']}}</span></li>
        </ul>
    </div>
</div>
@endif