<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Header</title>
    <style>

        .container .header {
            width: 100%;
            margin-top: -20px;
            margin-bottom:20px;
            padding: 15px 0;
            display: flex;
            justify-content: center; 
            align-items: center;
            position: sticky;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            gap: 30px;
        }

        .header a.home-btn {
            color: #00d4ff;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 10px;
            background-color: rgba(0, 212, 255, 0.1);
            transition: background 0.3s ease, color 0.3s ease;
        }
        .header a.home-btn:hover {
            background-color: rgba(0, 212, 255, 0.25);
            color: #fff;
        }

        .header a.logout-btn {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 10px;
            background-color: rgba(255, 0, 0, 0.6);
            transition: background 0.3s ease, color 0.3s ease;
        }
        .header a.logout-btn:hover {
            background-color: rgba(255, 0, 0, 0.8);
        }

    </style>

</head>

<body>

<div class="header">
    <a href="../Dashboard/dashboard.php" class="home-btn">Home</a>
    <a href="../logout.php" class="logout-btn">Logout</a>
</div>

</body>

</html>
