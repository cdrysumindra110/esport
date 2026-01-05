class InfiknightPredictor {
    constructor() {
        // Existing initializations...
        this.playerCount = 1;
        this.players = [];
        this.teams = [];
        
        // New event listeners
        this.initializeTeamLogic();
    }
    
    initializeTeamLogic() {
        // Match type change handler
        document.querySelectorAll('input[name="matchType"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.handleMatchTypeChange(e.target.value);
            });
        });
        
        // Add player button
        document.getElementById('addPlayerBtn').addEventListener('click', () => {
            this.addPlayerInput();
        });
    }
    
    handleMatchTypeChange(matchType) {
        const teamConfigSection = document.getElementById('teamConfigSection');
        const playerInputSection = document.getElementById('playerInputSection');
        
        switch(matchType) {
            case 'solo':
                teamConfigSection.classList.add('hidden');
                playerInputSection.querySelector('h3').innerHTML = 
                    '<i class="fas fa-user"></i> Player Details';
                this.resetToSoloMode();
                break;
                
            case 'duo':
                teamConfigSection.classList.remove('hidden');
                playerInputSection.querySelector('h3').innerHTML = 
                    '<i class="fas fa-user-friends"></i> Team Details';
                this.setupDuoTeams();
                break;
                
            case 'squad':
                teamConfigSection.classList.remove('hidden');
                playerInputSection.querySelector('h3').innerHTML = 
                    '<i class="fas fa-users"></i> Team Details';
                this.setupSquadTeams();
                break;
        }
    }
    
    resetToSoloMode() {
        // Keep only one player input for solo
        const playerInputs = document.getElementById('playerInputs');
        playerInputs.innerHTML = this.createPlayerInput(0);
        this.playerCount = 1;
        document.getElementById('addPlayerBtn').style.display = 'block';
    }
    
    setupDuoTeams() {
        const teamInputs = document.getElementById('teamInputs');
        const playerInputs = document.getElementById('playerInputs');
        
        // Setup team inputs
        teamInputs.innerHTML = `
            <div class="team-config">
                <p>Duo matches require teams of 2 players each.</p>
                <div class="team-count-selector">
                    <label>Number of Teams:</label>
                    <select id="teamCount">
                        <option value="5">5 Teams (10 players)</option>
                        <option value="4">4 Teams (8 players)</option>
                        <option value="3">3 Teams (6 players)</option>
                    </select>
                </div>
                <button class="btn-secondary" onclick="predictor.generateTeamInputs()">
                    <i class="fas fa-users"></i> Generate Team Inputs
                </button>
            </div>
        `;
        
        // Hide add player button for duo/squad
        document.getElementById('addPlayerBtn').style.display = 'none';
    }
    
    setupSquadTeams() {
        const teamInputs = document.getElementById('teamInputs');
        
        teamInputs.innerHTML = `
            <div class="team-config">
                <p>Squad matches require teams of 4 players each.</p>
                <div class="team-count-selector">
                    <label>Number of Teams:</label>
                    <select id="teamCount">
                        <option value="5">5 Teams (20 players)</option>
                        <option value="4">4 Teams (16 players)</option>
                        <option value="3">3 Teams (12 players)</option>
                    </select>
                </div>
                <button class="btn-secondary" onclick="predictor.generateTeamInputs()">
                    <i class="fas fa-users"></i> Generate Team Inputs
                </button>
            </div>
        `;
        
        document.getElementById('addPlayerBtn').style.display = 'none';
    }
    
    generateTeamInputs() {
        const matchType = document.querySelector('input[name="matchType"]:checked').value;
        const teamCount = parseInt(document.getElementById('teamCount').value);
        const playerInputs = document.getElementById('playerInputs');
        
        let playersPerTeam = matchType === 'duo' ? 2 : 4;
        let totalPlayers = teamCount * playersPerTeam;
        
        playerInputs.innerHTML = '';
        
        for (let teamIndex = 0; teamIndex < teamCount; teamIndex++) {
            const teamDiv = document.createElement('div');
            teamDiv.className = 'team-section';
            teamDiv.innerHTML = `<h4>Team ${teamIndex + 1}</h4>`;
            
            for (let playerIndex = 0; playerIndex < playersPerTeam; playerIndex++) {
                const playerInput = this.createTeamPlayerInput(teamIndex, playerIndex, playersPerTeam);
                teamDiv.appendChild(playerInput);
            }
            
            playerInputs.appendChild(teamDiv);
        }
    }
    
    createTeamPlayerInput(teamIndex, playerIndex, totalPlayers) {
        const div = document.createElement('div');
        div.className = 'team-player-input';
        div.innerHTML = `
            <div class="input-row">
                <div class="input-group">
                    <label>Player ${playerIndex + 1} Name</label>
                    <input type="text" 
                           data-team="${teamIndex}" 
                           data-player="${playerIndex}"
                           class="team-player-name"
                           placeholder="Enter player name">
                </div>
                <div class="input-group">
                    <label>Kills</label>
                    <input type="number" 
                           data-team="${teamIndex}" 
                           data-player="${playerIndex}"
                           class="team-player-kills"
                           placeholder="0" min="0">
                </div>
                <div class="input-group">
                    <label>Damage</label>
                    <input type="number" 
                           data-team="${teamIndex}" 
                           data-player="${playerIndex}"
                           class="team-player-damage"
                           placeholder="0" min="0">
                </div>
                <div class="input-group">
                    <label>Survival Time</label>
                    <input type="number" 
                           data-team="${teamIndex}" 
                           data-player="${playerIndex}"
                           class="team-player-survival"
                           placeholder="0" min="0">
                </div>
            </div>
        `;
        return div;
    }
    
    addPlayerInput() {
        const playerInputs = document.getElementById('playerInputs');
        const newInput = this.createPlayerInput(this.playerCount);
        playerInputs.appendChild(newInput);
        this.playerCount++;
    }
    
    createPlayerInput(index) {
        return `
            <div class="player-input" data-index="${index}">
                <div class="input-row">
                    <div class="input-group">
                        <label>Player ${index + 1} Name</label>
                        <input type="text" class="player-name" placeholder="Enter player name">
                    </div>
                    <div class="input-group">
                        <label>Kills</label>
                        <input type="number" class="player-kills" placeholder="0" min="0">
                    </div>
                    <div class="input-group">
                        <label>Damage</label>
                        <input type="number" class="player-damage" placeholder="0" min="0">
                    </div>
                    <div class="input-group">
                        <label>Survival Time (sec)</label>
                        <input type="number" class="player-survival" placeholder="0" min="0">
                    </div>
                    <div class="input-group">
                        <label>Headshots</label>
                        <input type="number" class="player-headshots" placeholder="0" min="0">
                    </div>
                </div>
                ${index > 0 ? '<button class="remove-player" onclick="predictor.removePlayerInput(' + index + ')"><i class="fas fa-times"></i></button>' : ''}
            </div>
        `;
    }
    
    removePlayerInput(index) {
        const element = document.querySelector(`.player-input[data-index="${index}"]`);
        if (element) element.remove();
    }
    
    async handleFileUpload() {
        // Existing code...
        
        // Get player/team data based on match type
        const matchType = document.querySelector('input[name="matchType"]:checked').value;
        const playerData = this.collectPlayerData(matchType);
        
        if (matchType !== 'solo' && !this.validateTeams(matchType)) {
            this.showError(`Please ensure all teams have complete data for ${matchType} mode.`);
            return;
        }
        
        // Add player data to formData
        formData.append('playerData', JSON.stringify(playerData));
        
        // Continue with upload...
    }
    
    collectPlayerData(matchType) {
        const data = {
            matchType: matchType,
            players: []
        };
        
        if (matchType === 'solo') {
            // Collect solo player data
            const playerInputs = document.querySelectorAll('.player-input');
            playerInputs.forEach(input => {
                const player = {
                    name: input.querySelector('.player-name').value || `Player_${Math.random().toString(36).substr(2, 5)}`,
                    kills: parseInt(input.querySelector('.player-kills').value) || 0,
                    damage: parseInt(input.querySelector('.player-damage').value) || 0,
                    survival: parseInt(input.querySelector('.player-survival').value) || 0,
                    headshots: parseInt(input.querySelector('.player-headshots')?.value) || 0
                };
                data.players.push(player);
            });
        } else {
            // Collect team data
            data.teams = this.collectTeamData(matchType);
        }
        
        return data;
    }
    
    collectTeamData(matchType) {
        const teams = [];
        const teamSections = document.querySelectorAll('.team-section');
        
        teamSections.forEach((section, teamIndex) => {
            const team = {
                name: `Team ${teamIndex + 1}`,
                players: []
            };
            
            const playerInputs = section.querySelectorAll('.team-player-input');
            playerInputs.forEach((input, playerIndex) => {
                const player = {
                    name: input.querySelector('.team-player-name').value || `Player_${playerIndex + 1}`,
                    kills: parseInt(input.querySelector('.team-player-kills').value) || 0,
                    damage: parseInt(input.querySelector('.team-player-damage').value) || 0,
                    survival: parseInt(input.querySelector('.team-player-survival').value) || 0
                };
                team.players.push(player);
            });
            
            // Calculate team stats
            team.totalKills = team.players.reduce((sum, p) => sum + p.kills, 0);
            team.totalDamage = team.players.reduce((sum, p) => sum + p.damage, 0);
            team.avgSurvival = team.players.reduce((sum, p) => sum + p.survival, 0) / team.players.length;
            team.coordinationScore = this.calculateTeamCoordination(team);
            
            teams.push(team);
        });
        
        return teams;
    }
    
    calculateTeamCoordination(team) {
        // Calculate team coordination based on stats variance
        const kills = team.players.map(p => p.kills);
        const avgKills = kills.reduce((a, b) => a + b) / kills.length;
        const variance = kills.reduce((a, b) => a + Math.pow(b - avgKills, 2), 0) / kills.length;
        
        // Lower variance = better coordination
        return Math.max(0, 100 - (variance * 10));
    }
    
    validateTeams(matchType) {
        const playersPerTeam = matchType === 'duo' ? 2 : 4;
        const teamSections = document.querySelectorAll('.team-section');
        
        if (teamSections.length === 0) return false;
        
        for (const section of teamSections) {
            const playerInputs = section.querySelectorAll('.team-player-input');
            if (playerInputs.length !== playersPerTeam) return false;
            
            // Check if all required fields are filled
            for (const input of playerInputs) {
                const name = input.querySelector('.team-player-name').value;
                const kills = input.querySelector('.team-player-kills').value;
                if (!name || kills === '') return false;
            }
        }
        
        return true;
    }
}

// Updated displayResults function
function displayResults(data) {
    const prediction = data.prediction;
    const matchType = document.querySelector('input[name="matchType"]:checked').value;
    
    // Update winner information based on match type
    if (matchType === 'solo') {
        document.getElementById('winnerName').textContent = prediction.winner.name;
        document.getElementById('winnerName').innerHTML = `
            <span class="winner-tag">Solo Winner</span><br>
            ${prediction.winner.name}
        `;
    } else {
        // Display team as winner
        document.getElementById('winnerName').textContent = prediction.winningTeam.name;
        document.getElementById('winnerName').innerHTML = `
            <span class="winner-tag">${matchType.charAt(0).toUpperCase() + matchType.slice(1)} Winner</span><br>
            ${prediction.winningTeam.name}
        `;
        
        // Show team members if team won
        this.displayTeamMembers(prediction.winningTeam);
    }
    
    // Rest of the function...
}

function displayTeamMembers(team) {
    const winnerSection = document.querySelector('.winner-section');
    
    // Add team members list
    const teamMembersDiv = document.createElement('div');
    teamMembersDiv.className = 'team-members';
    teamMembersDiv.innerHTML = `
        <h5>Team Members:</h5>
        <div class="member-list">
            ${team.players.map(player => `
                <div class="member-item">
                    <span class="member-name">${player.name}</span>
                    <span class="member-stats">
                        K:${player.kills} | D:${player.damage} | S:${player.survival}s
                    </span>
                </div>
            `).join('')}
        </div>
    `;
    
    winnerSection.querySelector('.winner-card').appendChild(teamMembersDiv);
}