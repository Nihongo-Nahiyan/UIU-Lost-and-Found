<?php

include "db.php";


header(
    "Content-Type: text/csv"
);

header(
    "Content-Disposition: attachment; filename=uiu_lost_found_report.csv"
);


$output =
    fopen(
        "php://output",
        "w"
    );


/* SUMMARY */

fputcsv(
    $output,
    array(
        "UIU Lost & Found Report"
    )
);


fputcsv(
    $output,
    array()
);


$result =
    $conn->query("
        SELECT COUNT(*) AS total
        FROM lost_items
    ");

$totalLost =
    $result->fetch_assoc()["total"];


$result =
    $conn->query("
        SELECT COUNT(*) AS total

        FROM found_items

        WHERE approval_status =
              'Approved'
    ");

$totalFound =
    $result->fetch_assoc()["total"];


$result =
    $conn->query("
        SELECT COUNT(*) AS total

        FROM claims

        WHERE status =
              'Approved'
    ");

$approvedClaims =
    $result->fetch_assoc()["total"];


fputcsv(
    $output,
    array(
        "Total Lost Reports",
        $totalLost
    )
);


fputcsv(
    $output,
    array(
        "Approved Found Items",
        $totalFound
    )
);


fputcsv(
    $output,
    array(
        "Approved Claims",
        $approvedClaims
    )
);


fputcsv(
    $output,
    array()
);


/* CATEGORY REPORT */

fputcsv(
    $output,
    array(
        "Category",
        "Lost",
        "Found",
        "Total"
    )
);


$sql = "
    SELECT
        c.category_name,

        (
            SELECT COUNT(*)

            FROM lost_items l

            WHERE l.category_id =
                  c.category_id
        )

        AS lost_count,


        (
            SELECT COUNT(*)

            FROM found_items f

            WHERE f.category_id =
                  c.category_id

            AND f.approval_status =
                'Approved'
        )

        AS found_count

    FROM categories c

    ORDER BY c.category_name
";


$result =
    $conn->query($sql);


while (
    $row =
    $result->fetch_assoc()
) {

    $total =
        $row["lost_count"]
        +
        $row["found_count"];


    fputcsv(
        $output,
        array(
            $row["category_name"],
            $row["lost_count"],
            $row["found_count"],
            $total
        )
    );

}


fclose($output);

exit;

?>