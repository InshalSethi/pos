<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pos', 'root', '');
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Total tables in 'pos': " . count($tables) . "\n";
    print_r($tables);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
