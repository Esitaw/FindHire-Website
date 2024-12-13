<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'HR') {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM messages WHERE hr_id = :hr_id ORDER BY sent_at DESC");
$stmt->execute(['hr_id' => $_SESSION['user_id']]);
$messages = $stmt->fetchAll();
?>

<link rel="stylesheet" type="text/css" href="../assets/styles.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

<header>
    <div class="logo">
        <h1>FindHire</h1>
    </div>
    <nav>
        <ul>
            <li><a href="hr_home.php">HR Dashboard</a></li>
            <li><a href="logout.php" class="btn btn-danger">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="hr-container">
    <h2>Messages from Applicants</h2>
    
    <?php if (count($messages) > 0): ?>
        <?php foreach ($messages as $message): ?>
            <div class="message-card">
                <h4>Subject: <?php echo htmlspecialchars($message['subject']); ?></h4>
                <small>From Applicant (ID: <?php echo $message['applicant_id']; ?>) - Sent on: <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($message['sent_at']))); ?></small>
                
                <p><strong>Message:</strong> <?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                
                <div class="message-actions">
                    <a href="mark_as_read.php?message_id=<?php echo $message['id']; ?>" class="btn btn-info">Mark as Read</a>
                    <a href="reply_message.php?message_id=<?php echo $message['id']; ?>" class="btn btn-primary">Reply</a>
                </div>

                <?php if ($message['reply_to']): ?>
                    <?php
                    $reply_stmt = $pdo->prepare("SELECT * FROM messages WHERE id = :reply_to");
                    $reply_stmt->execute(['reply_to' => $message['reply_to']]);
                    $reply = $reply_stmt->fetch();
                    ?>
                    <div class="reply-thread">
                        <p><strong>Reply:</strong> <?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No messages from applicants yet.</p>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
