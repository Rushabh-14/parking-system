<?php
    include_once('header.php');
    include_once('sidebar.php');
    include('dbconn/dbconn.php');
    include 'phpqrcode/qrlib.php';

    $result = $mysqli->query("SELECT parking_lot.id, parking_lot.no_of_lots, parking_type.type_name, parkin_location.location_name FROM parking_lot INNER JOIN parking_type ON parking_lot.parkintype = parking_type.id INNER JOIN parkin_location ON parking_lot.parkinglocation = parkin_location.id;");
    $result1 = $mysqli->query("SHOW TABLE STATUS LIKE 'register_user'");
    $row = $result1->fetch_assoc();

    $last_id = $row['Auto_increment'];
    if ($last_id >= 1) {
        $new_qr_id = (1000 + $last_id) . "BF";
    } else {
        $new_qr_id = "1001BF";
    }
?>

<link rel="stylesheet" href="assets/css/registration.css">

<div class="wrapper">
    <div class="title-text">
        <div class="title login">User Registration</div>
    </div>
    <div class="text-end">
        <a href="viewregistration.php" class="btn btn-primary me-2">View</a>
    </div>
    <hr>
    <div class="form-container">
        <div class="form-inner">
            
            <form method="post" class="signup">
                <div class="field">
                    <input type="text" placeholder="QR ID" name='qr_id' value="<?php echo $new_qr_id; ?>" readonly>
                </div>
                <div class="field">
                    <input type="number" placeholder="Shop Number" name='shop_number' required>
                </div>
                <div class="field">
                    <input type="text" placeholder="User Name" name='user_name' required>
                </div>
                <div class="field">
                    <input type="number" placeholder="Contact Number" name='contact_number' required>
                </div>
                <div class="field">
                    <select class="form-control" name="parking_lot_id" id="parking_lot_id" required>
                        <option value="">Select parking lot</option>
                        <?php while ($data = $result->fetch_assoc()) { ?>
                            <option value="<?= $data['id']."|".$data['no_of_lots'] ?>"><?= $data['location_name']."|".$data['type_name'] ?></option>
                        <?php
                            } 
                        ?>
                    </select>
                </div>
                    <?php
                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            $value = $_POST['parking_lot_id'];
                            $exploded_value = explode('|', $value);
                            $id = $exploded_value[0];
                            $no_of_lot = $exploded_value[1];
                        }
                    ?>
                <div class="field btn">
                    <div class="btn-layer"></div>
                    <input type="submit" value="Submit">
                </div>
            </form>
        </div>
    </div>
</div>

<?php
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $qr_id = $_POST['qr_id'];
        $shop_number = $_POST['shop_number'];
        $user_name = $_POST['user_name'];
        $contect_number = $_POST['contact_number'];
        $parking_lot_id = $_POST['parking_lot_id'];

        $parking_lot_id = $id;
        $total_lot = $no_of_lot;

        $ocuupied_lot = $mysqli->query("SELECT * FROM `register_user` WHERE parking_lot_id = $id");
        $occupied_count = 0;
        
        while ($lots = $ocuupied_lot->fetch_assoc()) {
            $occupied_count++;
        }

        echo "$total_lot / $occupied_count</p>";

        if (!empty($qr_id) && !empty($shop_number) && !empty($user_name) && !empty($contect_number) && !empty($parking_lot_id)) {
            if($total_lot > $occupied_count){
                
                $count_sql = "SELECT COUNT(*) AS total FROM `register_user` WHERE user_name = '$user_name'";
                $count_result = $mysqli->query($count_sql);
                
                if ($count_result) {
                    $row = $count_result->fetch_assoc();
                    $occupied_count = $row['total']; // integer

                    if ($occupied_count > 0) {
                        echo "User name already exists. Try another user name.";
                    } else {
                        // print_r($_POST);
                        $text = "$qr_id";
                        $path = 'images/';
                        $file = $path.uniqid().".png";

                        $ecc = 'L';
                        $pixel_Size = 20;
                        $frame_Size = 2;

                        QRcode::png($text, $file, $ecc, $pixel_Size, $frame_Size);

                        $sql = "INSERT INTO `register_user`(`qr_id`, `shop_no`, `user_name`, `contact`, `parking_lot_id` , `qr`) VALUES ('$new_qr_id','$shop_number','$user_name','$contect_number','$parking_lot_id','$file')";
                        
                        if ($mysqli->query($sql) === TRUE) {
                            echo "Location created successfully.";
                        } else {
                            echo "ERROR: Could not execute query. " . $mysqli->error;
                        }
                    }
                } else {
                    die("Count query failed: " . $mysqli->error);
                }
            }
            else{
                echo "Parking lot is full, choose another parking lot";
            }
        } else {
            echo "Please fill in the location name.";
        }
    }

    include_once('footer.php');
?>