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
   GET ITEM ID AND TYPE
===================================================== */

$item_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

$type = $_GET["type"] ?? "found";


if ($item_id <= 0) {

    die("Invalid item.");

}


/* =====================================================
   VARIABLES
===================================================== */

$item = null;
$error = "";
$success = "";



/* =====================================================
   SUBMIT CLAIM
===================================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && $type === "found"
    && isset($_POST["submit_claim"])
) {


    $reason = trim(
        $_POST["reason"] ?? ""
    );


    if ($reason === "") {

        $error =
            "Please provide a reason for your claim.";

    }

    else {


        /* ---------------------------------------------
           Check that the found item exists
        --------------------------------------------- */

        $check = $conn->prepare("

            SELECT found_id
            FROM found_items
            WHERE found_id = ?
              AND approval_status = 'Approved'
            LIMIT 1

        ");

        $check->bind_param(
            "i",
            $item_id
        );

        $check->execute();

        $checkResult =
            $check->get_result();

        $check->close();


        if ($checkResult->num_rows === 0) {

            $error =
                "This item is not available for claiming.";

        }

        else {


            /* -----------------------------------------
               Check whether this student already
               submitted a claim
            ----------------------------------------- */

            $checkClaim = $conn->prepare("

                SELECT claim_id
                FROM claims
                WHERE found_id = ?
                  AND claimant_id = ?
                LIMIT 1

            ");

            $checkClaim->bind_param(
                "ii",
                $item_id,
                $user_id
            );

            $checkClaim->execute();

            $claimResult =
                $checkClaim->get_result();

            $checkClaim->close();


            if ($claimResult->num_rows > 0) {

                $error =
                    "You have already submitted a claim for this item.";

            }

            else {


                /* -------------------------------------
                   Insert claim
                ------------------------------------- */

                $stmt = $conn->prepare("

                    INSERT INTO claims
                    (
                        found_id,
                        claimant_id,
                        reason,
                        status
                    )

                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        'Pending'
                    )

                ");


                if (!$stmt) {

                    $error =
                        "Could not prepare claim.";

                }

                else {


                    $stmt->bind_param(
                        "iis",
                        $item_id,
                        $user_id,
                        $reason
                    );


                    if ($stmt->execute()) {

                        $success =
                            "Your claim has been submitted successfully.";

                    }

                    else {

                        $error =
                            "Could not submit claim: "
                            . $stmt->error;

                    }


                    $stmt->close();

                }

            }

        }

    }

}



/* =====================================================
   GET ITEM
===================================================== */

if ($type === "found") {


    $stmt = $conn->prepare("

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

            users.name AS reporter_name

        FROM found_items

        JOIN categories
            ON found_items.category_id =
               categories.category_id

        JOIN users
            ON found_items.user_id =
               users.user_id

        WHERE found_items.found_id = ?

        LIMIT 1

    ");


}
else {


    $stmt = $conn->prepare("

        SELECT
            lost_items.lost_id,
            lost_items.title,
            lost_items.description,
            lost_items.last_location,
            lost_items.lost_date,
            lost_items.image,
            lost_items.status,

            categories.category_name,

            users.name AS reporter_name

        FROM lost_items

        JOIN categories
            ON lost_items.category_id =
               categories.category_id

        JOIN users
            ON lost_items.user_id =
               users.user_id

        WHERE lost_items.lost_id = ?

        LIMIT 1

    ");

}


$stmt->bind_param(
    "i",
    $item_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$item =
    $result->fetch_assoc();

$stmt->close();



/* =====================================================
   ITEM NOT FOUND
===================================================== */

if (!$item) {

    die("Item not found.");

}


/* =====================================================
   CHECK WHETHER CURRENT USER ALREADY CLAIMED IT
===================================================== */

$already_claimed = false;


if ($type === "found") {


    $claimCheck = $conn->prepare("

        SELECT claim_id
        FROM claims
        WHERE found_id = ?
          AND claimant_id = ?
        LIMIT 1

    ");


    $claimCheck->bind_param(
        "ii",
        $item_id,
        $user_id
    );


    $claimCheck->execute();


    $claimResult =
        $claimCheck->get_result();


    if ($claimResult->num_rows > 0) {

        $already_claimed = true;

    }


    $claimCheck->close();

}


?>


<!DOCTYPE html>

<html lang="en">


<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php
        echo htmlspecialchars(
            $item["title"]
        );
        ?>
        - UIU Lost & Found
    </title>


    <link
        rel="stylesheet"
        href="../css/Student_part1.css"
    >


    <style>

        .detail-page {

            max-width: 900px;

            margin: 40px auto;

            padding: 0 20px;

        }


        .back-button {

            display: inline-block;

            padding: 10px 16px;

            border: 1px solid #ddd;

            border-radius: 6px;

            text-decoration: none;

            color: #333;

            margin-bottom: 25px;

        }


        .detail-card {

            background: white;

            border: 1px solid #ddd;

            border-radius: 12px;

            overflow: hidden;

        }


        .detail-image {

            width: 100%;

            height: 400px;

            background: #eeeeee;

            display: flex;

            align-items: center;

            justify-content: center;

            overflow: hidden;

        }


        .detail-image img {

            width: 100%;

            height: 100%;

            object-fit: cover;

        }


        .no-image {

            font-size: 70px;

        }


        .detail-content {

            padding: 30px;

        }


        .badges {

            display: flex;

            gap: 10px;

            align-items: center;

            margin-bottom: 15px;

        }


        .badge {

            padding: 5px 12px;

            border-radius: 20px;

            font-size: 13px;

        }


        .found-badge {

            background: #dbeafe;

            color: #1d4ed8;

        }


        .lost-badge {

            background: #fee2e2;

            color: #b91c1c;

        }


        .approved-badge {

            background: #d1fae5;

            color: #047857;

        }


        .category-text {

            color: #888;

            font-size: 14px;

        }


        .detail-title {

            font-size: 32px;

            margin-bottom: 25px;

        }


        .detail-grid {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 25px;

            margin-bottom: 25px;

        }


        .detail-label {

            font-size: 12px;

            color: #999;

            text-transform: uppercase;

            margin-bottom: 7px;

        }


        .detail-value {

            font-size: 16px;

        }


        .description-box {

            background: #f8f8f8;

            border: 1px solid #eee;

            padding: 20px;

            border-radius: 8px;

            margin-top: 20px;

        }


        .message {

            padding: 12px 15px;

            border-radius: 7px;

            margin-bottom: 20px;

        }


        .success-message {

            background: #d1fae5;

            color: #047857;

        }


        .error-message {

            background: #fee2e2;

            color: #b91c1c;

        }


        .claim-box {

            margin-top: 25px;

            padding-top: 25px;

            border-top: 1px solid #eee;

        }


        .claim-box textarea {

            width: 100%;

            min-height: 120px;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 7px;

            resize: vertical;

            margin-bottom: 15px;

        }


        .claim-button {

            background: #ff5a00;

            color: white;

            border: none;

            padding: 12px 20px;

            border-radius: 6px;

            cursor: pointer;

            font-weight: bold;

        }


        .claim-button:hover {

            background: #e64f00;

        }


        .claimed-message {

            background: #f3f4f6;

            padding: 15px;

            border-radius: 7px;

            margin-top: 20px;

        }


    </style>

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


            <a href="student-dashboard.php">

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

                👤 Student

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


    <div class="detail-page">


        <a
            href="student-browse.php"
            class="back-button"
        >

            ← Back to Browse

        </a>



        <div class="detail-card">


            <!-- IMAGE -->

            <div class="detail-image">


                <?php

                if (
                    !empty(
                        $item["image"]
                    )
                ) {

                    ?>

                    <img
                        src="../uploads/<?php
                        echo htmlspecialchars(
                            $item["image"]
                        );
                        ?>"
                        alt="<?php
                        echo htmlspecialchars(
                            $item["title"]
                        );
                        ?>"
                    >

                    <?php

                }

                else {

                    ?>

                    <div class="no-image">

                        📦

                    </div>

                    <?php

                }

                ?>


            </div>



            <!-- CONTENT -->

            <div class="detail-content">


                <!-- MESSAGES -->

                <?php if ($success !== "") { ?>

                    <div class="message success-message">

                        <?php
                        echo htmlspecialchars(
                            $success
                        );
                        ?>

                    </div>

                <?php } ?>


                <?php if ($error !== "") { ?>

                    <div class="message error-message">

                        <?php
                        echo htmlspecialchars(
                            $error
                        );
                        ?>

                    </div>

                <?php } ?>



                <!-- BADGES -->

                <div class="badges">


                    <?php if ($type === "found") { ?>


                        <span class="badge found-badge">

                            Found

                        </span>


                        <?php if (
                            $item["approval_status"]
                            === "Approved"
                        ) { ?>

                            <span class="badge approved-badge">

                                Approved

                            </span>

                        <?php } ?>


                    <?php } else { ?>


                        <span class="badge lost-badge">

                            Lost

                        </span>


                    <?php } ?>


                    <span class="category-text">

                        <?php

                        echo htmlspecialchars(
                            $item["category_name"]
                        );

                        ?>

                    </span>


                </div>



                <!-- TITLE -->

                <h1 class="detail-title">

                    <?php

                    echo htmlspecialchars(
                        $item["title"]
                    );

                    ?>

                </h1>



                <!-- DETAILS -->

                <div class="detail-grid">


                    <div>


                        <div class="detail-label">

                            Location

                        </div>


                        <div class="detail-value">

                            📍

                            <?php

                            if (
                                $type === "found"
                            ) {

                                echo htmlspecialchars(
                                    $item[
                                        "found_location"
                                    ]
                                );

                            }

                            else {

                                echo htmlspecialchars(
                                    $item[
                                        "last_location"
                                    ]
                                );

                            }

                            ?>

                        </div>


                    </div>



                    <div>


                        <div class="detail-label">

                            Date

                        </div>


                        <div class="detail-value">

                            📅

                            <?php

                            if (
                                $type === "found"
                            ) {

                                echo htmlspecialchars(
                                    $item[
                                        "found_date"
                                    ]
                                );

                            }

                            else {

                                echo htmlspecialchars(
                                    $item[
                                        "lost_date"
                                    ]
                                );

                            }

                            ?>

                        </div>


                    </div>



                    <div>


                        <div class="detail-label">

                            Reported By

                        </div>


                        <div class="detail-value">

                            <?php

                            echo htmlspecialchars(
                                $item[
                                    "reporter_name"
                                ]
                            );

                            ?>

                        </div>


                    </div>


                </div>



                <!-- DESCRIPTION -->

                <div class="description-box">


                    <div class="detail-label">

                        Description

                    </div>


                    <div>

                        <?php

                        if (
                            !empty(
                                $item["description"]
                            )
                        ) {

                            echo nl2br(
                                htmlspecialchars(
                                    $item[
                                        "description"
                                    ]
                                )
                            );

                        }

                        else {

                            echo "No description provided.";

                        }

                        ?>

                    </div>


                </div>



                <!-- =================================================
                     CLAIM SECTION
                ================================================== -->

                <?php

                if (
                    $type === "found"
                    &&
                    $item["approval_status"]
                    === "Approved"
                ) {

                ?>


                    <div class="claim-box">


                        <?php

                        if ($already_claimed) {

                        ?>


                            <div class="claimed-message">

                                You have already submitted a
                                claim for this item.

                                <br><br>

                                You can check your claim status
                                from <b>My Claims</b>.

                            </div>


                        <?php

                        }

                        else {

                        ?>


                            <h3>

                                Is this your item?

                            </h3>


                            <p>

                                Submit a claim and explain
                                why you believe this item
                                belongs to you.

                            </p>


                            <br>


                            <form
                                method="POST"
                                action=""
                            >


                                <textarea
                                    name="reason"
                                    placeholder="Explain identifying details about the item..."
                                    required
                                ></textarea>


                                <button
                                    type="submit"
                                    name="submit_claim"
                                    class="claim-button"
                                >

                                    Submit a Claim

                                </button>


                            </form>


                        <?php

                        }

                        ?>


                    </div>


                <?php

                }

                ?>


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