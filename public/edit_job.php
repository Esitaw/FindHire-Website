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

$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = :job_id");
$stmt->execute(['job_id' => $job_id]);
$job = $stmt->fetch();

if (!$job) {
    echo "Job not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $update_stmt = $pdo->prepare("UPDATE jobs SET title = :title, description = :description WHERE id = :job_id");
    $update_stmt->execute(['title' => $title, 'description' => $description, 'job_id' => $job_id]);

    echo "<p class='success-message'>Job updated successfully.</p>";
    header("Location: hr_home.php");
    exit;
}
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Montserrat', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f9;
}

.edit-job-container {
    max-width: 600px;
    margin: 50px auto;
    padding: 20px;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.edit-job-container h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

.edit-job-form {
    margin-top: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: bold;
}

.form-group .form-input,
.form-group .form-textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    font-family: 'Montserrat', sans-serif;
}

.form-group .form-textarea {
    height: 120px;
    resize: none;
}

.btn {
    display: inline-block;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-weight: bold;
    text-align: center;
    font-size: 14px;
    cursor: pointer;
}

.btn-primary {
    background-color: #007bff;
    color: #fff;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-secondary {
    background-color: red;
    color: #fff;
}

.btn-secondary:hover {
    background-color: #565e64;
}

.button-container {
    margin-top: 15px;
    text-align: center;
}

footer {
            color: black;
            text-align: center;
            padding: 15px;
            font-size: 1em;
}
</style>

<div class="edit-job-container">
    <h2>Edit Job Posting</h2>

    <form method="POST" class="edit-job-form">
        <div class="form-group">
            <label for="title">Job Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($job['title']); ?>" required class="form-input">
        </div>
        <div class="form-group">
            <label for="description">Job Description</label>
            <textarea id="description" name="description" required class="form-textarea"><?php echo htmlspecialchars($job['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>

    <div class="button-container">
        <a href="hr_home.php" class="btn btn-secondary">Back to HR Homepage</a>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
