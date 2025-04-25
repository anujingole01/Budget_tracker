<!DOCTYPE html>
<html lang="en" class="terminal">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Tracker | <?php echo $pageTitle ?? 'Terminal Finance' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Space+Mono&display=swap" rel="stylesheet">

    <style>

        :root {
            --primary: #00ff9d;
            --secondary: #00b8ff;
            --background: #121212;
            --text: #e0e0e0;
            --accent: #ff2d75;
            --border: #333;
        }
        
        body {
            font-family: 'IBM Plex Mono', monospace;
            background-color: var(--background);
            color: var(--text);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .terminal-card {
            background: rgba(20, 20, 20, 0.8) !important;
            border: 1px solid #00ff9d !important;
        }
        
        .terminal-header {
            background-color: rgba(0, 0, 0, 0.7);
            border-bottom: 1px solid var(--primary);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(5px);
        }
        
        .logo {
            color: var(--primary);
            font-family: 'Space Mono', monospace;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            letter-spacing: -1px;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .nav-links {
            display: flex;
            gap: 1.5rem;
        }
        
        .nav-links a {
            color: var(--text);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: var(--primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .nav-links a:hover::after {
            transform: scaleX(1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            min-height: calc(100vh - 120px);
        }
    </style>
</head>
<body>
    <header class="terminal-header">
        <a href="index.php" class="logo">budget<span>_</span>tracker</a>
        <nav class="nav-links">
            <a href="dashboard.php">dashboard</a>
            <a href="transactions.php">transactions</a>
            <a href="reports.php">reports</a>
            <a href="settings.php">settings</a>
        </nav>
    </header>
    <main class="container">