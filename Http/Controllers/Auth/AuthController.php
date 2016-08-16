<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use Illuminate\Support\Facades\Session;

use Krucas\LaravelUserEmailVerification\AuthenticatesAndRegistersUsers as VerificationAuthenticatesAndRegistersUsers;


class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins, VerificationAuthenticatesAndRegistersUsers {
        AuthenticatesAndRegistersUsers::redirectPath insteadof VerificationAuthenticatesAndRegistersUsers;
        AuthenticatesAndRegistersUsers::getGuard insteadof VerificationAuthenticatesAndRegistersUsers;
        VerificationAuthenticatesAndRegistersUsers::register insteadof AuthenticatesAndRegistersUsers;
    }

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */

    protected $redirectTo = '/home';


    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);


    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function authenticated(Request $request)
    {
            if (!$request->session()->has('activeBandId')) {

                $activeBandName = DB::table('bands')->where('id', '>', 0)->where('user_id', '=', Auth::user()->id)->first();
                if ($activeBandName) {
                    Session::set('activeBandId', $activeBandName->id);
                    Session::set('activeBandName', $activeBandName->name);
                }

            }
        return redirect()->intended('/home');
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }


    public function logout(){
        Auth::logout();
        Session::flush();
        return Redirect::to('/');
    }


}
