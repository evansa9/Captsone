<?php
session_start();

include 'connect.php';

$keyword = urlencode($_POST['keyword']);

$latitude = $_SESSION['latitude'];
$longitude = $_SESSION['longitude'];

//gets full list of restaurants
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

//clears database, adds new values
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
	$name = $json_data["restaurants"][$i]["restaurant"]["name"];
	$hours = $json_data["restaurants"][$i]["restaurant"]["timings"];
	$cost_rating = $json_data["restaurants"][$i]["restaurant"]["price_range"];
	$average_rating = (double)$json_data["restaurants"][$i]["restaurant"]["user_rating"]["aggregate_rating"];
	$number_of_votes = (int) $json_data["restaurants"][$i]["restaurant"]["user_rating"]["votes"];
	$address = $json_data["restaurants"][$i]["restaurant"]["location"]["address"];
	$phone_number = $json_data["restaurants"][$i]["restaurant"]["phone_numbers"];
	$restaurant_url = $db->quote($json_data["restaurants"][$i]["restaurant"]["url"]);

$stmt->execute();
}
?>

<html>
<head>
	<title>Show Restaurants</title>
	<link rel="stylesheet" href="styles.css">
</head>
<body>
	<div style="overflow-x:auto;">
		<table class="restaurants">
			<thead class="restaurants">
				<th class="restaurants">Number</th>
				<th class="restaurants">Name</th>
				<th class="restaurants">Rating</th>
				<th class="restaurants">Hours</th>
				<th class="restaurants">Address</th>
			</thead>
			
			<?php
			//gets restaurant data from database
			$stmt = $db->query("SELECT restaurant_id, name, hours, average_rating, address FROM zomato.restaurants ORDER BY average_rating DESC LIMIT 5");
			$result = $stmt->fetchAll();

			if ($stmt->columnCount() > 0) {
				
				$count = 1;
				//table
				foreach ($result as $row) {
					?>
					<tr  class="restaurants">
						<td class="restaurants"><?php echo "$count" ?> </td>
						<td class="restaurants"><?php echo stripslashes($row["name"]) ?> </td>
						<td class="restaurants"><?php echo $row["average_rating"]?> </td>
						<td class="restaurants"><?php echo $row["hours"]?> </td>
						<td class="restaurants"><?php echo $row["address"]?> </td>
					</tr>
					<?php
					$count++;
				}
				echo "</table>";
			}
			else {
				echo "no results";
			}
			?>
		</table>
	</div>

	<p>
		<a href="./zomato.php" title="Return">&laquo; Go back</a>
	</p>
</body>
</html>