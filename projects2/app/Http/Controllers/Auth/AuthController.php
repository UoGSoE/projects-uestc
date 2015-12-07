<?php namespace App\Http\Controllers\Auth;

use DB;
use Auth;
use App\User;
use App\EventLog;
use App\Location;
use App\UserType;
use App\UserGroup;
use Carbon\Carbon;
use App\FundingType;
use App\PasswordReset;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\Ldap;
use App\Http\Controllers\Controller;

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
                EventLog::log(Auth::user()->id, 'Logged in');
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
                EventLog::log($user->id, 'Created new @glasgow user');
        	}

            //$user = User::where('username', Input::get('username'))->first();

            Auth::login( $user );
            Auth::user()->last_login = \Carbon\Carbon::now();
            Auth::user()->save();
            EventLog::log(Auth::user()->id, 'Logged in');

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

    public function password($token)
    {
        return view('auth.password', compact('token'));
    }

    public function resetPassword(Request $request, $token)
    {
        $resetToken = PasswordReset::where('token', '=', $token)->first();
        if (!$resetToken) {
            return redirect()->back()->with('errors', 'Invalid token');
        }
        if ($resetToken->hasExpired()) {
            return redirect()->back()->with('errors', 'Token has expired');
        }
        if ($request->password1 != $request->password2) {
            return redirect()->back()->with('errors', 'Passwords did not match');
        }
        if (strlen($request->password1) < 12) {
            return redirect()->back()->with('errors', 'Password was too short');
        }
        $user = $resetToken->user;
        if (!$user) {
            return redirect()->back()->with('errors', 'Invalid user');
        }
        $user->password = bcrypt($request->password1);
        $user->save();
        Auth::login($user);
        $resetToken->delete();
        return redirect('/');
    }
}
