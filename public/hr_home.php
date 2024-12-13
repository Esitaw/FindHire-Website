<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'HR') {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM jobs ORDER BY created_at DESC");
$stmt->execute();
$jobs = $stmt->fetchAll();

$messageStmt = $pdo->prepare("SELECT * FROM messages WHERE hr_id = :hr_id AND status = 'unread' ORDER BY sent_at DESC");
$messageStmt->execute(['hr_id' => $_SESSION['user_id']]);
$unreadMessages = $messageStmt->fetchAll();

if (isset($_GET['mark_all_read'])) {
    $updateStmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE hr_id = :hr_id AND status = 'unread'");
    $updateStmt->execute(['hr_id' => $_SESSION['user_id']]);
    header("Location: hr_home.php"); 
    exit;
}
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
            <li><a href="../scripts/post_job.php">Post a New Job</a></li>
            <li>
                <a href="view_messages.php" class="btn btn-info">
                    Messages
                    <?php if (count($unreadMessages) > 0): ?>
                        <span class="badge"><?php echo count($unreadMessages); ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="logout.php" class="btn btn-danger">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="hr-container">
    <div class="welcome-section">
        <h2>Welcome, HR Admin!</h2>
        <p>Manage your job postings and track applications below.</p>
    </div>

    <div class="job-list">
        <h3>Your Job Postings</h3>
        <?php if (count($jobs) > 0): ?>
            <?php foreach ($jobs as $job): ?>
                <div class="job-card">
                    <div class="job-header">
                        <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                        <small>Posted on: <?php echo htmlspecialchars(date("F j, Y", strtotime($job['created_at']))); ?></small>
                    </div>
                    <p><?php echo htmlspecialchars($job['description']); ?></p>

                    <?php if ($job['hired_applicant_id']): ?>
                        <?php
                            $applicantStmt = $pdo->prepare("SELECT email FROM users WHERE id = :applicant_id");
                            $applicantStmt->execute(['applicant_id' => $job['hired_applicant_id']]);
                            $applicant = $applicantStmt->fetch();
                        ?>
                        <p><strong>Hired Applicant:</strong> <?php echo htmlspecialchars($applicant['email']); ?></p>
                    <?php else: ?>
                        <p><strong>No applicant hired yet.</strong></p>
                    <?php endif; ?>

                    <div class="job-actions">
                        <a href="view_applicants.php?job_id=<?php echo $job['id']; ?>" class="btn btn-success">View Applicants</a>
                        <a href="edit_job.php?job_id=<?php echo $job['id']; ?>" class="btn btn-warning">Edit</a>
                        <a href="delete_job.php?job_id=<?php echo $job['id']; ?>" class="btn btn-danger">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-jobs">
                <h4>No job postings found.</h4>
                <p>Click below to post your first job and start receiving applications!</p>
                <a href="../scripts/post_job.php" class="btn btn-primary">Post a Job</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
