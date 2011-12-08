<?php

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