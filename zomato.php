<?php
session_start();

include 'connect.php';

$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

//converts location input to latitude and longitude if one is found
if (isset($_POST['location'])) {
	$location = urlencode(htmlspecialchars($_POST['location']));

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://developers.zomato.com/api/v2.1/locations?query=$location");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


	$headers = array();
	$headers[] = 'Accept: application/json';
	$headers[] = 'User-Key: f580e307653204250692fa05fa8d2b96';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close($ch);
	
	$json_data = json_decode($result, true);
	//echo "<pre>"; print_r($json_data); echo "</pre>";
	if (!empty($json_data["location_suggestions"])) {
		$_SESSION['latitude'] = (double) $json_data["location_suggestions"][0]["latitude"];
		$_SESSION['longitude'] = (double) $json_data["location_suggestions"][0]["longitude"];
		$_SESSION['location'] = $json_data["location_suggestions"][0]["title"];
		$_SESSION['location_exists'] = true;
	}
	else {
		$_SESSION['location_exists'] = false;
	}
}

//output error if location is not found
if (!$_SESSION['location_exists']) {
	?>
	<p class="zomato">
		Location could not be found, please go back and try again.
	</P>
	<?php
}
//else show form for map, weather, and restaurants
else if (!isset($_POST['keyword'])) {
?>

<html>
	<head>
		<title>Capstone</title>
		<link rel="stylesheet" href="./styles.css">
	</head>

	<body>
		<p class="zomato">Your current location is: <?php echo urldecode($_SESSION['location']) ?>.</p><br>
		<div>
			<h3>To pick a destination click the link below.</h1>
			<a class="zomato" href="./friend1.php">Pick destination</a><br>
			<h3>To look at weather click the link below.</h1>
			<a class="zomato" href="./travel_latlon.php">Show weather</a><br>
			<h3>To find restaurants enter a search keyword below.</h1>
			<form name="zomato" action="./show_restaurants.php" method="post">
				<label class="zomato" for="keyword">Keyword:</label>
				<input type="text" name="keyword" id="keyword" required><br>
				<input type="submit">
			</form>
		</div>

<?php
}
//if keyword is entered add restaurants to database
else {
	$keyword = urlencode($_POST['keyword']);
	
	$latitude = $_SESSION['latitude'];
	$longitude = $_SESSION['longitude'];

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://developers.zomato.com/api/v2.1/search?q=$keyword&lat=$latitude&lon=$longitude");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

	$headers = array();
	$headers[] = 'Accept: application/json';
	$headers[] = 'User-Key: f580e307653204250692fa05fa8d2b96';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close($ch);

	$json_data = json_decode($result,true);
	
	$stmt = $db->prepare ("DELETE FROM zomato.restaurants");
	$stmt->execute();

	$stmt = $db-> prepare ("INSERT INTO zomato.restaurants (name, address, phone_number, cost_rating, average_rating, number_of_votes, restaurant_url, hours) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	$stmt ->bindParam(1, $name);
	$stmt ->bindParam(2, $address);
	$stmt ->bindParam(3, $phone_number);
	$stmt ->bindParam(4, $cost_rating);
	$stmt ->bindParam(5, $average_rating);
	$stmt ->bindParam(6, $number_of_votes);
	$stmt ->bindParam(7, $restaurant_url);
	$stmt ->bindParam(8, $hours);


	$recieved = (int)$json_data["results_shown"];

	for ($i=0; $i < $recieved; $i++) {
		$name = $db->quote($json_data["restaurants"][$i]["restaurant"]["name"]);
		$hours = $json_data["restaurants"][$i]["restaurant"]["timings"];
		$cost_rating = $json_data["restaurants"][$i]["restaurant"]["price_range"];
		$average_rating = (double)$json_data["restaurants"][$i]["restaurant"]["user_rating"]["aggregate_rating"];
		$number_of_votes = (int) $json_data["restaurants"][$i]["restaurant"]["user_rating"]["votes"];
		$address = $json_data["restaurants"][$i]["restaurant"]["location"]["address"];
		$phone_number = $json_data["restaurants"][$i]["restaurant"]["phone_numbers"];
		$restaurant_url = $db->quote($json_data["restaurants"][$i]["restaurant"]["url"]);

	$stmt->execute();
	}
	

	//prints array of results
	//echo "<pre>"; print_r($json_data); echo "</pre>";
	
	//include 'show_restaurants.php';
}
?>
		<p class="zomato">
			<a href="./main.html" title="Return">&laquo; Enter new location</a>
		</p>

	</body>
</html>