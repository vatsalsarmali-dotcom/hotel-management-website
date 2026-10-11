<?php
$host = 'localhost';
$dbName = 'hotel_db';
$dbUser = 'root';
$dbPass = '';

// Create connection
$conn = new mysqli($host, $dbUser, $dbPass);

// Check connection for initial database creation
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $conn->connect_error
    ]));
}

// Try to select the database
if (!$conn->select_db($dbName)) {
    // Database doesn't exist, create it
    $sql = "CREATE DATABASE IF NOT EXISTS $dbName";
    if (!$conn->query($sql)) {
        die(json_encode([
            'success' => false,
            'message' => 'Failed to create database: ' . $conn->error
        ]));
    }
    // Now select it
    $conn->select_db($dbName);
}

$conn->set_charset('utf8mb4');

// Helper function to check if table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

// Helper function to run SQL file
function runSQLFile($conn, $filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $sql = file_get_contents($filePath);
    $statements = array_filter(array_map('trim', explode(';', $sql)), function($s) {
        return !empty($s) && !preg_match('/^--/', $s);
    });
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if (!$conn->query($statement . ';')) {
                error_log('SQL Error: ' . $conn->error . ' - Statement: ' . $statement);
                // Continue anyway - some statements might fail on re-run
            }
        }
    }
    
    return true;
}

// Initialize database if needed
if (!tableExists($conn, 'users')) {
    $schemaFile = __DIR__ . '/schema.sql';
    if (file_exists($schemaFile)) {
        runSQLFile($conn, $schemaFile);
    }
}
