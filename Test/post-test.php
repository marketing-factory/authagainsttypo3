<?php

$url = 'http://localhost/index.php?type=65537';

$curlHandler = curl_init();

// set the target url
curl_setopt($curlHandler, CURLOPT_URL, $url);

curl_setopt($curlHandler, CURLOPT_COOKIE, 'XDEBUG_SESSION=PHPSTORM');

// how many parameter to post
curl_setopt($curlHandler, CURLOPT_POST, 1);

// the parameter 'username' with its value 'testuser'
curl_setopt($curlHandler, CURLOPT_POSTFIELDS, array(
    'user' => 'testuser',
    'pass' => 'testuser',
    'logintype' => 'login',
    'serviceUser' => 'Service',
    'servicePass' => 'Pass'
));

$result = curl_exec($curlHandler);
curl_close($curlHandler);
print $result;
