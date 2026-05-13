<?php
if (isset($_POST['latitude']) && isset($_POST['longitude'])) {
    $lat = $_POST['latitude'];
    $lon = $_POST['longitude'];

    // Save to DB if you want
    // $mysqli->query("INSERT INTO user_location (latitude, longitude, created_at) VALUES ('$lat', '$lon', NOW())");

    echo "Latitude: $lat<br>Longitude: $lon";
} else {
    echo "No location data received.";
}
?>
