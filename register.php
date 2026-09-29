<?php

require_once "db.php";


// ======================================================
// ONLY ACCEPT POST REQUEST
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    exit("Invalid request method.");

}


// ======================================================
// GET FORM DATA
// ======================================================

$name = trim($_POST["name"] ?? "");
$student_id = trim($_POST["student_id"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$plain_password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";


// ======================================================
// REQUIRED FIELD CHECK
// ======================================================

if (
    $name === "" ||
    $student_id === "" ||
    $email === "" ||
    $phone === "" ||
    $plain_password === "" ||
    $confirm_password === ""
) {

    exit("Please fill in all fields.");

}


// ======================================================
// PASSWORD MATCH
// ======================================================

if ($plain_password !== $confirm_password) {

    exit("Passwords do not match.");

}
$password = password_hash($plain_password, PASSWORD_DEFAULT);

// ======================================================
// CHECK IF EMAIL ALREADY EXISTS
// ======================================================

$check = $conn->prepare(
    "SELECT user_id
     FROM users
     WHERE email = ?
     LIMIT 1"
);

if (!$check) {

    http_response_code(500);

    exit("Database query failed.");

}

$check->bind_param(
    "s",
    $email
);

$check->execute();

$result = $check->get_result();


if ($result->num_rows > 0) {

    $check->close();
    $conn->close();

    exit("This email is already registered.");

}

$check->close();


// ======================================================
// CHECK IF STUDENT ID ALREADY EXISTS
// ======================================================

$check = $conn->prepare(
    "SELECT user_id
     FROM users
     WHERE student_id = ?
     LIMIT 1"
);

if (!$check) {

    http_response_code(500);

    exit("Database query failed.");

}

$check->bind_param(
    "s",
    $student_id
);

$check->execute();

$result = $check->get_result();


if ($result->num_rows > 0) {

    $check->close();
    $conn->close();

    exit("This student ID is already registered.");

}

$check->close();


// ======================================================
// INSERT STUDENT
// ======================================================

$stmt = $conn->prepare(
    "INSERT INTO users
    (
        name,
        student_id,
        email,
        phone,
        password,
        role
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        'student'
    )"
);


if (!$stmt) {

    http_response_code(500);

    exit("Could not prepare registration.");

}


$stmt->bind_param(
    "sssss",
    $name,
    $student_id,
    $email,
    $phone,
    $password
);


// ======================================================
// SAVE USER
// ======================================================

if ($stmt->execute()) {

    $stmt->close();
    $conn->close();

    /*
       Registration successful.
       Send the user to the login page.
    */

    header("Location: ../public/login.html?registered=1");

    exit();

}


http_response_code(500);

echo "Registration failed: " . $stmt->error;


$stmt->close();

$conn->close();

?>