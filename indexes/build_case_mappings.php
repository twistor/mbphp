<?php

/**
 * @file
 * Builds the lower to uppercase, and vise-vera.
 *
 * @see http://www.unicode.org/Public/UNIDATA/UnicodeData.txt
 */

$handle = fopen('http://www.unicode.org/Public/UNIDATA/UnicodeData.txt', 'r');

if (!$handle) {
  return;
}

$comment = <<<COM


/**
 * @file
 * http://www.unicode.org/Public/UNIDATA/UnicodeData.txt
 */


COM;


// Lowercase to uppercase.
$output = array();

while ($line = fgets($handle)) {
    $parts = explode(';', $line);

    if (strlen(trim($parts[12]))) {
        $output[hexdec(trim($parts[0]))] = hexdec(trim($parts[12]));
    }
}

fclose($handle);

$filename = dirname(__FILE__) .'/lower_to_upper.php';
$content = '<?php'.$comment.'return '.var_export($output, true).';';
file_put_contents($filename, $content);


// Uppercase to lower.
$handle = fopen('http://www.unicode.org/Public/UNIDATA/UnicodeData.txt', 'r');
$output = array();

while ($line = fgets($handle)) {
    $parts = explode(';', $line);

    if (strlen(trim($parts[13]))) {
        $output[hexdec(trim($parts[0]))] = hexdec(trim($parts[13]));
    }
}

fclose($handle);

$filename = dirname(__FILE__) .'/upper_to_lower.php';
$content = '<?php'.$comment.'return '.var_export($output, true).';';
file_put_contents($filename, $content);
