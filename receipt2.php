<?php
require './vendor/autoload.php';
require_once '../php/connection.php';

use Razorpay\Api\Api;

session_start();

$api = new Api('rzp_test_7mhCHMGw6A7Zdu', '9Fg8hKTeXhvUnMT92BzJ8shN');
$razorpayPaymentId = $_POST['razorpay_payment_id'];

$payment = $api->payment->fetch($razorpayPaymentId);

$success = false;

if (!isset($_SESSION['rationcard_no'])) {
    die("Error: Ration card number not found in session.");
}

$rationcard_no = $_SESSION['rationcard_no'];
$amount = $payment->amount / 100; // Razorpay returns amount in paise
date_default_timezone_set("Asia/Kolkata");
$currentDate = date('Y-m-d'); // Proper format for SQL date

// Insert payment record
$sql = "INSERT INTO tbl_payment (order_id, payment_id, amount, status, rationcard_no, date) 
        VALUES ('$payment->order_id', '$payment->id', $amount, 'completed', '$rationcard_no', '$currentDate')";
$result = mysqli_query($conn, $sql);
$success = $result;

// Fetch user data
$sql2 = "SELECT * FROM tbl_ration WHERE rationcard_no = '$rationcard_no'";
$result2 = mysqli_query($conn, $sql2);
$row = mysqli_fetch_assoc($result2);

if (!$row) {
    $row = [
        'm_name' => 'User',
        'phone_number' => 'Not Available'
    ];
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Receipt | E-Ration</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        .invoice-box {
            max-width: 1000px;
            margin: auto;
            margin-top: 50px;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
            border-radius: 20px;
        }
    </style>
</head>

<body>
    <div class="w3-container">
        <div class="w3-center w3-margin">
            <img src="l2.png" width="100px" height="70px" alt="">
            <img src="l1.png" alt="" width="170px" height="70px">
        </div>
        <h2 class="w3-text-orange w3-dark-blue w3-padding-24 w3-round-large w3-center"
            style="text-shadow:1px 1px 0 #444">
            <b>Payment Receipt</b>
        </h2>
        <div class="w3-container w3-margin invoice-box">
            <p class="w3-large w3-text-dark-blue"><b><?php echo "Dear, " . htmlspecialchars($row['m_name']); ?><br></b></p>
            <h4>
                <b>Date : </b><?php echo date("d-m-Y"); ?>
                <span class="w3-right"><b>Time : </b><?php echo date("h:i:s a"); ?></span>
            </h4>
            <h4>
                <b>Ration Card No : </b><?php echo htmlspecialchars($rationcard_no); ?>
                <span class="w3-right"><b>Phone No : </b><?php echo htmlspecialchars($row['phone_number']); ?></span>
            </h4>
            <table class="w3-table-all w3-margin-top w3-centered">
                <thead>
                    <tr class="w3-center">
                        <th>SR No.</th>
                        <th>Date</th>
                        <th>Mode of Payment</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><?php echo date("d-m-Y", $payment->created_at); ?></td>
                        <td><?php echo htmlspecialchars($payment->method); ?></td>
                        <td><?php echo number_format($amount, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <div class="w3-center">
                <button type="button" class="w3-button w3-round-large w3-dark-blue w3-padding w3-margin-top" id="home">
                    <a href="customer.php" style="text-decoration: none; color: white;">Home</a>
                </button>
                <button type="button" class="w3-button w3-round-large w3-dark-blue w3-padding w3-margin-top"
                    onclick="printReceipt()" id="printpagebutton">Print Receipt</button>
            </div>
        </div>
        <script>
            function printReceipt() {
                const printButton = document.getElementById("printpagebutton");
                const homeButton = document.getElementById("home");
                printButton.style.visibility = 'hidden';
                homeButton.style.visibility = 'hidden';
                window.print();
                printButton.style.visibility = 'visible';
                homeButton.style.visibility = 'visible';
            }
        </script>
    </div>
</body>

</html>
