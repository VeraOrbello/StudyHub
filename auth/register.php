<?php
require_once '../includes/functions.php';
require_once '../models/User.php';

if (isLoggedIn()) {
    redirect('../dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();

    $firstname  = sanitize($_POST['firstname']);
    $lastname   = sanitize($_POST['lastname']);
    $email      = sanitize($_POST['email']);
    $username   = sanitize($_POST['username']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        $result = $user->register([
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $email,
            'username'  => $username,
            'password'  => $password
        ]);
        

        if ($result) {
            $_SESSION['success'] = "Registration successful! You may now log in.";
            redirect('login.php');
        } else {
            $error = "Registration failed! Username or Email may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - StudyHub</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');

body {
    margin: 0;
    padding: 0;
    background: url('../assets/Register.png') center/cover no-repeat;
    background-size: cover;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Montserrat', sans-serif;
}

.register-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
    max-width: 1200px;
    height: 100vh;
}

.register-left {
    display: flex;
    justify-content: center;
    align-items: center;
}

.logo-overlay {
    background: url('../assets/logo.png') center/contain no-repeat;
    width: 500px;
    height: 500px;
}

.register-right {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: -25rem;
}

.register-form-container {
    width: 100%;
    max-width: 400px;
    margin-top: 10rem;
    text-transform: uppercase;
    color: #0047AB;
}

.register-form-container h2 {
    text-align: center;
    margin-bottom: 2rem;
    font-size: 2rem;
    font-weight: 700;
}

.form-group {
    margin-bottom: 1rem;
    position: relative;
}

.form-group label {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: #0047AB;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.form-group input {
    width: 100%;
    padding: 1rem;
    border: 2px solid #0047AB;
    border-radius: 8px;
    background: transparent;
    font-size: 1rem;
    color: #0047AB;
    font-family: 'Montserrat', sans-serif;
    transition: all 0.3s ease;
}

.form-group input::placeholder {
    color: #0047AB;
    font-weight: 600;
}

.form-group input:focus {
    outline: none;
    border-color: #003a8f;
    box-shadow: 0 0 8px rgba(0, 71, 171, 0.3);
}

/* BUTTON */
.btn-register {
    width: 100%;
    padding: 1rem;
    background: #354eab;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-register:hover {
    background: #003a8f;
}

.login-link {
    text-align: center;
    margin-top: 1.5rem;
    font-weight: 600;
    color: #0047AB;
}

.login-link a {
    font-weight: 700;
    color: #0047AB;
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<div class="register-container">

    <!-- LEFT COLUMN -->
    <div class="register-left">
        <div class="logo-overlay"></div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="register-right">
        <div class="register-form-container">

            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="firstname" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastname" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>

                <div class="form-group">
    <label>Password</label>
    <div style="position: relative;">
        <input type="password" name="password" id="password" required>
        <i class="fa-solid fa-eye" id="togglePassword" 
           style="position: absolute; right: -4px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
    </div>
</div>

<div class="form-group">
    <label>Confirm Password</label>
    <div style="position: relative;">
        <input type="password" name="confirm_password" id="confirmPassword" required>
        <i class="fa-solid fa-eye" id="toggleConfirmPassword" 
           style="position: absolute; right: -4px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
    </div>
</div>

                <button type="submit" class="btn-register">Register</button>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>

        </div>
    </div>

</div>

<script>
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');
togglePassword.addEventListener('click', () => {
    if(password.type === 'password'){
        password.type = 'text';
        togglePassword.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        password.type = 'password';
        togglePassword.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
const confirmPassword = document.querySelector('#confirmPassword');
toggleConfirmPassword.addEventListener('click', () => {
    if(confirmPassword.type === 'password'){
        confirmPassword.type = 'text';
        toggleConfirmPassword.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        confirmPassword.type = 'password';
        toggleConfirmPassword.classList.replace('fa-eye-slash', 'fa-eye');
    }
});
</script>

</body>
</html>
