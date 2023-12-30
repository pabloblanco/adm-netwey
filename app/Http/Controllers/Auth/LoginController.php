<?php
//hola
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\MainMenuItem;
use App\User;
use App\Role;
use App\Helpers\CommonHelpers;

class LoginController extends Controller {

    public function authenticate(Request $request){
        $data = 'secret='.env('GOOGLE_CAPTCHA_BACK').'&response='.urlencode($request->get('recaptcha')).'&remoteip='.urlencode($request->ip());

        $rg = CommonHelpers::veifyCaptchaGoogle($data);

        if(true/*$rg['success'] && $rg['data']->success*/){
            if(true /*$rg['data']->score >= 0.3*/){
                $login = User::doLogin($request->email, $request->password);
                if ($login === true){
                    $user = User::getUser($request->email);
                    if ($user->hasPermission ($request->email, 'AMU-APA')){
                        session([
                            'user' => $user
                        ]);
                        return redirect('/');
                    }
                    
                    return redirect('login')->with('err', 'Usted no posee permisos para acceder a este portal');
                }else{
                    if($login == -1)
                        return redirect('login')->with('err', 'Su contraseña expiró.')
                                                ->with('cod_err', 'PASS_EXP')
                                                ->with('user_email', $request->email);
                    else
                        return redirect('login')->with('err', 'Error de Credenciales.');
                }
            }
        }

        return redirect('login')->with('err', 'Error en captcha');
    }

    public function logout(Request $request){
        session()->flush();
        
        return redirect()->route('login');
    }

    public function islog() {
        if (session('user') !== null) {
            session([
                'user' => User::getUser(session('user')->email)
            ]);
            return view('pages.home', ['sidebarConfig' => MainMenuItem::getItems()]);
        } else {
            return redirect('login')->with('err', 'No hay una sesión iniciada');
        }
    }

    public function login (Request $request){
        if (session('user') !== null) {
            return redirect()->route('root');
        } else {
            $err = '';
            $cod_err = '';
            $user_email = '';
            if (session('err') !== null){
                $err = session('err');
                $cod_err = session('cod_err');
                $user_email = session('user_email');
            }
            return view('pages.login', ['err'=>$err, 'cod_err' => $cod_err, 'user_email' => $user_email]);
        }
    }
}