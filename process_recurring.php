<?php
require 'config.php';

$conn->query("
    INSERT INTO transactions (user_id, type, category, amount, description, is_recurring, created_at)
    SELECT user_id, type, category, amount, description, FALSE, NOW()
    FROM transactions
    WHERE is_recurring = TRUE 
    AND DATEDIFF(NOW(), created_at) >= recurrence_interval
    AND (last_recurrence IS NULL OR DATEDIFF(NOW(), last_recurrence) >= recurrence_interval)
");

$conn->query("
    UPDATE transactions 
    SET last_recurrence = NOW() 
    WHERE is_recurring = TRUE 
    AND DATEDIFF(NOW(), created_at) >= recurrence_interval
");
?>