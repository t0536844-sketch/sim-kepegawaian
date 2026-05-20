<?php
// migrate.php — Run database migrations
require_once 'config.php';
$database = new Database();
$db = $database->getConnection();

echo "<pre>";
echo "=== Database Migration ===\n\n";

// Check and add missing columns
$columns = $db->query("PRAGMA table_info(pegawai)")->fetchAll(PDO::FETCH_COLUMN, 1);
$addColumns = ['no_telepon'];

foreach ($addColumns as $col) {
    if (!in_array($col, $columns)) {
        try {
            $db->exec("ALTER TABLE pegawai ADD COLUMN $col TEXT");
            echo "✅ Added column: $col\n";
        } catch (Exception $e) {
            echo "⚠️  Failed to add $col: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⏭️  Column $col already exists\n";
    }
}

// Summary
$cols = $db->query("PRAGMA table_info(pegawai)")->fetchAll(PDO::FETCH_COLUMN, 1);
echo "\nColumns in pegawai: " . implode(', ', $cols) . "\n";
echo "\n✅ Migration complete!\n";
echo "</pre>";
