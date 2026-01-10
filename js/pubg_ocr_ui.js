class PUBGOCRExtractorUI {
    constructor() {
        this.uploadZone = document.getElementById('uploadZone');
        this.ocrPreview = document.getElementById('ocrPreview');
        this.statsDisplay = document.getElementById('statsDisplay');
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.createStatsTemplate();
    }
    
    setupEventListeners() {
        // Drag and drop
        this.uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.uploadZone.classList.add('dragover');
        });
        
        this.uploadZone.addEventListener('dragleave', () => {
            this.uploadZone.classList.remove('dragover');
        });
        
        this.uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            this.uploadZone.classList.remove('dragover');
            this.handleFile(e.dataTransfer.files[0]);
        });
        
        // File input
        document.getElementById('browseBtn').addEventListener('click', () => {
            document.getElementById('statsFile').click();
        });
        
        document.getElementById('statsFile').addEventListener('change', (e) => {
            if (e.target.files[0]) {
                this.handleFile(e.target.files[0]);
            }
        });
    }
    
    async handleFile(file) {
        try {
            this.showLoading();
            
            const formData = new FormData();
            formData.append('statsFile', file);
            formData.append('csrf_token', document.getElementById('csrfToken').value);
            
            const response = await fetch('ocr_pubg.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.displayExtractedStats(result);
                this.enablePrediction(result.player_data);
            } else {
                this.showError(result.error);
            }
            
        } catch (error) {
            this.showError('Upload failed: ' + error.message);
        }
    }
    
    showLoading() {
        this.ocrPreview.classList.remove('hidden');
        this.ocrPreview.innerHTML = `
            <div class="ocr-processing pubg-loading">
                <div class="loading-icon">
                    <i class="fas fa-crosshairs fa-spin"></i>
                </div>
                <h4>Analyzing PUBG Career Stats...</h4>
                <p>Extracting K/D ratio, damage, accuracy, and other performance metrics</p>
                <div class="loading-steps">
                    <div class="step active">Reading image</div>
                    <div class="step">Extracting statistics</div>
                    <div class="step">Calculating metrics</div>
                    <div class="step">Preparing prediction</div>
                </div>
            </div>
        `;
    }
    
    displayExtractedStats(result) {
        const stats = result.stats;
        const player = result.player_data;
        
        let html = `
            <div class="pubg-results">
                <div class="result-header">
                    <h4><i class="fas fa-user-circle"></i> PUBG Career Analysis</h4>
                    <div class="confidence-badge ${parseInt(result.confidence) > 70 ? 'high' : 'medium'}">
                        ${result.confidence} Confidence
                    </div>
                </div>
                
                <div class="player-summary">
                    <div class="summary-card">
                        <div class="card-title">Overall Rating</div>
                        <div class="card-value rating-${Math.floor(player.rating)}">
                            ${player.rating ? player.rating.toFixed(1) : 'N/A'}/10
                        </div>
                        <div class="card-sub">Based on career stats</div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="card-title">Matches Played</div>
                        <div class="card-value">${stats.matches_played || 'N/A'}</div>
                        <div class="card-sub">Experience Level</div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="card-title">Win Rate</div>
                        <div class="card-value">${stats.win_rate ? stats.win_rate.toFixed(1) + '%' : 'N/A'}</div>
                        <div class="card-sub">Victory Frequency</div>
                    </div>
                </div>
        `;
        
        // Key Performance Metrics
        html += `
            <div class="performance-section">
                <h5><i class="fas fa-chart-line"></i> Key Performance Metrics</h5>
                <div class="metrics-grid">
        `;
        
        const keyMetrics = [
            { key: 'kd_ratio', label: 'K/D Ratio', icon: 'fa-skull', format: v => v.toFixed(2) },
            { key: 'avg_damage', label: 'Avg Damage', icon: 'fa-bullseye', format: v => Math.round(v) },
            { key: 'headshot_rate', label: 'HS Rate', icon: 'fa-crosshairs', format: v => v.toFixed(1) + '%' },
            { key: 'accuracy', label: 'Accuracy', icon: 'fa-bullseye', format: v => v.toFixed(1) + '%' },
            { key: 'kills', label: 'Total Kills', icon: 'fa-trophy', format: v => v.toLocaleString() },
            { key: 'total_damage', label: 'Total Damage', icon: 'fa-fire', format: v => this.formatNumber(v) }
        ];
        
        keyMetrics.forEach(metric => {
            if (stats[metric.key]) {
                html += `
                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas ${metric.icon}"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">${metric.label}</div>
                            <div class="metric-value">${metric.format(stats[metric.key])}</div>
                        </div>
                    </div>
                `;
            }
        });
        
        html += `</div></div>`;
        
        // Estimated Match Performance
        if (player.estimated_performance) {
            const est = player.estimated_performance;
            html += `
                <div class="prediction-section">
                    <h5><i class="fas fa-robot"></i> Estimated Next Match Performance</h5>
                    <div class="prediction-grid">
                        <div class="pred-card">
                            <div class="pred-icon"><i class="fas fa-skull"></i></div>
                            <div class="pred-content">
                                <div class="pred-label">Estimated Kills</div>
                                <div class="pred-value">${est.estimated_kills.toFixed(1)}</div>
                            </div>
                        </div>
                        <div class="pred-card">
                            <div class="pred-icon"><i class="fas fa-bullseye"></i></div>
                            <div class="pred-content">
                                <div class="pred-label">Estimated Damage</div>
                                <div class="pred-value">${Math.round(est.estimated_damage)}</div>
                            </div>
                        </div>
                        <div class="pred-card">
                            <div class="pred-icon"><i class="fas fa-clock"></i></div>
                            <div class="pred-content">
                                <div class="pred-label">Survival Time</div>
                                <div class="pred-value">${Math.floor(est.estimated_survival/60)}:${(est.estimated_survival%60).toString().padStart(2, '0')}</div>
                            </div>
                        </div>
                        <div class="pred-card">
                            <div class="pred-icon"><i class="fas fa-crosshairs"></i></div>
                            <div class="pred-content">
                                <div class="pred-label">Headshot %</div>
                                <div class="pred-value">${est.estimated_headshots.toFixed(1)}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Action Buttons
        html += `
            <div class="ocr-actions">
                <button class="btn btn-success btn-lg" onclick="generateCareerPrediction()" 
                        ${!player.prediction_ready ? 'disabled' : ''}>
                    <i class="fas fa-robot"></i> Generate AI Prediction
                </button>
                <button class="btn btn-secondary" onclick="compareWithOtherPlayers()">
                    <i class="fas fa-users"></i> Compare Stats
                </button>
                <button class="btn btn-outline-secondary" onclick="enterManualStats()">
                    <i class="fas fa-edit"></i> Adjust Manually
                </button>
            </div>
        </div>`;
        
        this.ocrPreview.innerHTML = html;
    }
    
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toLocaleString();
    }
    
    enablePrediction(playerData) {
        // Store extracted data for prediction
        window.extractedPlayerData = playerData;
        
        // Enable prediction button
        const predictBtn = document.getElementById('processBtn');
        if (predictBtn) {
            predictBtn.disabled = !playerData.prediction_ready;
            if (playerData.prediction_ready) {
                predictBtn.innerHTML = `<i class="fas fa-bolt"></i> Predict Using Career Stats`;
                predictBtn.classList.add('btn-success');
            }
        }
    }
    
    createStatsTemplate() {
        // CSS for PUBG-specific styling
        const style = document.createElement('style');
        style.textContent = `
            .pubg-loading {
                text-align: center;
                padding: 40px 20px;
            }
            .pubg-loading .loading-icon {
                font-size: 48px;
                color: #FF9900;
                margin-bottom: 20px;
            }
            .loading-steps {
                display: flex;
                justify-content: center;
                gap: 20px;
                margin-top: 30px;
            }
            .loading-steps .step {
                padding: 10px 15px;
                background: #f0f0f0;
                border-radius: 20px;
                opacity: 0.5;
            }
            .loading-steps .step.active {
                background: #FF9900;
                color: white;
                opacity: 1;
            }
            .pubg-results {
                padding: 20px;
            }
            .result-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 25px;
                padding-bottom: 15px;
                border-bottom: 2px solid #f0f0f0;
            }
            .confidence-badge {
                padding: 5px 15px;
                border-radius: 20px;
                font-weight: bold;
                font-size: 14px;
            }
            .confidence-badge.high {
                background: #4CAF50;
                color: white;
            }
            .confidence-badge.medium {
                background: #FF9800;
                color: white;
            }
            .player-summary {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
                margin-bottom: 25px;
            }
            .summary-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                border-radius: 10px;
                text-align: center;
            }
            .summary-card .card-title {
                font-size: 14px;
                opacity: 0.9;
                margin-bottom: 5px;
            }
            .summary-card .card-value {
                font-size: 28px;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .summary-card .card-sub {
                font-size: 12px;
                opacity: 0.8;
            }
            .rating-8, .rating-9, .rating-10 { color: #4CAF50; }
            .rating-6, .rating-7 { color: #FF9800; }
            .rating-0, .rating-1, .rating-2, .rating-3, .rating-4, .rating-5 { color: #f44336; }
            .metrics-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin: 15px 0;
            }
            @media (min-width: 768px) {
                .metrics-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
            }
            .metric-card {
                display: flex;
                align-items: center;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
                border-left: 4px solid #667eea;
            }
            .metric-icon {
                font-size: 24px;
                color: #667eea;
                margin-right: 15px;
                width: 40px;
                text-align: center;
            }
            .metric-label {
                font-size: 12px;
                color: #666;
                margin-bottom: 5px;
            }
            .metric-value {
                font-size: 18px;
                font-weight: bold;
                color: #333;
            }
            .prediction-section {
                margin: 25px 0;
                padding: 20px;
                background: #f0f7ff;
                border-radius: 10px;
                border: 1px solid #cce5ff;
            }
            .prediction-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin-top: 15px;
            }
            @media (min-width: 768px) {
                .prediction-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
            }
            .pred-card {
                text-align: center;
                padding: 15px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .pred-icon {
                font-size: 24px;
                color: #667eea;
                margin-bottom: 10px;
            }
            .pred-label {
                font-size: 12px;
                color: #666;
                margin-bottom: 5px;
            }
            .pred-value {
                font-size: 18px;
                font-weight: bold;
                color: #333;
            }
            .ocr-actions {
                display: flex;
                gap: 10px;
                margin-top: 25px;
                flex-wrap: wrap;
            }
        `;
        document.head.appendChild(style);
    }
    
    showError(message) {
        this.ocrPreview.innerHTML = `
            <div class="ocr-error">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4>Failed to Analyze Image</h4>
                <p>${message}</p>
                <p class="error-help">Please ensure you're uploading a clear PUBG Mobile career stats screenshot.</p>
                <div class="error-actions">
                    <button class="btn btn-primary" onclick="switchToManualInput()">
                        <i class="fas fa-keyboard"></i> Enter Stats Manually
                    </button>
                    <button class="btn btn-secondary" onclick="retryUpload()">
                        <i class="fas fa-redo"></i> Try Another Image
                    </button>
                </div>
            </div>
        `;
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    const pubgOCR = new PUBGOCRExtractorUI();
});