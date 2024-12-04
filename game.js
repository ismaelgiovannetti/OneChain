let gameState = {
    partyLink: null,
    playerName: null,
    activeCharacter: null,
    possibleCharacters: []
};

$(document).ready(function() {
    $('#characterSelect').select2({
        placeholder: 'Select a character',
        minimumInputLength: 0
    });

    // Create Party
    $('#createPartyBtn').on('click', function() {
        const playerName = $('#playerName').val();
        if (!playerName) {
            alert('Please enter your name');
            return;
        }

        $.ajax({
            url: 'create_party.php',
            method: 'POST',
            error : function(response) {
                console.log(response);
            },
            success: function(response) {
                const data = JSON.parse(response);
                gameState.partyLink = data.party_link;
                gameState.playerName = playerName;

                $('#partyLinkArea').show();
                $('#partyLink').val(window.location.origin + '?party=' + data.party_link);
                joinParty(data.party_link);
            }
        });
    });

    // Join Party
    $('#joinPartyBtn').on('click', function() {
        const playerName = $('#playerName').val();
        const partyLink = prompt('Enter Party Link:');
        
        if (!playerName || !partyLink) {
            alert('Please enter your name and party link');
            return;
        }

        gameState.playerName = playerName;
        gameState.partyLink = partyLink;
        joinParty(partyLink);
    });

    function joinParty(partyLink) {
        $.ajax({
            url: 'join_party.php',
            method: 'POST',
            data: { 
                party_link: partyLink, 
                player_name: gameState.playerName 
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.status === 'success') {
                    $('#partySetup').hide();
                    $('#gameArea').show();
                    startPollingGameState();
                } else {
                    alert(data.message);
                }
            }
        });
    }

    function startPollingGameState() {
        function pollState() {
            $.ajax({
                url: 'get_game_state.php',
                method: 'GET',
                data: { party_link: gameState.partyLink },
                success: function(response) {
                    const data = JSON.parse(response);
                    updateGameUI(data);
                }
            });
        }
        pollState();
        setInterval(pollState, 5000);
    }

    function updateGameUI(data) {
        gameState.activeCharacter = data.current_turn.active_character_name;
        gameState.possibleCharacters = data.possible_characters;

        $('#activeCharacter').text(data.current_turn.active_character_name);
        $('#gameStatus').text(`Current Player: ${data.current_turn.current_player}`);

        // Update character select
        $('#characterSelect').empty().append('<option value="">Select a character</option>');
        data.possible_characters.forEach(char => {
            $('#characterSelect').append(`<option value="${char.character_id}">${char.name}</option>`);
        });
        $('#characterSelect').trigger('change');
    }

    // Submit Character
    $('#submitCharacterBtn').on('click', function() {
        const selectedCharacterId = $('#characterSelect').val();
        
        if (!selectedCharacterId) {
            alert('Please select a character');
            return;
        }

        $.ajax({
            url: 'validate_turn.php',
            method: 'POST',
            data: {
                party_link: gameState.partyLink,
                player_name: gameState.playerName,
                current_character_id: gameState.activeCharacter.id,
                next_character_id: selectedCharacterId
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.status === 'error') {
                    alert(data.message);
                }
                startPollingGameState();
            }
        });
    });

    // Give Up
    $('#giveUpBtn').on('click', function() {
        $.ajax({
            url: 'validate_turn.php',
            method: 'POST',
            data: {
                party_link: gameState.partyLink,
                player_name: gameState.playerName,
                is_give_up: true
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.active_players === 1) {
                    alert('Game Over! Only one player remains.');
                }
                startPollingGameState();
            }
        });
    });
});
