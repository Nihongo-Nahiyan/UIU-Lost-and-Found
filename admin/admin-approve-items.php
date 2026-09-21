<?php

include "../php/db.php";


/* PENDING FOUND ITEM COUNT */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE approval_status = 'Pending'
");

$pendingItems = $result->fetch_assoc()["total"];


/* PENDING CLAIM COUNT */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE status = 'Pending'
");

$pendingClaims = $result->fetch_assoc()["total"];


/* CATEGORIES FOR FILTER */

$categoryResult = $conn->query("
    SELECT *
    FROM categories
    ORDER BY category_name
");


/* FIRST TWO PENDING ITEMS */

$pendingSql = "
    SELECT
        f.*,
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

$pendingCards = $conn->query($pendingSql);


/* FILTER VALUES */

$search = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "";
$category = (int)($_GET["category"] ?? 0);


$where = array();


if ($search != "") {

    $safeSearch = $conn->real_escape_string($search);

    $where[] =
        "f.title LIKE '%$safeSearch%'";

}


if (
    $status == "Pending" ||
    $status == "Approved" ||
    $status == "Rejected"
) {

    $safeStatus =
        $conn->real_escape_string($status);

    $where[] =
        "f.approval_status = '$safeStatus'";

}


if ($category > 0) {

    $where[] =
        "f.category_id = $category";

}


$whereSql = "";


if (count($where) > 0) {

    $whereSql =
        "WHERE " . implode(" AND ", $where);

}


/* ALL FOUND ITEMS */

$sql = "
    SELECT
        f.*,
        c.category_name,
        u.name

    FROM found_items f

    JOIN categories c
    ON f.category_id = c.category_id

    JOIN users u
    ON f.user_id = u.user_id

    $whereSql

    ORDER BY f.created_at DESC
";


$items = $conn->query($sql);

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Approve Items</title>

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
               class="nav-item active">

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



    <main class="main">


        <div class="topbar">

            <div class="topbar-left">

                <span class="topbar-title">
                    Approve Found Items
                </span>

                <span class="topbar-sub">
                    Review student found item reports
                </span>

            </div>


            <div class="topbar-right">

                <span class="badge badge-pending">

                    <?php echo $pendingItems; ?> Pending

                </span>

            </div>

        </div>



        <div class="content">


            <!-- FILTER -->

            <form class="filter-bar"
                  method="get">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search items"
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


                <select
                    name="category"
                    class="form-control"
                    onchange="this.form.submit()">

                    <option value="0">
                        All Categories
                    </option>


                    <?php
                    while (
                        $cat =
                        $categoryResult->fetch_assoc()
                    ) {
                    ?>


                        <option
                            value="<?php echo $cat["category_id"]; ?>"
                            <?php
                            if (
                                $category ==
                                $cat["category_id"]
                            ) {
                                echo "selected";
                            }
                            ?>>

                            <?php
                            echo htmlspecialchars(
                                $cat["category_name"]
                            );
                            ?>

                        </option>


                    <?php } ?>


                </select>

            </form>



            <div class="section-row">

                <h3>
                    Pending Approval
                </h3>


                <form
                    action="../php/approve-all-items.php"
                    method="post">

                    <button
                        type="submit"
                        class="btn btn-success btn-sm">

                        Approve All

                    </button>

                </form>

            </div>



            <div class="chart-grid-2">


                <?php
                while (
                    $item =
                    $pendingCards->fetch_assoc()
                ) {
                ?>


                    <div class="card">

                        <div class="card-head">

                            <div>

                                <div class="card-title">

                                    <?php
                                    echo htmlspecialchars(
                                        $item["title"]
                                    );
                                    ?>

                                </div>

                                <div class="card-sub">

                                    <?php
                                    echo htmlspecialchars(
                                        $item["category_name"]
                                    );
                                    ?>

                                </div>

                            </div>


                            <span class="badge badge-pending">
                                Pending
                            </span>

                        </div>


                        <p>

                            Location:

                            <?php
                            echo htmlspecialchars(
                                $item["found_location"] ?? ""
                            );
                            ?>

                        </p>


                        <p>

                            Date:

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

                        </p>


                        <p>

                            Reporter:

                            <?php
                            echo htmlspecialchars(
                                $item["name"]
                            );
                            ?>

                        </p>


                        <br>


                        <p>

                            <?php
                            echo htmlspecialchars(
                                $item["description"]
                            );
                            ?>

                        </p>


                        <br>


                        <form
                            action="../php/approve-item.php"
                            method="post"
                            style="display:inline;">

                            <input
                                type="hidden"
                                name="found_id"
                                value="<?php echo $item["found_id"]; ?>">

                            <button
                                type="submit"
                                class="btn btn-success btn-sm">

                                Approve

                            </button>

                        </form>


                        <form
                            action="../php/reject-item.php"
                            method="post"
                            style="display:inline;">

                            <input
                                type="hidden"
                                name="found_id"
                                value="<?php echo $item["found_id"]; ?>">

                            <button
                                type="submit"
                                class="btn btn-danger btn-sm">

                                Reject

                            </button>

                        </form>

                    </div>


                <?php } ?>


            </div>



            <div class="section-row">

                <h3>
                    All Found Items
                </h3>

            </div>


            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>
                            <th>ITEM</th>
                            <th>CATEGORY</th>
                            <th>REPORTER</th>
                            <th>STATUS</th>
                            <th>ACTION</th>
                        </tr>

                    </thead>


                    <tbody>


                    <?php
                    while (
                        $row =
                        $items->fetch_assoc()
                    ) {
                    ?>


                        <tr>

                            <td class="td-bold">

                                <?php
                                echo htmlspecialchars(
                                    $row["title"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["category_name"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["name"]
                                );
                                ?>

                            </td>


                            <td>


                                <?php
                                if (
                                    $row["approval_status"]
                                    == "Approved"
                                ) {
                                ?>

                                    <span class="badge badge-approved">
                                        Approved
                                    </span>


                                <?php
                                } elseif (
                                    $row["approval_status"]
                                    == "Rejected"
                                ) {
                                ?>

                                    <span class="badge badge-rejected">
                                        Rejected
                                    </span>


                                <?php
                                } else {
                                ?>

                                    <span class="badge badge-pending">
                                        Pending
                                    </span>

                                <?php } ?>


                            </td>


                            <td>


                                <?php
                                if (
                                    $row["approval_status"]
                                    == "Pending"
                                ) {
                                ?>


                                    <div class="td-actions">


                                        <form
                                            action="../php/approve-item.php"
                                            method="post">

                                            <input
                                                type="hidden"
                                                name="found_id"
                                                value="<?php echo $row["found_id"]; ?>">

                                            <button
                                                type="submit"
                                                class="btn btn-success btn-xs">

                                                Approve

                                            </button>

                                        </form>


                                        <form
                                            action="../php/reject-item.php"
                                            method="post">

                                            <input
                                                type="hidden"
                                                name="found_id"
                                                value="<?php echo $row["found_id"]; ?>">

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