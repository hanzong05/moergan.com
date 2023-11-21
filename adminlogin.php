<?php
session_start();

require_once('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'login') {
        $Email = $_POST['email'];
        $Password = $_POST['password'];

        // Use prepared statements to prevent SQL injection
        $sql = "SELECT id, position, chat_id FROM admins WHERE Email = ? AND Pass = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $Email, $Password);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // User credentials are correct, log them in
                $stmt->bind_result($id, $position, $chat_id); // Add $position here
                $stmt->fetch();
                $_SESSION['id'] = $id;
                $_SESSION['position'] = $position;
                $_SESSION['chat_id'] = $chat_id; // Store user's position in the session
                header("location: dashboard.php");
                exit;
            } else {
                // Invalid email or password
                echo "<script>alert('Invalid Email or password');</script>";
            }
        } else {
            // Handle database query execution error
            echo "<script>alert('Error in database query');</script>";
        }

        // Close the prepared statement
        $stmt->close();
    }
}
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    </style>
</head>
<body>
<section class="h-100 gradient-form" style="background-color: #eee; opacity:90%;">
  <div class="container py-3">
    <div class="row d-flex justify-content-center align-items-center">
      <div class="col-xl-10">
        <div class="card rounded-3 text-black">
          <div class="row g-0">
            <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
              <div class="text-white px-3 py-4 p-md-5 mx-md-4">
                <img src="image/haha.jpg" alt="" class="img-fluid">
              </div>
            </div>
            <div class="col-lg-6" style="background-color: #079848;">
              <div class="card-body p-md-5 mx-md-4">
                <div class="text-center">
                  <img src="image/bg.jpg"
                    style="width: 100px; height: 100px; border-radius:50%; opacity:90%;" alt="logo">
                </div>
                <form method="POST" action="adminlogin.php">
                  <input type="hidden" name="form_type" value="login">
                  <p><b>Please login to your account</b></p>
                  <div class="form-outline mb-4">
                    <input type="email" id="form2Example11" name="email" class="form-control" placeholder="Email address" />
                    <label class="form-label" for="form2Example11"><b>Username</b></label>
                  </div>
                  <div class="form-outline mb-4">
                    <input type="password" id="form2Example22" name="password" class="form-control" />
                    <label class="form-label" for="form2Example22"><b>Password</b></label>
                  </div>
                  <div class="text-center pt-1 mb-5 pb-1">
                    <button class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3" type="submit">Log in</button>
                    <a class="text" href="forgotadmin.php"><b>Forgot password?</b></a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</body>
</html>
