<?php

require 'src/facebook.php';
Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
							  'appId'  => '377900518581',
							  'secret' => 'e3451e3d3a3f7f7986046176fe866538',
						));

try {
      $csv = read_csvfile("comments.csv");

	  $results = array();
	  foreach($csv as $line){

	      //0 - comment id 2- alias
		  $alias = $line[2];
		  $id = $line[0];


	      $link = "http://poftabuna.ro/restaurant/$alias/avis.html?c=$id#$id";

	      $fql =   "SELECT share_count, like_count, comment_count, total_count
					FROM link_stat
					WHERE  '$link' IN url";

		  $facebook_result = $facebook->api(array(
		  								'method' => 'fql.query',
										'query' => $fql
		  							));


		  $results[$id] = $facebook_result[0]['total_count'];

		  if(count($results)>5) break;


	  }
	  var_dump($results);


}
catch(FacebookApiException $e)
{
	echo $e->getMessage();
}


function read_csvfile($file){
    $result = array();
	$row = 1;

	if (($handle = fopen($file, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	        $num = count($data);
	        $row++;
			$line = array();
	        for ($c=0; $c < $num; $c++) {
	            $line[] = $data[$c];
	        }
			$result[] = $line;
	    }
	    fclose($handle);
	}
	return $result;
}