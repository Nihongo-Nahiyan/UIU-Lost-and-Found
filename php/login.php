<?php

include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    // Find the user by email
    $stmt = $conn->prepare(
        "SELECT user_id, name, password, role
         FROM users
         WHERE email = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // Check password
        if ($password == $user["password"]) {

            // Send user to the correct page
            if ($user["role"] == "admin") {

                header("Location: ../admin/admin-dashboard.html");
                exit();

            } else {

                header("Location: ../student_part1/student-dashboard.html");
                exit();

            }

        } else {

            echo "Incorrect password.";

        }

    } else {

        echo "No account found with this email.";

    }

    $stmt->close();
}

$conn->close();

?>