<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Ldap;

use Zend\Ldap\Exception\LdapException;

class ReconnectTest extends AbstractOnlineTestCase
{
    protected static function getStandardOptions()
    {
        // Options array setup copied verbatim from
        // AbstractOnlineTestCase::setUpBeforeClass(), where it is unfortunately
        // not readily accessible without refactoring AbstractOnlineTestCase.
        $options = [
            'host'     => getenv('TESTS_ZEND_LDAP_HOST'),
            'username' => getenv('TESTS_ZEND_LDAP_USERNAME'),
            'password' => getenv('TESTS_ZEND_LDAP_PASSWORD'),
            'baseDn'   => getenv('TESTS_ZEND_LDAP_WRITEABLE_SUBTREE'),
        ];
        if (getenv('TESTS_ZEND_LDAP_PORT') && getenv('TESTS_ZEND_LDAP_PORT') != 389) {
            $options['port'] = getenv('TESTS_ZEND_LDAP_PORT');
        }
        if (getenv('TESTS_ZEND_LDAP_USE_START_TLS')) {
            $options['useStartTls'] = getenv('TESTS_ZEND_LDAP_USE_START_TLS');
        }
        if (getenv('TESTS_ZEND_LDAP_USE_SSL')) {
            $options['useSsl'] = getenv('TESTS_ZEND_LDAP_USE_SSL');
        }
        if (getenv('TESTS_ZEND_LDAP_BIND_REQUIRES_DN')) {
            $options['bindRequiresDn'] = getenv('TESTS_ZEND_LDAP_BIND_REQUIRES_DN');
        }
        if (getenv('TESTS_ZEND_LDAP_ACCOUNT_FILTER_FORMAT')) {
            $options['accountFilterFormat'] = getenv('TESTS_ZEND_LDAP_ACCOUNT_FILTER_FORMAT');
        }
        if (getenv('TESTS_ZEND_LDAP_ACCOUNT_DOMAIN_NAME')) {
            $options['accountDomainName'] = getenv('TESTS_ZEND_LDAP_ACCOUNT_DOMAIN_NAME');
        }
        if (getenv('TESTS_ZEND_LDAP_ACCOUNT_DOMAIN_NAME_SHORT')) {
            $options['accountDomainNameShort'] = getenv('TESTS_ZEND_LDAP_ACCOUNT_DOMAIN_NAME_SHORT');
        }
        return $options;
    }

    public function setUp()
    {
        if (! getenv('TESTS_ZEND_LDAP_ONLINE_ENABLED')) {
            $this->markTestSkipped("Zend_Ldap online tests are not enabled");
        }

        $this->getLDAP()->setOptions(static::getStandardOptions());
    }

    public function tearDown()
    {
        // Make sure we're using a non-expired connection with known settings
        // for each test.
        $this->getLDAP()->disconnect();
    }

    protected function triggerReconnection()
    {
        $entry = $this->getLDAP()->getEntry(
            'uid=' . getenv('TESTS_ZEND_LDAP_ALT_USERNAME') . ',' . getenv('TESTS_ZEND_LDAP_BASE_DN'),
            ['uid']
        );
        $this->assertEquals(
            getenv('TESTS_ZEND_LDAP_ALT_USERNAME'),
            $entry['uid'][0]
        );
        $this->assertEquals(
            0,
            $this->getLDAP()->getReconnectsAttempted()
        );

        $this->causeLdapConnectionFailure();

        $entry = $this->getLDAP()->getEntry(
            'uid=' . getenv('TESTS_ZEND_LDAP_ALT_USERNAME') . ',' . getenv('TESTS_ZEND_LDAP_BASE_DN'),
            ['uid']
        );
        $this->assertEquals(
            getenv('TESTS_ZEND_LDAP_ALT_USERNAME'),
            $entry['uid'][0]
        );

        $this->assertGreaterThan(
            0,
            $this->getLDAP()->getReconnectsAttempted()
        );
    }

    protected function causeLdapConnectionFailure()
    {
        $url = sprintf(
            'http://%s:%s/drop_3890.php',
            getenv('TESTS_ZEND_LDAP_HOST'),
            getenv('TESTS_ZEND_LDAP_SCRIPTS_PORT')
        );
        file_get_contents($url);
    }

    public function testNoReconnectWhenNotRequested()
    {
        $this->getLDAP()->setOptions(
            array_merge(
                $this->getLDAP()->getOptions(),
                ['reconnectAttempts' => 0]
            )
        );

        $this->getLDAP()->bind();
        $entry = $this->getLDAP()->getEntry(
            'uid=' . getenv('TESTS_ZEND_LDAP_ALT_USERNAME') . ',' . getenv('TESTS_ZEND_LDAP_BASE_DN'),
            ['uid']
        );
        $this->assertEquals(
            getenv('TESTS_ZEND_LDAP_ALT_USERNAME'),
            $entry['uid'][0]
        );

        $this->causeLdapConnectionFailure();

        $this->assertNull(
            $this->getLDAP()->getEntry(
                'uid=' . getenv('TESTS_ZEND_LDAP_ALT_USERNAME') . ',' . getenv('TESTS_ZEND_LDAP_BASE_DN'),
                ['uid']
            ),
            'A query on a connection that should have been timed out was honored by the server.'
        );
    }

    public function testReconnectWhenRequested()
    {
        $this->getLDAP()->setOptions(
            array_merge(
                $this->getLDAP()->getOptions(),
                ['reconnectAttempts' => 1]
            )
        );

        $this->getLDAP()->bind();
        $this->triggerReconnection();
    }

    public function testMultipleReconnectAttempts()
    {
        $this->getLDAP()->setOptions(
            array_merge(
                $this->getLDAP()->getOptions(),
                [
                    'reconnectAttempts' => 2,
                    'port'              => 3899
                ]
            )
        );

        try {
            $this->getLDAP()->bind();
            $this->assertTrue(false, 'Server listening on unexpected port?');
        } catch (LdapException $e) {
            $this->assertEquals(
                2,
                $this->getLDAP()->getReconnectsAttempted()
            );
        }
    }

    public function testConnectParameterPreservation()
    {
        $options = $this->getLDAP()->getOptions();
        unset($options['host']);
        unset($options['port']);
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->connect(
            getenv('TESTS_ZEND_LDAP_HOST'),
            getenv('TESTS_ZEND_LDAP_PORT')
        );

        $this->triggerReconnection();
    }

    public function testParametersOverridePropertiesDuringReconnect()
    {
        $options = $this->getLDAP()->getOptions();
        $options['port'] += 9;
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->connect(null, getenv('TESTS_ZEND_LDAP_PORT'));
        $this->triggerReconnection();
    }

    /**
     * TODO: Add this test once merged with PR#64, which has SSL support in CI.
     * public function testReconnectionWithSsl()
     * {
     * $options = $this->getLDAP()->getOptions();
     * $options['port'] = getenv('TESTS_ZEND_LDAPS_PORT');
     * $options['reconnectAttempts'] = 1;
     * $this->getLDAP()->setOptions($options);
     *
     * $this->getLDAP()->connect(null, null, true);
     * $this->triggerReconnection();
     * }
     */

    public function testAddReconnect()
    {
        $options = $this->getLDAP()->getOptions();
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->bind();

        $dn = $this->createDn('ou=TestCreatedOnReconnect,');
        $data = [
            'ou'          => 'TestCreatedOnReconnect',
            'objectClass' => 'organizationalUnit'
        ];

        if ($this->getLDAP()->exists($dn)) {
            $this->getLDAP()->delete($dn);
        }

        $this->assertEquals(0, $this->getLDAP()->getReconnectsAttempted());

        $this->causeLdapConnectionFailure();

        $this->getLDAP()->add($dn, $data);
        $this->assertEquals(1, $this->getLDAP()->getReconnectsAttempted());
        $this->assertEquals(1, $this->getLDAP()->count('ou=TestCreatedOnReconnect'));
        $this->getLDAP()->delete($dn);
        $this->assertEquals(0, $this->getLDAP()->count('ou=TestCreatedOnReconnect'));
    }

    public function testUpdateReconnect()
    {
        $options = $this->getLDAP()->getOptions();
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->bind();

        $dn = $this->createDn('ou=TestModifiedOnReconnect,');
        $data = [
            'ou'          => 'TestModifiedOnReconnect',
            'l'           => 'mylocation1',
            'objectClass' => 'organizationalUnit'
        ];

        if ($this->getLDAP()->exists($dn)) {
            $this->getLDAP()->delete($dn);
        }
        $this->getLDAP()->add($dn, $data);
        $entry = $this->getLDAP()->getEntry($dn);

        $this->assertEquals(0, $this->getLDAP()->getReconnectsAttempted());
        $this->causeLdapConnectionFailure();

        $entry['l'] = 'mylocation2';
        $this->getLDAP()->update($dn, $entry);
        $this->assertEquals(1, $this->getLDAP()->getReconnectsAttempted());
        $entry = $this->getLDAP()->getEntry($dn);
        $this->getLDAP()->delete($dn);
        $this->assertEquals('mylocation2', $entry['l'][0]);
    }

    public function testDeleteReconnect()
    {
        $options = $this->getLDAP()->getOptions();
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->bind();

        $dn = $this->createDn('ou=TestDeletedOnReconnect,');
        $data = [
            'ou'          => 'TestDeletedOnReconnect',
            'objectClass' => 'organizationalUnit'
        ];

        if (! $this->getLDAP()->exists($dn)) {
            $this->getLDAP()->add($dn, $data);
        }

        $this->assertEquals(0, $this->getLDAP()->getReconnectsAttempted());
        $this->causeLdapConnectionFailure();

        $this->getLDAP()->delete($dn);
        $this->assertEquals(1, $this->getLDAP()->getReconnectsAttempted());
    }

    public function testRenameReconnect()
    {
        if (! function_exists('ldap_rename')) {
            $this->markTestSkipped("Test would provide no useful coverage
            because the php installation lacks ldap_rename().");
        }
        $options = $this->getLDAP()->getOptions();
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->bind();

        $dn = $this->createDn('ou=TestRenameOnReconnect,');
        $data = [
            'ou'          => 'TestRenameOnReconnect',
            'objectClass' => 'organizationalUnit'
        ];

        if (! $this->getLDAP()->exists($dn)) {
            $this->getLDAP()->add($dn, $data);
        }

        $this->assertEquals(0, $this->getLDAP()->getReconnectsAttempted());
        $this->causeLdapConnectionFailure();

        $new_dn = $this->createDn('ou=TestRenamedOnReconnect');
        $this->getLDAP()->rename($dn, $new_dn);
        $this->assertEquals(1, $this->getLDAP()->getReconnectsAttempted());

        $this->getLDAP()->delete($new_dn, true);
    }

    public function testErroneousModificationDoesNotTriggerReconnect()
    {
        $options = $this->getLDAP()->getOptions();
        $options['reconnectAttempts'] = 1;
        $this->getLDAP()->setOptions($options);

        $this->getLDAP()->bind();

        $dn   = $this->createDn('ou=DoesNotExistReconnect,');
        $data = [
            'ou'          => 'DoesNotExistReconnect',
            'objectClass' => 'organizationalUnit'
        ];

        try {
            $this->getLDAP()->update($dn, $data);
            $this->assertFalse(true, 'Update of nonexistent DN succeeded?');
        } catch (LdapException $e) {
            $this->assertEquals(0, $this->getLDAP()->getReconnectsAttempted());
        }
    }
}
