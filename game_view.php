<?php
// Example of dynamic content injection with PHP
session_start();
if (!isset($_SESSION['player_id'])) {
    // Redirect to home if player is not logged in
    header('Location: index.php');
    exit;
}

// Fetch game data from the backend, such as the current player and character
// Replace this with actual database calls as needed
$player_id = $_SESSION['player_id'];
$game_state = get_game_state($player_id); // Assume this function returns the current game state
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Current Game: <?php echo $game_state['current_character']; ?></h1>

    <div>
        <h2>Your Turn</h2>
        <form action="next_turn.php" method="POST">
            <label for="character">Name a Linked Character:</label>
            <input type="text" id="character" name="character" required>
            <button type="submit">Submit</button>
        </form>
    </div>

    <div>
        <h2>Players</h2>
        <!-- Display list of players in the game -->
        <ul>
            <?php foreach ($game_state['players'] as $player): ?>
                <li><?php echo $player['name']; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
