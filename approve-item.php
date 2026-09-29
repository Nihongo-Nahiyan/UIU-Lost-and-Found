<?php

include "db.php";


if (
    $_SERVER["REQUEST_METHOD"]
    == "POST"
) {

    $found_id =
        (int)$_POST["found_id"];


    $stmt =
        $conn->prepare("
            UPDATE found_items

            SET approval_status =
                'Approved'

            WHERE found_id = ?
        ");


    $stmt->bind_param(
        "i",
        $found_id
    );


    $stmt->execute();

}


header(
    "Location: ../admin/admin-approve-items.php"
);

exit;

?>