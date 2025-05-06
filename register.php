<?php
include 'php/config.php'; // Secure database connection

$error_message = ''; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($con, trim($_POST['username']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $security_question = $_POST['security_question'];
    $security_answer = $_POST['security_answer'];

    // Password validation
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $error_message = 'Password must contain at least 8 characters, including uppercase, lowercase, number, and special character.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match. Please try again!';
    } else {
        // Generate a secure 128-bit key using random_bytes (16 bytes)
        $encryption_key = bin2hex(random_bytes(16)); // Generates a 128-bit key (16 bytes)

        // Encrypt security question and answer using AES-128-CTR encryption
        $iv = '1234567891011121'; // Initialization vector (16 bytes)
        $encrypted_question = openssl_encrypt($security_question, 'AES-128-CTR', $encryption_key, 0, $iv);
        $encrypted_answer = openssl_encrypt($security_answer, 'AES-128-CTR', $encryption_key, 0, $iv);

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $current_time = date('Y-m-d H:i:s'); // Capture registration time

        // Check if the email is already registered
        $check_email_query = "SELECT Email FROM users WHERE Email = ?";
        $stmt = mysqli_prepare($con, $check_email_query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error_message = 'This email is already registered. Please try another one!';
        } else {
            // Insert user into the database
            $insert_query = "INSERT INTO users (Username, Email, Password, SecurityQuestion, SecurityAnswer, Role, created_at,EncryptionKey) 
                             VALUES (?, ?, ?, ?, ?, 'user', ?, ?)";
            $stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($stmt, 'sssssss', $username, $email, $hashed_password, $encrypted_question, $encrypted_answer, $current_time, $encryption_key);

            if (mysqli_stmt_execute($stmt)) {
                echo "<div class='message center-box'>
                        <p>Registration successful!</p>
                      </div>";
                echo "<a href='login.php'><button class='btn'>Login Now</button></a>";
                exit();
            } else {
                $error_message = 'Error occurred during registration. Please try again later.';
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Register</title>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Sign Up</header>
            <!-- Display error messages inside the form box -->
            <?php if ($error_message): ?>
                <div class="error-message">
                    <p><?php echo $error_message; ?></p>
                </div>
            <?php endif; ?>
            <form action="" method="post">
                <div class="field input">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" autocomplete="off" required>
                </div>
                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" autocomplete="off" required>
                </div>
                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>
                <div class="field input">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" autocomplete="off" required>
                </div>
                <div class="field input">
                    <label for="security_question">Select Security Question</label>
                    <select name="security_question" id="security_question" required>
                        <option value="" disabled selected>Select a security question</option>
                        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                        <option value="What is the name of your first pet?">What is the name of your first pet?</option>
                        <option value="What is your favorite color?">What is your favorite color?</option>
                        <option value="What was the name of your first school?">What was the name of your first school?</option>
                        <option value="What is your favorite food?">What is your favorite food?</option>
                    </select>
                </div>
                <div class="field input">
                    <label for="security_answer">Security Answer</label>
                    <input type="text" name="security_answer" id="security_answer" autocomplete="off" required>
                </div>
                <div class="field">
                    <input type="submit" class="btn" name="submit" value="Register">
                </div>
                <div class="links">
                    Already a member? <a href="login.php">Sign In</a>
                </div>
            </form>
        </div>
    </div>
    <style>
        .error-message {
            margin: 10px 0;
            padding: 10px;
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</body>
</html>
