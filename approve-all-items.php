<?php

include "db.php";


if (
    $_SERVER["REQUEST_METHOD"]
    == "POST"
) {

    $conn->query("
        UPDATE found_items

        SET approval_status = 'Approved'

        WHERE approval_status = 'Pending'
    ");

}


header(
    "Location: ../admin/admin-approve-items.php"
);

exit;

?>