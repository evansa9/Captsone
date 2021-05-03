<?php
$conn = new mysqli("localhost", "root", "");

// Check connection
if ($conn->connect_error)
{
  die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT * FROM zomato.data;");

$stmt->execute();
$result = $stmt->get_result();

$weathersArray = Array();
while ($row = $result->fetch_array(MYSQLI_NUM))
 {
  $weatherElementArray = Array();
  foreach ($row as $element)
  {
    array_push($weatherElementArray, htmlspecialchars_decode($element, ENT_QUOTES));
  }
  array_push($weathersArray, $weatherElementArray);
}

$stmt->close();
$conn->close();
echo json_encode($weathersArray);
?>
