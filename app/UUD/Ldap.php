<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 4/26/16
 * Time: 1:52 PM
 */

namespace App\UUD;


use App\Preference;

class Ldap
{
    /**
     *
     * Object Properties and setters
     *
     */

    protected $ldap_bind_rety_count = 4;

    /**
     * @var \App\Preference
     */
    protected $preferences;

    /**
     * @return void
     */
    protected function preferences()
    {
        $this->preferences = Preference::all()->first();
    }

    /**
     * @var boolean
     */
    protected $enabled = false;

    /**
     * @return void
     */
    protected function enabled()
    {
        if (isset($this->preferences)) {
            $this->enabled = isset($this->preferences->ldap_enabled) ? $this->preferences->ldap_enabled : false;
        } else {
            $this->enabled = false;
        }
    }

    /**
     * @var array
     */
    protected $hosts;

    /**
     * @return void
     */
    protected function hosts()
    {
        $this->hosts = $this->hosts2Array($this->preferences->ldap_servers);
    }

    /**
     * @var boolean
     */
    protected $use_ssl = false;

    /**
     * @return void
     */
    protected function use_ssl()
    {
        $this->use_ssl = $this->preferences->ldap_ssl;
    }

    /**
     * @var string
     */

    protected $bind_user;

    /**
     * @return void
     */
    protected function bind_user()
    {
        $this->bind_user = $this->preferences->ldap_bind_user;
    }

    /**
     * @var string
     */
    protected $bind_password;

    /**
     * @return void
     */
    protected function bind_password()
    {
        $this->bind_password = $this->preferences->ldap_bind_password;
    }

    /**
     * @var string
     */
    protected $search_base;

    /**
     * @return void
     */
    protected function search_base()
    {
        $this->search_base = $this->preferences->ldap_search_base;
    }

    /**
     * @var string
     */
    protected $domain;

    /**
     * @return void
     */
    protected function domain()
    {
        $this->domain = $this->convertDomain($this->preferences->ldap_domain);
    }

    /**
     * @var resource
     */
    public $connection;

    /**
     * @return bool|resource
     */
    private function connect()
    {
        if ($this->enabled) {
            $prefix = ($this->use_ssl) ? 'ldaps://' : 'ldap://';
            $port = ($this->use_ssl) ? 636 : 389;
            foreach ($this->hosts as $host) {
                // Connect to the current host using the scheme and port specified
                $this->connection = ldap_connect($prefix . $host, $port);
                // Set ldap options
                //ldap_set_option($this->connection, LDAP_OPT_NETWORK_TIMEOUT, 10);
                //ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
                ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);

                // If the authenticate function returns true, break out of the foreach hosts loop and return the connection resource.
                // Otherwise move onto the next host.
                if ($this->authenticate($this->connection, $this->domain, $this->bind_user, $this->bind_password)) {
                    return $this->connection;
                }
            }
            return false;
        }
    }

    /**
     * @param $connection
     * @param $short_domain
     * @param $bind_user
     * @param $bind_password
     * @return bool
     */
    private function authenticate($connection, $short_domain, $bind_user, $bind_password)
    {
        // Try to bind with the connection and credentials
        $bind = @ldap_bind($connection, $short_domain . '\\' . $bind_user, $bind_password);
        // Return a boolean based on the successes of the bind
        return ($bind) ? true : false;
    }

    /**
     *
     * End Object Properties and setters
     *
     */

    /**
     * Ldap constructor.
     */
    public function __construct()
    {
        // Load our preferences
        $this->preferences();
        // Determine if LDAP is enabled
        $this->enabled();
        // If ldap is enabled
        if ($this->enabled) {
            // Load up the rest of our configuration
            $this->hosts();
            $this->use_ssl();
            $this->bind_user();
            $this->bind_password();
            $this->search_base();
            $this->domain();
            // Connect to LDAP
            $this->connect();
        }
    }

    /**
     *
     * Helper Functions
     *
     */

    /**
     * @param string $host
     * @param bool $use_ssl
     * @param string $bind_user
     * @param string $bind_password
     * @param string $domain
     * @return array
     */
    public function testBind($host = '', $use_ssl = false, $bind_user = '', $bind_password = '', $domain = '')
    {
        $prefix = ($use_ssl) ? 'ldaps://' : 'ldap://';
        $port = ($this->use_ssl) ? 636 : 389;
        $conn = ldap_connect($prefix . $host, $port);

        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 10);
        ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        $bind = $this->authenticate($conn, $domain, $bind_user, $bind_password);
        $message = ($bind) ? 'Success' : ldap_error($conn);
        $status = ($bind) ? true : false;
        return ['status' => $status, 'message' => $message];
    }

    /**
     * @param string $domain
     * @return mixed
     */
    public function convertDomain($domain = '')
    {
        return (str_contains($domain, '.')) ? strtoupper(trim(explode('.', $domain)[0])) : strtoupper(trim($domain));
    }

    /**
     * @param string $hosts
     * @return array
     */
    public function hosts2Array($hosts = '')
    {
        $hosts_out = [];
        if (!empty($hosts)) {
            if (str_contains($hosts, ',')) {
                foreach (explode(',', trim($hosts)) as $host) {
                    $hosts_out[] = trim($host);
                }
            } else {
                $hosts_out[] = trim($hosts);
            }
        }
        return $hosts_out;
    }

    /*
    * @param string $filter
    * @param array $attributes
    * @return array
    */
    public function query_ldap($filter = '', $attributes = array('*'), $binary = false)
    {
        if ($binary) {
            $search = ldap_search($this->connection, $this->search_base, $filter);
            $entry = ldap_first_entry($this->connection, $search);
            if (!$entry) return false;
            $results = ldap_get_values_len($this->connection, $entry, $attributes) or false;
        } else {
            $search = ldap_search($this->connection, $this->search_base, $filter, $attributes);
            $results = ldap_get_entries($this->connection, $search);
        }
        return $results;
    }

    /**
     * @param string $samAccountName
     * @return string|bool
     */
    public function samAccountName2Dn($samAccountName = '')
    {
        if (!empty(trim($samAccountName))) {
            $filter = '(&(objectClass=top)(objectClass=person)(objectClass=user)(sAMAccountName=' . $samAccountName . '))';
            $attributes = ['distinguishedName'];
            $results = $this->query_ldap($filter, $attributes);
            $bin = $this->query_ldap($filter, 'objectGUID', true);
            if (!$bin) return false;
            $hex = unpack("H*hex", $bin[0]);
            $results[0]['objectGUID'] = $hex['hex'];
            return ($results['count'] > 0 && isset($results[0]['distinguishedname'][0])) ? $results[0]['distinguishedname'][0] : false;
        }
        return false;
    }

    /**
     * @param string $password
     * @return string
     */
    public function unicodePassword_field($password = '')
    {
        $return = '';
        if (empty($password)) $password = str_random(16) . '1!Ab';
        $password = "\"" . $password . "\"";
        for ($i = 0; $i < strlen($password); $i++) {
            $return .= "{$password{$i}}\000";
        }
        return $return;
    }

    /**
     * @param string $dn
     * @param string $newPassword
     * @return array
     */
    public function changePassword($dn = '', $newPassword = '')
    {
        // Encode the password
        $password = $this->unicodePassword_field($newPassword);
        $attrs['unicodepwd'] = $password;
        $attrs['pwdLastSet'] = '-1';
        $attrs['lockouttime'] = 0;
        try {
            ldap_mod_replace($this->connection, $dn, $attrs);
        } catch (\ErrorException $e) {
            return [false, ldap_error($this->connection)];
        } catch (\Exception $e) {
            return [false, ldap_error($this->connection)];
        }
        return [true, $password];
    }

    /**
     *
     * End Helper Functions
     *
     */


}