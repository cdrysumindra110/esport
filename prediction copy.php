<?php
// Include header
include_once('header.php');

// Generate CSRF token
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infiknight AI Prediction System v2.0</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./css/prediction.css?version=<?php echo time(); ?>">
    
    <!-- External JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Inline CSS for critical styles -->
    <style>
        .hidden { display: none !important; }
        .fade-in { animation: fadeIn 0.5s ease; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Loading Spinner */
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e0e0e0;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Improved Team Inputs */
        .team-inputs-container {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-top: 15px;
            background: #f9f9f9;
        }
        
        .team-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
        }
        
        .team-section h4 {
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .team-player-input {
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 6px;
        }
        
        /* Scrollbar styling */
        .team-inputs-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .team-inputs-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .team-inputs-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .team-inputs-container::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .input-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            
            .team-inputs-container {
                max-height: 300px;
            }
        }
        
        /* Match option improvements */
        .match-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .match-option {
            flex: 1;
        }
        
        .match-option input[type="radio"] {
            display: none;
        }
        
        .match-option .option-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .match-option input[type="radio"]:checked + .option-content {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
        
        .match-option .option-content i {
            font-size: 24px;
            margin-bottom: 8px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <!-- Theme Toggle -->
    <div class="theme-toggle">
        <button class="toggle-btn" id="themeToggle">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="infiknight-container">
        <!-- Header Section -->
        <header class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fas fa-robot"></i> Infiknight AI Prediction System
                </h1>
                <p class="hero-subtitle">Advanced Battle Royale Winner Prediction with OCR & ML</p>
                <div class="version-badge">v2.0</div>
                <div class="game-badges">
                    <span class="badge bg-pubg">PUBG</span>
                    <span class="badge bg-freefire">Free Fire</span>
                    <span class="badge bg-cod">Call of Duty</span>
                    <span class="badge bg-apex">Apex Legends</span>
                </div>
            </div>
        </header>

        <div class="main-layout">
            <!-- Left Sidebar - Input Section -->
            <div class="sidebar">
                <div class="sidebar-card">
                    <h2 class="card-title">
                        <i class="fas fa-upload"></i> Data Input
                    </h2>
                    
                    <!-- Input Method Selector -->
                    <div class="input-method-selector">
                        <div class="method-tabs">
                            <button class="method-tab active" data-method="upload">
                                <i class="fas fa-file-upload"></i> Upload Image
                            </button>
                            <button class="method-tab" data-method="manual">
                                <i class="fas fa-keyboard"></i> Manual Input
                            </button>
                            <button class="method-tab" data-method="api">
                                <i class="fas fa-plug"></i> API Import
                            </button>
                        </div>
                        
                        <!-- Upload Section -->
                        <div class="method-content active" id="uploadMethod">
                            <div class="upload-zone" id="uploadZone">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <h3>Drop your screenshot here</h3>
                                <p class="upload-hint">Supports PNG, JPG up to 5MB</p>
                                <button class="btn btn-primary" id="browseBtn">
                                    <i class="fas fa-folder-open"></i> Browse Files
                                </button>
                                <input type="file" id="statsFile" accept=".png,.jpg,.jpeg" style="display: none;">
                                <p class="ocr-notice">
                                    <i class="fas fa-robot"></i> AI OCR will extract statistics automatically
                                </p>
                            </div>
                            
                            <!-- OCR Preview -->
                            <div class="ocr-preview hidden" id="ocrPreview">
                                <!-- Content loaded dynamically -->
                            </div>
                        </div>
                        
                        <!-- Manual Input Section -->
                        <div class="method-content" id="manualMethod">
                            <div class="match-type-selector">
                                <h3>Match Type</h3>
                                <div class="match-options">
                                    <label class="match-option">
                                        <input type="radio" name="matchType" value="solo" checked>
                                        <span class="option-content">
                                            <i class="fas fa-user"></i>
                                            <span>Solo</span>
                                            <small>1 player per team</small>
                                        </span>
                                    </label>
                                    <label class="match-option">
                                        <input type="radio" name="matchType" value="duo">
                                        <span class="option-content">
                                            <i class="fas fa-user-friends"></i>
                                            <span>Duo</span>
                                            <small>2 players per team</small>
                                        </span>
                                    </label>
                                    <label class="match-option">
                                        <input type="radio" name="matchType" value="squad">
                                        <span class="option-content">
                                            <i class="fas fa-users"></i>
                                            <span>Squad</span>
                                            <small>4 players per team</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Team Configuration -->
                            <div class="team-config-section" id="teamConfig">
                                <h3><i class="fas fa-users-cog"></i> Team Configuration</h3>
                                <div class="team-controls">
                                    <div class="form-group">
                                        <label>Number of Teams:</label>
                                        <select id="teamCount" class="form-control">
                                            <option value="2">2 Teams</option>
                                            <option value="3">3 Teams</option>
                                            <option value="4">4 Teams</option>
                                            <option value="5">5 Teams</option>
                                            <option value="6">6 Teams</option>
                                            <option value="7">7 Teams</option>
                                            <option value="8">8 Teams</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-secondary" id="generateTeams">
                                        <i class="fas fa-plus-circle"></i> Generate Teams
                                    </button>
                                </div>
                                <div class="team-inputs-container">
                                    <div class="team-inputs" id="teamInputs">
                                        <!-- Teams will be generated here -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Player Inputs (for solo) -->
                            <div class="player-inputs" id="playerInputs">
                                <div class="player-input-section">
                                    <h4><i class="fas fa-user"></i> Player 1</h4>
                                    <div class="input-grid">
                                        <div class="input-group">
                                            <label>Player Name</label>
                                            <input type="text" class="form-control player-name" 
                                                   placeholder="Enter player name" value="Player 1" required>
                                        </div>
                                        <div class="input-group">
                                            <label>Kills</label>
                                            <input type="number" class="form-control player-kills" 
                                                   min="0" max="50" value="5" required>
                                        </div>
                                        <div class="input-group">
                                            <label>Damage</label>
                                            <input type="number" class="form-control player-damage" 
                                                   min="0" max="5000" value="250" required>
                                        </div>
                                        <div class="input-group">
                                            <label>Survival (sec)</label>
                                            <input type="number" class="form-control player-survival" 
                                                   min="0" max="1800" value="450" required>
                                        </div>
                                        <div class="input-group">
                                            <label>Headshots</label>
                                            <input type="number" class="form-control player-headshots" 
                                                   min="0" max="50" value="2">
                                        </div>
                                        <div class="input-group">
                                            <label>Assists</label>
                                            <input type="number" class="form-control player-assists" 
                                                   min="0" max="20" value="0">
                                        </div>
                                    </div>
                                    <button class="btn btn-danger btn-sm remove-player" onclick="removePlayerInput(this)">
                                        <i class="fas fa-times"></i> Remove Player
                                    </button>
                                </div>
                            </div>
                            
                            <button class="btn btn-secondary" id="addPlayerBtn">
                                <i class="fas fa-plus"></i> Add Another Player
                            </button>
                        </div>
                        
                        <!-- API Import Section -->
                        <div class="method-content" id="apiMethod">
                            <div class="api-import">
                                <h3><i class="fas fa-plug"></i> Import from Game API</h3>
                                <div class="form-group">
                                    <label>Select Game Platform:</label>
                                    <select class="form-control" id="gamePlatform">
                                        <option value="steam">Steam</option>
                                        <option value="epic">Epic Games</option>
                                        <option value="xbox">Xbox Live</option>
                                        <option value="psn">PlayStation Network</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Player/Team IDs (comma separated):</label>
                                    <textarea class="form-control" id="playerIds" 
                                              placeholder="Enter player IDs or match codes"></textarea>
                                </div>
                                <button class="btn btn-primary" id="importApi">
                                    <i class="fas fa-download"></i> Import Statistics
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Process Button -->
                    <div class="process-actions">
                        <button class="btn btn-primary btn-lg btn-process" id="processBtn">
                            <i class="fas fa-bolt"></i> Generate AI Prediction
                        </button>
                        <input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    </div>
                </div>
                
                <!-- Recent Predictions -->
                <div class="sidebar-card" id="recentPredictions">
                    <h2 class="card-title">
                        <i class="fas fa-history"></i> Recent Predictions
                    </h2>
                    <div class="recent-list" id="recentList">
                        <div class="recent-loading">
                            <div class="spinner" style="width: 20px; height: 20px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content - Results & Analytics -->
            <div class="main-content">
                <!-- Processing Status -->
                <div class="processing-section card hidden" id="processingSection">
                    <div class="processing-header">
                        <h2><i class="fas fa-cogs"></i> AI Processing</h2>
                        <div class="processing-time" id="processingTime">0s</div>
                    </div>
                    
                    <div class="processing-steps">
                        <div class="step active" data-step="1">
                            <div class="step-icon">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="step-content">
                                <h4>Upload & Validation</h4>
                                <p>Validating input data...</p>
                            </div>
                            <div class="step-status">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        
                        <div class="step" data-step="2">
                            <div class="step-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="step-content">
                                <h4>OCR Processing</h4>
                                <p>Extracting statistics from image...</p>
                            </div>
                            <div class="step-status"></div>
                        </div>
                        
                        <div class="step" data-step="3">
                            <div class="step-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <div class="step-content">
                                <h4>AI Analysis</h4>
                                <p>Running ML algorithms...</p>
                            </div>
                            <div class="step-status"></div>
                        </div>
                        
                        <div class="step" data-step="4">
                            <div class="step-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="step-content">
                                <h4>Generating Insights</h4>
                                <p>Creating detailed analysis...</p>
                            </div>
                            <div class="step-status"></div>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                    </div>
                    
                    <div class="processing-details" id="processingDetails">
                        <p>Initializing prediction engine...</p>
                    </div>
                </div>
                
                <!-- Results Section -->
                <div class="results-section hidden" id="resultsSection">
                    <!-- Results Header -->
                    <div class="results-header card">
                        <div class="header-left">
                            <h2><i class="fas fa-trophy"></i> Prediction Results</h2>
                            <div class="result-meta">
                                <span class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span id="resultDate"><?php echo date('M d, Y H:i'); ?></span>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-code-branch"></i>
                                    <span id="algorithmVersion">v2.1</span>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-robot"></i>
                                    <span id="mlStatus">ML Enhanced</span>
                                </span>
                            </div>
                        </div>
                        <div class="header-right">
                            <div class="confidence-badge" id="confidenceBadge">
                                <div class="confidence-value" id="confidenceValueMain">0%</div>
                                <div class="confidence-label">Confidence</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Winner Card -->
                    <div class="winner-section card">
                        <div class="winner-header">
                            <h3><i class="fas fa-crown"></i> Predicted Winner</h3>
                            <div class="winner-tag" id="winnerTag">Solo Winner</div>
                        </div>
                        <div class="winner-card">
                            <div class="winner-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="winner-info">
                                <h4 id="winnerName">Loading...</h4>
                                <div class="winner-stats">
                                    <div class="stat">
                                        <i class="fas fa-skull"></i>
                                        <span>Kills: <strong id="winnerKills">0</strong></span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-bullseye"></i>
                                        <span>Damage: <strong id="winnerDamage">0</strong></span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-clock"></i>
                                        <span>Survival: <strong id="winnerSurvival">0m</strong></span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-star"></i>
                                        <span>Rating: <strong id="winnerRating">0.0</strong>/10</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Team Members (for team modes) -->
                        <div class="team-members" id="teamMembers">
                            <h5><i class="fas fa-users"></i> Team Performance</h5>
                            <div class="team-performance-grid" id="teamPerformanceGrid"></div>
                        </div>
                    </div>
                    
                    <!-- Performance Grid -->
                    <div class="performance-section card">
                        <h3><i class="fas fa-chart-line"></i> Performance Analysis</h3>
                        <div class="performance-grid">
                            <div class="performance-card top-performer">
                                <div class="card-header">
                                    <i class="fas fa-star"></i>
                                    <h4>Top Performer</h4>
                                </div>
                                <div class="card-body">
                                    <div class="performer-name" id="topPerformerName">Loading...</div>
                                    <div class="performer-stats">
                                        <span class="stat">K/D: <strong id="topPerformerKD">0.0</strong></span>
                                        <span class="stat">DMG: <strong id="topPerformerDMG">0</strong></span>
                                        <span class="stat">Rating: <strong id="topPerformerRating">0.0</strong></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="performance-card weak-link">
                                <div class="card-header">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h4>Improvement Needed</h4>
                                </div>
                                <div class="card-body">
                                    <div class="performer-name" id="weakLinkName">Loading...</div>
                                    <div class="performer-stats">
                                        <span class="stat">K/D: <strong id="weakLinkKD">0.0</strong></span>
                                        <span class="stat">DMG: <strong id="weakLinkDMG">0</strong></span>
                                        <span class="stat">Rating: <strong id="weakLinkRating">0.0</strong></span>
                                    </div>
                                    <div class="improvement-tip" id="improvementTip">
                                        Focus on positioning and aim training
                                    </div>
                                </div>
                            </div>
                            
                            <div class="performance-card synergy">
                                <div class="card-header">
                                    <i class="fas fa-users"></i>
                                    <h4>Team Synergy</h4>
                                </div>
                                <div class="card-body">
                                    <div class="synergy-score">
                                        <div class="score-value" id="synergyScore">0.0</div>
                                        <div class="score-label">/10</div>
                                    </div>
                                    <div class="synergy-desc" id="synergyDesc">
                                        Team coordination level
                                    </div>
                                    <div class="synergy-meter">
                                        <div class="meter-fill" id="synergyMeter" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="performance-card prediction">
                                <div class="card-header">
                                    <i class="fas fa-robot"></i>
                                    <h4>AI Prediction</h4>
                                </div>
                                <div class="card-body">
                                    <div class="prediction-accuracy">
                                        <div class="accuracy-value" id="predictionAccuracy">0%</div>
                                        <div class="accuracy-label">Confidence</div>
                                    </div>
                                    <div class="prediction-desc" id="predictionDesc">
                                        Based on ML analysis
                                    </div>
                                    <div class="algorithm-info">
                                        <span class="info-tag" id="algorithmTag">v2.1</span>
                                        <span class="info-tag" id="mlTag">ML Enhanced</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Error Display -->
                <div class="error-section card hidden" id="errorSection">
                    <div class="error-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Processing Error</h3>
                    </div>
                    <div class="error-body">
                        <p id="errorMessage">An error occurred during processing.</p>
                        <div class="error-actions">
                            <button class="btn btn-primary" id="retryButton">
                                <i class="fas fa-redo"></i> Try Again
                            </button>
                            <button class="btn btn-secondary" id="reportError">
                                <i class="fas fa-bug"></i> Report Issue
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- New Prediction Button -->
                <div class="new-prediction-section card hidden" id="newPredictionSection">
                    <div class="new-prediction-content">
                        <h3><i class="fas fa-redo"></i> Ready for Another Prediction?</h3>
                        <button class="btn btn-primary btn-lg" id="newPrediction">
                            <i class="fas fa-plus"></i> Start New Prediction
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script>
// Initialize after DOM loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing app...');
    
    // Initialize everything
    initializeApp();
    
    // Set initial state
    updateInputMode();
});

function initializeApp() {
    console.log('Initializing application...');
    
    // 1. Initialize Theme Toggle
    initializeTheme();
    
    // 2. Initialize Tabs
    initializeTabs();
    
    // 3. Initialize Event Listeners
    initializeEventListeners();
    
    console.log('Application initialized successfully');
}

// 1. Theme Toggle Function
function initializeTheme() {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;
    
    // Set initial theme from localStorage or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Update icon based on current theme
    const icon = themeToggle.querySelector('i');
    if (icon) {
        icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    
    // Add click event
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Update theme
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Update icon
        if (icon) {
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    });
}

// 2. Tab Switching Function
function initializeTabs() {
    const methodTabs = document.querySelectorAll('.method-tab');
    const methodContents = document.querySelectorAll('.method-content');
    
    if (methodTabs.length === 0) return;
    
    // Add click event to each tab
    methodTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const method = this.getAttribute('data-method');
            console.log('Switching to tab:', method);
            
            // Remove active class from all tabs
            methodTabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all method contents
            methodContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Show the selected method content
            const targetContent = document.getElementById(method + 'Method');
            if (targetContent) {
                targetContent.classList.add('active');
            }
            
            // If switching to manual input, update match type
            if (method === 'manual') {
                updateInputMode();
            }
        });
    });
}

// 3. Initialize All Event Listeners
function initializeEventListeners() {
    console.log('Initializing event listeners...');
    
    // A. Browse Files Button
    const browseBtn = document.getElementById('browseBtn');
    const statsFile = document.getElementById('statsFile');
    if (browseBtn && statsFile) {
        browseBtn.addEventListener('click', function() {
            statsFile.click();
        });
    }
    
    // B. File Input Change
    if (statsFile) {
        statsFile.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                console.log('File selected:', fileName);
                showOCRProcessing();
            }
        });
    }
    
    // C. Match Type Radio Buttons
    document.querySelectorAll('input[name="matchType"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Match type changed to:', this.value);
            updateInputMode();
        });
    });
    
    // D. Add Player Button
    const addPlayerBtn = document.getElementById('addPlayerBtn');
    if (addPlayerBtn) {
        addPlayerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Add Player button clicked');
            addPlayerInput();
        });
    }
    
    // E. Generate Teams Button
    const generateTeamsBtn = document.getElementById('generateTeams');
    if (generateTeamsBtn) {
        generateTeamsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            generateTeams();
        });
    }
    
    // F. Team Count Select
    const teamCountEl = document.getElementById('teamCount');
    if (teamCountEl) {
        teamCountEl.addEventListener('change', function() {
            generateTeams();
        });
    }
    
    // G. Process Button
    const processBtn = document.getElementById('processBtn');
    if (processBtn) {
        processBtn.addEventListener('click', function(e) {
            e.preventDefault();
            processData();
        });
    }
    
    // H. Import API Button
    const importApiBtn = document.getElementById('importApi');
    if (importApiBtn) {
        importApiBtn.addEventListener('click', function(e) {
            e.preventDefault();
            importFromAPI();
        });
    }
    
    // I. New Prediction Button
    const newPredictionBtn = document.getElementById('newPrediction');
    if (newPredictionBtn) {
        newPredictionBtn.addEventListener('click', function(e) {
            e.preventDefault();
            resetForm();
        });
    }
    
    // J. Retry Button
    const retryBtn = document.getElementById('retryButton');
    if (retryBtn) {
        retryBtn.addEventListener('click', function(e) {
            e.preventDefault();
            retryProcessing();
        });
    }
    
    console.log('All event listeners initialized');
}

// 4. Update Input Mode Based on Match Type
function updateInputMode() {
    const matchType = document.querySelector('input[name="matchType"]:checked').value;
    const teamConfig = document.getElementById('teamConfig');
    const playerInputs = document.getElementById('playerInputs');
    const addPlayerBtn = document.getElementById('addPlayerBtn');
    
    console.log('Updating input mode for:', matchType);
    
    if (matchType === 'solo') {
        // Solo mode - show individual player inputs
        if (teamConfig) teamConfig.classList.add('hidden');
        if (playerInputs) playerInputs.classList.remove('hidden');
        if (addPlayerBtn) addPlayerBtn.style.display = 'block';
        
        // Update winner tag
        document.getElementById('winnerTag').textContent = 'Solo Winner';
    } else {
        // Team modes - show team configuration
        if (teamConfig) teamConfig.classList.remove('hidden');
        if (playerInputs) playerInputs.classList.add('hidden');
        if (addPlayerBtn) addPlayerBtn.style.display = 'none';
        
        // Update winner tag
        document.getElementById('winnerTag').textContent = matchType === 'duo' ? 'Duo Winner' : 'Squad Winner';
        
        // Generate teams
        generateTeams();
    }
}

// 5. Generate Teams Function - IMPROVED
function generateTeams() {
    const teamInputs = document.getElementById('teamInputs');
    if (!teamInputs) return;
    
    const teamCount = parseInt(document.getElementById('teamCount').value) || 2;
    const matchType = document.querySelector('input[name="matchType"]:checked').value;
    
    // Determine players per team based on match type
    let playersPerTeam;
    if (matchType === 'solo') {
        playersPerTeam = 1;
    } else if (matchType === 'duo') {
        playersPerTeam = 2;
    } else { // squad
        playersPerTeam = 4;
    }
    
    console.log('Generating teams:', teamCount, 'teams,', playersPerTeam, 'players per team');
    
    teamInputs.innerHTML = '';
    
    for (let i = 0; i < teamCount; i++) {
        const teamDiv = document.createElement('div');
        teamDiv.className = 'team-section';
        teamDiv.innerHTML = `<h4><i class="fas fa-users"></i> Team ${i + 1}</h4>`;
        
        for (let j = 0; j < playersPerTeam; j++) {
            teamDiv.appendChild(createTeamPlayerInput(i, j));
        }
        
        teamInputs.appendChild(teamDiv);
    }
    
    // Auto-scroll to team inputs
    setTimeout(() => {
        const teamContainer = document.querySelector('.team-inputs-container');
        if (teamContainer) {
            teamContainer.scrollTop = 0;
        }
    }, 100);
}

// 6. Create Team Player Input
function createTeamPlayerInput(teamIndex, playerIndex) {
    const div = document.createElement('div');
    div.className = 'team-player-input';
    div.innerHTML = `
        <div class="input-grid">
            <div class="input-group">
                <label>Player ${playerIndex + 1} Name</label>
                <input type="text" class="form-control team-player-name" 
                    placeholder="Team ${teamIndex + 1} Player ${playerIndex + 1}" 
                    value="Team ${teamIndex + 1} Player ${playerIndex + 1}">
            </div>
            <div class="input-group">
                <label>Kills</label>
                <input type="number" class="form-control team-player-kills" 
                    min="0" max="50" placeholder="0" value="${Math.floor(Math.random() * 10)}">
            </div>
            <div class="input-group">
                <label>Damage</label>
                <input type="number" class="form-control team-player-damage" 
                    min="0" max="5000" placeholder="0" value="${Math.floor(Math.random() * 500) + 100}">
            </div>
            <div class="input-group">
                <label>Survival (sec)</label>
                <input type="number" class="form-control team-player-survival" 
                    min="0" max="1800" placeholder="0" value="${Math.floor(Math.random() * 600) + 300}">
            </div>
            <div class="input-group">
                <label>Headshots</label>
                <input type="number" class="form-control team-player-headshots" 
                    min="0" max="50" placeholder="0" value="${Math.floor(Math.random() * 5)}">
            </div>
            <div class="input-group">
                <label>Assists</label>
                <input type="number" class="form-control team-player-assists" 
                    min="0" max="20" placeholder="0" value="${Math.floor(Math.random() * 3)}">
            </div>
        </div>
    `;
    return div;
}

// 7. Add Player Input
function addPlayerInput() {
    const playerInputs = document.getElementById('playerInputs');
    if (!playerInputs) {
        console.error('Player inputs container not found!');
        return;
    }
    
    // Get current number of players
    const currentPlayers = playerInputs.querySelectorAll('.player-input-section').length;
    
    // Create new player input
    const newPlayer = createPlayerInput(currentPlayers);
    playerInputs.appendChild(newPlayer);
    
    console.log('Added player', currentPlayers + 1);
    
    // Update remove buttons visibility
    updateRemoveButtons();
    
    showNotification(`Player ${currentPlayers + 1} added successfully!`, 'success');
}

// 8. Create Player Input
function createPlayerInput(index) {
    const div = document.createElement('div');
    div.className = 'player-input-section fade-in';
    div.innerHTML = `
        <h4><i class="fas fa-user"></i> Player ${index + 1}</h4>
        <div class="input-grid">
            <div class="input-group">
                <label>Player Name</label>
                <input type="text" class="form-control player-name" 
                       placeholder="Player ${index + 1}" value="Player ${index + 1}">
            </div>
            <div class="input-group">
                <label>Kills</label>
                <input type="number" class="form-control player-kills" 
                       min="0" max="50" placeholder="0" value="${Math.floor(Math.random() * 10)}">
            </div>
            <div class="input-group">
                <label>Damage</label>
                <input type="number" class="form-control player-damage" 
                       min="0" max="5000" placeholder="0" value="${Math.floor(Math.random() * 500) + 100}">
            </div>
            <div class="input-group">
                <label>Survival (sec)</label>
                <input type="number" class="form-control player-survival" 
                       min="0" max="1800" placeholder="0" value="${Math.floor(Math.random() * 600) + 300}">
            </div>
            <div class="input-group">
                <label>Headshots</label>
                <input type="number" class="form-control player-headshots" 
                       min="0" max="50" placeholder="0" value="${Math.floor(Math.random() * 5)}">
            </div>
            <div class="input-group">
                <label>Assists</label>
                <input type="number" class="form-control player-assists" 
                       min="0" max="20" placeholder="0" value="${Math.floor(Math.random() * 3)}">
            </div>
        </div>
        <button class="btn btn-danger btn-sm remove-player" onclick="removePlayerInput(this)">
            <i class="fas fa-times"></i> Remove Player
        </button>
    `;
    return div;
}

// 9. Remove Player Input
function removePlayerInput(button) {
    const playerSection = button.closest('.player-input-section');
    if (!playerSection) return;
    
    const playerInputs = document.getElementById('playerInputs');
    const sections = playerInputs.querySelectorAll('.player-input-section');
    
    // Don't remove if it's the only player
    if (sections.length <= 1) {
        showNotification('Cannot remove the only player!', 'error');
        return;
    }
    
    playerSection.remove();
    
    // Renumber remaining players
    renumberPlayers();
    
    // Update remove buttons
    updateRemoveButtons();
    
    showNotification('Player removed successfully!', 'info');
}

// 10. Renumber Players
function renumberPlayers() {
    const playerInputs = document.getElementById('playerInputs');
    const sections = playerInputs.querySelectorAll('.player-input-section');
    
    sections.forEach((section, index) => {
        // Update heading
        const h4 = section.querySelector('h4');
        if (h4) {
            h4.innerHTML = `<i class="fas fa-user"></i> Player ${index + 1}`;
        }
        
        // Update placeholder name if empty
        const nameInput = section.querySelector('.player-name');
        if (nameInput && !nameInput.value) {
            nameInput.placeholder = `Player ${index + 1}`;
        }
    });
}

// 11. Update Remove Buttons Visibility
function updateRemoveButtons() {
    const playerInputs = document.getElementById('playerInputs');
    if (!playerInputs) return;
    
    const sections = playerInputs.querySelectorAll('.player-input-section');
    const removeButtons = playerInputs.querySelectorAll('.remove-player');
    
    // Show remove button only if there's more than 1 player
    if (sections.length > 1) {
        removeButtons.forEach(btn => {
            btn.style.display = 'block';
        });
    } else {
        removeButtons.forEach(btn => {
            btn.style.display = 'none';
        });
    }
}

// 12. Show OCR Processing - UPDATED
function showOCRProcessing() {
    const ocrPreview = document.getElementById('ocrPreview');
    if (ocrPreview) {
        ocrPreview.classList.remove('hidden');
        ocrPreview.innerHTML = `
            <div class="ocr-processing" style="text-align: center; padding: 20px;">
                <div class="spinner"></div>
                <p style="margin-top: 10px;">Uploading and analyzing image...</p>
                <div class="progress" style="margin-top: 20px; height: 6px; background: #e0e0e0; border-radius: 3px;">
                    <div class="progress-bar" style="width: 0%; height: 100%; background: #667eea; border-radius: 3px; transition: width 0.3s;"></div>
                </div>
            </div>
        `;
        
        // Actually process the file
        const fileInput = document.getElementById('statsFile');
        if (fileInput.files.length > 0) {
            processOCRFile(fileInput.files[0]);
        }
    }
}

// NEW: Process OCR file with backend
async function processOCRFile(file) {
    try {
        const formData = new FormData();
        formData.append('statsFile', file);
        formData.append('csrf_token', document.getElementById('csrfToken').value);
        formData.append('match_type', document.querySelector('input[name="matchType"]:checked').value);
        
        // Show upload progress
        const progressBar = document.querySelector('.progress-bar');
        let progress = 0;
        const progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += 10;
                if (progressBar) progressBar.style.width = progress + '%';
            }
        }, 300);
        
        // Send to backend
        const response = await fetch('ocr_backend.php', {
            method: 'POST',
            body: formData
        });
        
        clearInterval(progressInterval);
        if (progressBar) progressBar.style.width = '100%';
        
        const result = await response.json();
        
        if (result.success) {
            updateOCRPreviewWithRealData(result);
            populatePlayersFromOCR(result.players);
        } else {
            showOCRError(result.error || 'OCR processing failed');
        }
        
    } catch (error) {
        console.error('OCR Error:', error);
        showOCRError('Network error: ' + error.message);
    }
}

// 13. Update OCR Preview with Real Data - UPDATED
function updateOCRPreviewWithRealData(ocrResult) {
    const ocrPreview = document.getElementById('ocrPreview');
    if (!ocrPreview) return;
    
    const { players, confidence, source, total_players } = ocrResult;
    
    let html = `
        <div class="ocr-results">
            <div class="ocr-header">
                <h4><i class="fas fa-check-circle text-success"></i> Data Extracted Successfully</h4>
                <div class="ocr-meta">
                    <span class="meta-item">
                        <i class="fas fa-database"></i> Source: ${source}
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-users"></i> Players: ${total_players}
                    </span>
                </div>
            </div>
            
            <div class="confidence-display">
                <div class="confidence-label">OCR Confidence:</div>
                <div class="confidence-value ${confidence > 80 ? 'high' : confidence > 60 ? 'medium' : 'low'}">
                    ${confidence}%
                </div>
                <div class="confidence-bar">
                    <div class="confidence-fill" style="width: ${confidence}%"></div>
                </div>
            </div>
    `;
    
    if (players && players.length > 0) {
        html += `
            <div class="extracted-players">
                <h5><i class="fas fa-list"></i> Extracted Players</h5>
                <div class="players-grid">
        `;
        
        // Show top 5 players
        players.slice(0, 5).forEach((player, index) => {
            html += `
                <div class="player-card">
                    <div class="player-rank">#${index + 1}</div>
                    <div class="player-name">${player.name}</div>
                    <div class="player-stats">
                        <span class="stat"><i class="fas fa-skull"></i> ${player.kills}K</span>
                        <span class="stat"><i class="fas fa-bullseye"></i> ${player.damage}D</span>
                        <span class="stat"><i class="fas fa-clock"></i> ${Math.floor(player.survival/60)}:${(player.survival%60).toString().padStart(2, '0')}</span>
                    </div>
                    <div class="player-rating">
                        <i class="fas fa-star"></i> ${player.rating.toFixed(1)}
                    </div>
                </div>
            `;
        });
        
        if (players.length > 5) {
            html += `<div class="more-players">+${players.length - 5} more players</div>`;
        }
        
        html += `</div></div>`;
    }
    
    html += `
        <div class="ocr-actions">
            <button class="btn btn-success" onclick="useOCRData()">
                <i class="fas fa-robot"></i> Generate AI Prediction
            </button>
            <button class="btn btn-primary" onclick="populatePlayersFromOCR(${JSON.stringify(players).replace(/"/g, '&quot;')})">
                <i class="fas fa-edit"></i> Edit & Verify
            </button>
            <button class="btn btn-outline-secondary" onclick="switchToManualInput()">
                <i class="fas fa-keyboard"></i> Enter Manually
            </button>
        </div>
    </div>`;
    
    ocrPreview.innerHTML = html;
    
    // Store for later use
    window.lastOCRResult = ocrResult;
}

// NEW: Populate form with OCR data
function populatePlayersFromOCR(players) {
    if (!players || players.length === 0) return;
    
    // Switch to manual input tab
    document.querySelector('.method-tab[data-method="manual"]').click();
    
    const playerInputs = document.getElementById('playerInputs');
    if (!playerInputs) return;
    
    // Clear existing inputs
    playerInputs.innerHTML = '';
    
    // Add extracted players
    players.forEach((player, index) => {
        const playerDiv = document.createElement('div');
        playerDiv.className = 'player-input-section fade-in';
        playerDiv.innerHTML = `
            <h4><i class="fas fa-user"></i> ${player.name || `Player ${index + 1}`}</h4>
            <div class="input-grid">
                <div class="input-group">
                    <label>Player Name</label>
                    <input type="text" class="form-control player-name" 
                           value="${player.name || `Player ${index + 1}`}">
                </div>
                <div class="input-group">
                    <label>Kills</label>
                    <input type="number" class="form-control player-kills" 
                           min="0" max="50" value="${player.kills || 0}">
                </div>
                <div class="input-group">
                    <label>Damage</label>
                    <input type="number" class="form-control player-damage" 
                           min="0" max="5000" value="${player.damage || 0}">
                </div>
                <div class="input-group">
                    <label>Survival (sec)</label>
                    <input type="number" class="form-control player-survival" 
                           min="0" max="1800" value="${player.survival || 0}">
                </div>
                <div class="input-group">
                    <label>Headshots</label>
                    <input type="number" class="form-control player-headshots" 
                           min="0" max="50" value="${player.headshots || 0}">
                </div>
                <div class="input-group">
                    <label>Assists</label>
                    <input type="number" class="form-control player-assists" 
                           min="0" max="20" value="${player.assists || 0}">
                </div>
            </div>
            <button class="btn btn-danger btn-sm remove-player" onclick="removePlayerInput(this)">
                <i class="fas fa-times"></i> Remove Player
            </button>
        `;
        playerInputs.appendChild(playerDiv);
    });
    
    // Update remove buttons
    updateRemoveButtons();
    
    showNotification(`${players.length} players loaded from OCR! Please verify data.`, 'success');
}

// NEW: Show OCR Error
function showOCRError(message) {
    const ocrPreview = document.getElementById('ocrPreview');
    if (!ocrPreview) return;
    
    ocrPreview.innerHTML = `
        <div class="ocr-error">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h4>OCR Processing Failed</h4>
            <p>${message}</p>
            <div class="error-help">
                <p><strong>Tips for better OCR results:</strong></p>
                <ul>
                    <li>Use clear, high-contrast screenshots</li>
                    <li>Ensure text is readable and not blurry</li>
                    <li>Crop to just the scoreboard/statistics area</li>
                    <li>Use PNG format for best quality</li>
                </ul>
            </div>
            <div class="error-actions">
                <button class="btn btn-primary" onclick="switchToManualInput()">
                    <i class="fas fa-keyboard"></i> Enter Data Manually
                </button>
                <button class="btn btn-secondary" onclick="retryOCR()">
                    <i class="fas fa-redo"></i> Try Another Image
                </button>
            </div>
        </div>
    `;
}

// NEW: Retry OCR
function retryOCR() {
    document.getElementById('ocrPreview').classList.add('hidden');
    document.getElementById('statsFile').value = '';
}

// 15. Use OCR Data - UPDATED
function useOCRData() {
    if (!window.lastOCRResult || !window.lastOCRResult.players) {
        showNotification('No OCR data available. Please upload an image first.', 'error');
        return;
    }
    
    // Populate form and immediately process
    populatePlayersFromOCR(window.lastOCRResult.players);
    
    // Small delay to ensure form is populated
    setTimeout(() => {
        if (validateInputs()) {
            processData();
        }
    }, 500);
}

// Add CSS for new OCR elements
function addOCRStyles() {
    if (!document.querySelector('#ocr-styles')) {
        const style = document.createElement('style');
        style.id = 'ocr-styles';
        style.textContent = `
            .ocr-results {
                padding: 20px;
            }
            .ocr-header {
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid #e0e0e0;
            }
            .ocr-meta {
                display: flex;
                gap: 15px;
                margin-top: 10px;
                font-size: 14px;
                color: #666;
            }
            .meta-item {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .confidence-display {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            .confidence-label {
                font-size: 14px;
                color: #666;
                margin-bottom: 5px;
            }
            .confidence-value {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .confidence-value.high { color: #4CAF50; }
            .confidence-value.medium { color: #FF9800; }
            .confidence-value.low { color: #f44336; }
            .confidence-bar {
                height: 8px;
                background: #e0e0e0;
                border-radius: 4px;
                overflow: hidden;
            }
            .confidence-fill {
                height: 100%;
                background: linear-gradient(90deg, #667eea, #764ba2);
                transition: width 1s ease;
            }
            .extracted-players {
                margin: 25px 0;
            }
            .players-grid {
                display: grid;
                gap: 10px;
                margin-top: 15px;
            }
            .player-card {
                display: flex;
                align-items: center;
                padding: 12px 15px;
                background: white;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                gap: 15px;
            }
            .player-rank {
                background: #667eea;
                color: white;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
            }
            .player-name {
                flex: 1;
                font-weight: 500;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .player-stats {
                display: flex;
                gap: 15px;
            }
            .player-stats .stat {
                display: flex;
                align-items: center;
                gap: 5px;
                font-size: 14px;
                color: #666;
            }
            .player-rating {
                background: #FFD700;
                color: #333;
                padding: 4px 10px;
                border-radius: 12px;
                font-weight: bold;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .more-players {
                text-align: center;
                padding: 10px;
                color: #666;
                font-size: 14px;
            }
            .ocr-actions {
                display: flex;
                gap: 10px;
                margin-top: 25px;
            }
            .ocr-error {
                text-align: center;
                padding: 30px 20px;
            }
            .ocr-error .error-icon {
                font-size: 48px;
                color: #f44336;
                margin-bottom: 20px;
            }
            .error-help {
                text-align: left;
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                border-radius: 8px;
                padding: 15px;
                margin: 20px 0;
            }
            .error-help ul {
                margin: 10px 0 0 20px;
            }
            .error-actions {
                display: flex;
                gap: 10px;
                justify-content: center;
                margin-top: 20px;
            }
        `;
        document.head.appendChild(style);
    }
}

// Add OCR styles on page load
document.addEventListener('DOMContentLoaded', function() {
    addOCRStyles();
});

// 14. Switch to Manual Input
function switchToManualInput() {
    // Switch to manual input tab
    document.querySelector('.method-tab[data-method="manual"]').click();
    showNotification('Please enter your data manually for accurate predictions.', 'info');
}

// 15. Use OCR Data
function useOCRData() {
    showNotification('Using OCR data. Please verify the extracted information.', 'info');
}

// 16. Import from API
function importFromAPI() {
    const playerIds = document.getElementById('playerIds')?.value;
    if (!playerIds || playerIds.trim() === '') {
        showNotification('Please enter player IDs or match codes', 'error');
        return;
    }
    
    showNotification('API import would fetch real data here. Enter data manually for now.', 'info');
}

// 17. Process Data
function processData() {
    console.log('Processing data...');
    
    // Validate inputs
    if (!validateInputs()) {
        return;
    }
    
    // Show processing section
    const processingSection = document.getElementById('processingSection');
    if (processingSection) {
        processingSection.classList.remove('hidden');
        
        // Hide other sections
        document.getElementById('resultsSection')?.classList.add('hidden');
        document.getElementById('errorSection')?.classList.add('hidden');
        document.getElementById('newPredictionSection')?.classList.add('hidden');
        
        // Start processing animation
        startProcessingAnimation();
    }
}

// 18. Validate Inputs - IMPROVED
function validateInputs() {
    const matchType = document.querySelector('input[name="matchType"]:checked').value;
    let hasErrors = false;
    
    if (matchType === 'solo') {
        // Validate solo players
        const playerInputs = document.querySelectorAll('.player-input-section');
        
        if (playerInputs.length < 2) {
            showNotification('Please add at least 2 players for solo prediction.', 'error');
            return false;
        }
        
        playerInputs.forEach((input, index) => {
            const name = input.querySelector('.player-name')?.value.trim();
            const kills = input.querySelector('.player-kills')?.value;
            const damage = input.querySelector('.player-damage')?.value;
            
            if (!name) {
                showNotification(`Please enter name for Player ${index + 1}`, 'error');
                hasErrors = true;
            }
            
            if (kills === '' || damage === '') {
                showNotification(`Please fill all required fields for Player ${index + 1}`, 'error');
                hasErrors = true;
            }
        });
    } else {
        // Validate teams
        const teamSections = document.querySelectorAll('.team-section');
        
        if (teamSections.length < 2) {
            showNotification('Please generate at least 2 teams.', 'error');
            return false;
        }
        
        teamSections.forEach((team, teamIndex) => {
            const playerInputs = team.querySelectorAll('.team-player-input');
            
            playerInputs.forEach((input, playerIndex) => {
                const name = input.querySelector('.team-player-name')?.value.trim();
                const kills = input.querySelector('.team-player-kills')?.value;
                
                if (!name) {
                    showNotification(`Please enter name for Player ${playerIndex + 1} in Team ${teamIndex + 1}`, 'error');
                    hasErrors = true;
                }
                
                if (kills === '') {
                    showNotification(`Please enter kills for Player ${playerIndex + 1} in Team ${teamIndex + 1}`, 'error');
                    hasErrors = true;
                }
            });
        });
    }
    
    return !hasErrors;
}

// 19. Start Processing Animation
function startProcessingAnimation() {
    let step = 1;
    const totalSteps = 4;
    
    const interval = setInterval(() => {
        // Update current step
        const currentStep = document.querySelector(`.step[data-step="${step}"]`);
        if (currentStep) {
            currentStep.classList.add('active');
            const status = currentStep.querySelector('.step-status');
            if (status) {
                status.innerHTML = '<i class="fas fa-check"></i>';
            }
        }
        
        // Update progress bar
        const progress = (step / totalSteps) * 100;
        const progressBar = document.getElementById('progressBar');
        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }
        
        step++;
        
        if (step > totalSteps) {
            clearInterval(interval);
            setTimeout(() => {
                showResults();
            }, 500);
        }
    }, 1000);
    
    // Start timer
    const startTime = Date.now();
    const timeElement = document.getElementById('processingTime');
    const timerInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        if (timeElement) {
            timeElement.textContent = `${elapsed}s`;
        }
    }, 1000);
    
    // Store interval IDs for cleanup
    window.processingInterval = interval;
    window.timerInterval = timerInterval;
}

// 20. Show Results
function showResults() {
    // Clear intervals
    if (window.processingInterval) clearInterval(window.processingInterval);
    if (window.timerInterval) clearInterval(window.timerInterval);
    
    // Hide processing section
    const processingSection = document.getElementById('processingSection');
    if (processingSection) {
        processingSection.classList.add('hidden');
    }
    
    // Show results section
    const resultsSection = document.getElementById('resultsSection');
    const newPredictionSection = document.getElementById('newPredictionSection');
    if (resultsSection) {
        resultsSection.classList.remove('hidden');
        
        // Update with actual data
        updateResults();
        
        // Show new prediction button
        if (newPredictionSection) {
            setTimeout(() => {
                newPredictionSection.classList.remove('hidden');
            }, 1000);
        }
        
        // Scroll to results
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }
}

// 21. Update Results - IMPROVED for all modes
function updateResults() {
    // Get match type
    const matchType = document.querySelector('input[name="matchType"]:checked').value;
    
    if (matchType === 'solo') {
        updateSoloResults();
    } else {
        updateTeamResults();
    }
    
    // Update result date
    const resultDate = document.getElementById('resultDate');
    if (resultDate) {
        resultDate.textContent = new Date().toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// 22. Update Solo Results
function updateSoloResults() {
    const playerInputs = document.querySelectorAll('.player-input-section');
    const players = [];
    
    // Collect player data
    playerInputs.forEach((input, index) => {
        const name = input.querySelector('.player-name')?.value.trim() || `Player ${index + 1}`;
        const kills = parseInt(input.querySelector('.player-kills')?.value) || 0;
        const damage = parseInt(input.querySelector('.player-damage')?.value) || 0;
        const survival = parseInt(input.querySelector('.player-survival')?.value) || 0;
        const headshots = parseInt(input.querySelector('.player-headshots')?.value) || 0;
        const assists = parseInt(input.querySelector('.player-assists')?.value) || 0;
        
        const rating = calculatePlayerRating(kills, damage, survival, headshots, assists);
        const kd = kills > 0 ? (kills / Math.max(1, deathEstimate(kills, damage))) : 0;
        
        players.push({
            name: name,
            kills: kills,
            damage: damage,
            survival: survival,
            headshots: headshots,
            assists: assists,
            rating: rating,
            kd: kd
        });
    });
    
    // Sort by rating (highest first)
    players.sort((a, b) => b.rating - a.rating);
    
    // Update winner info
    if (players.length > 0) {
        const winner = players[0];
        document.getElementById('winnerName').textContent = winner.name;
        document.getElementById('winnerKills').textContent = winner.kills;
        document.getElementById('winnerDamage').textContent = winner.damage;
        document.getElementById('winnerSurvival').textContent = `${Math.floor(winner.survival / 60)}:${(winner.survival % 60).toString().padStart(2, '0')}`;
        document.getElementById('winnerRating').textContent = winner.rating.toFixed(1);
        
        // Calculate confidence
        const confidence = calculateConfidence(players);
        document.getElementById('confidenceValueMain').textContent = `${confidence}%`;
        document.getElementById('predictionAccuracy').textContent = `${confidence}%`;
        
        // Update performance cards
        document.getElementById('topPerformerName').textContent = winner.name;
        document.getElementById('topPerformerKD').textContent = winner.kd.toFixed(2);
        document.getElementById('topPerformerDMG').textContent = winner.damage;
        document.getElementById('topPerformerRating').textContent = winner.rating.toFixed(1);
        
        if (players.length > 1) {
            const weakLink = players[players.length - 1];
            document.getElementById('weakLinkName').textContent = weakLink.name;
            document.getElementById('weakLinkKD').textContent = weakLink.kd.toFixed(2);
            document.getElementById('weakLinkDMG').textContent = weakLink.damage;
            document.getElementById('weakLinkRating').textContent = weakLink.rating.toFixed(1);
        }
        
        // Hide team members section
        document.getElementById('teamMembers')?.classList.add('hidden');
    }
}

// 23. Update Team Results - NEW IMPLEMENTATION
function updateTeamResults() {
    const teamSections = document.querySelectorAll('.team-section');
    const matchType = document.querySelector('input[name="matchType"]:checked').value;
    const teams = [];
    
    // Collect team data
    teamSections.forEach((teamSection, teamIndex) => {
        const playerInputs = teamSection.querySelectorAll('.team-player-input');
        const teamPlayers = [];
        let teamTotalKills = 0;
        let teamTotalDamage = 0;
        let teamTotalSurvival = 0;
        let teamTotalHeadshots = 0;
        let teamTotalAssists = 0;
        
        playerInputs.forEach((input, playerIndex) => {
            const name = input.querySelector('.team-player-name')?.value.trim() || `Team ${teamIndex + 1} Player ${playerIndex + 1}`;
            const kills = parseInt(input.querySelector('.team-player-kills')?.value) || 0;
            const damage = parseInt(input.querySelector('.team-player-damage')?.value) || 0;
            const survival = parseInt(input.querySelector('.team-player-survival')?.value) || 0;
            const headshots = parseInt(input.querySelector('.team-player-headshots')?.value) || 0;
            const assists = parseInt(input.querySelector('.team-player-assists')?.value) || 0;
            
            const rating = calculatePlayerRating(kills, damage, survival, headshots, assists);
            
            teamPlayers.push({
                name: name,
                kills: kills,
                damage: damage,
                survival: survival,
                headshots: headshots,
                assists: assists,
                rating: rating
            });
            
            teamTotalKills += kills;
            teamTotalDamage += damage;
            teamTotalSurvival += survival;
            teamTotalHeadshots += headshots;
            teamTotalAssists += assists;
        });
        
        // Calculate team rating (average of players + team bonuses)
        const avgPlayerRating = teamPlayers.reduce((sum, player) => sum + player.rating, 0) / teamPlayers.length;
        const teamRating = calculateTeamRating(avgPlayerRating, teamTotalKills, teamTotalDamage, teamTotalSurvival);
        
        teams.push({
            name: `Team ${teamIndex + 1}`,
            players: teamPlayers,
            totalKills: teamTotalKills,
            totalDamage: teamTotalDamage,
            avgSurvival: Math.floor(teamTotalSurvival / teamPlayers.length),
            teamRating: teamRating,
            synergy: calculateTeamSynergy(teamPlayers)
        });
    });
    
    // Sort teams by team rating
    teams.sort((a, b) => b.teamRating - a.teamRating);
    
    // Update winner info
    if (teams.length > 0) {
        const winner = teams[0];
        document.getElementById('winnerName').textContent = winner.name;
        document.getElementById('winnerKills').textContent = winner.totalKills;
        document.getElementById('winnerDamage').textContent = winner.totalDamage;
        document.getElementById('winnerSurvival').textContent = `${Math.floor(winner.avgSurvival / 60)}:${(winner.avgSurvival % 60).toString().padStart(2, '0')}`;
        document.getElementById('winnerRating').textContent = winner.teamRating.toFixed(1);
        
        // Calculate confidence
        const confidence = calculateTeamConfidence(teams);
        document.getElementById('confidenceValueMain').textContent = `${confidence}%`;
        document.getElementById('predictionAccuracy').textContent = `${confidence}%`;
        
        // Update performance cards
        document.getElementById('topPerformerName').textContent = winner.players[0].name;
        document.getElementById('topPerformerKD').textContent = (winner.players[0].kills / Math.max(1, 3)).toFixed(2);
        document.getElementById('topPerformerDMG').textContent = winner.players[0].damage;
        document.getElementById('topPerformerRating').textContent = winner.players[0].rating.toFixed(1);
        
        // Find weakest player across all teams
        const allPlayers = teams.flatMap(team => team.players);
        allPlayers.sort((a, b) => a.rating - b.rating);
        
        if (allPlayers.length > 0) {
            const weakLink = allPlayers[0];
            document.getElementById('weakLinkName').textContent = weakLink.name;
            document.getElementById('weakLinkKD').textContent = (weakLink.kills / Math.max(1, 5)).toFixed(2);
            document.getElementById('weakLinkDMG').textContent = weakLink.damage;
            document.getElementById('weakLinkRating').textContent = weakLink.rating.toFixed(1);
        }
        
        // Update synergy
        document.getElementById('synergyScore').textContent = winner.synergy.toFixed(1);
        document.getElementById('synergyMeter').style.width = `${winner.synergy * 10}%`;
        document.getElementById('synergyDesc').textContent = getSynergyDescription(winner.synergy);
        
        // Show team members section
        const teamMembers = document.getElementById('teamMembers');
        if (teamMembers) {
            teamMembers.classList.remove('hidden');
            
            // Update team performance grid
            const performanceGrid = document.getElementById('teamPerformanceGrid');
            if (performanceGrid) {
                performanceGrid.innerHTML = '';
                
                teams.slice(0, 3).forEach((team, index) => {
                    const teamCard = document.createElement('div');
                    teamCard.className = `team-performance-card ${index === 0 ? 'winner' : ''}`;
                    teamCard.innerHTML = `
                        <div class="team-rank">${index + 1}</div>
                        <div class="team-name">${team.name}</div>
                        <div class="team-stats">
                            <span class="stat">Kills: ${team.totalKills}</span>
                            <span class="stat">DMG: ${team.totalDamage}</span>
                            <span class="stat">Rating: ${team.teamRating.toFixed(1)}</span>
                        </div>
                    `;
                    performanceGrid.appendChild(teamCard);
                });
            }
        }
    }
}

// 24. Calculate Player Rating
function calculatePlayerRating(kills, damage, survival, headshots, assists) {
    const killScore = Math.min(10, kills * 0.8);
    const damageScore = Math.min(10, damage / 100);
    const survivalScore = Math.min(10, survival / 180);
    const headshotScore = Math.min(5, headshots * 0.8);
    const assistScore = Math.min(5, assists * 0.6);
    
    const rating = (
        killScore * 0.30 + 
        damageScore * 0.25 + 
        survivalScore * 0.25 + 
        headshotScore * 0.10 +
        assistScore * 0.10
    );
    
    return Math.min(10, Math.max(0, rating));
}

// 25. Calculate Team Rating
function calculateTeamRating(avgPlayerRating, totalKills, totalDamage, totalSurvival) {
    const killScore = Math.min(10, totalKills * 0.4);
    const damageScore = Math.min(10, totalDamage / 300);
    const survivalScore = Math.min(10, totalSurvival / 720);
    
    const teamRating = (
        avgPlayerRating * 0.50 + 
        killScore * 0.20 + 
        damageScore * 0.20 + 
        survivalScore * 0.10
    );
    
    return Math.min(10, Math.max(0, teamRating));
}

// 26. Calculate Team Synergy
function calculateTeamSynergy(players) {
    if (players.length < 2) return 5.0;
    
    const avgRating = players.reduce((sum, player) => sum + player.rating, 0) / players.length;
    const ratingStdDev = Math.sqrt(
        players.reduce((sum, player) => sum + Math.pow(player.rating - avgRating, 2), 0) / players.length
    );
    
    // Lower std deviation = better synergy
    const synergy = Math.max(0, 10 - (ratingStdDev * 3));
    return Math.min(10, synergy);
}

// 27. Get Synergy Description
function getSynergyDescription(synergy) {
    if (synergy >= 8) return 'Excellent team coordination';
    if (synergy >= 6) return 'Good team synergy';
    if (synergy >= 4) return 'Average coordination';
    return 'Needs better teamwork';
}

// 28. Calculate Death Estimate (for K/D calculation)
function deathEstimate(kills, damage) {
    // Simple estimation based on average damage per kill
    const avgDamagePerKill = 150;
    return Math.max(1, Math.floor(damage / avgDamagePerKill));
}

// 29. Calculate Confidence
function calculateConfidence(players) {
    if (players.length < 2) return 85;
    
    const winnerRating = players[0].rating;
    const runnerUpRating = players[1].rating;
    const ratingDiff = winnerRating - runnerUpRating;
    
    let confidence = 75 + (ratingDiff * 15);
    confidence = Math.min(98, Math.max(60, confidence));
    
    return Math.round(confidence);
}

// 30. Calculate Team Confidence
function calculateTeamConfidence(teams) {
    if (teams.length < 2) return 85;
    
    const winnerRating = teams[0].teamRating;
    const runnerUpRating = teams[1].teamRating;
    const ratingDiff = winnerRating - runnerUpRating;
    
    let confidence = 70 + (ratingDiff * 20);
    confidence = Math.min(97, Math.max(55, confidence));
    
    return Math.round(confidence);
}

// 31. Reset Form
function resetForm() {
    console.log('Resetting form...');
    
    // Hide results and error sections
    document.getElementById('resultsSection')?.classList.add('hidden');
    document.getElementById('errorSection')?.classList.add('hidden');
    document.getElementById('ocrPreview')?.classList.add('hidden');
    document.getElementById('newPredictionSection')?.classList.add('hidden');
    
    // Reset file input
    const statsFile = document.getElementById('statsFile');
    if (statsFile) statsFile.value = '';
    
    // Switch to upload tab
    document.querySelector('.method-tab[data-method="upload"]').click();
    
    // Reset match type to solo
    document.querySelector('input[name="matchType"][value="solo"]').checked = true;
    updateInputMode();
    
    // Reset player inputs - keep only one empty player
    const playerInputs = document.getElementById('playerInputs');
    if (playerInputs) {
        playerInputs.innerHTML = `
            <div class="player-input-section">
                <h4><i class="fas fa-user"></i> Player 1</h4>
                <div class="input-grid">
                    <div class="input-group">
                        <label>Player Name</label>
                        <input type="text" class="form-control player-name" 
                               placeholder="Enter player name" value="Player 1">
                    </div>
                    <div class="input-group">
                        <label>Kills</label>
                        <input type="number" class="form-control player-kills" 
                               min="0" max="50" placeholder="0" value="5">
                    </div>
                    <div class="input-group">
                        <label>Damage</label>
                        <input type="number" class="form-control player-damage" 
                               min="0" max="5000" placeholder="0" value="250">
                    </div>
                    <div class="input-group">
                        <label>Survival (sec)</label>
                        <input type="number" class="form-control player-survival" 
                               min="0" max="1800" placeholder="0" value="450">
                    </div>
                    <div class="input-group">
                        <label>Headshots</label>
                        <input type="number" class="form-control player-headshots" 
                               min="0" max="50" placeholder="0" value="2">
                    </div>
                    <div class="input-group">
                        <label>Assists</label>
                        <input type="number" class="form-control player-assists" 
                               min="0" max="20" placeholder="0" value="0">
                    </div>
                </div>
                <button class="btn btn-danger btn-sm remove-player" style="display: none;" 
                        onclick="removePlayerInput(this)">
                    <i class="fas fa-times"></i> Remove Player
                </button>
            </div>
        `;
    }
    
    // Clear team inputs
    const teamInputs = document.getElementById('teamInputs');
    if (teamInputs) {
        teamInputs.innerHTML = '';
    }
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    showNotification('Form reset successfully!', 'info');
}

// 32. Retry Processing
function retryProcessing() {
    document.getElementById('errorSection')?.classList.add('hidden');
    processData();
}

// 33. Show Notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Initialize remove buttons on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
    
    // Add notification styles
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border-radius: 8px;
                padding: 15px 20px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                z-index: 9999;
                transform: translateX(100%);
                opacity: 0;
                transition: transform 0.3s ease, opacity 0.3s ease;
                max-width: 350px;
            }
            
            .notification.show {
                transform: translateX(0);
                opacity: 1;
            }
            
            .notification-success {
                border-left: 4px solid #4CAF50;
            }
            
            .notification-error {
                border-left: 4px solid #f44336;
            }
            
            .notification-info {
                border-left: 4px solid #2196F3;
            }
            
            .notification-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .notification-content i {
                font-size: 18px;
            }
            
            .notification-success .notification-content i {
                color: #4CAF50;
            }
            
            .notification-error .notification-content i {
                color: #f44336;
            }
            
            .notification-info .notification-content i {
                color: #2196F3;
            }
            
            /* Team performance grid */
            .team-performance-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }
            
            .team-performance-card {
                padding: 15px;
                border-radius: 8px;
                background: #f5f5f5;
                position: relative;
            }
            
            .team-performance-card.winner {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: black;
            }
            
            .team-rank {
                position: absolute;
                top: 10px;
                right: 10px;
                font-size: 24px;
                font-weight: bold;
            }
            
            .team-name {
                font-weight: bold;
                margin-bottom: 10px;
                font-size: 16px;
            }
            
            .team-stats {
                display: flex;
                flex-direction: column;
                gap: 5px;
                font-size: 14px;
            }
        `;
        document.head.appendChild(style);
    }
});

// Auto-resize textareas if you have them
document.addEventListener('input', function(e) {
    if (e.target.tagName === 'TEXTAREA') {
        e.target.style.height = 'auto';
        e.target.style.height = (e.target.scrollHeight) + 'px';
    }
});
    </script>
</body>
</html>
<?php
// Include footer
include_once('footer.php');
?>