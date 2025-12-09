<?php
// Initialize variables
$name = $email = $password = $confirm_password = "";
$errors = [];
$success_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Sanitize inputs
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // ---- VALIDATION ----

    // Check required fields
    if (empty($name)) {
        $errors['name'] = "Name is required.";
    }
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6 || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors['password'] = "Password must be at least 6 characters & contain a special character.";
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // If no validation errors
    if (empty($errors)) {
        $file = "users.json";

        // Check if file exists
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([]));
        }

        // Read JSON file
        $json_data = file_get_contents($file);
        $users = json_decode($json_data, true);

        if (!is_array($users)) {
            $users = [];
        }

        // Password hashing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Create user data array
        $new_user = [
            "name" => $name,
            "email" => $email,
            "password" => $hashed_password
        ];

        // Add new user to users array
        $users[] = $new_user;

        // Save to JSON file
        if (file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT))) {
            $success_message = "Registration Successful!";
            $name = $email = "";
        } else {
            $errors['file'] = "Error writing to users.json file.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        .error { color: red; font-size: 14px; }
        .success { color: green; font-size: 16px; margin-bottom: 10px; }
        form { width: 300px; margin: auto; }
        label { font-weight: bold; }
        input { width: 100%; margin-bottom: 10px; padding: 8px; }
    </style>
</head>
<body>

<h2 style="text-align:center;">User Registration</h2>

<?php if(!empty($success_message)) : ?>
    <div class="success"><?= $success_message; ?></div>
<?php endif; ?>

<form method="POST" action="">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($name); ?>">
    <div class="error"><?= $errors['name'] ?? ''; ?></div>

    <label>Email:</label><br>
    <input type="text" name="email" value="<?= htmlspecialchars($email); ?>">
    <div class="error"><?= $errors['email'] ?? ''; ?></div>

    <label>Password:</label><br>
    <input type="password" name="password">
    <div class="error"><?= $errors['password'] ?? ''; ?></div>

    <label>Confirm Password:</label><br>
    <input type="password" name="confirm_password">
    <div class="error"><?= $errors['confirm_password'] ?? ''; ?></div>

    <button type="submit">Register</button>

    <div class="error"><?= $errors['file'] ?? ''; ?></div>
</form>

</body>
</html>
