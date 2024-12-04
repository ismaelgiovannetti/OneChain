<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $party_link = $_GET['party_link'];

    // Get party details
    $sql = "SELECT id, status FROM parties WHERE party_link = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$party_link]);
    $party = $stmt->fetch();

    if (!$party) {
        echo json_encode(['status' => 'error', 'message' => 'Party not found']);
        exit;
    }

    // Get active players
    $sql = "SELECT player_name, player_order FROM party_players 
            WHERE party_id = ? AND is_active = 1 
            ORDER BY player_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$party['id']]);
    $active_players = $stmt->fetchAll();

    // Get current turn
    $sql = "SELECT pt.active_character_id, pt.used_characters, c.name as active_character_name, 
                   pp.player_name as current_player
            FROM party_turns pt
            JOIN characters c ON pt.active_character_id = c.id
            JOIN party_players pp ON pt.player_id = pp.id
            WHERE pt.party_id = ?
            ORDER BY pt.id DESC
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$party['id']]);
    $current_turn = $stmt->fetch();

    // Get possible next characters
    $used_characters = json_decode($current_turn['used_characters'], true) ?? [];
    $used_chars_str = implode(',', $used_characters ?: [0]);

    $sql = "SELECT r.character2_id as character_id, c.name
            FROM relations r
            JOIN characters c ON r.character2_id = c.id
            WHERE r.character1_id = ?
            AND r.character2_id NOT IN ($used_chars_str)
            UNION
            SELECT r.character1_id as character_id, c.name
            FROM relations r
            JOIN characters c ON r.character1_id = c.id
            WHERE r.character2_id = ?
            AND r.character1_id NOT IN ($used_chars_str)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_turn['active_character_id'], $current_turn['active_character_id']]);
    $possible_characters = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'party_status' => $party['status'],
        'active_players' => $active_players,
        'current_turn' => [
            'active_character_id' => $current_turn['active_character_id'],
            'active_character_name' => $current_turn['active_character_name'],
            'current_player' => $current_turn['current_player']
        ],
        'possible_characters' => $possible_characters,
        'used_characters' => $used_characters
    ]);
}
?>
