<?php

include "../php/db.php";


/* TOTAL LOST REPORTS */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM lost_items
");

$totalLost = $result->fetch_assoc()["total"];


/* LOST REPORTS THIS WEEK */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM lost_items
    WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
");

$lostThisWeek = $result->fetch_assoc()["total"];


/* APPROVED FOUND ITEMS */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE approval_status = 'Approved'
");

$totalFound = $result->fetch_assoc()["total"];


/* PENDING FOUND ITEMS */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE approval_status = 'Pending'
");

$pendingItems = $result->fetch_assoc()["total"];


/* APPROVED CLAIMS */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE status = 'Approved'
");

$approvedClaims = $result->fetch_assoc()["total"];


/* PENDING CLAIMS */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE status = 'Pending'
");

$pendingClaims = $result->fetch_assoc()["total"];


/* RECENT PENDING FOUND ITEMS */

$sql = "
    SELECT
        f.found_id,
        f.title,
        f.found_date,
        c.category_name,
        u.name

    FROM found_items f

    JOIN categories c
    ON f.category_id = c.category_id

    JOIN users u
    ON f.user_id = u.user_id

    WHERE f.approval_status = 'Pending'

    ORDER BY f.created_at DESC

    LIMIT 2
";

$pendingResult = $conn->query($sql);

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    <link rel="stylesheet"
          href="../css/admin.css">
</head>

<body>


    <!-- HEADER -->

    <header class="header">

        <div class="header-logo">

            <div class="logo-box">
                L&F
            </div>

            <div class="brand">
                UIU Lost <em>&</em> Found
            </div>

        </div>


        <div class="header-section">

            <a href="admin-dashboard.php"
               class="nav-item active">

                <span class="nav-icon">📊</span>
                Dashboard

            </a>


            <a href="admin-approve-items.php"
               class="nav-item">

                <span class="nav-icon">✅</span>
                Approve Items

                <span class="nav-badge">
                    <?php echo $pendingItems; ?>
                </span>

            </a>


            <a href="admin-manage-claims.php"
               class="nav-item">

                <span class="nav-icon">📋</span>
                Claims

                <span class="nav-badge">
                    <?php echo $pendingClaims; ?>
                </span>

            </a>


            <a href="admin-manage-categories.php"
               class="nav-item">

                <span class="nav-icon">🗂️</span>
                Categories

            </a>


            <a href="admin-manage-users.php"
               class="nav-item">

                <span class="nav-icon">👥</span>
                Users

            </a>


            <a href="admin-reports.php"
               class="nav-item">

                <span class="nav-icon">📈</span>
                Reports

            </a>

        </div>


        <div class="header-rightside">

            <div class="user-row">

                <div class="user-avatar">
                    A
                </div>

                <div>

                    <div class="user-name">
                        Admin
                    </div>

                    <div class="user-role">
                        Administrator
                    </div>

                </div>

            </div>


            <a href="../public/login.html"
               class="logout-btn">

                Logout

            </a>

        </div>

    </header>



    <!-- MAIN -->

    <main class="main">


        <div class="topbar">

            <div class="topbar-left">

                <span class="topbar-title">
                    Dashboard
                </span>

                <span class="topbar-sub">
                    UIU Lost & Found Admin Panel
                </span>

            </div>

        </div>



        <div class="content">


            <!-- KPI CARDS -->

            <div class="kpi-grid">


                <div class="kpi-card accent-orange">

                    <div>

                        <div class="kpi-label">
                            Total Lost Reports
                        </div>

                        <div class="kpi-value">
                            <?php echo $totalLost; ?>
                        </div>

                        <div class="kpi-sub">
                            +<?php echo $lostThisWeek; ?> this week
                        </div>

                    </div>

                    <div class="kpi-icon">
                        🔍
                    </div>

                </div>



                <div class="kpi-card accent-blue">

                    <div>

                        <div class="kpi-label">
                            Found Items
                        </div>

                        <div class="kpi-value">
                            <?php echo $totalFound; ?>
                        </div>

                        <div class="kpi-sub">
                            Approved items
                        </div>

                    </div>

                    <div class="kpi-icon">
                        ✅
                    </div>

                </div>



                <div class="kpi-card accent-yellow">

                    <div>

                        <div class="kpi-label">
                            Pending Approvals
                        </div>

                        <div class="kpi-value">
                            <?php echo $pendingItems; ?>
                        </div>

                        <div class="kpi-sub">
                            Needs review
                        </div>

                    </div>

                    <div class="kpi-icon">
                        ⏳
                    </div>

                </div>



                <div class="kpi-card accent-green">

                    <div>

                        <div class="kpi-label">
                            Approved Claims
                        </div>

                        <div class="kpi-value">
                            <?php echo $approvedClaims; ?>
                        </div>

                        <div class="kpi-sub">
                            Items returned
                        </div>

                    </div>

                    <div class="kpi-icon">
                        ✔️
                    </div>

                </div>

            </div>



            <!-- PENDING ITEMS -->

            <div class="section-row">

                <h3>
                    Pending Found Items
                </h3>

                <a href="admin-approve-items.php"
                   class="btn btn-primary btn-sm">

                    View All

                </a>

            </div>


            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>
                            <th>ITEM</th>
                            <th>CATEGORY</th>
                            <th>REPORTER</th>
                            <th>DATE</th>
                            <th>ACTION</th>
                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($item = $pendingResult->fetch_assoc()) { ?>


                        <tr>

                            <td class="td-bold">

                                <?php
                                echo htmlspecialchars(
                                    $item["title"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $item["category_name"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $item["name"]
                                );
                                ?>

                            </td>


                            <td class="td-mono">

                                <?php

                                if ($item["found_date"]) {

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $item["found_date"]
                                        )
                                    );

                                }

                                ?>

                            </td>


                            <td>

                                <div class="td-actions">


                                    <form action="../php/approve-item.php"
                                          method="post">

                                        <input
                                            type="hidden"
                                            name="found_id"
                                            value="<?php echo $item["found_id"]; ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-success btn-xs">

                                            Approve

                                        </button>

                                    </form>



                                    <form action="../php/reject-item.php"
                                          method="post">

                                        <input
                                            type="hidden"
                                            name="found_id"
                                            value="<?php echo $item["found_id"]; ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-xs">

                                            Reject

                                        </button>

                                    </form>


                                </div>

                            </td>

                        </tr>


                    <?php } ?>


                    </tbody>

                </table>

            </div>


        </div>

    </main>

</body>

</html>