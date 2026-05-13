<?php
    include_once('header.php');
    include_once('sidebar.php');
    include('dbconn/dbconn.php'); // Database connection

    function getTotalEntries($mysqli) {
    $sql = "
        SELECT log_date, SUM(total_entries) AS total_entries
        FROM (
            SELECT DATE(date_time) AS log_date, COUNT(*) AS total_entries
            FROM in_out_log
            WHERE in_out = 1
            GROUP BY DATE(date_time)

            UNION ALL

            SELECT DATE(date_time) AS log_date, COUNT(*) AS total_entries
            FROM manual_in_out
            WHERE in_out = 1
            GROUP BY DATE(date_time)
        ) AS combined
        GROUP BY log_date
        ORDER BY log_date DESC;
    ";

    $result = $mysqli->query($sql);

    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[$row['log_date']] = $row['total_entries'];
    }

    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    return [
        'today' => $entries[$today] ?? 0,       // correct
        'yesterday' => $entries[$yesterday] ?? 0
    ];
}


    function getChangeInfo($today, $yesterday) {
        if ($yesterday > 0) {
            $change = (($today - $yesterday) / $yesterday) * 10;
        } else {
            $change = 0;
        }

        if ($change > 0) {
            return ["class" => "text-success", "icon" => "mdi-menu-up", "percent" => round($change, 2)];
        } elseif ($change < 0) {
            return ["class" => "text-danger", "icon" => "mdi-menu-down", "percent" => round($change, 2)];
        } else {
            return ["class" => "text-muted", "icon" => "mdi-minus", "percent" => 0];
        }
    }

    /* 🔹 FUNCTION 3: Count vehicles currently inside */
    function getVehicleCount($mysqli, $vehicle_type_id) {
        $sql = "SELECT COUNT(*) AS total
                FROM (
                    SELECT r_id
                    FROM in_out_log
                    WHERE vehicle_type = ?
                    GROUP BY r_id
                    HAVING SUBSTRING_INDEX(GROUP_CONCAT(in_out ORDER BY date_time DESC), ',', 1) = '1'

                    UNION ALL

                    SELECT ticket_id AS r_id
                    FROM manual_in_out
                    WHERE vehicle_type = ?
                    GROUP BY ticket_id
                    HAVING SUBSTRING_INDEX(GROUP_CONCAT(in_out ORDER BY date_time DESC), ',', 1) = '1'
                ) AS active_vehicles";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ii", $vehicle_type_id, $vehicle_type_id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }

    /* ⚙️ MAIN LOGIC */
    $entries = getTotalEntries($mysqli);
    $today_entries = $entries['today'];
    $yesterday_entries = $entries['yesterday'];
    
    $change_info = getChangeInfo($today_entries, $yesterday_entries);
    
    $total_two_wheeler = getVehicleCount($mysqli, 2);
    $total_three_wheeler = getVehicleCount($mysqli, 3);
    $total_four_wheeler = getVehicleCount($mysqli, 1);

    // 🏍️ two Wheeler Status
    if ($total_two_wheeler > 0) {
        $vehicle_status_class = "text-success";
        $vehicle_status_icon = "mdi-motorbike";
        $vehicle_status_text = "Currently Inside";
    } else {
        $vehicle_status_class = "text-danger";
        $vehicle_status_icon = "mdi-alert-circle-outline";
        $vehicle_status_text = "No Two Wheelers Inside";
    }

    // 🛺 Three Wheeler Status
    if ($total_three_wheeler > 0) {
        $three_status_class = "text-success";
        $three_status_icon = "mdi-rickshaw";
        $three_status_text = "Currently Inside";
    } else {
        $three_status_class = "text-danger";
        $three_status_icon = "mdi-alert-circle-outline";
        $three_status_text = "No Three Wheelers Inside";
    }

    // 🚗 Four Wheeler Status
    if ($total_four_wheeler > 0) {
        $four_status_class = "text-success";
        $four_status_icon = "mdi-car";
        $four_status_text = "Currently Inside";
    } else {
        $four_status_class = "text-danger";
        $four_status_icon = "mdi-alert-circle-outline";
        $four_status_text = "No Four Wheelers Inside";
    }
?>

<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active ps-0" id="home-tab" data-bs-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">Overview</a>
                    </li>
                </ul>
                <div>
                    <div class="btn-wrapper">
                        <a href="#" class="btn btn-otline-dark align-items-center"><i class="icon-share"></i> Share</a>
                        <a href="#" class="btn btn-otline-dark"><i class="icon-printer"></i> Print</a>
                        <a href="#" class="btn btn-primary text-white me-0"><i class="icon-download"></i> Export</a>
                    </div>
                </div>
            </div>
            <div class="tab-content tab-content-basic">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="statistics-details d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="statistics-title">Today's Total Entries</p>
                                    <h3 class="rate-percentage"><?php echo $today_entries; ?></h3>
                                    <p class="<?php echo $change_info['class']; ?> d-flex">
                                        <i class="mdi <?php echo $change_info['icon']; ?>"></i>
                                        <span><?php echo $change_info['percent']; ?>%</span>
                                    </p>
                                </div>
                                <!-- Two Wheeler -->
                                <div>
                                    <p class="statistics-title">Two Wheeler Vehicles</p>
                                    <h3 class="rate-percentage"><?php echo $total_two_wheeler; ?></h3>
                                    <p class="<?php echo $vehicle_status_class; ?> d-flex">
                                        <i class="mdi <?php echo $vehicle_status_icon; ?>"></i>
                                        <span><?php echo $vehicle_status_text; ?></span>
                                    </p>
                                </div>

                                <!-- Three Wheeler -->
                                <div>
                                    <p class="statistics-title">Three Wheeler Vehicles</p>
                                    <h3 class="rate-percentage"><?php echo $total_three_wheeler; ?></h3>
                                    <p class="<?php echo $three_status_class; ?> d-flex">
                                        <i class="mdi <?php echo $three_status_icon; ?>"></i>
                                        <span><?php echo $three_status_text; ?></span>
                                    </p>
                                </div>

                                <!-- Four Wheeler -->
                                <div>
                                    <p class="statistics-title">Four Wheeler Vehicles</p>
                                    <h3 class="rate-percentage"><?php echo $total_four_wheeler; ?></h3>
                                    <p class="<?php echo $four_status_class; ?> d-flex">
                                        <i class="mdi <?php echo $four_status_icon; ?>"></i>
                                        <span><?php echo $four_status_text; ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
<?php
include_once('footer.php');
?>