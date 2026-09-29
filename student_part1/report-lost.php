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
    SELECT user_id, name
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
   GET CATEGORIES
===================================================== */

$categories = [];

$categoryResult = $conn->query("
    SELECT category_id, category_name
    FROM categories
    ORDER BY category_name ASC
");

if ($categoryResult) {

    while ($row = $categoryResult->fetch_assoc()) {

        $categories[] = $row;

    }

}


/* =====================================================
   FORM SUBMISSION
===================================================== */

$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* =================================================
       GET FORM VALUES
    ================================================= */

    $title = trim(
        $_POST["title"] ?? ""
    );

    $category_id = (int) (
        $_POST["category_id"] ?? 0
    );

    $lost_date = trim(
        $_POST["lost_date"] ?? ""
    );

    $last_location = trim(
        $_POST["last_location"] ?? ""
    );

    $description = trim(
        $_POST["description"] ?? ""
    );


    /* =================================================
       REQUIRED FIELD CHECK
    ================================================= */

    if (
        $title === "" ||
        $category_id <= 0 ||
        $lost_date === "" ||
        $last_location === ""
    ) {

        $error =
            "Please fill in all required fields.";

    }


    /* =================================================
       IMAGE UPLOAD
    ================================================= */

    $image_name = null;


    if (
        $error === "" &&
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {


        /* ---------------------------------------------
           CHECK UPLOAD ERROR
        --------------------------------------------- */

        if (
            $_FILES["image"]["error"]
            !== UPLOAD_ERR_OK
        ) {

            $error =
                "Image upload failed.";

        }


        /* ---------------------------------------------
           CHECK FILE SIZE
        --------------------------------------------- */

        elseif (
            $_FILES["image"]["size"]
            > 5 * 1024 * 1024
        ) {

            $error =
                "Image must be 5 MB or smaller.";

        }


        /* ---------------------------------------------
           CHECK FILE TYPE
        --------------------------------------------- */

        else {

            $allowed_types = [

                "image/jpeg" => "jpg",
                "image/png"  => "png",
                "image/webp" => "webp",
                "image/gif"  => "gif"

            ];


            $finfo = new finfo(
                FILEINFO_MIME_TYPE
            );


            $mime = $finfo->file(
                $_FILES["image"]["tmp_name"]
            );


            if (
                !isset(
                    $allowed_types[$mime]
                )
            ) {

                $error =
                    "Only JPG, PNG, WEBP and GIF images are allowed.";

            }


            /* -----------------------------------------
               SAVE IMAGE
            ----------------------------------------- */

            else {

                $upload_dir =
                    dirname(__DIR__)
                    . DIRECTORY_SEPARATOR
                    . "uploads";


                if (!is_dir($upload_dir)) {

                    if (
                        !mkdir(
                            $upload_dir,
                            0755,
                            true
                        )
                    ) {

                        $error =
                            "Could not create uploads folder.";

                    }

                }


                if ($error === "") {

                    $filename =
                        bin2hex(
                            random_bytes(16)
                        )
                        . "."
                        . $allowed_types[$mime];


                    $destination =
                        $upload_dir
                        . DIRECTORY_SEPARATOR
                        . $filename;


                    if (
                        !move_uploaded_file(
                            $_FILES["image"]["tmp_name"],
                            $destination
                        )
                    ) {

                        $error =
                            "Could not save uploaded image.";

                    }

                    else {

                        $image_name =
                            $filename;

                    }

                }

            }

        }

    }


    /* =================================================
       INSERT LOST ITEM
    ================================================= */

    if ($error === "") {


        $stmt = $conn->prepare("

            INSERT INTO lost_items
            (
                user_id,
                category_id,
                title,
                description,
                last_location,
                lost_date,
                image
            )

            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )

        ");


        if (!$stmt) {

            $error =
                "Database query preparation failed.";

        }

        else {


            $stmt->bind_param(
                "iisssss",
                $user_id,
                $category_id,
                $title,
                $description,
                $last_location,
                $lost_date,
                $image_name
            );


            if ($stmt->execute()) {

                $stmt->close();

                $conn->close();

                header(
                    "Location: student-dashboard.php?success=lost"
                );

                exit();

            }

            else {

                $error =
                    "Could not save lost item: "
                    . $stmt->error;

                $stmt->close();

            }

        }

    }

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
        Report Lost Item
    </title>


    <link
        rel="stylesheet"
        href="../css/Student_part1.css"
    >

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


            <a
                href="report-lost.php"
                class="active"
            >

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


    <div class="form-page">


        <div class="page-title">


            <h1>

                Report Lost Item

            </h1>


            <p>

                Fill in the details about the item you lost.

            </p>


        </div>



        <!-- ERROR MESSAGE -->

        <?php if ($error !== "") { ?>

            <div
                style="
                    background:#ffe5e5;
                    color:#b00020;
                    padding:12px;
                    border-radius:8px;
                    margin-bottom:20px;
                "
            >

                <?php

                echo htmlspecialchars(
                    $error
                );

                ?>

            </div>

        <?php } ?>



        <!-- FORM -->

        <div class="form-box">


            <form
                action="report-lost.php"
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- ITEM TITLE -->

                <label>

                    Item Title

                </label>


                <input
                    type="text"
                    name="title"
                    placeholder="e.g. Black Leather Wallet"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST["title"] ?? ""
                    );
                    ?>"
                    required
                >



                <!-- CATEGORY + DATE -->

                <div class="form-row">


                    <div>

                        <label>

                            Category

                        </label>


                        <select
                            name="category_id"
                            required
                        >

                            <option value="">

                                Select Category

                            </option>


                            <?php foreach (
                                $categories
                                as $cat
                            ) { ?>

                                <option
                                    value="<?php
                                    echo $cat["category_id"];
                                    ?>"

                                    <?php

                                    if (
                                        (int)(
                                            $_POST[
                                                "category_id"
                                            ] ?? 0
                                        )
                                        ===
                                        (int)$cat[
                                            "category_id"
                                        ]
                                    ) {

                                        echo "selected";

                                    }

                                    ?>
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $cat[
                                            "category_name"
                                        ]
                                    );

                                    ?>

                                </option>

                            <?php } ?>


                        </select>

                    </div>



                    <div>

                        <label>

                            Lost Date

                        </label>


                        <input
                            type="date"
                            name="lost_date"
                            value="<?php
                            echo htmlspecialchars(
                                $_POST["lost_date"] ?? ""
                            );
                            ?>"
                            required
                        >

                    </div>


                </div>



                <!-- LAST LOCATION -->

                <label>

                    Last Location

                </label>


                <input
                    type="text"
                    name="last_location"
                    placeholder="e.g. Library 2F Reading Room"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST["last_location"] ?? ""
                    );
                    ?>"
                    required
                >



                <!-- DESCRIPTION -->

                <label>

                    Description

                </label>


                <textarea
                    name="description"
                    placeholder="Describe the lost item..."
                ><?php

                echo htmlspecialchars(
                    $_POST["description"] ?? ""
                );

                ?></textarea>



                <!-- IMAGE -->

                <label>

                    Upload Image

                </label>


                <input
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                >



                <!-- BUTTONS -->

                <div class="form-buttons">


                    <button
                        type="submit"
                        class="submit-button"
                    >

                        Submit Report

                    </button>


                    <a
                        href="student-dashboard.php"
                        class="cancel-button"
                    >

                        Cancel

                    </a>


                </div>


            </form>


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