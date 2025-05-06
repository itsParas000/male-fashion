<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");

// Include database configuration and captcha
include 'php/config.php';
include 'captcha.php';

// Hard-coded encryption key (32 bytes for AES-128-CTR)
// In production, move this to a secure location (e.g., environment variable)
define('ENCRYPTION_KEY', 'mysecretkey12345678901234567890');

// Encryption and Decryption Functions
function encryptData($data, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-128-CTR'));
    $encrypted = openssl_encrypt($data, 'AES-128-CTR', $key, 0, $iv);
    return base64_encode($iv . $encrypted); // Store IV with encrypted data
}

function decryptData($data, $key) {
    $data = base64_decode($data);
    $iv = substr($data, 0, openssl_cipher_iv_length('AES-128-CTR'));
    $encrypted = substr($data, openssl_cipher_iv_length('AES-128-CTR'));
    return openssl_decrypt($encrypted, 'AES-128-CTR', $key, 0, $iv);
}

// Registration Process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($con, trim($_POST['username']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $securityQuestion = trim($_POST['security_question']);
    $securityAnswer = trim($_POST['security_answer']);

    // Password validation
    $password_pattern = '/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])[A-Za-z0-9!@#$%^&*]{8,}$/';
    if (!preg_match($password_pattern, $password)) {
        echo "<div class='message error'><p>Password must contain at least one uppercase letter, one number, and one special symbol, and be at least 8 characters long</p></div>";
        exit();
    }
    
    if ($password !== $confirm_password) {
        echo "<div class='message error'><p>Passwords do not match</p></div>";
        exit();
    }

    $password = password_hash($password, PASSWORD_BCRYPT);

    // Encrypt security question and answer
    $securityQuestionEncrypted = encryptData($securityQuestion, ENCRYPTION_KEY);
    $securityAnswerEncrypted = encryptData($securityAnswer, ENCRYPTION_KEY);

    $query = "INSERT INTO users (Username, Email, Password, SecurityQuestionEncrypted, SecurityAnswerEncrypted) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'sssss', $username, $email, $password, $securityQuestionEncrypted, $securityAnswerEncrypted);

    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='message success'><p>Registration successful! Please log in.</p></div>";
    } else {
        echo "<div class='message error'><p>Error: " . mysqli_error($con) . "</p></div>";
    }
    mysqli_stmt_close($stmt);
}

// Login Process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $password = $_POST['password'];
    $captcha_answer = trim($_POST['captcha_answer']);

    // Verify CAPTCHA
    $captcha = new Captcha();
    if (!$captcha->verifyCaptcha($captcha_answer)) {
        $captcha_error = "<div class='message error'><p>Incorrect CAPTCHA answer. Please try again.</p></div>";
    } else {
        // Rate-limiting mechanism
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        if ($_SESSION['login_attempts'] >= 5) {
            echo "<div class='message error'><p>Too many login attempts. Please try again later.</p></div>";
            exit();
        }

        $query = "SELECT * FROM users WHERE Email = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user['Password']) && $user['is_active'] == 1) {
                session_destroy();
                $role = $user['Role'];
                session_name('SESSION_' . strtoupper($role));
                session_start();
                session_regenerate_id(true);

                $_SESSION['valid'] = $user['Email'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['id'] = $user['Id'];
                $_SESSION['role'] = $role;
                $_SESSION['login_attempts'] = 0;

                if ($role === 'admin') {
                    header("Location: admin/dashboard.php");
                    include 'loading.php';
                }elseif($role === 'delivery') {
                    header("Location: delivery/dashboard.php");
                } else {
                    $_SESSION['user_id'] = $user['Id'];
                    header("Location: index.php");
                    include 'loading.php';
                }
                exit();
            } else if ($user && !$user['is_active']) {
                $_SESSION['login_attempts'] += 1;
                echo "<div class='message error'><p>Your account is inactive. Contact the admin.</p></div>";
            } else {
                $_SESSION['login_attempts'] += 1;
                echo "<div class='message error'><p>Invalid email or password. Please try again.</p></div>";
            }
        } else {
            echo "<div class='message error'><p>Email not found. Please register first.</p></div>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Forgot Password Process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $user_security_question = trim($_POST['security_question']);
    $user_security_answer = trim($_POST['security_answer']);
    $new_password = $_POST['new_password'];

    // Password validation
    $password_pattern = '/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])[A-Za-z0-9!@#$%^&*]{8,}$/';
    if (!preg_match($password_pattern, $new_password)) {
        echo "<div class='message error'><p>Password must contain at least one uppercase letter, one number, and one special symbol, and be at least 8 characters long</p></div>";
        exit();
    }

    $new_password = password_hash($new_password, PASSWORD_BCRYPT);

    $query = "SELECT * FROM users WHERE Email = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Decrypt stored security question and answer
        $decryptedQuestion = decryptData($user['SecurityQuestionEncrypted'], ENCRYPTION_KEY);
        $decryptedAnswer = decryptData($user['SecurityAnswerEncrypted'], ENCRYPTION_KEY);

        if ($decryptedQuestion === $user_security_question) {
            if ($decryptedAnswer === $user_security_answer) {
                $update_query = "UPDATE users SET Password = ? WHERE Email = ?";
                $stmt = mysqli_prepare($con, $update_query);
                mysqli_stmt_bind_param($stmt, 'ss', $new_password, $email);
                if (mysqli_stmt_execute($stmt)) {
                    echo "<div class='message success'><p>Password updated successfully. You can now log in.</p></div>";
                } else {
                    echo "<div class='message error'><p>Error: " . mysqli_error($con) . "</p></div>";
                }
            } else {
                echo "<div class='message error'><p>Incorrect security answer. Please try again.</p></div>";
            }
        } else {
            echo "<div class='message error'><p>Incorrect security question. Please try again.</p></div>";
        }
    } else {
        echo "<div class='message error'><p>Email not found. Please try again.</p></div>";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style/style.css">
    <title>Login & Register</title>
    <style>
        .form-container.sign-up {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-container.sign-up h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .subtext {
            display: block;
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .input-field {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .select-field {
            appearance: none;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="%23333"><path d="M7.41 8.59L6 10l-1.41-1.41L3 7l1.41-1.41L6 5.17l1.59 1.42L9 7l-1.59 1.59z"/></svg>') no-repeat right 10px center;
            background-size: 12px;
            padding-right: 30px;
        }

        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .password-info {
            position: absolute;
            right: 35px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .password-tooltip {
            display: none;
            position: absolute;
            right: 0;
            top: -40px;
            background: #fff;
            color: #333;
            padding: 8px 12px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            width: 220px;
            z-index: 10;
            font-size: 12px;
            line-height: 1.4;
            text-align: left;
        }

        .password-tooltip.active {
            display: block;
        }

        .submit-btn {
            width: 100%;
            padding: 10px;
            background: #6b48ff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #5a3de6;
        }
    </style>
</head>
<body>
    <div class="container" id="container">
        <!-- Sign In Panel remains unchanged -->
        <div class="form-container sign-in">
            <form action="" method="post">
                <h1>Sign In</h1>
                <span>or use your email password</span>
                <input type="email" name="email" placeholder="Email" autocomplete="off" required>
                <div class="password-container">
                    <input type="password" name="password" placeholder="Password" autocomplete="off" required>
                    <span class="toggle-password"><i class="fas fa-eye"></i></span>
                </div>
                <div class="captcha-container">
                    <span id="captcha-question">
                        <?php 
                        $captcha = new Captcha();
                        echo $captcha->getCaptcha();
                        ?>
                    </span>
                    <input type="number" name="captcha_answer" placeholder="Answer" required>
                    <button type="button" id="refresh-captcha">Refresh</button>
                </div>
                <?php if (isset($captcha_error)) echo $captcha_error; ?>
                <a href="#" onclick="toggleForgotPassword()">Forget Your Password?</a>
                <button type="submit" name="login">Sign In</button>
            </form>
        </div>

        <!-- Modified Sign Up Panel -->
        <div class="form-container sign-up">
            <form action="" method="post" onsubmit="return validateSignUpPassword()">
                <h1>Create Account</h1>
                <span class="subtext">use your email for registration</span>
                <input type="text" name="username" placeholder="Username" autocomplete="off" required class="input-field">
                <input type="email" name="email" placeholder="Email" autocomplete="off" required class="input-field">
                <div class="password-wrapper">
                    <input type="password" name="password" id="signup-password" placeholder="Password" autocomplete="off" required class="input-field">
                    <span class="toggle-password"><i class="fas fa-eye"></i></span>
                    <span class="password-info"><i class="fas fa-info-circle"></i></span>
                    <div class="password-tooltip">
                        Password must include:<br>
                        - At least one uppercase letter<br>
                        - At least one number<br>
                        - At least one special symbol<br>
                        - Minimum 8 characters
                    </div>
                </div>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="signup-confirm-password" placeholder="Confirm Password" autocomplete="off" required class="input-field">
                    <span class="toggle-password"><i class="fas fa-eye"></i></span>
                </div>
                <select name="security_question" required class="input-field select-field">
                    <option value="" disabled selected>Select a security question</option>
                    <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                    <option value="What is the name of your first pet?">What is the name of your first pet?</option>
                    <option value="What is your favorite color?">What is your favorite color?</option>
                    <option value="What was the name of your first school?">What was the name of your first school?</option>
                    <option value="What is your favorite food?">What is your favorite food?</option>
                </select>
                <input type="text" name="security_answer" placeholder="Security Answer" autocomplete="off" required class="input-field">
                <button type="submit" name="submit" class="submit-btn">Sign Up</button>
            </form>
        </div>

        <!-- Toggle Panel remains unchanged -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your details to sign in</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Friend!</h1>
                    <p>Register to get started</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>

        <!-- Modified Forgot Password Overlay -->
        <div class="form-container forgot-password" style="display: none;" id="forgot-password">
            <form action="" method="post" onsubmit="return validateForgotPassword()">
                <h1>Reset Password</h1>
                <input type="email" name="email" placeholder="Email" autocomplete="off" required>
                <select name="security_question" required>
                    <option value="" disabled selected>Select a security question</option>
                    <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                    <option value="What is the name of your first pet?">What is the name of your first pet?</option>
                    <option value="What is your favorite color?">What is your favorite color?</option>
                    <option value="What was the name of your first school?">What was the name of your first school?</option>
                    <option value="What is your favorite food?">What is your favorite food?</option>
                </select>
                <input type="text" name="security_answer" placeholder="Security Answer" autocomplete="off" required>
                <div class="password-container password-wrapper">
                    <input type="password" name="new_password" id="forgot-password-input" placeholder="New Password" autocomplete="off" required>
                    <span class="toggle-password"><i class="fas fa-eye"></i></span>
                    <span class="password-info"><i class="fas fa-info-circle"></i></span>
                    <div class="password-tooltip">
                        Password must include:<br>
                        - At least one uppercase letter<br>
                        - At least one number<br>
                        - At least one special symbol<br>
                        - Minimum 8 characters
                    </div>
                </div>
                <a href="#" onclick="toggleForgotPassword()">Sign in?</a>
                <button type="submit" name="forgot_password">Reset Password</button>
            </form>
        </div>
    </div>

    <script src="style/script.js"></script>
<script>
    function toggleForgotPassword() {
        var forgotPasswordForm = document.getElementById('forgot-password');
        forgotPasswordForm.style.display = forgotPasswordForm.style.display === 'none' ? 'block' : 'none';
    }

    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.add('fa-eye-slash');
                icon.classList.remove('fa-eye');
                this.classList.add('active');
            } else {
                passwordInput.type = 'password';
                icon.classList.add('fa-eye');
                icon.classList.remove('fa-eye-slash');
                this.classList.remove('active');
            }
        });
    });

    document.getElementById('refresh-captcha').addEventListener('click', function() {
        fetch('captcha.php?generate=true')
            .then(response => response.text())
            .then(data => {
                document.getElementById('captcha-question').textContent = data;
            });
    });

    // Add click event for password info tooltip
    document.querySelectorAll('.password-info').forEach(info => {
        info.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent any default behavior
            const tooltip = this.nextElementSibling; // Get the adjacent .password-tooltip
            tooltip.classList.toggle('active'); // Toggle visibility
        });
    });

    document.addEventListener('click', function(e) {
    const tooltips = document.querySelectorAll('.password-tooltip');
    tooltips.forEach(tooltip => {
        if (!e.target.closest('.password-info') && tooltip.classList.contains('active')) {
            tooltip.classList.remove('active');
        }
    });
});

    function validatePassword(password) {
        const upperCase = /[A-Z]/.test(password);
        const number = /[0-9]/.test(password);
        const specialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        const length = password.length >= 8;
        return upperCase && number && specialChar && length;
    }

    function validateSignUpPassword() {
        const password = document.getElementById('signup-password').value;
        const confirmPassword = document.getElementById('signup-confirm-password').value;
        
        if (!validatePassword(password)) {
            alert('Password must contain at least one uppercase letter, one number, one special symbol, and be at least 8 characters long');
            return false;
        }
        if (password !== confirmPassword) {
            alert('Passwords do not match');
            return false;
        }
        return true;
    }

    function validateForgotPassword() {
        const password = document.getElementById('forgot-password-input').value;
        if (!validatePassword(password)) {
            alert('Password must contain at least one uppercase letter, one number, one special symbol, and be at least 8 characters long');
            return false;
        }
        return true;
    }

    history.pushState(null, document.title, location.href);
    window.addEventListener('popstate', function(event) {
        history.pushState(null, document.title, location.href);
    });
</script>
</body>
</html>