<?php

require 'php-digest.class.php';

class phpAuthMySQL extends phpAuth {
    /*
     * This information is used to connect to the authentication user database
     */

    /**
     * The host for the authentication MySQL Server
     * @var string
     */
    private $db_auth_host = 'localhost';

    /**
     * The user for the authentication MySQL Server
     * @var string
     */
    private $db_auth_user = 'arc';

    /**
     * The password for the authentication MySQL Server
     * @var string
     */
    private $db_auth_pass = 'arc';

    /**
     * The database for the authentication MySQL Server
     * @var string
     */
    private $db_auth_data = 'arc';

    /**
     * The user table
     * @var string
     */
    private $db_user_table = 'arc_users';

    /*
     * This information is used to connect to the backend database 
     */

    /**
     * The host for the backend MySQL Server
     * @var string
     */
    private $db_back_host = 'localhost';

    /**
     * The username for the backend MySQL Server
     * @var string
     */
    private $db_back_user = 'arc';

    /**
     * The password for the backend MySQL Server
     * @var string
     */
    private $db_back_pass = 'arc';

    /**
     * The database to use for the backend MySQL Server
     * @var string
     */
    private $db_back_data = 'arc';

    /**
     * The nonce table in the database.
     * @var string
     */
    private $db_nonce_table = 'arc_nonce';

    /**
     * The log table in the database
     * @var string
     */
    private $db_log_table = 'arc_log';

    function __construct() {

        $opts = array(
            'get_hash_function' => 'getHashMySQL',
            'db_connect_function' => 'connectMySQL',
            'db_close_function' => 'closeMySQL',
            'nonce_create_function' => 'mkNonceMySQL',
            'nonce_check_function' => 'checkNonceMySQL',
            'nonce_expire_function' => 'expNonceMySQL',
            'log_auth_function' => 'logAuthMySQL',
            'log_check_function' => 'logCheckMySQL'
        );
        $this->setPrivates($opts);

        /* This must be called AFTER the set privates is called, otherwise the parent is unaware of what functions
         * to use and will crash.
         */
        parent::__construct();
    }

    /**
     * Connect to the listed MySQL databases for use in other functions.
     */
    protected function connectMySQL() {
        $this->debugPrint('Contacting MySQL Server...');

        /* Create the connection to the Authentication DB */
        $this->authdb = @new mysqli($this->db_auth_host, $this->db_auth_user, $this->db_auth_pass, $this->db_auth_data);
        if ($this->authdb->connect_errno)
            $this->callError("Error: Could not connect to auth database!! MySQL returned: " . $this->authdb->connect_error);

        /* Create the connection to the backend DB */
        $this->backdb = new mysqli($this->db_back_host, $this->db_back_user, $this->db_back_pass, $this->db_back_data);
        if ($this->backdb->connect_errno)
            $this->callError("Error: Could not connect to backend database!! MySQL returned: " . $this->backdb->connect_error);
    }

    /**
     * Attempt to get the user's hash from the database
     * @param string $user The username for the hash to get.
     * @return mixed Returns the user's hash, FALSE if the user is not found.
     */
    protected function getHashMySQL($user) {
        $this->debugPrint('Fetching User Hash...');

        /* Check to see if we are connected (Usually yes, but occasionaly no) */
        $local = FALSE;
        if (!is_a($this->authdb, 'mysqli')) {
            $this->connectMySQL();
            $local = TRUE;
        }

        /* Sanitize here, in case it hasn't been done elsewhere */
        $user = $this->authdb->real_escape_string($user);

        $query = "SELECT `hash` FROM `{$this->db_auth_data}`.`{$this->db_user_table}` WHERE `username` = '$user'";
        $result = $this->authdb->query($query)
                or $this->callError("Error: Could not select hash from database!! MySQL returned: " . $this->authdb->error);
        if ($result->num_rows != 1) {
            $this->debugPrint('Invalid username, failing.');
            return false;
        }
        $row = $result->fetch_assoc();
        $result->free();

        /* If we connected here, disconnect as well. */
        if ($local)
            $this->closeMySQL();

        return $row['hash'];
    }

    /**
     * Inserts the nonce into the database to make it valid.
     * @param string $nonce The nonce to validate
     * @return boolean Returns TRUE on sucess.
     */
    protected function mkNonceMySQL($nonce) {
        $local = FALSE;
        if (!is_a($this->authdb, 'mysqli')) {
            $this->connectMySQL();
            $local = TRUE;
        }

        $nonce = $this->backdb->real_escape_string($nonce);

        $this->debugPrint('Inserting new nonce...');

        $thetime = time();
        $exptime = $thetime + $this->nonce_expire;

        $query = "INSERT INTO `{$this->db_back_data}`.`{$this->db_nonce_table}` VALUES ( '$nonce', $thetime, $thetime, $exptime, 0 )";
        $this->backdb->query($query)
                or $this->callError("Error: Cannot insert new nonce!! MySQL Returned: " . $this->backdb->error);
        if ($local)
            $this->closeMySQL();
        return true;
    }

    /**
     * Checks the validity of a nonce.
     * @param string $nonce The nonce to check
     * @param integer $nc The NC value from the client
     * @return boolean Returns TRUE if valid, FALSE if invalid.
     */
    protected function checkNonceMySQL($nonce, $nc) {
        $local = FALSE;
        if (!is_a($this->authdb, 'mysqli')) {
            $this->connectMySQL();
            $local = TRUE;
        }

        $nonce = $this->backdb->real_escape_string($nonce);
        $nc = $this->backdb->real_escape_string($nc);

        $this->debugPrint('Checking nonce...');
        $thetime = time();
        $exptime = $thetime + $this->nonce_expire;

        $getQuery = "SELECT * FROM `{$this->db_back_data}`.`{$this->db_nonce_table}` WHERE `nonce` LIKE '$nonce' AND `nc` < $nc";
        $result = $this->backdb->query($getQuery)
                or callError("Error: Cannot get nonce data!! MySQL Returned: " . $this->backdb->error);
        if ($result->num_rows != 1) {
            $this->debugPrint('No nonce found, failing.');
            if ($local)
                $this->closeMySQL();
            return false;
        }
        $row = $result->fetch_assoc();
        $result->free();

        /* Check the expire */
        if (( $this->session_check_method == 'NONCE'
                || $this->session_check_method == 'BOTH' )
                && $row['expire'] < $thetime) {
            $this->debugPrint('Nonce expired, failing.');
            if ($local)
                $this->closeMySQL();
            return false;
        }

        /* Double check the expire time (to prevent high nonce expires) */
        if (( $this->session_check_method == 'NONCE'
                || $this->session_check_method == 'BOTH' )
                && ($row['exptime'] - $row['thetime']) > $this->nonce_expire) {
            $this->debugPrint('Nonce high expire, failing');
            if ($local)
                $this->closeMySQL();
            return false;
        }

        /* Update the db with our new nonce count */
        $insQuery = "UPDATE `{$this->db_back_data}`.`{$this->db_nonce_table}` SET `expire` = $exptime, `lastseen` = $thetime, `nc` = $nc WHERE `nonce` = '$nonce'";
        $this->backdb->query($insQuery)
                or $this->callError("Error: Cannot update nonce expire time!! MySQL Returned: " . $this->backdb->error);

        if ($local)
            $this->closeMySQL();

        /* Return true */
        return true;
    }

    /**
     * Expires a nonce.
     * @param string $nonce The nonce to expire
     * @return boolean Returns TRUE on success.
     */
    protected function expNonceMySQL($nonce) {
        $local = FALSE;
        if (!is_a($this->authdb, 'mysqli')) {
            $this->connectMySQL();
            $local = TRUE;
        }

        $nonce = $this->backdb->real_escape_string($nonce);

        $this->debugPrint('Expiring nonce...');

        $thetime = time();
        $exptime = $thetime - ( $this->nonce_expire - 600 );
        $query = "UPDATE `{$this->db_back_data}`.`{$this->db_nonce_table}` SET `expire` = $exptime, `lastseen` = $thetime WHERE `nonce` = '$nonce'";

        $this->backdb->query($query)
                or $this->callError("Error: Cannot expire nonce!! MySQL Returned: " . $this->backdb->error);

        if ($local)
            $this->closeMySQL();

        return true;
    }

    /**
     * Logs the result of an authentication.
     * @param string $ip The IP address to store.
     * @param integer $result The result of the auth
     * @param string $user The username to store.
     */
    protected function logAuthMySQL($ip, $result, $user) {
        $this->debugPrint("Logging this auth ($result)...");

        $longip = sprintf("%u", ip2long($ip));
        $thetime = time();
        $query = "INSERT INTO `{$this->db_back_data}`.`{$this->db_log_table}` VALUES (NULL, 
            $result, $thetime, $longip, '$user' )";
        $this->backdb->query($query)
                or $this->callError("Error: Cannot log this auth!! MySQL returned: " . $this->backdb->error);
    }

    /**
     * Checks the log to see if the user is blacklisted.
     * @param string $ip The IP of the user.
     * @return boolean Returns TRUE if the user is OK to log-in, FALSE if the user is blacklisted.
     */
    protected function logCheckMySQL($ip) {
        $this->debugPrint('Checking auth logs...');

        $minuteago = time() - 60;
        $longip = sprintf("%u", ip2long($ip));
        $query = "SELECT * FROM `{$this->db_back_data}`.`{$this->db_log_table}` WHERE `ip` = $longip 
	      AND `epoch` >= $minuteago AND `result` != 0";
        $result = $this->backdb->query($query)
                or callError("Error: Cannot get auths!!(1) MySQL returned: " . $this->backdb->error);

        if ($result->num_rows > 25)
            $blacklist = 1;

        $result->free();

        if ($blacklist == 1) {
            $this->debugPrint('Log check failed.');
            return false;
        }

        return true;
    }

    /**
     * Closes the MySQL connections
     */
    protected function closeMySQL() {
        $this->authdb->close();
        $this->backdb->close();
    }

}

?>
