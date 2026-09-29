<?php

include "db.php";


if (
    $_SERVER["REQUEST_METHOD"]
    == "POST"
) {

    $claim_id =
        (int)$_POST["claim_id"];


    $stmt =
        $conn->prepare("
            UPDATE claims

            SET status = 'Rejected'

            WHERE claim_id = ?

            AND status = 'Pending'
        ");


    $stmt->bind_param(
        "i",
        $claim_id
    );


    $stmt->execute();

}


header(
    "Location: ../admin/admin-manage-claims.php"
);

exit;

?>