<?php
/**
 * @link      http://github.com/zendframework/zend-ldap for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

/*
 * Set error reporting to the level to which Zend Framework code must comply.
 */
error_reporting(E_ALL | E_STRICT);

/**
 * Setup autoloading
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * Start output buffering, if enabled
 */
if (defined('TESTS_ZEND_OB_ENABLED') && constant('TESTS_ZEND_OB_ENABLED')) {
    ob_start();
}

/**
 * A limitation in the OpenLDAP libraries linked to PHP requires that if a
 * client certificate/key will be used in any ldap bind, the environment must
 * point to them before the first bind made by the process, even if that first
 * bind is not client certificate-based.
 *
 * Therefore, configure this aspect of the environment here in bootstrap.
 * Applications using a client cert with zend-ldap should similarly ensure their
 * environment variables are set before the first ldap connect/bind.
 */
putenv(sprintf("LDAPTLS_CERT=%s", getenv('TESTS_ZEND_LDAP_SASL_CERTIFICATE')));
putenv(sprintf("LDAPTLS_KEY=%s", getenv('TESTS_ZEND_LDAP_SASL_KEY')));

/**
 * Work around https://bugs.php.net/bug.php?id=68541 by defining function
 * mocks early.
 *
 * The Mock instances need to be defined now, but accessible for enabling/
 * inspection by OfflineTest.
 * They are wrapped in a class because if they were simply declared globally,
 * phpunit would find them and error while attempting to serialize global
 * variables.
 */
class LdapReusableMocks
{
    public static $ldap_connect_mock = null;
    public static $ldap_bind_mock = null;
    public static $ldap_set_option_mock = null;

    public static function createMocks()
    {
        $ldap_connect_mock = new \phpmock\Mock(
            'Zend\\Ldap',
            'ldap_connect',
            function () {
                static $a_resource = null;
                if ($a_resource == null) {
                    $a_resource = fopen(__FILE__, 'r');
                }
                return $a_resource;
            }
        );

        $ldap_bind_mock = new \phpmock\Mock(
            'Zend\\Ldap',
            'ldap_bind',
            function () {
                return true;
            }
        );

        $ldap_set_option_mock = new \phpmock\Mock(
            'Zend\\Ldap',
            'ldap_set_option',
            function () {
                return true;
            }
        );

        $ldap_connect_mock->define();
        $ldap_bind_mock->define();
        $ldap_set_option_mock->define();

        static::$ldap_connect_mock = $ldap_connect_mock;
        static::$ldap_bind_mock = $ldap_bind_mock;
        static::$ldap_set_option_mock = $ldap_set_option_mock;
    }
}
LdapReusableMocks::createMocks();
