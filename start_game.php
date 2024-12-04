<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $party_link = $_POST['party_link'];

    // Get the party ID based on the party link
    $sql = "SELECT id FROM parties WHERE party_link = ? AND status = 'waiting'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$party_link]);
    $party = $stmt->fetch();

    if ($party) {
        $party_id = $party['id'];

        // Randomly select a character from the characters table
        $sql = "SELECT id FROM characters ORDER BY RAND() LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $character = $stmt->fetch();

        if ($character) {
            // Set the game status to 'started'
            $sql = "UPDATE parties SET status = 'started' WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$party_id]);

            // Insert the first turn into the party_turns table
            $sql = "INSERT INTO party_turns (party_id, active_character_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$party_id, $character['id']]);

            echo json_encode(['status' => 'success', 'message' => 'Game started!', 'character' => $character['id']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No characters available.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Party not found or not in waiting status.']);
    }
}
?>
