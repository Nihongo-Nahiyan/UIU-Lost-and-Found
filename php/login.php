<?php

session_start();

require_once "db.php";


/* ======================================================
   ONLY ACCEPT POST REQUESTS
====================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    exit("Invalid request method.");

}


/* ======================================================
   GET FORM DATA
====================================================== */

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";


if ($email === "" || $password === "") {

    exit("Please enter your email and password.");

}


/* ======================================================
   FIND USER
====================================================== */

$stmt = $conn->prepare(
    "SELECT
        user_id,
        name,
        email,
        password,
        role
     FROM users
     WHERE email = ?
     LIMIT 1"
);


if (!$stmt) {

    http_response_code(500);

    exit("Database query failed.");

}


$stmt->bind_param(
    "s",
    $email
);


$stmt->execute();


$result = $stmt->get_result();


/* ======================================================
   CHECK USER
====================================================== */

if ($result->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    exit("No account found with this email.");

}


$user = $result->fetch_assoc();


/* ======================================================
   CHECK PASSWORD
====================================================== */

if (!password_verify($password, $user["password"])) {

    $stmt->close();
    $conn->close();

    exit("Incorrect password.");

}

/* ======================================================
   CREATE LOGIN SESSION
====================================================== */

$_SESSION["user_id"] = (int)$user["user_id"];

$_SESSION["name"] = $user["name"];

$_SESSION["email"] = $user["email"];

$_SESSION["role"] = $user["role"];


/* ======================================================
   CLOSE DATABASE
====================================================== */

$stmt->close();

$conn->close();


/* ======================================================
   REDIRECT USER
====================================================== */

if ($user["role"] === "admin") {

    header(
        "Location: ../admin/admin-dashboard.php"
    );

    exit();

}


/* ======================================================
   STUDENT
====================================================== */

header(
    "Location: ../student_part1/student-dashboard.php"
);

exit();

?>