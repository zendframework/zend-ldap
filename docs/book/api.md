# API overview

## Configuration options

`Zend\Ldap\Ldap` accepts an array of options either supplied to the constructor
or through the `setOptions()` method. The permitted options are as follows:

Name                   | Description
---------------------- | -----------
host                   | The default hostname of the LDAP server if not supplied to `connect()` (also may be used when trying to canonicalize usernames in bind()).
port                   | Default port of the LDAP server if not supplied to `connect()`.
useStartTls            | Whether or not the LDAP client should use TLS (aka SSLv2) encrypted transport. A value of `true` is strongly favored in production environments to prevent passwords from be transmitted in clear text. The default value is `false`, as servers frequently require that a certificate be installed separately after installation. The `useSsl` and `useStartTls` options are mutually exclusive. The `useStartTls` option should be favored over `useSsl`, but not all servers support this newer mechanism.
useSsl                 | Whether or not the LDAP client should use SSL encrypted transport. The `useSsl` and `useStartTls` options are mutually exclusive.
username               | The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.
password               | The default credentials password (used only with username above).
bindRequiresDn         | If `true`, this instructs `Zend\Ldap\Ldap` to retrieve the DN for the account used to bind if the username is not already in DN form. The default value is `false`.
baseDn                 | The default base DN used for searching (e.g., for accounts). This option is required for most account related operations and should indicate the DN under which accounts are located.
accountCanonicalForm   | A small integer indicating the form to which account names should be canonicalized. See the [Account Name Canonicalization section](intro.md#account-name-canonicalization).
accountDomainName      | The FQDN domain for which the target LDAP server is an authority (e.g., `example.com`).
accountDomainNameShort | The ‘short’ domain for which the target LDAP server is an authority. This is usually used to specify the NetBIOS domain name for Windows networks, but may also be used by non-AD servers.
accountFilterFormat    | The LDAP search filter used to search for accounts. This string is a `sprintf()` style expression that must contain one `%s` to accommodate the username. The default value is `(&(objectClass=user)(sAMAccountName=%s))` unless `bindRequiresDn` is set to `true`, in which case the default is `(&(objectClass=posixAccount)(uid=%s))`. Users of custom schemas may need to change this option.
allowEmptyPassword     | Some LDAP servers can be configured to accept an empty string password as an anonymous bind. This behavior is almost always undesirable. For this reason, empty passwords are explicitly disallowed. Set this value to `true` to allow an empty string password to be submitted during the bind.
optReferrals           | If set to `true`, this option indicates to the LDAP client that referrals should be followed. The default value is `false`.
tryUsernameSplit       | If set to `false`, this option indicates that the given username should not be split at the first `@` or `\\` character to separate the username from the domain during the binding-procedure. This allows the user to use usernames that contain an `@` or `\\` character that do not inherit some domain-information, e.g. using email-addresses for binding. The default value is `true`.
networkTimeout         | Number of seconds to wait for LDAP connection before fail. If not set, the default value is the system value.

## API Reference

Class names are relative to the `Zend\Ldap` namespace, unless otherwise noted.

### Zend\\Ldap\\Ldap

`Zend\Ldap\Ldap` is the base interface into a LDAP server. It provides connection and binding
methods as well as methods to operate on the LDAP tree.

Method signature                                                                                | Description
----------------------------------------------------------------------------------------------- | -----------
`__construct(array|Traversable $options = null) : void`                                         | If no options are provided at instantiation, the connection parameters must be passed to the instance using `setOptions()`. The allowed options are specified in the [options section](#configuration-options).
`getResource() : resource`                                                                      | Returns the raw LDAP extension (ext/ldap) resource.
`getLastErrorCode() : int`                                                                      | Returns the LDAP error number of the last LDAP command.
`getLastError(int &$errorCode = null, array &$errorMessages = null) : string`                   | Returns the LDAP error message of the last LDAP command. The optional `$errorCode` parameter is set to the LDAP error number when given. The optional `$errorMessages` array will be filled with the raw error messages when given. The various LDAP error retrieval functions can return different things, so they are all collected if `$errorMessages` is given.
`setOptions(array|Traversable $options) : void`                                                 | Sets the LDAP connection and binding parameters. Allowed options are specified in the [options section](#configuration-options).
`getOptions() : array`                                                                          | Returns the current connection and binding parameters.
`getBaseDn() : string`                                                                          | Returns the base DN this LDAP connection is bound to.
`getCanonicalAccountName(string $acctname, int $form) : string`                                 | Returns the canonical account name of the given account name `$acctname`. `$form` specifies the format into which the account name is canonicalized. See [Account Name Canonicalization](intro.md#account-name-canonicalization) for more details.
`disconnect() : void`                                                                           | Disconnects the instance from the LDAP server.
`connect(string $host, int $port, bool $useSsl, bool $useStartTls, int $networkTimeout) : void` | Connects the instance to the given LDAP server. All parameters are optional and will be taken from the LDAP connection and binding parameters passed to the instance via the constructor or via `setOptions()` if `null`.
`bind(string $username, string $password) : void`                                               | Authenticates `$username` with `$password` on the LDAP server. If both parameters are omitted, the binding will be carried out with the credentials given in the connection and binding parameters. If no credentials are given in the connection and binding parameters, an anonymous bind will be performed. Note that this requires anonymous binds to be allowed on the LDAP server. An empty string, `''`, can be passed as `$password` together with a username if, and only if, `allowEmptyPassword` is set to `true` in the connection and binding parameters.
`search(/* ... */) : Collection`                                                                | Searches the LDAP tree with the given `$filter` and the given search parameters; see below for full details.
`count(string|Filter\AbstractFilter $filter, string|Dn $basedn, int $scope) : int`              | Counts the elements returned by the given search parameters. See [search()](#search) for a detailed description of the method parameters.
`countChildren(string|Dn $dn) : int`                                                            | Counts the direct descendants (children) of the entry identified by the given `$dn`.
`exists(string|Dn $dn) : bool`                                                                  | Checks whether the entry identified by the given `$dn` exists.
`searchEntries(/* ... */) : array`                                                              | Performs a search operation and returns the result as an PHP array. This is essentially the same method as `search()` except for the return type. See [search()](#search) and [searchEntries()](#searchentries) below for more details.
`getEntry(string|Dn $dn, array $attributes, bool $throwOnNotFound) : array`                     | Retrieves the LDAP entry identified by `$dn` with the attributes specified in `$attributes`. if `$attributes` is omitted, all attributes (`[]`) are included in the result. `$throwOnNotFound` is `false` by default, so the method will return `null` if the specified entry cannot be found. If set to `true`, a `Zend\Ldap\Exception\LdapException` will be thrown instead.
`prepareLdapEntryArray(array &$entry) : void`                                                   | Prepare an array for the use in LDAP modification operations. This method does not need to be called by the end-user as it's implicitly called on every data modification method.
`add(string|Dn $dn, array $entry) : void`                                                       | Adds the entry identified by `$dn` with its attributes `$entry` to the LDAP tree. Throws a `Zend\Ldap\Exception\LdapException` if the entry could not be added.
`update(string|Dn $dn, array $entry) : void`                                                    | Updates the entry identified by `$dn` with its attributes `$entry` to the LDAP tree. Throws a `Zend\Ldap\Exception\LdapException` if the entry could not be modified.
`save(string|Dn $dn, array $entry) : void`                                                      | Saves the entry identified by `$dn` with its attributes $entry to the LDAP tree. Throws a `Zend\Ldap\Exception\LdapException` if the entry could not be saved. This method decides by querying the LDAP tree if the entry will be added or updated.
`delete(string|Dn $dn, boolean $recursively) : void`                                            | Deletes the entry identified by `$dn` from the LDAP tree. Throws a `Zend\Ldap\Exception\LdapException` if the entry could not be deleted. `$recursively` is `false` by default. If set to `true` the deletion will be carried out recursively and will effectively delete a complete subtree. Deletion will fail if $recursively is `false` and the entry `$dn` is not a leaf entry.
`moveToSubtree(string|Dn $from, string|Dn $to, bool $recursively, bool $alwaysEmulate) : void`  | Moves the entry identified by `$from` to a location below `$to` keeping its RDN unchanged. `$recursively` specifies if the operation will be carried out recursively (`false` by default) so that the entry `$from` and all its descendants will be moved. Moving will fail if `$recursively` is `false` and the entry `$from` is not a leaf entry. `$alwaysEmulate` controls whether the ext/ldap function `ldap_rename()` should be used if available. This can only work for leaf entries and for servers and for ext/ldap supporting this function. Set to `true` to always use an emulated rename operation. All move-operations are carried out by copying and then deleting the corresponding entries in the LDAP tree. These operations are not atomic so that failures during the operation will result in an inconsistent state on the LDAP server. The same is true for all recursive operations. They also are by no means atomic. Please keep this in mind.
`move(string|Dn $from, string|Dn $to, bool $recursively, bool $alwaysEmulate) : void`           | This is an alias for `rename()`.
`rename(string|Dn $from, string|Dn $to, bool $recursively, bool $alwaysEmulate) : void`         | Renames the entry identified by `$from` to `$to`. `$recursively` specifies if the operation will be carried out recursively (`false` by default) so that the entry `$from` and all its descendants will be moved. Moving will fail if `$recursively` is `false` and the entry `$from` is not a leaf entry. `$alwaysEmulate` controls whether the ext/ldap function `ldap_rename()` should be used if available. This can only work for leaf entries and for servers and for ext/ldap supporting this function. Set to `TRUE` to always use an emulated rename operation.
`copyToSubtree(string|Dn $from, string|Dn $to, bool $recursively) : void`                       | Copies the entry identified by `$from` to a location below `$to` keeping its RDN unchanged. `$recursively` specifies if the operation will be carried out recursively (`false` by default) so that the entry `$from` and all its descendants will be copied. Copying will fail if `$recursively` is `false` and the entry `$from` is not a leaf entry.
`copy(string|Dn $from, string|Dn $to, bool $recursively) : void`                                | Copies the entry identified by `$from` to `$to`. `$recursively` specifies if the operation will be carried out recursively (`false` by default) so that the entry `$from` and all its descendants will be copied. Copying will fail if `$recursively` is `false` and the entry `$from` is not a leaf entry.
`getNode(string|Dn $dn) : Node`                                                                 | Returns the entry `$dn` wrapped in a `Zend\Ldap\Node`.
`getBaseNode() : Node`                                                                          | Returns the entry for the base DN `$baseDn` wrapped in a `Zend\Ldap\Node`.
`getRootDse() : Node\RootDse`                                                                   | Returns the RootDSE for the current server.
`getSchema() : Node\Schema`                                                                     | Returns the LDAP schema for the current server.

#### search()

The `search()` signature is as follows:

```php
search(
    string|Filter\AbstractFilter $filter,
    string|Dn $basedn,
    int $scope,
    array $attributes,
    string $sort,
    string $collectionClass,
    int $sizelimit,
    int $timelimit
) : Collection
```

where:

- `$filter`: The filter string to be used in the search, e.g. `(objectClass=posixAccount)`.
- `$basedn`: The search base for the search. If omitted or `null`, the `baseDn`
  from the connection and binding parameters is used.
- `$scope`: The search scope:
    - `Ldap::SEARCH_SCOPE_SUB` searches the complete subtree including the
      `$baseDn` node. This is the default value.
    - `Ldap::SEARCH_SCOPE_ONE` restricts search to one level below `$baseDn`.
    - `Ldap::SEARCH_SCOPE_BASE` restricts search to the `$baseDn` itself; this
      can be used to efficiently retrieve a single entry by its DN.
- `$attributes`: Specifies the attributes contained in the returned entries. To
  include all possible attributes (ACL restrictions can disallow certain
  attribute to be retrieved by a given user), pass either an empty array (`[]`)
  or an array containing a wildcard (`['*']`) to the method. On some LDAP
  servers, you can retrieve special internal attributes by passing `['*', '+']`
  to the method.
- `$sort`: If given, the result collection will be sorted according to the
  attribute `$sort`. Results can only be sorted after one single attribute as
  this parameter uses the ext/ldap function `ldap_sort()`.
- `$collectionClass`: If given, the result will be wrapped in an object of type
  `$collectionClass`. By default, an object of type `Zend\Ldap\Collection` will
  be returned. The custom class must extend `Zend\Ldap\Collection`, and will be
  passed a `Zend\Ldap\Collection\Iterator\Default` on instantiation.
- `$sizelimit`: Enables you to limit the count of entries fetched. Setting this
  to `0` means no limit.
- `$timelimit`: Sets the maximum number of seconds to spend on the search.
  Setting this to `0` means no limit.

#### searchEntries()

```php
searchEntries(
    string|Dn $basedn,
    int $scope,
    array $attributes,
    string $sort,
    bool $reverseSort,
    int $sizelimit,
    int $timelimit
) : array
```

Arguments are essentially the same as for [search()](#search), with two
differences:

- `$reverseSort`: a boolean indicating whether or not the results should be
  returned in reverse sort order.
- `$collectionClass` is not present in this signature.

Unlike `search()`, this method always returns an array of results.

### Zend\\Ldap\\Collection

`Zend\Ldap\Collection` implements `Iterator` to allow for item traversal using
`foreach()` and `Countable` to be able to respond to `count()`. With its
protected `createEntry()` method, it provides an extension point for developers
needing custom result objects.

Method signature                                           | Description
---------------------------------------------------------- | -----------
`__construct(Collection\DefaultIterator $iterator) : void` | The constructor must be provided with a `Zend\Ldap\Collection\DefaultIterator`, which does the real result iteration.
`close() : bool`                                           | Closes the internal iterator. This is also called in the destructor.
`toArray() : array`                                        | Returns all entries as an array.
`getFirst() : array`                                       | Returns the first entry in the collection or `null` if the collection is empty.

### Zend\\Ldap\\Attribute

`Zend\Ldap\Attribute` is a helper class providing only static methods to
manipulate arrays suitable to the structure used in `Zend\Ldap\Ldap` data
modification methods, and to the data format required by the LDAP server. PHP
data types are converted using `Zend\Ldap\Converter\Converter` methods.

Method signature                                                                           | Description
------------------------------------------------------------------------------------------ | -----------
`static setAttribute(array &$data, string $attribName, mixed $value, bool $append) : void` | Sets the attribute `$attribName` in `$data` to the value `$value`. If `$append` is `true` (`false` by default) `$value` will be appended to the attribute. `$value` can be a scalar value or an array of scalar values. Conversion will take place.
`static getAttribute(array $data, string $attribName, int|null $index) : array|mixed`      | Returns the attribute `$attribName` from `$data`. If `$index` is `null` (default), an array will be returned containing all the values for the given attribute. An empty array will be returned if the attribute does not exist in the given array. If an integer index is specified the corresponding value at the given index will be returned. If the index is out of bounds, `null` will be returned. Conversion will take place.
`static attributeHasValue(array &$data, string $attribName, mixed|array $value) : bool`    | Checks if the attribute `$attribName` in `$data` has the value(s) given in `$value`. The method returns `true` only if all values in `$value` are present in the attribute. Comparison is done strictly (respecting the data type).
`static removeDuplicatesFromAttribute(array &$data, string $attribName) : void`            | Removes all duplicates from the attribute `$attribName` in `$data`.
`static removeFromAttribute(array &$data, string $attribName, mixed|array $value) : void`  | Removes the value(s) given in `$value` from the attribute `$attribName` in `$data`.
`static setPassword(/* ... */) : void`                                                     | See [setPassword](#setpassword) below for details.
`static createPassword(string $password, string $hashType) : string`                       | Creates an LDAP password. The password hash can be specified with `$hashType`. The default value here is `Attribute::PASSWORD_HASH_MD5` with `Attribute::PASSWORD_HASH_SHA` as the other possibility.
static setDateTimeAttribute(/* ... */) : void                                              | See [setDateTimeAttribute()](#setdatetimeattribute) below for details.
static getDateTimeAttribute(/* ... */) : array|int                                         | See [getDateTimeAttribute()](#getdatetimeattribute) below for details.

#### setPassword()

The full signature of `setPassword()` is as follows:

```php
static setPassword(
    array &$data,
    string $password,
    string $hashType,
    string $attribName
) : void
```

Sets an LDAP password for the attribute `$attribName` in `$data`. `$attribName`
defaults to `userPassword` which is the standard password attribute. The
password hash can be specified with `$hashType`. The default value here is
`Attribute::PASSWORD_HASH_MD5` with `Attribute::PASSWORD_HASH_SHA` as the other
possibility.

#### setDateTimeAttribute()

The full signature of `setDateTimeAttribute()` is as follows:

```php
static setDateTimeAttribute(
    array &$data,
    string $attribName,
    int|array $value,
    boolean $utc,
    boolean $append
) : void
```

Sets the attribute `$attribName` in `$data` to the date/time value `$value`. if
`$append` is `true` (`false` by default) `$value` will be appended to the
attribute. `$value` can be an integer value or an array of integers.
Date-time-conversion according to `Converter\Converter::toLdapDateTime()` will
take place.

#### getDateTimeAttribute()

The full signature of `getDateTimeAttribute()` is as follows:

```php
static getDateTimeAttribute(
    array $data,
    string $attribName,
    int|null $index
) : array|int
```

Returns the date/time attribute `$attribName` from `$data`. If `$index` is
`null` (default), an array will be returned containing all the date/time values
for the given attribute. An empty array will be returned if the attribute does
not exist in the given array. If an integer index is specified the corresponding
date/time value at the given index will be returned. If the index is out of
bounds, `null` will be returned. Date-time-conversion according to
`Converter\Converter::fromLdapDateTime()` will take place.

### Zend\\Ldap\\Converter\\Converter

`Zend\Ldap\Converter\Converter` is a helper class providing only static methods
to manipulate arrays suitable to the data format required by the LDAP server.
PHP data types are converted the following way:

- `string`: No conversion will be done.
- `integer and float`: The value will be converted to a string.
- `boolean`: `true` will be converted to `'TRUE'` and `false` to `'FALSE'`.
- `object and array`: The value will be converted to a string by using `serialize()`.
- `Date/Time`: The value will be converted to a string with the following
  `date()` format `YmdHisO`, UTC timezone (`+0000`) will be replaced with a `Z`.
  For example `01-30-2011 01:17:32 PM GMT-6` will be `20113001131732-0600` and
  `30-01-2012 15:17:32 UTC` will be `20120130151732Z`.
- `resource`: If a stream resource is given, the data will be fetched by calling `stream_get_contents()`.
- Others: All other data types (namely non-stream resources) will be omitted.

On reading values, the following conversion will take place:

- `'TRUE'`: Converted to `true`.
- `'FALSE'`: Converted to `false`.
- Others: All other strings won't be automatically converted and are passed as they are.

Method signature                                                              | Description
----------------------------------------------------------------------------- | -----------
`static ascToHex32(string $string) : string`                                  | Convert all Ascii characters with decimal value less than 32 to hexadecimal value.
`static hex32ToAsc(string $string) : string`                                  | Convert all hexadecimal characters to Ascii values.
`static toLdap(mixed $value, int $type) : string|null`                        | Converts a PHP data type into its LDAP representation. `$type` argument is used to set the conversion method. The default, `Converter::STANDARD`, allows the function to try to guess the conversion method to use. Others possibilities are `Converter::BOOLEAN` and `Converter::GENERALIZED_TIME`. See the introduction for details.
`static fromLdap(string $value, int $type, bool $dateTimeAsUtc) : mixed`      | Converts an LDAP value into its PHP data type. See introduction and `toLdap()` and `toLdapDateTime()` for details.
`static toLdapDateTime(int|string|DateTime $date, bool $asUtc) : string|null` | Converts a timestamp, a `DateTime` instance, or a string that is parseable by `strtotime()` into its LDAP date/time representation. If `$asUtc` is `true` (`false` by default), the resulting LDAP date/time string will be in UTC; otherwise a local date/time string will be returned.
`static fromLdapDateTime(string $date, boolean $asUtc) : DateTime`            | Converts LDAP date/time representation into a PHP `DateTime` object.
`static toLdapBoolean(bool|int|string $value) : string`                       | Converts a PHP data type into its LDAP boolean representation. By default, always return `false` except if the value is `true`, `'true'`, or `1`.
`static fromLdapBoolean(string $value) : bool`                                | Converts LDAP boolean representation into a PHP boolean data type.
`static toLdapSerialize(mixed $value) : string`                               | The value will be converted to a string by using `serialize()`.
`static fromLdapUnserialize(string $value) : mixed`                           | The value will be converted from a string by using `unserialize()`.

### Zend\\Ldap\\Dn

`Zend\Ldap\Dn` provides an object-oriented interface to manipulating LDAP
distinguished names (DN). The parameter `$caseFold` that is used in several
methods determines the way DN attributes are handled regarding their case.
Allowed values for this parameter are:

- `Dn::ATTR_CASEFOLD_NONE`: No case-folding will be done.
- `Dn::ATTR_CASEFOLD_UPPER`: All attributes will be converted to upper-case.
- `Dn::ATTR_CASEFOLD_LOWER`: All attributes will be converted to lower-case.

The default case-folding is `Dn::ATTR_CASEFOLD_NONE`; set an alternative with
`Dn::setDefaultCaseFold()`. Each instance of `Zend\Ldap\Dn` can have its own
case-folding setting. If the `$caseFold` parameter is omitted in method-calls it
defaults to the instance's case-folding setting.

The class implements `ArrayAccess` to allow indexer-access to the different
parts of the DN. The `ArrayAccess` methods proxy to `Dn::get($offset, 1, null)`
for `offsetGet(int $offset)`, to `Dn::set($offset, $value)` for `offsetSet()`,
and to `Dn::remove($offset, 1)` for `offsetUnset()`. `offsetExists()` simply
checks if the index is within the bounds.

Method signature                                                                   | Description
---------------------------------------------------------------------------------- | -----------
`static factory(string|array $dn, string|null $caseFold) : Dn`                     | Creates an instance from an array or a string. The array must conform to the array structure detailed under `Dn::implodeDn()`.
`static fromString(string $dn, string|null $caseFold) : Dn`                        | Creates an instance from a string.
`static fromArray(array $dn, string|null $caseFold) : Dn`                          | Creates an instance from an array. The array must conform to the array structure detailed under `Dn::implodeDn()`.
`getRdn(string|null $caseFold) : array`                                            | Gets the RDN of the current DN. The return value is an array with the RDN attribute names its keys and the RDN attribute values.
`getRdnString(string|null $caseFold) : string`                                     | Gets the RDN of the current DN. The return value is a string.
`getParentDn(integer $levelUp) : Dn`                                               | Gets the DN of the current DN’s ancestor `$levelUp` levels up the tree. `$levelUp` defaults to `1`.
`get(int $index, int $length, string|null $caseFold) : array`                      | Returns a slice of the current DN determined by `$index` and `$length`. `$index` starts with `0` on the DN part from the left.
`set(int $index, array $value) : void`                                             | Replaces a DN part in the current DN. This operation manipulates the current instance.
`remove(int $index, int $length) : void`                                           | Removes a DN part from the current DN. This operation manipulates the current instance. $length defaults to 1
`append(array $value) : void`                                                      | Appends a DN part to the current DN. This operation manipulates the current instance.
`prepend(array $value) : void`                                                     | Prepends a DN part to the current DN. This operation manipulates the current instance.
`insert(int $index, array $value) : void`                                          | Inserts a DN part after the index `$index` to the current DN. This operation manipulates the current instance.
`setCaseFold(string|null $caseFold) : void`                                        | Sets the case-folding option to the current DN instance. If `$caseFold` is `null`, the default case-folding setting is used for the current instance.
`toString(string|null $caseFold) : string`                                         | Returns DN as a string.
`toArray(string|null $caseFold) : array`                                           | Returns DN as an array.
`__toString() : string`                                                            | Returns DN as a string; proxies to `Dn::toString(null)`.
`static setDefaultCaseFold(string $caseFold) : void`                               | Sets the default case-folding option used by all instances on creation by default. Already existing instances are not affected by this setting.
`escapeValue(string|array $values) : array`                                        | Escapes a DN value according to RFC 2253.
`unescapeValue(string|array $values) : array`                                      | Undoes the conversion done by `Dn::escapeValue()`.
`explodeDn(string $dn, array &$keys, array &$vals, string|null $caseFold) : array` | Explodes the DN `$dn` into an array containing all parts of the given DN. `$keys` optionally receive DN keys (e.g. CN, OU, DC, ...). `$vals` optionally receive DN values. The resulting array will be of type `[ ['cn' => 'name1', 'uid' => 'user'], ['cn' => 'name2'), ['dc' => 'example'], ['dc' => 'org'] ]` for a DN of `cn=name1+uid=user,cn=name2,dc=example,dc=org`.
`checkDn(string $dn, array &$keys, array &$vals, string|null $caseFold) : bool`    | Checks if a given DN `$dn` is malformed. If `$keys` or `$keys` and `$vals` are given, these arrays will be filled with the appropriate DN keys and values.
`implodeRdn(array $part, string|null $caseFold) : string`                          | Returns a DN part in the form `$attribute=$value`
`implodeDn(array $dnArray, string|null $caseFold, string $separator) : string`     | Implodes an array in the form delivered by `Dn::explodeDn()` to a DN string.  `$separator` defaults to `,` but some LDAP servers also understand `;`.  `$dnArray` must of type `[ ['cn' => 'name1', 'uid' => 'user'], ['cn' => 'name2'], ['dc' => 'example'], ['dc' => 'org'] ]`
`isChildOf(string|Dn $childDn, string|Dn $parentDn) : bool`                        | Checks if given `$childDn` is beneath `$parentDn` subtree.

### Zend\\Ldap\\Filter

### Zend\\Ldap\\Node

`Zend\Ldap\Node` includes the magic property accessors `__set()`, `__get()`,
`__unset()`, and `__isset()` for accessing the attributes by name. They proxy to
`Node::setAttribute()`, `Node::getAttribute()`, `Node::deleteAttribute()`, and
`Node::existsAttribute()` respectively. Furthermore the class implements
`ArrayAccess` for array-style access to the attributes. `Zend\Ldap\Node` also
implements `Iterator` and `RecursiveIterator` to allow for recursive
tree-traversal.

Method signature                                                | Description
--------------------------------------------------------------- | -----------
`static equals(string $attr, string $value) : Filter`           | Creates an "equals" filter: `(attr=value)`.
`begins(string $attr, string $value) : Filter`                  | Creates a "begins with" filter: `(attr=value*)`.
`ends(string $attr, string $value) : Filter`                    | Creates an "ends with" filter: `(attr=*value)`.
`contains(string $attr, string $value) : Filter`                | Creates a "contains" filter: `(attr=*value*)`.
`greater(string $attr, string $value) : Filter`                 | Creates a "greater than" filter: `(attr>value)`.
`greaterOrEqual(string $attr, string $value) : Filter`          | Creates a "greater than or equal" filter: `(attr>=value)`.
`less(string $attr, string $value) : Filter`                    | Creates a "less than" filter: `(attr<value)`.
`lessOrEqual(string $attr, string $value) : Filter`             | Creates a "less than or equal" filter: `(attr<=value)`.
`approx(string $attr, string $value) : Filter`                  | Creates an "approx" filter: `(attr~=value)`.
`any(string $attr) : Filter`                                    | Creates an "any" filter: `(attr=*)`.
`string(string $filter) : Filter`                               | Creates a simple custom string filter. The user is responsible for all value-escaping as the filter is used as is.
`mask(string $mask, string $value, ...) : Filter`               | Creates a filter from a string mask. All `$value` parameters will be escaped and substituted into `$mask` by using `sprintf()`.
`andFilterFilter\AbstractFilter $filter, ...) : Filter`         | Creates an "and" filter from all arguments given.
`orFilter(Filter\AbstractFilter $filter, ...) : Filter`         | Creates an "or" filter from all arguments given.
`__construct(/* ... */) : void`                                 | Create an arbitrary filter according to the parameters supplied; see the [Node constructor](#node-constructor) below for the full signature.
`toString() : string`                                           | Returns a string representation of the filter.
`__toString() : string`                                         | Returns a string representation of the filter. Proxies to `Filter::toString()`.
`negate() : Filter\NotFilter`                                   | Creates and returns a new filter that is a negation of the current filter.
`addAnd(Filter\AbstractFilter $filter, ...) : Filter\AndFilter` | Creates an "and" filter from the current filter and all filters passed in as the arguments.
`addOr(Filter\AbstractFilter $filter, ...) : Filter\OrFilter`   | Creates an "or" filter from the current filter and all filters passed in as the arguments.
`escapeValue(string|array $values) : string|array`              | Escapes the given `$values` according to RFC 2254 so that they can be safely used in LDAP filters. If a single string is given, a string is returned, otherwise an array is returned.  Any control characters with an ASCII code `< 32` as well as characters with special meaning in LDAP filters (`*`, `(`, `)`, and `\\` (the backslash)) are converted into the representation of a backslash followed by two hex digits representing the hexadecimal value of the character.
`unescapeValue(string|array $values) : string|array`            | Undoes the conversion done by `Filter::escapeValue()`. Converts any sequences of a backslash followed by two hex digits into the corresponding character.

#### Node constructor

The full signature of the `Zend\Ldap\Node` constructor is:

```php
__construct(
    string $attr,
    string $value,
    string $filtertype,
    string|null $prepend,
    string|null $append
) : void
```

The resulting filter will be a concatenation of `$attr`, `$filtertype`,
`$prepend`, `$value`, and `$append`. Normally this constructor is not needed, as
all filters can be created by using the appropriate factory methods.

### Zend\\Ldap\\Node\\RootDse

The following methods are available on all vendor-specific subclasses.

`Zend\Ldap\Node\RootDse` includes the magic property accessors `__get()` and
`__isset()` to access the attributes by their name. They proxy to
`Node\RootDse::getAttribute()` and `Node\RootDse::existsAttribute()`
respectively. `__set()` and `__unset()` are also implemented but they throw a
`BadMethodCallException`, as modifications are not allowed on RootDSE nodes.
Furthermore the class implements `ArrayAccess` for array-style access to the
attributes.  `offsetSet()` and `offsetUnset()` also throw a
`BadMethodCallException`.

Method signature                                                  | Description
----------------------------------------------------------------- | -----------
`getDn() : Dn`                                                    | Gets the DN of the current node as a `Zend\Ldap\Dn` instance.
`getDnString(string $caseFold) : string`                          | Gets the DN of the current node as a string.
`getDnArray(string $caseFold) : array`                            | Gets the DN of the current node as an array.
`getRdnString(string $caseFold) : string`                         | Gets the RDN of the current node as a string.
`getRdnArray(string $caseFold) : array`                           | Gets the RDN of the current node as an array.
`getObjectClass() : array`                                        | Returns the `objectClass` of the node.
`toString() : string`                                             | Returns the DN of the current node; proxies to `Zend\Ldap\Dn::getDnString()`.
`__toString() : string`                                           | Casts to string representation; proxies to `Zend\Ldap\Dn::toString()`.
`toArray(bool $includeSystemAttributes) : array`                  | Returns an array representation of the current node. If `$includeSystemAttributes` is `false` (defaults to `true`), the system specific attributes are stripped from the array. Unlike `getAttributes()`, the resulting array contains the DN with key ‘dn’.
`toJson(bool $includeSystemAttributes) : string`                  | Returns a JSON representation of the current node using `toArray()`.
`getData(bool $includeSystemAttributes) : array`                  | Returns the node's attributes. The array contains all attributes in its internal format (no conversion).
`existsAttribute(string $name, bool $emptyExists) : bool`         | Checks whether a given attribute exists. If `$emptyExists` is `false`, empty attributes (containing only `[]`) are treated as non-existent, returning `false`. If `$emptyExists` is `true`, empty attributes are treated as existent, returning `true`. In this case, the method returns `false` only if the attribute name is missing in the key-collection.
`attributeHasValue(string $name, mixed|array $value) : bool`      | Checks if the given value(s) exist in the attribute. The method returns `true` only if all values in `$value` are present in the attribute. Comparison is done strictly (respecting the data type).
`count() : int`                                                   | Returns the number of attributes in the node. Implements `Countable`.
`getAttribute(string $name, int|null $index) : mixed`             | Gets an LDAP attribute. Data conversion is applied using `Attribute::getAttribute()`.
`getAttributes(bool $includeSystemAttributes) : array`            | Gets all attributes of node. If `$includeSystemAttributes` is `false` (defaults to `true`), the system specific attributes are stripped from the array.
`getDateTimeAttribute(string $name, int|null $index) : array|int` | Gets an LDAP date/time attribute. Data conversion is applied using `Attribute::getDateTimeAttribute()`.
`reload(Ldap $ldap) : void`                                       | Reloads the current node's attributes from the given LDAP server.
`static create(Ldap $ldap) : RootDse`                             | Factory method to create the RootDSE.
`getNamingContexts() : array`                                     | Gets the `namingContexts`.
`getSubschemaSubentry() : string|null`                            | Gets the `subschemaSubentry`.
`supportsVersion(string|int|array $versions) : bool`              | Determines if the LDAP version is supported.
`supportsSaslMechanism(string|array $mechlist) : bool`            | Determines if the SASL mechanism is supported.
`getServerType() : int`                                           | Gets the server type. Returns `RootDse::SERVER_TYPE_GENERIC` for unknown LDAP servers, `RootDse::SERVER_TYPE_OPENLDAP` for OpenLDAP servers, `RootDse::SERVER_TYPE_ACTIVEDIRECTORY` for Microsoft ActiveDirectory servers, and `RootDse::SERVER_TYPE_EDIRECTORY` for Novell eDirectory servers.
`getSchemaDn() : Dn`                                              | Returns the schema DN.

#### OpenLDAP

Additionally the common methods above apply to instances of `Zend\Ldap\Node\RootDse\OpenLdap`.

Refer to [LDAP Operational Attributes and Objects](http://www.zytrax.com/books/ldap/ch3/#operational)
specification for information on the attributes of OpenLDAP RootDSE.

Method signature                               | Description
---------------------------------------------- | -----------
`getServerType() : int`                        | Gets the server type. Returns `Zend\Ldap\Node\RootDse::SERVER_TYPE_OPENLDAP`
`getConfigContext() : string|null`             | Gets the `configContext`.
`getMonitorContext() : string|null`            | Gets the `monitorContext`.
`supportsControl(string|array $oids) : bool`   | Determines if the control is supported.
`supportsExtension(string|array $oids) : bool` | Determines if the extension is supported.
`supportsFeature(string|array $oids) : bool`   | Determines if the feature is supported.

#### ActiveDirectory

Additionally the common methods above apply to instances of
`Zend\Ldap\Node\RootDse\ActiveDirectory`.

Refer to the [RootDSE](http://msdn.microsoft.com/en-us/library/ms684291(VS.85).aspx)
specification for information on the attributes of Microsoft ActiveDirectory
RootDSE.

Method signature                                   | Description
-------------------------------------------------- | -----------
`getServerType() : int`                            | Gets the server type. Returns `Zend\Ldap\Node\RootDse::SERVER_TYPE_ACTIVEDIRECTORY`
`getConfigurationNamingContext() : string|null`    | Gets the `configurationNamingContext`.
`getCurrentTime() : string|null`                   | Gets the `currentTime`.
`getDefaultNamingContext() : string|null`          | Gets the `defaultNamingContext`.
`getDnsHostName() : string|null`                   | Gets the `dnsHostName`.
`getDomainControllerFunctionality() : string|null` | Gets the `domainControllerFunctionality`.
`getDomainFunctionality() : string|null`           | Gets the `domainFunctionality`.
`getDsServiceName() : string|null`                 | Gets the `dsServiceName`.
`getForestFunctionality() : string|null`           | Gets the `forestFunctionality`.
`getHighestCommittedUSN() : string|null`           | Gets the `highestCommittedUSN`.
`getIsGlobalCatalogReady() : string|null`          | Gets the `isGlobalCatalogReady`.
`getIsSynchronized() : string|null`                | Gets the `isSynchronized`.
`getLdapServiceName() : string|null`               | Gets the `ldapServiceName`.
`getRootDomainNamingContext() : string|null`       | Gets the `rootDomainNamingContext`.
`getSchemaNamingContext() : string|null`           | Gets the `schemaNamingContext`.
`getServerName() : string|null`                    | Gets the `serverName`.
`supportsCapability(string|array $oids) : bool`    | Determines if the capability is supported.
`supportsControl(string|array $oids) : bool`       | Determines if the control is supported.
`supportsPolicy(string|array $policies) : bool`    | Determines if the version is supported.

#### eDirectory

Additionally the common methods above apply to instances of
`Zend\Ldap\Node\RootDse\eDirectory`.

Refer to [Getting Information about the LDAP Server](http://www.novell.com/documentation/edir88/edir88/index.html?page=/documentation/edir88/edir88/data/ah59jqq.html)
for information on the attributes of Novell eDirectory RootDSE.

Method signature                                     | Description
---------------------------------------------------- | -----------
`getServerType() : int`                              | Gets the server type. Returns `Zend\Ldap\Node\RootDse::SERVER_TYPE_EDIRECTORY`
`supportsExtension(string|array $oids) : bool`       | Determines if the extension is supported.
`getVendorName() : string|null`                      | Gets the `vendorName`.
`getVendorVersion() : string|null`                   | Gets the `vendorVersion`.
`getDsaName() : string|null`                         | Gets the `dsaName`.
`getStatisticsErrors() : string|null`                | Gets the server statistics `errors`.
`getStatisticsSecurityErrors() : string|null`        | Gets the server statistics `securityErrors`.
`getStatisticsChainings() : string|null`             | Gets the server statistics `chainings`.
`getStatisticsReferralsReturned() : string|null`     | Gets the server statistics `referralsReturned`.
`getStatisticsExtendedOps() : string|null`           | Gets the server statistics `extendedOps`.
`getStatisticsAbandonOps() : string|null`            | Gets the server statistics `abandonOps`.
`getStatisticsWholeSubtreeSearchOps() : string|null` | Gets the server statistics `wholeSubtreeSearchOps`.

### Zend\\Ldap\\Node\\Schema

The following methods are available on all vendor-specific subclasses.

`Zend\Ldap\Node\Schema` includes the magic property accessors `__get()` and `__isset()` to access
the attributes by their name. They proxy to `Schema::getAttribute()` and
`Schema::existsAttribute()` respectively. `__set()` and `__unset()` are also
implemented, but they throw a `BadMethodCallException`, as modifications are not allowed on RootDSE
nodes. Furthermore the class implements `ArrayAccess` for array-style access to the attributes.
`offsetSet()` and `offsetUnset()` also throw a `BadMethodCallException`.

Method signature                                                  | Description
----------------------------------------------------------------- | -----------
`getDn() : Dn`                                                    | Gets the DN of the current node as a `Zend\Ldap\Dn` instance.
`getDnString(string $caseFold) : string`                          | Gets the DN of the current node as a string.
`getDnArray(string $caseFold) : array`                            | Gets the DN of the current node as an array.
`getRdnString(string $caseFold) : string`                         | Gets the RDN of the current node as a string.
`getRdnArray(string $caseFold) : array`                           | Gets the RDN of the current node as an array.
`getObjectClass() : array`                                        | Returns the `objectClass` of the node.
`toString() : string`                                             | Returns the DN of the current node; proxies to `Zend\Ldap\Dn::getDnString()`.
`__toString() : string`                                           | Casts to string representation; proxies to `Zend\Ldap\Dn::toString()`.
`toArray(bool $includeSystemAttributes) : array`                  | Returns an array representation of the current node. If `$includeSystemAttributes` is `false` (defaults to `true`), the system specific attributes are stripped from the array. Unlike `Node\Schema::getAttributes()`, the resulting array contains the DN with key `dn`.
`toJson(bool $includeSystemAttributes) : string`                  | Returns a JSON representation of the current node using `Node\Schema::toArray()`.
`getData(bool $includeSystemAttributes) : array`                  | Returns the node’s attributes. The array contains all attributes in its internal format (no conversion).
`existsAttribute(string $name, bool $emptyExists) : bool`         | Checks whether a given attribute exists. If `$emptyExists` is `false`, empty attributes (containing only `[]`) are treated as non-existent, returning `false`. If `$emptyExists` is `true`, empty attributes are treated as existent, returning `true`. In this case, the method returns `false` only if the attribute name is missing in the key-collection.
`attributeHasValue(string $name, mixed|array $value) : bool`      | Checks if the given value(s) exist in the attribute. The method returns `true` only if all values in $value are present in the attribute. Comparison is done strictly (respecting the data type).
`count() : int`                                                   | Returns the number of attributes in the node. Implements `Countable`.
`getAttribute(string $name, int|null $index) : mixed`             | Gets an LDAP attribute.  Data conversion is applied using `Attribute::getAttribute()`.
`getAttributes(bool $includeSystemAttributes) : array`            | Gets all attributes of node. If `$includeSystemAttributes` is `false` (defaults to `true`) the system specific attributes are stripped from the array.
`getDateTimeAttribute(string $name, int|null $index) : array|int` | Gets a LDAP date/time attribute. Data conversion is applied using `Attribute::getDateTimeAttribute()`.
`reload(Ldap $ldap) : void`                                       | Reloads the current node’s attributes from the given LDAP server.
`static create(Ldap $ldap) : Node\Schema`                         | Factory method to create the `Schema` node.
`getAttributeTypes() : array`                                     | Gets the attribute types as an array.
`getObjectClasses() : array`                                      | Gets the object classes as an array of `Zend\Ldap\Node\Schema\ObjectClass\ObjectClassInterface` instances.

#### AttributeTypeInterface

Method signature            | Description
--------------------------- | -----------
`getName() : string`        | Gets the attribute name.
`getOid() : string`         | Gets the attribute OID.
`getSyntax() : string`      | Gets the attribute syntax.
`getMaxLength() : int|null` | Gets the attribute maximum length.
`isSingleValued() : bool`   | Returns if the attribute is single-valued.
`getDescription() : string` | Gets the attribute description

#### ObjectClassInterface

Method signature             | Description
---------------------------- | -----------
`getName() : string`         | Returns the objectClass name.
`getOid() : string`          | Returns the objectClass OID.
`getMustContain() : array`   | Returns the attributes that this objectClass must contain.
`getMayContain() : array`    | Returns the attributes that this objectClass may contain.
`getDescription() : string`  | Returns the attribute description
`getType() : int`            | Returns the `objectClass` type. The method returns one of the following values: `Schema::OBJECTCLASS_TYPE_UNKNOWN` for unknown class types, `Schema::OBJECTCLASS_TYPE_STRUCTURAL` for structural classes, `Schema::OBJECTCLASS_TYPE_ABSTRACT` for abstract classes, `Schema::OBJECTCLASS_TYPE_AUXILIARY` for auxiliary classes.
`getParentClasses() : array` | Returns the parent `objectClass`es of this class. This includes structural, abstract and auxiliary `objectClass`es.

#### AbstractItem

Classes representing attribute types and object classes extend
`Zend\Ldap\Node\Schema\AbstractItem`, which provides some core methods to access
arbitrary attributes on the underlying LDAP node.  `AbstractItem` includes the
magic property accessors `__get()` and `__isset()` to access the attributes by
their name. Furthermore the class implements `ArrayAccess` for
array-style-access to the attributes. `offsetSet()` and `offsetUnset()` throw a
`BadMethodCallException`, as modifications are not allowed on schema information
nodes.

Method signature    | Description
------------------- | -----------
`getData() : array` | Gets all the underlying data from the schema information node.
`count() : int`     | Returns the number of attributes in this schema information node. Implements `Countable`.

#### OpenLDAP

Additionally the common methods above apply to instances of
`Zend\Ldap\Node\Schema\OpenLDAP`.

Method signature               | Description
------------------------------ | -----------
`getLdapSyntaxes() : array`    | Gets the LDAP syntaxes.
`getMatchingRules() : array`   | Gets the matching rules.
`getMatchingRuleUse() : array` | Gets the matching rule use.

`Zend\Ldap\Node\Schema\AttributeType\OpenLDAP` has the following API:

Method signature                                        | Description
------------------------------------------------------- | -----------
`getParent() : Node\Schema\AttributeType\OpenLdap|null` | Returns the parent attribute type in the inheritance tree if one exists.

`Zend\Ldap\Node\Schema\ObjectClass\OpenLDAP` has the following API:

Method signature       | Description
---------------------- | -----------
`getParents() : array` | Returns the parent object classes in the inheritance tree if one exists. The returned array is an array of `Zend\Ldap\Node\Schema\ObjectClass\OpenLdap`.

#### ActiveDirectory

> #### Schema browsing on ActiveDirectory servers
>
> Due to restrictions on Microsoft ActiveDirectory servers regarding the number
> of entries returned by generic search routines, and due to the structure of
> the ActiveDirectory schema repository, schema browsing is currently **not**
> available for Microsoft ActiveDirectory servers.

None of `Zend\Ldap\Node\Schema\ActiveDirectory`,
`Zend\Ldap\Node\Schema\AttributeType\ActiveDirectory`, or
`Zend\Ldap\Node\Schema\\ObjectClass\ActiveDirectory` provide additional
methods.

### Zend\\Ldap\\Ldif\\Encoder

Method signature                                            | Description
----------------------------------------------------------- | -----------
`decode(string $string) : array`                            | Decodes the string `$string` into an array of LDIF items.
`encode(scalar|array|Node $value, array $options) : string` | Encode `$value` into a LDIF representation.

The `$options` argument to `encode()` may contain the following keys:

- `sort`: Sort the given attributes with dn following `objectClass`, and
  following all other attributes sorted alphabetically. `true` by default.
- `version`: The LDIF format version. `1` by default.
- `wrap`: The line-length. `78` by default to conform to the LDIF specification.
