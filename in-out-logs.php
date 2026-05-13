<?php
include_once('header.php');
include_once('sidebar.php');
include('dbconn/dbconn.php');
include('phpqrcode/qrlib.php'); // Make sure phpqrcode exists

$types_result = $mysqli->query("SELECT type_name, id FROM parking_type");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $r_id = $_POST['r_id'] ?? '';
    $date_time = date('Y-m-d H:i:s');   // ALWAYS server time
    $user_name = $_POST['user_name'] ?? '';
    $contact = $_POST['contect'] ?? '';
    $vehical_no = $_POST['vehical_no'] ?? '';
    $vehical_type = $_POST['vehicle_type'] ?? '';
    $in_out = "1"; // default IN
    $qr_data = $_POST['qr_data'] ?? '';

    // --- Helper Function for IP ---
    function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
        else return $_SERVER['REMOTE_ADDR'];
    }

    // 🟢 1️⃣ Exit for Manual Tickets
    if (!empty($qr_data)) {
        preg_match('/TKT-[A-Z0-9]+/', $qr_data, $matches);
        $ticket_id = $matches[0] ?? $qr_data;

        $check_ticket = "SELECT * FROM manual_in_out WHERE ticket_id = '$ticket_id' ORDER BY id DESC LIMIT 1";
        $result_ticket = $mysqli->query($check_ticket);

        if ($result_ticket && $result_ticket->num_rows > 0) {
            $ticket = $result_ticket->fetch_assoc();

            if ($ticket['in_out'] == 0) {
                echo "<div class='alert alert-danger'>❌ This ticket is already marked as OUT!</div>";
            } else {
                $exit_time = date('Y-m-d H:i:s');

                // --- Fetch Entry ---
                $entry_query = "SELECT date_time, vehicle_type FROM manual_in_out 
                                WHERE ticket_id = '{$ticket['ticket_id']}' AND in_out = 1 
                                ORDER BY id ASC LIMIT 1";
                $entry_result = $mysqli->query($entry_query);

                if ($entry_result && $entry_result->num_rows > 0) {
                    $entry = $entry_result->fetch_assoc();
                    $entry_time = $entry['date_time'];
                    $vehicle_type = $entry['vehicle_type'];

                    // --- Duration Calculation ---
                    $entry_ts = strtotime($entry_time);
                    $exit_ts  = strtotime($exit_time);
                    $duration_seconds = max(0, $exit_ts - $entry_ts);
                    $duration_hours = ceil($duration_seconds / 3600);
                    if ($duration_hours == 0) $duration_hours = 1;

                    // --- Rate Calculation ---
                    $rate_query = "SELECT price_per_hour FROM parking_pricing WHERE vehicle_type_id = '$vehicle_type' LIMIT 1";
                    $rate_result = $mysqli->query($rate_query);
                    $rate_row = $rate_result && $rate_result->num_rows > 0 ? $rate_result->fetch_assoc() : null;
                    $rate = $rate_row ? (float)$rate_row['price_per_hour'] : 20;
                    $total_amount = $rate * $duration_hours;

                    // --- Omni Card UPI Config ---
                    $business_upi_id = "7383219966@omni";
                    $business_name = "Smart Parking";

                    // ✅ Use ngrok public URL
                    $public_url = "https://wilber-newish-chery.ngrok-free.dev/php/parking/parking/confirm_payment.php";
                    $redirect_link = $public_url . "?ticket_id=" . urlencode($ticket['ticket_id']) . "&amount=" . urlencode($total_amount);
                    $upi_url = $redirect_link; // UPI redirection handled by confirm_payment.php

                    // --- Confirm Payment ---
                    if (isset($_POST['confirm_payment'])) {
                        $payment_method = $_POST['payment_method'];
                        $transaction_id_user = $_POST['transaction_id'] ?? '';
                        $used_upi_id = ($payment_method == "UPI") ? $business_upi_id : NULL;

                        // ✅ Step 1: Fetch payer IP from log (recorded when they scanned QR)
                        $check_ip = $mysqli->query("SELECT payer_ip, latitude, longitude, city, country, source FROM payment_ip_log WHERE ticket_id='{$ticket['ticket_id']}' ORDER BY id DESC LIMIT 1");
                        if ($check_ip && $check_ip->num_rows > 0) {
                            $ip_row = $check_ip->fetch_assoc();
                            $payer_ip = $ip_row['payer_ip'] ?? 'Not captured';
                            $latitude = $ip_row['latitude'] ?? '';
                            $longitude = $ip_row['longitude'] ?? '';
                            $city = $ip_row['city'] ?? '';
                            $country = $ip_row['country'] ?? '';
                            $source = $ip_row['source'] ?? 'ip';
                        } else {
                            $payer_ip = 'Not captured';
                            $city = '';
                            $latitude = $longitude = '';
                            $source = 'none';
                        }
                        
                        // ✅ Step 2: Save Exit Log
                        $sql_exit = "INSERT INTO manual_in_out 
                                    (ticket_id, in_out, date_time, user_name, contact, vehicle_no, vehicle_type, qr_code, is_used)
                                    VALUES (
                                        '{$ticket['ticket_id']}',
                                        0,
                                        '$exit_time',
                                        '{$ticket['user_name']}',
                                        '{$ticket['contact']}',
                                        '{$ticket['vehicle_no']}',
                                        '{$ticket['vehicle_type']}',
                                        '{$ticket['qr_code']}',
                                        1
                                    )";

                        if ($mysqli->query($sql_exit)) {

                            // ✅ Step 3: Save Payment Record
                            $sql_payment = "INSERT INTO parking_payment 
                                (ticket_id, amount, hours, payment_method, payment_time, created_at, upi_id, transaction_id, payer_ip) 
                                VALUES (
                                    '{$ticket['ticket_id']}',
                                    '$total_amount',
                                    '$duration_hours',
                                    '$payment_method',
                                    '$exit_time',
                                    NOW(),
                                    " . ($used_upi_id ? "'$used_upi_id'" : "NULL") . ",
                                    " . (!empty($transaction_id_user) ? "'$transaction_id_user'" : "NULL") . ",
                                    '$payer_ip'
                                )";
                            $mysqli->query($sql_payment);

                            // ✅ Step 4: Generate print receipt link
                            $qr_link = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($upi_url);
                            $print_url = 'print_receipt.php?ticket_id=' . urlencode($ticket['ticket_id']) .
                                        '&amount=' . urlencode($total_amount) .
                                        '&hours=' . urlencode($duration_hours) .
                                        '&qr_code=' . urlencode($qr_link);

                            echo "<div class='alert alert-success text-center'>
                                    ✅ Payment successful & exit recorded!<br>
                                    <b>Ticket ID:</b> {$ticket['ticket_id']}<br>
                                    <b>Total Paid:</b> ₹$total_amount<br>
                                    <b>Method:</b> $payment_method<br>
                                    <b>Payer IP:</b> $payer_ip<br>" .
                                    ($payment_method == "UPI" ? "<b>Transaction ID:</b> $transaction_id_user<br>" : "") . "
                                    <br>
                                    <a href='$print_url' target='_blank' class='btn btn-primary'>🖨️ Print Receipt</a>
                                </div>";

                        } else {
                            echo "<div class='alert alert-danger'>❌ Error saving exit: " . $mysqli->error . "</div>";
                        }
                    } else {
                        // --- Payment UI ---
                        echo "
                        <div class='alert alert-info'>
                            <h5>🚗 Exit Summary</h5>
                            <b>Ticket ID:</b> {$ticket['ticket_id']}<br>
                            <b>Entry:</b> $entry_time<br>
                            <b>Exit:</b> $exit_time<br>
                            <b>Duration:</b> $duration_hours hour(s)<br>
                            <b>Rate:</b> ₹$rate/hr<br>
                            <b>Total:</b> ₹$total_amount<br><br>

                            <form method='POST'>
                                <input type='hidden' name='qr_data' value='{$qr_data}'>
                                <input type='hidden' name='confirm_payment' value='1'>
                                <label><b>Select Payment Method:</b></label>
                                <select name='payment_method' id='payment_method' class='form-control' onchange='toggleUPI()' required>
                                    <option value='Cash'>Cash</option>
                                    <option value='Card'>Card</option>
                                    <option value='UPI'>UPI</option>
                                </select><br>

                                <div id='upi_section' style='display:none; text-align:center;'>
                                    <h6>Scan & Pay via UPI (Omni Card)</h6>
                                    <img src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($redirect_link) . "' alt='UPI QR Code'><br>
                                    <small>UPI ID: $business_upi_id</small><br><br>
                                    <label>Enter Transaction ID (after payment):</label>
                                    <input type='text' name='transaction_id' class='form-control' placeholder='UPI Transaction ID'>
                                </div>

                                <button type='submit' class='btn btn-success mt-3 w-100'>💳 Confirm Payment & Exit</button>
                            </form>
                        </div>

                        <script>
                            function toggleUPI() {
                                var method = document.getElementById('payment_method').value;
                                document.getElementById('upi_section').style.display = (method === 'UPI') ? 'block' : 'none';
                            }
                        </script>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>⚠️ Entry record not found for this ticket.</div>";
                }
            }
        } else {
            echo "<div class='alert alert-warning'>⚠️ Ticket not found.</div>";
        }
    }

    // 🟢 2️⃣ Registered Users (with r_id)
    elseif (!empty($r_id)) {
        $sql_check = "SELECT in_out FROM in_out_log WHERE r_id = '$r_id' ORDER BY id DESC LIMIT 1";
        $result_check = $mysqli->query($sql_check);
        $in_out = ($result_check && ($row = $result_check->fetch_assoc()) && $row['in_out'] == 1) ? "0" : "1";

        $sql_insert = "INSERT INTO in_out_log 
                        (r_id, in_out, date_time, user_name, contact, vehicle_no, Vehicle_type)
                        VALUES ('$r_id', '$in_out', '$date_time', '$user_name', '$contact', '$vehical_no', '$vehical_type')";
        if ($mysqli->query($sql_insert)) {
            echo "<div class='alert alert-success'>✅ Log saved successfully (Registered Entry).</div>";
        }
    }

    // 🟢 3️⃣ Manual entries (no r_id)
    else {
        if (empty($vehical_no)) {
            echo "<div class='alert alert-warning'>⚠️ Vehicle number is required.</div>";
        } else {
            $ticket_id = 'TKT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            $qr_folder = 'manual_qr/';
            if (!file_exists($qr_folder)) mkdir($qr_folder, 0777, true);

            $qr_filename = $qr_folder . $ticket_id . '.png';
            $qr_data = "Ticket ID: $ticket_id\nName: $user_name\nVehicle: $vehical_no\nDate: $date_time";
            QRcode::png($qr_data, $qr_filename, QR_ECLEVEL_L, 4);

            $sql_manual = "INSERT INTO manual_in_out 
                          (ticket_id, in_out, date_time, user_name, contact, vehicle_no, vehicle_type, qr_code, is_used)
                          VALUES ('$ticket_id', '$in_out', '$date_time', '$user_name', '$contact', '$vehical_no', '$vehical_type', '$qr_filename', 0)";
            if ($mysqli->query($sql_manual)) {
                echo "<div class='alert alert-success'>
                        ✅ Manual entry added successfully!<br>
                        <b>Ticket ID:</b> $ticket_id<br>
                        <img src='$qr_filename' width='120'><br>
                        <small>Scan this QR for exit</small><br><br>
                        <a href='print_ticket.php?ticket_id=$ticket_id' target='_blank' class='btn btn-primary mt-2'>🖨️ Print Ticket</a>
                      </div>";
            } else {
                echo "<div class='alert alert-danger'>❌ Error: " . $mysqli->error . "</div>";
            }
        }
    }
}
?>


<!-- ============================ FRONTEND FORM ============================ -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8"><h4 class="card-title">Create / Scan IN - OUT LOG</h4></div>
                    <div class="col-md-4"><a href="viewlot.php" class="btn btn-primary me-2">View</a></div>
                </div>

                <form method="post" id="scanForm">
                    <div class="form-group d-flex" style="margin-bottom: 20px;">
                        <label for="r_id" class="w-25">Registered ID</label>
                        <input type="text" name="r_id" id="r_id" class="form-control" placeholder="Registered ID">
                        <a onclick="qrread()" class="btn btn-primary ms-2">QR Scan</a>
                    </div>

                    <div id="output" style="margin-bottom: 10px;"></div>
                    <div id="reader" style="width: 300px;"></div>
                    <input type="hidden" name="qr_data" id="qr_data">

                    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
                    <script>
                        const qrReader = new Html5Qrcode("reader");
                        function qrread() {
                            qrReader.start(
                                { facingMode: "environment" },
                                { fps: 60, qrbox: 250 },
                                (decodedText) => {
                                    qrReader.stop();
                                    if (decodedText.includes("TKT-")) {
                                        document.getElementById("qr_data").value = decodedText;
                                        document.getElementById("scanForm").submit();
                                    } else {
                                        document.getElementById("r_id").value = decodedText;
                                        fetch("get-contact.php", {
                                            method: "POST",
                                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                            body: "qr_data=" + encodeURIComponent(decodedText)
                                        })
                                        .then(res => res.json())
                                        .then(data => {
                                            const output = document.getElementById("output");
                                            if (data.error) {
                                                output.innerHTML = "<span style='color:red;'>User not found.</span>";
                                            } else {
                                                document.getElementById("contect").value = data.contact;
                                                document.getElementById("user_name").value = data.user_name;
                                                output.innerHTML = "<strong>QR scanned successfully.</strong>";
                                            }
                                        });
                                    }
                                }
                            );
                        }
                    </script>

                    <div class="form-group">
                        <label>Date & Time</label>
                        <input type="hidden" name="date_time" id="date_time" required>
                        <input type="text" id="date_time_display" class="form-control" readonly>
                        <script>
                            function updateDateTime() {
                                const now = new Date();

                                const year = now.getFullYear();
                                const month = String(now.getMonth() + 1).padStart(2, '0');
                                const day = String(now.getDate()).padStart(2, '0');

                                const hours = String(now.getHours()).padStart(2, '0');
                                const minutes = String(now.getMinutes()).padStart(2, '0');
                                const seconds = String(now.getSeconds()).padStart(2, '0');

                                const finalDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

                                document.getElementById("date_time").value = finalDate;
                                document.getElementById("date_time_display").value = finalDate;
                            }

                            updateDateTime();
                            setInterval(updateDateTime, 1000);
                        </script>
                    </div>

                    <div class="form-group"><label>User Name</label><input type="text" name="user_name" id="user_name" class="form-control" required></div>
                    <div class="form-group"><label>Contact</label><input type="number" name="contect" id="contect" class="form-control" required></div>
                    <div class="form-group"><label>Vehicle No.</label><input type="text" name="vehical_no" id="vehical_no" class="form-control"></div>

                    <div class="form-group">
                        <label>Parking Type</label>
                        <select class="form-control" name="vehicle_type" id="parkingtype" required>
                            <option value="">Select Parking Type</option>
                            <?php while ($type = $types_result->fetch_assoc()) { ?>
                                <option value="<?= $type['id'] ?>"><?= $type['type_name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Submit</button>
                    <button type="reset" class="btn btn-light">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>