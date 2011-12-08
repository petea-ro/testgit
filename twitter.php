<?php
$params = array(
			'screen_name'			=>'navalny',
			'include_entities'		=>'false',
			'include_rts'			=>'true',
			'count'					=>'20'
		);
$query = http_build_query($params, '', '&');
$url ='https://api.twitter.com/1/statuses/user_timeline.json?';
$url .=$query;




$result = @file_get_contents($url);
if(!$result){
    echo "not found";
}
else{
    $items = json_decode($result);
	foreach($items as $item)
	{
		if(isset($item->retweeted_status))
			$item = $item->retweeted_status;
	   $src = $item->user->profile_image_url;
	   $screen_name = $item->user->screen_name;
	   $name = $item->user->name;
	   $text = $item->text;
	   $date = strtotime($item->created_at);
		echo "<img src=\"$src\" /> <b>$screen_name</b> $name : $text : ".date('d/m/y H:i:s', $date)." <br/>";
	}
}



?>