<?php

include "db.php";


if (
    $_SERVER["REQUEST_METHOD"]
    == "POST"
) {

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
                INSERT INTO categories
                (
                    category_name,
                    icon,
                    description
                )

                VALUES (?, ?, ?)
            ");


        $stmt->bind_param(
            "sss",
            $category_name,
            $icon,
            $description
        );


        $stmt->execute();

    }

}


header(
    "Location: ../admin/admin-manage-categories.php"
);

exit;

?>