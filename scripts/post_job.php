<?php
session_start();
include('../includes/db.php');

if ($_SESSION['role'] != 'HR') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO jobs (title, description, hr_id) VALUES (:title, :description, :hr_id)");
    $stmt->execute(['title' => $title, 'description' => $description, 'hr_id' => $_SESSION['user_id']]);

    echo "<p class='success-message'>Job posted successfully.</p>";
}
?>

<link rel="stylesheet" type="text/css" href="../assets/home.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

<div class="post-job-container">
    <h2>Post a New Job</h2>
    <form method="POST">
        <div class="form-group">
            <label for="title">Job Title</label>
            <input type="text" id="title" name="title" placeholder="Job Title" required class="form-input">
        </div>
        <div class="form-group">
            <label for="description">Job Description</label>
            <textarea id="description" name="description" placeholder="Job Description" required class="form-textarea"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Post Job</button>
    </form>

    <div class="button-container">
        <a href="../public/hr_home.php" class="btn btn-secondary">Back to HR Homepage</a>
    </div>
</div>

<?php
include('../includes/footer.php');
?>
