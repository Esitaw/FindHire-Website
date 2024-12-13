<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'HR') {
        header("Location: hr_home.php");
    } else if ($_SESSION['role'] == 'Applicant') {
        header("Location: applicant_home.php");
    }
} else {
    header("Location: login.php");
}
exit;
?>
