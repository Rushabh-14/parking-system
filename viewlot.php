<?php
    include_once('header.php');
    include_once('sidebar.php');
    include('dbconn/dbconn.php');

    $sqldata = $mysqli->query("SELECT in_out_log.*, parking_type.type_name FROM in_out_log LEFT JOIN parking_type ON in_out_log.Vehicle_type = parking_type.id ORDER BY in_out_log.id ASC;");

    if (isset($_GET['action']) == 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        
        $mysqli->query("DELETE FROM `parking_lot` WHERE id = '$id'");

    }
?>

<div class="row">
              
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Hoverable Table</h4>
                <p class="card-description"> Add class <code>.table-hover</code></p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>register Id</th>
                                <th>IN OUT</th>
                                <th>DATE TIME</th>
                                <th>USER NAME</th>
                                <th>CONTECT</th>
                                <th>VEHICLE NO...</th>
                                <th>VEHICLE TYPE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($results = $sqldata -> fetch_assoc()){
                                    echo "<tr><td>".$results['id']."</td>";
                                    echo "<td>".$results['r_id']."</td>";
                                    echo "<td>".$results['in_out']."</td>";
                                    echo "<td>".$results['date_time']."</td>";
                                    echo "<td>".$results['user_name']."</td>";
                                    echo "<td>".$results['contact']."</td>";
                                    echo "<td>".$results['vehicle_no']."</td>";
                                    echo "<td>".$results['type_name']."</td>";
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