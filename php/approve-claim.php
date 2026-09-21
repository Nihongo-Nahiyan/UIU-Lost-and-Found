<?php

include "db.php";


if (
    $_SERVER["REQUEST_METHOD"]
    == "POST"
) {

    $claim_id =
        (int)$_POST["claim_id"];


    $conn->begin_transaction();


    try {


        /* GET FOUND ITEM */

        $stmt =
            $conn->prepare("
                SELECT found_id

                FROM claims

                WHERE claim_id = ?

                AND status = 'Pending'
            ");


        $stmt->bind_param(
            "i",
            $claim_id
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        $claim =
            $result->fetch_assoc();


        if ($claim) {


            $found_id =
                $claim["found_id"];



            /* APPROVE CLAIM */

            $stmt =
                $conn->prepare("
                    UPDATE claims

                    SET status = 'Approved'

                    WHERE claim_id = ?
                ");


            $stmt->bind_param(
                "i",
                $claim_id
            );


            $stmt->execute();



            /* ITEM IS NOW CLAIMED */

            $stmt =
                $conn->prepare("
                    UPDATE found_items

                    SET claimed_status =
                        'Claimed'

                    WHERE found_id = ?
                ");


            $stmt->bind_param(
                "i",
                $found_id
            );


            $stmt->execute();



            /* REJECT OTHER CLAIMS */

            $stmt =
                $conn->prepare("
                    UPDATE claims

                    SET status = 'Rejected'

                    WHERE found_id = ?

                    AND claim_id != ?

                    AND status = 'Pending'
                ");


            $stmt->bind_param(
                "ii",
                $found_id,
                $claim_id
            );


            $stmt->execute();

        }


        $conn->commit();


    } catch (Exception $e) {


        $conn->rollback();


    }

}


header(
    "Location: ../admin/admin-manage-claims.php"
);

exit;

?>