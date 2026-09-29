<?php

session_start();

require_once "../php/db.php";


/* =====================================================
   CHECK LOGIN
===================================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: ../public/login.html");
    exit();

}


/* =====================================================
   GET LOGGED-IN USER
===================================================== */

$user_id = (int) $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT user_id, name
    FROM users
    WHERE user_id = ?
      AND role = 'student'
    LIMIT 1
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


if (!$user) {

    session_destroy();

    header("Location: ../public/login.html");
    exit();

}


/* =====================================================
   SEARCH / FILTER VALUES
===================================================== */

$search = trim($_GET["search"] ?? "");

$type = $_GET["type"] ?? "all";

$category = $_GET["category"] ?? "all";


/* =====================================================
   GET CATEGORIES FROM DATABASE
===================================================== */

$categories = [];

$categoryResult = $conn->query("
    SELECT category_id, category_name
    FROM categories
    ORDER BY category_name ASC
");

if ($categoryResult) {

    while ($row = $categoryResult->fetch_assoc()) {

        $categories[] = $row;

    }

}


/* =====================================================
   PREPARE SEARCH VALUE
===================================================== */

$searchValue = "%" . $search . "%";


/* =====================================================
   BUILD THE ITEM QUERY
=====================================================

   LOST ITEMS
   +
   APPROVED FOUND ITEMS

   Both are converted into the same columns so
   they can be displayed together.
===================================================== */

$sql = "

    SELECT *

    FROM (

        /* =========================================
           LOST ITEMS
        ========================================= */

        SELECT

            l.lost_id AS item_id,

            l.title,

            l.description,

            l.last_location AS location,

            l.lost_date AS item_date,

            l.image AS image,

            l.status AS item_status,

            'Lost' AS item_type,

            c.category_name,

            u.name AS reporter_name,

            l.created_at

        FROM lost_items l

        INNER JOIN categories c
            ON l.category_id = c.category_id

        INNER JOIN users u
            ON l.user_id = u.user_id


        /* =========================================
           FOUND ITEMS
        ========================================= */

        UNION ALL

        SELECT

            f.found_id AS item_id,

            f.title,

            f.description,

            f.found_location AS location,

            f.found_date AS item_date,

            f.image AS image,

            f.approval_status AS item_status,

            'Found' AS item_type,

            c.category_name,

            u.name AS reporter_name,

            f.created_at

        FROM found_items f

        INNER JOIN categories c
            ON f.category_id = c.category_id

        INNER JOIN users u
            ON f.user_id = u.user_id

        WHERE f.approval_status = 'Approved'

    ) AS items

    WHERE 1 = 1
";


/* =====================================================
   SEARCH FILTER
===================================================== */

if ($search !== "") {

    $sql .= "

        AND (
            title LIKE ?
            OR description LIKE ?
            OR location LIKE ?
        )

    ";

}


/* =====================================================
   TYPE FILTER
===================================================== */

if ($type === "lost") {

    $sql .= "
        AND item_type = 'Lost'
    ";

}

elseif ($type === "found") {

    $sql .= "
        AND item_type = 'Found'
    ";

}


/* =====================================================
   CATEGORY FILTER
===================================================== */

if ($category !== "all") {

    $sql .= "
        AND category_name = ?
    ";

}


/* =====================================================
   ORDER
===================================================== */

$sql .= "

    ORDER BY created_at DESC

";


/* =====================================================
   PREPARE QUERY
===================================================== */

$stmt = $conn->prepare($sql);


/* =====================================================
   BIND PARAMETERS
===================================================== */

if ($search !== "" && $category !== "all") {

    $stmt->bind_param(
        "ssss",
        $searchValue,
        $searchValue,
        $searchValue,
        $category
    );

}

elseif ($search !== "") {

    $stmt->bind_param(
        "sss",
        $searchValue,
        $searchValue,
        $searchValue
    );

}

elseif ($category !== "all") {

    $stmt->bind_param(
        "s",
        $category
    );

}


/* =====================================================
   EXECUTE
===================================================== */

$stmt->execute();

$items = $stmt->get_result();

?>


<!DOCTYPE html>

<html lang="en">


<head>

    <meta charset="UTF-8">


    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">


    <title>
        Browse Items
    </title>


    <link rel="stylesheet"
          href="../css/Student_part1.css">

</head>



<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<header>

    <nav class="navbar">


        <!-- LOGO -->

        <div class="logo-area">


            <div class="logo-box">

                L&F

            </div>


            <h2>

                UIU Lost & Found

            </h2>


        </div>



        <!-- NAVIGATION -->

        <div class="nav-links">


            <a href="student-dashboard.php">

                Dashboard

            </a>


            <a href="student-browse.php"
               class="active">

                Browse

            </a>


            <a href="report-lost.php">

                Report Lost

            </a>


            <a href="report-found.php">

                Report Found

            </a>


            <a href="../student/MyClaims.php">

                My Claims

            </a>


            <a href="../student/messages.php">

                Messages

            </a>


        </div>



        <!-- STUDENT -->

        <div class="student-area">


            <span>

                👤

                <?php

                echo htmlspecialchars(
                    $user["name"]
                );

                ?>

            </span>


            <a href="../public/index.php">

                Logout

            </a>


        </div>


    </nav>

</header>



<!-- =====================================================
     MAIN
===================================================== -->

<main>


    <div class="browse-container">


        <!-- =================================================
             PAGE TITLE
        ================================================== -->

        <div class="page-title">


            <h1>

                Browse Items

            </h1>


            <p>

                Search through all reported lost and found items.

            </p>


        </div>



        <!-- =================================================
             FILTER BAR
        ================================================== -->

        <form
            method="GET"
            action="student-browse.php"
            class="filter-bar"
        >


            <!-- SEARCH -->

            <input
                type="text"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search by name or location..."
            >



            <!-- TYPE -->

            <select name="type">


                <option
                    value="all"
                    <?php
                    if ($type === "all") {
                        echo "selected";
                    }
                    ?>
                >

                    All Types

                </option>


                <option
                    value="lost"
                    <?php
                    if ($type === "lost") {
                        echo "selected";
                    }
                    ?>
                >

                    Lost

                </option>


                <option
                    value="found"
                    <?php
                    if ($type === "found") {
                        echo "selected";
                    }
                    ?>
                >

                    Found

                </option>


            </select>



            <!-- CATEGORY -->

            <select name="category">


                <option
                    value="all"
                    <?php
                    if ($category === "all") {
                        echo "selected";
                    }
                    ?>
                >

                    All Categories

                </option>


                <?php foreach ($categories as $cat) { ?>


                    <option
                        value="<?php
                        echo htmlspecialchars(
                            $cat["category_name"]
                        );
                        ?>"

                        <?php

                        if (
                            $category ===
                            $cat["category_name"]
                        ) {

                            echo "selected";

                        }

                        ?>
                    >

                        <?php

                        echo htmlspecialchars(
                            $cat["category_name"]
                        );

                        ?>

                    </option>


                <?php } ?>


            </select>



            <!-- SEARCH BUTTON -->

            <button type="submit">

                Search

            </button>


        </form>



        <!-- =================================================
             ITEMS
        ================================================== -->

        <div class="items-grid">


            <?php


            if ($items->num_rows > 0) {


                while ($item = $items->fetch_assoc()) {


                    /* =====================================
                       ITEM INFORMATION
                    ===================================== */

                    $itemId =
                        (int) $item["item_id"];


                    $itemType =
                        strtolower(
                            $item["item_type"]
                        );


                    $title =
                        htmlspecialchars(
                            $item["title"]
                        );


                    $description =
                        htmlspecialchars(
                            $item["description"] ?? ""
                        );


                    $location =
                        htmlspecialchars(
                            $item["location"]
                        );


                    $categoryName =
                        htmlspecialchars(
                            $item["category_name"]
                        );


                    $reporterName =
                        htmlspecialchars(
                            $item["reporter_name"]
                        );


                    $itemDate =
                        htmlspecialchars(
                            $item["item_date"]
                        );


                    /* =====================================
                       IMAGE
                    ===================================== */

                    $image = $item["image"] ?? "";


                    /*
                       If the database contains:

                       wallet.jfif

                       and the actual file is:

                       /uploads/wallet.jfif

                       then this becomes:

                       ../uploads/wallet.jfif
                    */

                    if ($image !== "") {

                        /*
                           Remove accidental leading
                           slash/path if necessary.
                        */

                        $image =
                            basename($image);

                        $imagePath =
                            "../uploads/" .
                            $image;

                    }

                    else {

                        $imagePath = "";

                    }


                    /* =====================================
                       BADGE CLASS
                    ===================================== */

                    if ($itemType === "found") {

                        $badgeClass =
                            "found-badge";

                    }

                    else {

                        $badgeClass =
                            "lost-badge";

                    }


                    /* =====================================
                       ITEM DETAIL LINK
                    ===================================== */

                    $detailLink =
                        "../student_part1/item-detail.php?id="
                        . $itemId
                        . "&type="
                        . $itemType;


            ?>


                    <!-- =================================================
                         ITEM CARD
                    ================================================== -->

                    <a
                        href="<?php
                        echo htmlspecialchars(
                            $detailLink
                        );
                        ?>"
                        class="item-link"
                    >


                        <div class="item-card">


                            <!-- IMAGE -->

                            <div class="item-image">


                                <?php

                                if (
                                    $imagePath !== ""
                                ) {

                                ?>


                                    <img
                                        src="<?php
                                        echo htmlspecialchars(
                                            $imagePath
                                        );
                                        ?>"
                                        alt="<?php
                                        echo $title;
                                        ?>"
                                    >


                                <?php

                                }

                                else {

                                ?>


                                    <div class="no-image">

                                        No Image

                                    </div>


                                <?php

                                }

                                ?>


                            </div>



                            <!-- DETAILS -->

                            <div class="item-details">


                                <!-- TYPE -->

                                <span
                                    class="<?php
                                    echo $badgeClass;
                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $item["item_type"]
                                    );

                                    ?>

                                </span>



                                <!-- CATEGORY -->

                                <p class="category">

                                    <?php

                                    echo $categoryName;

                                    ?>

                                </p>



                                <!-- TITLE -->

                                <h3>

                                    <?php

                                    echo $title;

                                    ?>

                                </h3>



                                <!-- LOCATION -->

                                <p>

                                    📍

                                    <?php

                                    echo $location;

                                    ?>

                                </p>



                                <!-- DATE -->

                                <p>

                                    📅

                                    <?php

                                    echo $itemDate;

                                    ?>

                                </p>



                                <!-- REPORTER -->

                                <p>

                                    by

                                    <?php

                                    echo $reporterName;

                                    ?>

                                </p>



                                <!-- STATUS -->

                                <span class="status-badge">

                                    <?php

                                    echo htmlspecialchars(
                                        $item["item_status"]
                                    );

                                    ?>

                                </span>


                            </div>


                        </div>


                    </a>


            <?php

                }


            }

            else {


            ?>


                <!-- =================================================
                     NO RESULTS
                ================================================== -->

                <div class="no-results">


                    <h3>

                        No items found

                    </h3>


                    <p>

                        Try changing your search or filter.

                    </p>


                </div>


            <?php

            }


            ?>


        </div>


    </div>


</main>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer>

    © 2026 UIU Lost & Found Portal ·
    United International University

</footer>



</body>

</html>


<?php

$stmt->close();

$conn->close();

?>