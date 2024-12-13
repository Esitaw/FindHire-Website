<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'HR') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['job_id'])) {
    echo "Job ID is required.";
    exit;
}

$job_id = intval($_GET['job_id']);

$job_stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = :job_id");
$job_stmt->execute(['job_id' => $job_id]);
$job = $job_stmt->fetch();

if (!$job) {
    echo "Job not found.";
    exit;
}

$applicants_stmt = $pdo->prepare("
    SELECT 
        applications.id AS application_id, 
        users.email AS applicant_email, 
        applications.resume_path, 
        applications.status, 
        applications.message,
        applications.user_id  -- Ensure user_id is selected
    FROM applications
    JOIN users ON applications.user_id = users.id
    WHERE applications.job_post_id = :job_id
");


$applicants_stmt->execute(['job_id' => $job_id]);
$applicants = $applicants_stmt->fetchAll();
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Montserrat', sans-serif;
        background-color: #f4f8fc; 
        color: #333; 
        margin: 0;
        padding: 0;
    }

    .view-applicants-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }

    .center-text {
        text-align: center;
    }

    .applicant-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .actions {
        margin-top: 10px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        text-align: center;
    }

    .actions a {
        flex-grow: 1;
        max-width: 200px;
    }

    .btn {
        display: inline-block;
        padding: 10px 15px;
        margin: 10px 5px 0;
        text-decoration: none;
        font-size: 1em;
        border-radius: 5px;
        text-align: center;
        font-weight: bold;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
        border: none;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
        border: none;
    }

    .btn:hover {
        opacity: 0.9;
    }

    .button-container {
        text-align: center;
        margin-bottom: 20px;
    }

    .btn-secondary {
        background-color: red;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
    }

    .btn-secondary:hover {
        opacity: 0.9;
    }

    footer {
        background-color: #007bff;
        color: white;
        text-align: center;
        padding: 15px;
        margin-top: 30px;
        position: relative;
        bottom: 0;
        width: 100%;
        font-size: 1em;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    footer {
        margin-top: auto;
    }

    .confirmation-message {
        background-color: #28a745;
        color: white;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
    }

</style>

<div class="view-applicants-container">
    <h2 class="center-text">Applicants for "<?php echo htmlspecialchars($job['title']); ?>"</h2>

    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="confirmation-message">
            <?php echo $_SESSION['confirmation_message']; ?>
            <?php unset($_SESSION['confirmation_message']); ?> 
        </div>
    <?php endif; ?>

    <div class="button-container center-text">
        <a href="hr_home.php" class="btn btn-secondary">Back to HR Homepage</a>
    </div>

    <?php if (count($applicants) > 0): ?>
        <div class="applicants-list">
            <?php foreach ($applicants as $applicant): ?>
                <div class="applicant-card">
                    <h3>Applicant ID: <?php echo htmlspecialchars($applicant['application_id']); ?></h3>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($applicant['applicant_email']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($applicant['status']); ?></p>
                    <p><strong>Message:</strong> <?php echo nl2br(htmlspecialchars($applicant['message'])); ?></p>
                    <div class="actions">
                        <a href="../uploads/<?php echo htmlspecialchars($applicant['resume_path']); ?>" target="_blank" class="btn btn-primary">View Resume</a>
                        <a href="../scripts/update_application.php?application_id=<?php echo $applicant['application_id']; ?>&status=Accepted&job_id=<?php echo $job_id; ?>&user_id=<?php echo $applicant['user_id']; ?>" class="btn btn-success">Accept</a>
                        <a href="../scripts/update_application.php?application_id=<?php echo $applicant['application_id']; ?>&status=Rejected&job_id=<?php echo $job_id; ?>&user_id=<?php echo $applicant['user_id']; ?>" class="btn btn-success">Reject</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="center-text">No applicants found for this job.</p>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
