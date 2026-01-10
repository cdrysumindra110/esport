// // Main Prediction Application
// class InfiknightPredictorApp {
//     constructor() {
//         this.predictor = null;
//         this.currentData = null;
//         this.predictionResults = null;
//         this.charts = {};
//         this.isProcessing = false;
//         this.currentMatchType = 'solo';
//         this.playerCount = 1;
//         this.teamCount = 2;
//         this.processingTimer = null;
//         this.startTime = null;
//         this.csrfToken = document.getElementById('csrfToken')?.value || '';
//     }
    
//     initialize() {
//         this.initializeEventListeners();
//         this.initializeTheme();
//         this.initializeTabs();
//         this.initializeMatchType();
//         this.loadRecentPredictions();
//         this.initializeCharts();
        
//         console.log('Infiknight AI Prediction System v2.0 initialized');
//     }
    
//     initializeEventListeners() {
//         // File upload
//         const uploadZone = document.getElementById('uploadZone');
//         const browseBtn = document.getElementById('browseBtn');
//         const statsFile = document.getElementById('statsFile');
        
//         if (uploadZone) {
//             uploadZone.addEventListener('dragover', this.handleDragOver.bind(this));
//             uploadZone.addEventListener('dragleave', this.handleDragLeave.bind(this));
//             uploadZone.addEventListener('drop', this.handleDrop.bind(this));
//         }
        
//         if (browseBtn) {
//             browseBtn.addEventListener('click', () => statsFile.click());
//         }
        
//         if (statsFile) {
//             statsFile.addEventListener('change', this.handleFileSelect.bind(this));
//         }
        
//         // Method tabs
//         document.querySelectorAll('.method-tab').forEach(tab => {
//             tab.addEventListener('click', this.switchMethod.bind(this));
//         });
        
//         // Match type
//         document.querySelectorAll('input[name="matchType"]').forEach(radio => {
//             radio.addEventListener('change', this.handleMatchTypeChange.bind(this));
//         });
        
//         // Team configuration
//         const teamCountEl = document.getElementById('teamCount');
//         if (teamCountEl) {
//             teamCountEl.addEventListener('change', this.handleTeamCountChange.bind(this));
//         }
        
//         const generateTeamsBtn = document.getElementById('generateTeams');
//         if (generateTeamsBtn) {
//             generateTeamsBtn.addEventListener('click', this.generateTeams.bind(this));
//         }
        
//         // Player management
//         const addPlayerBtn = document.getElementById('addPlayerBtn');
//         if (addPlayerBtn) {
//             addPlayerBtn.addEventListener('click', this.addPlayerInput.bind(this));
//         }
        
//         // Process button
//         const processBtn = document.getElementById('processBtn');
//         if (processBtn) {
//             processBtn.addEventListener('click', this.processData.bind(this));
//         }
        
//         // Action buttons
//         document.getElementById('exportTable')?.addEventListener('click', this.exportTable.bind(this));
//         document.getElementById('savePrediction')?.addEventListener('click', this.savePrediction.bind(this));
//         document.getElementById('newPrediction')?.addEventListener('click', this.resetForm.bind(this));
//         document.getElementById('retryButton')?.addEventListener('click', this.retryProcessing.bind(this));
        
//         // Theme toggle
//         document.getElementById('themeToggle')?.addEventListener('click', this.toggleTheme.bind(this));
        
//         // Import API
//         document.getElementById('importApi')?.addEventListener('click', this.importFromAPI.bind(this));
        
//         // Window events
//         window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));
//     }
    
//     initializeTheme() {
//         const savedTheme = localStorage.getItem('theme') || 'light';
//         document.documentElement.setAttribute('data-theme', savedTheme);
//         this.updateThemeIcon(savedTheme);
//     }
    
//     updateThemeIcon(theme) {
//         const icon = document.querySelector('#themeToggle i');
//         if (icon) {
//             icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
//         }
//     }
    
//     toggleTheme() {
//         const currentTheme = document.documentElement.getAttribute('data-theme');
//         const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
//         document.documentElement.setAttribute('data-theme', newTheme);
//         localStorage.setItem('theme', newTheme);
//         this.updateThemeIcon(newTheme);
//     }
    
//     initializeTabs() {
//         const tabs = document.querySelectorAll('.method-tab');
//         const contents = document.querySelectorAll('.method-content');
        
//         tabs.forEach(tab => {
//             tab.addEventListener('click', () => {
//                 const method = tab.dataset.method;
                
//                 // Update active tab
//                 tabs.forEach(t => t.classList.remove('active'));
//                 tab.classList.add('active');
                
//                 // Show corresponding content
//                 contents.forEach(content => {
//                     content.classList.remove('active');
//                     if (content.id === `${method}Method`) {
//                         content.classList.add('active');
//                     }
//                 });
                
//                 // Handle match type for manual input
//                 if (method === 'manual') {
//                     this.handleMatchTypeChange();
//                 }
//             });
//         });
//     }
    
//     initializeMatchType() {
//         this.currentMatchType = document.querySelector('input[name="matchType"]:checked')?.value || 'solo';
//         this.updateInputMode();
//     }
    
//     handleMatchTypeChange(e) {
//         if (e && e.target) {
//             this.currentMatchType = e.target.value;
//         } else {
//             this.currentMatchType = document.querySelector('input[name="matchType"]:checked')?.value || 'solo';
//         }
//         this.updateInputMode();
//     }
    
//     updateInputMode() {
//         const teamConfig = document.getElementById('teamConfig');
//         const playerInputs = document.getElementById('playerInputs');
//         const addPlayerBtn = document.getElementById('addPlayerBtn');
        
//         if (this.currentMatchType === 'solo') {
//             if (teamConfig) teamConfig.classList.add('hidden');
//             if (addPlayerBtn) addPlayerBtn.style.display = 'block';
            
//             if (playerInputs) {
//                 // Keep existing player inputs or create default
//                 if (playerInputs.children.length === 0) {
//                     playerInputs.innerHTML = this.createPlayerInput(0);
//                     this.playerCount = 1;
//                 }
//             }
//         } else {
//             if (teamConfig) teamConfig.classList.remove('hidden');
//             if (addPlayerBtn) addPlayerBtn.style.display = 'none';
//             this.generateTeams();
//         }
//     }
    
//     handleTeamCountChange() {
//         this.generateTeams();
//     }
    
//     generateTeams() {
//         const teamInputs = document.getElementById('teamInputs');
//         if (!teamInputs) return;
        
//         const teamCount = parseInt(document.getElementById('teamCount')?.value || 2);
//         const playersPerTeam = this.currentMatchType === 'duo' ? 2 : 4;
        
//         teamInputs.innerHTML = '';
        
//         for (let i = 0; i < teamCount; i++) {
//             const teamDiv = document.createElement('div');
//             teamDiv.className = 'team-section';
//             teamDiv.innerHTML = `<h4>Team ${i + 1}</h4>`;
            
//             for (let j = 0; j < playersPerTeam; j++) {
//                 const playerInput = this.createTeamPlayerInput(i, j);
//                 teamDiv.appendChild(playerInput);
//             }
            
//             teamInputs.appendChild(teamDiv);
//         }
//     }
    
//     createTeamPlayerInput(teamIndex, playerIndex) {
//         const div = document.createElement('div');
//         div.className = 'team-player-input';
//         div.innerHTML = `
//             <div class="input-grid">
//                 <div class="input-group">
//                     <label>Player ${playerIndex + 1} Name</label>
//                     <input type="text" class="form-control team-player-name" 
//                            placeholder="Enter player name" value="Player ${teamIndex * 4 + playerIndex + 1}" required>
//                 </div>
//                 <div class="input-group">
//                     <label>Kills</label>
//                     <input type="number" class="form-control team-player-kills" 
//                            min="0" max="50" value="${Math.floor(Math.random() * 10) + 2}" required>
//                 </div>
//                 <div class="input-group">
//                     <label>Damage</label>
//                     <input type="number" class="form-control team-player-damage" 
//                            min="0" max="5000" value="${Math.floor(Math.random() * 400) + 100}" required>
//                 </div>
//                 <div class="input-group">
//                     <label>Survival (sec)</label>
//                     <input type="number" class="form-control team-player-survival" 
//                            min="0" max="1800" value="${Math.floor(Math.random() * 600) + 300}" required>
//                 </div>
//                 <div class="input-group">
//                     <label>Headshots</label>
//                     <input type="number" class="form-control team-player-headshots" 
//                            min="0" max="50" value="${Math.floor(Math.random() * 5)}">
//                 </div>
//                 <div class="input-group">
//                     <label>Assists</label>
//                     <input type="number" class="form-control team-player-assists" 
//                            min="0" max="20" value="${Math.floor(Math.random() * 4)}">
//                 </div>
//             </div>
//         `;
//         return div;
//     }
    
//     createPlayerInput(index) {
//         const div = document.createElement('div');
//         div.className = 'player-input-section';
//         div.innerHTML = `
//             <h4><i class="fas fa-user"></i> Player ${index + 1}</h4>
//             <div class="input-grid">
//                 <div class="input-group">
//                     <label>Player Name</label>
//                     <input type="text" class="form-control player-name" 
//                            placeholder="Enter player name" value="Player ${index + 1}" required>
//                 </div>
//                 <div class="input-group">
//                     <label>Kills</label>
//                     <input type="number" class="form-control player-kills" 
//                            min="0" max="50" value="${Math.floor(Math.random() * 10) + 2}" required>
//                 </div>
//                 <div class="input-group">
//                     <label>Damage</label>
//                     <input type="number" class="form-control player-damage" 
//                            min="0" max="5000" value="${Math.floor(Math.random() * 400) + 100}" required>
//                 </div>
//                 <div class="input-group">
//                     <label>Survival (sec)</label>
//                     <input type="number" class="form-control player-survival" 
//                            min="0" max="1800" value="${Math.floor(Math.random() * 600) + 300}" required>
//                 </div>
//                 <div class="input-group">
//                     <label>Headshots</label>
//                     <input type="number" class="form-control player-headshots" 
//                            min="0" max="50" value="${Math.floor(Math.random() * 5)}">
//                 </div>
//                 <div class="input-group">
//                     <label>Assists</label>
//                     <input type="number" class="form-control player-assists" 
//                            min="0" max="20" value="${Math.floor(Math.random() * 4)}">
//                 </div>
//             </div>
//             ${index > 0 ? '<button class="btn btn-danger btn-sm remove-player" data-index="' + index + '"><i class="fas fa-times"></i> Remove</button>' : ''}
//         `;
        
//         return div;
//     }
    
//     addPlayerInput() {
//         const playerInputs = document.getElementById('playerInputs');
//         if (!playerInputs) return;
        
//         const newInput = this.createPlayerInput(this.playerCount);
//         playerInputs.appendChild(newInput);
//         this.playerCount++;
        
//         // Attach event listener to remove button
//         const removeBtn = newInput.querySelector('.remove-player');
//         if (removeBtn) {
//             removeBtn.addEventListener('click', (e) => {
//                 this.removePlayerInput(e.target.closest('.player-input-section'));
//             });
//         }
//     }
    
//     removePlayerInput(element) {
//         if (this.playerCount > 1 && element) {
//             element.remove();
//             this.playerCount--;
            
//             // Renumber remaining players
//             const sections = document.querySelectorAll('.player-input-section');
//             sections.forEach((section, index) => {
//                 const h4 = section.querySelector('h4');
//                 if (h4) {
//                     h4.innerHTML = `<i class="fas fa-user"></i> Player ${index + 1}`;
//                 }
//             });
//         }
//     }
    
//     switchMethod(e) {
//         const method = e.currentTarget.dataset.method;
//         const tabs = document.querySelectorAll('.method-tab');
//         const contents = document.querySelectorAll('.method-content');
        
//         // Update active tab
//         tabs.forEach(t => t.classList.remove('active'));
//         e.currentTarget.classList.add('active');
        
//         // Show corresponding content
//         contents.forEach(content => {
//             content.classList.remove('active');
//             if (content.id === `${method}Method`) {
//                 content.classList.add('active');
//             }
//         });
//     }
    
//     handleDragOver(e) {
//         e.preventDefault();
//         e.currentTarget.classList.add('dragover');
//     }
    
//     handleDragLeave(e) {
//         e.preventDefault();
//         e.currentTarget.classList.remove('dragover');
//     }
    
//     async handleDrop(e) {
//         e.preventDefault();
//         e.currentTarget.classList.remove('dragover');
        
//         const files = e.dataTransfer.files;
//         if (files.length > 0) {
//             await this.processImageFile(files[0]);
//         }
//     }
    
//     async handleFileSelect(e) {
//         const files = e.target.files;
//         if (files.length > 0) {
//             await this.processImageFile(files[0]);
//         }
//     }
    
//     async processImageFile(file) {
//         // Validate file
//         if (!file.type.match('image/(png|jpeg|jpg)')) {
//             this.showError('Please upload a PNG or JPEG image file.');
//             return;
//         }
        
//         if (file.size > 5 * 1024 * 1024) {
//             this.showError('File size must be less than 5MB.');
//             return;
//         }
        
//         // Show OCR processing
//         this.showOCRProcessing();
        
//         try {
//             // Simulate OCR processing
//             const ocrResult = await this.simulateOCR(file);
            
//             // Update OCR preview
//             this.updateOCRPreview(ocrResult);
            
//         } catch (error) {
//             console.error('OCR Processing error:', error);
//             this.showError('Failed to process image. Please try again or enter data manually.');
//         }
//     }
    
//     async simulateOCR(file) {
//         return new Promise((resolve) => {
//             setTimeout(() => {
//                 // Generate simulated OCR results
//                 const players = [];
//                 const playerCount = 8 + Math.floor(Math.random() * 4);
                
//                 for (let i = 1; i <= playerCount; i++) {
//                     players.push({
//                         name: `Player_${i}`,
//                         kills: Math.floor(Math.random() * 15) + 2,
//                         damage: Math.floor(Math.random() * 700) + 150,
//                         survival: Math.floor(Math.random() * 1000) + 200,
//                         headshots: Math.floor(Math.random() * 8),
//                         assists: Math.floor(Math.random() * 6)
//                     });
//                 }
                
//                 const ocrText = players.map(p => 
//                     `${p.name} | ${p.kills} | ${p.damage} | ${p.survival} | ${p.headshots} | ${p.assists}`
//                 ).join('\n');
                
//                 resolve({
//                     text: ocrText,
//                     players: players,
//                     confidence: 85 + Math.random() * 10
//                 });
//             }, 1500);
//         });
//     }
    
//     showOCRProcessing() {
//         const ocrPreview = document.getElementById('ocrPreview');
//         if (ocrPreview) {
//             ocrPreview.classList.remove('hidden');
//             ocrPreview.innerHTML = `
//                 <div class="ocr-processing" style="text-align: center; padding: 20px;">
//                     <div class="spinner"></div>
//                     <p style="margin-top: 10px;">Extracting statistics from image...</p>
//                 </div>
//             `;
//         }
//     }
    
//     updateOCRPreview(result) {
//         const ocrPreview = document.getElementById('ocrPreview');
//         if (!ocrPreview) return;
        
//         const confidence = Math.round(result.confidence);
        
//         ocrPreview.innerHTML = `
//             <h4><i class="fas fa-file-alt"></i> Extracted Data</h4>
//             <div class="ocr-text">${result.text}</div>
//             <div class="ocr-confidence">
//                 <span class="confidence-label">OCR Confidence:</span>
//                 <div class="confidence-bar">
//                     <div class="confidence-fill" style="width: ${confidence}%"></div>
//                 </div>
//                 <span class="confidence-value">${confidence}%</span>
//             </div>
//             <div class="ocr-actions">
//                 <button class="btn btn-secondary" id="editOCR">
//                     <i class="fas fa-edit"></i> Edit Data
//                 </button>
//                 <button class="btn btn-success" id="useOCR">
//                     <i class="fas fa-check"></i> Use This Data
//                 </button>
//             </div>
//         `;
        
//         // Re-attach event listeners
//         document.getElementById('editOCR')?.addEventListener('click', () => this.editOCRData(result));
//         document.getElementById('useOCR')?.addEventListener('click', () => this.useOCRData(result.players));
//     }
    
//     editOCRData(result) {
//         // Switch to manual input mode
//         document.querySelector('.method-tab[data-method="manual"]')?.click();
        
//         // Populate manual inputs with OCR data
//         const playerInputs = document.getElementById('playerInputs');
//         if (playerInputs && result && result.players) {
//             playerInputs.innerHTML = '';
//             result.players.forEach((player, index) => {
//                 const playerInput = this.createPlayerInput(index);
                
//                 // Fill with OCR data
//                 const nameInput = playerInput.querySelector('.player-name');
//                 const killsInput = playerInput.querySelector('.player-kills');
//                 const damageInput = playerInput.querySelector('.player-damage');
//                 const survivalInput = playerInput.querySelector('.player-survival');
//                 const headshotsInput = playerInput.querySelector('.player-headshots');
//                 const assistsInput = playerInput.querySelector('.player-assists');
                
//                 if (nameInput) nameInput.value = player.name;
//                 if (killsInput) killsInput.value = player.kills;
//                 if (damageInput) damageInput.value = player.damage;
//                 if (survivalInput) survivalInput.value = player.survival;
//                 if (headshotsInput) headshotsInput.value = player.headshots || 0;
//                 if (assistsInput) assistsInput.value = player.assists || 0;
                
//                 playerInputs.appendChild(playerInput);
//             });
            
//             this.playerCount = result.players.length;
//         }
//     }
    
//     useOCRData(players) {
//         if (!players || !Array.isArray(players)) {
//             this.showError('Invalid OCR data');
//             return;
//         }
        
//         this.currentData = {
//             matchType: 'solo', // Default to solo for OCR
//             players: players
//         };
        
//         // Process the OCR data
//         this.processData();
//     }
    
//     importFromAPI() {
//         const platform = document.getElementById('gamePlatform')?.value;
//         const playerIds = document.getElementById('playerIds')?.value;
        
//         if (!playerIds || playerIds.trim() === '') {
//             this.showError('Please enter player IDs or match codes');
//             return;
//         }
        
//         this.showProcessing();
        
//         // Simulate API import
//         setTimeout(() => {
//             const simulatedPlayers = playerIds.split(',').map((id, index) => ({
//                 name: `Player_${id.trim()}`,
//                 kills: Math.floor(Math.random() * 15) + 2,
//                 damage: Math.floor(Math.random() * 700) + 150,
//                 survival: Math.floor(Math.random() * 1000) + 200,
//                 headshots: Math.floor(Math.random() * 8),
//                 assists: Math.floor(Math.random() * 6)
//             }));
            
//             this.currentData = {
//                 matchType: 'solo',
//                 players: simulatedPlayers
//             };
            
//             this.sendDataToServer();
//         }, 2000);
//     }
    
//     validateInputs() {
//         if (this.currentMatchType === 'solo') {
//             const playerInputs = document.querySelectorAll('.player-input-section');
//             const players = [];
            
//             for (const input of playerInputs) {
//                 const name = input.querySelector('.player-name')?.value.trim();
//                 const kills = parseInt(input.querySelector('.player-kills')?.value) || 0;
//                 const damage = parseInt(input.querySelector('.player-damage')?.value) || 0;
//                 const survival = parseInt(input.querySelector('.player-survival')?.value) || 0;
                
//                 if (!name) {
//                     this.showError('Please enter a name for all players');
//                     return null;
//                 }
                
//                 players.push({
//                     name: name,
//                     kills: kills,
//                     damage: damage,
//                     survival: survival,
//                     headshots: parseInt(input.querySelector('.player-headshots')?.value) || 0,
//                     assists: parseInt(input.querySelector('.player-assists')?.value) || 0
//                 });
//             }
            
//             return {
//                 matchType: 'solo',
//                 players: players
//             };
//         } else {
//             // Team mode validation
//             const teams = [];
//             const teamSections = document.querySelectorAll('.team-section');
            
//             for (const teamSection of teamSections) {
//                 const team = {
//                     name: teamSection.querySelector('h4')?.textContent || 'Unknown Team',
//                     players: []
//                 };
                
//                 const playerInputs = teamSection.querySelectorAll('.team-player-input');
//                 for (const input of playerInputs) {
//                     const name = input.querySelector('.team-player-name')?.value.trim();
//                     const kills = parseInt(input.querySelector('.team-player-kills')?.value) || 0;
//                     const damage = parseInt(input.querySelector('.team-player-damage')?.value) || 0;
//                     const survival = parseInt(input.querySelector('.team-player-survival')?.value) || 0;
                    
//                     if (!name) {
//                         this.showError('Please enter names for all players in each team');
//                         return null;
//                     }
                    
//                     team.players.push({
//                         name: name,
//                         kills: kills,
//                         damage: damage,
//                         survival: survival,
//                         headshots: parseInt(input.querySelector('.team-player-headshots')?.value) || 0,
//                         assists: parseInt(input.querySelector('.team-player-assists')?.value) || 0
//                     });
//                 }
                
//                 teams.push(team);
//             }
            
//             return {
//                 matchType: this.currentMatchType,
//                 teams: teams
//             };
//         }
//     }
    
//     async processData() {
//         if (this.isProcessing) return;
        
//         // Validate inputs or use current data
//         if (!this.currentData) {
//             this.currentData = this.validateInputs();
//             if (!this.currentData) return;
//         }
        
//         this.isProcessing = true;
//         this.startTime = Date.now();
        
//         // Hide results and error sections
//         document.getElementById('resultsSection')?.classList.add('hidden');
//         document.getElementById('errorSection')?.classList.add('hidden');
        
//         // Show processing section
//         this.showProcessing();
        
//         // Start processing timer
//         this.startProcessingTimer();
        
//         try {
//             await this.sendDataToServer();
//         } catch (error) {
//             console.error('Processing error:', error);
//             this.showError(error.message || 'An error occurred during processing.');
//             this.isProcessing = false;
//             clearInterval(this.processingTimer);
//         }
//     }
    
//     showProcessing() {
//         const processingSection = document.getElementById('processingSection');
//         if (processingSection) {
//             processingSection.classList.remove('hidden');
//         }
        
//         // Reset steps
//         document.querySelectorAll('.step').forEach(step => {
//             step.classList.remove('active');
//             const status = step.querySelector('.step-status');
//             if (status) status.innerHTML = '';
//         });
        
//         // Activate first step
//         const firstStep = document.querySelector('.step[data-step="1"]');
//         if (firstStep) {
//             firstStep.classList.add('active');
//         }
        
//         // Reset progress bar
//         const progressBar = document.getElementById('progressBar');
//         if (progressBar) {
//             progressBar.style.width = '0%';
//         }
//     }
    
//     updateProcessingStep(stepNumber) {
//         const steps = document.querySelectorAll('.step');
//         steps.forEach((step, index) => {
//             if (index < stepNumber) {
//                 step.classList.add('active');
//                 const status = step.querySelector('.step-status');
//                 if (status) {
//                     status.innerHTML = '<i class="fas fa-check"></i>';
//                 }
//             } else {
//                 step.classList.remove('active');
//             }
//         });
        
//         // Update progress bar
//         const progress = (stepNumber / 4) * 100;
//         const progressBar = document.getElementById('progressBar');
//         if (progressBar) {
//             progressBar.style.width = `${progress}%`;
//         }
        
//         // Update processing details
//         const details = document.getElementById('processingDetails');
//         if (details) {
//             const messages = [
//                 'Validating input data...',
//                 'Extracting statistics...',
//                 'Running AI analysis...',
//                 'Generating insights...'
//             ];
//             details.textContent = messages[stepNumber - 1] || 'Processing...';
//         }
//     }
    
//     startProcessingTimer() {
//         const timeElement = document.getElementById('processingTime');
//         this.startTime = Date.now();
        
//         this.processingTimer = setInterval(() => {
//             const elapsed = Date.now() - this.startTime;
//             const seconds = Math.floor(elapsed / 1000);
//             if (timeElement) {
//                 timeElement.textContent = `${seconds}s`;
//             }
//         }, 1000);
//     }
    
//     async sendDataToServer() {
//         try {
//             this.updateProcessingStep(1);
            
//             // Prepare form data
//             const formData = new FormData();
            
//             // Add CSRF token
//             if (this.csrfToken) {
//                 formData.append('csrf_token', this.csrfToken);
//             }
            
//             // Add match type
//             formData.append('match_type', this.currentData.matchType);
            
//             // Add player data
//             if (this.currentData.players) {
//                 formData.append('playerData', JSON.stringify({
//                     matchType: this.currentData.matchType,
//                     players: this.currentData.players
//                 }));
//             } else if (this.currentData.teams) {
//                 formData.append('playerData', JSON.stringify({
//                     matchType: this.currentData.matchType,
//                     teams: this.currentData.teams
//                 }));
//             }
            
//             // Simulate step progression
//             await this.simulateStep(2, 1000);
//             await this.simulateStep(3, 1500);
//             await this.simulateStep(4, 1000);
            
//             // Simulate server response
//             setTimeout(() => {
//                 this.handleServerResponse(this.generateSimulatedResponse());
//             }, 500);
            
//         } catch (error) {
//             throw new Error('Failed to communicate with server: ' + error.message);
//         }
//     }
    
//     simulateStep(stepNumber, delay) {
//         return new Promise(resolve => {
//             setTimeout(() => {
//                 this.updateProcessingStep(stepNumber);
//                 resolve();
//             }, delay);
//         });
//     }
    
//     generateSimulatedResponse() {
//         const isSolo = this.currentData.matchType === 'solo';
        
//         if (isSolo) {
//             const players = this.currentData.players || [];
//             const sortedPlayers = [...players].sort((a, b) => {
//                 const ratingA = this.calculatePlayerRating(a);
//                 const ratingB = this.calculatePlayerRating(b);
//                 return ratingB - ratingA;
//             });
            
//             return {
//                 success: true,
//                 prediction: {
//                     winner: sortedPlayers[0],
//                     confidence: 85 + Math.random() * 10,
//                     topPerformer: sortedPlayers[0],
//                     weakLink: sortedPlayers[sortedPlayers.length - 1],
//                     allPlayers: sortedPlayers.map(p => ({
//                         ...p,
//                         rating: this.calculatePlayerRating(p)
//                     })),
//                     insights: [
//                         `Top performer has ${this.calculatePlayerRating(sortedPlayers[0]).toFixed(1)}/10 rating`,
//                         `Average kills: ${(players.reduce((sum, p) => sum + p.kills, 0) / players.length).toFixed(1)}`,
//                         `Winner excels in ${sortedPlayers[0].kills > 8 ? 'aggressive playstyle' : 'survival skills'}`
//                     ],
//                     predictionType: 'solo',
//                     algorithmVersion: 'v2.1',
//                     mlEnhanced: true
//                 }
//             };
//         } else {
//             const teams = this.currentData.teams || [];
//             const ratedTeams = teams.map(team => {
//                 const teamPlayers = team.players.map(p => ({
//                     ...p,
//                     rating: this.calculatePlayerRating(p)
//                 }));
                
//                 const teamRating = teamPlayers.reduce((sum, p) => sum + p.rating, 0) / teamPlayers.length;
//                 const synergy = this.calculateTeamSynergy(team);
                
//                 return {
//                     ...team,
//                     players: teamPlayers,
//                     teamScore: (teamRating * 0.7) + (synergy * 0.3),
//                     synergyBonus: synergy,
//                     totalKills: teamPlayers.reduce((sum, p) => sum + p.kills, 0),
//                     totalDamage: teamPlayers.reduce((sum, p) => sum + p.damage, 0)
//                 };
//             });
            
//             const sortedTeams = ratedTeams.sort((a, b) => b.teamScore - a.teamScore);
//             const winningTeam = sortedTeams[0];
            
//             return {
//                 success: true,
//                 prediction: {
//                     winningTeam: winningTeam,
//                     confidence: 80 + Math.random() * 15,
//                     mvp: this.findMVP(winningTeam),
//                     teamWeakLink: this.findTeamWeakLink(winningTeam),
//                     allTeams: sortedTeams,
//                     insights: [
//                         `Team synergy score: ${winningTeam.synergyBonus.toFixed(1)}/10`,
//                         winningTeam.synergyBonus > 7 ? 'Excellent team coordination' : 'Good team balance',
//                         'Focus on positioning and communication'
//                     ],
//                     predictionType: this.currentData.matchType,
//                     algorithmVersion: 'v2.1',
//                     mlEnhanced: true
//                 }
//             };
//         }
//     }
    
//     calculatePlayerRating(player) {
//         const killScore = Math.min(10, player.kills * 0.7);
//         const damageScore = Math.min(10, player.damage / 80);
//         const survivalScore = Math.min(10, player.survival / 120);
//         const headshotScore = Math.min(5, (player.headshots || 0) * 0.6);
//         const assistScore = Math.min(5, (player.assists || 0) * 0.5);
        
//         const rating = (
//             killScore * 0.35 + 
//             damageScore * 0.25 + 
//             survivalScore * 0.20 + 
//             headshotScore * 0.10 +
//             assistScore * 0.10
//         );
        
//         return Math.min(10, Math.max(0, rating));
//     }
    
//     calculateTeamSynergy(team) {
//         if (!team.players || team.players.length < 2) return 5;
        
//         const kills = team.players.map(p => p.kills);
//         const damages = team.players.map(p => p.damage);
        
//         const killStd = this.calculateStandardDeviation(kills);
//         const damageStd = this.calculateStandardDeviation(damages);
        
//         const synergy = 10 - ((killStd * 1.5) + (damageStd / 150));
//         return Math.max(0, Math.min(10, synergy));
//     }
    
//     calculateStandardDeviation(arr) {
//         if (arr.length < 2) return 0;
        
//         const mean = arr.reduce((a, b) => a + b) / arr.length;
//         const variance = arr.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / arr.length;
//         return Math.sqrt(variance);
//     }
    
//     findMVP(team) {
//         if (!team.players || team.players.length === 0) return null;
        
//         let mvp = team.players[0];
//         let maxRating = this.calculatePlayerRating(mvp);
        
//         for (const player of team.players) {
//             const rating = this.calculatePlayerRating(player);
//             if (rating > maxRating) {
//                 maxRating = rating;
//                 mvp = player;
//             }
//         }
        
//         return { ...mvp, rating: maxRating };
//     }
    
//     findTeamWeakLink(team) {
//         if (!team.players || team.players.length === 0) return null;
        
//         let weakLink = team.players[0];
//         let minRating = this.calculatePlayerRating(weakLink);
        
//         for (const player of team.players) {
//             const rating = this.calculatePlayerRating(player);
//             if (rating < minRating) {
//                 minRating = rating;
//                 weakLink = player;
//             }
//         }
        
//         return { ...weakLink, rating: minRating };
//     }
    
//     handleServerResponse(response) {
//         clearInterval(this.processingTimer);
        
//         if (!response.success) {
//             this.showError(response.message || 'Server returned an error');
//             this.isProcessing = false;
//             return;
//         }
        
//         this.predictionResults = response.prediction;
        
//         // Hide processing section
//         document.getElementById('processingSection')?.classList.add('hidden');
        
//         // Display results
//         this.displayResults();
        
//         // Update recent predictions
//         this.loadRecentPredictions();
        
//         this.isProcessing = false;
//     }
    
//     displayResults() {
//         const resultsSection = document.getElementById('resultsSection');
//         if (!resultsSection) return;
        
//         resultsSection.classList.remove('hidden');
//         resultsSection.classList.add('fade-in');
        
//         const prediction = this.predictionResults;
        
//         // Update result date
//         const resultDate = document.getElementById('resultDate');
//         if (resultDate) {
//             resultDate.textContent = new Date().toLocaleString();
//         }
        
//         // Update algorithm info
//         document.getElementById('algorithmVersion')?.textContent = prediction.algorithmVersion || 'v2.1';
//         document.getElementById('mlStatus')?.textContent = prediction.mlEnhanced ? 'ML Enhanced' : 'Standard';
        
//         // Update confidence
//         document.getElementById('confidenceValueMain')?.textContent = `${prediction.confidence.toFixed(1)}%`;
//         document.getElementById('confidenceBadge')?.querySelector('.confidence-value').textContent = `${prediction.confidence.toFixed(1)}%`;
        
//         if (prediction.predictionType === 'solo') {
//             this.displaySoloResults(prediction);
//         } else {
//             this.displayTeamResults(prediction);
//         }
        
//         // Update performance cards
//         this.updatePerformanceCards(prediction);
        
//         // Update table
//         this.updateStatsTable(prediction);
        
//         // Update insights
//         this.updateInsights(prediction);
        
//         // Update charts
//         this.updateCharts(prediction);
        
//         // Scroll to results
//         resultsSection.scrollIntoView({ behavior: 'smooth' });
//     }
    
//     displaySoloResults(prediction) {
//         // Update winner info
//         document.getElementById('winnerName').textContent = prediction.winner.name;
//         document.getElementById('winnerKills').textContent = prediction.winner.kills;
//         document.getElementById('winnerDamage').textContent = prediction.winner.damage;
//         document.getElementById('winnerSurvival').textContent = `${Math.floor(prediction.winner.survival / 60)}:${(prediction.winner.survival % 60).toString().padStart(2, '0')}`;
//         document.getElementById('winnerRating').textContent = prediction.winner.rating?.toFixed(1) || '0.0';
//         document.getElementById('winnerTag').textContent = 'Solo Winner';
        
//         // Hide team members
//         document.getElementById('teamMembers')?.classList.add('hidden');
//     }
    
//     displayTeamResults(prediction) {
//         const winningTeam = prediction.winningTeam;
        
//         // Update winner info
//         document.getElementById('winnerName').textContent = winningTeam.name;
//         document.getElementById('winnerKills').textContent = winningTeam.totalKills || 0;
//         document.getElementById('winnerDamage').textContent = Math.round((winningTeam.totalDamage || 0) / winningTeam.players.length);
//         document.getElementById('winnerSurvival').textContent = `${Math.floor((winningTeam.avgSurvival || 0) / 60)}:${((winningTeam.avgSurvival || 0) % 60).toString().padStart(2, '0')}`;
//         document.getElementById('winnerRating').textContent = winningTeam.teamScore?.toFixed(1) || '0.0';
//         document.getElementById('winnerTag').textContent = `${prediction.predictionType.charAt(0).toUpperCase() + prediction.predictionType.slice(1)} Winner`;
        
//         // Show team members
//         const teamMembers = document.getElementById('teamMembers');
//         const membersList = document.getElementById('membersList');
        
//         if (teamMembers && membersList && winningTeam.players) {
//             teamMembers.classList.remove('hidden');
//             membersList.innerHTML = winningTeam.players.map(player => `
//                 <div class="member-card">
//                     <div class="member-name">${player.name}</div>
//                     <div class="member-stats">
//                         <span>K: ${player.kills}</span>
//                         <span>D: ${player.damage}</span>
//                         <span>S: ${Math.floor(player.survival / 60)}:${(player.survival % 60).toString().padStart(2, '0')}</span>
//                         <span>R: ${player.rating?.toFixed(1) || '0.0'}</span>
//                     </div>
//                 </div>
//             `).join('');
//         }
//     }
    
//     updatePerformanceCards(prediction) {
//         if (prediction.predictionType === 'solo') {
//             // Top performer
//             document.getElementById('topPerformerName').textContent = prediction.topPerformer?.name || 'N/A';
//             document.getElementById('topPerformerKD').textContent = prediction.topPerformer?.kills ? `${prediction.topPerformer.kills}/1` : '0/0';
//             document.getElementById('topPerformerDMG').textContent = prediction.topPerformer?.damage || 0;
//             document.getElementById('topPerformerRating').textContent = prediction.topPerformer?.rating?.toFixed(1) || '0.0';
            
//             // Weak link
//             document.getElementById('weakLinkName').textContent = prediction.weakLink?.name || 'N/A';
//             document.getElementById('weakLinkKD').textContent = prediction.weakLink?.kills ? `${prediction.weakLink.kills}/2` : '0/0';
//             document.getElementById('weakLinkDMG').textContent = prediction.weakLink?.damage || 0;
//             document.getElementById('weakLinkRating').textContent = prediction.weakLink?.rating?.toFixed(1) || '0.0';
            
//             // Hide synergy for solo
//             document.querySelector('.performance-card.synergy')?.classList.add('hidden');
//         } else {
//             // MVP
//             document.getElementById('topPerformerName').textContent = `${prediction.mvp?.name} (MVP)` || 'N/A';
//             document.getElementById('topPerformerKD').textContent = prediction.mvp?.kills ? `${prediction.mvp.kills}/1` : '0/0';
//             document.getElementById('topPerformerDMG').textContent = prediction.mvp?.damage || 0;
//             document.getElementById('topPerformerRating').textContent = prediction.mvp?.rating?.toFixed(1) || '0.0';
            
//             // Team weak link
//             document.getElementById('weakLinkName').textContent = prediction.teamWeakLink?.name || 'N/A';
//             document.getElementById('weakLinkKD').textContent = prediction.teamWeakLink?.kills ? `${prediction.teamWeakLink.kills}/2` : '0/0';
//             document.getElementById('weakLinkDMG').textContent = prediction.teamWeakLink?.damage || 0;
//             document.getElementById('weakLinkRating').textContent = prediction.teamWeakLink?.rating?.toFixed(1) || '0.0';
            
//             // Show synergy
//             const synergyCard = document.querySelector('.performance-card.synergy');
//             if (synergyCard) {
//                 synergyCard.classList.remove('hidden');
//                 const winningTeam = prediction.winningTeam;
//                 const synergyScore = winningTeam.synergyBonus || 5;
//                 document.getElementById('synergyScore').textContent = synergyScore.toFixed(1);
//                 document.getElementById('synergyMeter').style.width = `${synergyScore * 10}%`;
                
//                 if (synergyScore > 7) {
//                     document.getElementById('synergyDesc').textContent = 'Excellent team coordination';
//                 } else if (synergyScore > 5) {
//                     document.getElementById('synergyDesc').textContent = 'Good team balance';
//                 } else {
//                     document.getElementById('synergyDesc').textContent = 'Needs improvement';
//                 }
//             }
//         }
        
//         // Improvement tip
//         const improvementTip = document.getElementById('improvementTip');
//         if (improvementTip) {
//             improvementTip.textContent = 'Focus on positioning and aim training';
//         }
        
//         // Prediction accuracy
//         document.getElementById('predictionAccuracy').textContent = `${prediction.confidence.toFixed(1)}%`;
//         document.getElementById('algorithmTag').textContent = prediction.algorithmVersion || 'v2.1';
//         document.getElementById('mlTag').textContent = prediction.mlEnhanced ? 'ML Enhanced' : 'Standard';
//     }
    
//     updateStatsTable(prediction) {
//         const tableHeader = document.getElementById('tableHeader');
//         const tableBody = document.getElementById('statsTableBody');
        
//         if (!tableHeader || !tableBody) return;
        
//         if (prediction.predictionType === 'solo') {
//             tableHeader.innerHTML = `
//                 <th>Player</th>
//                 <th>Kills</th>
//                 <th>Damage</th>
//                 <th>Survival</th>
//                 <th>Headshots</th>
//                 <th>Assists</th>
//                 <th>Rating</th>
//             `;
            
//             tableBody.innerHTML = prediction.allPlayers.map((player, index) => `
//                 <tr class="${index === 0 ? 'winner-row' : ''}">
//                     <td><strong>${player.name}</strong></td>
//                     <td>${player.kills}</td>
//                     <td>${player.damage}</td>
//                     <td>${Math.floor(player.survival / 60)}:${(player.survival % 60).toString().padStart(2, '0')}</td>
//                     <td>${player.headshots || 0}</td>
//                     <td>${player.assists || 0}</td>
//                     <td><span class="rating-badge">${player.rating?.toFixed(1) || '0.0'}</span></td>
//                 </tr>
//             `).join('');
//         } else {
//             tableHeader.innerHTML = `
//                 <th>Team</th>
//                 <th>Players</th>
//                 <th>Total Kills</th>
//                 <th>Avg Damage</th>
//                 <th>Team Score</th>
//                 <th>Synergy</th>
//                 <th>Rank</th>
//             `;
            
//             tableBody.innerHTML = prediction.allTeams.map((team, index) => `
//                 <tr class="${index === 0 ? 'winner-row' : ''}">
//                     <td><strong>${team.name}</strong></td>
//                     <td>${team.players?.length || 0}</td>
//                     <td>${team.totalKills || 0}</td>
//                     <td>${Math.round((team.totalDamage || 0) / (team.players?.length || 1))}</td>
//                     <td><span class="rating-badge">${team.teamScore?.toFixed(1) || '0.0'}</span></td>
//                     <td>${team.synergyBonus?.toFixed(1) || '5.0'}</td>
//                     <td><span class="rank-badge">${index + 1}</span></td>
//                 </tr>
//             `).join('');
//         }
//     }
    
//     updateInsights(prediction) {
//         const insights = prediction.insights || [
//             'Analyzing performance patterns...',
//             'Generating strategic recommendations...',
//             'Identifying improvement areas...'
//         ];
        
//         document.getElementById('insight1').textContent = insights[0] || 'No insights available';
//         document.getElementById('insight2').textContent = insights[1] || 'No insights available';
//         document.getElementById('insight3').textContent = insights[2] || 'No insights available';
//     }
    
//     initializeCharts() {
//         // Initialize Chart.js instances
//         const ratingCtx = document.getElementById('ratingChart')?.getContext('2d');
//         const performanceCtx = document.getElementById('performanceChart')?.getContext('2d');
        
//         if (ratingCtx) {
//             this.charts.rating = new Chart(ratingCtx, {
//                 type: 'bar',
//                 data: {
//                     labels: [],
//                     datasets: [{
//                         label: 'Player Rating',
//                         data: [],
//                         backgroundColor: '#667eea',
//                         borderColor: '#764ba2',
//                         borderWidth: 1
//                     }]
//                 },
//                 options: {
//                     responsive: true,
//                     maintainAspectRatio: false,
//                     scales: {
//                         y: {
//                             beginAtZero: true,
//                             max: 10,
//                             title: {
//                                 display: true,
//                                 text: 'Rating'
//                             }
//                         },
//                         x: {
//                             title: {
//                                 display: true,
//                                 text: 'Players'
//                             }
//                         }
//                     }
//                 }
//             });
//         }
        
//         if (performanceCtx) {
//             this.charts.performance = new Chart(performanceCtx, {
//                 type: 'radar',
//                 data: {
//                     labels: ['Kills', 'Damage', 'Survival', 'Headshots', 'Assists'],
//                     datasets: [{
//                         label: 'Performance Metrics',
//                         data: [0, 0, 0, 0, 0],
//                         backgroundColor: 'rgba(102, 126, 234, 0.2)',
//                         borderColor: '#667eea',
//                         borderWidth: 2,
//                         pointBackgroundColor: '#667eea'
//                     }]
//                 },
//                 options: {
//                     responsive: true,
//                     maintainAspectRatio: false,
//                     scales: {
//                         r: {
//                             beginAtZero: true,
//                             max: 100,
//                             ticks: {
//                                 stepSize: 20
//                             }
//                         }
//                     }
//                 }
//             });
//         }
//     }
    
//     updateCharts(prediction) {
//         if (prediction.predictionType === 'solo' && this.charts.rating && prediction.allPlayers) {
//             // Update rating chart
//             const labels = prediction.allPlayers.map(p => p.name.substring(0, 10) + (p.name.length > 10 ? '...' : ''));
//             const ratings = prediction.allPlayers.map(p => p.rating || 0);
            
//             this.charts.rating.data.labels = labels;
//             this.charts.rating.data.datasets[0].data = ratings;
//             this.charts.rating.update();
            
//             // Update performance chart for winner
//             if (prediction.winner && this.charts.performance) {
//                 const winner = prediction.winner;
//                 const maxKills = Math.max(...prediction.allPlayers.map(p => p.kills));
//                 const maxDamage = Math.max(...prediction.allPlayers.map(p => p.damage));
//                 const maxSurvival = Math.max(...prediction.allPlayers.map(p => p.survival));
//                 const maxHeadshots = Math.max(...prediction.allPlayers.map(p => p.headshots || 0));
//                 const maxAssists = Math.max(...prediction.allPlayers.map(p => p.assists || 0));
                
//                 this.charts.performance.data.datasets[0].data = [
//                     (winner.kills / maxKills) * 100,
//                     (winner.damage / maxDamage) * 100,
//                     (winner.survival / maxSurvival) * 100,
//                     ((winner.headshots || 0) / maxHeadshots) * 100,
//                     ((winner.assists || 0) / maxAssists) * 100
//                 ];
//                 this.charts.performance.update();
//             }
//         }
//     }
    
//     exportTable() {
//         const table = document.getElementById('statsTable');
//         if (!table) return;
        
//         const rows = table.querySelectorAll('tr');
//         const csv = [];
        
//         rows.forEach(row => {
//             const rowData = [];
//             row.querySelectorAll('th, td').forEach(cell => {
//                 rowData.push(`"${cell.textContent.replace(/"/g, '""')}"`);
//             });
//             csv.push(rowData.join(','));
//         });
        
//         const csvContent = csv.join('\n');
//         const blob = new Blob([csvContent], { type: 'text/csv' });
//         const url = URL.createObjectURL(blob);
//         const a = document.createElement('a');
//         a.href = url;
//         a.download = `infiknight-stats-${new Date().toISOString().slice(0,10)}.csv`;
//         document.body.appendChild(a);
//         a.click();
//         document.body.removeChild(a);
//         URL.revokeObjectURL(url);
//     }
    
//     savePrediction() {
//         if (!this.predictionResults) {
//             this.showError('No prediction results to save');
//             return;
//         }
        
//         // In a real application, this would save to a database
//         const predictions = JSON.parse(localStorage.getItem('savedPredictions') || '[]');
//         predictions.push({
//             timestamp: new Date().toISOString(),
//             data: this.predictionResults
//         });
        
//         localStorage.setItem('savedPredictions', JSON.stringify(predictions));
        
//         // Show success message
//         this.showNotification('Prediction saved successfully!', 'success');
//     }
    
//     resetForm() {
//         // Reset form elements
//         document.getElementById('statsFile').value = '';
//         document.getElementById('resultsSection').classList.add('hidden');
//         document.getElementById('errorSection').classList.add('hidden');
//         document.getElementById('ocrPreview').classList.add('hidden');
        
//         // Reset to default state
//         this.currentData = null;
//         this.predictionResults = null;
//         this.isProcessing = false;
        
//         // Reset match type to solo
//         document.querySelector('input[name="matchType"][value="solo"]').checked = true;
//         this.currentMatchType = 'solo';
//         this.updateInputMode();
        
//         // Switch to upload method
//         document.querySelector('.method-tab[data-method="upload"]').click();
        
//         // Clear charts
//         if (this.charts.rating) {
//             this.charts.rating.data.labels = [];
//             this.charts.rating.data.datasets[0].data = [];
//             this.charts.rating.update();
//         }
        
//         if (this.charts.performance) {
//             this.charts.performance.data.datasets[0].data = [0, 0, 0, 0, 0];
//             this.charts.performance.update();
//         }
        
//         // Scroll to top
//         window.scrollTo({ top: 0, behavior: 'smooth' });
        
//         this.showNotification('Form reset successfully!', 'info');
//     }
    
//     retryProcessing() {
//         document.getElementById('errorSection').classList.add('hidden');
//         this.processData();
//     }
    
//     showError(message) {
//         const errorSection = document.getElementById('errorSection');
//         const errorMessage = document.getElementById('errorMessage');
        
//         if (errorSection && errorMessage) {
//             errorMessage.textContent = message;
//             errorSection.classList.remove('hidden');
//             errorSection.scrollIntoView({ behavior: 'smooth' });
//         } else {
//             alert(message);
//         }
//     }
    
//     showNotification(message, type = 'info') {
//         // Create notification element
//         const notification = document.createElement('div');
//         notification.className = `notification notification-${type}`;
//         notification.innerHTML = `
//             <div class="notification-content">
//                 <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
//                 <span>${message}</span>
//             </div>
//         `;
        
//         // Add to page
//         document.body.appendChild(notification);
        
//         // Animate in
//         setTimeout(() => {
//             notification.classList.add('show');
//         }, 10);
        
//         // Remove after 3 seconds
//         setTimeout(() => {
//             notification.classList.remove('show');
//             setTimeout(() => {
//                 document.body.removeChild(notification);
//             }, 300);
//         }, 3000);
//     }
    
//     async loadRecentPredictions() {
//         const recentList = document.getElementById('recentList');
//         if (!recentList) return;
        
//         // Simulate loading recent predictions
//         setTimeout(() => {
//             const predictions = [
//                 { match: 'Solo Match', winner: 'Player_1', confidence: '87%', time: '2 min ago' },
//                 { match: 'Duo Tournament', winner: 'Team Alpha', confidence: '92%', time: '15 min ago' },
//                 { match: 'Squad Practice', winner: 'Pro Team', confidence: '78%', time: '1 hour ago' },
//                 { match: 'Solo Ranked', winner: 'ShadowPlayer', confidence: '85%', time: '2 hours ago' }
//             ];
            
//             recentList.innerHTML = predictions.map(pred => `
//                 <div class="recent-item">
//                     <div class="recent-match">${pred.match}</div>
//                     <div class="recent-winner">Winner: ${pred.winner}</div>
//                     <div class="recent-confidence">Confidence: ${pred.confidence} • ${pred.time}</div>
//                 </div>
//             `).join('');
//         }, 500);
//     }
    
//     handleBeforeUnload(e) {
//         if (this.isProcessing) {
//             e.preventDefault();
//             e.returnValue = 'You have a prediction in progress. Are you sure you want to leave?';
//             return e.returnValue;
//         }
//     }
// }

// // Add notification styles
// document.addEventListener('DOMContentLoaded', function() {
//     const style = document.createElement('style');
//     style.textContent = `
//         .notification {
//             position: fixed;
//             top: 20px;
//             right: 20px;
//             background: white;
//             border-radius: 8px;
//             padding: 15px 20px;
//             box-shadow: 0 4px 20px rgba(0,0,0,0.15);
//             z-index: 9999;
//             transform: translateX(100%);
//             opacity: 0;
//             transition: transform 0.3s ease, opacity 0.3s ease;
//             max-width: 350px;
//         }
        
//         .notification.show {
//             transform: translateX(0);
//             opacity: 1;
//         }
        
//         .notification-success {
//             border-left: 4px solid #4CAF50;
//         }
        
//         .notification-error {
//             border-left: 4px solid #f44336;
//         }
        
//         .notification-info {
//             border-left: 4px solid #2196F3;
//         }
        
//         .notification-content {
//             display: flex;
//             align-items: center;
//             gap: 10px;
//         }
        
//         .notification-content i {
//             font-size: 1.2rem;
//         }
        
//         .notification-success i {
//             color: #4CAF50;
//         }
        
//         .notification-error i {
//             color: #f44336;
//         }
        
//         .notification-info i {
//             color: #2196F3;
//         }
//     `;
//     document.head.appendChild(style);
// });

// // Make the class available globally
// window.InfiknightPredictorApp = InfiknightPredictorApp;