# Getting information from the LDAP server

## RootDSE

See the following documents for more information on the attributes contained within the RootDSE for
a given *LDAP* server.

- [OpenLDAP](http://www.zytrax.com/books/ldap/ch3/#operational)
- [Microsoft ActiveDirectory](http://msdn.microsoft.com/en-us/library/ms684291(VS.85).aspx)
- [Novell
eDirectory](http://www.novell.com/documentation/edir88/edir88/index.html?page=/documentation/edir88/edir88/data/ah59jqq.html)

### Getting hands on the RootDSE

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$rootdse = $ldap->getRootDse();
$serverType = $rootdse->getServerType();
```

## Schema Browsing

### Getting hands on the server schema

```php
$options = array(/* ... */);
$ldap = new Zend\Ldap\Ldap($options);
$schema = $ldap->getSchema();
$classes = $schema->getObjectClasses();
```

#### OpenLDAP

#### ActiveDirectory

> ### Note
#### Schema browsing on ActiveDirectory servers
Due to restrictions on Microsoft ActiveDirectory servers regarding the number of entries returned by
generic search routines and due to the structure of the ActiveDirectory schema repository, schema
browsing is currently **not** available for Microsoft ActiveDirectory servers.
