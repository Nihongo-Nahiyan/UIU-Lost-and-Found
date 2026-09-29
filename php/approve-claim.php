<?php

include "db.php";


/* CHECK CLAIM ID */

if (!isset($_POST["claim_id"])) {

    header("Location: ../admin/admin-manage-claims.php");

    exit;
}


$claim_id = intval($_POST["claim_id"]);


/* GET CLAIM INFORMATION */

$stmt = $conn->prepare("
    SELECT
        claim_id,
        found_id,
        claimant_id,
        status
    FROM claims
    WHERE claim_id = ?
");

$stmt->bind_param("i", $claim_id);

$stmt->execute();

$result = $stmt->get_result();

$claim = $result->fetch_assoc();


/* CLAIM NOT FOUND */

if (!$claim) {

    header("Location: ../admin/admin-manage-claims.php");

    exit;
}


/* ONLY APPROVE PENDING CLAIMS */

if ($claim["status"] !== "Pending") {

    header("Location: ../admin/admin-manage-claims.php");

    exit;
}


/* APPROVE CLAIM */

$update = $conn->prepare("
    UPDATE claims
    SET status = 'Approved'
    WHERE claim_id = ?
");

$update->bind_param("i", $claim_id);

$update->execute();


/* MARK FOUND ITEM AS CLAIMED */

$found_id = intval($claim["found_id"]);

$found = $conn->prepare("
    UPDATE found_items
    SET claimed_status = 'Claimed'
    WHERE found_id = ?
");

$found->bind_param("i", $found_id);

$found->execute();


/* RETURN TO ADMIN CLAIM PAGE */

header("Location: ../admin/admin-manage-claims.php");

exit;

?>
