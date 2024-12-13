<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Applicant') {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id, email FROM users WHERE role = 'HR'");
$stmt->execute();
$hrs = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $hr_id = $_POST['hr_id'];

    $stmt = $pdo->prepare("INSERT INTO messages (applicant_id, hr_id, subject, message) VALUES (:applicant_id, :hr_id, :subject, :message)");
    $stmt->execute([
        'applicant_id' => $_SESSION['user_id'],
        'hr_id' => $hr_id,
        'subject' => $subject,
        'message' => $message
    ]);

    echo "<p class='success-message'>Your message has been sent to the HR representative.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message HR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between; 
            align-items: center;
        }

        header .logo h1 {
            margin: 0;
            font-size: 24px;
        }

        header nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
        }

        header nav ul li {
            margin-left: 20px;
        }

        header nav ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        main {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .success-message {
            background-color: #4caf50;
            color: white;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
        }

        select, input, textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #4caf50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
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
            <li><a href="../public/applicant_home.php">Home</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="send-message-container">
        <h2>Send Message to HR</h2>

        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <p class="success-message">Your message has been sent to the HR representative.</p>
        <?php endif; ?>

        <form method="POST" action="send_message.php">
            <div class="form-group">
                <label for="hr_id">Select HR Representative</label>
                <select name="hr_id" id="hr_id" required>
                    <?php foreach ($hrs as $hr): ?>
                        <option value="<?php echo $hr['id']; ?>"><?php echo htmlspecialchars($hr['email']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" name="subject" id="subject" required>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea name="message" id="message" rows="5" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
</main>

<?php include('../includes/footer.php'); ?>

</body>
</html>
