<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $party_link = $_POST['party_link'];
    $player_name = $_POST['player_name'];
    $current_character_id = $_POST['current_character_id'];
    $next_character_id = $_POST['next_character_id'];
    $is_give_up = isset($_POST['is_give_up']) ? $_POST['is_give_up'] : false;

    // Validate party and player
    $sql = "SELECT p.id as party_id, pp.id as player_id, pp.player_order 
            FROM parties p
            JOIN party_players pp ON p.id = pp.party_id
            WHERE p.party_link = ? AND pp.player_name = ? AND p.status = 'started'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$party_link, $player_name]);
    $party_info = $stmt->fetch();

    if (!$party_info) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid party or player']);
        exit;
    }

    // If player gives up
    if ($is_give_up) {
        // Remove player from active players
        $sql = "UPDATE party_players SET is_active = 0 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$party_info['player_id']]);

        // Check if only one player remains
        $sql = "SELECT COUNT(*) as active_players FROM party_players 
                WHERE party_id = ? AND is_active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$party_info['party_id']]);
        $active_players = $stmt->fetch()['active_players'];

        if ($active_players == 1) {
            // Update party status to ended
            $sql = "UPDATE parties SET status = 'ended' WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$party_info['party_id']]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Player gives up', 'active_players' => $active_players]);
        exit;
    }

    // Check if characters are directly connected (bidirectional)
    $sql = "SELECT * FROM relations 
            WHERE (character1_id = ? AND character2_id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_character_id, $next_character_id]);
    $connection = $stmt->fetch();

    if (!$connection) {
        echo json_encode(['status' => 'error', 'message' => 'Characters are not connected']);
        exit;
    }

    // Get the last turn
    $sql = "SELECT used_characters FROM party_turns 
            WHERE party_id = ? ORDER BY id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$party_info['party_id']]);
    $last_turn = $stmt->fetch();

    // Check if character has been used before
    $used_characters = json_decode($last_turn['used_characters'], true) ?? [];
    if (in_array($next_character_id, $used_characters)) {
        echo json_encode(['status' => 'error', 'message' => 'Character already used']);
        exit;
    }

    // Add new turn
    $used_characters[] = $next_character_id;
    $sql = "INSERT INTO party_turns (party_id, active_character_id, player_id, used_characters) 
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $party_info['party_id'], 
        $next_character_id, 
        $party_info['player_id'], 
        json_encode($used_characters)
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Turn validated']);
}
?>
