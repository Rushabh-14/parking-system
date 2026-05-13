<?php
    include_once('header.php');
    include_once('sidebar.php');
    include('dbconn/dbconn.php');
    $types_result = $mysqli->query("SELECT type_name , id FROM parking_type");
    $locations_result = $mysqli->query("SELECT location_name , id FROM parkin_location");
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $parkintype = $_POST['parking_type'];
        $parking_location = $_POST['parking_location'];
        $no_of_lots = $_POST['no_of_lots'];

        if (!empty($parkintype) && !empty($parkintype) && $no_of_lots) {

            $sql = "INSERT INTO `parking_lot`(`parkintype`, `parkinglocation`, `no_of_lots`, `isactive`) VALUES ('$parkintype' , '$parking_location', '$no_of_lots' ,1)";
            
            if ($mysqli->query($sql) === TRUE) {
                echo "Location created successfully.";
            } else {
                echo "ERROR: Could not execute query. " . $mysqli->error;
            }
        } else {
            echo "Please fill in the location name.";
        }
    }

?>
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="card-title">Ceate Paking Loction</h4>
                    </div>
                    <div class="col-md-4">
                        <a href="viewlot.php" class="btn btn-primary me-2">View</a>
                    </div>
                </div>
                
                <form method="post">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="parkingtype"  style="text-align: start;">Parking Type</label>
                        <select class="form-control" name="parking_type" id="parkingtype" required>
                            <option value="">Select Parking Type</option>
                            <?php while ($type = $types_result->fetch_assoc()) { ?>
                                    <option value="<?= $type['id'] ?>"><?= $type['type_name'] ?></option>
                            <?php 
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="parkinglocation"  style="text-align: start;">Parking Location</label>
                        <select class="form-control" name="parking_location" id="parkinglocation" required>
                            <option value="">Select Parking Locations</option>
                            <?php while ($location = $locations_result->fetch_assoc()) { ?>
                                <option value="<?= $location['id'] ?>"><?= $location['location_name'] ?></option>
                            <?php
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="no_of_lots">No of Lots</label>
                        <input type="number" name="no_of_lots" id="no_of_lots" class="form-control" placeholder="No of Lots">
                    </div>
                    <button type="submit" class="btn btn-primary me-2">Submit</button>
                    <button class="btn btn-light">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
    include_once('footer.php');
?>