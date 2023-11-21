<?php
session_start();

// Include the database connection logic from db_connection.php
require_once('db_connection.php');
$execval = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'login') {
        // Handle login form
        $Email = $_POST['Email'];
        $Password = $_POST['Password'];

        // Use the existing database connection from db_connection.php
        // $conn is already established in db_connection.php

        // Validate and sanitize user input
        $Email = filter_var($Email, FILTER_SANITIZE_EMAIL);

        $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
        $stmt->bind_param("s", $Email);
        $stmt->execute();
        $stmt_result = $stmt->get_result();

        if ($stmt_result->num_rows > 0) {
            $data = $stmt_result->fetch_assoc();

            if ($data['is_verified'] == 1) {
                // User is verified, allow login
                if (password_verify($Password, $data['Password'])) {
                    $_SESSION['user'] = $data; 
                    $_SESSION['unique_id'] = $data; 
                    // Store user data in the session

                   
                    $userId = $data['Id']; 
                    
                   // Assuming you have a user ID in your 'users' table
                    $updateIsArchiveSql = "UPDATE users SET is_archive = 0 WHERE Id = ?";
                    $stmt = $conn->prepare($updateIsArchiveSql);
                    $stmt->bind_param("i", $userId);

                    $usrn = "SELECT Firstname AND Lastname FROM users WHERE Id = $userId";
                    $updatePetsQuery = "UPDATE pets SET is_archived_userid = 0 WHERE owner = ?";
                    $stmtPets = $conn->prepare($updatePetsQuery);
                    $stmtPets->bind_param("i", $usrn);
                
                        
                    if ($stmt->execute()) {
                        if ($stmtPets->execute()) {
                            echo 'Client and associated pets archived successfully';
                        } else {
                            echo 'Error archiving client pets: ' . $stmtPets->error;
                        }
                    } else {
                        echo "Error updating 'is_archive' field: " . $stmt->error;
                    }// Assuming you have a user ID in your 'users' table
                    // Create a table for storing pet data for the user
                    $createPetTableSql = "CREATE TABLE IF NOT EXISTS pet_data_" . $userId . " (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        unique_id VARCHAR(255) UNIQUE NOT NULL,
                        pet_name VARCHAR(255) NOT NULL,
                        species VARCHAR(255) NOT NULL,
                        breed VARCHAR(255) NOT NULL,
                        age VARCHAR(20) NOT NULL,
                        gender ENUM('Male', 'Female') NOT NULL,
                        birthdate DATE NOT NULL,
                        status ENUM('Good', 'Bad')
                    )";
                    if ($conn->query($createPetTableSql) === TRUE) {
                        echo "Pet data table created successfully.";
                    } else {
                        echo "Error creating pet data table: " . $conn->error;
                    }

                    // Create a table for storing pet buttons for the user
                    $createPetButtonTableSql = "CREATE TABLE IF NOT EXISTS petbutton_" . $userId . " (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        button_name VARCHAR(255) NOT NULL,
                        button_action VARCHAR(255) NOT NULL
                    )";

                    if ($conn->query($createPetButtonTableSql) === TRUE) {
                        echo "Pet button table created successfully.";
                    } else {
                        echo "Error creating pet button table: " . $conn->error;
                    }

                    // Create a table for storing user appointments
                  
                    header('Location: home.php'); // Redirect to the home page
                } else {
                    echo "<script>";
                    echo "alert('Invalid Email or password');";
                    echo "</script>";
                }
            } else {
                // User is not verified, show an error message
                echo "<script>";
                echo "alert('Your account is not verified. Please check your email for a verification link.');";
                echo "</script>";
            }
        } else {
            echo "<script>";
            echo "alert('Invalid Email or password');";
            echo "</script>";
        }
}elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'register') {
            // Handle registration form
            $Firstname = $_POST['Firstname'];
            $Lastname = $_POST['Lastname'];
            $Address = $_POST['Address'];
            $Email = $_POST['Email'];
            $Password = $_POST['Password']; // Store password as plain text
            $img = 'profile-default.png';
            $unique_id = uniqid();
            $checkEmailQuery = "SELECT Email FROM users WHERE Email = ?";
            $checkEmailStmt = $conn->prepare($checkEmailQuery);
            $checkEmailStmt->bind_param("s", $Email);
            $checkEmailStmt->execute();
            $checkEmailResult = $checkEmailStmt->get_result();
        
            if ($checkEmailResult->num_rows > 0) {
                // Email is already registered, show an error message
                echo '<script type="text/javascript">
                    alert("Email is already used. Please use a different email.");
                </script>';
            } 
              elseif (strlen($Password) < 8 || strlen($Password) > 16 ) {
                    echo '<script type="text/javascript">
                        alert("Password must be at least 8 - 16 characters long.");
                    </script>';
                } else {
                    // Password meets the minimum length requirement, proceed with registration
                   

            // Password meets the minimum length requirement, proceed with registration
            $Password = password_hash($_POST['Password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users(unique_id, Firstname, Lastname, Address, Email, Password, Image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $unique_id, $Firstname, $Lastname, $Address, $Email, $Password, $img);
                    $execval = $stmt->execute();
                }
            
        
                if ($execval) {
            

  $email = $_REQUEST['Email'];
  $check_email = mysqli_query($conn,"select Email from users where email='$email'");
  $res = mysqli_num_rows($check_email);
  if($res>0)
  {
    $message = '<div>
     <p><b>Hello!</b></p>
     <p>You are recieving this email because you need to verify your account.</p>
     <br>
     <p><button class="btn btn-primary"><a href="http://localhost/case%20study/Verfy.php?secret='.base64_encode($email).'">Verify</a></button></p>
     <br>
     <p>If you did not used your account to register in any website, no further action is required.</p>
    </div>';

include_once("SMTP/class.phpmailer.php");
include_once("SMTP/class.smtp.php");
$email = $email; 
$mail = new PHPMailer;
$mail->IsSMTP();
$mail->SMTPAuth = true;                 
$mail->SMTPSecure = "tls";      
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587; 
$mail->Username = "hjpillerva24@gmail.com";   //Enter your username/emailid
$mail->Password = "iwwk vzrv vfgd wgxb";   //Enter your password
$mail->FromName = "hanzongpiller";
$mail->AddAddress($email);
$mail->Subject = "verify email";
$mail->isHTML( TRUE );
$mail->Body =$message;
if($mail->send())
{
  $msg = "We have e-mailed your verification code!";
}
}
else
{
  $msg = "We can't find a user with that email address";
}
}

            echo '<script type="text/javascript">
            alert("Registration successful. please Verify Email");
            </script>';
}
            
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <title>Moergan&Friends</title>
    <style>
         input::placeholder {
            color:  #404040
;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <nav class="nav">
        <div class="nav-logo">
            <img src="image/bg.jpg" alt="">
        </div>
        <div class="nav-menu" id="navMenu">
            <ul>
                <li><a href="#" class="link active">Home</a></li>
                <li><a href="services.html" class="link">Services</a></li>
                <li><a href="#" class="link">About</a></li>
            </ul>
        </div>
        <div class="nav-button">
            <button class="btn white-btn" id="loginBtn" onclick="login()">Sign In</button>
            <button class="btn" id="registerBtn" onclick="register()">Sign Up</button>
        </div>
        <div class="nav-menu-btn">
            <i class="bx bx-menu" onclick="myMenuFunction()"></i>
        </div>
    </nav>

    <!-- Form box -->
    <div class="form-box">
        <!-- Login form -->
        <div class="login-container" id="login" style="display: block;">
            <form action="" method="post">
                <div class="top">
                    <span>Don't have an account? <a href="#" onclick="register()">Sign Up</a></span>
                    <header>Login</header>
                </div>
                <div class="input-box">
                    <input type="text" class="input-field" placeholder="Username or Email" name="Email">
                    <i class="bx bx-user"></i>
                </div>
                <div class="input-box">
                    <input type="password" class="input-field" placeholder="Password" name="Password">
                    <i class="bx bx-lock-alt"></i>
                </div>
                <div class="input-box">
                    <input type="hidden" name="form_type" value="login"> <!-- Hidden field to identify login form -->
                    <input type="submit" class="submit" value="Sign In">
                </div>
                <div class="two-col">
                    <div class="two">
                        <label><a href="adminlogin.php">Admin Login</a></label>
                    </div>
                    <div class="two">
                        <label><a href="forgot.php">Forgot password?</a></label>
                    </div>
                </div>
            </form>
            
        </div>

        <!-- Registration form -->
        <div class="register-container" id="register">
            <div class="top">
                <span>Have an account? <a href="#" onclick="login()">Login</a></span>
                <header>Sign Up</header>
            </div>
            <form action="" method="post">
                <div class="two-forms">
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="Firstname" name="Firstname" required>
                        <i class="bx bx-user"></i>
                    </div>
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="Lastname" name="Lastname" required>
                        <i class="bx bx-user"></i>
                    </div>
                </div>
                <div class="input-box">
                    <input type="text" class="input-field" placeholder="Address" name="Address" required>
                    <i class='bx bxs-home' ></i>
                </div>
                <div class="input-box">
                    <input type="text" class="input-field" placeholder="Email" name="Email" required>
                    <i class="bx bx-envelope"></i>
                </div>
                <div class="input-box">
                    <input type="password" class="input-field" placeholder="Password" name="Password" required>
                    <i class="bx bx-lock-alt"></i>
                </div>
                <div class="input-box">
                    <input type="hidden" name="form_type" value="register"> <!-- Hidden field to identify registration form -->
                    <input type="submit" class="submit" value="Register" >
                </div>
            </form>
        </div>
    </div>
</div>

<script>
   
   function myMenuFunction() {
    var i = document.getElementById("navMenu");

    if(i.className === "nav-menu") {
        i.className += " responsive";
    } else {
        i.className = "nav-menu";
    }
   }
 
</script>

<script>
    // JavaScript functions for switching between login and registration forms
    // and handling the mobile menu toggle
    var a = document.getElementById("loginBtn");
    var b = document.getElementById("registerBtn");
    var x = document.getElementById("login");
    var y = document.getElementById("register");

    function login() {
        x.style.left = "4px";
        y.style.right = "-520px";
        a.className += " white-btn";
        b.className = "btn";
        x.style.opacity = 1;
        y.style.opacity = 0;
    }

    function register() {
        x.style.left = "-510px";
        y.style.right = "5px";
        a.className = "btn";
        b.className += " white-btn";
        x.style.opacity = 0;
        y.style.opacity = 1;
    }
</script>
</body>
</html>
