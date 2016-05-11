# Object-oriented access to the LDAP tree using Zend\\Ldap\\Node

## Basic CRUD operations

### Retrieving data from the LDAP

### Getting a node by its DN

### Searching a node's subtree

### Adding a new node to the LDAP

### Deleting a node from the LDAP

### Updating a node on the LDAP

## Extended operations

### Copy and move nodes in the LDAP

## Tree traversal

### Traverse LDAP tree recursively

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$ldap->bind();
$ri = new RecursiveIteratorIterator($ldap->getBaseNode(),
                                    RecursiveIteratorIterator::SELF_FIRST);
foreach ($ri as $rdn => $n) {
    var_dump($n);
}
```
