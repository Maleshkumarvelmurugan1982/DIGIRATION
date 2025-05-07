<?php
include '../php/connection.php';	
$ap_id = $_REQUEST['sr_id'];

$sql2 = "SELECT tbl_apply_stock.ap_id, tbl_apply_stock.date, tbl_stock.stock_id, tbl_stock.stock_name, 
    tbl_distributor.fname, tbl_distributor.mname, tbl_distributor.lname, tbl_distributor.pds_no, 
    tbl_apply_stock.quantity, tbl_apply_stock.status
    FROM tbl_stock, tbl_distributor, tbl_apply_stock 
    WHERE tbl_apply_stock.pds_no = tbl_distributor.pds_no 
    AND tbl_apply_stock.stock_id = tbl_stock.stock_id 
    AND tbl_apply_stock.ap_id = '$ap_id'
    ORDER BY tbl_apply_stock.ap_id";

$result2 = mysqli_query($conn, $sql2);
$rows = mysqli_fetch_all($result2, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin | E-Ration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="w3-container">
        <div class="w3-center w3-margin no-print">
            <img src="./images/l2.png" width="100px" height="70px" alt="">
            <img src="./images/l1.png" alt="" width="170px" height="70px">
        </div>

        <h2 style="background-color:#0A2558;" class="w3-card w3-text-orange w3-padding-24 w3-round-large w3-center">
            <b>Applied Stock Details of Applied Id - <?php echo $ap_id; ?></b>
        </h2>

        <div class="w3-container w3-margin">
            <h4>
                <b>Date:</b> <?php date_default_timezone_set("Asia/Kolkata"); echo date("d-m-y"); ?>
                <span class="w3-right"><b>Time:</b> <?php echo date("h:i:s a"); ?></span>
            </h4>

            <?php foreach ($rows as $row): ?>
                <p class="w3-large w3-text-dark-blue">
                    <b>Dear, <?php echo "{$row['fname']} {$row['mname']} {$row['lname']}"; ?></b>
                </p>

                <table class="w3-table w3-bordered w3-margin-top">
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
                            <td><?php echo $row['ap_id']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['pds_no']; ?></td>
                            <td><?php echo $row['stock_id']; ?></td>
                            <td><?php echo $row['stock_name']; ?></td>
                            <td>
                                <?php echo $row['quantity']; ?>
                                <?php echo ($row['stock_name'] === 'Oil') ? 'Litre' : 'KG'; ?>
                            </td>
                            <td><?php echo ucfirst($row['status']); ?></td>
                        </tr>
                    </tbody>
                </table>

                <?php if ($row['status'] == "no"): ?>
                <div class="w3-center no-print w3-margin-top">
                    <form action="approve_stock.php" method="post">
                        <input type="hidden" name="sr_id" value="<?php echo $row['ap_id']; ?>">
                        <input type="hidden" name="iname" value="<?php echo $row['stock_name']; ?>">
                        <input type="hidden" name="quan" value="<?php echo $row['quantity']; ?>">
                        <input type="hidden" name="pds" value="<?php echo $row['pds_no']; ?>">
                        <label><input type="radio" name="check" value="yes" required> Approve</label>
                        <label><input type="radio" name="check" value="no"> Reject</label>
                        <br><br>
                        <button class="w3-button w3-dark-blue w3-round-large" name="btn-approve">Submit Decision</button>
                    </form>
                </div>
                <?php endif; ?>

                <div class="w3-center no-print w3-margin-top">
                    <button onclick="window.print()" class="w3-button w3-dark-grey w3-round-large">🖨️ Print</button>
                    <a href="applied_stock.php" class="w3-button w3-dark-blue w3-round-large">Home</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
