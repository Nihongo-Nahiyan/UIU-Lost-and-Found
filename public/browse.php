<?php

include "../php/db.php";

$search = $_GET["search"] ?? "";
$type = $_GET["type"] ?? "all";
$category = $_GET["category"] ?? "all";

$sql = "
    SELECT 
        found_items.found_id,
        found_items.title,
        found_items.description,
        found_items.found_location,
        found_items.found_date,
        found_items.image,
        found_items.approval_status,
        found_items.claimed_status,
        categories.category_name,
        users.name
    FROM found_items
    JOIN categories
        ON found_items.category_id = categories.category_id
    JOIN users
        ON found_items.user_id = users.user_id
    WHERE found_items.approval_status = 'Approved'
";

if ($search != "") {
    $search = $conn->real_escape_string($search);

    $sql .= "
        AND (
            found_items.title LIKE '%$search%'
            OR found_items.found_location LIKE '%$search%'
        )
    ";
}

if ($category != "all") {
    $category = (int)$category;

    $sql .= "
        AND found_items.category_id = $category
    ";
}

$sql .= " ORDER BY found_items.created_at DESC";

$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Items</title>

    <link rel="stylesheet" href="../css/public.css">
</head>

<body>

    <!-- NAVBAR -->
    <header>
        <nav class="navbar">

            <div class="logo">
                <span class="logo-box">L&F</span>
                <span>UIU Lost & Found</span>
            </div>

            <div class="nav-links">
                <a href="index.html">Home</a>
                <a href="browse.php">Browse Items</a>
            </div>

            <div class="nav-buttons">
                <a href="login.html" class="login-btn">Login</a>
                <a href="register.html" class="register-btn">Register</a>
            </div>

        </nav>
    </header>


    <!-- BROWSE SECTION -->
    <main class="browse-section">

        <h1>Browse Items</h1>

        <p class="browse-text">
            Search through all reported lost and found items
        </p>


        <!-- SEARCH -->
        <form class="search-area" method="GET" action="browse.php">

    <input type="text"
           name="search"
           placeholder="Search by name or location..."
           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

    <select name="type">
        <option value="all">All Types</option>
        <option value="lost">Lost</option>
        <option value="found"
            <?php echo (($_GET['type'] ?? '') == 'found') ? 'selected' : ''; ?>>
            Found
        </option>
    </select>

    <select name="category">
    <option value="all">All Categories</option>

    <?php
    $categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");

    while ($cat = $categories->fetch_assoc()) {
    ?>

        <option value="<?php echo $cat['category_id']; ?>"
            <?php echo (($_GET['category'] ?? '') == $cat['category_id']) ? 'selected' : ''; ?>>

            <?php echo htmlspecialchars($cat['category_name']); ?>

        </option>

    <?php } ?>

</select>

    <button type="submit">Search</button>

</form>


        <!-- ITEM GRID -->
                <div class="browse-grid">

            <?php while ($item = $result->fetch_assoc()) { ?>

                <a href="item-detail.php?id=<?php echo $item['found_id']; ?>" class="item-link">

                    <div class="browse-card">

                        <div class="browse-image">
                            <?php
                            if (!empty($item["image"])) {
                                echo '<img src="../' . htmlspecialchars($item["image"]) . '" alt="Item">';
                            } else {
                                echo "📦";
                            }
                            ?>
                        </div>

                        <span class="found">
                            Found
                        </span>

                        <p class="category">
                             <?php echo htmlspecialchars($item["category_name"]); ?>
                            </p>

                        <h3>
                            <?php echo htmlspecialchars($item["title"]); ?>
                        </h3>

                        <p>
                            📍 <?php echo htmlspecialchars($item["found_location"]); ?>
                        </p>

                        <p>
                            📅 <?php echo htmlspecialchars($item["found_date"]); ?>
                        </p>

                        <div class="item-bottom">

                            <span>
                                by <?php echo htmlspecialchars($item["name"]); ?>
                            </span>

                            <span class="approved">
                                Approved
                            </span>

                        </div>

                    </div>

                </a>

            <?php } ?>

        </div>

    </main>


    <footer>
        © 2026 UIU Lost & Found Portal · United International University
    </footer>

</body>
</html>