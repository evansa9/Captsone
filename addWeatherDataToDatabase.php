

<?php
session_start();
$conn = new mysqli("localhost", "root", "");

// Check connection
if ($conn->connect_error)
{
	die("Connection failed: " . $conn->connect_error);
}

// Empties the table to ensure the most up-to-date data.
$stmt = $conn->prepare("TRUNCATE TABLE zomato.data;");
$stmt->execute();

$lat = $_SESSION['latitude'];
$lon = $_SESSION['longitude'];



//Get weather data from DarkSky.
$url = "https://api.darksky.net/forecast/40beaa0512a83d0b7d23a9eff4543dd2/" . $lat . "," . $lon;
$darkSkyData = file_get_contents($url);
$data = json_decode($darkSkyData, true);



// Prepare and Bind
$stmt = $conn->prepare("INSERT INTO zomato.data (lat, lon, temperatureHigh, summary, icon, humidity, windspeed) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iidssdd", $lat, $lon, $temperature, $summary, $icon, $humidity, $windspeed);

// Loop through weather data and insert it into the databse.
		foreach ($data["daily"]["data"] as $dataPoint)
{
	$temperature = $dataPoint["temperatureHigh"];
	$summary = $dataPoint["summary"];
	$icon = $dataPoint["icon"];
	$humidity = $dataPoint["humidity"];
	$windspeed = $dataPoint["windSpeed"];
	$isSuccess = $stmt->execute();
}

$stmt->close();
$conn->close();

?>
