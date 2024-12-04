<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $party_link = $_POST['party_link'];
    $player_name = $_POST['player_name'];

    // Check if the party exists
    $sql = "SELECT id FROM parties WHERE party_link = ? AND status = 'waiting'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$party_link]);
    $party = $stmt->fetch();

    if ($party) {
        // Insert the player into the party_players table
        $party_id = $party['id'];

        // Get the current number of players to determine the turn order
        $sql = "SELECT COUNT(*) FROM party_players WHERE party_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$party_id]);
        $turn_order = $stmt->fetchColumn() + 1; // next turn number

        // Insert the new player
        $sql = "INSERT INTO party_players (party_id, player_name, player_order, is_active) VALUES (?, ?, ?, true)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$party_id, $player_name, $turn_order]);

        echo json_encode(['status' => 'success', 'message' => 'Player added to party.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Party not found or not in waiting status.']);
    }
}
?>
