<?php

require_once "../php/db.php";


// ======================================================
// GET FILTER VALUES
// ======================================================

$search = trim($_GET["search"] ?? "");
$type = $_GET["type"] ?? "all";
$category = $_GET["category"] ?? "all";


// Only allow valid types
if (!in_array($type, ["all", "lost", "found"])) {
    $type = "all";
}


// ======================================================
// GET CATEGORIES
// ======================================================

$categories = [];

$category_sql = "
    SELECT
        category_id,
        category_name
    FROM categories
    ORDER BY category_name
";

$category_result = $conn->query($category_sql);

if ($category_result) {

    while ($cat = $category_result->fetch_assoc()) {
        $categories[] = $cat;
    }

}


// ======================================================
// BUILD SEARCH CONDITION
// ======================================================

$search_condition_found = "";
$search_condition_lost = "";

$search_value = "";

if ($search !== "") {

    $search_value = "%" . $search . "%";

    $search_condition_found = "
        AND (
            found_items.title LIKE ?
            OR found_items.description LIKE ?
            OR found_items.found_location LIKE ?
        )
    ";

    $search_condition_lost = "
        AND (
            lost_items.title LIKE ?
            OR lost_items.description LIKE ?
            OR lost_items.last_location LIKE ?
        )
    ";
}


// ======================================================
// CATEGORY CONDITION
// ======================================================

$category_condition_found = "";
$category_condition_lost = "";

$category_value = null;

if ($category !== "all" && is_numeric($category)) {

    $category_value = (int)$category;

    $category_condition_found = "
        AND found_items.category_id = ?
    ";

    $category_condition_lost = "
        AND lost_items.category_id = ?
    ";
}


// ======================================================
// GET FOUND ITEMS
// ======================================================

$found_items = [];

if ($type === "all" || $type === "found") {

    $found_sql = "
        SELECT
            found_items.found_id AS item_id,
            'found' AS item_type,

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
            ON found_items.category_id = categories.category_id

        INNER JOIN users
            ON found_items.user_id = users.user_id

        WHERE found_items.approval_status = 'Approved'

        $search_condition_found

        $category_condition_found

        ORDER BY found_items.created_at DESC
    ";


    if ($search !== "" && $category_value !== null) {

        $stmt = $conn->prepare($found_sql);

        $stmt->bind_param(
            "sssi",
            $search_value,
            $search_value,
            $search_value,
            $category_value
        );

        $stmt->execute();

        $found_result = $stmt->get_result();

    } elseif ($search !== "") {

        $stmt = $conn->prepare($found_sql);

        $stmt->bind_param(
            "sss",
            $search_value,
            $search_value,
            $search_value
        );

        $stmt->execute();

        $found_result = $stmt->get_result();

    } elseif ($category_value !== null) {

        $stmt = $conn->prepare($found_sql);

        $stmt->bind_param(
            "i",
            $category_value
        );

        $stmt->execute();

        $found_result = $stmt->get_result();

    } else {

        $found_result = $conn->query($found_sql);

    }


    if ($found_result) {

        while ($item = $found_result->fetch_assoc()) {
            $found_items[] = $item;
        }

    }

}


// ======================================================
// GET LOST ITEMS
// ======================================================

$lost_items = [];

if ($type === "all" || $type === "lost") {

    $lost_sql = "
        SELECT
            lost_items.lost_id AS item_id,
            'lost' AS item_type,

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
            ON lost_items.category_id = categories.category_id

        INNER JOIN users
            ON lost_items.user_id = users.user_id

        WHERE lost_items.status = 'Lost'

        $search_condition_lost

        $category_condition_lost

        ORDER BY lost_items.created_at DESC
    ";


    if ($search !== "" && $category_value !== null) {

        $stmt = $conn->prepare($lost_sql);

        $stmt->bind_param(
            "sssi",
            $search_value,
            $search_value,
            $search_value,
            $category_value
        );

        $stmt->execute();

        $lost_result = $stmt->get_result();

    } elseif ($search !== "") {

        $stmt = $conn->prepare($lost_sql);

        $stmt->bind_param(
            "sss",
            $search_value,
            $search_value,
            $search_value
        );

        $stmt->execute();

        $lost_result = $stmt->get_result();

    } elseif ($category_value !== null) {

        $stmt = $conn->prepare($lost_sql);

        $stmt->bind_param(
            "i",
            $category_value
        );

        $stmt->execute();

        $lost_result = $stmt->get_result();

    } else {

        $lost_result = $conn->query($lost_sql);

    }


    if ($lost_result) {

        while ($item = $lost_result->fetch_assoc()) {
            $lost_items[] = $item;
        }

    }

}


// ======================================================
// COMBINE LOST + FOUND ITEMS
// ======================================================

$items = array_merge(
    $found_items,
    $lost_items
);


// Sort newest first by date
usort(
    $items,
    function ($a, $b) {

        return strcmp(
            $b["item_date"],
            $a["item_date"]
        );

    }
);


// ======================================================
// CLOSE DATABASE CONNECTION
// ======================================================

$conn->close();

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Browse Items - UIU Lost & Found</title>

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
     BROWSE SECTION
================================================== -->

<main class="browse-section">


    <h1>
        Browse Items
    </h1>


    <p class="browse-text">
        Search through all reported lost and found items
    </p>



    <!-- ==================================================
         SEARCH / FILTER
    ================================================== -->

    <form
        class="search-area"
        method="GET"
        action="browse.php"
    >


        <input
            type="text"
            name="search"
            placeholder="Search by name, description or location..."
            value="<?php echo htmlspecialchars($search); ?>"
        >


        <select name="type">

            <option
                value="all"
                <?php echo ($type === "all") ? "selected" : ""; ?>
            >
                All Types
            </option>


            <option
                value="lost"
                <?php echo ($type === "lost") ? "selected" : ""; ?>
            >
                Lost
            </option>


            <option
                value="found"
                <?php echo ($type === "found") ? "selected" : ""; ?>
            >
                Found
            </option>

        </select>



        <select name="category">

            <option value="all">
                All Categories
            </option>


            <?php foreach ($categories as $cat): ?>

                <option
                    value="<?php echo $cat["category_id"]; ?>"
                    <?php
                    echo (
                        (string)$category ===
                        (string)$cat["category_id"]
                    )
                        ? "selected"
                        : "";
                    ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $cat["category_name"]
                    );
                    ?>

                </option>

            <?php endforeach; ?>

        </select>


        <button type="submit">
            Search
        </button>


    </form>



    <!-- ==================================================
         ITEM GRID
    ================================================== -->

    <div class="browse-grid">


        <?php if (count($items) > 0): ?>


            <?php foreach ($items as $item): ?>


                <?php

                /*
                 * Different URL depending on whether
                 * this is a lost or found item.
                 */

                $detail_url =
                    "item-detail.php?id="
                    . urlencode($item["item_id"])
                    . "&type="
                    . urlencode($item["item_type"]);

                ?>


                <a
                    href="<?php echo $detail_url; ?>"
                    class="item-link"
                >


                    <div class="browse-card">


                        <!-- IMAGE -->

                        <div class="browse-image">


                            <?php if (!empty($item["image"])): ?>


                                <?php

                                $image = $item["image"];

                                /*
                                 * If database contains only
                                 * the filename:
                                 *
                                 * image.jpg
                                 *
                                 * use ../uploads/image.jpg
                                 *
                                 * If it already contains:
                                 *
                                 * uploads/image.jpg
                                 *
                                 * use ../uploads/image.jpg
                                 */

                                if (
                                    strpos(
                                        $image,
                                        "uploads/"
                                    ) === 0
                                ) {

                                    $image_url =
                                        "../" . $image;

                                } else {

                                    $image_url =
                                        "../uploads/" . $image;

                                }

                                ?>


                                <img
                                    src="<?php echo htmlspecialchars($image_url); ?>"
                                    alt="<?php echo htmlspecialchars($item["title"]); ?>"
                                >


                            <?php else: ?>


                                <span
                                    style="font-size: 50px;"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $item["icon"] ?? "📦"
                                    );
                                    ?>

                                </span>


                            <?php endif; ?>


                        </div>



                        <!-- TYPE -->

                        <?php if ($item["item_type"] === "found"): ?>


                            <span class="found">
                                Found
                            </span>


                        <?php else: ?>


                            <span class="found">
                                Lost
                            </span>


                        <?php endif; ?>



                        <!-- CATEGORY -->

                        <p class="category">

                            <?php
                            echo htmlspecialchars(
                                $item["category_name"]
                            );
                            ?>

                        </p>



                        <!-- TITLE -->

                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $item["title"]
                            );
                            ?>

                        </h3>



                        <!-- LOCATION -->

                        <p>

                            📍

                            <?php
                            echo htmlspecialchars(
                                $item["location"]
                            );
                            ?>

                        </p>



                        <!-- DATE -->

                        <p>

                            📅

                            <?php
                            echo htmlspecialchars(
                                $item["item_date"]
                            );
                            ?>

                        </p>



                        <!-- BOTTOM -->

                        <div class="item-bottom">


                            <span>

                                by

                                <?php
                                echo htmlspecialchars(
                                    $item["name"]
                                );
                                ?>

                            </span>


                            <?php if ($item["item_type"] === "found"): ?>


                                <span class="approved">
                                    Approved
                                </span>


                            <?php else: ?>


                                <span class="approved">
                                    Lost
                                </span>


                            <?php endif; ?>


                        </div>


                    </div>


                </a>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="no-results">

                <h3>
                    No items found
                </h3>

                <p>
                    Try changing your search or filter.
                </p>

            </div>


        <?php endif; ?>


    </div>


</main>



<!-- ==================================================
     FOOTER
================================================== -->

<footer>

    © 2026 UIU Lost & Found Portal · United International University

</footer>


</body>

</html>