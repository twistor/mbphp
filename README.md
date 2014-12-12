mbphp
=====

[![Build Status](https://img.shields.io/travis/twistor/mbphp/master.svg?style=flat-square)](https://travis-ci.org/twistor/mbphp)

An mbstring like implementation in PHP.

Quick usage:
```php
<?php

use MbPhp\Mb;

// Internal encoding is utf-8 by default.
Mb::internalEncoding('ISO-8859-2');

Mb::checkEncoding($string, 'iso-8859-2');

Mb::convertEncoding($string, 'utf-8');

Mb::strlen($string);

Mb::strrpos($string, $needle);

Mb::strpos($string, $needle);

Mb::strtolower($string);

Mb::strtoupper($string);

// All methods support an optional encoding param.
Mb::substrCount($string, $needle, 'utf-8');

Mb::substr($string, 0, 5);

```
Right now I'm focusing on adding/verifying encoders, rather than implementing
more mb_* functions.

Supported encodings:
- gb18030
- gbk
- ibm866
- iso-8859-2 (latin2)
- iso-8859-3 (latin3)
- iso-8859-4 (latin4)
- iso-8859-5
- iso-8859-6
- iso-8859-7 (greek)
- iso-8859-8 (hebrew)
- iso-8859-10 (latin6)
- iso-8859-13
- iso-8859-14
- iso-8859-15
- iso-8859-16
- koi8-r
- koi8-u
- macintosh (x-mac-roman)
- utf-8
- windows-874 (iso-8859-11)
- windows-1250
- windows-1251
- windows-1252 (iso-8859-1, latin1)
- windows-1253
- windows-1254 (iso8859-9, latin5)
- windows-1255
- windows-1256
- windows-1257
- x-mac-cyrillic (x-mac-ukrainian)
