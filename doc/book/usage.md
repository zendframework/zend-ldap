# Usage Scenarios

## Authentication scenarios

### OpenLDAP

- TODO

### ActiveDirectory

- TODO

## Basic CRUD operations

### Retrieving data from the LDAP

#### Getting an entry by its DN

```php
use Zend\Ldap\Ldap;

$options = [/* ... */];
$ldap = new Ldap($options);
$ldap->bind();
$hm = $ldap->getEntry('cn=Hugo Müller,ou=People,dc=my,dc=local');

/*
$hm is an array of the following structure:
[
    'dn'          => 'cn=Hugo Müller,ou=People,dc=my,dc=local',
    'cn'          => ['Hugo Müller'],
    'sn'          => ['Müller'],
    'objectclass' => ['inetOrgPerson', 'top'],
    ...
]
*/
```

#### Check for the existence of a given DN

```php
use Zend\Ldap\Ldap;

$options = [/* ... */];
$ldap = new Ldap($options);
$ldap->bind();
$isThere = $ldap->exists('cn=Hugo Müller,ou=People,dc=my,dc=local');
```

#### Count children of a given DN

```php
use Zend\Ldap\Ldap;

$options = [/* ... */];
$ldap = new Ldap($options);
$ldap->bind();
$childrenCount = $ldap->countChildren('cn=Hugo Müller,ou=People,dc=my,dc=local');
```

#### Searching the LDAP tree

```php
use Zend\Ldap\Ldap;

$options = [/* ... */];
$ldap = new Ldap($options);
$ldap->bind();
$result = $ldap->search(
    '(objectclass=*)',
    'ou=People,dc=my,dc=local',
    Ldap::SEARCH_SCOPE_ONE
);

foreach ($result as $item) {
    echo $item["dn"] . ': ' . $item['cn'][0] . PHP_EOL;
}
```

### Adding data to the LDAP

#### Add a new entry to the LDAP

```php
use Zend\Ldap\Attribute;
use Zend\Ldap\Ldap;

$options = [/* ... */];
$ldap = new Ldap($options);
$ldap->bind();

$entry = [];
Attribute::setAttribute($entry, 'cn', 'Hans Meier');
Attribute::setAttribute($entry, 'sn', 'Meier');
Attribute::setAttribute($entry, 'objectClass', 'inetOrgPerson');

$ldap->add('cn=Hans Meier,ou=People,dc=my,dc=local', $entry);
```

### Deleting from the LDAP

#### Delete an existing entry from the LDAP

```php
use Zend\Ldap\Ldap;

$options = [/* ... */];
$ldap = new Ldap($options);
$ldap->bind();
$ldap->delete('cn=Hans Meier,ou=People,dc=my,dc=local');
```

### Updating the LDAP

#### Update an existing entry on the LDAP

```php
use Zend\Ldap\Attribute;
use Zend\Ldap\Ldap;

$options = [/* ... */];
$ldap = new Ldap($options);
$ldap->bind();

$hm = $ldap->getEntry('cn=Hugo Müller,ou=People,dc=my,dc=local');
Attribute::setAttribute($hm, 'mail', 'mueller@my.local');
Attribute::setPassword($hm, 'newPa$$w0rd', Attribute::PASSWORD_HASH_SHA1);

$ldap->update('cn=Hugo Müller,ou=People,dc=my,dc=local', $hm);
```

## Extended operations

### Copy and move entries in the LDAP

#### Copy a LDAP entry recursively with all its descendants

```php
use Zend\Ldap\Ldap;

$options = [/* ... */];
$ldap = new Ldap($options);
$ldap->bind();
$ldap->copy(
    'cn=Hugo Müller,ou=People,dc=my,dc=local',
    'cn=Hans Meier,ou=People,dc=my,dc=local',
    true
);
```

#### Move a LDAP entry recursively with all its descendants to a different subtree

```php
use Zend\Ldap\Ldap;

$options = [/* ... */];
$ldap = new Ldap($options);
$ldap->bind();
$ldap->moveToSubtree(
    'cn=Hugo Müller,ou=People,dc=my,dc=local',
    'ou=Dismissed,dc=my,dc=local',
    true
);
```
