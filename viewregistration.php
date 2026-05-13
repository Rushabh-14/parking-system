<?php
    include_once('header.php');
    include_once('sidebar.php');
    include('dbconn/dbconn.php');

    $sqldata = $mysqli->query("SELECT register_user.id, register_user.qr_id, register_user.shop_no, register_user.user_name, register_user.contact, register_user.parking_lot_id, register_user.qr, parking_type.type_name, parkin_location.location_name 
    FROM register_user 
    INNER JOIN parking_lot ON register_user.parking_lot_id = parking_lot.id 
    INNER JOIN parking_type ON parking_lot.parkintype = parking_type.id 
    INNER JOIN parkin_location ON parking_lot.parkinglocation = parkin_location.id;");


    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        
        $result = $mysqli->query("SELECT qr FROM `register_user` WHERE id = '$id'");
        $user   = $result->fetch_assoc();
        $mysqli->query("DELETE FROM `register_user` WHERE id = '$id'");
        
        $qr_file = $user['qr']; 
        echo $qr_file;

        if (file_exists($qr_file)) {
            unlink($qr_file);
        }
    }
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Registered Users</h4>
                <p class="card-description"> Parking users table with actions </p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>QR Id</th>
                                <th>Shop No</th>
                                <th>User Name</th>
                                <th>Contact No</th>
                                <th>Parking Lot</th>
                                <th>Parking Pass</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($user = $sqldata->fetch_assoc()){
                                    echo "<tr>";
                                    echo "<td>".$user['id']."</td>";
                                    echo "<td>".$user['qr_id']."</td>";
                                    echo "<td>".$user['shop_no']."</td>";
                                    echo "<td>".$user['user_name']."</td>";
                                    echo "<td>".$user['contact']."</td>";
                                    echo "<td>".$user['type_name']. " , " .$user['location_name']."</td>";

                                    echo "<td><a href='generate_pass.php?name=" . urlencode($user['user_name']) . "&contact=" . urlencode($user['contact']) . "&vehicle=" . urlencode($user['qr_id']) ."&id=" . urlencode($user['id']) . "' class='btn btn-primary me-2'>Download Pass</a></td>";

                                    echo "<td>
                                            <a href='?action=delete&id=" . $user['id'] . "' class='btn btn-danger me-2' onclick=\"return confirm('Are you sure you want to delete this item?');\">Delete</a>
                                            <a href='edit_user.php?id=".$user['id']."' class='btn btn-warning'>Edit</a>
                                          </td>";
                                    echo "</tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> 
</div>

<?php
    include_once('footer.php');
?>
