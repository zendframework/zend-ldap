<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Collection;

use Countable;
use Iterator;
use Zend\Ldap;
use Zend\Ldap\Exception;
use Zend\Ldap\ErrorHandler;

/**
 * Zend\Ldap\Collection\DefaultIterator is the default collection iterator implementation
 * using ext/ldap
 */
class DefaultIterator implements Iterator, Countable
{
    const ATTRIBUTE_TO_LOWER = 1;
    const ATTRIBUTE_TO_UPPER = 2;
    const ATTRIBUTE_NATIVE   = 3;

    /**
     * LDAP Connection
     *
     * @var \Zend\Ldap\Ldap
     */
    protected $ldap = null;

    /**
     * Result identifier resource
     *
     * @var resource
     */
    protected $resultId = null;

    /**
     * Current result entry identifier
     *
     * @var resource
     */
    protected $current = null;

    /**
     * Number of items in query result
     *
     * @var int
     */
    protected $itemCount = -1;

    /**
     * The method that will be applied to the attribute's names.
     *
     * @var  integer|callable
     */
    protected $attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;

    /**
     * This array holds a list of resources and sorting-values.
     *
     * Each result is represented by an array containing the keys <var>resource</var>
     * which holds a resource of a result-item and the key <var>sortValue</var>
     * which holds the value by which the array will be sorted.
     *
     * The resources will be filled on creating the instance and the sorting values
     * on sorting.
     *
     * @var array
     */
    protected $entries = [];

    /**
     * The function to sort the entries by
     *
     * @var callable
     */
    protected $sortFunction;

    /**
     * Constructor.
     *
     * @param  \Zend\Ldap\Ldap $ldap
     * @param  resource        $resultId
     * @throws \Zend\Ldap\Exception\LdapException if no entries was found.
     * @return DefaultIterator
     */
    public function __construct(Ldap\Ldap $ldap, $resultId)
    {
        $this->setSortFunction('strnatcasecmp');
        $this->ldap      = $ldap;
        $this->resultId  = $resultId;

        $resource = $ldap->getResource();
        ErrorHandler::start();
        $this->itemCount = ldap_count_entries($resource, $resultId);
        ErrorHandler::stop();
        if ($this->itemCount === false) {
            throw new Exception\LdapException($this->ldap, 'counting entries');
        }

        $identifier = ldap_first_entry(
            $ldap->getResource(),
            $resultId
        );

        while (false !== $identifier) {
            $this->entries[] = [
                'resource' => $identifier,
                'sortValue' => '',
            ];

            $identifier = ldap_next_entry(
                $ldap->getResource(),
                $identifier
            );
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the current result set
     *
     * @return bool
     */
    public function close()
    {
        $isClosed = false;
        if (is_resource($this->resultId)) {
            ErrorHandler::start();
            $isClosed       = ldap_free_result($this->resultId);
            ErrorHandler::stop();

            $this->resultId = null;
            $this->current  = null;
        }
        return $isClosed;
    }

    /**
     * Gets the current LDAP connection.
     *
     * @return \Zend\Ldap\Ldap
     */
    public function getLDAP()
    {
        return $this->ldap;
    }

    /**
     * Sets the attribute name treatment.
     *
     * Can either be one of the following constants
     * - Zend\Ldap\Collection\DefaultIterator::ATTRIBUTE_TO_LOWER
     * - Zend\Ldap\Collection\DefaultIterator::ATTRIBUTE_TO_UPPER
     * - Zend\Ldap\Collection\DefaultIterator::ATTRIBUTE_NATIVE
     * or a valid callback accepting the attribute's name as it's only
     * argument and returning the new attribute's name.
     *
     * @param  int|callable $attributeNameTreatment
     * @return DefaultIterator Provides a fluent interface
     */
    public function setAttributeNameTreatment($attributeNameTreatment)
    {
        if (is_callable($attributeNameTreatment)) {
            if (is_string($attributeNameTreatment) && ! function_exists($attributeNameTreatment)) {
                $this->attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
            } elseif (is_array($attributeNameTreatment)
                && ! method_exists($attributeNameTreatment[0], $attributeNameTreatment[1])
            ) {
                $this->attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
            } else {
                $this->attributeNameTreatment = $attributeNameTreatment;
            }
        } else {
            $attributeNameTreatment = (int) $attributeNameTreatment;
            switch ($attributeNameTreatment) {
                case self::ATTRIBUTE_TO_LOWER:
                case self::ATTRIBUTE_TO_UPPER:
                case self::ATTRIBUTE_NATIVE:
                    $this->attributeNameTreatment = $attributeNameTreatment;
                    break;
                default:
                    $this->attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
                    break;
            }
        }

        return $this;
    }

    /**
     * Returns the currently set attribute name treatment
     *
     * @return int|callable
     */
    public function getAttributeNameTreatment()
    {
        return $this->attributeNameTreatment;
    }

    /**
     * Returns the number of items in current result
     * Implements Countable
     *
     * @return int
     */
    public function count()
    {
        return $this->itemCount;
    }

    /**
     * Return the current result item
     * Implements Iterator
     *
     * @return array|null
     * @throws \Zend\Ldap\Exception\LdapException
     */
    public function current()
    {
        if (! is_resource($this->current)) {
            $this->rewind();
        }
        if (! is_resource($this->current)) {
            return;
        }

        $entry         = ['dn' => $this->key()];

        $resource = $this->ldap->getResource();
        ErrorHandler::start();
        $name = ldap_first_attribute($resource, $this->current);
        ErrorHandler::stop();

        while ($name) {
            ErrorHandler::start();
            $data = ldap_get_values_len($resource, $this->current, $name);
            ErrorHandler::stop();

            if (! $data) {
                $data = [];
            }

            if (isset($data['count'])) {
                unset($data['count']);
            }

            switch ($this->attributeNameTreatment) {
                case self::ATTRIBUTE_TO_LOWER:
                    $attrName = strtolower($name);
                    break;
                case self::ATTRIBUTE_TO_UPPER:
                    $attrName = strtoupper($name);
                    break;
                case self::ATTRIBUTE_NATIVE:
                    $attrName = $name;
                    break;
                default:
                    $attrName = call_user_func($this->attributeNameTreatment, $name);
                    break;
            }
            $entry[$attrName] = $data;

            ErrorHandler::start();
            $name = ldap_next_attribute($resource, $this->current);
            ErrorHandler::stop();
        }
        ksort($entry, SORT_LOCALE_STRING);
        return $entry;
    }

    /**
     * Return the result item key
     * Implements Iterator
     *
     * @throws \Zend\Ldap\Exception\LdapException
     * @return string|null
     */
    public function key()
    {
        if (! is_resource($this->current)) {
            $this->rewind();
        }
        if (is_resource($this->current)) {
            $resource = $this->ldap->getResource();
            ErrorHandler::start();
            $currentDn = ldap_get_dn($resource, $this->current);
            ErrorHandler::stop();

            if ($currentDn === false) {
                throw new Exception\LdapException($this->ldap, 'getting dn');
            }

            return $currentDn;
        } else {
            return;
        }
    }

    /**
     * Move forward to next result item
     *
     * @see Iterator
     *
     * @return void
     */
    public function next()
    {
        next($this->entries);
        $nextEntry = current($this->entries);
        $this->current = $nextEntry['resource'];
    }

    /**
     * Rewind the Iterator to the first result item
     *
     * @see Iterator
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->entries);
        $nextEntry = current($this->entries);
        $this->current = $nextEntry['resource'];
    }

    /**
     * Check if there is a current result item
     * after calls to rewind() or next()
     * Implements Iterator
     *
     * @return bool
     */
    public function valid()
    {
        return (is_resource($this->current));
    }

    /**
     * Set a sorting-algorithm for this iterator
     *
     * The callable has to accept two parameters that will be compared.
     *
     * @param callable $sortingAlgorithm The algorithm to be used for sorting
     *
     * @return DefaultIterator Provides a fluent interface
     */
    public function setSortFunction(callable $sortFunction)
    {
        $this->sortFunction = $sortFunction;

        return $this;
    }

    /**
     * Sort the iterator
     *
     * Sorting is done using the set sortFunction which is by default strnatcasecmp.
     *
     * The attribute is determined by lowercasing everything.
     *
     * The sort-value will be the first value of the attribute.
     *
     * @param string $sortAttribute The attribute to sort by. If not given the
     *                              value set via setSortAttribute is used.
     *
     * @return void
     */
    public function sort($sortAttribute)
    {
        foreach ($this->entries as $key => $entry) {
            $attributes = ldap_get_attributes(
                $this->ldap->getResource(),
                $entry['resource']
            );

            $attributes = array_change_key_case($attributes, CASE_LOWER);

            if (isset($attributes[$sortAttribute][0])) {
                $this->entries[$key]['sortValue'] =
                    $attributes[$sortAttribute][0];
            }
        }

        $sortFunction = $this->sortFunction;
        $sorted = usort($this->entries, function ($a, $b) use ($sortFunction) {
            return $sortFunction($a['sortValue'], $b['sortValue']);
        });

        if (! $sorted) {
            throw new Exception\LdapException($this, 'sorting result-set');
        }
    }
}
