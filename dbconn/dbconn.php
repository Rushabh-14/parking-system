<?php
    $mysqli = new mysqli("localhost", "root", "", "parking"); 

    if ($mysqli->connect_error) {
        die("ERROR: Could not connect. " . $mysqli->connect_error);
    }
?>