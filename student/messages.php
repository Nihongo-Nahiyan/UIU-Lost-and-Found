<?php

session_start();

require_once "../php/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../public/login.html");
    exit();
}

$uid = (int) $_SESSION["user_id"];
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
// GET APPROVED CLAIM CONVERSATIONS
// --------------------------------------------------

// Show conversations for BOTH:
// 1. The student who made the claim
// 2. The student who found the item

$conversationStmt = $conn->prepare(
    "SELECT
        c.claim_id,
        c.found_id,
        c.claimant_id,

        f.user_id AS finder_id,
        f.title,

        claimer.name AS claimer_name,
        finder.name AS finder_name,

        CASE
            WHEN c.claimant_id = ? THEN f.user_id
            ELSE c.claimant_id
        END AS other_id,

        CASE
            WHEN c.claimant_id = ? THEN finder.name
            ELSE claimer.name
        END AS other_name,

        (
            SELECT MAX(m.sent_time)
            FROM messages m
            WHERE m.found_id = c.found_id
            AND (
                (m.sender_id = c.claimant_id
                 AND m.receiver_id = f.user_id)

                OR

                (m.sender_id = f.user_id
                 AND m.receiver_id = c.claimant_id)
            )
        ) AS last_message_time

    FROM claims c

    INNER JOIN found_items f
        ON c.found_id = f.found_id

    INNER JOIN users claimer
        ON c.claimant_id = claimer.user_id

    INNER JOIN users finder
        ON f.user_id = finder.user_id

    WHERE
        (c.claimant_id = ? OR f.user_id = ?)
        AND LOWER(c.status) = 'approved'

    ORDER BY
        COALESCE(
            (
                SELECT MAX(m2.sent_time)
                FROM messages m2
                WHERE m2.found_id = c.found_id
            ),
            c.claim_id
        ) DESC"
);

$conversationStmt->bind_param(
    "iiii",
    $uid,
    $uid,
    $uid,
    $uid
);

$conversationStmt->execute();

$conversationResult =
    $conversationStmt->get_result();

$conversations = [];

while (
    $row =
    $conversationResult->fetch_assoc()
) {
    $conversations[] = $row;
}

$conversationStmt->close();


// --------------------------------------------------
// SELECT CONVERSATION
// --------------------------------------------------

// No demo/default conversation.
// If no conversation is selected, the first approved
// claim will automatically be opened.

$with = isset($_GET["with"])
    ? intval($_GET["with"])
    : 0;

$found = isset($_GET["found"])
    ? intval($_GET["found"])
    : 0;


// --------------------------------------------------
// VALIDATE SELECTED CONVERSATION
// --------------------------------------------------

$selectedConversation = null;

foreach ($conversations as $chat) {

    if (
        intval($chat["other_id"]) == $with &&
        intval($chat["found_id"]) == $found
    ) {
        $selectedConversation = $chat;
        break;
    }
}


// If no valid conversation was selected,
// automatically open the first approved claim.

if ($selectedConversation === null && !empty($conversations)) {

    $selectedConversation = $conversations[0];

    $with = intval($selectedConversation["other_id"]);
    $found = intval($selectedConversation["found_id"]);
}
if (!isset($_GET["with"]) && !empty($conversations)) {
    header("Location: messages.php?with=$with&found=$found");
    exit();
}


// --------------------------------------------------
// GET OTHER USER
// --------------------------------------------------

$other = null;

if ($selectedConversation) {

    $stmt = $conn->prepare(
        "SELECT user_id, name
         FROM users
         WHERE user_id = ?"
    );

    $stmt->bind_param("i", $with);

    $stmt->execute();

    $other = $stmt->get_result()->fetch_assoc();

    $stmt->close();
}


// --------------------------------------------------
// GET FOUND ITEM
// --------------------------------------------------

$item = null;

if ($selectedConversation) {

    $stmt = $conn->prepare(
        "SELECT found_id, title
         FROM found_items
         WHERE found_id = ?"
    );

    $stmt->bind_param("i", $found);

    $stmt->execute();

    $item = $stmt->get_result()->fetch_assoc();

    $stmt->close();
}


// --------------------------------------------------
// GET MESSAGES
// --------------------------------------------------

$messages = [];

if ($other && $item) {

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

    $messageResult = $stmt->get_result();

    while ($row = $messageResult->fetch_assoc()) {
        $messages[] = $row;
    }

    $stmt->close();
}


// --------------------------------------------------
// MARK RECEIVED MESSAGES AS READ
// --------------------------------------------------

if ($other && $item) {

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
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Messages - UIU Lost & Found</title>

    <link rel="stylesheet" href="../css/student.css">

</head>


<body>


<nav>

    <div class="nav-inner">


        <!-- LOGO -->

        <div class="nav-logo">

            <div class="logo-box">
                L&F
            </div>

            <div class="brand">
                UIU Lost <em>&</em> Found
            </div>

        </div>


        <!-- NAVIGATION -->

        <div class="nav-links">

            <a href="../student_part1/student-dashboard.php">
                Dashboard
            </a>

            <a href="../student_part1/student-browse.php">
                Browse
            </a>

            <a href="../student_part1/report-lost.php">
                Report Lost
            </a>

            <a href="../student_part1/report-found.php">
                Report Found
            </a>

            <a href="MyClaims.php">
                My Claims
            </a>

            <a href="messages.php">
    Messages
</a>

        </div>


        <!-- USER -->

        <div class="nav-right">

            <span class="nav-user">
                👤 Student
            </span>

            <a href="../public/index.php"
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
            <!-- LEFT SIDE - DYNAMIC CONVERSATIONS -->
            <!-- ================================= -->

            <div class="msg-list">


                <?php if (!empty($conversations)): ?>


                    <?php foreach ($conversations as $chat): ?>


                        <a href="messages.php?with=<?php echo intval($chat["other_id"]); ?>&found=<?php echo intval($chat["found_id"]); ?>">


                            <div class="msg-item
                                <?php

                               if (
    intval($chat["other_id"]) == $with &&
    intval($chat["found_id"]) == $found
)
                                 {
                                    echo "active";
                                }

                                ?>">


                                <div class="msg-item-name">


                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $chat["other_name"]
                                        );

                                        ?>

                                    </span>


                                    <span class="msg-date">

                                        <?php

                                        if (!empty($chat["last_message_time"])) {

                                            echo date(
                                                "M d",
                                                strtotime(
                                                    $chat["last_message_time"]
                                                )
                                            );

                                        } else {

                                            echo "Approved";

                                        }

                                        ?>

                                    </span>


                                </div>


                                <div class="msg-item-topic">

                                    <?php

                                    echo htmlspecialchars(
                                        $chat["title"]
                                    );

                                    ?>

                                </div>


                                <div class="msg-item-prev">

                                    <?php

                                    if (
                                        !empty($chat["last_message_time"])
                                    ) {

                                        echo "Continue conversation";

                                    } else {

                                        echo "Chat about your approved claim";

                                    }

                                    ?>

                                </div>


                            </div>


                        </a>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="msg-item">

                        <div class="msg-item-prev">

                            No approved claims yet.

                        </div>

                    </div>


                <?php endif; ?>


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


                        <?php if (!empty($messages)): ?>


                            <?php foreach ($messages as $row): ?>


                                <?php

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
                                            data-message-id="<?php echo intval($row["message_id"]); ?>"
                                        >

                                            Delete

                                        </button>


                                    <?php endif; ?>


                                </div>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <div class="bubble them">

                                No messages yet. You can start the conversation.

                            </div>


                        <?php endif; ?>


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


                <?php else: ?>


                    <div class="msg-body">

                        <div class="bubble them">

                            Your approved claims will appear here.
                            Once an admin approves a claim,
                            you can chat with the finder.

                        </div>

                    </div>


                <?php endif; ?>


            </div>


        </div>


    </div>


</main>


<?php if ($other && $item): ?>

<script>

const receiverId =
    <?php echo intval($with); ?>;

const foundId =
    <?php echo intval($found); ?>;

</script>

<script src="messages.js"></script>

<?php endif; ?>


</body>

</html>
