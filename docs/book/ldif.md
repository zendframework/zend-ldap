# Serializing LDAP data to and from LDIF

## Serialize a LDAP entry to LDIF

```php
use Zend\Ldap\Ldif\Encoder;

$data = [
    'dn'                         => 'uid=rogasawara,ou=営業部,o=Airius',
    'objectclass'                => [
        'top',
        'person',
        'organizationalPerson',
        'inetOrgPerson',
    ],
    'uid'                        => ['rogasawara'],
    'mail'                       => ['rogasawara@airius.co.jp'],
    'givenname;lang-ja'          => ['ロドニー'],
    'sn;lang-ja'                 => ['小笠原'],
    'cn;lang-ja'                 => ['小笠原 ロドニー'],
    'title;lang-ja'              => ['営業部 部長'],
    'preferredlanguage'          => ['ja'],
    'givenname'                  => ['ロドニー'],
    'sn'                         => ['小笠原'],
    'cn'                         => ['小笠原 ロドニー'],
    'title'                      => ['営業部 部長'],
    'givenname;lang-ja;phonetic' => ['ろどにー'],
    'sn;lang-ja;phonetic'        => ['おがさわら'],
    'cn;lang-ja;phonetic'        => ['おがさわら ろどにー'],
    'title;lang-ja;phonetic'     => ['えいぎょうぶ ぶちょう'],
    'givenname;lang-en'          => ['Rodney'],
    'sn;lang-en'                 => ['Ogasawara'],
    'cn;lang-en'                 => ['Rodney Ogasawara'],
    'title;lang-en'              => ['Sales, Director'],
];

$ldif = Encoder::encode($data, ['sort' => false, 'version' => null]);

/*
$ldif contains:
dn:: dWlkPXJvZ2FzYXdhcmEsb3U95Za25qWt6YOoLG89QWlyaXVz
objectclass: top
objectclass: person
objectclass: organizationalPerson
objectclass: inetOrgPerson
uid: rogasawara
mail: rogasawara@airius.co.jp
givenname;lang-ja:: 44Ot44OJ44OL44O8
sn;lang-ja:: 5bCP56yg5Y6f
cn;lang-ja:: 5bCP56yg5Y6fIOODreODieODi+ODvA==
title;lang-ja:: 5Za25qWt6YOoIOmDqOmVtw==
preferredlanguage: ja
givenname:: 44Ot44OJ44OL44O8
sn:: 5bCP56yg5Y6f
cn:: 5bCP56yg5Y6fIOODreODieODi+ODvA==
title:: 5Za25qWt6YOoIOmDqOmVtw==
givenname;lang-ja;phonetic:: 44KN44Gp44Gr44O8
sn;lang-ja;phonetic:: 44GK44GM44GV44KP44KJ
cn;lang-ja;phonetic:: 44GK44GM44GV44KP44KJIOOCjeOBqeOBq+ODvA==
title;lang-ja;phonetic:: 44GI44GE44GO44KH44GG44G2IOOBtuOBoeOCh+OBhg==
givenname;lang-en: Rodney
sn;lang-en: Ogasawara
cn;lang-en: Rodney Ogasawara
title;lang-en: Sales, Director
*/
```

## Deserialize a LDIF string into a LDAP entry

```php
use Zend\Ldap\Ldif\Encoder;

$ldif = "dn:: dWlkPXJvZ2FzYXdhcmEsb3U95Za25qWt6YOoLG89QWlyaXVz
objectclass: top
objectclass: person
objectclass: organizationalPerson
objectclass: inetOrgPerson
uid: rogasawara
mail: rogasawara@airius.co.jp
givenname;lang-ja:: 44Ot44OJ44OL44O8
sn;lang-ja:: 5bCP56yg5Y6f
cn;lang-ja:: 5bCP56yg5Y6fIOODreODieODi+ODvA==
title;lang-ja:: 5Za25qWt6YOoIOmDqOmVtw==
preferredlanguage: ja
givenname:: 44Ot44OJ44OL44O8
sn:: 5bCP56yg5Y6f
cn:: 5bCP56yg5Y6fIOODreODieODi+ODvA==
title:: 5Za25qWt6YOoIOmDqOmVtw==
givenname;lang-ja;phonetic:: 44KN44Gp44Gr44O8
sn;lang-ja;phonetic:: 44GK44GM44GV44KP44KJ
cn;lang-ja;phonetic:: 44GK44GM44GV44KP44KJIOOCjeOBqeOBq+ODvA==
title;lang-ja;phonetic:: 44GI44GE44GO44KH44GG44G2IOOBtuOBoeOCh+OBhg==
givenname;lang-en: Rodney
sn;lang-en: Ogasawara
cn;lang-en: Rodney Ogasawara
title;lang-en: Sales, Director";

$data = Encoder::decode($ldif);

/*
$data = [
    'dn'                         => 'uid=rogasawara,ou=営業部,o=Airius',
    'objectclass'                => [
        'top',
        'person',
        'organizationalPerson',
        'inetOrgPerson',
    ],
    'uid'                        => ['rogasawara'],
    'mail'                       => ['rogasawara@airius.co.jp'],
    'givenname;lang-ja'          => ['ロドニー'],
    'sn;lang-ja'                 => ['小笠原'],
    'cn;lang-ja'                 => ['小笠原 ロドニー'],
    'title;lang-ja'              => ['営業部 部長'],
    'preferredlanguage'          => ['ja'],
    'givenname'                  => ['ロドニー'],
    'sn'                         => ['小笠原'],
    'cn'                         => ['小笠原 ロドニー'],
    'title'                      => ['営業部 部長'],
    'givenname;lang-ja;phonetic' => ['ろどにー'],
    'sn;lang-ja;phonetic'        => ['おがさわら'],
    'cn;lang-ja;phonetic'        => ['おがさわら ろどにー'],
    'title;lang-ja;phonetic'     => ['えいぎょうぶ ぶちょう'],
    'givenname;lang-en'          => ['Rodney'],
    'sn;lang-en'                 => ['Ogasawara'],
    'cn;lang-en'                 => ['Rodney Ogasawara'],
    'title;lang-en'              => ['Sales, Director'],
];
*/
```
