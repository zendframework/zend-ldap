# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.8.0 - 2017-03-06

### Added

- [#53](https://github.com/zendframework/zend-ldap/pull/53) Adds addAttribute-method
to Ldap-class
- [#57](https://github.com/zendframework/zend-ldap/pull/57) adds support for new
coding-standards.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.7.1 - 2016-05-23

### Added

- [#48](https://github.com/zendframework/zend-ldap/pull/48) adds and publishes
  the documentation to https://zendframework.github.io/zend-ldap/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#47](https://github.com/zendframework/zend-ldap/pull/47) Fixes a BC-Break caused
  by the missing default-ErrorHandler

## 2.7.0 - 2016-04-21

### Added

- [#43](https://github.com/zendframework/zend-ldap/pull/43) Adds possibility
  to use [Zend\StdLib](https://github.com/zendframework/zend-stdlib) and
  [Zend\EventManager](https://github.com/zendframework/zend-eventmanager) in
  Version 3
- Support for PHP7

### Deprecated

- Nothing.

### Removed

- [#21](https://github.com/zendframework/zend-ldap/pull/21) Removes dependency
  Zend\StdLib

### Fixed

- [#17](https://github.com/zendframework/zend-ldap/issues/17) Fixes HHVM builds
- [#44](https://github.com/zendframework/zend-ldap/pull/40) Fixes broken builds
  in PHP7 due to faulty sorting-test
- [#40](https://github.com/zendframework/zend-ldap/pull/40) Fixes connection test
  that failed due to different failure messages in PHP5 and 7

## 2.6.1 - 2016-04-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#19](https://github.com/zendframework/zend-ldap/pull/20) checks whether the
  LDAP-connection shall use SSL or not and decides based on that which port to
  use if no port has been set.
- [#25](https://github.com/zendframework/zend-ldap/issues/25) Check for correct
  Headers in the documentation and fix it
- [#27](https://github.com/zendframework/zend-ldap/issues/27) Check for different
  issues in the documentation and fixed it
- [#29](https://github.com/zendframework/zend-ldap/issues/29) Check for incorrect
  Blockquotes in the documentation and fix it


## 2.6.0 - 2016-02-11

### Added

- [#6](https://github.com/zendframework/zend-ldap/pull/6) Adds a possibility 
  to delete attributes without having to remove the complete node and add it
  again without the attribute

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#16](https://github.com/zendframework/zend-ldap/pull/16) Fixed the usage of
  ```ldap_sort``` during sorting search-results due to deprecation of 
  ```ldap_sort``` in PHP 7

## 2.5.2 - 2016-02-11

### Added

- [#16](https://github.com/zendframework/zend-ldap/pull/16) removes the call
  to the now deprecated ldap_sort-function wile still preserving the
  sort-functionality.
- [#14](https://github.com/zendframework/zend-ldap/pull/14) adds a Vagrant
  environment for running an LDAP server against which to run the tests;
  additionally, it adds Travis-CI scripts for setting up an LDAP server with
  test data.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#18](https://github.com/zendframework/zend-ldap/pull/18) Fixes an already
  removed second parameter to ```ldap_first_attribute``` and ```ldap_next_attribute```
