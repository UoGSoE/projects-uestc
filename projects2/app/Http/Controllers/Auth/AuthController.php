<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\Ldap;
use Illuminate\Http\Request;
use App\User;
use Auth;
use App\FundingType;
use App\UserType;
use App\UserGroup;

class AuthController extends Controller {

    public function __construct(Ldap $ldap)
    {
        $this->ldap = $ldap;
    }

    public function login(Request $request)
    {
        $username = trim($request->input('username'));
        $password = $request->input('password');

        // handle local users first
        if (User::where('username','=',$username)->whereNotNull('password')->count() == 1) {
            if (Auth::attempt(['username' => $username, 'password' => $password])) {
                Auth::user()->last_login = \Carbon\Carbon::now();
                Auth::user()->save();
                return redirect()->intended('/');
            } else {
                return redirect()->refresh()->with('errors', 'Username and/or password are incorrect.');
            }
        }

        // try and LDAP auth if we are not in emergency "passwordless" mode. See app/Console/Commands/Passwordless.php
        if (!file_exists(storage_path() . '/.passwordless')) {
            $result = $this->ldap->authenticate( $username, $password );
        } else {
            // if we *are* in passwordless mode just grab their details from the existing User entry
            $result = User::whereUsername($username)->first()->toArray();
            if (!$result) {
                // new or invalid user - we should probably do something?
                $x = 1;
            }
        }
        if ( $result )
        {
        	$username = $result['username'];

            // check if the user already exists in the database
            //  ** should we check withTrashed and restore deleted account?
        	$user = User::where('username','=',$username)->first();
        	if (is_null($user)) {
        		$user = new User;
        		$user->username = $username;
                $user->surname = $result['surname'];
                $user->forenames = $result['forenames'];
                $user->email = $result['email'];
                if (preg_match('/^[0-9]{6}[a-z]$/i', $username)) {
                    $user->is_student = true;
                }
        		$user->save();
        	}

            //$user = User::where('username', Input::get('username'))->first();

            Auth::login( $user );
            Auth::user()->last_login = \Carbon\Carbon::now();
            Auth::user()->save();

            return redirect()->intended('/');
        }

        return redirect()->refresh()->with('errors', 'Username and/or password are incorrect.');
    }

    public function logout()
    {

        if ( ! Auth::guest())
        {
            Auth::logout();

            return redirect('auth/login');
                    //->with('message', 'You just logged out.');                  
        }

        return redirect('auth/login');   

    }

}
