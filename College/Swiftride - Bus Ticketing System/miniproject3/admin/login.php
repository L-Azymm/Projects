<?php
include '../includes/db_connection.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check admin credentials
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_type'] = $user['user_type'];
        header("Location: booking_management.php");
        exit();
    } else {
        echo "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Login</title>

    <style>
        /* General body and form styling */
        body {
            background-color: #f2efeb;

        }

        .login-header {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            background-color: #A7C7E7;
            padding: 10px;
            color: #000000;
            border: 3px solid #000000;
            margin-bottom: 25;

        }

        /* Login form box styling */
        .login-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            border: 1px solid #ccc;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            text-align: center;
            border: 2px solid rgb(0, 0, 0);
            width: 40%;
        }

        .input-group {
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: 80px auto;
            /* Label width and input width */
            gap: 10px;
            /* Space between label and input */
            align-items: center;
            /* Vertically align items */
        }

        .input-group label {
            font-size: 14px;
            text-align: left;
            /* Right-align labels */
        }

        .input-group input {
            padding: 8px;
            border: 2px solid rgb(0, 0, 0);
            box-sizing: border-box;
        }


        /* Button styling */
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
            color: black;
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
    </style>
</head>


<body>

    <div class=login-box>
        <h2 class="login-header"> Login to Admin Dashboard</h2>
        <form method="post">
            <div class="input-group">
                <label for="email"><b>Email:</b></label>
                <input type="email" name="email" placeholder="Email" value="admin@example.com" required>
            </div>

            <div class="input-group">
                <label for="password"><b>Password:</b></label>
                <input type="password" name="password" placeholder="Password" value="admin" required><br>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-submit"><b>Login</b></button>
                <button type="reset" class="btn-clear"><b>Clear</b></button>
            </div>
        </form>
    </div>


</body>


</html>