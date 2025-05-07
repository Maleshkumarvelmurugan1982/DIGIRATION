<?php
session_start();
include '../php/connection.php';

if (!isset($_SESSION['rationcard_no'])) {
    header("Location: ../login/login.php");
    exit();
}

$rcard_no = $_SESSION['rationcard_no'];

// Get distributor details
$sql = "SELECT * FROM tbl_distributor WHERE rationcard_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rcard_no);
$stmt->execute();
$result = $stmt->get_result();
$distributor = $result->fetch_assoc();

if (!$distributor) {
    echo "<script>alert('Distributor not found.'); window.location.href = 'logout.php';</script>";
    exit();
}

$d_fname = $distributor['fname'];
$d_mname = $distributor['mname'];
$d_lname = $distributor['lname'];
$d_pds = $distributor['pds_no'];
$d_image = $distributor['image'];
$d_pincode = $distributor['pincode'];

// Get all available stocks for autofill dropdowns
$stocks = [];
$sql = "SELECT stock_id, stock_name FROM tbl_stock";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $stocks[] = $row;
}

if (isset($_POST['btn-edit-dist'])) {
    $fname = $d_fname;
    $mname = $d_mname;
    $lname = $d_lname;
    $stock_name = $_POST['item'];
    $stock_id = $_POST['id'];
    $date = $_POST['date'];
    $t_quan = (int)$_POST['quantity'];

    // Get total users in distributor's area
    $sql = "SELECT COUNT(*) AS total FROM tbl_user WHERE pincode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $d_pincode);
    $stmt->execute();
    $user_count = $stmt->get_result()->fetch_assoc()['total'];

    // Get stock info
    $sql = "SELECT * FROM tbl_stock WHERE stock_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $stock_id);
    $stmt->execute();
    $stock = $stmt->get_result()->fetch_assoc();

    if (!$stock) {
        echo '<script>alert("Invalid stock ID.");</script>';
    } else {
        $available_per_user = $stock['quantity'];
        $stock_price = $stock['stock_price'];

        $max_quantity = $available_per_user * $user_count;

        if ($t_quan > 0 && $t_quan <= $max_quantity) {
            $sql = "INSERT INTO tbl_apply_stock 
                    (date, stock_id, stock_name, stock_price, d_fname, d_mname, d_lname, pds_no, quantity) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssissssi", $date, $stock_id, $stock_name, $stock_price, $fname, $mname, $lname, $d_pds, $t_quan);
            if ($stmt->execute()) {
                echo '<script>alert("Your request has been accepted."); window.location.href="avail_stock.php";</script>';
            } else {
                echo '<script>alert("Something went wrong while applying for stock.");</script>';
            }
        } else {
            echo '<script>alert("Invalid quantity. Max available: ' . $max_quantity . '");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply for Stock</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body class="w3-light-grey">
    <div class="w3-container w3-margin">
        <h2 class="w3-center w3-blue w3-padding-16">Apply for Stock</h2>
        <form class="w3-container w3-card w3-white w3-padding" method="POST">
            <p>
                <label>Stock Name</label>
                <select class="w3-select w3-border" name="item" id="stock_name" required onchange="syncStockID()">
                    <option value="" disabled selected>Choose a stock</option>
                    <?php foreach ($stocks as $stock): ?>
                        <option value="<?php echo $stock['stock_name']; ?>" data-id="<?php echo $stock['stock_id']; ?>">
                            <?php echo $stock['stock_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label>Stock ID</label>
                <input class="w3-input w3-border" type="text" name="id" id="stock_id" required readonly>
            </p>
            <p>
                <label>Date</label>
                <input class="w3-input w3-border" type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </p>
            <p>
                <label>Quantity</label>
                <input class="w3-input w3-border" type="number" name="quantity" required min="1">
            </p>
            <p>
                <a href="avail_stock.php" class="w3-button w3-gray w3-round">← Back</a>
                <button class="w3-button w3-blue w3-round" type="submit" name="btn-edit-dist">Apply</button>
            </p>
        </form>
    </div>

    <script>
        function syncStockID() {
            const select = document.getElementById('stock_name');
            const selectedOption = select.options[select.selectedIndex];
            const stockID = selectedOption.getAttribute('data-id');
            document.getElementById('stock_id').value = stockID;
        }
    </script>
</body>
</html>
