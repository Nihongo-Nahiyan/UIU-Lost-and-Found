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


/* STUDENT COUNT */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'student'
");

$totalStudents =
    $result->fetch_assoc()["total"];


/* ADMIN COUNT */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'admin'
");

$totalAdmins =
    $result->fetch_assoc()["total"];


/* NEW USERS THIS MONTH */

$result = $conn->query("
    SELECT COUNT(*) AS total

    FROM users

    WHERE MONTH(created_at)
          = MONTH(CURDATE())

    AND YEAR(created_at)
        = YEAR(CURDATE())
");

$newUsers =
    $result->fetch_assoc()["total"];


/* FILTER */

$search =
    trim($_GET["search"] ?? "");

$role =
    $_GET["role"] ?? "";


$where =
    array();


if ($search != "") {

    $safeSearch =
        $conn->real_escape_string(
            $search
        );

    $where[] = "
        (
            u.name LIKE '%$safeSearch%'
            OR u.email LIKE '%$safeSearch%'
            OR u.student_id LIKE '%$safeSearch%'
        )
    ";

}


if (
    $role == "student" ||
    $role == "admin"
) {

    $safeRole =
        $conn->real_escape_string(
            $role
        );

    $where[] =
        "u.role = '$safeRole'";

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


/* USER DATA */

$sql = "
    SELECT
        u.*,

        (
            SELECT COUNT(*)

            FROM lost_items l

            WHERE l.user_id = u.user_id
        )

        +

        (
            SELECT COUNT(*)

            FROM found_items f

            WHERE f.user_id = u.user_id
        )

        AS reports

    FROM users u

    $whereSql

    ORDER BY u.created_at DESC
";


$users =
    $conn->query($sql);

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Users</title>

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
               class="nav-item active">

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
                    Manage Users
                </span>

                <span class="topbar-sub">
                    View registered users
                </span>

            </div>

        </div>



        <div class="content">


            <div class="kpi-grid">


                <div class="kpi-card accent-teal">

                    <div>

                        <div class="kpi-label">
                            Total Students
                        </div>

                        <div class="kpi-value">
                            <?php echo $totalStudents; ?>
                        </div>

                    </div>

                    <div class="kpi-icon">
                        🎓
                    </div>

                </div>



                <div class="kpi-card accent-orange">

                    <div>

                        <div class="kpi-label">
                            Admins
                        </div>

                        <div class="kpi-value">
                            <?php echo $totalAdmins; ?>
                        </div>

                    </div>

                    <div class="kpi-icon">
                        🛡️
                    </div>

                </div>



                <div class="kpi-card accent-green">

                    <div>

                        <div class="kpi-label">
                            New This Month
                        </div>

                        <div class="kpi-value">
                            <?php echo $newUsers; ?>
                        </div>

                    </div>

                    <div class="kpi-icon">
                        ✨
                    </div>

                </div>


            </div>



            <form
                method="get"
                class="filter-bar">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search user"
                    value="<?php echo htmlspecialchars($search); ?>">


                <select
                    name="role"
                    class="form-control"
                    onchange="this.form.submit()">

                    <option value="">
                        All Roles
                    </option>

                    <option
                        value="student"
                        <?php
                        if ($role == "student") {
                            echo "selected";
                        }
                        ?>>

                        Student

                    </option>

                    <option
                        value="admin"
                        <?php
                        if ($role == "admin") {
                            echo "selected";
                        }
                        ?>>

                        Admin

                    </option>

                </select>

            </form>



            <div class="section-row">

                <h3>
                    Registered Users
                </h3>

            </div>



            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>
                            <th>USER</th>
                            <th>STUDENT ID</th>
                            <th>EMAIL</th>
                            <th>ROLE</th>
                            <th>REPORTS</th>
                            <th>ACTION</th>
                        </tr>

                    </thead>


                    <tbody>


                    <?php
                    while (
                        $row =
                        $users->fetch_assoc()
                    ) {
                    ?>


                        <tr>


                            <td class="td-bold">

                                <?php
                                echo htmlspecialchars(
                                    $row["name"]
                                );
                                ?>

                            </td>


                            <td class="td-mono">

                                <?php
                                echo htmlspecialchars(
                                    $row["student_id"]
                                );
                                ?>

                            </td>


                            <td class="td-muted">

                                <?php
                                echo htmlspecialchars(
                                    $row["email"]
                                );
                                ?>

                            </td>


                            <td>


                                <?php
                                if (
                                    $row["role"]
                                    == "admin"
                                ) {
                                ?>

                                    <span class="badge badge-admin">
                                        Admin
                                    </span>


                                <?php
                                } else {
                                ?>

                                    <span class="badge badge-student">
                                        Student
                                    </span>

                                <?php } ?>


                            </td>


                            <td>

                                <?php
                                echo $row["reports"];
                                ?>

                            </td>


                            <td>


                                <?php
                                if (
                                    $row["role"]
                                    == "admin"
                                ) {
                                ?>


                                    <button
                                        class="btn btn-secondary btn-xs">

                                        Protected

                                    </button>


                                <?php
                                } else {
                                ?>


                                    <div class="td-actions">


                                        <button
                                            class="btn btn-secondary btn-xs">

                                            View

                                        </button>


                                        <form
                                            action="../php/delete-user.php"
                                            method="post">

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?php echo $row["user_id"]; ?>">

                                            <button
                                                type="submit"
                                                class="btn btn-danger btn-xs">

                                                Remove

                                            </button>

                                        </form>


                                    </div>


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