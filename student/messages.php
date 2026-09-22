<?php

include "php/db.php";


// Temporary logged-in student
// Student One = user_id 1
$uid = 1;
// --------------------------------------------------
// DELETE MESSAGE
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_message"])) {

    $message_id = intval($_POST["message_id"]);

    $stmt = $conn->prepare(
        "DELETE FROM messages
         WHERE message_id = ?
         AND sender_id = ?"
    );

    $stmt->bind_param(
        "ii",
        $message_id,
        $uid
    );

    $stmt->execute();

    if ($stmt->affected_rows > 0) {

        echo json_encode([
            "success" => true
        ]);

    } else {

        echo json_encode([
            "success" => false
        ]);
    }

    $stmt->close();

    exit;
}
// --------------------------------------------------
// SEND MESSAGE
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_message"])) {

    $receiver_id = intval($_POST["receiver_id"]);
    $found_id = intval($_POST["found_id"]);
    $message = trim($_POST["message"]);

    if ($message != "") {

        $stmt = $conn->prepare(
            "INSERT INTO messages
            (sender_id, receiver_id, found_id, message)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "iiis",
            $uid,
            $receiver_id,
            $found_id,
            $message
        );

        if ($stmt->execute()) {

            echo json_encode([
                "success" => true
            ]);

        } else {

            echo json_encode([
                "success" => false
            ]);

        }

        $stmt->close();

        exit;
    }

    echo json_encode([
        "success" => false
    ]);

    exit;
}


// --------------------------------------------------
// SELECT CONVERSATION
// --------------------------------------------------

$with = isset($_GET["with"])
    ? intval($_GET["with"])
    : 2;

$found = isset($_GET["found"])
    ? intval($_GET["found"])
    : 1;
    // --------------------------------------------------
// GET FAHIM AND TANVIR USER IDS
// --------------------------------------------------

$fahimRow = $conn->query(
    "SELECT user_id
     FROM users
     WHERE student_id = '0112420205'
     LIMIT 1"
)->fetch_assoc();

$fahimId = $fahimRow
    ? intval($fahimRow["user_id"])
    : 0;


$tanvirRow = $conn->query(
    "SELECT user_id
     FROM users
     WHERE student_id = '0112420204'
     LIMIT 1"
)->fetch_assoc();

$tanvirId = $tanvirRow
    ? intval($tanvirRow["user_id"])
    : 0;
// --------------------------------------------------
// GET FAHIM AND TANVIR USER IDS
// --------------------------------------------------

$fahimRow = $conn->query(
    "SELECT user_id
     FROM users
     WHERE student_id = '0112420205'
     LIMIT 1"
)->fetch_assoc();

$fahimId = $fahimRow
    ? intval($fahimRow["user_id"])
    : 0;


$tanvirRow = $conn->query(
    "SELECT user_id
     FROM users
     WHERE student_id = '0112420204'
     LIMIT 1"
)->fetch_assoc();

$tanvirId = $tanvirRow
    ? intval($tanvirRow["user_id"])
    : 0;

// --------------------------------------------------
// GET OTHER USER
// --------------------------------------------------

$stmt = $conn->prepare(
    "SELECT user_id, name
     FROM users
     WHERE user_id = ?"
);

$stmt->bind_param("i", $with);

$stmt->execute();

$other = $stmt->get_result()->fetch_assoc();

$stmt->close();


// --------------------------------------------------
// GET FOUND ITEM
// --------------------------------------------------

$stmt = $conn->prepare(
    "SELECT found_id, title
     FROM found_items
     WHERE found_id = ?"
);

$stmt->bind_param("i", $found);

$stmt->execute();

$item = $stmt->get_result()->fetch_assoc();

$stmt->close();


// --------------------------------------------------
// GET MESSAGES
// --------------------------------------------------

$stmt = $conn->prepare(
    "SELECT
        message_id,
        sender_id,
        receiver_id,
        message,
        is_read,
        sent_time

     FROM messages

     WHERE found_id = ?

     AND (
        (sender_id = ? AND receiver_id = ?)
        OR
        (sender_id = ? AND receiver_id = ?)
     )

    ORDER BY sent_time ASC, message_id ASC"
);

$stmt->bind_param(
    "iiiii",
    $found,
    $uid,
    $with,
    $with,
    $uid
);

$stmt->execute();

$messages = $stmt->get_result();


// --------------------------------------------------
// MARK RECEIVED MESSAGES AS READ
// --------------------------------------------------

$stmt_read = $conn->prepare(
    "UPDATE messages
     SET is_read = 1
     WHERE sender_id = ?
     AND receiver_id = ?
     AND found_id = ?"
);

$stmt_read->bind_param(
    "iii",
    $with,
    $uid,
    $found
);

$stmt_read->execute();

$stmt_read->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Messages - UIU Lost & Found</title>

    <link rel="stylesheet" href="student.css">

</head>


<body>


<nav>

    <div class="nav-inner">


        <!-- LOGO -->

       <div class="nav-logo">
    <div class="logo-box">L&F</div>
    <div class="brand">UIU Lost <em>&</em> Found</div>
</div>

        <!-- NAVIGATION -->

        <div class="nav-links">

            <a href="student-dashboard.html">
                Dashboard
            </a>

            <a href="student-browse.html">
                Browse
            </a>

            <a href="report-lost.html">
                Report Lost
            </a>

            <a href="report-found.html">
                Report Found
            </a>

            <a href="MyClaims.php">
                My Claims
            </a>

            <a href="messages.php"
               class="active">
                Messages
            </a>

        </div>


        <!-- USER -->

        <div class="nav-right">

            <span class="nav-user">
                👤 Student
            </span>

            <a href="index.html"
               class="btn btn-secondary btn-sm">

                Logout

            </a>

        </div>


    </div>

</nav>


<main>


    <div class="message-page">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <h2>
                Messages
            </h2>

            <p>
                Chat with finders about lost and found items
            </p>

        </div>


        <!-- MESSAGE BOX -->

        <div class="msg-layout">


            <!-- ================================= -->
            <!-- LEFT SIDE - EXISTING CONVERSATIONS -->
            <!-- ================================= -->

          <div class="msg-list">


    <!-- NADIA - BLACK LEATHER WALLET -->

    <a href="messages.php?with=2&found=1">

        <div class="msg-item
            <?php
            if ($with == 2 && $found == 1) {
                echo "active";
            }
            ?>">

            <div class="msg-item-name">

                <span>
                    Nadia Rahman
                </span>

                <span class="msg-date">
                    Aug 29
                </span>

            </div>

            <div class="msg-item-topic">
                Black Leather Wallet
            </div>

            <div class="msg-item-prev">
                Yes, I found it near the library.
            </div>

        </div>

    </a>


    <!-- FAHIM - HP LAPTOP CHARGER -->

    <a href="messages.php?with=<?php echo $fahimId; ?>&found=2">

        <div class="msg-item
            <?php
            if ($with == $fahimId && $found == 2) {
                echo "active";
            }
            ?>">

            <div class="msg-item-name">

                <span>
                    Fahim Hossain
                </span>

                <span class="msg-date">
                    Aug 28
                </span>

            </div>

            <div class="msg-item-topic">
                HP Laptop Charger
            </div>

            <div class="msg-item-prev">
                When can I collect the charger?
            </div>

        </div>

    </a>


    <!-- TANVIR - UIU STUDENT ID CARD -->

    <a href="messages.php?with=<?php echo $tanvirId; ?>&found=3">

        <div class="msg-item
            <?php
            if ($with == $tanvirId && $found == 3) {
                echo "active";
            }
            ?>">

            <div class="msg-item-name">

                <span>
                    Tanvir Ahmed
                </span>

                <span class="msg-date">
                    Aug 27
                </span>

            </div>

            <div class="msg-item-topic">
                UIU Student ID Card
            </div>

            <div class="msg-item-prev">
                Thank you for finding my ID.
            </div>

        </div>

    </a>


</div>

            <!-- ================================= -->
            <!-- RIGHT SIDE CHAT -->
            <!-- ================================= -->

            <div class="msg-chat">


                <?php if ($other && $item): ?>


                    <!-- CHAT HEADER -->

                    <div class="msg-chat-hd">


                        <div class="cn">

                            <?php

                            echo htmlspecialchars(
                                $other["name"]
                            );

                            ?>

                        </div>


                        <div class="ct">

                            <?php

                            echo htmlspecialchars(
                                $item["title"]
                            );

                            ?>

                        </div>


                    </div>


                    <!-- CHAT BODY -->

                    <div class="msg-body"
                         id="messageBody">


                        <?php

                        while ($row =
                               $messages->fetch_assoc()):


                            $myMessage =
                                ($row["sender_id"] == $uid);

                        ?>


                            <div class="bubble
                                <?php

                                echo $myMessage
                                    ? "me"
                                    : "them";

                                ?>">


                                <?php

                                echo nl2br(
                                    htmlspecialchars(
                                        $row["message"]
                                    )
                                );

                                ?>


                                <div class="bubble-time">

                                    <?php

                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $row["sent_time"]
                                        )
                                    );

                                    ?>

                                </div>
                                <?php if ($myMessage): ?>

    <button
        type="button"
        class="delete-message-btn"
        data-message-id="<?php echo $row["message_id"]; ?>"
    >
        Delete
    </button>

<?php endif; ?>


                            </div>


                        <?php endwhile; ?>


                    </div>


                    <!-- MESSAGE INPUT -->

                    <div class="msg-footer">


                        <input
                            type="text"
                            id="messageInput"
                            class="form-control"
                            placeholder="Type a message..."
                            autocomplete="off"
                        >


                        <button
                            type="button"
                            id="sendButton"
                            class="btn btn-primary">

                            Send

                        </button>


                    </div>


                <?php endif; ?>


            </div>


        </div>


    </div>


</main>


<script>

const receiverId =
    <?php echo $with; ?>;

const foundId =
    <?php echo $found; ?>;

</script>


<script src="js/messages.js"></script>


</body>

</html>