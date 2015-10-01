<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Ldap;

use Zend\Ldap\Collection\DefaultIterator;

class SortTest extends AbstractOnlineTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->prepareLDAPServer();
    }

    protected function tearDown()
    {
        $this->cleanupLDAPServer();
        parent::tearDown();
    }

    /**
     * Test whether a callable is set correctly
     */
    public function testSettingCallable()
    {
        $search = ldap_search(
            $this->getLDAP()->getResource(),
            getenv('TESTS_ZEND_LDAP_WRITEABLE_SUBTREE'),
            '(l=*)',
            ['l']
        );

        $iterator = new DefaultIterator($this->getLdap(), $search);
        $sortFunction = function ($a, $b) {
            return 1;
        };

        $this->assertAttributeEquals('strnatcasecmp', 'sortFunction', $iterator);
        $iterator->setSortFunction($sortFunction);
        $this->assertAttributeEquals($sortFunction, 'sortFunction', $iterator);
    }

    /**
     * Test whether sorting works as expected out of the box
     */
    public function testSorting()
    {
        $lSorted = ['a', 'b', 'c', 'd', 'e'];

        $search = ldap_search(
            $this->getLDAP()->getResource(),
            getenv('TESTS_ZEND_LDAP_WRITEABLE_SUBTREE'),
            '(l=*)',
            ['l']
        );

        $iterator = new DefaultIterator($this->getLdap(), $search);

        $this->assertAttributeEquals('strnatcasecmp', 'sortFunction', $iterator);
        $reflectionObject = new \ReflectionObject($iterator);
        $reflectionProperty = $reflectionObject->getProperty('entries');
        $reflectionProperty->setAccessible(true);
        $reflectionEntries = $reflectionProperty->getValue($iterator);

        $iterator->sort('l');

        $this->assertAttributeEquals([
            [
                'resource' => $reflectionEntries[4]['resource'],
                'sortValue' => 'a',
            ], [
                'resource' => $reflectionEntries[3]['resource'],
                'sortValue' => 'b',
            ], [
                'resource' => $reflectionEntries[2]['resource'],
                'sortValue' => 'c',
            ], [
                'resource' => $reflectionEntries[1]['resource'],
                'sortValue' => 'd',
            ], [
                'resource' => $reflectionEntries[0]['resource'],
                'sortValue' => 'e',
            ],
        ], 'entries', $iterator);
    }

    /**
     * Test sorting with custom sort-function
     */
    public function testCustomSorting()
    {
        $lSorted = ['a', 'b', 'c', 'd', 'e'];

        $search = ldap_search(
            $this->getLDAP()->getResource(),
            getenv('TESTS_ZEND_LDAP_WRITEABLE_SUBTREE'),
            '(l=*)',
            ['l']
        );

        $iterator = new DefaultIterator($this->getLdap(), $search);
        $sortFunction = function ($a, $b) use ($lSorted) {
            if (array_search($a, $lSorted) % 2 === 0) {
                return -1;
            }

            return 1;
        };
        $iterator->setSortFunction($sortFunction);

        $this->assertAttributeEquals($sortFunction, 'sortFunction', $iterator);
        $reflectionObject = new \ReflectionObject($iterator);
        $reflectionProperty = $reflectionObject->getProperty('entries');
        $reflectionProperty->setAccessible(true);
        $reflectionEntries = $reflectionProperty->getValue($iterator);

        $iterator->sort('l');

        $this->assertAttributeEquals([
            [
                'resource' => $reflectionEntries[4]['resource'],
                'sortValue' => 'a',
            ], [
                'resource' => $reflectionEntries[0]['resource'],
                'sortValue' => 'e',
            ], [
                'resource' => $reflectionEntries[2]['resource'],
                'sortValue' => 'c',
            ], [
                'resource' => $reflectionEntries[3]['resource'],
                'sortValue' => 'b',
            ], [
                'resource' => $reflectionEntries[1]['resource'],
                'sortValue' => 'd',
            ],
        ], 'entries', $iterator);
    }
}
