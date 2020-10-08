<?php

namespace App\Http\Controllers\Auth;

use Log;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Ldap
{
    public static function authenticate($username, $password)
    {
        if (! config('ldap.server', false)) {
            return false;
        }

        $username = trim(strtolower($username));
        if (empty($username) or empty($password)) {
            Log::error('Error binding to LDAP: username or password empty');

            return false;
        }
        $ldapconn = static::connectToServer(config('ldap.server'));
        if (! $ldapconn) {
            return false;
        }
        $ldapOrg = 'O='.config('ldap.ou');
        $user = static::findUser($username, $password, $ldapOrg, $ldapconn);
        if (! $user) {
            return false;
        }

        return $user;
    }

    private static function connectToServer($ldapServer)
    {
        if (! config('ldap.authentication')) {
            return 'Fake';
        }
        $ldapconn = ldap_connect($ldapServer);
        if (! $ldapconn) {
            Log::error('Could not connect to LDAP server');

            return false;
        }

        if (! ldap_start_tls($ldapconn)) {
            Log::error('Could not start TLS on ldap binding');

            return false;
        }

        return $ldapconn;
    }

    private static function findUser($username, $password, $ldapOrg, $ldapconn)
    {
        if (config('ldap.authentication')) {
            $ldapbind = @ldap_bind($ldapconn, config('ldap.username'), config('ldap.password'));
            $search = ldap_search($ldapconn, $ldapOrg, "uid={$username}");
            if (ldap_count_entries($ldapconn, $search) != 1) {
                ldap_unbind($ldapconn);
                Log::error("Could not find {$username} in LDAP");

                return false;
            }
            $info = ldap_get_entries($ldapconn, $search);
            $ldapbind = @ldap_bind($ldapconn, $info[0]['dn'], $password);
            if (! $ldapbind) {
                ldap_unbind($ldapconn);
                Log::error("Could not bind to LDAP as {$username} with supplied password");

                return false;
            }
            $search = ldap_search($ldapconn, $ldapOrg, "uid={$username}");
            $info = ldap_get_entries($ldapconn, $search);
            ldap_unbind($ldapconn);
        } else {
            $info = [];
            $info[0]['sn'][0] = 'Surname';
            $info[0]['givenname'][0] = 'Forenames';
            $info[0]['mail'][0] = 'test@example.com';
        }
        $result = [
            'username' => $username,
            'surname' => $info[0]['sn'][0],
            'forenames' => $info[0]['givenname'][0],
            'email' => $info[0]['mail'][0],
        ];

        return $result;
    }
}
