<?php
    include('dbconn/dbconn.php');

    if (isset($_POST['ticket_id'])) {
        $ticket_id = $_POST['ticket_id'];
        $latitude = $_POST['latitude'] ?? 'NULL';
        $longitude = $_POST['longitude'] ?? 'NULL';
        $city = $_POST['city'] ?? 'NULL';
        $country = $_POST['country'] ?? 'NULL';
        $method = $_POST['method'] ?? 'unknown';

        $stmt = $mysqli->prepare("UPDATE payment_ip_log SET latitude=?, longitude=?, city=?, country=?, method=? WHERE ticket_id=? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("ddssss", $latitude, $longitude, $city, $country, $method, $ticket_id);
        $stmt->execute();
    }

    $ticket_id = $_POST['ticket_id'] ?? '';
    $latitude  = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $city      = $_POST['city'] ?? null;
    $country   = $_POST['country'] ?? null;
    $accuracy  = $_POST['accuracy'] ?? null;
    $source    = $_POST['source'] ?? 'gps';

    if (empty($ticket_id)) {
        http_response_code(400);
        echo "Missing ticket_id";
        exit;
    }

    // Try update the most recent payment_ip_log for this ticket
    $sql = "UPDATE payment_ip_log
            SET latitude = ?, longitude = ?, city = ?, country = ?, accuracy = ?, source = ?
            WHERE ticket_id = ?
            ORDER BY id DESC
            LIMIT 1";

    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssss", $latitude, $longitude, $city, $country, $accuracy, $source, $ticket_id);
        if ($stmt->execute()) {
            echo "OK";
        } else {
            http_response_code(500);
            echo "DB update failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo "DB prepare failed: " . $mysqli->error;
    }
?>

<!DOCTYPE html>
<html>
<head>
  <title>Get Live Location</title>
</head>
<body>
  <h3>Fetching your live location...</h3>
<script>
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function (position) {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;

        document.body.innerHTML = `<h3>✅ Location captured!</h3>
        <p>Latitude: ${lat}<br>Longitude: ${lon}</p>`;

        fetch("save_location.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `latitude=${lat}&longitude=${lon}`
        });
      },
      function (error) {
        let msg = '';
        switch (error.code) {
          case error.PERMISSION_DENIED:
            msg = "❌ Permission denied. Allow location access.";
            break;
          case error.POSITION_UNAVAILABLE:
            msg = "❌ Location info unavailable.";
            break;
          case error.TIMEOUT:
            msg = "❌ Location request timed out.";
            break;
          default:
            msg = "❌ Unknown error occurred.";
        }
        document.body.innerHTML = `<h3>${msg}</h3>`;
      },
      { timeout: 10000 } // 10 seconds timeout
    );
  } else {
    document.body.innerHTML = "<h3>❌ Geolocation not supported in your browser.</h3>";
  }
</script>
</body>
</html>
