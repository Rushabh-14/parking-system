<?php
include("phpqrcode/qrlib.php");
include("dbconn/dbconn.php");
include("fpdf186/fpdf.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // secure input

    // Fetch user
    $result = $mysqli->query("SELECT `id`, `qr_id`, `shop_no`, `user_name`, `contact`, `parking_lot_id`, `qr` 
                              FROM `register_user` WHERE id = $id");
    $user = $result->fetch_assoc();

    if ($user) {
        $r_id      = $user['qr_id'];
        $user_name = $user['user_name'];
        $contact   = $user['contact'];
        $shop_no   = $user['shop_no'];

        $qr_file = $user['qr'];

        // If "download" parameter is set, send PDF
        if (isset($_GET['download']) && $_GET['download'] == 1) {
            $pdf = new FPDF("P", "mm", [80, 60]); // Small card size
            $pdf->AddPage();

            // Border
            $pdf->Rect(2, 2, 56, 76);

            // Title
            $pdf->SetFont("Arial", "B", 14);
            $pdf->Cell(0, 10, "Parking Pass", 0, 1, "C");

            // Left column for text
            $pdf->SetFont("Arial", "", 11);
            $pdf->SetXY(8, 20); // move to left side
            $pdf->Cell(0, 6, "ID: " . $r_id, 0, 1);
            $pdf->SetX(8);
            $pdf->Cell(0, 6, "Name: " . $user_name, 0, 1);
            $pdf->SetX(8);
            $pdf->Cell(0, 6, "Contact: " . $contact, 0, 1);
            $pdf->SetX(8);
            $pdf->Cell(0, 6, "Shop No: " . $shop_no, 0, 1);

            // QR on right side
            $pdf->Image($qr_file, 15, 45, 30, 30);

            // Download PDF
            $filename = "C:/xampp/htdocs/php/parking/parking/pass(pdf)/" . $r_id . ".pdf";
            $pdf->Output("F", $filename);
            exit;
        }

        // Else show HTML card preview
        ?>
        <html>
        <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .id-card {
                width: 300px;
                border: 2px solid #000;
                border-radius: 15px;
                padding: 15px;
                text-align: center;
                box-shadow: 0px 0px 5px #888;
                margin: 20px auto;
            }
            .id-card h2 { margin: 0; font-size: 18px; color: #2c3e50; }
            .id-card p { margin: 4px 0; font-size: 14px; }
            .qr { margin-top: 10px; }
            .qr img { margin-top: 10px;  }
            .btn {
                display: inline-block;
                margin-top: 10px;
                padding: 8px 12px;
                background: #2c3e50;
                color: #fff;
                text-decoration: none;
                border-radius: 5px;
            }
        </style>
        </head>
        <body>
            <div class='id-card'>
                <h2>Parking Pass</h2>
                <p><strong>ID:</strong> <?= $r_id ?></p>
                <p><strong>Name:</strong> <?= $user_name ?></p>
                <p><strong>Contact:</strong> <?= $contact ?></p>
                <p><strong>Shop No:</strong> <?= $shop_no ?></p>
                <div class='qr'>
                    <img src='<?= $qr_file ?>' width='200'>
                </div>
                <a href="?id=<?= $id ?>&download=1" class="btn">Download PDF</a>
                <a href="https://wa.me/?text=Here%20is%20your%20Parking%20Pass:%20http://localhost/php/parking/parking/pass(pdf)/<?= $r_id ?>.pdf" target="_blank" class="btn">
                    Send via WhatsApp
                </a>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "User not found.";
    }
} else {
    echo "Missing user id.";
}