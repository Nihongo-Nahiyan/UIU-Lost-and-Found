<?php

include "php/db.php";

// Temporary logged-in student for testing
$uid = 1;

$stmt = $conn->prepare("
    SELECT
        c.claim_id,
        c.found_id,
        c.claimant_id,
        c.status,
        c.claim_date,
        f.title AS item_title,
        f.found_location,
        f.user_id AS finder_id,
        finder.name AS finder_name,
        claimant.name AS claimant_name
    FROM claims c
    JOIN found_items f
        ON c.found_id = f.found_id
    JOIN users finder
        ON f.user_id = finder.user_id
    JOIN users claimant
        ON c.claimant_id = claimant.user_id
    WHERE c.claimant_id = ?
    ORDER BY c.claim_date DESC
");

$stmt->bind_param("i", $uid);
$stmt->execute();

$claims = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Claims - UIU Lost & Found</title>

    <link rel="stylesheet" href="student.css">
</head>

<body>

    <header>

        <nav>

            <div class="nav-inner">

                <a href="../public/index.html" class="nav-logo">

                    <div class="logo-box">
                        L&F
                    </div>

                    <div class="brand">
                        UIU Lost <em>&</em> Found
                    </div>

                </a>


                <div class="nav-links">

                    <a href="../student_part1/student-dashboard.html">
                        Dashboard
                    </a>

                    <a href="../student_part1/student-browse.html">
                        Browse
                    </a>

                    <a href="../student_part1/report-lost.html">
                        Report Lost
                    </a>

                    <a href="../student_part1/report-found.html">
                        Report Found
                    </a>

                    <a href="MyClaims.php" class="active">
                        My Claims
                    </a>

                    <a href="messages.php">
                        Messages
                    </a>

                </div>


                <div class="nav-right">

                    <span class="nav-user">
                        👤 Student
                    </span>

                    <a href="../public/index.html" class="btn btn-secondary btn-sm">
                        Logout
                    </a>

                </div>

            </div>

        </nav>

    </header>


    <main>

        <div class="page-wrap-md">

            <div class="page-header">

                <h2>My Claims</h2>

                <p>
                    Track the status of items you have claimed
                </p>

            </div>


            <?php

            if ($claims->num_rows > 0) {

                while ($row = $claims->fetch_assoc()) {

            ?>

                    <div class="report-row">

                        <div>

                            <div class="report-row-title">
                                <?php echo htmlspecialchars($row['item_title']); ?>
                            </div>

                            <div class="report-row-meta">
                                Submitted on
                                <?php echo date("Y-m-d", strtotime($row['claim_date'])); ?>
                            </div>

                        </div>


                        <div>

                            <?php

                            $status = strtolower(trim($row['status']));

                            if ($status == 'pending') {

                                echo '<span class="badge badge-pending">Pending</span>';

                            } elseif ($status == 'approved') {

                                echo '<span class="badge badge-approved">Approved</span>';

                                echo '<a href="messages.php?with='
                                    . (int)$row['finder_id']
                                    . '&found='
                                    . (int)$row['found_id']
                                    . '" class="btn btn-primary btn-sm" style="margin-left: 10px;">'
                                    . 'Contact Finder'
                                    . '</a>';

                            } elseif ($status == 'rejected') {

                                echo '<span class="badge badge-rejected">Rejected</span>';

                            }

                            ?>

                        </div>

                    </div>

            <?php

                }

            } else {

                echo "<p>No claims found.</p>";

            }

            ?>

        </div>

    </main>

</body>

</html>
