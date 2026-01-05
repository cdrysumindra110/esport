// Switch between tabs (Details / Rules / Prizes / Participants / Contact)
function showContent(section) {
    // Hide all content containers
    document.querySelectorAll('.content-container').forEach(el => {
        el.classList.remove('active');
    });

    // Remove active class from tabs
    document.querySelectorAll('.tournament-details div').forEach(el => {
        el.classList.remove('active');
    });

    // Show the selected content
    const targetContainer = document.getElementById(section + "-container");
    if (targetContainer) {
        targetContainer.classList.add('active');
    }

    // Highlight the clicked tab
    const targetTab = document.getElementById(section);
    if (targetTab) {
        targetTab.classList.add('active');
    }
}

// Start the game (redirects to start_game.php)
function start_game(tournamentId) {
    if (!tournamentId || isNaN(tournamentId)) {
        alert("❌ Invalid tournament ID");
        return;
    }
    if (confirm("Are you sure you want to start this tournament?")) {
        window.location.href = "start_game.php?tournament_id=" + encodeURIComponent(tournamentId);
    }
}

// Remove a participant/team
function confirmRemove(tournamentId, matchType, teamName) {
    if (confirm("Are you sure you want to remove " + teamName + "?")) {
        // Create a hidden form and submit it
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "";

        form.innerHTML = `
            <input type="hidden" name="remove" value="yes">
            <input type="hidden" name="tournament_id" value="${tournamentId}">
            <input type="hidden" name="match_type" value="${matchType}">
            <input type="hidden" name="team_name" value="${teamName}">
        `;

        document.body.appendChild(form);
        form.submit();
    }
}

// Function to confirm tournament deletion
function confirmDelete(tournamentId) {
    if (confirm("⚠️ Are you sure you want to delete this tournament?\n\nThis action will permanently delete:\n• All tournament details\n• All registered teams/players\n• Brackets and matches\n• Leaderboard data\n\nThis action cannot be undone!")) {
        // If user confirms, submit the deletion form
        document.getElementById('deleteTournamentId').value = tournamentId;
        document.getElementById('deleteTournamentForm').submit();
    }
}

// Function to confirm team removal (already exists but ensure it's correct)
function confirmRemove(tournamentId, matchType, teamName) {
    if (confirm("Are you sure you want to remove '" + teamName + "' from the tournament?")) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const removeInput = document.createElement('input');
        removeInput.type = 'hidden';
        removeInput.name = 'remove';
        removeInput.value = 'yes';
        form.appendChild(removeInput);
        
        const tournamentIdInput = document.createElement('input');
        tournamentIdInput.type = 'hidden';
        tournamentIdInput.name = 'tournament_id';
        tournamentIdInput.value = tournamentId;
        form.appendChild(tournamentIdInput);
        
        const teamNameInput = document.createElement('input');
        teamNameInput.type = 'hidden';
        teamNameInput.name = 'team_name';
        teamNameInput.value = teamName;
        form.appendChild(teamNameInput);
        
        const matchTypeInput = document.createElement('input');
        matchTypeInput.type = 'hidden';
        matchTypeInput.name = 'match_type';
        matchTypeInput.value = matchType;
        form.appendChild(matchTypeInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}