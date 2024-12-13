<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'HR') {
    header("Location: login.php");
    exit;
}

$message_id = $_GET['message_id'];

$stmt = $pdo->prepare("UPDATE messages SET read_by_hr = 'read' WHERE id = :message_id");
$stmt->execute(['message_id' => $message_id]);

header("Location: hr_messages.php");
exit;
