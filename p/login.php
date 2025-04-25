<?php
require_once 'config.php';
require_once 'auth.php';
redirect_if_logged_in();

// Initialize variables
$error = null;
$email = '';

// Check for logout success
if (isset($_GET['logout'])) {
    $error = 'You have been successfully logged out.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['last_activity'] = time();
            
            $redirect = $_SESSION['redirect_to'] ?? 'dashboard.php';
            unset($_SESSION['redirect_to']);
            header("Location: $redirect");
            exit();
        }
    }
    
    $error = "Invalid email or password!";
}
?>
<?php include 'header.php'; ?>
<!-- Rest of your HTML form remains the same -->

<div class="terminal-card">
    <header>login</header>
    <?php if (!empty($error)): ?>
        <div class="terminal-alert error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
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
        <button type="submit" class="terminal-btn">authenticate</button>
    </form>
    <div class="terminal-alert">
        no account? <a href="register.php" class="terminal-link">register here</a>
    </div>
</div>

<style>
    .terminal-card {
        background: rgba(20, 20, 20, 0.8);
        border: 1px solid var(--primary);
        max-width: 400px;
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