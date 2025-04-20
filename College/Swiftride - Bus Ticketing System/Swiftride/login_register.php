<?php
$message = '';
$header_title = "Login/Register";
$header_icon = "assets/image/login-icon.svg";
include 'includes/header.php';

include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Registration logic
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $user_type = $_POST['user_type'];

        // Ensure only student/staff can register
        if (!in_array($user_type, ['student', 'staff'])) {
            $message = "Invalid user type for registration.";
            exit();
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, user_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password_hash, $user_type]);

        $message = "Registration successful! <a href='login_register.php'>Login here</a>";
    }

    if (isset($_POST['login'])) {
        // Login logic
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Check user credentials
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];

            // Redirect based on user type
            if ($user['user_type'] === 'driver') {
                header("Location: driver_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            $message = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" href="assets/image/favicon.png" type="image/x-icon">
    <title>Login Register | Swiftride</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f2efeb;
        }


        .form-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-box {
            height: 70%;
            width: 50%;
            box-sizing: border-box;
            text-align: center;
            margin-right: 20px;

        }

        .gif-box {
            width: 50%;
        }

        .gif-box img {
            width: 100%;
        }

        section {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background-color: white;
            border: 2px solid black;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            display: none;
        }

        .container.active {
            display: block;
        }

        .container h2 {
            margin-top: 0;
            padding: 10px;
            border: 2px solid black;
            color: black;
            background-color: #A7C7E7;
            text-align: center;
        }

        .input-group {
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: 90px auto;
            gap: 10px;
            align-items: center;
        }

        .input-group label {
            font-size: 14px;
            text-align: left;
            font-weight: bold;
        }

        .input-group input,
        .input-group select {
            padding: 8px;
            border: 2px solid rgb(0, 0, 0);
            box-sizing: border-box;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
        }

        button {
            padding: 10px 15px;
            border: 2px solid rgb(0, 0, 0);
            cursor: pointer;
            width: 48%;
            box-sizing: border-box;
        }

        .btn-submit {
            background-color: #A7C7E7;
        }

        .btn-submit:hover {
            background-color: #5A9BD8;
        }

        .btn-clear {
            background-color: #EF5B5B;
            color: white;
        }

        .btn-clear:hover {
            background-color: #D94B4B;
        }

        .toggle-buttons {
            display: flex;
            justify-content: center;


        }

        .toggle-buttons button {
            margin-top: 0;
            padding: 10px;
            background-color: #f3df6e;
            color: #000;
            font-weight: bold;


            text-align: center;
        }
    </style>
</head>

<body class="form-body">
    <div class="form-container">

        <div class="form-box">

            <section>
                <div id="login-form" class="container active">

                    <h2>Login</h2>
                    <form method="POST">
                        <div class="input-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" placeholder="Ex: swiftride@student.com" value="user1@gmail.com" required>
                        </div>
                        <div class="input-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" placeholder="Your password" value="user1" required>
                        </div>

                        <p><?php echo htmlspecialchars($message); ?></p>
                        <div class="button-group">

                            <button type="submit" name="login" class="btn-submit"><b>Login</b></button>
                            <button type="reset" class="btn-clear"><b>Clear</b></button>
                        </div>
                    </form>
                    <div class="toggle-buttons">
                        <button onclick="showForm('register')">Register instead</button>
                    </div>
                </div>
            </section>

            <section>
                <div id="register-form" class="container">
                    <h2>Register</h2>
                    <form method="POST">
                        <div class="input-group">
                            <label for="name">Name:</label>
                            <input type="text" name="name" placeholder="Full Name" required>
                        </div>
                        <div class="input-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" placeholder="Institute email address" required>
                        </div>
                        <div class="input-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" placeholder="Set new password" required>
                        </div>
                        <div class="input-group">
                            <label for="user_type">Who are you:</label>
                            <select name="user_type" required>
                                <option value="" selected disabled hidden>Choose here</option>
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>

                        <p><?php echo htmlspecialchars($message); ?></p>
                        <div class="button-group">

                            <button type="submit" name="register" class="btn-submit"><b>Register</b></button>
                            <button type="reset" class="btn-clear"><b>Clear</b></button>
                        </div>
                    </form>

                    <div class="toggle-buttons">
                        <button onclick="showForm('login')">Login instead</button>
                    </div>
                </div>
            </section>
        </div>

        <div class="gif-box">
            <img src="assets/image/bus_animation.gif">
        </div>

    </div>



    <script>
        function showForm(formType) {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');

            if (formType === 'login') {
                loginForm.classList.add('active');
                registerForm.classList.remove('active');
            } else {
                registerForm.classList.add('active');
                loginForm.classList.remove('active');
            }
        }
    </script>
</body>

</html>