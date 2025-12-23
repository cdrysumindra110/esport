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

// Delete tournament
function confirmDelete(tournamentId) {
    if (!tournamentId || isNaN(tournamentId)) {
        alert("❌ Invalid tournament ID");
        return;
    }
    if (confirm("Are you sure you want to delete this tournament?")) {
        window.location.href = "delete_tour.php?tournament_id=" + encodeURIComponent(tournamentId);
    }
}
