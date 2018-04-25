<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Ldap\Exception;

use PHPUnit\Framework\TestCase;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap;

class LdapExceptionTest extends TestCase
{
    /**
     * @dataProvider constructorArgumentsProvider
     *
     * @param Ldap $ldap
     * @param string $message
     * @param int $code
     * @param string $expectedMessage
     * @param int $expectedCode
     */
    public function testException($ldap, $message, $code, $expectedMessage, $expectedCode)
    {
        $e = new LdapException($ldap, $message, $code);

        $this->assertEquals($expectedMessage, $e->getMessage());
        $this->assertEquals($expectedCode, $e->getCode());
    }

    public function constructorArgumentsProvider()
    {
        return [
            // Description => [LDAP object, message, code, expected message, expected code]
            'default' => [null, '', 0, 'no exception message', 0],
            'hexadecimal' => [null, '', 15, '0xf: no exception message', 15],
        ];
    }
}
