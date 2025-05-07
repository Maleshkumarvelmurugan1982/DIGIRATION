<?php
include "../php/connection.php"; // Include your database connection
include '../php/config.php'; // Include your configuration settings

// Initialize session for OTP storage if needed
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Handle new registration request with ration card number
    if (isset($data['ration_number']) && isset($data['isVarification'])) {
        $ration_number = mysqli_real_escape_string($conn, $data['ration_number']);
        
        // Here, validate if the ration card number exists or some other validation as required
        // For simplicity, we are assuming this ration number is valid for new registration
        $otp = rand(1000, 9999); // Generate a random 4-digit OTP
        // Store OTP in the session for later verification
        $_SESSION['otp'] = $otp;
        $_SESSION['ration_number'] = $ration_number; // Store the ration number in the session for later use

        // Return OTP for display in the GUI (for testing purposes)
        echo json_encode(['status' => 200, 'otp' => $otp]); // Send the OTP back to the client
        exit;
    }

    // Handle password registration after OTP verification
    if (isset($data['password']) && isset($data['userOTP'])) {
        // Verify the OTP
        if ($_SESSION['otp'] == $data['userOTP']) {
            $ration_number = $_SESSION['ration_number']; // Retrieve stored ration number
            $password = mysqli_real_escape_string($conn, $data['password']);
            // Insert user into the database
            $insert_query = "INSERT INTO tbl_user (rationcard_no, password) VALUES ('$ration_number', MD5('$password'))"; 

            if (mysqli_query($conn, $insert_query)) {
                echo json_encode(['status' => 200, 'msg' => 'Registration successful!']);
                // Clear the session after successful registration
                unset($_SESSION['otp']);
                unset($_SESSION['ration_number']);
            } else {
                echo json_encode(['status' => 500, 'msg' => 'Error: ' . mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['status' => 400, 'msg' => 'Invalid OTP. Please try again.']);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .center {
            text-align: left;
        }
    </style>
</head>

<body>
    <section class="w3l-workinghny-form ">
        <div class="workinghny-form-grid">
            <div class="wrapper">
                <div class="logo">
                    <h2><a class="brand-logo" style="pointer-events:none;"> Register Here</a></h2>
                </div>
                <div class="workinghny-block-grid">
                    <div class="workinghny-left-img align-end">
                        <img src="register.jpg" class="img-responsive" alt="img" />
                    </div>
                    <div class="form-right-inf">
                        <div class="login-form-content">
                            <div class="loginuser admin w3-margin-right w3-margin-left w3-margin-top" id="loginForm">
                                <label>Rationcard Number</label>
                                <div class="inputdiv" style="display:flex;align-content:center">
                                    <input type="number" class="w3-input w3-margin-top w3-margin-bottom" name="rationNumber" id="rationNumber" placeholder="Enter Rationcard" required>
                                </div>
                                <button class="btn btn-style mt-10" style="background-color: #007dfe" onclick="registerUser()">Verify Account</button>
                            </div>
                            <div id="otpSection" class="w3-margin-top" style="display: none;">
                                <label>Your OTP: <span id="displayOTP"></span></label>
                                <input type="number" class="w3-input w3-margin-top" name="userOTP" id="userOTP" placeholder="Enter OTP" required>
                                <button class="btn btn-style mt-10" onclick="verifyOTP()">Verify OTP</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        const registerUser = async () => {
            const rationNumber = document.getElementById('rationNumber').value;

            if (rationNumber) {
                await fetch('./signup.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ "ration_number": rationNumber, "isVarification": 0 })
                }).then((response) => {
                    return response.json();
                }).then((result) => {
                    if (result.status == 200) {
                        // Display the OTP on the GUI
                        document.getElementById('displayOTP').innerText = result.otp; // Display OTP for testing
                        document.getElementById('otpSection').style.display = 'block'; // Show OTP section
                    } else {
                        alert(result.msg);
                    }
                });
            } else {
                alert("Please enter a Ration card number.");
            }
        }

        const verifyOTP = async () => {
            const userOTP = document.getElementById('userOTP').value;
            const password = prompt("Please enter your password:"); // Prompt for password

            // Check if OTP is provided and is a valid length
            if (userOTP.length === 4) {
                await fetch('./signup.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ "userOTP": userOTP, "password": password })
                }).then((response) => {
                    return response.json();
                }).then((result) => {
                    if (result.status == 200) {
                        alert('Registration successful!');
                        window.location.href = './login.php'; // Redirect to login after successful registration
                    } else {
                        alert(result.msg);
                    }
                });
            } else {
                alert("Please enter a valid 4 digit OTP!");
            }
        }
    </script>
</body>

</html>