<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logoS.png" type="image/png">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            /* flex-direction: row; */
        }

        nav {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            background-color: #fff;
            color: #000;
            padding: 10px;
            width: 200px;
            /* height: 100vh; */
            gap: 10px;
            box-shadow: 2px 0 2px 1px rgba(0, 0, 0, 0.1);
            z-index: 10;
            overflow-y: auto;
            overflow-x: hidden;
            -ms-overflow-style: none;
            /* IE et Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        nav::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari et Opera */
        }

        nav h1 a {
            padding: 10px;
            cursor: pointer;
            text-decoration: none;
            color: #000;
        }

        nav>div>div {
            color: #000;
            text-decoration: none;
            margin-right: 10px;
            padding: 10px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: nowrap;
            width: 100%;
        }

        nav>div>div:hover {
            cursor: pointer;
            background-color: #f5f5f5;
        }

        nav>div {
            display: flex;
            flex-direction: column;
            gap: 25px;
            justify-content: space-between;
            align-items: flex-start;
        }

        nav div i {
            color: #000;
            margin-right: 5px;
        }

        nav div.active i {
            color: #764ba2 !important;
        }

        .fa-sign-out-alt {
            color: red !important;
        }

        main {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100vh;
            padding: 15px;
            overflow-y: auto;
            overflow-x: hidden;
            -ms-overflow-style: none;
            /* IE et Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        main::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari et Opera */
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #fff;
            color: #333;
            z-index: 1;
        }

        .header div {
            display: flex;
            gap: 20px;
        }

        .header div i {
            color: #333;
            font-size: 20px;
        }

        .header div i:hover {
            color: #666;
            cursor: pointer;
        }

        #content {
            margin-top: 20px;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            overflow-x: auto;
            -ms-overflow-style: none;
            /* IE et Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        #content::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari et Opera */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: nowrap;
        }

        table th {
            background-color: #f5f5f5;
            font-weight: bold;
            cursor: pointer;
            white-space: nowrap;
        }

        table tr:hover {
            background-color: #f9f9f9;
        }

        .logout {
            color: red;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <nav>
        <div>
            <h1><a href="index_admin.php">StackCore</a></h1>
        </div>
        <div>
            <div onclick="loadContent(this,'dashboard.php')" class="active"><i class="fas fa-home"></i> Dashboard</div>
            <div onclick="loadContent(this,'products.php')"><i class="fas fa-box"></i> Products</div>
            <div onclick="loadContent(this,'categories.php')"><i class="fas fa-list"></i> Categories</div>
            <div onclick="loadContent(this,'promotions.php')"><i class="fas fa-tag"></i> Promotions</div>
            <div onclick="loadContent(this,'customers.php')"><i class="fas fa-users"></i> Customers</div>
            <div onclick="loadContent(this,'orders.php')"><i class="fas fa-shopping-cart"></i> Orders</div>
            <div><i class="fas fa-sign-out-alt"></i> <a href="logout-admin.php" class="logout">Logout</a></div>
        </div>
    </nav>
    <main>
        <div class="header">
            <div>
                <i class="fas fa-list hideNav"></i>
                <i class="fas fa-search"></i>
            </div>
            <div>
                <i class="fas fa-bell"></i>
                <i class="fas fa-moon"></i>
                <i class="fas fa-sun"></i>
                <i class="fas fa-cog"></i>
                <i class="fas fa-user"></i>
                <i class="fas fa-sign-out-alt"></i>
            </div>
        </div>
        <div id="content"></div>
    </main>

    <script>
        $(document).ready(function () {
            $('#content').load('dashboard.php');

            var nav = $("nav");
            var isNavVisible = true;

            $(".hideNav").click(function () {
                if (isNavVisible) {
                    nav.animate({
                        width: 'toggle'
                    }, "veryfast");
                } else {
                    nav.animate({
                        width: 'toggle'
                    }, "veryfast");
                }
                isNavVisible = !isNavVisible;
            });

        });

        function loadContent(element, url) {
            $("#content").empty();
            console.log(url);
            $('#content').load(url);
            $('.active').removeClass('active');
            $(element).addClass('active');
        }

        function reloadContent(url, param1, param2) {
            $("#content").html("");
            $('#content').load(url + '?tri=' + param1 + '&asc=' + param2);
        }
    </script>
</body>

</html>