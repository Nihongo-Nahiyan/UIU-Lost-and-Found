<?php

include "../php/db.php";


/* PENDING ITEM COUNT */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE approval_status = 'Pending'
");

$pendingItems =
    $result->fetch_assoc()["total"];


/* CLAIM COUNTS */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE status = 'Pending'
");

$pendingClaims =
    $result->fetch_assoc()["total"];


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE status = 'Approved'
");

$approvedClaims =
    $result->fetch_assoc()["total"];


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE status = 'Rejected'
");

$rejectedClaims =
    $result->fetch_assoc()["total"];


/* FILTER */

$search =
    trim($_GET["search"] ?? "");

$status =
    $_GET["status"] ?? "";


$where =
    array();


if ($search != "") {

    $safeSearch =
        $conn->real_escape_string(
            $search
        );

    $where[] = "
        (
            f.title LIKE '%$safeSearch%'
            OR u.name LIKE '%$safeSearch%'
            OR u.student_id LIKE '%$safeSearch%'
        )
    ";

}


if (
    $status == "Pending" ||
    $status == "Approved" ||
    $status == "Rejected"
) {

    $safeStatus =
        $conn->real_escape_string(
            $status
        );

    $where[] =
        "cl.status = '$safeStatus'";

}


$whereSql = "";


if (count($where) > 0) {

    $whereSql =
        "WHERE " .
        implode(
            " AND ",
            $where
        );

}


/* CLAIM DATA */

$sql = "
    SELECT
        cl.claim_id,
        cl.found_id,
        cl.status,
        cl.reason,
        cl.claim_date,

        f.title,

        u.name,
        u.student_id

    FROM claims cl

    JOIN found_items f
    ON cl.found_id = f.found_id

    JOIN users u
    ON cl.claimant_id = u.user_id

    $whereSql

    ORDER BY cl.claim_date DESC
";


$claims =
    $conn->query($sql);

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Claims</title>

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
               class="nav-item active">

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
                    Manage Claims
                </span>

                <span class="topbar-sub">
                    Review ownership claims
                </span>

            </div>

        </div>



        <div class="content">


            <!-- CLAIM STATUS CARDS -->

            <div class="kpi-grid">


                <div class="kpi-card accent-yellow">

                    <div>

                        <div class="kpi-label">
                            Pending
                        </div>

                        <div class="kpi-value">
                            <?php echo $pendingClaims; ?>
                        </div>

                    </div>

                    <div class="kpi-icon">
                        ⏳
                    </div>

                </div>



                <div class="kpi-card accent-green">

                    <div>

                        <div class="kpi-label">
                            Approved
                        </div>

                        <div class="kpi-value">
                            <?php echo $approvedClaims; ?>
                        </div>

                    </div>

                    <div class="kpi-icon">
                        ✔️
                    </div>

                </div>



                <div class="kpi-card accent-orange">

                    <div>

                        <div class="kpi-label">
                            Rejected
                        </div>

                        <div class="kpi-value">
                            <?php echo $rejectedClaims; ?>
                        </div>

                    </div>

                    <div class="kpi-icon">
                        ❌
                    </div>

                </div>


            </div>



            <!-- SEARCH AND FILTER -->

            <form
                method="get"
                class="filter-bar">


                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search claims"
                    value="<?php echo htmlspecialchars($search); ?>">


                <select
                    name="status"
                    class="form-control"
                    onchange="this.form.submit()">


                    <option value="">
                        All Status
                    </option>


                    <option
                        value="Pending"
                        <?php

                        if ($status == "Pending") {
                            echo "selected";
                        }

                        ?>>

                        Pending

                    </option>


                    <option
                        value="Approved"
                        <?php

                        if ($status == "Approved") {
                            echo "selected";
                        }

                        ?>>

                        Approved

                    </option>


                    <option
                        value="Rejected"
                        <?php

                        if ($status == "Rejected") {
                            echo "selected";
                        }

                        ?>>

                        Rejected

                    </option>


                </select>


            </form>



            <!-- CLAIM TABLE TITLE -->

            <div class="section-row">

                <h3>
                    All Claims
                </h3>

            </div>



            <!-- CLAIM TABLE -->

            <div class="table-wrap">

                <table>


                    <thead>

                        <tr>

                            <th>CLAIM ID</th>

                            <th>ITEM</th>

                            <th>CLAIMANT</th>

                            <th>STUDENT ID</th>

                            <th>REASON</th>

                            <th>DATE</th>

                            <th>STATUS</th>

                            <th>ACTION</th>

                        </tr>

                    </thead>



                    <tbody>


                    <?php

                    while (
                        $row =
                        $claims->fetch_assoc()
                    ) {

                    ?>


                        <tr>


                            <!-- CLAIM ID -->

                            <td class="td-mono">

                                CLM-<?php

                                echo str_pad(
                                    $row["claim_id"],
                                    3,
                                    "0",
                                    STR_PAD_LEFT
                                );

                                ?>

                            </td>



                            <!-- ITEM -->

                            <td class="td-bold">

                                <?php

                                echo htmlspecialchars(
                                    $row["title"]
                                );

                                ?>

                            </td>



                            <!-- CLAIMANT -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row["name"]
                                );

                                ?>

                            </td>



                            <!-- STUDENT ID -->

                            <td class="td-mono">

                                <?php

                                echo htmlspecialchars(
                                    $row["student_id"]
                                );

                                ?>

                            </td>



                            <!-- REASON -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row["reason"]
                                );

                                ?>

                            </td>



                            <!-- CLAIM DATE -->

                            <td class="td-mono">

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row["claim_date"]
                                    )
                                );

                                ?>

                            </td>



                            <!-- STATUS -->

                            <td>


                                <?php

                                if (
                                    $row["status"]
                                    == "Pending"
                                ) {

                                ?>


                                    <span class="badge badge-pending">
                                        Pending
                                    </span>


                                <?php

                                } elseif (
                                    $row["status"]
                                    == "Approved"
                                ) {

                                ?>


                                    <span class="badge badge-approved">
                                        Approved
                                    </span>


                                <?php

                                } else {

                                ?>


                                    <span class="badge badge-rejected">
                                        Rejected
                                    </span>


                                <?php } ?>


                            </td>



                            <!-- ACTION -->

                            <td>


                                <?php

                                if (
                                    $row["status"]
                                    == "Pending"
                                ) {

                                ?>


                                    <div class="td-actions">


                                        <!-- APPROVE -->

                                        <form
                                            action="../php/approve-claim.php"
                                            method="post">


                                            <input
                                                type="hidden"
                                                name="claim_id"
                                                value="<?php echo $row["claim_id"]; ?>">


                                            <button
                                                type="submit"
                                                class="btn btn-success btn-xs">

                                                Approve

                                            </button>


                                        </form>



                                        <!-- REJECT -->

                                        <form
                                            action="../php/reject-claim.php"
                                            method="post">


                                            <input
                                                type="hidden"
                                                name="claim_id"
                                                value="<?php echo $row["claim_id"]; ?>">


                                            <button
                                                type="submit"
                                                class="btn btn-danger btn-xs">

                                                Reject

                                            </button>


                                        </form>


                                    </div>


                                <?php

                                } else {

                                ?>


                                    <button
                                        class="btn btn-secondary btn-xs">

                                        View

                                    </button>


                                <?php } ?>


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