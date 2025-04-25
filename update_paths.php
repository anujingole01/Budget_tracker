<?php
// Run this AFTER running reorganize.bat
$files = [
    'p/dashboard.php',
    'p/login.php',
    'p/register.php',
    'p/transactions.php',
    'p/logout.php'
];

$replacements = [
    "require 'config.php'" => "require __DIR__ . '/private/config.php'",
    "require 'header.php'" => "require __DIR__ . '/private/header.php'",
    "require 'footer.php'" => "require __DIR__ . '/private/footer.php'",
    "assets/" => "public/assets/"
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $content = str_replace(
        array_keys($replacements),
        array_values($replacements),
        $content
    );
    file_put_contents($file, $content);
    echo "Updated paths in: $file\n";
}

echo "Path updates complete!\n";
?>