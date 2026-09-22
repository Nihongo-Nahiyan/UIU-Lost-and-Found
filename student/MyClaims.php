<?php

include "php/db.php";

$sql = "SELECT claims.claim_id,
               found_items.title,
               claims.status,
               claims.claim_date
        FROM claims
        JOIN found_items
        ON claims.found_id = found_items.found_id
        WHERE claims.claim_id IN (1, 2, 5)
        ORDER BY claims.claim_id ASC";
        
$result = $conn->query($sql);

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

                    <a href="messages_stu1.html">
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

            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

            ?>

                    <div class="report-row">

                        <div>

                            <div class="report-row-title">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </div>

                            <div class="report-row-meta">
                                Submitted on
                                <?php echo date("Y-m-d", strtotime($row['claim_date'])); ?>
                            </div>

                        </div>


                        <?php

                        if ($row['status'] == 'Pending') {

                            echo '<span class="badge badge-pending">Pending</span>';

                        } elseif ($row['status'] == 'Approved') {

                            echo '<span class="badge badge-approved">Approved</span>';

                        } elseif ($row['status'] == 'Rejected') {

                            echo '<span class="badge badge-rejected">Rejected</span>';

                        }

                        ?>

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