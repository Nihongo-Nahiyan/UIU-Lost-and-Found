<?php

include "../php/db.php";


/* HEADER COUNTS */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE approval_status = 'Pending'
");

$pendingItems =
    $result->fetch_assoc()["total"];


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE status = 'Pending'
");

$pendingClaims =
    $result->fetch_assoc()["total"];


/* TOTAL LOST */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM lost_items
");

$totalLost =
    $result->fetch_assoc()["total"];


/* APPROVED FOUND ITEMS */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE approval_status = 'Approved'
");

$totalFound =
    $result->fetch_assoc()["total"];


/* APPROVED CLAIMS */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE status = 'Approved'
");

$approvedClaims =
    $result->fetch_assoc()["total"];


/* RESOLVED CASES */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE claimed_status = 'Claimed'
");

$resolvedFound =
    $result->fetch_assoc()["total"];


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM lost_items
    WHERE status = 'Resolved'
");

$resolvedLost =
    $result->fetch_assoc()["total"];


$resolvedCases =
    $resolvedFound + $resolvedLost;


/* CATEGORY REPORT */

$sql = "
    SELECT
        c.category_id,
        c.category_name,

        (
            SELECT COUNT(*)

            FROM lost_items l

            WHERE l.category_id =
                  c.category_id
        )

        AS lost_count,


        (
            SELECT COUNT(*)

            FROM found_items f

            WHERE f.category_id =
                  c.category_id

            AND f.approval_status =
                'Approved'
        )

        AS found_count

    FROM categories c

    ORDER BY c.category_name
";


$categories =
    $conn->query($sql);


$grandTotal =
    $totalLost + $totalFound;

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Reports</title>

    <link rel="stylesheet"
          href="../css/admin.css">

</head>


<body>


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
               class="nav-item">

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
               class="nav-item active">

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



    <main class="main">


        <div class="topbar">

            <div class="topbar-left">

                <span class="topbar-title">
                    Statistics & Reports
                </span>

                <span class="topbar-sub">
                    Lost and found activity
                </span>

            </div>


            <div class="topbar-right">

                <form
                    action="../php/export-report.php"
                    method="get">

                    <button
                        type="submit"
                        class="btn btn-primary btn-sm">

                        Export Report

                    </button>

                </form>

            </div>

        </div>



        <div class="content">


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
                            All time
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
                            Approved
                        </div>

                    </div>

                    <div class="kpi-icon">
                        ✅
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



                <div class="kpi-card accent-purple">

                    <div>

                        <div class="kpi-label">
                            Resolved Cases
                        </div>

                        <div class="kpi-value">
                            <?php echo $resolvedCases; ?>
                        </div>

                        <div class="kpi-sub">
                            Successfully resolved
                        </div>

                    </div>

                    <div class="kpi-icon">
                        🤝
                    </div>

                </div>


            </div>



            <div class="section-row">

                <h3>
                    Category Report
                </h3>


                <form
                    action="../php/export-report.php"
                    method="get">

                    <button
                        type="submit"
                        class="btn btn-secondary btn-sm">

                        Export CSV

                    </button>

                </form>

            </div>



            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>
                            <th>CATEGORY</th>
                            <th>LOST</th>
                            <th>FOUND</th>
                            <th>TOTAL</th>
                            <th>SHARE</th>
                        </tr>

                    </thead>


                    <tbody>


                    <?php
                    while (
                        $row =
                        $categories->fetch_assoc()
                    ) {

                        $total =
                            $row["lost_count"]
                            +
                            $row["found_count"];


                        if ($grandTotal > 0) {

                            $percentage =
                                round(
                                    (
                                        $total /
                                        $grandTotal
                                    )
                                    *
                                    100
                                );

                        } else {

                            $percentage = 0;

                        }

                    ?>


                        <tr>


                            <td class="td-bold">

                                <?php
                                echo htmlspecialchars(
                                    $row["category_name"]
                                );
                                ?>

                            </td>


                            <td>

                                <span class="badge badge-lost">

                                    <?php
                                    echo $row["lost_count"];
                                    ?>

                                </span>

                            </td>


                            <td>

                                <span class="badge badge-found">

                                    <?php
                                    echo $row["found_count"];
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo $total;
                                ?>

                            </td>


                            <td>

                                <div class="prog-row">

                                    <div class="prog-bar">

                                        <div
                                            class="prog-fill"
                                            style="width: <?php echo $percentage; ?>%;">
                                        </div>

                                    </div>


                                    <span class="prog-pct">

                                        <?php
                                        echo $percentage;
                                        ?>%

                                    </span>

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