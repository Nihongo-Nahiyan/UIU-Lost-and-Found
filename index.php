<?php
session_start();

require_once "../php/db.php";

/* ======================================================
   STATISTICS
====================================================== */

/* Approved found items */
$found_count = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE approval_status = 'Approved'
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $found_count = (int)$row['total'];
}


/* Reunited / claimed items */
$reunited_count = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE claimed_status = 'Claimed'
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $reunited_count = (int)$row['total'];
}


/* Total claims */
$claim_count = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM claims
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $claim_count = (int)$row['total'];
}


/* Total users */
$user_count = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM users
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $user_count = (int)$row['total'];
}


/* ======================================================
   RECENTLY POSTED ITEMS
   Shows both APPROVED FOUND and ACTIVE LOST items
====================================================== */

$recent_items = [];

$sql = "

    /* FOUND ITEMS */
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

        users.name,

        found_items.created_at

    FROM found_items

    INNER JOIN categories
        ON found_items.category_id = categories.category_id

    INNER JOIN users
        ON found_items.user_id = users.user_id

    WHERE found_items.approval_status = 'Approved'


    UNION ALL


    /* LOST ITEMS */
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

        users.name,

        lost_items.created_at

    FROM lost_items

    INNER JOIN categories
        ON lost_items.category_id = categories.category_id

    INNER JOIN users
        ON lost_items.user_id = users.user_id

    WHERE lost_items.status = 'Lost'

    ORDER BY created_at DESC

    LIMIT 3
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $recent_items[] = $row;
    }
}


/* ======================================================
   CLOSE DATABASE CONNECTION
====================================================== */

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

    <title>UIU Lost & Found</title>

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

            <!-- HTML page containing the login form -->
            <a
                href="login.html"
                class="login-btn"
            >
                Login
            </a>

            <!-- HTML page containing the registration form -->
            <a
                href="register.html"
                class="register-btn"
            >
                Register
            </a>

        </div>

    </nav>

</header>


<main>


<!-- ==================================================
     HERO
================================================== -->

<section class="hero">


    <div class="hero-left">

        <p class="small-title">
            UIU Campus Portal
        </p>


        <h1>

            Lost something?

            <br>

            <span>
                We help you find it.
            </span>

        </h1>


        <p class="description">

            The official United International University
            lost and found portal. Report lost items,
            browse found ones, and connect with finders.

        </p>


        <div class="hero-buttons">

            <a
                href="browse.php"
                class="orange-btn"
            >
                Browse Found Items
            </a>


            <a
                href="register.html"
                class="white-btn"
            >
                Register Now
            </a>

        </div>

    </div>


    <!-- ==================================================
         DYNAMIC STATISTICS
    ================================================== -->

    <div class="stats">


        <div class="stat-box">

            <div class="icon">
                🎯
            </div>

            <h2>
                <?php echo $found_count; ?>
            </h2>

            <p>
                Items Found
            </p>

        </div>


        <div class="stat-box">

            <div class="icon">
                🤝
            </div>

            <h2>
                <?php echo $reunited_count; ?>
            </h2>

            <p>
                Reunited
            </p>

        </div>


        <div class="stat-box">

            <div class="icon">
                📋
            </div>

            <h2>
                <?php echo $claim_count; ?>
            </h2>

            <p>
                Claims
            </p>

        </div>


        <div class="stat-box">

            <div class="icon">
                👥
            </div>

            <h2>
                <?php echo $user_count; ?>
            </h2>

            <p>
                Active Users
            </p>

        </div>


    </div>

</section>


<!-- ==================================================
     RECENTLY POSTED
================================================== -->

<section class="recent">


    <div class="recent-heading">

        <div>

            <h2>
                Recently Posted
            </h2>

            <p>
                Latest lost and found reports from campus
            </p>

        </div>


        <a
            href="browse.php"
            class="view-btn"
        >
            View All →
        </a>

    </div>


    <div class="item-grid">


        <?php if (count($recent_items) > 0): ?>


            <?php foreach ($recent_items as $item): ?>


                <a
                    href="item-detail.php?id=<?php echo (int)$item['item_id']; ?>&type=<?php echo urlencode($item['item_type']); ?>"
                    class="item-card"
                >


                    <!-- IMAGE -->

                    <div class="item-image">

                        <?php if (!empty($item['image'])): ?>

                            <img
                                src="../uploads/<?php echo htmlspecialchars($item['image']); ?>"
                                alt="<?php echo htmlspecialchars($item['title']); ?>"
                            >

                        <?php else: ?>

                            <span style="font-size: 50px;">

                                <?php
                                echo htmlspecialchars(
                                    $item['icon'] ?? '📦'
                                );
                                ?>

                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- TYPE -->

                    <?php if ($item['item_type'] === 'found'): ?>

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

                        <?php
                        echo htmlspecialchars(
                            $item['category_name']
                        );
                        ?>

                    </p>


                    <!-- TITLE -->

                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $item['title']
                        );
                        ?>

                    </h3>


                    <!-- LOCATION -->

                    <p>

                        📍

                        <?php
                        echo htmlspecialchars(
                            $item['location']
                        );
                        ?>

                    </p>


                    <!-- DATE -->

                    <p>

                        📅

                        <?php
                        echo htmlspecialchars(
                            $item['item_date']
                        );
                        ?>

                    </p>


                    <!-- BOTTOM -->

                    <div class="item-bottom">

                        <span>

                            by

                            <?php
                            echo htmlspecialchars(
                                $item['name']
                            );
                            ?>

                        </span>


                        <?php if ($item['item_type'] === 'found'): ?>

                            <span class="approved">

                                <?php
                                echo htmlspecialchars(
                                    $item['approval_status']
                                );
                                ?>

                            </span>

                        <?php else: ?>

                            <span class="approved">

                                Lost

                            </span>

                        <?php endif; ?>


                    </div>


                </a>


            <?php endforeach; ?>


        <?php else: ?>


            <p>
                No recent items have been posted yet.
            </p>


        <?php endif; ?>


    </div>

</section>


<!-- ==================================================
     HOW IT WORKS
================================================== -->

<section class="how-it-works">


    <h2>
        How It Works
    </h2>


    <div class="steps">


        <div class="step">

            <h3>
                01
            </h3>

            <h4>
                Report
            </h4>

            <p>
                Submit a lost or found item report.
            </p>

        </div>


        <div class="step">

            <h3>
                02
            </h3>

            <h4>
                Browse
            </h4>

            <p>
                Search through reported lost and found items.
            </p>

        </div>


        <div class="step">

            <h3>
                03
            </h3>

            <h4>
                Claim
            </h4>

            <p>
                Submit a claim with your item details.
            </p>

        </div>


        <div class="step">

            <h3>
                04
            </h3>

            <h4>
                Reunite
            </h4>

            <p>
                Connect with the finder and receive your item.
            </p>

        </div>


    </div>

</section>


<!-- ==================================================
     READY SECTION
================================================== -->

<section class="ready">


    <h2>
        Ready to get started?
    </h2>


    <p>
        UIU Lost & Found helps students report and recover lost items.
    </p>


    <div class="ready-buttons">


        <a
            href="browse.php"
            class="orange-btn"
        >
            Browse All Items
        </a>


        <a
            href="register.html"
            class="white-btn"
        >
            Create an Account
        </a>


    </div>


</section>


</main>


<!-- ==================================================
     FOOTER
================================================== -->

<footer>

    © 2026 UIU Lost & Found Portal · United International University

</footer>


</body>

</html>