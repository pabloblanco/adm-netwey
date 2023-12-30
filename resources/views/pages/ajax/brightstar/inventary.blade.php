<div class="col-md-12">
	@if($error)
		<div class="card card-inverse card-danger text-center text-white">
	        <div class="card-block">
	            <p>Ocurrio un error consultando el inventario.</p>
	        </div>
	    </div>
    @else
		<div class="row">
			@foreach($devices as $device)
		        <div class="col-md-6">
		            <div class="card bg-theme m-b-15 text-center text-dark">
		                <div class="card-block">
		                    <label class="text-white control-label">{{$device->quantity}}</label>
		                    <footer class="text-white">{{$device->title}}</footer>
		                    <footer class="text-white">({{$device->desc}})</footer>
		                </div>
		            </div>
		        </div>
		    @endforeach
		</div>
	@endif
</div>