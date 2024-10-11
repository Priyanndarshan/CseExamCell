<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
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
            min-height: 100%;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .container {
    text-align: center;
    background-color: #FEAE79;
    padding: 20px;
    border-radius: 12px; /* Increased border-radius for a softer effect */
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15), 0 2px 5px rgba(0, 0, 0, 0.2); /* Enhanced shadow for more depth */
    width: 100%; /* Ensure container takes full width */
    max-width: 100%; /* Ensures the container stays within full width */
    transition: transform 0.2s ease-in-out; /* Smooth effect on hover */
}




        h1 {
            color: #343a40;
        }

        h2 {
            color: #380000;
        }

        button {
    padding: 10px;
    font-size: 24px;
    background-color: #ff474c; /* Main button color */
    color: white;
    border: none;
    border-radius: 8px; /* Slightly increased border radius for more rounded corners */
    cursor: pointer;
    width: 300px; /* Button width */
    align-self: center;
    margin: 10px 0;
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease; /* Smooth hover transition */
}

button:hover {
    background-color: #cc0000; /* Darker red on hover */
    transform: translateY(-3px); /* Slight lift effect on hover */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); /* Subtle shadow on hover */
}

        .button-container {
            width: 100%; /* Set the button container to full width */
            display: flex;
            flex-direction: column; /* Stack buttons vertically */
            justify-content: center;
            flex-grow: 1;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Velammal College of Engineering and Technology</h1>
        <h2>Department of Computer Science and Engineering</h2>
        <h1>CSE Examcell</h1>
    </div>
    <div class="button-container">
        <form action="examqp/main.html" method="get">
            <button type="submit">Convert QB to QP</button>
        </form>
        <form action="hall plan generator/index.html" method="get">
            <button type="submit">Generate Hall Plan</button>
        </form>
    </div>
</body>
</html>
