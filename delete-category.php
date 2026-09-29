<?php

include "db.php";


if (
    $_SERVER["REQUEST_METHOD"]
    == "POST"
) {

    $category_id =
        (int)$_POST["category_id"];



    /* LOST ITEMS USING CATEGORY */

    $stmt =
        $conn->prepare("
            SELECT COUNT(*) AS total

            FROM lost_items

            WHERE category_id = ?
        ");


    $stmt->bind_param(
        "i",
        $category_id
    );


    $stmt->execute();


    $lostCount =
        $stmt
        ->get_result()
        ->fetch_assoc()["total"];



    /* FOUND ITEMS USING CATEGORY */

    $stmt =
        $conn->prepare("
            SELECT COUNT(*) AS total

            FROM found_items

            WHERE category_id = ?
        ");


    $stmt->bind_param(
        "i",
        $category_id
    );


    $stmt->execute();


    $foundCount =
        $stmt
        ->get_result()
        ->fetch_assoc()["total"];



    /* DELETE ONLY IF UNUSED */

    if (
        $lostCount == 0 &&
        $foundCount == 0
    ) {


        $stmt =
            $conn->prepare("
                DELETE FROM categories

                WHERE category_id = ?
            ");


        $stmt->bind_param(
            "i",
            $category_id
        );


        $stmt->execute();

    }

}


header(
    "Location: ../admin/admin-manage-categories.php"
);

exit;

?>