<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Applicant') {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM jobs ORDER BY created_at DESC");
$stmt->execute();
$jobs = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT m.id, m.subject, m.sent_at, u.email AS hr_email
                       FROM messages m
                       JOIN users u ON m.hr_id = u.id
                       WHERE m.applicant_id = :applicant_id ORDER BY m.sent_at DESC");
$stmt->execute(['applicant_id' => $_SESSION['user_id']]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Homepage</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../assets/home2.css">
</head>
<body>

<header>
    <div class="logo">
        <h1>FindHire</h1>
    </div>
    <nav>
        <ul>
            <li><a href="applicant_home.php">Home</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li><a href="../scripts/send_message.php">Message HR</a></li> 
            <li><a href="../scripts/view_messages.php">View Messages</a></li> 
        </ul>
    </nav>
</header>

<div class="applicant-container">
    <div class="welcome-section">
        <h2>Welcome, Applicant!</h2>
        <p>Explore job opportunities below and apply to the ones that match your skills and interests.</p>
    </div>

    <div class="job-list">
        <?php if (count($jobs) > 0): ?>
            <?php foreach ($jobs as $job): ?>
                <div class="job-card">
                    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p><?php echo htmlspecialchars($job['description']); ?></p>
                    <a href="../scripts/apply_job.php?job_id=<?php echo $job['id']; ?>">Apply Now</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No job postings are available at the moment. Please check back later!</p>
        <?php endif; ?>
    </div>
</div>


<?php include('../includes/footer.php'); ?>

</body>
</html>
