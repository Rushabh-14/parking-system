<?php
include('dbconn/dbconn.php'); // Database connection

if (isset($_GET['ticket_id']) && isset($_GET['amount'])) {
    $ticket_id = $_GET['ticket_id'];
    $amount = $_GET['amount'];

    // Get payer IP
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $payer_ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $payer_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $payer_ip = $_SERVER['REMOTE_ADDR'];
    }

    // Save basic IP info
    $mysqli->query("INSERT INTO payment_ip_log (ticket_id, payer_ip, access_time) VALUES ('$ticket_id', '$payer_ip', NOW())");

    // Business UPI info
    $business_upi_id = "your upi id";
    $business_name = "Smart Parking";

    // Create UPI payment URL
    $upi_url = "upi://pay?pa={$business_upi_id}&pn=" . urlencode($business_name) . "&am={$amount}&cu=INR&tn=" . urlencode("Payment for {$ticket_id}");
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Confirm Payment</title>
    </head>
    <body onload="captureLocation('<?php echo $ticket_id; ?>', '<?php echo $_SERVER['REMOTE_ADDR']; ?>', '<?php echo $upi_url; ?>')">
        <h3>Fetching your location...</h3>
        <script>
            async function captureLocation(ticketId, ip, upiUrl) {
                let gpsTimeout;

                // Helper: send location data to server
                async function sendToServer(lat, lon, city, country, source) {
                    await fetch('capture_location.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `ticket_id=${ticketId}&latitude=${lat}&longitude=${lon}&city=${city}&country=${country}&source=${source}`
                    });
                }

                // Helper: get location from IP
                async function getIPLocation() {
                    try {
                        const res = await fetch(`https://ipapi.co/${ip}/json/`);
                        const data = await res.json();

                        const lat = data.latitude || null;
                        const lon = data.longitude || null;
                        const city = data.city || 'Unknown';
                        const country = data.country_name || 'Unknown';

                        await sendToServer(lat, lon, city, country, 'ip');
                        console.log(`📍 IP-based location captured: ${city}, ${country}`);
                    } catch (err) {
                        console.error("❌ IP location fetch failed:", err);
                    }

                    window.location.href = upiUrl; // Continue to payment
                }

                // GPS logic with timeout
                if (navigator.geolocation) {
                    gpsTimeout = setTimeout(() => {
                        console.warn("⚠️ GPS timeout reached — using IP-based location.");
                        getIPLocation();
                    }, 7000); // 5 seconds

                    navigator.geolocation.getCurrentPosition(
                        async (pos) => {
                            clearTimeout(gpsTimeout); // cancel timeout if GPS works
                            const lat = pos.coords.latitude;
                            const lon = pos.coords.longitude;

                            // Reverse geocoding to get city
                            const resp = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                            const data = await resp.json();

                            const city = data.address.city || data.address.town || data.address.village || 'Unknown';
                            const country = data.address.country || 'Unknown';

                            await sendToServer(lat, lon, city, country, 'gps');
                            console.log(lat, lon, city, country, 'gps');
                            alert(`✅ Location Captured!\nCity: ${city}\nLat: ${lat}\nLon: ${lon}`);

                            setTimeout(() => { window.location.href = upiUrl; }, 1000);
                        },
                        (err) => {
                            clearTimeout(gpsTimeout);
                            console.warn("⚠️ GPS failed — using IP location:", err.message);
                            getIPLocation();
                        }
                    );
                } else {
                    console.warn("❌ Geolocation not supported — using IP fallback.");
                    getIPLocation();
                }
            }
        </script>
    </body>
    </html>
<?php
} else {
    echo "<h3 style='color:red;'>Invalid request. Ticket ID or amount missing.</h3>";
}
?>
