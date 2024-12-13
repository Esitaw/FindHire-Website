<?php
session_start();
include('../includes/db.php');
include('../includes/header.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'HR') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['job_id'])) {
    $job_id = $_GET['job_id'];

    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = :job_id AND hr_id = :hr_id");
    $stmt->execute(['job_id' => $job_id, 'hr_id' => $_SESSION['user_id']]);
    $job = $stmt->fetch();

    if ($job) {
        $deleteApplicationsStmt = $pdo->prepare("DELETE FROM applications WHERE job_post_id = :job_id");
        $deleteApplicationsStmt->execute(['job_id' => $job_id]);

        $deleteStmt = $pdo->prepare("DELETE FROM jobs WHERE id = :job_id");
        $deleteStmt->execute(['job_id' => $job_id]);

        echo "<p class='success-message'>Job and related applications deleted successfully.</p>";
        header("Location: hr_home.php");
        exit;
    } else {
        echo "<p class='error-message'>Job not found or you do not have permission to delete it.</p>";
    }
} else {
    echo "<p class='error-message'>Invalid request.</p>";
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
            <li><a href="post_job.php">Post a New Job</a></li>
            <li><a href="logout.php" class="btn btn-danger">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="delete-job-container">
    <h2>Delete Job Posting</h2>
    <div class="message">
        <p>Are you sure you want to delete this job posting?</p>
        <div class="button-container">
            <a href="hr_home.php" class="btn btn-secondary">Cancel</a>
            <a href="delete_job.php?job_id=<?php echo $_GET['job_id']; ?>" class="btn btn-danger">Delete</a>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
