// js/ocr_prediction.js
// Complete OCR + Prediction Frontend Logic

class INFIKNIGHT_Predictor {
    
    constructor() {
        this.apiUrl = 'prediction/api/ocr_prediction_api.php';
        this.matchType = 'solo';
        this.players = [];
        this.teams = [];
        this.prediction = null;
        this.sessionId = null;
        this.processedPlayers = 0;
        
        this.initializeEventListeners();
        this.updateUI();
    }
    
    /**
     * Initialize all event listeners
     */
    initializeEventListeners() {
        // Method tabs
        document.querySelectorAll('.method-tab').forEach(tab => {
            tab.addEventListener('click', (e) => this.switchMethod(e));
        });
        
        // Match type selection
        document.querySelectorAll('input[name="matchType"]').forEach(radio => {
            radio.addEventListener('change', (e) => this.onMatchTypeChange(e));
        });
        
        // Upload zone
        const uploadZone = document.getElementById('uploadZone');
        const browseBtn = document.getElementById('browseBtn');
        const fileInput = document.getElementById('statsFile');
        
        if (uploadZone) {
            uploadZone.addEventListener('click', () => fileInput.click());
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('highlight');
            });
            uploadZone.addEventListener('dragleave', () => {
                uploadZone.classList.remove('highlight');
            });
            uploadZone.addEventListener('drop', (e) => this.handleDrop(e));
        }
        
        if (browseBtn) {
            browseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                fileInput.click();
            });
        }
        
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }
        
        // Team generation
        const generateTeams = document.getElementById('generateTeams');
        if (generateTeams) {
            generateTeams.addEventListener('click', () => this.generateTeamInputs());
        }
        
        // Process button
        const processBtn = document.getElementById('processBtn');
        if (processBtn) {
            processBtn.addEventListener('click', () => this.processPrediction());
        }
        
        // Add player button
        const addPlayerBtn = document.getElementById('addPlayerBtn');
        if (addPlayerBtn) {
            addPlayerBtn.addEventListener('click', () => this.addPlayerInput());
        }
        
        // Team count change
        const teamCount = document.getElementById('teamCount');
        if (teamCount) {
            teamCount.addEventListener('change', () => this.generateTeamInputs());
        }
        
        // New prediction button
        const newPrediction = document.getElementById('newPrediction');
        if (newPrediction) {
            newPrediction.addEventListener('click', () => this.resetUI());
        }
        
        // Retry button
        const retryBtn = document.getElementById('retryButton');
        if (retryBtn) {
            retryBtn.addEventListener('click', () => this.processPrediction());
        }
    }
    
    /**
     * Switch between input methods
     */
    switchMethod(event) {
        const tab = event.currentTarget;
        const method = tab.dataset.method;
        
        // Update tabs
        document.querySelectorAll('.method-tab').forEach(t => {
            t.classList.remove('active');
        });
        tab.classList.add('active');
        
        // Update content
        document.querySelectorAll('.method-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(method + 'Method').classList.add('active');
        
        // Reset upload zone if switching to upload
        if (method === 'upload') {
            this.resetUploadZone();
        }
    }
    
    /**
     * Handle match type change
     */
    onMatchTypeChange(event) {
        this.matchType = event.target.value;
        
        // Update UI based on match type
        const teamConfig = document.getElementById('teamConfig');
        const playerInputs = document.getElementById('playerInputs');
        
        if (this.matchType === 'solo') {
            teamConfig.style.display = 'none';
            playerInputs.style.display = 'block';
        } else {
            teamConfig.style.display = 'block';
            playerInputs.style.display = 'none';
            this.generateTeamInputs();
        }
    }
    
    /**
     * Handle file drop
     */
    handleDrop(event) {
        event.preventDefault();
        const uploadZone = document.getElementById('uploadZone');
        uploadZone.classList.remove('highlight');
        
        const files = event.dataTransfer.files;
        this.processUploadedFiles(files);
    }
    
    /**
     * Handle file select
     */
    handleFileSelect(event) {
        const files = event.target.files;
        this.processUploadedFiles(files);
    }
    
    /**
     * Process uploaded files based on match type
     */
    async processUploadedFiles(files) {
        const uploadZone = document.getElementById('uploadZone');
        const fileCount = files.length;
        
        // Validate minimum players
        let requiredPlayers = 2; // Solo: at least 2 players
        if (this.matchType === 'duo') requiredPlayers = 4;
        if (this.matchType === 'squad') requiredPlayers = 8;
        
        if (fileCount < requiredPlayers) {
            this.showNotification(
                `${this.matchType.toUpperCase()} match requires at least ${requiredPlayers} players (${fileCount} uploaded)`,
                'error'
            );
            return;
        }
        
        // Show processing state
        uploadZone.innerHTML = `
            <div class="upload-icon">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <h3>Processing ${fileCount} Player Images...</h3>
            <p class="upload-hint">Extracting stats via OCR.space</p>
            <div class="progress-container" style="margin-top: 20px;">
                <div class="progress-bar" id="ocrProgress" style="width: 0%"></div>
            </div>
        `;
        
        // Prepare form data
        const formData = new FormData();
        Array.from(files).forEach(file => {
            formData.append('player_images[]', file);
        });
        
        // Add player names
        Array.from(files).forEach((file, index) => {
            formData.append('player_names[]', `Player ${index + 1}`);
        });
        
        // Determine endpoint based on match type
        let endpoint = 'process-solo';
        if (this.matchType === 'duo') endpoint = 'process-duo';
        if (this.matchType === 'squad') endpoint = 'process-squad';
        
        try {
            // Simulate progress
            this.simulateOCRProgress();
            
            const response = await fetch(`${this.apiUrl}?endpoint=${endpoint}`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.sessionId = result.session_id;
                
                if (this.matchType === 'solo') {
                    this.players = result.data.players.filter(p => p.success !== false);
                    this.displayOCRResults(this.players, result.data);
                } else {
                    this.players = result.data.players;
                    this.teams = result.data.teams;
                    this.displayTeamResults(result.data);
                }
                
                // Enable process button
                document.getElementById('processBtn').disabled = false;
                
                this.showNotification(`✅ Processed ${result.data.processed} players successfully`, 'success');
                
            } else {
                this.showNotification('OCR failed: ' + result.error, 'error');
                this.resetUploadZone();
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error processing images: ' + error.message, 'error');
            this.resetUploadZone();
        }
    }
    
    /**
     * Simulate OCR progress for better UX
     */
    simulateOCRProgress() {
        let progress = 0;
        const interval = setInterval(() => {
            progress += 5;
            const progressBar = document.getElementById('ocrProgress');
            if (progressBar) {
                progressBar.style.width = progress + '%';
            }
            if (progress >= 90) {
                clearInterval(interval);
            }
        }, 100);
        
        // Store interval to clear later
        this.ocrProgressInterval = interval;
    }
    
    /**
     * Display OCR results for solo mode
     */
    displayOCRResults(players, data) {
        const uploadZone = document.getElementById('uploadZone');
        const ocrPreview = document.getElementById('ocrPreview');
        
        // Clear OCR progress interval
        if (this.ocrProgressInterval) {
            clearInterval(this.ocrProgressInterval);
        }
        
        // Build OCR results HTML
        let html = `
            <div class="ocr-results">
                <div class="ocr-header">
                    <h4><i class="fas fa-check-circle" style="color: #06d6a0;"></i> OCR Extraction Complete</h4>
                    <div class="ocr-meta">
                        <span class="meta-item">
                            <i class="fas fa-users"></i> ${data.processed} Players
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-robot"></i> OCR.space Engine 3
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-check-circle"></i> ${data.total_players - data.failed} Successful
                        </span>
                    </div>
                </div>
        `;
        
        // Show confidence display
        const avgConfidence = players.reduce((sum, p) => sum + (p.ocr_confidence || 0), 0) / players.length;
        const confidenceClass = avgConfidence > 80 ? 'high' : (avgConfidence > 60 ? 'medium' : 'low');
        
        html += `
            <div class="confidence-display">
                <div class="confidence-label">OCR Confidence</div>
                <div class="confidence-value ${confidenceClass}">${Math.round(avgConfidence)}%</div>
                <div class="confidence-bar">
                    <div class="confidence-fill" style="width: ${avgConfidence}%"></div>
                </div>
            </div>
        `;
        
        // Player stats cards
        html += `<div class="career-stats-grid">`;
        
        players.forEach((player, index) => {
            const stats = player.stats || {};
            const powerScore = player.power_score || 0;
            const confidence = player.ocr_confidence || 0;
            
            html += `
                <div class="stat-card" style="border-left: 4px solid ${powerScore > 70 ? '#06d6a0' : '#ffd60a'}">
                    <div class="stat-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">${player.name}</div>
                        <div class="stat-value">PS: ${powerScore}</div>
                        <div style="display: flex; gap: 8px; margin-top: 4px; font-size: 0.75rem;">
                            <span>K/D: ${stats.kd_ratio || 'N/A'}</span>
                            <span>Win: ${stats.win_ratio || 'N/A'}%</span>
                        </div>
                        <div style="font-size: 0.7rem; color: #64748b;">
                            OCR: ${Math.round(confidence)}%
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `</div>`;
        
        // Raw text preview (first player)
        if (players.length > 0 && players[0].ocr_text_preview) {
            html += `
                <div class="raw-text-preview">
                    <h5><i class="fas fa-code"></i> Extracted Text Preview</h5>
                    <div class="text-preview">
                        ${players[0].ocr_text_preview}
                    </div>
                </div>
            `;
        }
        
        // OCR Actions
        html += `
            <div class="ocr-actions">
                <button class="btn btn-success" onclick="predictor.processPrediction()">
                    <i class="fas fa-bolt"></i> Generate AI Prediction
                </button>
                <button class="btn btn-outline-secondary" onclick="predictor.resetUploadZone()">
                    <i class="fas fa-redo"></i> Upload Different Images
                </button>
            </div>
        </div>`;
        
        uploadZone.style.display = 'none';
        ocrPreview.innerHTML = html;
        ocrPreview.classList.remove('hidden');
    }
    
    /**
     * Display team results for duo/squad mode
     */
    displayTeamResults(data) {
        const uploadZone = document.getElementById('uploadZone');
        const ocrPreview = document.getElementById('ocrPreview');
        
        if (this.ocrProgressInterval) {
            clearInterval(this.ocrProgressInterval);
        }
        
        let html = `
            <div class="ocr-results">
                <div class="ocr-header">
                    <h4><i class="fas fa-check-circle" style="color: #06d6a0;"></i> Teams Formed Successfully</h4>
                    <div class="ocr-meta">
                        <span class="meta-item">
                            <i class="fas fa-users"></i> ${data.total_players} Players
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-people-arrows"></i> ${data.total_teams} Teams
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-user-plus"></i> ${data.players_per_team}/Team
                        </span>
                    </div>
                </div>
        `;
        
        // Teams grid
        html += `<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">`;
        
        data.teams.forEach((team, index) => {
            const medalClass = index === 0 ? 'medal-gold' : (index === 1 ? 'medal-silver' : 'medal-bronze');
            
            html += `
                <div class="team-performance-card ${medalClass}" style="padding: 16px;">
                    <div class="team-rank">#${index + 1}</div>
                    <div class="team-name">${team.name}</div>
                    <div class="team-stats">
                        <span><i class="fas fa-bolt"></i> Power Score: ${team.power_score}</span>
                        <span><i class="fas fa-handshake"></i> Synergy: ${team.synergy}x</span>
                        <span><i class="fas fa-users"></i> Players: ${team.player_count}</span>
                    </div>
                    <hr style="margin: 10px 0; opacity: 0.2;">
                    <div style="font-size: 0.85rem;">
                        ${team.players.map(p => `
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <span>${p.name}</span>
                                <span style="font-weight: bold;">PS: ${p.power_score}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        });
        
        html += `</div>`;
        
        // OCR Actions
        html += `
            <div class="ocr-actions" style="margin-top: 30px;">
                <button class="btn btn-success" onclick="predictor.processPrediction()">
                    <i class="fas fa-bolt"></i> Predict Winner
                </button>
                <button class="btn btn-outline-secondary" onclick="predictor.resetUploadZone()">
                    <i class="fas fa-redo"></i> Upload Different Images
                </button>
            </div>
        </div>`;
        
        uploadZone.style.display = 'none';
        ocrPreview.innerHTML = html;
        ocrPreview.classList.remove('hidden');
    }
    
    /**
     * Process prediction (after OCR)
     */
    async processPrediction() {
        // Show processing section
        document.getElementById('processingSection').classList.remove('hidden');
        document.getElementById('resultsSection').classList.add('hidden');
        document.getElementById('errorSection').classList.add('hidden');
        
        // Start progress simulation
        this.simulateProcessingSteps();
        
        try {
            const response = await fetch(`${this.apiUrl}?endpoint=predict`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.prediction = result.prediction;
                this.displayPredictionResults(result.prediction);
                
                // Hide processing, show results
                document.getElementById('processingSection').classList.add('hidden');
                document.getElementById('resultsSection').classList.remove('hidden');
                document.getElementById('newPredictionSection').classList.remove('hidden');
                
            } else {
                throw new Error(result.error);
            }
            
        } catch (error) {
            console.error('Prediction error:', error);
            
            document.getElementById('processingSection').classList.add('hidden');
            document.getElementById('errorSection').classList.remove('hidden');
            document.getElementById('errorMessage').textContent = error.message;
        }
    }
    
    /**
     * Display prediction results in UI
     */
    displayPredictionResults(prediction) {
        const winner = prediction.winner;
        
        // Update winner card
        document.getElementById('winnerName').textContent = winner.team_name;
        document.getElementById('confidenceValueMain').textContent = prediction.confidence + '%';
        
        // Update confidence badge color
        const confidenceBadge = document.getElementById('confidenceBadge');
        if (prediction.confidence > 80) {
            confidenceBadge.style.background = 'linear-gradient(135deg, #06d6a0, #059669)';
        } else if (prediction.confidence > 60) {
            confidenceBadge.style.background = 'linear-gradient(135deg, #ffd60a, #f39c12)';
        } else {
            confidenceBadge.style.background = 'linear-gradient(135deg, #ef476f, #d62828)';
        }
        
        // Update winner stats
        document.getElementById('winnerKills').textContent = winner.players[0]?.kd || '0';
        document.getElementById('winnerDamage').textContent = winner.players[0]?.damage || '0';
        document.getElementById('winnerSurvival').textContent = '15m'; // Default
        document.getElementById('winnerRating').textContent = (winner.power_score / 10).toFixed(1);
        
        // Update top performer
        const allPlayers = prediction.all_teams.flatMap(t => t.players);
        const topPerformer = allPlayers.sort((a, b) => b.power_score - a.power_score)[0];
        
        if (topPerformer) {
            document.getElementById('topPerformerName').textContent = topPerformer.name;
            document.getElementById('topPerformerKD').textContent = topPerformer.kd || '0.0';
            document.getElementById('topPerformerDMG').textContent = topPerformer.damage || '0';
            document.getElementById('topPerformerRating').textContent = (topPerformer.power_score / 10).toFixed(1);
        }
        
        // Update weak link
        const weakLink = allPlayers.sort((a, b) => a.power_score - b.power_score)[0];
        
        if (weakLink) {
            document.getElementById('weakLinkName').textContent = weakLink.name;
            document.getElementById('weakLinkKD').textContent = weakLink.kd || '0.0';
            document.getElementById('weakLinkDMG').textContent = weakLink.damage || '0';
            document.getElementById('weakLinkRating').textContent = (weakLink.power_score / 10).toFixed(1);
        }
        
        // Update synergy
        const avgSynergy = prediction.all_teams.reduce((sum, t) => sum + t.synergy, 0) / prediction.all_teams.length;
        document.getElementById('synergyScore').textContent = (avgSynergy * 8).toFixed(1);
        document.getElementById('synergyMeter').style.width = (avgSynergy * 80) + '%';
        document.getElementById('synergyDesc').textContent = 
            avgSynergy > 1.2 ? 'Excellent coordination' : 
            avgSynergy > 1.0 ? 'Good synergy' : 'Needs improvement';
        
        // Update prediction accuracy
        document.getElementById('predictionAccuracy').textContent = prediction.confidence + '%';
        document.getElementById('predictionDesc').textContent = 
            prediction.confidence > 80 ? 'Very confident prediction' :
            prediction.confidence > 60 ? 'Moderate confidence' : 'Low confidence';
        
        // Update team performance grid for team modes
        if (this.matchType !== 'solo' && prediction.all_teams.length > 0) {
            this.displayTeamPerformance(prediction.all_teams);
        }
    }
    
    /**
     * Display team performance grid
     */
    displayTeamPerformance(teams) {
        const grid = document.getElementById('teamPerformanceGrid');
        grid.innerHTML = '';
        
        teams.sort((a, b) => b.power_score - a.power_score).forEach((team, index) => {
            const medalClass = index === 0 ? 'medal-gold' : (index === 1 ? 'medal-silver' : 'medal-bronze');
            
            const card = document.createElement('div');
            card.className = `team-performance-card ${medalClass}`;
            card.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 1.5rem; font-weight: 800;">#${index + 1}</span>
                    <span style="background: rgba(0,0,0,0.2); padding: 4px 8px; border-radius: 12px;">
                        ${team.win_probability}%
                    </span>
                </div>
                <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 4px;">${team.team_name}</div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Power Score</span>
                    <span style="font-weight: 700;">${team.power_score}</span>
                </div>
                <div style="width: 100%; height: 6px; background: rgba(0,0,0,0.1); border-radius: 3px;">
                    <div style="width: ${team.win_probability}%; height: 100%; background: ${index === 0 ? '#f2c94c' : '#94a3b8'}; border-radius: 3px;"></div>
                </div>
            `;
            
            grid.appendChild(card);
        });
    }
    
    /**
     * Simulate processing steps animation
     */
    simulateProcessingSteps() {
        let currentStep = 1;
        const steps = document.querySelectorAll('.step');
        const progressBar = document.getElementById('progressBar');
        const processingTime = document.getElementById('processingTime');
        const processingDetails = document.getElementById('processingDetails');
        
        let startTime = Date.now();
        let progress = 0;
        
        const interval = setInterval(() => {
            progress += 1;
            progressBar.style.width = progress + '%';
            
            // Update time
            const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
            processingTime.textContent = elapsed + 's';
            
            // Update steps
            if (progress === 25) {
                currentStep = 2;
                this.updateSteps(currentStep);
                processingDetails.innerHTML = '<p>Extracting statistics from player images via OCR.space...</p>';
            } else if (progress === 50) {
                currentStep = 3;
                this.updateSteps(currentStep);
                processingDetails.innerHTML = '<p>Running ML algorithms and calculating power scores...</p>';
            } else if (progress === 75) {
                currentStep = 4;
                this.updateSteps(currentStep);
                processingDetails.innerHTML = '<p>Generating insights and win probability distribution...</p>';
            } else if (progress >= 100) {
                clearInterval(interval);
            }
        }, 50);
        
        this.processingInterval = interval;
    }
    
    /**
     * Update processing steps UI
     */
    updateSteps(currentStep) {
        document.querySelectorAll('.step').forEach(step => {
            step.classList.remove('active');
        });
        
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
    }
    
    /**
     * Generate team input fields (for manual input)
     */
    generateTeamInputs() {
        const teamCount = parseInt(document.getElementById('teamCount').value);
        const container = document.getElementById('teamInputs');
        const playersPerTeam = this.matchType === 'duo' ? 2 : 4;
        
        let html = '';
        
        for (let t = 1; t <= teamCount; t++) {
            html += `
                <div class="team-section">
                    <div class="team-header">
                        <div class="team-header-row">
                            <h4><i class="fas fa-trophy"></i> Team ${t}</h4>
                            <div class="team-name-group">
                                <label>Team Name</label>
                                <input type="text" class="team-name-input" 
                                       placeholder="Enter team name" value="Team ${t}">
                            </div>
                            ${t > 2 ? `<button class="remove-team-btn" onclick="predictor.removeTeam(this)">
                                <i class="fas fa-times"></i> Remove Team
                            </button>` : ''}
                        </div>
                    </div>
                    <div class="team-players-grid">
            `;
            
            for (let p = 1; p <= playersPerTeam; p++) {
                html += `
                    <div class="team-player-input">
                        <h5><i class="fas fa-user"></i> Player ${p}</h5>
                        <div class="input-grid">
                            <div class="input-group">
                                <label>Name</label>
                                <input type="text" class="team-player-name" 
                                       placeholder="Player name" value="Player ${p}">
                            </div>
                            <div class="input-group">
                                <label>Kills</label>
                                <input type="number" class="team-player-kills" 
                                       min="0" max="50" placeholder="0" value="${Math.floor(Math.random() * 10) + 1}">
                            </div>
                            <div class="input-group">
                                <label>Damage</label>
                                <input type="number" class="team-player-damage" 
                                       min="0" max="5000" placeholder="0" value="${Math.floor(Math.random() * 400) + 100}">
                            </div>
                            <div class="input-group">
                                <label>Survival</label>
                                <div class="survival-time-wrapper">
                                    <input type="number" class="team-player-survival-min" 
                                           min="0" max="30" placeholder="MM" value="7">
                                    <span>:</span>
                                    <input type="number" class="team-player-survival-sec" 
                                           min="0" max="59" placeholder="SS" value="30">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            html += `</div></div>`;
        }
        
        container.innerHTML = html;
    }
    
    /**
     * Add player input (for solo mode)
     */
    addPlayerInput() {
        const container = document.getElementById('playerInputs');
        const playerCount = container.children.length + 1;
        
        const div = document.createElement('div');
        div.className = 'player-input-section';
        div.innerHTML = `
            <h4><i class="fas fa-user-circle"></i> Player ${playerCount}</h4>
            <div class="input-grid">
                <div class="input-group">
                    <label>Player Name</label>
                    <input type="text" class="player-name" 
                           placeholder="Enter name" value="Player ${playerCount}">
                </div>
                <div class="input-group">
                    <label>Kills</label>
                    <input type="number" class="player-kills" 
                           min="0" max="50" placeholder="0" value="5">
                </div>
                <div class="input-group">
                    <label>Damage</label>
                    <input type="number" class="player-damage" 
                           min="0" max="5000" placeholder="0" value="250">
                </div>
                <div class="input-group">
                    <label>Survival Time</label>
                    <div class="survival-time-wrapper">
                        <input type="number" class="player-survival-min" 
                               min="0" max="30" placeholder="MM" value="7">
                        <span>:</span>
                        <input type="number" class="player-survival-sec" 
                               min="0" max="59" placeholder="SS" value="30">
                    </div>
                </div>
                <div class="input-group">
                    <label>Headshots</label>
                    <input type="number" class="player-headshots" 
                           min="0" max="50" placeholder="0" value="2">
                </div>
                <div class="input-group">
                    <label>Assists</label>
                    <input type="number" class="player-assists" 
                           min="0" max="20" placeholder="0" value="0">
                </div>
            </div>
            <button class="btn btn-danger btn-sm remove-player" onclick="predictor.removePlayer(this)">
                <i class="fas fa-times"></i> Remove Player
            </button>
        `;
        
        container.appendChild(div);
    }
    
    /**
     * Remove player input
     */
    removePlayer(button) {
        button.closest('.player-input-section').remove();
    }
    
    /**
     * Remove team
     */
    removeTeam(button) {
        button.closest('.team-section').remove();
    }
    
    /**
     * Reset upload zone
     */
    resetUploadZone() {
        const uploadZone = document.getElementById('uploadZone');
        const ocrPreview = document.getElementById('ocrPreview');
        const fileInput = document.getElementById('statsFile');
        
        uploadZone.style.display = 'block';
        ocrPreview.classList.add('hidden');
        ocrPreview.innerHTML = '';
        fileInput.value = '';
        
        uploadZone.innerHTML = `
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <h3>Drop your screenshot here</h3>
            <p class="upload-hint">Supports PNG, JPG up to 5MB</p>
            <button class="btn btn-primary" id="browseBtn">
                <i class="fas fa-folder-open"></i> Browse Files
            </button>
            <p class="ocr-notice">
                <i class="fas fa-robot"></i> AI OCR will extract statistics automatically
            </p>
        `;
        
        // Reattach event listener
        document.getElementById('browseBtn').addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.click();
        });
    }
    
    /**
     * Reset entire UI
     */
    resetUI() {
        this.players = [];
        this.teams = [];
        this.prediction = null;
        
        document.getElementById('resultsSection').classList.add('hidden');
        document.getElementById('newPredictionSection').classList.add('hidden');
        document.getElementById('processingSection').classList.add('hidden');
        document.getElementById('errorSection').classList.add('hidden');
        
        this.resetUploadZone();
        
        // Reset to solo mode
        document.querySelector('input[name="matchType"][value="solo"]').checked = true;
        this.matchType = 'solo';
        this.onMatchTypeChange({ target: { value: 'solo' } });
    }
    
    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} show`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                                type === 'error' ? 'fa-exclamation-circle' : 
                                'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
    
    /**
     * Update UI based on state
     */
    updateUI() {
        // Set default match type to solo
        document.querySelector('input[name="matchType"][value="solo"]').checked = true;
        this.onMatchTypeChange({ target: { value: 'solo' } });
        
        // Disable process button initially
        document.getElementById('processBtn').disabled = true;
    }
}

// Initialize predictor
const predictor = new INFIKNIGHT_Predictor();
window.predictor = predictor;