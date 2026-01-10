<?php
// prediction.php
// Include header
include_once('header.php');

// Database connection
include_once('config.php');
echo '<div class="prediction-page">';
?>
<style>
    /* prediction.css - Infiknight AI Predictor Styles */
/* prediction-styles.css - Infiknight Prediction Page Specific Styles */

/* Infiknight Specific Styles */
.prediction-page .infiknight-container {
    max-width: 100%;
    margin: 0 auto;
    padding: 20px;
}

.prediction-page .hero-section {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 20px;
    background: linear-gradient(135deg, #ffffffff 0%, #764ba2 100%);
    border-radius: 15px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.prediction-page .hero-section h1 {
    font-size: 3rem;
    margin-bottom: 10px;
    font-weight: 800;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.prediction-page .subtitle {
    font-size: 1.2rem;
    color : rgba(255, 255, 255, 0.9);
    opacity: 0.9;
    margin-bottom: 20px;
    font-weight: 300;
}

.prediction-page .game-badges {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

.prediction-page .badge {
    padding: 8px 20px;
    border-radius: 25px;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 1px;
    transition: transform 0.3s ease;
}

.prediction-page .badge:hover {
    transform: translateY(-2px);
}

.prediction-page .badge.pubg {
    background: #4CAF50;
    color: white;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.prediction-page .badge.freefire {
    background: #FF9800;
    color: white;
    box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
}

.prediction-page .badge.cod {
    background: #2196F3;
    color: white;
    box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
}

.prediction-page .main-content {
    display: grid;
    gap: 30px;
}

@media (min-width: 992px) {
    .prediction-page .main-content {
        grid-template-columns: 1fr 1fr;
    }
}

.prediction-page .card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.prediction-page .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.prediction-page .upload-section {
    height: fit-content;
}

.prediction-page .upload-zone {
    border: 3px dashed #667eea;
    border-radius: 10px;
    padding: 40px 20px;
    text-align: center;
    margin: 20px 0;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(102, 126, 234, 0.05);
}

.prediction-page .upload-zone:hover {
    background: rgba(102, 126, 234, 0.1);
}

.prediction-page .upload-zone.dragover {
    background: rgba(102, 126, 234, 0.15);
    border-color: #764ba2;
    transform: scale(1.02);
}

.prediction-page .upload-zone i {
    font-size: 48px;
    color: #667eea;
    margin-bottom: 15px;
    transition: transform 0.3s ease;
}

.prediction-page .upload-zone.dragover i {
    transform: scale(1.2);
}

.prediction-page .file-types {
    font-size: 0.9rem;
    color: #666;
    margin: 10px 0 20px;
}

.prediction-page .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.prediction-page .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}

.prediction-page .btn-primary:active {
    transform: translateY(0);
}

.prediction-page .btn-secondary {
    background: #f8f9fa;
    color: #333;
    border: 2px solid #667eea;
    padding: 12px 30px;
    border-radius: 25px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.prediction-page .btn-secondary:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.prediction-page .btn-secondary:active {
    transform: translateY(0);
}

.prediction-page .match-type-selector {
    margin: 25px 0;
}

.prediction-page .match-type-selector h3 {
    margin-bottom: 15px;
    color: #333;
    font-size: 1.2rem;
}

.prediction-page .match-options {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.prediction-page .match-option {
    flex: 1;
}

.prediction-page .match-option input {
    display: none;
}

.prediction-page .option-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.prediction-page .option-content:hover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.05);
}

.prediction-page .match-option input:checked + .option-content {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.1);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
}

.prediction-page .option-content i {
    font-size: 24px;
    margin-bottom: 8px;
    color: #667eea;
}

.prediction-page .option-content span {
    font-weight: 500;
    color: #333;
}

/* Processing Status */
.prediction-page .status {
    text-align: center;
    padding: 40px 20px;
}

.prediction-page .spinner {
    width: 60px;
    height: 60px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.prediction-page .status-steps {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    position: relative;
}

.prediction-page .status-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e0e0e0;
    z-index: 1;
}

.prediction-page .step {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
}

.prediction-page .step-number {
    display: inline-block;
    width: 40px;
    height: 40px;
    background: #e0e0e0;
    color: #666;
    border-radius: 50%;
    line-height: 40px;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.prediction-page .step.active .step-number {
    background: #667eea;
    color: white;
    transform: scale(1.1);
}

.prediction-page .step-text {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
}

/* Results Display */
.prediction-page .results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.prediction-page .results-header h2 {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #333;
    font-size: 1.8rem;
    margin: 0;
}

.prediction-page .confidence-badge {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    padding: 10px 20px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.prediction-page .confidence-badge span:first-child {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    line-height: 1;
}

.prediction-page .winner-section {
    margin-bottom: 30px;
}

.prediction-page .winner-section h3 {
    color: #333;
    font-size: 1.3rem;
    margin-bottom: 15px;
}

.prediction-page .winner-card {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    padding: 25px;
    border-radius: 12px;
    margin: 20px 0;
    border: 2px solid rgba(102, 126, 234, 0.2);
}

.prediction-page .winner-icon {
    font-size: 48px;
    color: #FFD700;
    margin-right: 25px;
    text-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
}

.prediction-page .winner-info h4 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: #333;
}

.prediction-page .winner-stats {
    display: flex;
    gap: 25px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.prediction-page .stat {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #666;
    background: white;
    padding: 8px 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.prediction-page .stat i {
    color: #667eea;
}

/* Performance Grid */
.prediction-page .performance-section {
    margin: 30px 0;
}

.prediction-page .performance-section h3 {
    color: #333;
    font-size: 1.3rem;
    margin-bottom: 15px;
}

.prediction-page .performance-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    margin: 20px 0;
}

@media (min-width: 768px) {
    .prediction-page .performance-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.prediction-page .performance-card {
    padding: 20px;
    border-radius: 10px;
    border-left: 5px solid;
    background: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
}

.prediction-page .performance-card:hover {
    transform: translateY(-3px);
}

.prediction-page .top-performer {
    border-left-color: #4CAF50;
    background: rgba(76, 175, 80, 0.05);
}

.prediction-page .weak-link {
    border-left-color: #FF9800;
    background: rgba(255, 152, 0, 0.05);
}

.prediction-page .performance-card h4 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    color: #333;
    font-size: 1.1rem;
}

.prediction-page .player-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.prediction-page .player-name {
    font-weight: 600;
    color: #333;
    font-size: 1.1rem;
}

.prediction-page .player-kda {
    font-size: 0.9rem;
    color: #666;
}

/* Table Styling */
.prediction-page .stats-table-section {
    margin: 30px 0;
}

.prediction-page .stats-table-section h3 {
    color: #333;
    font-size: 1.3rem;
    margin-bottom: 15px;
}

.prediction-page .table-container {
    overflow-x: auto;
    margin: 20px 0;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.prediction-page #statsTable {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.prediction-page #statsTable th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: bold;
    color: #333;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.prediction-page #statsTable td {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
    color: #555;
}

.prediction-page #statsTable tr:hover {
    background: rgba(102, 126, 234, 0.02);
}

.prediction-page .rating-badge {
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: bold;
    display: inline-block;
}

/* Insights */
.prediction-page .insights-section {
    margin: 30px 0;
}

.prediction-page .insights-section h3 {
    color: #333;
    font-size: 1.3rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.prediction-page .insights-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 15px;
    margin-top: 20px;
}

@media (min-width: 768px) {
    .prediction-page .insights-container {
        grid-template-columns: repeat(3, 1fr);
    }
}

.prediction-page .insight-card {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    text-align: center;
    transition: transform 0.3s ease;
    border: 1px solid #e9ecef;
}

.prediction-page .insight-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.prediction-page .insight-card i {
    font-size: 32px;
    color: #667eea;
    margin-bottom: 15px;
}

.prediction-page .insight-card p {
    color: #555;
    line-height: 1.5;
    margin: 0;
}

/* Action Buttons */
.prediction-page .action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #f0f0f0;
}

/* Utility Classes */
.prediction-page .hidden {
    display: none !important;
}

.prediction-page .alert {
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.prediction-page .alert-error {
    background: #fde8e8;
    color: #c81e1e;
    border-left: 4px solid #c81e1e;
}

.prediction-page .info-box {
    background: #e3f2fd;
    padding: 15px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    border: 1px solid #bbdefb;
}

.prediction-page .info-box i {
    color: #2196F3;
    font-size: 1.2rem;
}

.prediction-page .info-box p {
    margin: 0;
    color: #1565c0;
    font-size: 0.95rem;
}

/* Team Configuration Styles */
.prediction-page .team-config-section {
    margin: 20px 0;
}

.prediction-page .team-config {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.prediction-page .team-count-selector {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 15px 0;
    flex-wrap: wrap;
}

.prediction-page .team-count-selector label {
    font-weight: 500;
    color: #333;
}

.prediction-page .team-count-selector select {
    padding: 8px 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
    background: white;
    font-size: 1rem;
    min-width: 200px;
    cursor: pointer;
    transition: border-color 0.3s ease;
}

.prediction-page .team-count-selector select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.prediction-page .team-section {
    margin: 25px 0;
    padding: 20px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    background: #fafafa;
    transition: border-color 0.3s ease;
}

.prediction-page .team-section:hover {
    border-color: #667eea;
}

.prediction-page .team-section h4 {
    color: #667eea;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
    font-size: 1.2rem;
}

.prediction-page .team-player-input {
    margin: 10px 0;
}

.prediction-page .input-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.prediction-page .input-group {
    display: flex;
    flex-direction: column;
}

.prediction-page .input-group label {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 5px;
    font-weight: 500;
}

.prediction-page .input-group input {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.prediction-page .input-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Team Results Display */
.prediction-page .winner-tag {
    font-size: 0.9rem;
    color: #764ba2;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 5px;
}

.prediction-page .team-members {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.prediction-page .team-members h5 {
    color: #333;
    margin-bottom: 10px;
    font-size: 1rem;
}

.prediction-page .member-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.prediction-page .member-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
    border-left: 3px solid #4CAF50;
    transition: transform 0.2s ease;
}

.prediction-page .member-item:hover {
    transform: translateX(5px);
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.prediction-page .member-name {
    font-weight: 500;
    color: #333;
}

.prediction-page .member-stats {
    font-size: 0.9rem;
    color: #666;
}

/* Remove player button */
.prediction-page .remove-player {
    background: #ff4444;
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 10px;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.prediction-page .remove-player:hover {
    background: #cc0000;
    transform: scale(1.1);
}

.prediction-page .remove-player:active {
    transform: scale(0.95);
}

/* Upload Actions */
.prediction-page .upload-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

/* Winner Row and Rank Badge */
.prediction-page .winner-row {
    background: rgba(76, 175, 80, 0.1) !important;
    border-left: 4px solid #4CAF50;
}

.prediction-page .rank-badge {
    display: inline-block;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    background: #667eea;
    color: white;
    border-radius: 50%;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(102, 126, 234, 0.3);
}

/* Player Input Section */
.prediction-page .player-input-section {
    margin-top: 20px;
}

.prediction-page .player-input-section h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 1.2rem;
}

.prediction-page .player-input {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid #e9ecef;
    position: relative;
}

.prediction-page .player-input:hover {
    border-color: #667eea;
}

/* Responsive Design */
@media (max-width: 768px) {
    .prediction-page .hero-section h1 {
        font-size: 2rem;
    }
    
    .prediction-page .hero-section {
        padding: 30px 15px;
    }
    
    .prediction-page .main-content {
        grid-template-columns: 1fr;
    }
    
    .prediction-page .winner-card {
        flex-direction: column;
        text-align: center;
    }
    
    .prediction-page .winner-icon {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .prediction-page .winner-stats {
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }
    
    .prediction-page .action-buttons {
        flex-direction: column;
    }
    
    .prediction-page .input-row {
        grid-template-columns: 1fr;
    }
    
    .prediction-page .member-list {
        grid-template-columns: 1fr;
    }
    
    .prediction-page .team-count-selector {
        flex-direction: column;
        align-items: stretch;
    }
    
    .prediction-page .team-count-selector select {
        width: 100%;
    }
    
    .prediction-page .match-options {
        flex-direction: column;
    }
    
    .prediction-page .insights-container {
        grid-template-columns: 1fr;
    }
    
    .prediction-page .results-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .prediction-page .confidence-badge {
        align-self: center;
    }
}

/* Loading Animation */
@keyframes pulse {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
}

.prediction-page .loading-text {
    animation: pulse 1.5s infinite;
}

/* Scrollbar Styling */
.prediction-page .table-container::-webkit-scrollbar {
    height: 8px;
}

.prediction-page .table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.prediction-page .table-container::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 4px;
}

.prediction-page .table-container::-webkit-scrollbar-thumb:hover {
    background: #764ba2;
}

/* Success Animation */
@keyframes success {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.prediction-page .success-animation {
    animation: success 0.5s ease;
}

/* Fade In Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.prediction-page .fade-in {
    animation: fadeIn 0.5s ease;
}
</style>
<!-- Page content -->
     <main role="main">    
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(./img/full_bg.jpg); background-size: cover; ">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              <center>AI Prediction</center>
            </h1>
          </div>
        </header>
      </article>  
    </main>
<!-- Page content -->
<div class="infiknight-container">
    <div class="hero-section">
        <h1>AI-Powered Battle Royale Predictor</h1>
        <p class="subtitle">Upload match statistics to predict winners with AI precision</p>
        <div class="game-badges">
            <span class="badge pubg">Battle</span>
            <span class="badge freefire">Royal</span>
            <span class="badge cod">Games</span>
        </div>
    </div>

    <div class="main-content">
        <!-- Upload Section -->
        <div class="upload-section card">
            <h2><i class="fas fa-upload"></i> Upload Match Statistics</h2>
            <div class="upload-zone" id="uploadZone">
                <i class="fas fa-file-image"></i>
                <p>Drag & drop your PNG statistics file here</p>
                <p class="file-types">Supported: PNG files (max 5MB)</p>
                <input type="file" id="statsFile" accept=".png" hidden>
                <button class="btn-primary" id="browseBtn">
                    Browse Files
                </button>
            </div>
            <div class="match-type-selector">
                <h3>Match Type</h3>
                <div class="match-options">
                    <label class="match-option">
                        <input type="radio" name="matchType" value="solo" checked>
                        <span class="option-content">
                            <i class="fas fa-user"></i>
                            <span>Solo</span>
                        </span>
                    </label>
                    <label class="match-option">
                        <input type="radio" name="matchType" value="duo">
                        <span class="option-content">
                            <i class="fas fa-user-friends"></i>
                            <span>Duo</span>
                        </span>
                    </label>
                    <label class="match-option">
                        <input type="radio" name="matchType" value="squad">
                        <span class="option-content">
                            <i class="fas fa-users"></i>
                            <span>Squad</span>
                        </span>
                    </label>
                </div>
            </div>
            
            <!-- Team Configuration Section -->
            <div class="team-config-section card hidden" id="teamConfigSection">
                <h3><i class="fas fa-users-cog"></i> Team Configuration</h3>
                <div class="team-inputs" id="teamInputs">
                    <!-- Dynamically generated based on match type -->
                </div>
            </div>

            <!-- Player Input Section -->
            <div class="player-input-section card" id="playerInputSection">
                <h3><i class="fas fa-gamepad"></i> Player/Team Details</h3>
                <div class="player-inputs" id="playerInputs">
                    <div class="player-input" data-index="0">
                        <div class="input-row">
                            <div class="input-group">
                                <label>Player 1 Name</label>
                                <input type="text" class="player-name" placeholder="Enter player name">
                            </div>
                            <div class="input-group">
                                <label>Kills</label>
                                <input type="number" class="player-kills" placeholder="0" min="0" value="5">
                            </div>
                            <div class="input-group">
                                <label>Damage</label>
                                <input type="number" class="player-damage" placeholder="0" min="0" value="250">
                            </div>
                            <div class="input-group">
                                <label>Survival Time (sec)</label>
                                <input type="number" class="player-survival" placeholder="0" min="0" value="450">
                            </div>
                            <div class="input-group">
                                <label>Headshots</label>
                                <input type="number" class="player-headshots" placeholder="0" min="0" value="2">
                            </div>
                            <div class="input-group">
                                <label>Assists</label>
                                <input type="number" class="player-assists" placeholder="0" min="0" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn-secondary" id="addPlayerBtn">
                    <i class="fas fa-plus"></i> Add Another Player
                </button>
            </div>

            <div class="upload-actions">
                <button class="btn-primary" id="processBtn" style="width: 100%; margin-top: 20px;">
                    <i class="fas fa-calculator"></i> Calculate & Predict Winner
                </button>
            </div>

            <div class="upload-info">
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <p>Upload PNG files containing player/team statistics or enter data manually. Our AI will analyze and predict the winner.</p>
                </div>
            </div>
        </div>

        <!-- Processing & Results Section -->
        <div class="results-section">
            <!-- Processing Status -->
            <div class="status-container card">
                <div id="processingStatus" class="status hidden">
                    <div class="spinner"></div>
                    <h3>Processing Your Statistics</h3>
                    <div class="status-steps">
                        <div class="step active">
                            <span class="step-number">1</span>
                            <span class="step-text">Uploading & Validating</span>
                        </div>
                        <div class="step">
                            <span class="step-number">2</span>
                            <span class="step-text">Extracting Data</span>
                        </div>
                        <div class="step">
                            <span class="step-number">3</span>
                            <span class="step-text">AI Prediction Analysis</span>
                        </div>
                        <div class="step">
                            <span class="step-number">4</span>
                            <span class="step-text">Generating Results</span>
                        </div>
                    </div>
                </div>

                <div id="errorAlert" class="alert alert-error hidden">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="errorMessage"></span>
                </div>
            </div>

            <!-- Prediction Results -->
            <div id="predictionResults" class="card hidden">
                <div class="results-header">
                    <h2><i class="fas fa-trophy"></i> AI Prediction Results</h2>
                    <div class="confidence-badge">
                        <span id="confidenceScore">0%</span>
                        <span>Confidence</span>
                    </div>
                </div>

                <div class="winner-section">
                    <h3>Predicted Winner</h3>
                    <div class="winner-card">
                        <div class="winner-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="winner-info">
                            <h4 id="winnerName">Loading...</h4>
                            <div class="winner-stats">
                                <span class="stat">
                                    <i class="fas fa-skull"></i>
                                    <span id="totalKills">0</span> Kills
                                </span>
                                <span class="stat">
                                    <i class="fas fa-crosshairs"></i>
                                    <span id="avgDamage">0</span> Avg Damage
                                </span>
                                <span class="stat">
                                    <i class="fas fa-percentage"></i>
                                    <span id="winRate">0%</span> Win Rate
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team/Player Performance -->
                <div class="performance-section">
                    <h3>Performance Analysis</h3>
                    <div class="performance-grid">
                        <div class="performance-card top-performer">
                            <h4><i class="fas fa-star"></i> Top Performer</h4>
                            <div class="player-info">
                                <span class="player-name" id="topPerformerName">Loading...</span>
                                <span class="player-kda">K/D/A: <span id="topPerformerKDA">0/0/0</span></span>
                            </div>
                        </div>
                        <div class="performance-card weak-link">
                            <h4><i class="fas fa-exclamation-triangle"></i> Improvement Needed</h4>
                            <div class="player-info">
                                <span class="player-name" id="weakLinkName">Loading...</span>
                                <span class="player-kda">K/D/A: <span id="weakLinkKDA">0/0/0</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Table -->
                <div class="stats-table-section">
                    <h3>Detailed Statistics</h3>
                    <div class="table-container">
                        <table id="statsTable">
                            <thead>
                                <tr>
                                    <th>Player/Team</th>
                                    <th>Kills</th>
                                    <th>Damage</th>
                                    <th>Survival</th>
                                    <th>Win %</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody id="statsTableBody">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Insights -->
                <div class="insights-section">
                    <h3><i class="fas fa-lightbulb"></i> AI Insights</h3>
                    <div class="insights-container">
                        <div class="insight-card">
                            <i class="fas fa-chart-line"></i>
                            <p id="insight1">Loading insights...</p>
                        </div>
                        <div class="insight-card">
                            <i class="fas fa-shield-alt"></i>
                            <p id="insight2">Loading insights...</p>
                        </div>
                        <div class="insight-card">
                            <i class="fas fa-tools"></i>
                            <p id="insight3">Loading insights...</p>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn-secondary" onclick="exportResults()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                    <button class="btn-primary" onclick="newPrediction()">
                        <i class="fas fa-redo"></i> New Prediction
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- JavaScript -->
<script>
// Global predictor instance
let predictor;

class InfiknightPredictor {
    constructor() {
        this.uploadZone = document.getElementById('uploadZone');
        this.statsFile = document.getElementById('statsFile');
        this.processingStatus = document.getElementById('processingStatus');
        this.errorAlert = document.getElementById('errorAlert');
        this.predictionResults = document.getElementById('predictionResults');
        this.playerCount = 1;
        
        this.initializeEventListeners();
        this.initializeTeamLogic();
    }
    
    initializeEventListeners() {
        // Drag and drop functionality
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
            
            if (e.dataTransfer.files.length) {
                this.statsFile.files = e.dataTransfer.files;
                this.handleFileUpload();
            }
        });

        // File input change
        this.statsFile.addEventListener('change', () => {
            if (this.statsFile.files.length) {
                this.handleFileUpload();
            }
        });

        // Browse button
        document.getElementById('browseBtn').addEventListener('click', () => {
            this.statsFile.click();
        });

        // Process button
        document.getElementById('processBtn').addEventListener('click', () => {
            this.processData();
        });
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
        
        teamInputs.innerHTML = `
            <div class="team-config">
                <p>Duo matches require teams of 2 players each.</p>
                <div class="team-count-selector">
                    <label>Number of Teams:</label>
                    <select id="teamCount">
                        <option value="5">5 Teams (10 players)</option>
                        <option value="4">4 Teams (8 players)</option>
                        <option value="3">3 Teams (6 players)</option>
                        <option value="2">2 Teams (4 players)</option>
                    </select>
                </div>
                <button class="btn-secondary" onclick="predictor.generateTeamInputs()">
                    <i class="fas fa-users"></i> Generate Team Inputs
                </button>
            </div>
        `;
        
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
                        <option value="2">2 Teams (8 players)</option>
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
        
        playerInputs.innerHTML = '';
        
        for (let teamIndex = 0; teamIndex < teamCount; teamIndex++) {
            const teamDiv = document.createElement('div');
            teamDiv.className = 'team-section';
            teamDiv.innerHTML = `<h4>Team ${teamIndex + 1}</h4>`;
            
            for (let playerIndex = 0; playerIndex < playersPerTeam; playerIndex++) {
                const playerInput = this.createTeamPlayerInput(teamIndex, playerIndex);
                teamDiv.appendChild(playerInput);
            }
            
            playerInputs.appendChild(teamDiv);
        }
    }
    
    createTeamPlayerInput(teamIndex, playerIndex) {
        const div = document.createElement('div');
        div.className = 'team-player-input';
        div.innerHTML = `
            <div class="input-row">
                <div class="input-group">
                    <label>Player ${playerIndex + 1} Name</label>
                    <input type="text" 
                           class="team-player-name"
                           placeholder="Enter player name">
                </div>
                <div class="input-group">
                    <label>Kills</label>
                    <input type="number" 
                           class="team-player-kills"
                           placeholder="0" min="0">
                </div>
                <div class="input-group">
                    <label>Damage</label>
                    <input type="number" 
                           class="team-player-damage"
                           placeholder="0" min="0">
                </div>
                <div class="input-group">
                    <label>Survival Time (sec)</label>
                    <input type="number" 
                           class="team-player-survival"
                           placeholder="0" min="0">
                </div>
                <div class="input-group">
                    <label>Headshots</label>
                    <input type="number" 
                           class="team-player-headshots"
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
        const div = document.createElement('div');
        div.className = 'player-input';
        div.setAttribute('data-index', index);
        div.innerHTML = `
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
        `;
        return div;
    }
    
    removePlayerInput(index) {
        const element = document.querySelector(`.player-input[data-index="${index}"]`);
        if (element) element.remove();
    }
    
    async handleFileUpload() {
        const file = this.statsFile.files[0];
        const matchType = document.querySelector('input[name="matchType"]:checked').value;
        
        if (!file) return;

        // Validate file
        if (!file.type.match('image/png')) {
            this.showError('Please upload a PNG image file.');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            this.showError('File size must be less than 5MB.');
            return;
        }

        // Show processing status
        this.showProcessing();
        
        // Create FormData
        const formData = new FormData();
        formData.append('statsFile', file);
        formData.append('matchType', matchType);

        try {
            // Upload and process file
            const response = await fetch('prediction_uploads.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Update processing steps
                this.updateProcessingStep(2);
                await this.simulateProcessingDelay();
                
                this.updateProcessingStep(3);
                await this.simulateProcessingDelay();
                
                this.updateProcessingStep(4);
                
                // Display results after delay
                setTimeout(() => {
                    this.displayResults(result);
                }, 1000);
                
            } else {
                throw new Error(result.message);
            }
            
        } catch (error) {
            this.showError(error.message || 'An error occurred during processing.');
        }
    }
    
    async processData() {
        const matchType = document.querySelector('input[name="matchType"]:checked').value;
        
        // Validate input
        if (matchType !== 'solo' && !this.validateTeams(matchType)) {
            this.showError(`Please ensure all teams have complete data for ${matchType} mode.`);
            return;
        }
        
        // Collect data
        const playerData = this.collectPlayerData(matchType);
        
        // Show processing
        this.showProcessing();
        
        // Create FormData
        const formData = new FormData();
        formData.append('matchType', matchType);
        formData.append('playerData', JSON.stringify(playerData));

        try {
            const response = await fetch('prediction_uploads.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.updateProcessingStep(2);
                await this.simulateProcessingDelay();
                
                this.updateProcessingStep(3);
                await this.simulateProcessingDelay();
                
                this.updateProcessingStep(4);
                
                setTimeout(() => {
                    this.displayResults(result);
                }, 1000);
                
            } else {
                throw new Error(result.message);
            }
            
        } catch (error) {
            this.showError(error.message || 'An error occurred during processing.');
        }
    }
    
    showProcessing() {
        this.errorAlert.classList.add('hidden');
        this.predictionResults.classList.add('hidden');
        this.processingStatus.classList.remove('hidden');
        this.updateProcessingStep(1);
    }
    
    updateProcessingStep(stepNumber) {
        const steps = document.querySelectorAll('.status-steps .step');
        steps.forEach((step, index) => {
            if (index < stepNumber) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
    }
    
    async simulateProcessingDelay() {
        return new Promise(resolve => setTimeout(resolve, 800));
    }
    
 collectPlayerData(matchType) {
    const data = {
        matchType: matchType
    };
    
    if (matchType === 'solo') {
        // Collect solo player data
        const playerInputs = document.querySelectorAll('.player-input');
        data.players = [];
        
        playerInputs.forEach(input => {
            const player = {
                name: input.querySelector('.player-name').value || `Player_${Math.random().toString(36).substr(2, 5)}`,
                kills: parseInt(input.querySelector('.player-kills').value) || 0,
                damage: parseInt(input.querySelector('.player-damage').value) || 0,
                survival: parseInt(input.querySelector('.player-survival').value) || 0,
                headshots: parseInt(input.querySelector('.player-headshots')?.value) || 0,
                assists: parseInt(input.querySelector('.player-assists')?.value) || 0 // Add this line
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
                    name: input.querySelector('.team-player-name').value || `Player ${playerIndex + 1}`,
                    kills: parseInt(input.querySelector('.team-player-kills').value) || 0,
                    damage: parseInt(input.querySelector('.team-player-damage').value) || 0,
                    survival: parseInt(input.querySelector('.team-player-survival').value) || 0,
                    headshots: parseInt(input.querySelector('.team-player-headshots')?.value) || 0,
                    assists: 0
                };
                team.players.push(player);
            });
            
            teams.push(team);
        });
        
        return teams;
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
                if (!name.trim() || kills === '') return false;
            }
        }
        
        return true;
    }
    
    displayResults(data) {
        const prediction = data.prediction;
        const matchType = document.querySelector('input[name="matchType"]:checked').value;
        
        // Clear previous team members
        const existingMembers = document.querySelector('.team-members');
        if (existingMembers) existingMembers.remove();
        
        // Hide processing and show results
        this.processingStatus.classList.add('hidden');
        this.predictionResults.classList.remove('hidden');
        
        // Update confidence score
        document.getElementById('confidenceScore').textContent = prediction.confidence + '%';
        
        if (prediction.predictionType === 'solo') {
            this.updateSoloResults(prediction);
        } else {
            this.updateTeamResults(prediction, matchType);
        }
        
        // Scroll to results
        this.predictionResults.scrollIntoView({ behavior: 'smooth' });
    }
    
    updateSoloResults(prediction) {
        document.getElementById('winnerName').innerHTML = `
            <span class="winner-tag">Solo Winner</span><br>
            ${prediction.winner.name}
        `;
        
        document.getElementById('totalKills').textContent = prediction.winner.kills;
        document.getElementById('avgDamage').textContent = prediction.winner.damage;
        document.getElementById('winRate').textContent = Math.round(prediction.winner.rating * 10) + '%';
        
        // Update performer names
        document.getElementById('topPerformerName').textContent = prediction.topPerformer.name;
        document.getElementById('weakLinkName').textContent = prediction.weakLink.name;
        
        // Update KDA
        document.getElementById('topPerformerKDA').textContent = 
            `${prediction.topPerformer.kills}/1/${Math.round(prediction.topPerformer.damage/50)}`;
        document.getElementById('weakLinkKDA').textContent = 
            `${prediction.weakLink.kills}/2/${Math.round(prediction.weakLink.damage/50)}`;
        
        // Populate table
        this.populateStatsTable(prediction.allPlayers, 'solo');
        
        // Update insights
        if (prediction.insights && prediction.insights.length >= 3) {
            document.getElementById('insight1').textContent = prediction.insights[0];
            document.getElementById('insight2').textContent = prediction.insights[1];
            document.getElementById('insight3').textContent = prediction.insights[2];
        }
    }
    
    updateTeamResults(prediction, matchType) {
        const winningTeam = prediction.winningTeam;
        
        document.getElementById('winnerName').innerHTML = `
            <span class="winner-tag">${matchType.charAt(0).toUpperCase() + matchType.slice(1)} Winner</span><br>
            ${winningTeam.name}
        `;
        
        document.getElementById('totalKills').textContent = winningTeam.totalKills || 0;
        document.getElementById('avgDamage').textContent = Math.round((winningTeam.totalDamage || 0) / winningTeam.players.length);
        document.getElementById('winRate').textContent = Math.round((winningTeam.teamScore || 0) * 10) + '%';
        
        // Update performer names
        document.getElementById('topPerformerName').textContent = prediction.mvp.name + ' (MVP)';
        document.getElementById('weakLinkName').textContent = prediction.teamWeakLink.name;
        
        // Update KDA
        document.getElementById('topPerformerKDA').textContent = 
            `${prediction.mvp.kills}/1/${Math.round(prediction.mvp.damage/50)}`;
        document.getElementById('weakLinkKDA').textContent = 
            `${prediction.teamWeakLink.kills}/2/${Math.round(prediction.teamWeakLink.damage/50)}`;
        
        // Display team members
        this.displayTeamMembers(winningTeam);
        
        // Populate table with teams
        this.populateTeamTable(prediction.allTeams, matchType);
        
        // Update insights
        if (prediction.insights && prediction.insights.length >= 3) {
            document.getElementById('insight1').textContent = prediction.insights[0];
            document.getElementById('insight2').textContent = prediction.insights[1];
            document.getElementById('insight3').textContent = prediction.insights[2];
        }
    }
    
    displayTeamMembers(team) {
        const winnerCard = document.querySelector('.winner-card');
        
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
                            K:${player.kills} | D:${player.damage} | S:${Math.floor(player.survival/60)}:${(player.survival%60).toString().padStart(2, '0')}
                        </span>
                    </div>
                `).join('')}
            </div>
        `;
        
        winnerCard.appendChild(teamMembersDiv);
    }
    
    populateStatsTable(players, type) {
        const tableBody = document.getElementById('statsTableBody');
        tableBody.innerHTML = '';
        
        // Update table headers for solo
        const tableHead = document.querySelector('#statsTable thead tr');
        tableHead.innerHTML = `
            <th>Player</th>
            <th>Kills</th>
            <th>Damage</th>
            <th>Survival</th>
            <th>Win %</th>
            <th>Rating</th>
        `;
        
        players.forEach((player, index) => {
            const row = document.createElement('tr');
            if (index === 0) row.classList.add('winner-row');
            
            const survivalMinutes = Math.floor(player.survival / 60);
            const survivalSeconds = player.survival % 60;
            
            row.innerHTML = `
                <td><strong>${player.name}</strong></td>
                <td>${player.kills}</td>
                <td>${player.damage}</td>
                <td>${survivalMinutes}:${survivalSeconds.toString().padStart(2, '0')}</td>
                <td>${Math.round((player.rating || 0) * 10)}%</td>
                <td><span class="rating-badge">${(player.rating || 0).toFixed(1)}</span></td>
            `;
            tableBody.appendChild(row);
        });
    }
    
    populateTeamTable(teams, matchType) {
        const tableBody = document.getElementById('statsTableBody');
        tableBody.innerHTML = '';
        
        // Update table headers for teams
        const tableHead = document.querySelector('#statsTable thead tr');
        tableHead.innerHTML = `
            <th>Team</th>
            <th>Total Kills</th>
            <th>Avg Damage</th>
            <th>Avg Survival</th>
            <th>Team Score</th>
            <th>Rank</th>
        `;
        
        teams.forEach((team, index) => {
            const row = document.createElement('tr');
            if (index === 0) row.classList.add('winner-row');
            
            const avgDamage = Math.round((team.totalDamage || 0) / team.players.length);
            const avgSurvival = Math.round((team.avgSurvival || 0));
            const survivalMinutes = Math.floor(avgSurvival / 60);
            const survivalSeconds = avgSurvival % 60;
            
            row.innerHTML = `
                <td><strong>${team.name}</strong><br>
                    <small>${team.players.length} players</small>
                </td>
                <td>${team.totalKills || 0}</td>
                <td>${avgDamage}</td>
                <td>${survivalMinutes}:${survivalSeconds.toString().padStart(2, '0')}</td>
                <td><span class="rating-badge">${(team.teamScore || 0).toFixed(1)}</span></td>
                <td><span class="rank-badge">${index + 1}</span></td>
            `;
            tableBody.appendChild(row);
        });
    }
    
    showError(message) {
        this.processingStatus.classList.add('hidden');
        this.errorAlert.classList.remove('hidden');
        document.getElementById('errorMessage').textContent = message;
    }
}

// Utility functions
function exportResults() {
    const winnerName = document.getElementById('winnerName').textContent;
    const confidence = document.getElementById('confidenceScore').textContent;
    const totalKills = document.getElementById('totalKills').textContent;
    
    const report = `
Infiknight Prediction Report
=============================
Date: ${new Date().toLocaleDateString()}
Time: ${new Date().toLocaleTimeString()}

Predicted Winner: ${winnerName}
Confidence Score: ${confidence}
Total Kills: ${totalKills}

Top Performer: ${document.getElementById('topPerformerName').textContent}
Improvement Needed: ${document.getElementById('weakLinkName').textContent}

Insights:
1. ${document.getElementById('insight1').textContent}
2. ${document.getElementById('insight2').textContent}
3. ${document.getElementById('insight3').textContent}
    `.trim();
    
    // Create download link
    const blob = new Blob([report], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `infiknight-report-${new Date().toISOString().slice(0,10)}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function newPrediction() {
    // Reset form
    document.getElementById('statsFile').value = '';
    document.getElementById('predictionResults').classList.add('hidden');
    document.getElementById('errorAlert').classList.add('hidden');
    document.getElementById('playerInputs').innerHTML = `
        <div class="player-input" data-index="0">
            <div class="input-row">
                <div class="input-group">
                    <label>Player 1 Name</label>
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
                <div class="input-group">
                    <label>Assists</label>
                    <input type="number" class="player-assists" placeholder="0" min="0" value="0">
                </div>
            </div>
        </div>
    `;
    document.getElementById('teamInputs').innerHTML = '';
    document.getElementById('teamConfigSection').classList.add('hidden');
    
    // Reset to solo mode
    document.querySelector('input[name="matchType"][value="solo"]').checked = true;
    predictor.handleMatchTypeChange('solo');
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Initialize application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    predictor = new InfiknightPredictor();
});
</script>

<style>
/* Additional CSS for winner rows and rank badges */
.winner-row {
    background: rgba(76, 175, 80, 0.1) !important;
    border-left: 4px solid #4CAF50;
}

.rank-badge {
    display: inline-block;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    background: #667eea;
    color: white;
    border-radius: 50%;
    font-weight: bold;
}

.upload-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}
</style>

<?php
// Include footer
include_once('footer.php');
?>