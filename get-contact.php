<?php
    include('dbconn/dbconn.php');

    if (isset($_POST['qr_data'])) {
        $qr_data = mysqli_real_escape_string($mysqli, $_POST['qr_data']); // ✅ use $mysqli

        $sql = "SELECT user_name, contact FROM register_user WHERE qr_id = '$qr_data'";
        $result = mysqli_query($mysqli, $sql); // ✅ use $mysqli

        if ($result && mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "No user found"]);
        }
    }
        
?>
