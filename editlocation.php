<?php
    include_once('header.php');
    include_once('sidebar.php');
    include('dbconn/dbconn.php');
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $location_name = $_POST['location_name'];
        $isactive = $_POST['isactive'];

        if (!empty($location_name)) {

            $sql = "UPDATE `parkin_location` SET `location_name`='$location_name',`isactive`='$isactive' WHERE 1";
            
            if ($mysqli->query($sql) === TRUE) {
                echo "Location Updated Successfully.";
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
                </div>
            
            <!--<p class="card-description"> Basic form layout </p>-->
                <form class="forms-sample" method="post">
                    <div class="form-group">
                        <label for="location_name">Parking Location</label>
                        <input type="text" name="location_name" id="location_name" class="form-control" placeholder="Paking Location">
                    </div>
                    <div class="form-group">
                        <label for="isactive">IS Active</label>
                        <input type="number" name="isactive" id="isactive" class="form-control" placeholder="IS Active" value="1">
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