<?php
    include_once('header.php');
    include('dbconn/dbconn.php');

    if (!isset($_GET['ticket_id'])) {
        die("❌ Ticket ID not provided.");
    }

    $ticket_id = $_GET['ticket_id'];

    // Fetch ticket details from manual_in_out
    $sql = "SELECT * FROM manual_in_out WHERE ticket_id = '$ticket_id' LIMIT 1";
    $result = $mysqli->query($sql);

    if ($result->num_rows === 0) {
        die("❌ Ticket not found.");
    }

    $ticket = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Parking Ticket -
        <?= $ticket['ticket_id'] ?>
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .ticket-card {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }

        .ticket-card h3 {
            text-align: center;
            color: #007bff;
        }

        .ticket-details {
            margin-top: 15px;
            font-size: 16px;
        }

        .qr-code {
            text-align: center;
            margin-top: 20px;
        }

        @media print {
            .btn {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="ticket-card">
        <h3>🚗 Parking Ticket</h3>
        <hr>
        <div class="ticket-details">
            <p><strong>Ticket ID:</strong>
                <?= $ticket['ticket_id'] ?>
            </p>
            <p><strong>User Name:</strong>
                <?= htmlspecialchars($ticket['user_name']) ?>
            </p>
            <p><strong>Contact:</strong>
                <?= htmlspecialchars($ticket['contact']) ?>
            </p>
            <p><strong>Vehicle No:</strong>
                <?= htmlspecialchars($ticket['vehicle_no']) ?>
            </p>
            <p><strong>Vehicle Type:</strong>
                <?= htmlspecialchars($ticket['vehicle_type']) ?>
            </p>
            <p><strong>Date & Time:</strong>
                <?= htmlspecialchars($ticket['date_time']) ?>
            </p>
        </div>
        <div class="qr-code">
            <img src="<?= $ticket['qr_code'] ?>" alt="QR Code" width="180">
        </div>
        <div class="text-center mt-4">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Ticket</button>
            <a href="in-out-logs.php" class="btn btn-secondary">⬅️ Back</a>
        </div>
    </div>

</body>

</html>

<?php include_once('footer.php'); ?>
