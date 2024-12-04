<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $party_link = $_POST['party_link'];
    $player_name = $_POST['player_name']; // Player to kick
    $leader_name = $_POST['leader_name']; // Leader who can kick

    // Get the party ID
    $sql = "SELECT id, leader_id FROM parties WHERE party_link = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$party_link]);
    $party = $stmt->fetch();

    if ($party && $party['leader_id'] == $leader_name) {
        // Check if the player exists in the party
        $sql = "SELECT * FROM party_players WHERE party_id = ? AND player_name = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$party['id'], $player_name]);
        $player = $stmt->fetch();

        if ($player) {
            // Remove the player from the game
            $sql = "UPDATE party_players SET is_active = false WHERE party_id = ? AND player_name = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$party['id'], $player_name]);

            echo json_encode(['status' => 'success', 'message' => 'Player kicked from party.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Player not found in the party.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'You are not the leader or party not found.']);
    }
}
?>
