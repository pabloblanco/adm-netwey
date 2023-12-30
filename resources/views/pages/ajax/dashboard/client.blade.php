<div class="col-md-6 {{ $pt }}">
	<div class="card card-outline-primary text-center text-dark">
	    <div class="card-block">
	    	<header>{{{ $title }}}</header>
			<hr>
	    	<div class="row">
	    		<div class="preloader-ajax" id="client{{ $type }}">
			        <div class="cssload-speeding-wheel"></div>
			    </div>
	        	<div class="col-md-6">
	        		<div class="card card-outline-primary text-center text-dark">
	                    <div class="card-block">
	                        <label id="clientact{{ $type }}" class="control-label"></label>
	                        <footer>Clientes activos</footer>
	                    </div>
	                </div>
	        	</div>
	        	<div class="col-md-6">
	        		<div class="card card-outline-primary text-center text-dark">
	                    <div class="card-block">
	                        <label id="clientnct{{ $type }}" class="control-label"></label>
	                        <footer>Clientes inactivos</footer>
	                    </div>
	                </div>
	        	</div>
	        </div>
	        <br>
	    	<header>Totales</header>
			<hr>
	    	<div class="row">
	        	<div class="col-md-6">
	        		<div class="card card-outline-primary text-center text-dark">
	                    <div class="card-block">
	                        <label id="UpsT{{ $type }}" class="control-label"></label>
	                        <footer>Altas totales</footer>
	                    </div>
	                </div>
	        	</div>
	        	<div class="col-md-6">
	        		<div class="card card-outline-primary text-center text-dark">
	                    <div class="card-block">
	                        <label id="RechT{{ $type }}" class="control-label"></label>
	                        <footer>Recargas totales</footer>
	                    </div>
	                </div>
	        	</div>
	        </div>
	    </div>
	</div>
</div>