<?php
require 'db_connection.php';

// Function to safely insert character if not exists
function insertCharacter($pdo, $name) {
    $sql = "INSERT IGNORE INTO characters (name) VALUES (?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name]);
    
    // Get the character ID (either newly inserted or existing)
    $sql = "SELECT id FROM characters WHERE name = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name]);
    return $stmt->fetchColumn();
}

// Function to insert relation if not exists (bidirectional)
function insertRelation($pdo, $char1_id, $char2_id) {
    // Check if relation already exists in either direction
    $sql = "SELECT COUNT(*) FROM relations 
            WHERE (character1_id = ? AND character2_id = ?) 
               OR (character1_id = ? AND character2_id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$char1_id, $char2_id, $char2_id, $char1_id]);
    
    if ($stmt->fetchColumn() == 0) {
        $sql = "INSERT INTO relations (character1_id, character2_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$char1_id, $char2_id]);
    }
}

// Array of characters with their connections
$characters = [
    "Monkey D. Luffy" => [
        "Zoro", "Nami", "Usopp", "Sanji", "Chopper", 
        "Robin", "Franky", "Brook", "Jinbe"
    ],
    "Roronoa Zoro" => [
        "Monkey D. Luffy", "Sanji", "Chopper"
    ],
    "Nami" => [
        "Monkey D. Luffy", "Usopp", "Sanji", "Robin"
    ],
    "Usopp" => [
        "Monkey D. Luffy", "Nami", "Sanji", "Chopper"
    ],
    "Sanji" => [
        "Monkey D. Luffy", "Zoro", "Nami", "Usopp", "Chopper", "Robin"
    ],
    "Tony Tony Chopper" => [
        "Monkey D. Luffy", "Zoro", "Usopp", "Sanji", "Robin"
    ],
    "Nico Robin" => [
        "Monkey D. Luffy", "Nami", "Sanji", "Chopper", "Franky"
    ],
    "Franky" => [
        "Nico Robin", "Brook"
    ],
    "Brook" => [
        "Franky", "Jinbe"
    ],
    "Jinbe" => [
        "Monkey D. Luffy", "Brook"
    ]
];

// Start transaction for better performance and atomicity
$pdo->beginTransaction();

try {
    // First, insert all characters
    $character_ids = [];
    foreach (array_keys($characters) as $character) {
        $character_ids[$character] = insertCharacter($pdo, $character);
    }

    // Then, insert relations
    foreach ($characters as $main_char => $connected_chars) {
        $main_char_id = $character_ids[$main_char];
        
        foreach ($connected_chars as $connected_char) {
            $connected_char_id = $character_ids[$connected_char];
            insertRelation($pdo, $main_char_id, $connected_char_id);
        }
    }

    // Commit transaction
    $pdo->commit();
    echo "Successfully populated characters and relations!\n";
} catch (Exception $e) {
    // Rollback in case of error
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

// Print some stats
$sql = "SELECT COUNT(*) FROM characters";
$stmt = $pdo->query($sql);
echo "Total Characters: " . $stmt->fetchColumn() . "\n";

$sql = "SELECT COUNT(*) FROM relations";
$stmt = $pdo->query($sql);
echo "Total Relations: " . $stmt->fetchColumn() . "\n";
?>
