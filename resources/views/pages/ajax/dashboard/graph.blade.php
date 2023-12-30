<!--altas hbb-->
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
        	<div class="card card-outline-primary text-center text-dark">
                <div class="card-block text-left">
                    <header>
                    	<center><h3>{{{ $title }}}</h3></center>
                    </header>
                    <hr>
                    <div class="row">
                    	<div class="preloader-ajax" id="loader{{ $type.$sType }}">
					        <div class="cssload-speeding-wheel"></div>
					    </div>
                    	<div class="col-md-12">
                    		<div class="row">
	                            <div class="col-md-4">
	                                <div class="card m-b-15 text-center text-dark {{ $colorMet }}">
					                    <div class="card-block">
					                        <label id="upsD{{ $type.$sType }}" class="text-white control-label"></label>
					                        <footer class="text-white">Hoy</footer>
					                    </div>
					                </div>
	                            </div>
	                            <div class="col-md-4">
	                                <div class="card m-b-15 text-center text-dark {{ $colorMet }}">
					                    <div class="card-block">
					                        <label id="upsT{{ $type.$sType }}" class="text-white control-label"></label>
					                        <footer class="text-white">Totales en el mes</footer>
					                    </div>
					                </div>
	                            </div>
	                            <div class="col-md-4">
	                                <div class="card m-b-15 text-center text-dark {{ $colorMet }}">
					                    <div class="card-block">
					                        <label id="upsZ{{ $type.$sType }}" class="text-white control-label"></label>
					                        <footer class="text-white">ultimos tres meses</footer>
					                    </div>
					                </div>
	                            </div>
                    		</div>
                    	</div>
                    </div>
                    <hr>
                    <div class="row">
                    	<div class="col-md-4">
                    		<select name="upsinterval{{ $type.$sType }}" id="upsinterval{{ $type.$sType }}" class="form-control intervalGrap" data-type="{{ $sType }}" data-g="ups{{ $type.$sType }}" data-device="{{ $type }}">
                    			<option value="daily">Ayer</option>
                    			<option value="weekly">Semana</option>
                    			<option value="monthly">Mes</option>
                    			<option value="quarterly" selected="true">Trimestre</option>
                    		</select>
                    	</div>
                    </div>
                    <hr>
                    <div class="row">
                    	<div class="col-md-12">
                            <div class="card card-outline-primary text-center text-dark">
			                    <div class="card-block">
			                    	<div id="ups{{ $type.$sType }}" data-label="{{ $type.$sType }}" style="height: 300px; width: 100%;"></div>
			                    </div>
			                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>