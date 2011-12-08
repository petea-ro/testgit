<?php
/* Last updated with phpFlickr 1.3.2
 *
 * This example file shows you how to call the 100 most recent public
 * photos.  It parses through them and prints out a link to each of them
 * along with the owner's name.
 *
 * Most of the processing time in this file comes from the 100 calls to
 * flickr.people.getInfo.  Enabling caching will help a whole lot with
 * this as there are many people who post multiple photos at once.
 *
 * Obviously, you'll want to replace the "<api key>" with one provided 
 * by Flickr: http://www.flickr.com/services/api/key.gne
 */

require_once("phpFlickr.php");
$f = new phpFlickr("d44e154438e064ee28e76d09f445fc20");

//$recent = $f->photos_getRecent();

//var_dump($recent);
/*
foreach ($recent['photos']['photo'] as $photo) {
    $owner = $f->people_getInfo($photo['owner']);
    echo "<a href='http://www.flickr.com/photos/" . $photo['owner'] . "/" . $photo['id'] . "/'>";
    echo $photo['title'];
    echo "</a> Owner: ";
    echo "<a href='http://www.flickr.com/people/" . $photo['owner'] . "/'>";
    echo $owner['username'];
    echo "</a><br>";
}*/

//$data = $f->photosets_getList('46602471@N07');
//var_dump($data);

$photos = $f->photos_search(array("tags"=>"cahul"));
//var_dump($photos);

foreach($photos['photo'] as $photo){
	$src = "http://farm".$photo['farm'].".static.flickr.com/".$photo['server']."/".$photo['id']."_".$photo['secret'].".jpg";
	echo "<img src='$src' /><br/>";
}

//echo "http://farm{farm-id}.static.flickr.com/{server-id}/{id}_{secret}.jpg";
//echo "http://farm6.static.flickr.com/5228/72157626475406789_f17260aa77.jpg";
?>
