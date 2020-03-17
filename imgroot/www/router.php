<?php 

/* Router file for the docker-public-ip-monitor image.

Possible paths:
 - /    -> Outputs a simple HTML table with the IP history
 - /ip  -> Outputs the last IP
 - /log -> Outputs the raw IP history
*/

$data_file = '/data/ip-history.txt';

$file = fopen($data_file, 'r');
if (!$file) {
    header('HTTP/1.1 501 Internal Server Error');
    die('Error opening IP address history.');
}

// Read file in reverse order of lines
fseek($file, 0, SEEK_END);
function fbegin($fh) {
    return ftell($fh) == 0;
}
function fgets_r($fh) {
    $str = '';
    while (!fbegin($fh) && ("\n" != ($char = fgetc($fh)))) {
        fseek($fh, ftell($fh)-2);
        $str = $char . $str;
    }
    if (!fbegin($fh)) {
        fseek($fh, ftell($fh)-2);
    }
    else {
        $str = fgetc($fh) . $str;
        fseek($fh, 0);
    }
    return $str;
}

if (preg_match(':^/ip/?$:', $_SERVER['REQUEST_URI'])) {
    $ip = '';
    try {
        $last_line = fgets_r($file);
        list(,$ip) = explode(' ',$last_line);
        die($ip);
    }
    finally {
        fclose($file);
    }
}
elseif (preg_match(':^/log/?$:', $_SERVER['REQUEST_URI'])) {
    try {
        header('Content-Type: text/plain');
        while (!fbegin($file)) {
            echo fgets_r($file);
            echo "\n";
        }
        die();
    }
    finally {
        fclose($file);
    }
}
elseif (!preg_match(':^/$:', $_SERVER['REQUEST_URI'])) {
    // Unsupported URI -> Treat it as a file path (i.e., 404)
    phpinfo();
    die();
    return false;
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Public IP history</title>
<style>
body {
    font: sans-serif 16px;
    text-align: center; 
}
table {
    /* border-spacing: 1em 0.5ex; */
    border-spacing: 0;
    margin: 0 auto;
}
table td {
    /* padding: 0.4ex 0.5ex; */
    padding: 0.9ex 1.25em;
}
tr td:first-child {
    margin-right: 1em;
}
tbody tr:hover {
    background-color: #ccc;
    cursor: default;
}
</style>
<body>
<h1>IP Address History</h1>

<p>
Plain text:
<a href="/ip">IP Address</a>
<a href="/log">Log</a>
</p>

<table>
<thead>
<tr>
<th>Timestamp</th>
<th>Public IP</th>
<tr>
</thead>
<tbody>
<?php

try {
    while (!fbegin($file)) {
        $row = fgets_r($file);
        if (empty($row)) {
            continue;
        }
        list($ts, $ip) = explode(' ',$row);
        $ts = date('r', $ts);
?>
    <tr><td><?=$ts?></td><td><?=$ip?></td></tr>
<?php
    }
}
finally {
    fclose($file);
}

?>
</tbody>
</table>
</body>
</html>