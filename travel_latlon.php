<?php
//session_start();
include 'addWeatherDataToDatabase.php';
?>

<!DOCTYPE html>

<html lang="en-US">
<head>
<link rel="stylesheet" href="styles.css">
</head>
<body>
  <p class="zomato">The Daily Weather Forecast: <?php echo urldecode($_SESSION['location']) ?>.</p><br>
  <table id="WEATHER_TABLE"></table>
</body>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script>
  var weatherArray = [];

  // These coordinates are passed to the PHP file and used to build the URL that is sent to DarkSky.
  var lat = <?php echo $_SESSION['latitude'] ?>;
  var lon = <?php echo $_SESSION['longitude'] ?>;
  addWeatherDataToDatabase(lat, lon);
  //the coordinates are correct
  console.log(lat);
  console.log(lon);

  generateTable();

  function addWeatherDataToDatabase(lat,lon)
   {
    var ajaxCall = $.ajax({
      type: "POST",
      url: "addWeatherDataToDatabase.php",
      data: {LAT:lat, LON:lon},
      success: function (data) {
        $.when.apply(null, ajaxCall).then(function() {
          if (data == 0) {
            //Success
           getWeatherDataFromDatabase();
          } else {
			//Failure
			//This is what I get when I run the file
            console.log("failed to add weather to database");
          }
        });
      }
    });
  }

  function getWeatherDataFromDatabase()
   {
    $.ajax({
      type: "GET",
      url: "getWeatherDataFromDatabase.php",
      success: function (data) {
        weatherArray = JSON.parse(data);
        generateTable();
		console.log("generated table");
      }
    });
  }


  function generateTable()
  {
    $("#WEATHER_TABLE").empty();

    var weatherTable = document.getElementById("WEATHER_TABLE");

    var header = document.createElement("thead");
    weatherTable.appendChild(header);

    var headerRow = weatherTable.insertRow(0);

    var latTH = document.createElement("th");
    latTH.innerHTML = "latitude";
    headerRow.appendChild(latTH);

    var lonTH = document.createElement("th");
    lonTH.innerHTML = "longitude";
    headerRow.appendChild(lonTH);

    var temperatureTH = document.createElement("th");
    temperatureTH.innerHTML = "Temperature";
    headerRow.appendChild(temperatureTH);

    var summaryTH = document.createElement("th");
    summaryTH.innerHTML = "Summary";
    headerRow.appendChild(summaryTH);

    var iconTH = document.createElement("th");
    iconTH.innerHTML = "Icon";
    headerRow.appendChild(iconTH);

    var humidityTH = document.createElement("th");
    humidityTH.innerHTML = "Humidity";
    headerRow.appendChild(humidityTH);

    var windspeedTH = document.createElement("th");
    windspeedTH.innerHTML = "Wind Speed";
    headerRow.appendChild(windspeedTH);

    for (i = 0; i < weatherArray.length; i++) {
      var row = weatherTable.insertRow(-1);

      var latitudeTR = row.insertCell(-1);
      latitudeTR.innerHTML = weatherArray[i][1];

      var longitudeTR = row.insertCell(-1);
      longitudeTR.innerHTML = weatherArray[i][2];

      var temperatureTR = row.insertCell(-1);
      temperatureTR.innerHTML = weatherArray[i][3];

      var summaryTR = row.insertCell(-1);
      summaryTR.innerHTML = weatherArray[i][4]

      var iconTR = row.insertCell(-1);
      iconTR.innerHTML = weatherArray[i][5];

      var humidityTR = row.insertCell(-1);
      humidityTR.innerHTML = weatherArray[i][6];

      var windspeedTR = row.insertCell(-1);
      windspeedTR.innerHTML = weatherArray[i][7];
    }
  }
</script>


</html>
<p>
	<a href="zomato.php" title="Return to the previous page">&laquo; Go back</a>
</p>
