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
- utf-8
- gb18030
- ibm866
- iso-8859-2
- iso-8859-3
- iso-8859-4
- iso-8859-5
- iso-8859-6
- iso-8859-7
- iso-8859-8
- iso-8859-10
- iso-8859-13
- iso-8859-14
- iso-8859-15
- iso-8859-16
- koi8-r
- koi8-u
- macintosh
- windows-874
- windows-1250
- windows-1251
- windows-1252
- windows-1253
- windows-1254
- windows-1255
- windows-1256
- windows-1257
- x-mac-cyrillic
