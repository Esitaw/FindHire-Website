<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'HR') {
    header("Location: login.php");
    exit;
}

$messageStmt = $pdo->prepare("SELECT * FROM messages WHERE hr_id = :hr_id ORDER BY sent_at DESC");
$messageStmt->execute(['hr_id' => $_SESSION['user_id']]);
$messages = $messageStmt->fetchAll();

if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $messageId = $_GET['toggle_status'];
    $messageStmt = $pdo->prepare("SELECT status FROM messages WHERE id = :id");
    $messageStmt->execute(['id' => $messageId]);
    $message = $messageStmt->fetch();

    if ($message) {
        $newStatus = ($message['status'] == 'read') ? 'unread' : 'read';  
        $updateStmt = $pdo->prepare("UPDATE messages SET status = :status WHERE id = :id");
        $updateStmt->execute(['status' => $newStatus, 'id' => $messageId]);
    }

    header("Location: view_messages.php");
    exit;
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteStmt = $pdo->prepare("DELETE FROM messages WHERE id = :id AND hr_id = :hr_id");
    $deleteStmt->execute(['id' => $_GET['delete'], 'hr_id' => $_SESSION['user_id']]);
    header("Location: view_messages.php"); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'])) {
    $applicant_id = $_POST['applicant_id'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    if (empty($subject) || empty($message)) {
        echo "<p style='color: red; text-align: center;'>Subject and message cannot be empty.</p>";
    } else {
        $replyStmt = $pdo->prepare("INSERT INTO messages (applicant_id, hr_id, subject, message, status) 
                                   VALUES (:applicant_id, :hr_id, :subject, :message, 'unread')");
        $replyStmt->execute([
            'applicant_id' => $applicant_id,
            'hr_id' => $_SESSION['user_id'],
            'subject' => $subject,
            'message' => $message
        ]);

        header("Location: view_messages.php?response_sent=true");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Messages</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
        }

        header {
            background-color: #007bff;
            color: #fff;
            padding: 10px 30px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header .logo h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        header nav ul {
            list-style-type: none;
            padding: 0;
        }

        header nav ul li {
            display: inline;
            margin-left: 20px;
        }

        header nav ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        header nav ul li a:hover {
            text-decoration: underline;
        }

        main {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #007bff;
        }

        .message-card {
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .message-card .header {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .message-card .content {
            margin-bottom: 10px;
        }

        .message-card .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .message-card .footer a {
            color: #007bff;
            text-decoration: none;
        }

        .message-card .footer a:hover {
            text-decoration: underline;
        }

        .unread {
            background-color: #fff3cd;
        }

        .read {
            background-color: #d4edda;
        }

        footer {
            color: black;
            text-align: center;
            padding: 15px;
            font-size: 1em;
        }

        .reply-form {
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f4f7fc;
        }

        .reply-form label {
            font-weight: bold;
        }

        .reply-form textarea, .reply-form input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .reply-form button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        .reply-form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <h1>FindHire</h1>
    </div>
    <nav>
        <ul>
            <li><a href="hr_home.php">HR Dashboard</a></li>
            <li><a href="../scripts/post_job.php">Post a New Job</a></li>
            <li><a href="view_messages.php">View Messages</a></li>
            <li><a href="logout.php" class="btn btn-danger">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <h2>Messages from Applicants</h2>

    <?php if (isset($_GET['response_sent'])): ?>
        <p style="color: green; text-align: center;">Your reply has been sent successfully!</p>
    <?php endif; ?>

    <?php if (count($messages) > 0): ?>
        <?php foreach ($messages as $message): ?>
            <div class="message-card <?php echo $message['status'] == 'unread' ? 'unread' : 'read'; ?>">
                <div class="header">
                    <strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?>
                </div>
                <div class="content">
                    <strong>Message:</strong> <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                </div>
                <div class="footer">
                    <span><strong>Sent by:</strong> Applicant #<?php echo htmlspecialchars($message['applicant_id']); ?></span>
                    <span><strong>Sent at:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($message['sent_at']))); ?></span>
                    <a href="view_messages.php?toggle_status=<?php echo $message['id']; ?>">
                        <?php echo ($message['status'] == 'unread') ? 'Mark as Read' : 'Mark as Unread'; ?>
                    </a>
                    <a href="view_messages.php?delete=<?php echo $message['id']; ?>">Delete</a>
                    <a href="view_messages.php?message_id=<?php echo $message['id']; ?>">Reply</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No messages found.</p>
    <?php endif; ?>


    <?php if (isset($_GET['message_id'])): ?>
        <?php
        $message_id = $_GET['message_id'];
        $messageDetailsStmt = $pdo->prepare("SELECT * FROM messages WHERE id = :id LIMIT 1");
        $messageDetailsStmt->execute(['id' => $message_id]);
        $messageDetails = $messageDetailsStmt->fetch();
        ?>

        <div class="reply-form">
            <h3>Reply to Applicant</h3>
            <form action="view_messages.php" method="POST">
                <label for="subject">Subject:</label>
                <input type="text" name="subject" id="subject" value="Re: <?php echo htmlspecialchars($messageDetails['subject']); ?>" required>

                <label for="message">Message:</label>
                <textarea name="message" id="message" rows="5" required></textarea>

                <input type="hidden" name="applicant_id" value="<?php echo htmlspecialchars($messageDetails['applicant_id']); ?>">

                <button type="submit" name="reply_message">Send Reply</button>
            </form>
        </div>
    <?php endif; ?>

</main>

<footer>
    &copy; 2024 FindHire. All rights reserved.
</footer>

</body>
</html>
