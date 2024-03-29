<?php
	require_once("../src/FoursquareAPI.class.php");
	$location = array_key_exists("location",$_GET) ? $_GET['location'] : "Montreal, QC";
?>
<!doctype html>
<html>
<head>
	<title>PHP-Foursquare :: Unauthenticated Request Example</title>
</head>
<body>
<h1>Basic Request Example</h1>
<p>
	Search for venues near...
	<form action="" method="GET">
		<input type="text" name="location" />
		<input type="submit" value="Search!" />
	</form>
</p>
<p>Searching for venues near <?php echo $location; ?></p>
<hr />
<?php 
	// Set your client key and secret
	$client_key = "W2VZZYBMAOI1BGPEAYP0PDI4RZO5JQBFFZXNYGE32Z4IOXXF";
	$client_secret = "QPSLOIUOBPXYKV11PNVY3DZSOSK0C0JNM00H3DOOCTWRDW0I";
	// Load the Foursquare API library
	$foursquare = new FoursquareAPI($client_key,$client_secret);

	// Generate a latitude/longitude pair using Google Maps API
	list($lat,$lng) = $foursquare->GeoLocate($location);
	
	// Prepare parameters
	$params = array("ll"=>"$lat,$lng");
	
	// Perform a request to a public resource
	$response = $foursquare->GetPublic("venues/search",$params);
	$venues = json_decode($response);
	
	foreach($venues->response->groups as $group):
?>

	<h2><?php echo $group->name; ?></h2>
	<ul>
		<?php foreach($group->items as $venue): ?>
			<li>
				<?php 
					echo $venue->name;
					if(property_exists($venue->contact,"twitter")){
						echo " -- follow this venue <a href=\"http://www.twitter.com/{$venue->contact->twitter}\">@{$venue->contact->twitter}</a>";
					}
				?>
			
			</li>
		<?php endforeach; ?>
	</ul>

<?php endforeach; ?>
<?php var_dump($venues)?>
</body>
</html>
