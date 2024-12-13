<?php
session_start();
include('../includes/db.php');
include('../includes/header.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'HR') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['application_id']) || !isset($_GET['status']) || !isset($_GET['job_id']) || !isset($_GET['user_id'])) {
    echo "Missing parameters in the request. Ensure all required parameters are passed.";
    exit;
}

$application_id = intval($_GET['application_id']);
$status = $_GET['status'];
$job_id = intval($_GET['job_id']);
$user_id = intval($_GET['user_id']);

if (!in_array($status, ['Accepted', 'Rejected'])) {
    echo "Invalid status.";
    exit;
}

$update_stmt = $pdo->prepare("
    UPDATE applications 
    SET status = :status 
    WHERE id = :application_id
");
$update_stmt->execute([
    'status' => $status,
    'application_id' => $application_id
]);

if ($status == 'Accepted') {
    $update_job_stmt = $pdo->prepare("
        UPDATE jobs 
        SET hired_applicant_id = :user_id 
        WHERE id = :job_id
    ");
    $update_job_stmt->execute([
        'user_id' => $user_id,
        'job_id' => $job_id
    ]);

    $_SESSION['confirmation_message'] = "The applicant has been hired successfully!";
} else {
    $_SESSION['confirmation_message'] = "The applicant has been rejected.";
}

header("Location: ../public/view_applicants.php?job_id=" . $job_id);
exit;
?>
