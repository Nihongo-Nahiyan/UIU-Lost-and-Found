<?php

session_start();

require_once "../php/db.php";


// ======================================================
// GET ITEM ID AND TYPE
// ======================================================

$item_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$item_type = $_GET["type"] ?? "found";


// Only allow found or lost
if (!in_array($item_type, ["found", "lost"])) {
    $item_type = "found";
}


// If no valid ID was supplied
if ($item_id <= 0) {

    http_response_code(400);

    exit("Invalid item ID.");

}


// ======================================================
// GET FOUND ITEM
// ======================================================

if ($item_type === "found") {


    $sql = "

        SELECT

            found_items.found_id AS item_id,

            found_items.title,

            found_items.description,

            found_items.found_location AS location,

            found_items.found_date AS item_date,

            found_items.image,

            found_items.approval_status,

            found_items.claimed_status,

            categories.category_name,

            categories.icon,

            users.name

        FROM found_items

        INNER JOIN categories
            ON found_items.category_id =
               categories.category_id

        INNER JOIN users
            ON found_items.user_id =
               users.user_id

        WHERE found_items.found_id = ?

          AND found_items.approval_status = 'Approved'

        LIMIT 1
    ";


    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        http_response_code(500);

        exit("Database query preparation failed.");

    }


    $stmt->bind_param(
        "i",
        $item_id
    );


    $stmt->execute();


    $result = $stmt->get_result();


    $item = $result->fetch_assoc();


    $stmt->close();


}


// ======================================================
// GET LOST ITEM
// ======================================================

else {


    $sql = "

        SELECT

            lost_items.lost_id AS item_id,

            lost_items.title,

            lost_items.description,

            lost_items.last_location AS location,

            lost_items.lost_date AS item_date,

            lost_items.image,

            lost_items.status AS approval_status,

            NULL AS claimed_status,

            categories.category_name,

            categories.icon,

            users.name

        FROM lost_items

        INNER JOIN categories
            ON lost_items.category_id =
               categories.category_id

        INNER JOIN users
            ON lost_items.user_id =
               users.user_id

        WHERE lost_items.lost_id = ?

          AND lost_items.status = 'Lost'

        LIMIT 1
    ";


    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        http_response_code(500);

        exit("Database query preparation failed.");

    }


    $stmt->bind_param(
        "i",
        $item_id
    );


    $stmt->execute();


    $result = $stmt->get_result();


    $item = $result->fetch_assoc();


    $stmt->close();

}


// ======================================================
// ITEM NOT FOUND
// ======================================================

if (!$item) {

    http_response_code(404);

    exit("Item not found.");

}


// ======================================================
// CLOSE DATABASE
// ======================================================

$conn->close();


// ======================================================
// PREPARE DISPLAY VALUES
// ======================================================

$title = htmlspecialchars(
    $item["title"]
);

$description = htmlspecialchars(
    $item["description"] ?? ""
);

$category = htmlspecialchars(
    $item["category_name"]
);

$location = htmlspecialchars(
    $item["location"]
);

$item_date = htmlspecialchars(
    $item["item_date"]
);

$reporter = htmlspecialchars(
    $item["name"]
);


// ======================================================
// IMAGE URL
// ======================================================

$image_url = null;

if (!empty($item["image"])) {

    $image = $item["image"];

    if (strpos($image, "uploads/") === 0) {

        $image_url = "../" . $image;

    } else {

        $image_url = "../uploads/" . $image;

    }

}


// ======================================================
// TYPE LABEL
// ======================================================

$type_label =
    ($item_type === "found")
    ? "Found"
    : "Lost";


?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo $title; ?> - UIU Lost & Found
    </title>

    <link
        rel="stylesheet"
        href="../css/public.css"
    >

</head>


<body>


<!-- ==================================================
     NAVBAR
================================================== -->

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

            <a href="index.php">
                Home
            </a>

            <a href="browse.php">
                Browse Items
            </a>

        </div>


        <div class="nav-buttons">

            <a
                href="login.html"
                class="login-btn"
            >
                Login
            </a>


            <a
                href="register.html"
                class="register-btn"
            >
                Register
            </a>

        </div>


    </nav>

</header>


<!-- ==================================================
     ITEM DETAILS
================================================== -->

<main class="detail-page">


    <a
        href="browse.php"
        class="back-link"
    >

        ← Back to Browse

    </a>


    <div class="detail-box">


        <!-- ==================================================
             IMAGE
        ================================================== -->

        <div class="detail-image">


            <?php if ($image_url): ?>


                <img
                    src="<?php echo htmlspecialchars($image_url); ?>"
                    alt="<?php echo $title; ?>"
                >


            <?php else: ?>


                <span style="font-size: 65px;">

                    <?php
                    echo htmlspecialchars(
                        $item["icon"] ?? "📦"
                    );
                    ?>

                </span>


            <?php endif; ?>


        </div>


        <!-- ==================================================
             INFORMATION
        ================================================== -->

        <div class="detail-info">


            <!-- TYPE -->

            <?php if ($item_type === "found"): ?>


                <span class="found">
                    Found
                </span>


            <?php else: ?>


                <span class="lost">
                    Lost
                </span>


            <?php endif; ?>


            <!-- CATEGORY -->

            <p class="category">

                <?php echo $category; ?>

            </p>


            <!-- TITLE -->

            <h1>

                <?php echo $title; ?>

            </h1>


            <!-- LOCATION -->

            <p>

                📍

                <?php echo $location; ?>

            </p>


            <!-- DATE -->

            <p>

                📅

                <?php echo $item_date; ?>

            </p>


            <!-- DESCRIPTION -->

            <h3>
                Description
            </h3>


            <p>

                <?php

                if ($description !== "") {

                    echo nl2br($description);

                } else {

                    echo "No description provided.";

                }

                ?>

            </p>


            <!-- REPORTED BY -->

            <h3>
                Reported By
            </h3>


            <p>

                <?php echo $reporter; ?>

            </p>


            <!-- ==================================================
                 FOUND STATUS
            ================================================== -->

            <?php if ($item_type === "found"): ?>


                <h3>
                    Status
                </h3>


                <p>

                    <?php

                    if (
                        $item["claimed_status"]
                        === "Claimed"
                    ) {

                        echo "Already Claimed";

                    } else {

                        echo "Available for Claim";

                    }

                    ?>

                </p>


            <?php endif; ?>


            <!-- ==================================================
                 CLAIM BUTTON
            ================================================== -->

            <?php if ($item_type === "found"): ?>


                <?php if (
                    $item["claimed_status"]
                    !== "Claimed"
                ): ?>


                    <a
                        href="login.html"
                        class="orange-btn"
                    >

                        Claim This Item

                    </a>


                <?php else: ?>


                    <p>
                        This item has already been claimed.
                    </p>


                <?php endif; ?>


            <?php else: ?>


                <a
                    href="login.html"
                    class="orange-btn"
                >

                    Login to Report a Match

                </a>


            <?php endif; ?>


        </div>


    </div>


</main>


<!-- ==================================================
     FOOTER
================================================== -->

<footer>

    © 2026 UIU Lost & Found Portal ·
    United International University

</footer>


</body>

</html>