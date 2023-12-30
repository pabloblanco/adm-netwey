@extends('layouts.auth')
@section('content')
<section id="wrapper">
    <div class="back-img"></div>
    <div class="login-box login-sidebar">
        <div class="white-box">
            <form class="form-horizontal form-material" id="loginform" method="POST" action="login">
                {{ csrf_field() }}
                <input type="hidden" name="recaptcha" id="recaptcha">
                <a href="javascript:void(0)" class="text-center db">
                    <br/>
                </a>
                <div class="form-group m-t-40">
                    @if ($err)
                        <div class="alert alert-warning">{{ $err }}</div>
                    @endif
                    <div class="col-xs-12">
                        <input class="form-control" type="email" required="" name="email" placeholder="email">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <input class="form-control" type="password" required="" name="password" placeholder="Contraseña">
                    </div>
                </div>
                <!--<div class="form-group">
                    <div class="col-md-12">
                        <div class="checkbox checkbox-primary pull-left p-t-0">
                            <input id="checkbox-signup" type="checkbox">
                            <label for="checkbox-signup"> Recuerdame </label>
                        </div>
                    </div>
                </div>-->
                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Log In</button>
                    </div>
                </div>
            </form>
            <!--<div class="form-group m-b-0">
                <div class="col-sm-12 text-center">
                    <p>Aun no tienes una cuenta? <a href="register2.html" class="text-primary m-l-5"><b>Registrate</b></a></p>
                </div>
            </div>
            <a href="javascript:void(0)" id="to-recover" class="text-dark pull-right">
                <i class="fa fa-lock m-r-5"></i> Perdiste tu contraseña?
            </a> 
            <form class="form-horizontal" id="recoverform" action="index.html">
                <div class="form-group ">
                    <div class="col-xs-12">
                        <h3>Recuperar contraseña</h3>
                        <p class="text-muted">Ingresa tu correo y nos comunicaremos contigo con los pasos a seguir </p>
                    </div>
                </div>
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input class="form-control" type="text" required="" placeholder="Email">
                    </div>
                </div>
                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Restaurar</button>
                    </div>
                </div>
            </form>-->
        </div>
    </div>
</section>

<!--<div class="modal fade" id="changepass" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Modal title</h4>
      </div>
      <div class="modal-body">
        <p>One fine body&hellip;</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>-->

@if(!empty($cod_err) && $cod_err == 'PASS_EXP')
    <button hidden type="button" id="open_uppas" class="btn btn-info btn-lg" data-toggle="modal" data-target="#changepass"></button>

    <div class="modal fade" id="changepass" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Debe actualizar su contraseña</h4>
            </div>
            <div class="modal-body">
                <form id="updpass_form" name="updpass_form" action="" method="POST">
                    {{ csrf_field() }}
                    <input type="hidden" name="user" value="{{ $user_email }}">

                    <div class="form-group">
                        <label class="control-label">Contraseña actual</label>
                        <input type="password" id="ac_password" name="ac_password" class="form-control" placeholder="Ingrese Contraseña">
                    </div>

                    <div class="form-group">
                        <label class="control-label">Contraseña nueva</label>
                        <input type="password" id="n1_password" name="n1_password" class="form-control" placeholder="Ingrese Nueva Contraseña">
                    </div>

                    <div class="form-group">
                        <label class="control-label">Re. Contraseña</label>
                        <input type="password" id="n2_password" name='n2_password' class="form-control" placeholder="Repetir Contraseña">
                    </div>
                </form>
            </div>

            <div class="form-actions modal-footer">
                <button type="button" class="btn btn-success" id="saveUpdate">Guardar</button>
                <button type="button" id="chpass_close_btn" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
      </div>
    </div>
@endif

@stop

@section('script')
    <script src="https://www.google.com/recaptcha/api.js?render={{env('GOOGLE_CAPTCHA_FRONT')}}"></script>

    <script>
         grecaptcha.ready(function() {
             grecaptcha.execute('{{env('GOOGLE_CAPTCHA_FRONT')}}', {action: 'login'}).then(function(token){
                if(token){
                    document.getElementById('recaptcha').value = token;
                }
             });
         });

         @if(!empty($cod_err) && $cod_err == 'PASS_EXP')
            //$(function(){
                $(".preloader").fadeOut();

                $('#updpass_form').validate({
                    rules: {
                        ac_password: {
                            required: true
                        },
                        n1_password: {
                            required: true,
                            notEqualTo: "#ac_password"
                        },
                        n2_password: {
                            required: true,
                            equalTo: "#n1_password"
                        }
                    },
                    messages: {
                        ac_password: "Introduzca su contraseña actual",
                        n1_password: {
                            required: "Introduzca una contraseña",
                            notEqualTo: "La nueva contraseña no puede ser igual a la anterior."
                        },
                        n2_password: {
                            required: "Introduzca nuevamente la contraseña",
                            equalTo: "Las contraseñas no coinciden"
                        }
                    }
                });

                $('#saveUpdate').on('click', function(e){
                    if($('#updpass_form').valid()){
                        $('.preloader').fadeIn();
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: "{{ route('updateUserPass') }}",
                            method: 'POST',
                            data: $('#updpass_form').serialize(),
                            dataType: 'json',
                            success: function(res){
                                $('.preloader').fadeOut();
                                alert(res.msg)

                                if(res.success) location.reload()
                                
                            },
                            error: function(res){
                                $('.preloader').fadeOut();
                                alert('Ocurrio un error');
                            }
                        });
                    }
                });

                $('#open_uppas').click();
            //});
         @endif
    </script>
@stop