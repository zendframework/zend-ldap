<?php

namespace ZendTest\Ldap\TestAsset;

class CustomNaming
{
    public static function name1($attrib)
    {
        return strtolower(strrev($attrib));
    }

    public function name2($attrib)
    {
        return strrev($attrib);
    }
}
