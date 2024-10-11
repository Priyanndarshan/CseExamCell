<?php
session_start();
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the username and password are correct
    if ($username === 'admin' && $password === '1234') {
        $_SESSION['loggedin'] = true;
        header("Location: welcome.php");
        exit;
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
    <title>Login</title>
    <style>
        html {
            background: 
                linear-gradient(180deg, rgba(248, 184, 139,0) 20%, rgba(248, 184, 139,.1) 20%, rgba(248, 184, 139,.1) 40%, rgba(248, 184, 139,.2) 40%, rgba(248, 184, 139,.2) 60%, rgba(248, 184, 139,.4) 60%, rgba(248, 184, 139,.4) 80%, rgba(248, 184, 139,.5) 80%),
                linear-gradient(45deg, rgba(250, 248, 132,.3) 20%, rgba(250, 248, 132,.4) 20%, rgba(250, 248, 132,.4) 40%, rgba(250, 248, 132,.5) 40%, rgba(250, 248, 132,.5) 60%, rgba(250, 248, 132,.6) 60%, rgba(250, 248, 132,.6) 80%, rgba(250, 248, 132,.7) 80%),
                linear-gradient(-45deg, rgba(186, 237, 145,0) 20%, rgba(186, 237, 145,.1) 20%, rgba(186, 237, 145,.1) 40%, rgba(186, 237, 145,.2) 40%, rgba(186, 237, 145,.2) 60%, rgba(186, 237, 145,.4) 60%, rgba(186, 237, 145,.4) 80%, rgba(186, 237, 145,.6) 80%),
                linear-gradient(90deg, rgba(178, 206, 254,0) 20%, rgba(178, 206, 254,.3) 20%, rgba(178, 206, 254,.3) 40%, rgba(178, 206, 254,.5) 40%, rgba(178, 206, 254,.5) 60%, rgba(178, 206, 254,.7) 60%, rgba(178, 206, 254,.7) 80%, rgba(178, 206, 254,.8) 80%),
                linear-gradient(-90deg, rgba(242, 162, 232,0) 20%, rgba(242, 162, 232,.4) 20%, rgba(242, 162, 232,.4) 40%, rgba(242, 162, 232,.5) 40%, rgba(242, 162, 232,.5) 60%, rgba(242, 162, 232,.6) 60%, rgba(242, 162, 232,.6) 80%, rgba(242, 162, 232,.8) 80%),
                linear-gradient(180deg, rgba(254, 163, 170,0) 20%, rgba(254, 163, 170,.4) 20%, rgba(254, 163, 170,.4) 40%, rgba(254, 163, 170,.6) 40%, rgba(254, 163, 170,.6) 60%, rgba(254, 163, 170,.8) 60%, rgba(254, 163, 170,.8) 80%, rgba(254, 163, 170,.9) 80%);
            background-color: rgb(254, 163, 170);
            background-size: 100% 100%;
            min-height: 100vh; /* Ensure full viewport height */
        }
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px; /* Set a fixed width for the login container */
            box-sizing: border-box; /* Include padding and border in the element's total width */
        }
        .login-container h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        .login-container input[type="text"], 
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* Include padding and border in the element's total width */
        }
        .login-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .login-container input[type="submit"]:hover {
            background-color: #218838;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" name="submit" value="Login">
        </form>
    </div>
</body>
</html>
