<?php
namespace ZendTest\Ldap\TestAsset;

class BuiltinFunctionMocks
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
