# Usage Scenarios

## Authentication scenarios

### OpenLDAP

### ActiveDirectory

## Basic CRUD operations

### Retrieving data from the LDAP

#### Getting an entry by its DN

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$ldap->bind();
$hm = $ldap->getEntry('cn=Hugo Müller,ou=People,dc=my,dc=local');
/*
$hm is an array of the following structure
array(
    'dn'          => 'cn=Hugo Müller,ou=People,dc=my,dc=local',
    'cn'          => array('Hugo Müller'),
    'sn'          => array('Müller'),
    'objectclass' => array('inetOrgPerson', 'top'),
    ...
)
*/
```

#### Check for the existence of a given DN

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$ldap->bind();
$isThere = $ldap->exists('cn=Hugo Müller,ou=People,dc=my,dc=local');
```

#### Count children of a given DN

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$ldap->bind();
$childrenCount = $ldap->countChildren(
                            'cn=Hugo Müller,ou=People,dc=my,dc=local');
```

#### Searching the LDAP tree

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$ldap->bind();
$result = $ldap->search('(objectclass=*)',
                        'ou=People,dc=my,dc=local',
                        Zend\Ldap\Ldap::SEARCH_SCOPE_ONE);
foreach ($result as $item) {
    echo $item["dn"] . ': ' . $item['cn'][0] . PHP_EOL;
}
```

### Adding data to the LDAP

#### Add a new entry to the LDAP

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$ldap->bind();
$entry = array();
Zend\Ldap\Attribute::setAttribute($entry, 'cn', 'Hans Meier');
Zend\Ldap\Attribute::setAttribute($entry, 'sn', 'Meier');
Zend\Ldap\Attribute::setAttribute($entry, 'objectClass', 'inetOrgPerson');
$ldap->add('cn=Hans Meier,ou=People,dc=my,dc=local', $entry);
```

### Deleting from the LDAP

#### Delete an existing entry from the LDAP

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$ldap->bind();
$ldap->delete('cn=Hans Meier,ou=People,dc=my,dc=local');
```

### Updating the LDAP

#### Update an existing entry on the LDAP

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$ldap->bind();
$hm = $ldap->getEntry('cn=Hugo Müller,ou=People,dc=my,dc=local');
Zend\Ldap\Attribute::setAttribute($hm, 'mail', 'mueller@my.local');
Zend\Ldap\Attribute::setPassword($hm,
                                 'newPa$$w0rd',
                                 Zend\Ldap\Attribute::PASSWORD_HASH_SHA1);
$ldap->update('cn=Hugo Müller,ou=People,dc=my,dc=local', $hm);
```

## Extended operations

### Copy and move entries in the LDAP

#### Copy a LDAP entry recursively with all its descendants

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$ldap->bind();
$ldap->copy('cn=Hugo Müller,ou=People,dc=my,dc=local',
            'cn=Hans Meier,ou=People,dc=my,dc=local',
            true);
```

#### Move a LDAP entry recursively with all its descendants to a different subtree

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$ldap->bind();
$ldap->moveToSubtree('cn=Hugo Müller,ou=People,dc=my,dc=local',
                     'ou=Dismissed,dc=my,dc=local',
                     true);
```
