<?php

include "db.php";


if (
    $_SERVER["REQUEST_METHOD"]
    == "POST"
) {

    $category_id =
        (int)$_POST["category_id"];


    $category_name =
        trim(
            $_POST["category_name"]
        );


    $icon =
        trim(
            $_POST["icon"]
        );


    $description =
        trim(
            $_POST["description"]
        );


    if ($category_name != "") {


        $stmt =
            $conn->prepare("
                UPDATE categories

                SET
                    category_name = ?,
                    icon = ?,
                    description = ?

                WHERE category_id = ?
            ");


        $stmt->bind_param(
            "sssi",
            $category_name,
            $icon,
            $description,
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