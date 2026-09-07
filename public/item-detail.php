<?php

include "../php/db.php";

$found_id = $_GET["id"];

$result = $conn->query("
    SELECT 
        found_items.found_id,
        found_items.title,
        found_items.description,
        found_items.found_location,
        found_items.found_date,
        found_items.image,
        found_items.approval_status,
        found_items.claimed_status,
        categories.category_name,
        users.name
    FROM found_items
    JOIN categories
        ON found_items.category_id = categories.category_id
    JOIN users
        ON found_items.user_id = users.user_id
    WHERE found_items.found_id = $found_id
");

$item = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Item Details</title>

       <link rel="stylesheet" href="../css/public.css">


</head>


<body>


    <!-- NAVBAR -->

    <header>

        <nav class="navbar">

            <div class="logo">

                <span class="logo-box">
                    L&F
                </span>

                <span>
                    UIU Lost & Found
                </span>

            </div>


            <div class="nav-links">

                <a href="index.html">
                    Home
                </a>

                <a href="browse.html">
                    Browse Items
                </a>

            </div>


            <div class="nav-buttons">

                <a href="login.html"
                   class="login-btn">
                    Login
                </a>

                <a href="register.html"
                   class="register-btn">
                    Register
                </a>

            </div>

        </nav>

    </header>



    <!-- ITEM DETAILS -->

    <main class="detail-page">


        <a href="browse.html"
           class="back-link">

            ← Back to Browse

        </a>


        <div class="detail-box">


            <div class="detail-image">

                👛

            </div>


            <div class="detail-info">


                <span class="found">
                    Found
                </span>


                <p class="category">
                    <?php echo htmlspecialchars($item["category_name"]); ?>
                </p>


                <h1>
                    <?php echo htmlspecialchars($item["title"]); ?>
                </h1>


                <p>
                    📍 <?php echo htmlspecialchars($item["found_location"]); ?>
                </p>


                <p>
                    📅 <?php echo htmlspecialchars($item["found_date"]); ?>
                </p>


                <h3>
                    Description
                </h3>


                <p>
                    <?php echo htmlspecialchars($item["description"]); ?>
                </p>


                <h3>
                    Reported By
                </h3>


                <p>
                    <?php echo htmlspecialchars($item["name"]); ?>
                </p>


                <a href="login.html"
                   class="orange-btn">

                    Claim This Item

                </a>

            </div>

        </div>

    </main>



    <footer>

        © 2026 UIU Lost & Found Portal ·
        United International University

    </footer>


</body>

</html>