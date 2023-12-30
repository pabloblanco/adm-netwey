@php
	$police = session('user')->policies->where('code', 'DTS-DAS')->first(); //permiso para ver dashboard
	$dashboardPermission = (!empty($police) > 0  && $police->value > 0);
@endphp
@extends('layouts.default')
@section('content')
	<div class="container-fluid">
	    <div class="row bg-title">
	        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
	            <h4 class="page-title">Inicio</h4>
	        </div>
	        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
	            <ol class="breadcrumb">
	                <li><a href="/islim/">Dashboard</a></li>
	            </ol>
	        </div>
	    </div>
	</div>
	@if($dashboardPermission)
	<div class="container" id="container-dash">
		<!--altas hbb-->
		@include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Altas Internet Hogar',
                'type' => 'H',
                'sType' => 'P',
                'colorMet' => 'bg-theme'
            ]
        )

        <!--altas mbb-->
        @include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Altas Telefon&iacute;a',
                'type' => 'T',
                'sType' => 'P',
                'colorMet' => 'bg-theme'
            ]
        )

        <!--altas mifi-->
        @include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Altas MIFI Nacional',
                'type' => 'M',
                'sType' => 'P',
                'colorMet' => 'bg-theme'
            ]
        )

         <!--altas mifi huella altan-->
        @include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Altas MIFI Huella Altan',
                'type' => 'MH',
                'sType' => 'P',
                'colorMet' => 'bg-theme'
            ]
        )

        <!--altas mifi huella altan migrado-->
        @include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Altas Migradas MIFI Huella Altan',
                'type' => 'MH_M',
                'sType' => 'P',
                'colorMet' => 'bg-theme'
            ]
        )

        <!--altas Fibra-->
        @include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Altas Fibra',
                'type' => 'F',
                'sType' => 'P',
                'colorMet' => 'bg-theme'
            ]
        )

        <!--recargas HBB-->
        @include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Recargas internet hogar',
                'type' => 'H',
                'sType' => 'R',
                'colorMet' => 'bg-theme-dark'
            ]
        )

        <!--recargas MBB-->
        @include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Recargas Telefon&iacute;a',
                'type' => 'T',
                'sType' => 'R',
                'colorMet' => 'bg-theme-dark'
            ]
        )

        <!--recargas MIFI-->
        @include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Recargas MIFI Nacional',
                'type' => 'M',
                'sType' => 'R',
                'colorMet' => 'bg-theme-dark'
            ]
        )

        <!--recargas MIFI Huella Altan-->
        @include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Recargas MIFI Huella Altan',
                'type' => 'MH',
                'sType' => 'R',
                'colorMet' => 'bg-theme-dark'
            ]
        )

        <!--recargas Fibra-->
        @include(
            'pages.ajax.dashboard.graph',
            [
                'title' => 'Recargas Fibra',
                'type' => 'F',
                'sType' => 'R',
                'colorMet' => 'bg-theme-dark'
            ]
        )

	    <div class="row">
	        <div class="col-sm-12">
	            <div class="white-box">
                    <div class="row">
                    	<!--clientes HBB-->
				        @include(
				            'pages.ajax.dashboard.client',
				            [
				                'title' => 'Clientes Internet Hogar',
				                'type' => 'H',
				                'pt' => ''
				            ]
				        )

				        <!--clientes MBB-->
				        @include(
				            'pages.ajax.dashboard.client',
				            [
				                'title' => 'Clientes Telefon&iacute;a',
				                'type' => 'T',
				                'pt' => ''
				            ]
				        )

				        <!--clientes MIFI-->
				        @include(
				            'pages.ajax.dashboard.client',
				            [
				                'title' => 'Clientes MIFI Nacional',
				                'type' => 'M',
				                'pt' => 'p-t-20'
				            ]
				        )

                        <!--clientes MIFI Huella Altan-->
                        @include(
                            'pages.ajax.dashboard.client',
                            [
                                'title' => 'Clientes MIFI Huella Altan',
                                'type' => 'MH',
                                'pt' => 'p-t-20'
                            ]
                        )

                        <!--clientes Fibra-->
                        @include(
                            'pages.ajax.dashboard.client',
                            [
                                'title' => 'Clientes Fibra',
                                'type' => 'F',
                                'pt' => 'p-t-20'
                            ]
                        )

                        <div class="col-md-12 p-t-20">
                            <div class="card card-outline-primary text-center text-dark">
			                    <div class="card-block">
			                    	<div class="preloader-ajax" id="conc-loader">
								        <div class="cssload-speeding-wheel"></div>
								    </div>
			                    	<header>Concentradores</header>
                        			<hr>
                        			<table id="balance-concentrator" class="table table-striped">
                        				<thead>
							                <tr>
							                  <th>Concentrador</th>
							                  <th>Balance</th>
							                </tr>
							            </thead>
							            <tbody>
							            </tbody>
                        			</table>
			                    </div>
			                </div>
                        </div>
                    </div>
	            </div>
	        </div>
	    </div>
	</div>
	@endif
@stop
@section('script')
	@if($dashboardPermission)
		<script src="js/dashboard/main.js?v=3.3"></script>
	@endif
@endsection
