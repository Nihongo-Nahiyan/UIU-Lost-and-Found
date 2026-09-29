<?php

session_start();

require_once "../php/db.php";


/* =====================================================
   CHECK LOGIN
===================================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: ../public/login.html");
    exit();

}

$user_id = (int) $_SESSION["user_id"];


/* =====================================================
   GET LOGGED-IN STUDENT
===================================================== */

$stmt = $conn->prepare("
    SELECT user_id, name, student_id, email
    FROM users
    WHERE user_id = ?
      AND role = 'student'
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


if (!$user) {

    session_destroy();

    header("Location: ../public/login.html");
    exit();

}


/* =====================================================
   MY LOST REPORTS
===================================================== */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM lost_items
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$myLostReports = $result->fetch_assoc()["total"];

$stmt->close();


/* =====================================================
   MY FOUND REPORTS
===================================================== */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$myFoundReports = $result->fetch_assoc()["total"];

$stmt->close();


/* =====================================================
   MY CLAIMS
===================================================== */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE claimant_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$myClaims = $result->fetch_assoc()["total"];

$stmt->close();


/* =====================================================
   PENDING CLAIMS
===================================================== */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE claimant_id = ?
      AND status = 'Pending'
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$pendingClaims = $result->fetch_assoc()["total"];

$stmt->close();


/* =====================================================
   UNREAD MESSAGES
===================================================== */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM messages
    WHERE receiver_id = ?
      AND is_read = 0
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$unreadMessages = $result->fetch_assoc()["total"];

$stmt->close();


/* =====================================================
   RECENT REPORTS
===================================================== */

$stmt = $conn->prepare("
    SELECT *
    FROM (

        SELECT
            lost_id AS report_id,
            title,
            last_location AS location,
            lost_date AS report_date,
            status AS report_status,
            'Lost' AS report_type,
            created_at

        FROM lost_items

        WHERE user_id = ?


        UNION ALL


        SELECT
            found_id AS report_id,
            title,
            found_location AS location,
            found_date AS report_date,
            approval_status AS report_status,
            'Found' AS report_type,
            created_at

        FROM found_items

        WHERE user_id = ?

    ) AS reports

    ORDER BY created_at DESC

    LIMIT 3
");

$stmt->bind_param(
    "ii",
    $user_id,
    $user_id
);

$stmt->execute();

$recentReports = $stmt->get_result();

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard</title>


    <link rel="stylesheet"
          href="../css/Student_part1.css">

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<header>

    <nav class="navbar">


        <div class="logo-area">

            <div class="logo-box">

                L&F

            </div>


            <h2>

                UIU Lost & Found

            </h2>

        </div>



        <div class="nav-links">


            <a href="student-dashboard.php"
               class="active">

                Dashboard

            </a>


            <a href="student-browse.php">

                Browse

            </a>


            <a href="report-lost.php">

                Report Lost

            </a>


            <a href="report-found.php">

                Report Found

            </a>


            <a href="../student/MyClaims.html">

                My Claims

            </a>


            <a href="../student/messages_stu1.html">

                Messages

            </a>


        </div>



        <div class="student-area">


            <span>

                👤

                <?php

                echo htmlspecialchars(
                    $user["name"]
                );

                ?>

            </span>


            <a href="../public/index.php">

                Logout

            </a>


        </div>


    </nav>

</header>



<!-- =====================================================
     MAIN
===================================================== -->

<main>


    <div class="dashboard-container">


        <!-- WELCOME -->

        <div class="welcome">


            <p>

                Good day,

            </p>


            <h1>

                <?php

                echo htmlspecialchars(
                    $user["name"]
                );

                ?>

            </h1>


        </div>



        <!-- =================================================
             SUMMARY BOXES
        ================================================== -->

        <div class="summary-grid">


            <!-- LOST -->

            <div class="summary-box">


                <h3>

                    My Lost Reports

                </h3>


                <h1>

                    <?php

                    echo $myLostReports;

                    ?>

                </h1>


            </div>



            <!-- FOUND -->

            <div class="summary-box">


                <h3>

                    My Found Reports

                </h3>


                <h1>

                    <?php

                    echo $myFoundReports;

                    ?>

                </h1>


            </div>



            <!-- CLAIMS -->

            <div class="summary-box">


                <h3>

                    My Claims

                </h3>


                <h1>

                    <?php

                    echo $myClaims;

                    ?>

                </h1>


                <p>

                    <?php

                    echo $pendingClaims;

                    ?>

                    pending

                </p>


            </div>



            <!-- MESSAGES -->

            <div class="summary-box">


                <h3>

                    Unread Messages

                </h3>


                <h1>

                    <?php

                    echo $unreadMessages;

                    ?>

                </h1>


                <p>

                    <?php

                    echo $unreadMessages;

                    ?>

                    unread

                </p>


            </div>


        </div>



        <!-- =================================================
             BOTTOM AREA
        ================================================== -->

        <div class="dashboard-bottom">



            <!-- =================================================
                 RECENT REPORTS
            ================================================== -->

            <div class="recent-reports">


                <h2>

                    My Recent Reports

                </h2>



                <?php

                if ($recentReports->num_rows > 0) {


                    while (
                        $report =
                        $recentReports->fetch_assoc()
                    ) {


                        /*
                         * Decide which CSS class to use
                         */

                        $statusClass = "lost";


                        if (
                            $report["report_status"] === "Approved" ||
                            $report["report_status"] === "Resolved"
                        ) {

                            $statusClass = "approved";

                        }

                ?>


                        <div class="report-card">


                            <div>


                                <h3>

                                    <?php

                                    echo htmlspecialchars(
                                        $report["title"]
                                    );

                                    ?>

                                </h3>


                                <p>

                                    📍

                                    <?php

                                    echo htmlspecialchars(
                                        $report["location"]
                                    );

                                    ?>

                                    ·

                                    <?php

                                    echo date(
                                        "Y-m-d",
                                        strtotime(
                                            $report["report_date"]
                                        )
                                    );

                                    ?>

                                </p>


                            </div>



                            <span
                                class="<?php echo $statusClass; ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $report["report_status"]
                                );

                                ?>

                            </span>


                        </div>


                <?php

                    }

                } else {

                ?>


                    <div class="report-card">


                        <div>


                            <h3>

                                No reports yet

                            </h3>


                            <p>

                                Your recent lost or found
                                reports will appear here.

                            </p>


                        </div>


                    </div>


                <?php

                }

                ?>


            </div>



            <!-- =================================================
                 QUICK ACTIONS
            ================================================== -->

            <div class="quick-actions">


                <h2>

                    Quick Actions

                </h2>



                <a href="report-lost.php"
                   class="quick-button orange">

                    + Report Lost Item

                </a>



                <a href="report-found.php"
                   class="quick-button">

                    + Report Found Item

                </a>



                <a href="student-browse.php"
                   class="quick-button">

                    Browse All Items

                </a>



                <a href="../student/MyClaims.html"
                   class="quick-button">

                    My Claims

                </a>



                <a href="../student/messages_stu1.html"
                   class="quick-button">

                    Messages

                </a>


            </div>


        </div>


    </div>


</main>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer>

    © 2026 UIU Lost & Found Portal ·
    United International University

</footer>



</body>

</html>


<?php

$conn->close();

?>