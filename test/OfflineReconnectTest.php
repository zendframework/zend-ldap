<?php


namespace ZendTest\Ldap;

use phpmock\Mock;
use ZendTest\Ldap\TestAsset\BuiltinFunctionMocks;

class OfflineReconnectTest extends OfflineTest
{
    /**
     * Enables mocks for ldap_connect(), ldap_bind(), and ldap_set_option().
     * Not all tests need or are compatible with this, so it is called expliclty
     * by tests that do.
     */
    protected function activateBindableOfflineMocks()
    {
        BuiltinFunctionMocks::$ldap_connect_mock->enable();
        BuiltinFunctionMocks::$ldap_bind_mock->enable();
        BuiltinFunctionMocks::$ldap_set_option_mock->enable();
    }

    public function tearDown()
    {
        parent::tearDown();
        Mock::disableAll();
    }

    protected function reportErrorsAsConnectionFailure()
    {
        $ldap_errno = $this->getFunctionMock('Zend\\Ldap', 'ldap_errno');
        $ldap_errno->expects($this->atLeastOnce())
            ->willReturn(-1);
    }

    public function testAddingAttributesReconnect()
    {
        $this->activateBindableOfflineMocks();
        $this->reportErrorsAsConnectionFailure();

        $ldap_mod_add = $this->getFunctionMock('Zend\\Ldap', 'ldap_mod_add');
        $ldap_mod_add->expects($this->exactly(2))
            ->willReturnOnConsecutiveCalls(false, true);

        $ldap = new \Zend\Ldap\Ldap([
            'host' => 'offline phony',
            'reconnectAttempts' => 1
        ]);
        $ldap->bind();
        $ldap->addAttributes('foo', ['bar']);
        $this->assertEquals(1, $ldap->getReconnectsAttempted());
    }

    public function testRemovingAttributesReconnect()
    {
        $this->activateBindableOfflineMocks();
        $this->reportErrorsAsConnectionFailure();

        $ldap_mod_del = $this->getFunctionMock('Zend\\Ldap', 'ldap_mod_del');
        $ldap_mod_del->expects($this->exactly(2))
            ->willReturnOnConsecutiveCalls(false, true);

        $ldap = new \Zend\Ldap\Ldap([
            'host' => 'offline phony',
            'reconnectAttempts' => 1
        ]);
        $ldap->bind();
        $ldap->deleteAttributes('foo', ['bar']);
        $this->assertEquals(1, $ldap->getReconnectsAttempted());
    }

}