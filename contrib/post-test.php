<?php

$url='http://ista-intranet.typo-dev.web-factory.de/index.php?id=7244&type=65537&no_cache=1';



$ch = curl_init();

// set the target url
curl_setopt($ch, CURLOPT_URL,$url);

// howmany parameter to post
curl_setopt($ch, CURLOPT_POST, 1);

// the parameter 'username' with its value 'johndoe'
curl_setopt($ch, CURLOPT_POSTFIELDS,array('user'=>'testuser','pass'=>'testuser','logintype'=>'login','serviceUser'=>'Service','servicePass'=>'Pass'));

$result= curl_exec ($ch);
curl_close ($ch);
print $result;


?>