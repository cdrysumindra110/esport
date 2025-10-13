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

document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtn = document.getElementById('br-options-btn');
    const dropdownContainer = document.querySelector('.br-controls-dropdown');

    dropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // prevent window click from immediately closing
        dropdownContainer.classList.toggle('show');
    });

    window.addEventListener('click', function() {
        dropdownContainer.classList.remove('show');
    });
});

// Functions for buttons
function updateLeaderboard(tournamentId) {
    fetch('update_br_leaderboard.php?tournament_id=' + tournamentId)
        .then(res => res.text())
        .then(() => { alert("✅ Leaderboard updated!"); location.reload(); });
}

function updateBrackets(tournamentId) {
    fetch('update_br_brackets.php?tournament_id=' + tournamentId)
        .then(res => res.text())
        .then(() => { alert("✅ Brackets updated!"); location.reload(); });
}

function simulateMatches(tournamentId) {
    fetch('simulate_matches.php?tournament_id=' + tournamentId)
        .then(res => res.text())
        .then(() => { alert("🎲 Matches simulated!"); updateLeaderboard(tournamentId); });
}
