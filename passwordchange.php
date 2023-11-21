<?php
session_start();

// Include the database connection logic from db_connection.php
require_once('db_connection.php');
if (isset($_POST['pwdrst'])) {
    $email = $_POST['email'];
    $password = $_POST['pwd'];
    $confirmPassword = $_POST['cpwd'];

    // Validate and sanitize user input
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if ($password === $confirmPassword) {
        // Check if the password meets the length requirements
        if (strlen($password) >= 8 && strlen($password) <= 11) {
            // Hash the new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Update the user's password in the database
            $stmt = $conn->prepare("UPDATE users SET Password = ? WHERE Email = ?");
            $stmt->bind_param("ss", $hashedPassword, $email);

            if ($stmt->execute()) {
                $msg = 'Your password has been updated successfully. <a href="index.php">Click here</a> to login.';
            } else {
                $msg = "Error while updating the password.";
            }
        } else {
            $msg = "Password length should be between 8 and 11 characters.";
        }
    } else {
        $msg = "Password and Confirm Password do not match.";
    }
}




if (isset($_GET['secret'])) {
    $email = base64_decode($_GET['secret']);
    $stmt = $conn->prepare("SELECT Email FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $res = $stmt->num_rows;

    if ($res > 0) {
        // Display the password reset form
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Reset Password</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
            
            <style> .box
 {
  width:100%;
  max-width:600px;
  background-color:#f9f9f9;
  border:1px solid #ccc;
  border-radius:5px;
  padding:16px;
  margin:0 auto;
 }
 input.parsley-success,
 select.parsley-success,
 textarea.parsley-success {
   color: #468847;
   background-color: #DFF0D8;
   border: 1px solid #D6E9C6;
 }

 input.parsley-error,
 select.parsley-error,
 textarea.parsley-error {
   color: #B94A48;
   background-color: #F2DEDE;
   border: 1px solid #EED3D7;
 }

 .parsley-errors-list {
   margin: 2px 0 3px;
   padding: 0;
   list-style-type: none;
   font-size: 0.9em;
   line-height: 0.9em;
   opacity: 0;

   transition: all .3s ease-in;
   -o-transition: all .3s ease-in;
   -moz-transition: all .3s ease-in;
   -webkit-transition: all .3s ease-in;
 }

 .parsley-errors-list.filled {
   opacity: 1;
 }
 
 .parsley-type, .parsley-required, .parsley-equalto{
  color:#ff0000;
 }
.error
{
  color: red;
  font-weight: 700;
} </style>
        </head>
        <body>
        <div class="container">
            <div class="table-responsive">
                <h3 style="text-align: center;">Change Password</h3><br/>
                <div class="box">
                <form id="validate_form" method="post">
    <input type="hidden" name="email" value="<?php echo $email; ?>"/>
    <div class="form-group">
        <label for="pwd">Password</label>
        <input type="password" name="pwd" id="pwd" placeholder="Enter Password" required
               data-parsley-type="password" data-parsley-trigger="keyup"
               data-parsley-minlength="8"
               class="form-control"/>
    </div>
    <div class="form-group">
        <label for="cpwd">Confirm Password</label>
        <input type="password" name="cpwd" id="cpwd" placeholder="Enter Confirm Password" required
               data-parsley-type="password" data-parsley-trigger="keyup"
               data-parsley-minlength="8" 
               data-parsley-equalto="#pwd" 
               class="form-control"/>
    </div>
    <div class="form-group">
        <input type="submit" id="login" name="pwdrst" value="Reset Password" class="btn btn-success"/>
    </div>
    <p class="error" style="color: <?php echo (!empty($msg) && $res > 0) ? 'green' : 'red'; ?>">
  <?php if (!empty($msg)) { echo $msg; } ?>
</p>

</form>

                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
    } else {
        echo "Invalid secret link or user not found.";
    }
}

// Include any necessary JavaScript libraries for form validation, such as Parsley.js.
?>
