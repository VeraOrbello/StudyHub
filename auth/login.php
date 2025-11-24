<?php
require_once '../includes/functions.php';
require_once '../models/User.php';

if (isLoggedIn()) {
    redirect('../dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $userData = $user->login($username, $password);
    
    if ($userData) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['user_data'] = $userData;
        
        redirect('../dashboard.php');
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StudyHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    

    <style>

        /* WHOLE PAGE HAS ONE BACKGROUND IMAGE */
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');
        body {
            margin: 0;
            padding: 0;
            background: url('../assets/Login.png') center/cover no-repeat;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* TWO COLUMNS */
        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 1200px;
            height: 100vh;
        }

        /* LEFT COLUMN — ONLY LOGO OVERLAY */
        .login-left {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-overlay {
            background: url('../assets/logo.png') center/contain no-repeat;
            width: 500px;
            height: 500px;
        }

/* RIGHT COLUMN — CENTER FORM MIDDLE & LOWER */
.login-right {
    display: flex;
    justify-content: center;   /* center horizontally */
    align-items: center;       /* center vertically */
    margin-right: -20rem;       /* shifts slightly to the right */
}
/* FORM CONTAINER — MOVE LOWER */
.login-form-container {
    width: 100%;
    max-width: 380px;
    margin-top: 6rem; /* lowers input fields */
    text-transform: uppercase;
    font-family: 'Montserrat', sans-serif;
    color: #0047AB;
}

/* HEADINGS */
.login-form-container h2 {
    text-align: center;
    margin-bottom: 2rem;
    font-size: 2rem;
    font-weight: 700;
    color: #0047AB;
}

/* FORM GROUP */
.form-group {
    margin-bottom: 1.5rem;
}

/* LABELS */
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: #0047AB;
    font-family: 'Montserrat', sans-serif;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* INPUTS */
.form-group input {
    width: 100%;
    padding: 1rem;
    border: 2px solid #0047AB;
    border-radius: 8px;
    background: rgba(255,255,255,0.9);
    font-size: 1rem;
    font-family: 'Montserrat', sans-serif;
    color: #0047AB;
}

.form-group input::placeholder {
    color: #0047AB;
    font-weight: 600;
}

/* BUTTON */
.btn-login {
    width: 100%;
    padding: 1rem;
    background: #354eab;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Montserrat', sans-serif;
}

.btn-login:hover {
    background: #003a8f;
}

/* REGISTER LINK */
.register-link {
    text-align: center;
    margin-top: 1.5rem;
    color: #0047AB;
    font-weight: 600;
    font-family: 'Montserrat', sans-serif;
}

.register-link a {
    color: #;
    font-weight: 700;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }

            .logo-overlay {
                width: 200px;
                height: 200px;
            }
        }

    </style>
</head>



<body>

    <div class="login-container">

        <!-- LEFT COLUMN — LOGO ONLY -->
        <div class="login-left">
            <div class="logo-overlay"></div>
        </div>

        <!-- RIGHT COLUMN — CREDENTIALS -->
        <div class="login-right">
            <div class="login-form-container">
              

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="text" name="username" placeholder=" " required>
                </div>

                <div class="form-group">
    <label>Password</label>
    <div style="position: relative;">
        <input type="password" name="password" id="password" placeholder=" " required>
        <i class="fa-solid fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #0047AB;"></i>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', () => {
        // Toggle password visibility
        if(password.type === 'password'){
            password.type = 'text';
            togglePassword.classList.remove('fa-eye');
            togglePassword.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            togglePassword.classList.remove('fa-eye-slash');
            togglePassword.classList.add('fa-eye');
        }
    });
</script>
    

                <button type="submit" class="btn-login">Login</button>

                </form>


                <div class="register-link">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
