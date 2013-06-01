<?php

/*
 * Name:     lib-php-digest
 *
 * Credit:   This code makes use of an example PHP function found in the manual.
 *           The PHP manual text is copyright the PHP Documentation Group,
 *           and covered by Creative Commons Attribution 3.0 License.
 *           A copy is found at:
 *            http://www.creativecommons.org/licenses/by/3.0/legalcode
 *           
 *           This code also makes use of the Portable PHP password hashing
 *           framework from OpenWall.  It is public domain.
 *
 * Author:   Andrew Kester
 *
 * Version:  xxxxx
 *           Released: xxxxx
 *
 * License:  This library is copyright (C) 2012 Andrew Kester.
 * 
 *           This program is free software: you can redistribute it and/or
 *            modify it under the terms of the GNU Lesser General Public 
 *            License as published by the Free Software Foundation, either 
 *            version 3 of the License, or (at your option) any later version.
 *
 *           This program is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public
 *             License along with this program.
 *             If not, see <http://www.gnu.org/licenses/>.
 *
 * Changes:   Official releases will be posted to the SourceForge project
 *           (sourceforge.net/projects/libphpdigest).
 *
 * Docs:     Documentation for this library is on-line under our SourceForge
 *           project page.  If you cannot find or need more detailed help, 
 *           submit a ticket in sourceforge.  
 *
 * This software is currently in a BETA phase, use should be restricted to
 * testing.
 */

/**
 * A class for handling standards based Basic and Digest authentication with PHP.
 */
class phpAuth {

    /**
     * Turns on debugging during execution.
     * @var boolean
     */
    private $debug = TRUE;

    /**
     * Controls if the script will quit on an error or try to pass it to an exterior error handler.
     * @var boolean
     */
    public $quit_on_error = TRUE;

    /**
     * An external error handler.
     * @var string
     */
    public $error_handler = '';


    /*     * **************************************************************************
     *                      REDIRECTOIN/FAILURE BEHAVIOR
     * ************************************************************************** */

    /**
     * Controls if the script will redirect users when the authentication process is canceled.
     * @var boolean
     */
    public $redirect_on_failure = TRUE;

    /**
     * The URL to redirect to on a failure.
     * @var string
     */
    public $redirect_on_failure_url = '/arc/error.php';

    /**
     * Text that is echoed if the login fails, but fails to redirect.
     * @var string
     */
    public $fail_text = 'You Must Login.';

    /**
     * Controls if the user is redirected after logging out.
     * @var boolean
     */
    public $redirect_on_logout = TRUE;

    /**
     * The URL to redirect to on a logout.
     * @var string
     */
    public $redirect_on_logout_url = '/arc';

    /**
     * Text to display if the user logs out but fails to redirect.
     * @var string
     */
    public $logout_text = 'You have been logged out. <a href="index.php">Return</a>';

    /**
     * The wait time in seconds after a failed authentication.
     * @var integer
     */
    public $wait_time = 1;

    /**
     * If the user fails to login because they failed to pass an IP address, they can be redirected.
     * @var boolean
     */
    public $redirect_on_ip_fail = TRUE;

    /**
     * The URL to redirect to on a failure due to an IP address.
     * @var string
     */
    public $redirect_on_ip_fail_url = '/arc/iperror.php';

    /**
     * If the user does not redirect on an IP failure, this message is displayed.
     * @var string
     */
    public $ip_fail_text = 'For security reasons,
                your browser or proxy must provide an IP address.';

    /*     * **************************************************************************
     *                      AUTHENTICATION METHODS
     * ************************************************************************** */

    /**
     * The method to use to execute the authentication.  Valid options are (Case insensitive):
     *     basic - Basic user authentication
     *     digest - Digest based user autentication
     *     java - Future (Reserved)
     * @var string
     */
    private $auth_http_method = 'digest';

    /**
     * To check for inactivity, we can either use cookies (PHP session vars) or
     * track user by the nonce.  Checking by cookies is more reliable for some
     * browsers, but requires a cookie to be stored on the client system. (could
     * be problematic for sites trying to avoid stricter privacy regulations.)
     * Set COOKIE to check for cookie, NONCE to check by nonce, BOTH to check both.
     * @var string
     */
    private $session_check_method = 'BOTH';

    /**
     * Exipire time in second for nonces
     * @var integer
     */
    private $nonce_expire = 600;

    /**
     * Expire time in seconds for cookies.
     * @var integer
     */
    private $cookie_expire = 600;

    /*     * **************************************************************************
     *                      PASSWORD HASH SETTINGS
     * ************************************************************************** */

    /**
     * For added security, we can hash user passwords.  It is highly reccomended 
     * to leave this on unless an external program requires a plaintext password
     * (Which is rare)  Refer to the documentation for more information.
     * 
     * If we are using Digest authentication, this setting controls if the passwords
     * are encrypted before they are stored in the database.  We cannot hash the
     * values since the A1 value is required in plaintext to test the validy of the
     * response.  However, since the A1 value is static, storing it is just as
     * insecure as storing a plaintext password.  (Yes, it's not plaintext, but
     * it's easy to figure out authetication model and reverse engineer a valid
     * response from that value)
     * @var boolean
     */
    private $hash_passwords = TRUE;

    /**
     * The framework used can use various levels of secuirty.  8 is a good choice.
     * Higher numbers add signaficantly to load/login times.  (Has no effect on
     * digest encryptions) (Default: 8)
     * @var integer
     */
    private $hash_level = 8;

    /*     * **************************************************************************
     *                      CUSTOM FUNCITON SETTINGS
     * ***************************************************************************
     * 
     * Edit these if you set to use custom functions above.
     * Refer to the documentation for information on how to set up these functions.
     */

    /**
     * The function that retrives a user's hash from the database.
     * @var string 
     */
    private $get_hash_function = '';

    /**
     * The function that connects to the configured databases.
     * @var string 
     */
    private $db_connect_function = '';

    /**
     * Function that closes open db connections.
     * @var string
     */
    private $db_close_function = '';

    /**
     * Function that creates a new nonce
     * @var string
     */
    private $nonce_create_function = '';

    /**
     * Function that checks a nonce
     * @var string
     */
    private $nonce_check_function = '';

    /**
     * Function that expires a nonce.
     * @var string
     */
    private $nonce_expire_function = '';

    /**
     * The function to log authentications
     * @var string
     */
    private $log_auth_function = '';

    /**
     * The function to check if a user is currently blacklisted.
     * @var string
     */
    private $log_check_function = '';

    /*     * **************************************************************************
     *                      ADVANCED CONFIGURATION
     * 
     * 
     * These values control major detials of the software and should be edited 
     * with caution.
     * ************************************************************************** */

    /**
     * Realm is the name of the secure area that is passed to the users. 
     * 
     * IMPORTANT! - Changing the realm in a Digest based application will break
     * all of the user passwords!  It should not be changed once valid passwords
     * are stored!
     * @var string
     */
    private $realm = 'ARC Render Cluster';

    /**
     * Secret key used to encrypt/decrypt the stored hash values.
     *
     * IMPORTANT! - CHANGING THE SECRET WILL BREAK ALL CURRENT PASSWORDS!
     * IT'S RECCOMENDED TO PICK A STRONG PASSWORD AND TO NEVER CHANGE IT!
     * (256+ Random chars from /dev/urandom is a good choice here)
     * @var string
     */
    private $secret = '	bkbys5ZtCQXfqD+fZS44De+5WZUf7ZweZwE5QJvelpgSCHgjEk5SjoU7yqUCYeHJ
						0t1ehdXP7EosIqXyTbhov7NDzXnYs/77VTMHOa9RMW6Kz8gzgyBU3gUrt6wdl7Nu
						sS20kaR99u+vp4Mt98cEVcFLAVUuL+5l4IZmnFGXTsFSop/6DfI7D+e7BVVIvjCq
						jvXgCRstxWbzmtlHRfZzHjjjvXJnrnZGoZHvfGPEWroEGvc5+PdNbcw4L/Ao1HPC
						03IiwXNVe67ztwSZUKCWeQ5zbil3s/RRYqDBlBCcvnbDYQvjnj3EYwfgNgK0O0tb
						l10U9J26814W38L+K6deTO9yF6IHFsvRc+Edrhy4VklI1Vq97fQJ0cDIb3ODMQYO
						4uUUAzwH/7NaGlFuXyj2wzts6siKqSeENei+CKAGRX1x/b5yRs7zdIaC9+M+zk38
						OEu8dtPPXif9hxEnJ8tXhUNHTCeSe0s518ngAQS1te+SdAlhvEseuAOLB99+Ha1l
						D7J+QKMBPVSG7w1TOUPm3UNNLyFRpsBm3O74BBNJ1+sOiLz8ra7gl9XjFi2K1yaD
						Kjs2OkFLclHumsapqRkTknvZVH6y6tn4D/Tojg+a+muwkaRZohmwg5usJ3Dcj7Sd
						36CBadZom4/S1uviFhfxS4mj/HK2QyjpxXNWtwsV9NQI6v1R1fbEi3SVNR/PMs/O
						5DxU3SbU7gxDzomIGkRlM1VCzRoa3WuDTb+2k+TVE5ZoJcFGZtkJ6YbEtEgMiMNK
						/reR73+G7bQbBodFZo5ZVq3lCPG3Tc57TH64u56bD/lSBIoVW2PgGXDw0cUK1p9B
						Z+ldB9TIV+oXr8b0Xes35p0OCdKTN5Qqg+kDU6ZsYMOaLOcE9ue12Ao+pu+qntYU
						Wdrcd0nyUxeLV4hIXSE8XSkTZuNfiS/Lh6fXQEno97NYF+PxMynJTqveTaAD7Kj1
						0rnWtc/WFO37CCz5Cy+X52zay/9dwQVOe8smsF3nGv1TcFBJImsR57jJY2MptS56
						7KRR9NBSgzlZ7mWEygrFn4hyLD04sDcmMX1DXmqPtA7Z9NAXs0XIQ/Kv/zOgtvhZ
						tlhhf1KBeKPERxGabtIiBcq/sNRIkZUxbUHXBRt3Oo2k6ipLuAAKaNgJNIdXZMxd
						ezTIRMRsBL4t6B36UDKn6KGgdJ1RnaPTHf5XGc/Mdgu2Fwddewo3x4u/HRD3uol+
						zm3jzY7ng8rrIQspww19bC/f9Y4YoOUZpWvxMGvuq4CwY2jV31fhYBGPPIAdBh/F
						edbga7G2y8VPiHBIV7BdtcU2qYT8uYSLC9SlY5xfVPuI38dfEIC9P5kQQkqgm9J3
						hw6iyFRQy8gcunENzrcwJtC25ZU=';

    /**
     * Verifing nonce values is more secure, but it can add to the load on
     * the database.
     * @var boolean
     */
    private $verify_nonce = TRUE;

    /**
     * Should we log auths to prevent brute force attacks 
     * @var boolean
     */
    private $log_auth = TRUE;

    /**
     * If we log auths, should we requre that users pass an IP address? 
     * (More secure, but if legitamate clients frequently don't/can't pass IP's 
     * it would lock them out.)
     * @var boolean
     */
    private $require_ip = TRUE;

    /*     * ************************************************************************* *
     *                      END CONFIGURATION!
     *
     *                      GENERALLY, DON'T EDIT BELOW THIS LINE! 
     * ************************************************************************** */

    /**
     * The database connection to the authentication database.
     * @var mixed 
     */
    private $authdb = '';

    /**
     * The database connection to the backend database.
     * @var mixed
     */
    private $backdb = '';

    /**
     *  A class for handling standards based Basic and Digest authentication with PHP.authentication
     */
    function __construct() {
        $this->debugPrint('lib-php-digest: Library loaded, beginning execution.');
        $this->debugPrint('If you are reading this, debugging is turned on.  
            It can be disabled by editing the config section of the library.
            It should NOT be left on in a production enviroment.');

        /* Check if the error handler exitsts */
        if (!$this->quit_on_error && !function_exists($this->error_handler))
            die("Authentication Error: No error handler!" . PHP_EOL);

        $this->auth_http_method = strtoupper($this->auth_http_method);

        /* Check for an empty key.  (A bad thing) */
        if ($this->auth_http_method == 'DIGEST' && $this->hash_passwords && empty($this->secret))
            $this->debugPrint('********WARNING******** YOU HAVE SELECTED TO ENCRYPT PASSWORDS, BUT HAVE
              NOT PROVIDED A SECRET KEY.  YOU SHOULD PROVIDE A KEY OTHERWISE THE
              ENCRYPTION IS USELESS!');

        /* Check if functions exists */
        $this->debugPrint('Checking function status...');
        if (!method_exists($this, $this->get_hash_function))
            $this->callError("Error: Could not find 'get_hash' function!!");
        if (!method_exists($this, $this->db_connect_function))
            $this->callError("Error: Could not find 'db_connect' function!!");
        if (!method_exists($this, $this->db_close_function))
            $this->callError("Error: Could not find 'db_close' function!!");
        if (!method_exists($this, $this->nonce_check_function))
            $this->callError("Error: Could not find 'nonce_check' function!!");
        if (!method_exists($this, $this->nonce_create_function))
            $this->callError("Error: Could not find 'nonce_create' function!!");
        if (!method_exists($this, $this->nonce_expire_function))
            $this->callError("Error: Could not find 'nonce_expire' function!!");
        if (!method_exists($this, $this->log_auth_function))
            $this->callError("Error: Could not find 'log_auth' function!!");
        if (!method_exists($this, $this->log_check_function))
            $this->callError("Error: Could not find 'log_check' function!!");

        $this->debugPrint('No Errors.  Waiting for calls.');
    }

    /**
     * Internal error handler, will either quit or pass the error to an external logger.
     * @param string $message The error message to print/log
     */
    protected function callError($message) {
        if ($this->quit_on_error) {
            header('HTTP/1.0 500 Internal Server Error');
            echo("<h3>A system error occured, Please try again later.</h3>");
            if ($this->debug)
                echo ("<br>Crash Information:<br><p style=\"font-family: monospace;\">" . $message . "</p>");
            exit();
        } else {
            $this->error_handler($message);
        }
    }

    /**
     * Prints debugging information if debugging is turned on.
     * @param string $message The data to print.
     */
    protected function debugPrint($message) {
        if ($this->debug) {
            echo "<p style=\"font-family: monospace;\">[ lib-php-digest DEBUG ]:   {$message}</p>";
        }
    }

    /**
     * Sets public options via an array.
     * @param array $opts The array of options to set.
     */
    public function setOptions($opts) {
        /* Vars that can be set via this function */
        $allowed = array(
            "quit_on_error",
            "error_handler",
            "redirect_on_failure",
            "redirect_on_failure_url",
            "fail_text",
            "redirect_on_logout",
            "redirect_on_logout_url",
            "logout_text",
            "wait_time",
            "redirect_on_ip_fail",
            "redirect_on_ip_fail_url",
            "ip_fail_text"
        );

        foreach ($opts as $k => $o) {
            if (in_array($k, $allowed))
                $this->$k = $o;
        }
    }
    
    /**
     * Sets private options via an array.
     * @param array $opts The array of options to set.
     */
    protected function setPrivates($opts) {
        foreach ($opts as $k => $o) {
            if (isset($this->$k))
                $this->$k = $o;
        }
    }

    /**
     * Encrypts the string using mcrypt and the seceret key configured.
     * @param string $data The data to encrypt
     * @return string The encrypted data.
     */
    protected function encrypt($data) {
        if ($this->hash_passwords) {
            return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->secret), $data, MCRYPT_MODE_CBC, md5(md5($this->secret))));
        } else {
            return $data;
        }
    }

    /**
     * Decrypts the string using mcypt and the configured key.
     * @param string $data The data to decrypt
     * @return string The decrpyted data.
     */
    protected function decrypt($data) {
        if ($this->hash_passwords) {
            return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->secret), base64_decode($data), MCRYPT_MODE_CBC, md5(md5($this->secret))), "\0");
        } else {
            return $data;
        }
    }

    /**
     * Sends information to the browser to prompt the user for a Digest authentication
     */
    private function promptDigest() {
        $this->debugPrint('Prompting user...');

        $nonce = base64_encode(openssl_random_pseudo_bytes(32));
        if ($this->verify_nonce)
            $this->{$this->nonce_create_function}($nonce);

        header("WWW-Authenticate: Digest realm=\"{$this->realm}\",qop=\"auth\",nonce=\"$nonce\",opaque=\"" . md5($this->realm) . "\"");
        header('HTTP/1.0 401 Unauthorized');

        /* This is sent if the user cancels */
        if ($this->redirect_on_failure) {
            echo <<<EOF
      <html>
	<meta http-equiv="refresh" content="0; url={$this->redirect_on_failure_url}?error=1" />
	<body><h2>{$this->fail_text}</h2></body>
      </html>
EOF;
        } else {
            echo "<html><body><h2>{$this->fail_text}</h2></body></html>";
        }
        exit();
    }

    /**
     * Sends information to the browser to prompt the user for a basic authentication
     */
    private function promptBasic() {
        $this->debugPrint('Prompting user (BASIC)...');


        header("WWW-Authenticate: Basic realm=\"{$this->realm}\"");
        header('HTTP/1.0 401 Unauthorized');

        /* This is sent if the user cancels */
        if ($this->redirect_on_failure) {
            echo <<<EOF
      <html>
        <meta http-equiv="refresh" content="0; url={$this->redirect_on_failure_url}?error=1" />
        <body><h2>{$this->fail_text}</h2></body>
      </html>
EOF;
        } else {
            echo "<html><body><h2>{$this->fail_text}</h2></body></html>";
        }
        exit();
    }

    /**
     * Decides if and how to prompt the user.
     * @param boolean $kill setting this to TRUE reprompts the user.
     * @return boolean returns FALSE if $kill is false
     */
    private function prompt($kill = TRUE) {
        if ($kill) {
            if ($this->auth_http_method == 'BASIC')
                $this->promptBasic();
            else
                $this->promptDigest();
        } else {
            return false;
        }
    }

    /**
     * Authenticates a user.
     * @param boolean $kill Should the page load be killed if the user is not authenticated.  (Setting FALSE is useful
     * for pages that have both authenticated and non authenticated modes)
     * @return mixed Returns the username if the auth was successful, FALSE if the auth failed and this function did not
     * stop it (because $kill is asserted FALSE)
     */
    public function auth($kill = TRUE) {
        $this->debugPrint('Begining DIGEST auth process...');

        /* Connect to the database */
        $this->{$this->db_connect_function}();

        if ($this->session_check_method == 'COOKIE' || $this->session_check_method == 'BOTH')
            session_start();

        /* Attempt to get the IP of the user */
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (empty($ip) && $this->require_ip && $this->log_auth) {
            $this->debugPrint('No IP Passed, failing.');
            $this->ipFail();
        }
        $this->debugPrint("User IP is $ip");

        if ($this->auth_http_method == 'DIGEST' && empty($_SERVER['PHP_AUTH_DIGEST'])) {
            /* User has not authenticated. */
            $this->debugPrint("User has not authenticated.");
            if (!$this->prompt($kill))
                return false;
        } elseif ($this->auth_http_method == 'BASIC' && empty($_SERVER['PHP_AUTH_USER'])) {
            /* User has not authenticated. */
            $this->debugPrint("User has not authenticated.");
            if (!$this->prompt($kill))
                return false;
        } else {
            /* Check and see if we are blacklisted */
            if ($this->log_auth && !($this->{$this->log_check_function}($ip))) {
                sleep($this->wait_time);
                /* Blacklisted temporarily */
                $this->debugPrint("User has been blacklisted.");
                if (!$this->prompt($kill))
                    return false;
            }

            /* Break apart the Digest response */
            if ($this->auth_http_method == 'DIGEST' && !($digest_data = $this->parseDigest($_SERVER['PHP_AUTH_DIGEST']))) {
                sleep($this->wait_time);
                /* Bad Digest data */
                $this->debugPrint("User passed invalid digest data.");
                if ($kill) {
                    $this->promptDigest();
                    if ($this->log_auth)
                        $this->{$this->log_auth_function}($ip, 1, '');
                }
                else
                    return false;
            }

            /* Store the username in a common variable */
            $username = NULL;
            if ($this->auth_http_method == 'BASIC')
                $username = $_SERVER['PHP_AUTH_USER'];
            else
                $username = $digest_data['username'];

            /* Get the hash */
            if (!($stored_hash = $this->{$this->get_hash_function}($username))) {
                sleep($this->wait_time);
                /* Invalid username */
                $this->debugPrint("User passed invalid name.");
                if (!$this->prompt($kill))
                    return false;
            }

            /* Validate the data */
            if ($this->auth_http_method == 'BASIC') {
                if (!$this->validateHashBasic($_SERVER['PHP_AUTH_PW'], $stored_hash)) {
                    if ($this->log_auth)
                        $this->{$this->log_auth_function}($ip, 1, '');
                    sleep($this->wait_time);
                    /* Bad response (bad password) */
                    $this->debugPrint("User passed bad combination.");
                    if (!$this->prompt($kill))
                        return false;
                }
            } else {
                /* Build the test value and compare it to the current value */
                if (!$this->validateHashDigest($digest_data['username'], $stored_hash)) {
                    if ($this->log_auth)
                        $this->{$this->log_auth_function}($ip, 1, '');
                    sleep($this->wait_time);
                    /* Bad response (bad password) */
                    $this->debugPrint("User passed bad combination.");
                    if (!$this->prompt($kill))
                        return false;
                }

                /* Check the nonce */
                $nc = hexdec($digest_data['nc']);
                if ($this->verify_nonce && (!$this->{$this->nonce_check_function}($digest_data['nonce'], $nc))) {

                    sleep($this->wait_time);

                    /* BUGFIX - Unset lastseen here as well */
                    if ($this->session_check_method == 'COOKIE' || $this->session_check_method == 'BOTH')
                        unset($_SESSION['lastseen']);

                    /* Bad nonce */
                    $this->debugPrint("Nonce check failed.");
                    if (!$this->prompt($kill))
                        return false;
                }
            }

            /* Check if we are expired */
            $this->debugPrint("Checking cookie expire...");
            if (( $this->session_check_method == 'COOKIE'
                    || $this->session_check_method == 'BOTH' )
                    && isset($_SESSION['lastseen'])
                    && time() > ( $_SESSION['lastseen'] + $this->cookie_expire )) {
                sleep($this->wait_time);
                unset($_SESSION['lastseen']);
                /* Session Expired */
                $this->debugPrint("Cookie expired.");
                if (!$this->prompt($kill))
                    return false;
            }
        }
        /* Auth passed, let the page load */
        if ($this->log_auth)
            $this->{$this->log_auth_function}($ip, 0, $digest_data['username']);

        if ($this->session_check_method == 'COOKIE' || $this->session_check_method == 'BOTH')
            $_SESSION['lastseen'] = time();

        /* Close the database connection */
        $this->{$this->db_close_function};

        $this->debugPrint("Auth sucessful, loading the page.");

        return $username;
    }

    /**
     * Validates the passed digest data of a user
     * @param string $username The username of the user
     * @param string $stored_hash The valid hash of the user
     * @return boolean Returns TRUE if the data is valid, FALSE if it is not.
     */
    private function validateHashDigest($username, $stored_hash) {
        $this->debugPrint("Validating user's hash...");

        /* Check if we've passed both the values we need */
        if (empty($username))
            callError("Internal Error: Username empty.");
        if (empty($stored_hash))
            callError("Internal Error: Hash empty.");

        /* Break apart the Digest response */
        if (!($digest_data = $this->parseDigest($_SERVER['PHP_AUTH_DIGEST']))) {
            $this->debugPrint("User passed invalid data.");
            return false;
        }

        $a1 = $this->decrypt($stored_hash);

        /* Generate a valid response */
        $a2 = md5($_SERVER['REQUEST_METHOD'] . ":" . $digest_data['uri']);
        $valid = md5($a1 . ":" . $digest_data['nonce'] . ":" . $digest_data['nc'] . ":" . $digest_data['cnonce'] . ":" . $digest_data['qop'] . ":" . $a2);

        return $valid == $digest_data['response'];
    }

    /**
     * Parses the Digest response
     * @param string $txt The raw digest data
     * @return array The digest data in an assoc. array
     */
    private function parseDigest($txt) {
        /* Protect against missing data */
        $needed_parts = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }

    /**
     * Validates a Basic username/password combo
     * @param string $password  The user supplied password.
     * @param string $stored The known good password
     * @return boolean Returns TRUE for a match, FALSE otherwise.
     */
    private function validateHashBasic($password, $stored) {
        $this->debugPrint("Validating user's hash...");

        /* Check if we've passed both the values we need */
        if (empty($password))
            callError("Internal Error: Password empty.");
        if (empty($stored))
            callError("Internal Error: Hash empty.");

        if ($this->hash_passwords) {
            $h = new PasswordHash($this->hash_level, FALSE);
            return $h->CheckPassword($password, $stored);
        } else {
            return $password == $stored;
        }
    }

    /**
     * Logs out a user using the defined HTTP method.
     */
    public function logoutUser() {
        $this->debugPrint("Logging out user...");

        if ($this->auth_http_method == 'DIGEST')
            $data = $this->parseDigest($_SERVER['PHP_AUTH_DIGEST']);

        if ($this->session_check_method == 'BOTH' || $this->session_check_method == 'NONCE' && !empty($data))
            $this->{$this->nonce_expire_function}($data['nonce']);
        if ($this->session_check_method == 'BOTH' || $this->session_check_method == 'COOKIE') {
            session_start();
            $_SESSION['lastseen'] = time() - ($this->cookie_expire + 3600 );
        }
        if ($this->redirect_on_logout) {
            echo <<<EOF
      <html>
        <meta http-equiv="refresh" content="0; url={$this->redirect_on_logout_url}?error=2" />
        <body><h2>{$this->logout_text}</h2></body>
      </html>
EOF;
        } else {
            echo "<html><body><h2>{$this->logout_text}</h2></body></html>";
        }
        exit();
    }

    /*     * **************************************************************************
     *                      SUPPORT FUNCTIONS
     * ************************************************************************** */

    /**
     * Generates a hash to be stored in the database.
     * @param string $user The username for the password
     * @param string $password The password to store
     * @return mixed The hash to store, FALSE on failure
     */
    public function genHash($user, $password) {

        switch ($this->auth_http_method) {
            case 'DIGEST':
                /* Generate the A1 Value */
                $a1 = md5($user . ":" . $this->realm . ":" . $password);
                if ($this->hash_passwords) {
                    $encrypted = $this->encrypt($a1);
                    return $encrypted;
                } else {
                    return $a1;
                }
                break;
            case 'BASIC':
                /* Only hash the password */
                if ($this->hash_passwords) {
                    $h = new PasswordHash($this->hash_level, FALSE);
                    $final = $h->HashPassword($password);
                } else {
                    $final = $password;
                }

                return $final;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Checks a password for validity.  This should not be used to login users, only to verify the password for times like
     * changing the password.
     * @param string $user The username to check
     * @param string $password The password to check
     * @return boolean Returns TRUE on a match, FALSE otherwise
     */
    public function checkPassword($user, $password) {

        if (empty($user))
            callError("Error: Username empty");
        if (empty($password))
            callError("Error: Password empty");

        if (!($stored = $this->{$this->get_hash_function}($user)))
            return false;

        $h = new PasswordHash($this->hash_level, FALSE);
        switch ($this->auth_http_method) {
            case 'DIGEST':
                /* Re-generate the A1 value */
                $a1 = md5($user . ":" . $this->realm . ":" . $password);
                $stored = $this->decrypt($stored);

                return $a1 == $stored;
                break;
            case 'BASIC':
                if ($this->hash_passwords)
                    return $h->CheckPassword($password, $stored);
                else
                    return $stored == $password;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Echos the login failed due to an absent IP and redirects if configured.
     */
    private function ipFail() {

        if (!$this->redirect_on_ip_fail) {
            echo <<<EOT
    <h2>{$this->ip_fail_text}</h2>
EOT;
            exit();
        } else {
            echo <<<EOF
      <html>
        <meta http-equiv="refresh" content="0; url={$this->redirect_on_ip_fail_url}" />
        <body><h2>{$this->ip_fail_text}</h2></body>
      </html>
EOF;
        }
    }

    /* This is a dummy function used to disable features */

    protected function nullFunc() {
        return true;
    }

}

/* * **************************************************************************
 *                      PASSWORD HASH FRAMEWORK
 * ************************************************************************** */
#
# Portable PHP password hashing framework.
#
# Version 0.3 / genuine.
#
# Written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
# the public domain.  Revised in subsequent years, still public domain.
#
# There's absolutely no warranty.
#
# The homepage URL for this framework is:
#
#       http://www.openwall.com/phpass/
#
# Please be sure to update the Version line if you edit this file in any way.
# It is suggested that you leave the main version number intact, but indicate
# your project name (after the slash) and add your own revision information.
#
# Please do not change the "private" password hashing method implemented in
# here, thereby making your hashes incompatible.  However, if you must, please
# change the hash type identifier (the "$P$") to something different.
#
# Obviously, since this code is in the public domain, the above are not
# requirements (there can be none), but merely suggestions.
#

class PasswordHash {

    var $itoa64;
    var $iteration_count_log2;
    var $portable_hashes;
    var $random_state;

    function PasswordHash($iteration_count_log2, $portable_hashes) {
        $this->itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
            $iteration_count_log2 = 8;
        $this->iteration_count_log2 = $iteration_count_log2;

        $this->portable_hashes = $portable_hashes;

        $this->random_state = microtime();
        if (function_exists('getmypid'))
            $this->random_state .= getmypid();
    }

    function get_random_bytes($count) {
        $output = '';
        if (is_readable('/dev/urandom') &&
                ($fh = @fopen('/dev/urandom', 'rb'))) {
            $output = fread($fh, $count);
            fclose($fh);
        }

        if (strlen($output) < $count) {
            $output = '';
            for ($i = 0; $i < $count; $i += 16) {
                $this->random_state =
                        md5(microtime() . $this->random_state);
                $output .=
                        pack('H*', md5($this->random_state));
            }
            $output = substr($output, 0, $count);
        }

        return $output;
    }

    function encode64($input, $count) {
        $output = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $output .= $this->itoa64[$value & 0x3f];
            if ($i < $count)
                $value |= ord($input[$i]) << 8;
            $output .= $this->itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count)
                break;
            if ($i < $count)
                $value |= ord($input[$i]) << 16;
            $output .= $this->itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count)
                break;
            $output .= $this->itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }

    function gensalt_private($input) {
        $output = '$P$';
        $output .= $this->itoa64[min($this->iteration_count_log2 +
                        ((PHP_VERSION >= '5') ? 5 : 3), 30)];
        $output .= $this->encode64($input, 6);

        return $output;
    }

    function crypt_private($password, $setting) {
        $output = '*0';
        if (substr($setting, 0, 2) == $output)
            $output = '*1';

        $id = substr($setting, 0, 3);
        # We use "$P$", phpBB3 uses "$H$" for the same thing
        if ($id != '$P$' && $id != '$H$')
            return $output;

        $count_log2 = strpos($this->itoa64, $setting[3]);
        if ($count_log2 < 7 || $count_log2 > 30)
            return $output;

        $count = 1 << $count_log2;

        $salt = substr($setting, 4, 8);
        if (strlen($salt) != 8)
            return $output;

        # We're kind of forced to use MD5 here since it's the only
        # cryptographic primitive available in all versions of PHP
        # currently in use.  To implement our own low-level crypto
        # in PHP would result in much worse performance and
        # consequently in lower iteration counts and hashes that are
        # quicker to crack (by non-PHP code).
        if (PHP_VERSION >= '5') {
            $hash = md5($salt . $password, TRUE);
            do {
                $hash = md5($hash . $password, TRUE);
            } while (--$count);
        } else {
            $hash = pack('H*', md5($salt . $password));
            do {
                $hash = pack('H*', md5($hash . $password));
            } while (--$count);
        }

        $output = substr($setting, 0, 12);
        $output .= $this->encode64($hash, 16);

        return $output;
    }

    function gensalt_extended($input) {
        $count_log2 = min($this->iteration_count_log2 + 8, 24);
        # This should be odd to not reveal weak DES keys, and the
        # maximum valid value is (2**24 - 1) which is odd anyway.
        $count = (1 << $count_log2) - 1;

        $output = '_';
        $output .= $this->itoa64[$count & 0x3f];
        $output .= $this->itoa64[($count >> 6) & 0x3f];
        $output .= $this->itoa64[($count >> 12) & 0x3f];
        $output .= $this->itoa64[($count >> 18) & 0x3f];

        $output .= $this->encode64($input, 3);

        return $output;
    }

    function gensalt_blowfish($input) {
        # This one needs to use a different order of characters and a
        # different encoding scheme from the one in encode64() above.
        # We care because the last character in our encoded string will
        # only represent 2 bits.  While two known implementations of
        # bcrypt will happily accept and correct a salt string which
        # has the 4 unused bits set to non-zero, we do not want to take
        # chances and we also do not want to waste an additional byte
        # of entropy.
        $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $output = '$2a$';
        $output .= chr(ord('0') + $this->iteration_count_log2 / 10);
        $output .= chr(ord('0') + $this->iteration_count_log2 % 10);
        $output .= '$';

        $i = 0;
        do {
            $c1 = ord($input[$i++]);
            $output .= $itoa64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            if ($i >= 16) {
                $output .= $itoa64[$c1];
                break;
            }

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 4;
            $output .= $itoa64[$c1];
            $c1 = ($c2 & 0x0f) << 2;

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 6;
            $output .= $itoa64[$c1];
            $output .= $itoa64[$c2 & 0x3f];
        } while (1);

        return $output;
    }

    function HashPassword($password) {
        $random = '';

        if (CRYPT_BLOWFISH == 1 && !$this->portable_hashes) {
            $random = $this->get_random_bytes(16);
            $hash =
                    crypt($password, $this->gensalt_blowfish($random));
            if (strlen($hash) == 60)
                return $hash;
        }

        if (CRYPT_EXT_DES == 1 && !$this->portable_hashes) {
            if (strlen($random) < 3)
                $random = $this->get_random_bytes(3);
            $hash =
                    crypt($password, $this->gensalt_extended($random));
            if (strlen($hash) == 20)
                return $hash;
        }

        if (strlen($random) < 6)
            $random = $this->get_random_bytes(6);
        $hash =
                $this->crypt_private($password, $this->gensalt_private($random));
        if (strlen($hash) == 34)
            return $hash;

        # Returning '*' on error is safe here, but would _not_ be safe
        # in a crypt(3)-like function used _both_ for generating new
        # hashes and for validating passwords against existing hashes.
        return '*';
    }

    function CheckPassword($password, $stored_hash) {
        $hash = $this->crypt_private($password, $stored_hash);
        if ($hash[0] == '*')
            $hash = crypt($password, $stored_hash);

        return $hash == $stored_hash;
    }

}

?>