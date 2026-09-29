<?php

include "db.php";


if (
    $_SERVER["REQUEST_METHOD"]
    == "POST"
) {

    $user_id =
        (int)$_POST["user_id"];



    /* GET USER ROLE */

    $stmt =
        $conn->prepare("
            SELECT role

            FROM users

            WHERE user_id = ?
        ");


    $stmt->bind_param(
        "i",
        $user_id
    );


    $stmt->execute();


    $user =
        $stmt
        ->get_result()
        ->fetch_assoc();



    /* NEVER DELETE ADMIN */

    if (
        $user &&
        $user["role"] == "student"
    ) {


        /* CHECK LOST REPORTS */

        $stmt =
            $conn->prepare("
                SELECT COUNT(*) AS total

                FROM lost_items

                WHERE user_id = ?
            ");


        $stmt->bind_param(
            "i",
            $user_id
        );


        $stmt->execute();


        $lost =
            $stmt
            ->get_result()
            ->fetch_assoc()["total"];



        /* CHECK FOUND REPORTS */

        $stmt =
            $conn->prepare("
                SELECT COUNT(*) AS total

                FROM found_items

                WHERE user_id = ?
            ");


        $stmt->bind_param(
            "i",
            $user_id
        );


        $stmt->execute();


        $found =
            $stmt
            ->get_result()
            ->fetch_assoc()["total"];



        /* CHECK CLAIMS */

        $stmt =
            $conn->prepare("
                SELECT COUNT(*) AS total

                FROM claims

                WHERE claimant_id = ?
            ");


        $stmt->bind_param(
            "i",
            $user_id
        );


        $stmt->execute();


        $claims =
            $stmt
            ->get_result()
            ->fetch_assoc()["total"];



        /* CHECK MESSAGES */

        $stmt =
            $conn->prepare("
                SELECT COUNT(*) AS total

                FROM messages

                WHERE sender_id = ?

                OR receiver_id = ?
            ");


        $stmt->bind_param(
            "ii",
            $user_id,
            $user_id
        );


        $stmt->execute();


        $messages =
            $stmt
            ->get_result()
            ->fetch_assoc()["total"];



        if (
            $lost == 0 &&
            $found == 0 &&
            $claims == 0 &&
            $messages == 0
        ) {


            $stmt =
                $conn->prepare("
                    DELETE FROM users

                    WHERE user_id = ?
                ");


            $stmt->bind_param(
                "i",
                $user_id
            );


            $stmt->execute();

        }

    }

}


header(
    "Location: ../admin/admin-manage-users.php"
);

exit;

?>