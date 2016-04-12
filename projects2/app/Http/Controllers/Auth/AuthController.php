<?php namespace App\Http\Controllers\Auth;

use DB;
use Auth;
use Mail;
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

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AuthController extends Controller
{
    public function __construct(Ldap $ldap)
    {
        $this->ldap = $ldap;
    }

    public function login(Request $request)
    {
        $username = trim(strtolower($request->input('username')));
        $password = $request->input('password');

        // handle local users first
        if (User::where('username', '=', $username)->whereNotNull('password')->count() == 1) {
            if (Auth::attempt(['username' => $username, 'password' => $password])) {
                Auth::user()->last_login = \Carbon\Carbon::now();
                Auth::user()->save();
                EventLog::log(Auth::user()->id, 'Logged in');
                return redirect()->intended('/');
            }
            return redirect()->refresh()->withErrors(['errors' => 'Username and/or password are incorrect.']);
        }

        // if the username is an email address, try and find them
        if (preg_match('/\@/', $username)) {
            if (Auth::attempt(['email' => $username, 'password' => $password])) {
                Auth::user()->last_login = \Carbon\Carbon::now();
                Auth::user()->save();
                EventLog::log(Auth::user()->id, 'Logged in');
                return redirect()->intended('/');
            }
            return redirect()->refresh()->withErrors(['errors' => 'Username and/or password are incorrect.']);
        }

        // try and LDAP auth
        $result = $this->ldap->authenticate($username, $password);
        if ($result) {
            $username = $result['username'];

            // check if the user already exists in the database
            //  ** should we check withTrashed and restore deleted account?
            $user = User::where('username', '=', $username)->first();
            if (is_null($user)) {
                $user = new User;
                $user->username = $username;
                $user->surname = $result['surname'];
                $user->forenames = $result['forenames'];
                $user->email = strtolower($result['email']);
                if (preg_match('/^[0-9]{6}[a-z]$/i', $username)) {
                    $user->is_student = true;
                }
                $user->save();
                EventLog::log($user->id, 'Created new @glasgow user');
            }

            //$user = User::where('username', Input::get('username'))->first();

            Auth::login($user);
            Auth::user()->last_login = \Carbon\Carbon::now();
            Auth::user()->save();
            EventLog::log(Auth::user()->id, 'Logged in');

            return redirect()->intended('/');
        }

        return redirect()->refresh()->withErrors(['errors' => 'Username and/or password are incorrect.']);
    }

    public function logout()
    {

        if (!Auth::guest()) {
            Auth::logout();

            return redirect('auth/login');
        }

        return redirect('auth/login');

    }

    public function generateResetLink(Request $request)
    {
        $email = strtolower(trim($request->email));
        $user = User::where('email', '=', $email)->first();
        if (!$user) {
            return redirect()->refresh()->withErrors(['errors' => 'Could not find that email address']);
        }
        $token = PasswordReset::create([
            'user_id' => $user->id,
            'token' => strtolower(str_random(32)),
        ]);
        Mail::send('emails.reset_password', ['token' => $token], function ($m) use ($user) {
            $m->from('donotreply@eng.gla.ac.uk', '[UoG] Student Projects');
            $m->to($user->email)->subject('[UoG] Student Projects - Password Reset');
        });
        EventLog::log($user->id, 'Generated a password reset email');
        return view('auth.password_reset_message', compact('token', 'user'));
    }

    public function password($token)
    {
        $resetToken = PasswordReset::where('token', '=', $token)->first();
        if (!$resetToken) {
            return redirect('/auth/login')->withErrors(['errors' => 'Invalid token']);
        }
        if ($resetToken->hasExpired()) {
            return redirect('/auth/login')->withErrors(['errors' => 'Token has expired']);
        }
        return view('auth.password', compact('token'));
    }

    public function resetPassword(Request $request, $token)
    {
        $resetToken = PasswordReset::where('token', '=', $token)->first();
        if (!$resetToken) {
            return redirect()->back()->withErrors(['errors' => 'Invalid token']);
        }
        if ($resetToken->hasExpired()) {
            return redirect()->back()->withErrors(['errors' => 'Token has expired']);
        }
        if ($request->password1 != $request->password2) {
            return redirect()->back()->withErrors(['errors' => 'Passwords did not match']);
        }
        if (strlen($request->password1) < 12) {
            return redirect()->back()->withErrors(['errors' => 'Password was too short']);
        }
        $user = $resetToken->user;
        if (!$user) {
            return redirect()->back()->withErrors(['errors' => 'Invalid user']);
        }
        $user->password = bcrypt($request->password1);
        $user->save();
        Auth::login($user);
        $resetToken->delete();
        EventLog::log($user->id, 'Reset their password');
        return redirect('/');
    }
}
