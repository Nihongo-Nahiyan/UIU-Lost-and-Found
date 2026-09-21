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


/* EDIT CATEGORY */

$editCategory = null;


if (isset($_GET["edit"])) {

    $editId =
        (int)$_GET["edit"];


    $stmt =
        $conn->prepare("
            SELECT *
            FROM categories
            WHERE category_id = ?
        ");


    $stmt->bind_param(
        "i",
        $editId
    );


    $stmt->execute();


    $editCategory =
        $stmt
        ->get_result()
        ->fetch_assoc();

}


/* CATEGORY LIST */

$categories =
    $conn->query("
        SELECT *
        FROM categories
        ORDER BY category_name
    ");

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Categories</title>

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
               class="nav-item active">

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
                    Manage Categories
                </span>

                <span class="topbar-sub">
                    Add and manage item categories
                </span>

            </div>

        </div>



        <div class="content">


            <div class="chart-grid-2">


                <!-- ADD CATEGORY -->

                <div>

                    <div class="section-row">

                        <h3>

                            <?php
                            if ($editCategory) {
                                echo "Edit Category";
                            } else {
                                echo "Add Category";
                            }
                            ?>

                        </h3>

                    </div>


                    <div class="card">


                        <form
                            action="<?php
                            if ($editCategory) {
                                echo "../php/edit-category.php";
                            } else {
                                echo "../php/add-category.php";
                            }
                            ?>"
                            method="post">


                            <?php
                            if ($editCategory) {
                            ?>

                                <input
                                    type="hidden"
                                    name="category_id"
                                    value="<?php echo $editCategory["category_id"]; ?>">

                            <?php } ?>


                            <div class="form-group">

                                <label>
                                    Category Name
                                </label>

                                <input
                                    type="text"
                                    name="category_name"
                                    class="form-control"
                                    placeholder="Enter category"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $editCategory["category_name"]
                                        ?? ""
                                    );
                                    ?>"
                                    required>

                            </div>



                            <div class="form-group">

                                <label>
                                    Icon
                                </label>

                                <input
                                    type="text"
                                    name="icon"
                                    class="form-control"
                                    placeholder="Enter icon"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $editCategory["icon"]
                                        ?? ""
                                    );
                                    ?>">

                            </div>



                            <div class="form-group">

                                <label>
                                    Description
                                </label>

                                <textarea
                                    name="description"
                                    class="form-control"
                                    placeholder="Enter description"><?php
                                    echo htmlspecialchars(
                                        $editCategory["description"]
                                        ?? ""
                                    );
                                    ?></textarea>

                            </div>



                            <button
                                type="submit"
                                class="btn btn-primary">

                                <?php
                                if ($editCategory) {
                                    echo "Save Changes";
                                } else {
                                    echo "Add Category";
                                }
                                ?>

                            </button>


                            <?php
                            if ($editCategory) {
                            ?>

                                <a
                                    href="admin-manage-categories.php"
                                    class="btn btn-secondary">

                                    Cancel

                                </a>

                            <?php } ?>


                        </form>

                    </div>

                </div>



                <!-- CATEGORIES -->

                <div>

                    <div class="section-row">

                        <h3>
                            Categories
                        </h3>

                    </div>


                    <?php
                    while (
                        $row =
                        $categories->fetch_assoc()
                    ) {
                    ?>


                        <div class="card">

                            <div class="card-head">

                                <div>

                                    <div class="card-title">

                                        <?php
                                        echo htmlspecialchars(
                                            $row["icon"]
                                            ?? ""
                                        );
                                        ?>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["category_name"]
                                        );
                                        ?>

                                    </div>


                                    <div class="card-sub">

                                        <?php
                                        echo htmlspecialchars(
                                            $row["description"]
                                            ?? ""
                                        );
                                        ?>

                                    </div>

                                </div>



                                <div class="td-actions">


                                    <a
                                        href="admin-manage-categories.php?edit=<?php echo $row["category_id"]; ?>"
                                        class="btn btn-secondary btn-xs">

                                        Edit

                                    </a>


                                    <form
                                        action="../php/delete-category.php"
                                        method="post">

                                        <input
                                            type="hidden"
                                            name="category_id"
                                            value="<?php echo $row["category_id"]; ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-xs">

                                            Remove

                                        </button>

                                    </form>


                                </div>

                            </div>

                        </div>


                        <br>


                    <?php } ?>


                </div>


            </div>

        </div>

    </main>

</body>

</html>