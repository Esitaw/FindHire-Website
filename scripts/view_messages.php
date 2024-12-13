<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Applicant') {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT m1.*, m2.message AS reply_message, m2.sent_at AS reply_sent_at, u.email AS hr_email 
    FROM messages m1 
    LEFT JOIN messages m2 ON m1.reply_to = m2.id 
    JOIN users u ON m1.hr_id = u.id 
    WHERE m1.applicant_id = :applicant_id 
    ORDER BY m1.sent_at DESC
");
$stmt->execute(['applicant_id' => $_SESSION['user_id']]);
$messages = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'])) {
    $applicant_id = $_POST['applicant_id'];
    $message = $_POST['message'];
    $subject = $_POST['subject'];
    
    $replyStmt = $pdo->prepare("INSERT INTO messages (applicant_id, hr_id, subject, message, status, reply_to) 
                                VALUES (:applicant_id, :hr_id, :subject, :message, 'unread', :reply_to)");
    $replyStmt->execute([
        'applicant_id' => $applicant_id,
        'hr_id' => $_SESSION['user_id'],
        'subject' => $subject,
        'message' => $message,
        'reply_to' => $_POST['message_id']
    ]);

    header("Location: view_messages.php?response_sent=true");
    exit;
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
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between; /* Align items to left and right */
            align-items: center;
        }
        .logo h1 {
            margin: 0;
        }
        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin-left: 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        .messages-container {
            margin: 20px auto;
            padding: 20px;
            max-width: 800px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .message-card {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fafafa;
        }
        .message-card p {
            margin: 8px 0;
        }
        .message-card .sender {
            font-weight: bold;
        }
        .message-card .message-content {
            margin-top: 10px;
        }

        .hr-reply {
            background-color: #e0f7fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-top: 10px;
            border-radius: 8px;
        }
        .hr-reply p {
            margin: 8px 0;
            color: #007bff;
        }
        .hr-reply .reply-label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .reply-form {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .reply-form textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 10px;
        }
        .reply-form input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        footer {
            color: black;
            text-align: center;
            padding: 15px;
            font-size: 1em;
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
            <li><a href="../public/applicant_home.php">Home</a></li>
            <li><a href="../public/logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="messages-container">
    <h3>Your Messages</h3>

    <?php if (count($messages) > 0): ?>
        <?php 
        $applicantMessageDisplayed = false;
        ?>

        <?php foreach ($messages as $message): ?>
            <?php if (!empty($message['hr_email']) && !$applicantMessageDisplayed): ?>
                <div class="message-card">
                    <p class="sender"><strong>From HR:</strong> <?php echo htmlspecialchars($message['hr_email']); ?></p>
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?></p>
                    <div class="message-content">
                        <p><strong>Message:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                        <p><strong>Sent at:</strong> <?php echo $message['sent_at']; ?></p>
                    </div>
                </div>
                <?php $applicantMessageDisplayed = true; ?>
            <?php endif; ?>

            <?php if ($message['applicant_id'] == $_SESSION['user_id'] && empty($message['reply_to']) && !$applicantMessageDisplayed): ?>
                <div class="message-card" style="background-color: #e9f7e9; border-left: 4px solid #28a745;">
                    <p class="sender"><strong>To HR:</strong> You</p>
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?></p>
                    <div class="message-content">
                        <p><strong>Message:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                        <p><strong>Sent at:</strong> <?php echo $message['sent_at']; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($message['reply_message'])): ?>
                <div class="hr-reply">
                    <span class="reply-label">From HR:</span>
                    <p><?php echo nl2br(htmlspecialchars($message['reply_message'])); ?></p>
                    <p><strong>Sent at:</strong> <?php echo $message['reply_sent_at']; ?></p>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php else: ?>
        <p>No messages found.</p>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>

</body>
</html>
