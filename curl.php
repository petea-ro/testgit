<?php

function curl($url){

     $curl = curl_init($url);
     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
     return curl_exec($curl);
     curl_close($curl);

}

if ( ! function_exists('valid_email'))
{
	echo "No curl available";
}
else{
 echo curl("http://mail.md");
}

?>