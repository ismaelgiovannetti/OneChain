<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Generate a unique party link
    $party_link = uniqid('party', true);

    // Insert into the parties table
    $sql = "INSERT INTO parties (party_link, status) VALUES (?, 'waiting')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$party_link]);

    // Get the party ID for the newly created party
    $party_id = $pdo->lastInsertId();

    // Return the party link to share with others
    echo json_encode(['party_link' => $party_link]);
}
?>

