<?php namespace App\Http\Controllers\Auth;

use App\EventLog;
use App\FundingType;
use App\Http\Controllers\Auth\Ldap;
use App\Http\Controllers\Controller;
use App\Location;
use App\PasswordReset;
use App\ProjectConfig;
use App\User;
use App\UserGroup;
use App\UserType;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Mail;

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

        if ($this->studentLoginsDisabled($username)) {
            return redirect()->refresh()->withErrors(['invalid' => 'Student logins are currently disabled']);
        }

        $user = $this->localLoginByUsername($username, $password);
        if ($user) {
            return $this->redirectUrl();
        }

        $user = $this->localLoginByEmail($username, $password);
        if ($user) {
            return $this->redirectUrl();
        }

        $user = $this->ldapLogin($username, $password);
        if ($user) {
            return $this->redirectUrl();
        }

        return redirect()->refresh()->withErrors(['invalid' => 'Username and/or password are incorrect.']);
    }

    public function redirectUrl()
    {
        return redirect()->intended('/');
    }

    public function localLoginByUsername($username, $password)
    {
        if (User::where('username', '=', $username)->whereNotNull('password')->count() == 1) {
            if (Auth::attempt(['username' => $username, 'password' => $password])) {
                Auth::user()->last_login = \Carbon\Carbon::now();
                Auth::user()->save();
                EventLog::log(Auth::user()->id, 'Logged in');
                return Auth::user();
            }
        }
        return null;
    }

    public function localLoginByEmail($email, $password)
    {
        if (preg_match('/\@/', $email)) {
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                Auth::user()->last_login = \Carbon\Carbon::now();
                Auth::user()->save();
                EventLog::log(Auth::user()->id, 'Logged in');
                return Auth::user();
            }
        }
        return null;
    }

    public function ldapLogin($username, $password)
    {
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
                if (preg_match('/^[0-9]{7}[a-z]$/i', $username)) {
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
            return Auth::user();
        }
        return null;
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
        return redirect()->to('/auth/login')->with('success_message', 'Password reset link has been sent.  Please check your email shortly.');
        //return view('auth.password_reset_message', compact('token', 'user'));
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
            return redirect()->back()->withErrors(['token_invalid' => 'Invalid token']);
        }
        if ($resetToken->hasExpired()) {
            return redirect()->back()->withErrors(['token_expired' => 'Token has expired']);
        }
        if ($request->password1 != $request->password2) {
            return redirect()->back()->withErrors(['password_mismatch' => 'Passwords did not match']);
        }
        if (strlen($request->password1) < 12) {
            return redirect()->back()->withErrors(['password_length' => 'Password was too short']);
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

    public function isAStudent($username)
    {
        if (preg_match("/^[0-9]{7}[a-zA-Z]/", $username)) {
            return true;
        }
        return false;
    }

    public function studentLoginsDisabled($username)
    {
        if (!$this->isAStudent($username) or ProjectConfig::getOption('logins_allowed', config("projects.logins_allowed")) == true) {
            return false;
        }
        return true;
    }
}
