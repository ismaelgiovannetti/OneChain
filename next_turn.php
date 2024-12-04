<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $party_link = $_POST['party_link'];
    $player_name = $_POST['player_name'];

    // Get the party ID
    $sql = "SELECT id FROM parties WHERE party_link = ? AND status = 'started'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$party_link]);
    $party = $stmt->fetch();

    if ($party) {
        $party_id = $party['id'];

        // Get the current turn information
        $sql = "SELECT * FROM party_turns WHERE party_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$party_id]);
        $turn = $stmt->fetch();

        // Get the current player and check if it's their turn
        $sql = "SELECT * FROM party_players WHERE party_id = ? AND player_name = ? AND is_active = true";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$party_id, $player_name]);
        $player = $stmt->fetch();

        if ($player && $turn) {
            // Get the player’s turn order
            $player_order = $player['player_order'];

            // Check if it's the player's turn
            if ($player_order == $turn['player_id']) {
                // Proceed with the game (e.g., validate the move, change active character)
                
                // Example: If the player makes a valid move, update the active character
                $new_active_character_id = $_POST['new_character_id']; // Character selected by player
                
                // Update the turn with the new active character
                $sql = "INSERT INTO party_turns (party_id, active_character_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$party_id, $new_active_character_id]);

                echo json_encode(['status' => 'success', 'message' => 'Turn completed!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'It is not your turn!']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Player not found or inactive.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Party not found or not started yet.']);
    }
}
?>
