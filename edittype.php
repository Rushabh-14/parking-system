<?php
    include_once('header.php');
    include_once('sidebar.php');
    include('dbconn/dbconn.php');
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $type_name = $_POST['type_name'];
        $isactive = $_POST['isactive'];

        if (!empty($type_name)) {

            $sql = "UPDATE `parking_type` SET `type_name`='$type_name',`isactive`='$isactive' WHERE 1";
            
            if ($mysqli->query($sql) === TRUE) {
                echo "Type Updated Successfully.";
            } else {
                echo "ERROR: Could not execute query. " . $mysqli->error;
            }
        } else {
            echo "Please fill in the Type name.";
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
                        <label for="type_name">Paking Type name</label>
                        <input type="text" name="type_name" id="type_name" class="form-control" placeholder="Paking Type">
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