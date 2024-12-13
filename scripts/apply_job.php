<?php
session_start();
include('../includes/db.php');


if ($_SESSION['role'] != 'Applicant') {
    header("Location: login.php");
    exit;
}

$success_message = "";

if (isset($_GET['job_id'])) {
    $job_id = $_GET['job_id'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $description = $_POST['description'];
        $resume = $_FILES['resume'];

        $target_dir = "../uploads/applicant_resumes/";
        $target_file = $target_dir . basename($resume["name"]);
        move_uploaded_file($resume["tmp_name"], $target_file);

        $stmt = $pdo->prepare("INSERT INTO applications (user_id, job_post_id, message, resume_path) VALUES (:user_id, :job_post_id, :message, :resume_path)");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'job_post_id' => $job_id,
            'message' => $description,
            'resume_path' => $target_file
        ]);

        $success_message = "Application submitted successfully!";
    }

    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = :id");
    $stmt->execute(['id' => $job_id]);
    $job = $stmt->fetch();
} else {
    echo "Job not found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header .logo h1 {
            margin: 0;
            font-size: 1.8em;
        }

        header nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 15px;
        }

        header nav ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        header nav ul li a:hover {
            color: #ff4500;
        }

        .apply-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .apply-container h2 {
            font-size: 2em;
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
            text-align: center;
        }

        .apply-container form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .apply-container label {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .apply-container textarea {
            width: 90%;
            padding: 15px;
            font-size: 1em;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: vertical;
            min-height: 150px; 
        }

        .apply-container input[type="file"] {
            padding: 10px;
            font-size: 1em;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .apply-container button {
            padding: 12px 20px;
            font-size: 1.2em;
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .apply-container button:hover {
            background-color: #ff4500;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }

        .button-container a {
            text-decoration: none;
            background-color: #c71515;
            color: white;
            padding: 12px 25px;
            font-size: 1.2em;
            border-radius: 5px;
            cursor: pointer;
        }

        footer {
            color: black;
            text-align: center;
            padding: 15px;
            font-size: 1em;
        }

    </style>
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
        </ul>
    </nav>
</header>

<div class="apply-container">
    <?php if ($success_message): ?>
        <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>
    <h2>Apply for Job: <?php echo htmlspecialchars($job['title']); ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="description">Why are you the best fit?</label>
            <textarea id="description" name="description" placeholder="Write your reasons here..." required></textarea>
        </div>
        <div class="form-group">
            <label for="resume">Upload Resume</label>
            <input type="file" id="resume" name="resume" required>
        </div>
        <button type="submit">Apply</button>
    </form>

    <div class="button-container">
        <a href="../public/applicant_home.php">Back to Home</a>
    </div>
</div>

<?php include('../includes/footer.php'); ?>

</body>
</html>
