<?php
    include '../php/connection.php';    
    $post_ap_id = $_REQUEST['sr_id'];
    $check = $_REQUEST['check'];
    $iname = $_REQUEST['iname'];
    $quan = $_REQUEST['quan'];
    $pds = $_REQUEST['pds'];

    if ($check == "yes") {
        // Get admin stock from tbl_stock
        $sql_admin_stock = "SELECT * FROM tbl_stock WHERE stock_name='$iname'";
        $result_admin_stock = mysqli_query($conn, $sql_admin_stock);
        $admin_row = mysqli_fetch_array($result_admin_stock);
        $admin_quantity = $admin_row['quantity'];

        // Check if enough stock is available
        if ($admin_quantity < $quan) {
            echo "<script>alert('Error: Not enough stock in admin inventory. Available: $admin_quantity'); window.location.href='applied_stock.php';</script>";
            exit();
        }

        // Update status to 'yes' in tbl_apply_stock
        $sql = "UPDATE tbl_apply_stock SET status='yes' WHERE ap_id='$post_ap_id'";

        // Get existing distributor stock from tbl_pds
        $sql3 = "SELECT * FROM tbl_pds WHERE pds_no='$pds' AND stock_name='$iname'";
        $result4 = mysqli_query($conn, $sql3);
        $rows = mysqli_fetch_array($result4);
        $quantity = $rows['quantity'];
        $new_quantity = $quan + $quantity;

        // Update distributor stock in tbl_pds
        $sql1 = "UPDATE tbl_pds SET quantity='$new_quantity' WHERE pds_no='$pds' AND stock_name='$iname'";

        // Calculate updated quantity for admin
        $updated_admin_quantity = $admin_quantity - $quan;

        // Update admin stock in tbl_stock
        $sql_update_admin_stock = "UPDATE tbl_stock SET quantity='$updated_admin_quantity' WHERE stock_name='$iname'";

        if (mysqli_query($conn, $sql) && mysqli_query($conn, $sql1) && mysqli_query($conn, $sql_update_admin_stock)) {
            echo '<script>alert("Status Updated Successfully.");</script>';
            $sql2 = "SELECT tbl_apply_stock.ap_id ,tbl_apply_stock.date ,tbl_stock.stock_id ,tbl_stock.stock_name, tbl_distributor.fname,
            tbl_distributor.mname, tbl_distributor.lname, tbl_distributor.pds_no, tbl_apply_stock.quantity, tbl_apply_stock.status
            FROM tbl_stock, tbl_distributor, tbl_apply_stock 
            WHERE tbl_apply_stock.pds_no=tbl_distributor.pds_no AND tbl_apply_stock.stock_id=tbl_stock.stock_id AND tbl_apply_stock.ap_id='$post_ap_id'
            ORDER BY tbl_apply_stock.ap_id";
            $result3 = mysqli_query($conn, $sql2);
            $set = mysqli_fetch_all($result3, MYSQLI_ASSOC);
        }
    } else {
        $sql2 = "SELECT tbl_apply_stock.ap_id ,tbl_apply_stock.date ,tbl_stock.stock_id ,tbl_stock.stock_name, tbl_distributor.fname,
        tbl_distributor.mname, tbl_distributor.lname, tbl_distributor.pds_no, tbl_apply_stock.quantity, tbl_apply_stock.status
        FROM tbl_stock, tbl_distributor, tbl_apply_stock 
        WHERE tbl_apply_stock.pds_no=tbl_distributor.pds_no AND tbl_apply_stock.stock_id=tbl_stock.stock_id AND tbl_apply_stock.ap_id='$post_ap_id'
        ORDER BY tbl_apply_stock.ap_id";
        $result3 = mysqli_query($conn, $sql2);
        $set = mysqli_fetch_all($result3, MYSQLI_ASSOC);
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title> Admin | E-Ration </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<div class="w3-container">
    <div class="w3-center w3-margin">
        <img src="./images/l2.png" width="100px" height="70px" alt="">
        <img src="./images/l1.png" alt="" width="170px" height="70px">
    </div>
    <h2 style="background-color:#0A2558;" class="w3-card w3-text-orange w3-padding-24 w3-round-large w3-center"
        style="text-shadow:1px 1px 0 #444">
        <b>Applied Stock Details of Applied Id - <?php echo $post_ap_id; ?></b>
    </h2>
    <div class="w3-container w3-margin">
        <br>
        <h4><b>Date : </b><?php date_default_timezone_set("Asia/Kolkata"); echo date("d-m-y"); ?>
            <p class="w3-right"><b>Time : </b><?php echo date("h:i:s a"); ?></p>
        </h4>
        <?php foreach ($set as $row): ?>
            <p class="w3-large w3-text-dark-blue">
                <b><?php echo "Dear, " . $row['fname'] . " " . $row['mname'] . " " . $row['lname']; ?> <br></b>
            </p>
            <table class="table w3-margin-top">
                <thead>
                    <tr>
                        <th>Applied ID</th>
                        <th>Date</th>
                        <th>PDS No.</th>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="w3-padding" data-label="Applied ID"><?php echo $post_ap_id; ?></td>
                        <td class="w3-padding" data-label="Date"><?php echo $row['date']; ?></td>
                        <td class="w3-padding" data-label="PDS No."><?php echo $row['pds_no']; ?></td>
                        <td class="w3-padding" data-label="Item ID"><?php echo $row['stock_id']; ?></td>
                        <td class="w3-padding" data-label="Item Name"><?php echo $row['stock_name']; ?></td>
                        <td class="w3-padding" data-label="Quantity">
                            <?php echo $row['quantity'] . ($row['stock_name'] != "Oil" ? " KG" : " Litre"); ?>
                        </td>
                        <td class="w3-padding" data-label="Status"><?php echo $row['status']; ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endforeach; ?>
        <div class="w3-center">
            <button type="button" style="background-color:#0A2558;"
                class="w3-button w3-round-large w3-text-white w3-padding w3-margin-top" id="home">
                <a href="applied_stock.php" style="text-decoration: none;">Home</a>
            </button>
            <button type="button" style="background-color:#0A2558;"
                class="w3-button w3-round-large w3-text-white w3-padding w3-margin-top" onclick="myFunction()"
                id="printpagebutton">Print Receipt</button>
        </div>
    </div>
    <script>
    function myFunction() {
        var printButton = document.getElementById("printpagebutton");
        printButton.style.visibility = 'hidden';
        window.print();
        printButton.style.visibility = 'visible';
    }
    </script>
</div>
</body>
</html>
