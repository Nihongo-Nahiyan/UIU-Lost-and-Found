<?php

include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $student_id = $_POST["student_id"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Check if passwords match
    if ($password != $confirm_password) {
        die("Passwords do not match.");
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        die("This email is already registered.");
    }

    // Insert new user
    $stmt = $conn->prepare(
        "INSERT INTO users (name, student_id, email, phone, password, role)
         VALUES (?, ?, ?, ?, ?, 'student')"
    );

    $stmt->bind_param(
        "sssss",
        $name,
        $student_id,
        $email,
        $phone,
        $password
    );

    if ($stmt->execute()) {
        echo "Account created successfully!";
    } else {
        echo "Registration failed.";
    }

    $stmt->close();
    $check->close();
}

$conn->close();

?>