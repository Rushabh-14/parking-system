<?php
    include_once('header.php');
    include_once('sidebar.php');
    include('dbconn/dbconn.php');

    $sqldata = $mysqli->query("SELECT * FROM `parking_type` WHERE 1");

    if (isset($_GET['action']) == 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        
        $mysqli->query("DELETE FROM parking_type WHERE id = '$id'");

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
                            <th>User</th>
                            <th>Product</th>
                            <th>Sale</th>
                            <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--<tr>
                                <td>Jacob</td>
                                <td>Photoshop</td>
                                <td class="text-danger"> 28.76% <i class="ti-arrow-down"></i></td>
                                <td><label class="badge badge-danger">Pending</label></td>
                            </tr>-->
                            <?php
                                while($results = $sqldata -> fetch_assoc()){
                                    echo "<tr><td>".$results['id']."</td>";
                                    echo "<td>".$results['type_name']."</td>";
                                    echo "<td>".$results['isactive']."</td>";
                                    echo "<td><a href='?action=delete&id=" . $results['id'] . "' class='btn btn-primary me-2' onclick=\"return confirm('Are you sure you want to delete this item?');\">Delete</a> &nbsp; <a class='btn btn-primary' href='edittype.php?id=$results[id]'>Edit</a></td></tr>";
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