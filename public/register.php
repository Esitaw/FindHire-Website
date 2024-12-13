<?php
session_start();
include('../includes/db.php');


if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'HR') {
        header("Location: hr_home.php");
    } else {
        header("Location: applicant_home.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];

    if ($password != $confirm_password) {
        echo "Passwords do not match!";
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $existing_user = $stmt->fetch();

    if ($existing_user) {
        echo "Email already exists!";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (:full_name, :email, :password, 'Applicant')");
    $stmt->execute([
        'full_name' => $full_name,
        'email' => $email,
        'password' => $hashed_password,
    ]);

    header("Location: login.php");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindHire - Register</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }

        label {
            font-size: 16px;
            margin-bottom: 8px;
            display: block;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            font-size: 14px;
            text-align: center;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
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

<div class="register-form">
    <h2>Register</h2>
    <form method="POST">
        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Register</button>
    </form>

    <div class="login-link">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<?php include('../includes/footer.php'); ?>

</body>
</html>
