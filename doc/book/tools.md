# Tools

## Creation and modification of DN strings

## Using the filter API to create search filters

### Create simple LDAP filters

```php
use Zend\Ldap\Filter;

$f1  = Filter::equals('name', 'value');         // (name=value)
$f2  = Filter::begins('name', 'value');         // (name=value*)
$f3  = Filter::ends('name', 'value');           // (name=*value)
$f4  = Filter::contains('name', 'value');       // (name=*value*)
$f5  = Filter::greater('name', 'value');        // (name>value)
$f6  = Filter::greaterOrEqual('name', 'value'); // (name>=value)
$f7  = Filter::less('name', 'value');           // (name<value)
$f8  = Filter::lessOrEqual('name', 'value');    // (name<=value)
$f9  = Filter::approx('name', 'value');         // (name~=value)
$f10 = Filter::any('name');                     // (name=*)
```

### Create more complex LDAP filters

```php
use Zend\Ldap\Filter;

$f1 = Filter::ends('name', 'value')->negate(); // (!(name=*value))

$f2 = Filter::equals('name', 'value');
$f3 = Filter::begins('name', 'value');
$f4 = Filter::ends('name', 'value');

// (&(name=value)(name=value*)(name=*value))
$f5 = Filter::andFilter($f2, $f3, $f4);

// (|(name=value)(name=value*)(name=*value))
$f6 = Filter::orFilter($f2, $f3, $f4);
```

## Modify LDAP entries using the Attribute API

- TODO
