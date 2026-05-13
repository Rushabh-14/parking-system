<?php
// payment_redirect.php
    include 'dbconn/dbconn.php'; // adjust path


    if (!isset($_GET['ticket_id'])) {
        die("Invalid request");
    }

    $ticket_id = $_GET['ticket_id'];

    // Capture the real IP address
    function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        else return $_SERVER['REMOTE_ADDR'];
    }

    $ip = getRealIpAddr();
    $time = date('Y-m-d H:i:s');

    // Store in database (create table below)
    $mysqli->query("INSERT INTO payment_ip_log (ticket_id, payer_ip, access_time) VALUES ('$ticket_id', '$ip', '$time')");

    // Redirect to payment app
    $business_upi_id = "7383219966@omni";
    $business_name = "Smart Parking";
    $amount = $_GET['amount'] ?? 20; // fallback
    $upi_url = "upi://pay?pa={$business_upi_id}&pn=" . urlencode($business_name) . "&am={$amount}&cu=INR&tn=" . urlencode("Parking Payment for {$ticket_id}");

    header("Location: $upi_url");
    exit;
?>
<!doctype html>
<html>
    <head>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Redirecting to UPI</title>
    <style>body{font-family:Arial; text-align:center; padding:30px}</style>
    </head>
    <body>
    <h3>Proceed to Pay</h3>
    <p>Ticket: <strong><?=htmlspecialchars($ticket_id)?></strong></p>
    <p>Amount: <strong>₹<?=htmlspecialchars(number_format($total_amount,2))?></strong></p>
    <p>Tap "Open UPI app" if it doesn't open automatically.</p>

    <p>
        <a id="openLink" href="<?=htmlspecialchars($upi_url)?>" style="display:inline-block;padding:10px 16px;background:#28a745;color:#fff;border-radius:6px;text-decoration:none;">Open UPI app</a>
    </p>

    <p style="font-size:12px;color:#666">If your UPI app doesn't open automatically, tap the button above. The tab will try to close after redirect.</p>

    <script>
        (function(){
        // attempt to open the UPI link
            var upi = <?= json_encode($upi_url) ?>;
            // first try immediate location change
            window.location.href = upi;

            // fallback: also set open button href
            document.getElementById('openLink').href = upi;

            // after 1.5s attempt to close the window/tab (may be blocked)
            setTimeout(function(){
                try { window.close(); } catch(e) {}
                // as a second attempt open small about:blank and close
                setTimeout(function(){ window.open('about:blank','_self'); window.close(); }, 800);
            }, 1500);
        })();
    </script>
    </body>
</html>
