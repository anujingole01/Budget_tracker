<?php
require_once 'config.php';
require_once 'auth.php'; 

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        
        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

$pageTitle = "Register";
include 'header.php';
?>

<div class="terminal-card">
    <header>create account</header>
    <?php if (!empty($errors)): ?>
        <div class="terminal-alert error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <div class="form-group">
            <label for="name">name:</label>
            <input type="text" id="name" name="name" 
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                   required class="terminal-input" placeholder="john_doe">
        </div>
        <div class="form-group">
            <label for="email">email:</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                   required class="terminal-input" placeholder="user@domain.com">
        </div>
        <div class="form-group">
            <label for="password">password:</label>
            <input type="password" id="password" name="password" 
                   required class="terminal-input" placeholder="••••••••">
        </div>
        <button type="submit" class="terminal-btn">register</button>
    </form>
    <div class="terminal-alert">
        existing user? <a href="login.php" class="terminal-link">login here</a>
    </div>
</div>

<style>
    .terminal-card {
        background: rgba(20, 20, 20, 0.8);
        border: 1px solid var(--primary);
        max-width: 500px;
        margin: 3rem auto;
        padding: 2rem;
        box-shadow: 0 0 15px rgba(0, 255, 157, 0.1);
    }
    
    .terminal-card header {
        color: var(--primary);
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        text-align: center;
        font-family: 'Space Mono', monospace;
        text-transform: lowercase;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--secondary);
    }
    
    .terminal-input {
        width: 100%;
        padding: 0.75rem;
        background: rgba(0, 0, 0, 0.5);
        border: 1px solid var(--border);
        color: var(--text);
        font-family: 'IBM Plex Mono', monospace;
    }
    
    .terminal-input::placeholder {
        color: #555;
    }
    
    .terminal-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(0, 255, 157, 0.2);
    }
    
    .terminal-btn {
        width: 100%;
        padding: 0.75rem;
        background: var(--primary);
        color: #000;
        border: none;
        font-family: 'IBM Plex Mono', monospace;
        font-weight: bold;
        cursor: pointer;
        text-transform: lowercase;
    }
    
    .terminal-btn:hover {
        background: #00e68a;
    }
    
    .terminal-alert {
        margin-top: 1.5rem;
        padding: 0.75rem;
        background: rgba(0, 184, 255, 0.1);
        border-left: 3px solid var(--secondary);
        text-align: center;
    }
    
    .terminal-alert.error {
        background: rgba(255, 45, 117, 0.1);
        border-left-color: var(--accent);
    }
    
    .terminal-link {
        color: var(--secondary);
        text-decoration: none;
    }
    
    .terminal-link:hover {
        text-decoration: underline;
    }
</style>

<?php include 'footer.php'; ?>