# Object-oriented access to the LDAP tree using Zend\\Ldap\\Node

## Basic CRUD operations

### Retrieving data from the LDAP

- TODO

### Getting a node by its DN

- TODO

### Searching a node's subtree

- TODO

### Adding a new node to the LDAP

- TODO

### Deleting a node from the LDAP

- TODO

### Updating a node on the LDAP

- TODO

## Extended operations

### Copy and move nodes in the LDAP

- TODO

## Tree traversal

### Traverse LDAP tree recursively

```php
use RecursiveIteratorIterator;
use Zend\Ldap\Ldap;

$options = [/* ... */];
$ldap = new Ldap($options);
$ldap->bind();
$ri = new RecursiveIteratorIterator(
    $ldap->getBaseNode(),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($ri as $rdn => $n) {
    var_dump($n);
}
```
