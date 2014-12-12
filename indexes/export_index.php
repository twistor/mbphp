<?php

/**
 * @file
 * Downloads an index file and exports it to a php array.
 *
 * @see https://encoding.spec.whatwg.org
 */

if (empty($argv[1])) {
  print "Please provide a URL.\n";
  return;
}

$url = $argv[1];

$handle = fopen($url, 'r');

if (!$handle) {
  return;
}

$output = array();

while ($line = fgets($handle)) {
    if ($line[0] === '#' || $line === "\n") {
        continue;
    }

    // Get the position of the first tab.
    $first = strpos($line, "\t");
    $pointer = ltrim(substr($line, 0, $first), ' ');

    // The code point is up to 7 characters after the first tab.
    $output[$pointer] = hexdec(rtrim(substr($line, $first+1, 7)));
}

$filename = basename($url);
$filename = substr($filename, 0, strrpos($filename, '.')).'.php';

// Convert index-iso-8859-7.php to index-iso88597.php
$parts = explode('-', $filename, 2);
$parts[1] = str_replace('-', '', $parts[1]);
$filename = implode('-', $parts);

$filename = dirname(__FILE__) .'/'.$filename;

$comment = <<<COM


/**
 * @file
 * $url
 */


COM;

$content = '<?php'.$comment.'return '.var_export($output, true).';';

file_put_contents($filename, $content);
