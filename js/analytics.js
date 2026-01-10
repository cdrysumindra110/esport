// js/analytics.js - Analytics and Charting
class AnalyticsManager {
    constructor() {
        this.charts = {};
        this.data = null;
    }
    
    initialize() {
        this.initializeCharts();
        this.setupEventListeners();
    }
    
    initializeCharts() {
        // Initialize all chart canvases
        this.initializeRatingChart();
        this.initializePerformanceChart();
        this.initializeWinProbabilityChart();
        this.initializeTrendChart();
    }
    
    initializeRatingChart() {
        const ctx = document.getElementById('ratingChart')?.getContext('2d');
        if (!ctx) return;
        
        this.charts.rating = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Player Rating',
                    data: [],
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Rating: ${context.raw.toFixed(1)}/10`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10,
                        title: {
                            display: true,
                            text: 'Rating (0-10)',
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Players',
                            color: '#666'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    initializePerformanceChart() {
        const ctx = document.getElementById('performanceChart')?.getContext('2d');
        if (!ctx) return;
        
        this.charts.performance = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['Kills', 'Damage', 'Survival', 'Headshots', 'Assists'],
                datasets: [
                    {
                        label: 'Average',
                        data: [50, 50, 50, 50, 50],
                        backgroundColor: 'rgba(200, 200, 200, 0.2)',
                        borderColor: 'rgba(200, 200, 200, 0.5)',
                        borderWidth: 1,
                        pointRadius: 0
                    },
                    {
                        label: 'Top Performer',
                        data: [0, 0, 0, 0, 0],
                        backgroundColor: 'rgba(76, 175, 80, 0.2)',
                        borderColor: 'rgba(76, 175, 80, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(76, 175, 80, 1)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20,
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        pointLabels: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 20
                        }
                    }
                }
            }
        });
    }
    
    initializeWinProbabilityChart() {
        const ctx = document.getElementById('winProbabilityChart')?.getContext('2d');
        if (!ctx) return;
        
        this.charts.winProbability = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Win', 'Loss'],
                datasets: [{
                    data: [85, 15],
                    backgroundColor: [
                        'rgba(76, 175, 80, 0.8)',
                        'rgba(244, 67, 54, 0.8)'
                    ],
                    borderColor: [
                        'rgba(76, 175, 80, 1)',
                        'rgba(244, 67, 54, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    initializeTrendChart() {
        const ctx = document.getElementById('trendChart')?.getContext('2d');
        if (!ctx) return;
        
        this.charts.trend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Match 1', 'Match 2', 'Match 3', 'Match 4', 'Match 5'],
                datasets: [{
                    label: 'Rating Trend',
                    data: [5.2, 6.8, 7.1, 6.5, 8.2],
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(102, 126, 234, 1)',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10,
                        title: {
                            display: true,
                            text: 'Rating'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Recent Matches'
                        }
                    }
                }
            }
        });
    }
    
    setupEventListeners() {
        // Export chart buttons
        document.querySelectorAll('.export-chart').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const chartId = e.target.dataset.chart;
                this.exportChart(chartId);
            });
        });
        
        // Chart type toggles
        document.querySelectorAll('.chart-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                const chartId = e.target.dataset.chart;
                const type = e.target.dataset.type;
                this.changeChartType(chartId, type);
            });
        });
    }
    
    updateWithPrediction(prediction) {
        if (!prediction) return;
        
        this.data = prediction;
        
        if (prediction.predictionType === 'solo') {
            this.updateSoloCharts(prediction);
        } else {
            this.updateTeamCharts(prediction);
        }
        
        // Update win probability
        this.updateWinProbability(prediction.confidence);
        
        // Show all charts
        this.showAllCharts();
    }
    
    updateSoloCharts(prediction) {
        // Update rating chart
        if (this.charts.rating && prediction.allPlayers) {
            const players = prediction.allPlayers;
            const labels = players.map(p => p.name.substring(0, 10) + (p.name.length > 10 ? '...' : ''));
            const ratings = players.map(p => p.rating || 0);
            
            this.charts.rating.data.labels = labels;
            this.charts.rating.data.datasets[0].data = ratings;
            
            // Color bars based on rank
            this.charts.rating.data.datasets[0].backgroundColor = ratings.map((rating, index) => {
                if (index === 0) return 'rgba(76, 175, 80, 0.7)'; // Winner - green
                if (index === ratings.length - 1) return 'rgba(244, 67, 54, 0.7)'; // Last - red
                return 'rgba(102, 126, 234, 0.7)'; // Others - blue
            });
            
            this.charts.rating.update();
        }
        
        // Update performance chart
        if (this.charts.performance && prediction.topPerformer && prediction.allPlayers) {
            const top = prediction.topPerformer;
            const players = prediction.allPlayers;
            
            // Calculate max values for normalization
            const maxKills = Math.max(...players.map(p => p.kills));
            const maxDamage = Math.max(...players.map(p => p.damage));
            const maxSurvival = Math.max(...players.map(p => p.survival));
            const maxHeadshots = Math.max(...players.map(p => p.headshots || 0));
            const maxAssists = Math.max(...players.map(p => p.assists || 0));
            
            this.charts.performance.data.datasets[1].data = [
                (top.kills / Math.max(maxKills, 1)) * 100,
                (top.damage / Math.max(maxDamage, 1)) * 100,
                (top.survival / Math.max(maxSurvival, 1)) * 100,
                ((top.headshots || 0) / Math.max(maxHeadshots, 1)) * 100,
                ((top.assists || 0) / Math.max(maxAssists, 1)) * 100
            ];
            
            this.charts.performance.update();
        }
    }
    
    updateTeamCharts(prediction) {
        // Update rating chart for teams
        if (this.charts.rating && prediction.allTeams) {
            const teams = prediction.allTeams;
            const labels = teams.map(t => t.name);
            const scores = teams.map(t => t.teamScore || 0);
            
            this.charts.rating.data.labels = labels;
            this.charts.rating.data.datasets[0].data = scores;
            this.charts.rating.data.datasets[0].label = 'Team Score';
            
            // Color bars
            this.charts.rating.data.datasets[0].backgroundColor = scores.map((score, index) => {
                if (index === 0) return 'rgba(76, 175, 80, 0.7)';
                if (index === scores.length - 1) return 'rgba(244, 67, 54, 0.7)';
                return 'rgba(102, 126, 234, 0.7)';
            });
            
            this.charts.rating.update();
        }
        
        // Update performance chart for winning team
        if (this.charts.performance && prediction.winningTeam) {
            const team = prediction.winningTeam;
            const players = team.players || [];
            
            if (players.length > 0) {
                // Calculate team averages
                const avgKills = players.reduce((sum, p) => sum + p.kills, 0) / players.length;
                const avgDamage = players.reduce((sum, p) => sum + p.damage, 0) / players.length;
                const avgSurvival = players.reduce((sum, p) => sum + p.survival, 0) / players.length;
                const avgHeadshots = players.reduce((sum, p) => sum + (p.headshots || 0), 0) / players.length;
                const avgAssists = players.reduce((sum, p) => sum + (p.assists || 0), 0) / players.length;
                
                // Get max values from all teams for normalization
                const allTeams = prediction.allTeams || [];
                const allPlayers = allTeams.flatMap(t => t.players || []);
                const maxKills = Math.max(...allPlayers.map(p => p.kills));
                const maxDamage = Math.max(...allPlayers.map(p => p.damage));
                const maxSurvival = Math.max(...allPlayers.map(p => p.survival));
                const maxHeadshots = Math.max(...allPlayers.map(p => p.headshots || 0));
                const maxAssists = Math.max(...allPlayers.map(p => p.assists || 0));
                
                this.charts.performance.data.datasets[1].data = [
                    (avgKills / Math.max(maxKills, 1)) * 100,
                    (avgDamage / Math.max(maxDamage, 1)) * 100,
                    (avgSurvival / Math.max(maxSurvival, 1)) * 100,
                    (avgHeadshots / Math.max(maxHeadshots, 1)) * 100,
                    (avgAssists / Math.max(maxAssists, 1)) * 100
                ];
                this.charts.performance.data.datasets[1].label = 'Team Average';
                
                this.charts.performance.update();
            }
        }
    }
    
    updateWinProbability(confidence) {
        if (this.charts.winProbability) {
            this.charts.winProbability.data.datasets[0].data = [
                confidence,
                100 - confidence
            ];
            this.charts.winProbability.update();
        }
    }
    
    showAllCharts() {
        // Ensure all chart containers are visible
        document.querySelectorAll('.chart-container').forEach(container => {
            container.style.display = 'block';
        });
    }
    
    changeChartType(chartId, type) {
        const chart = this.charts[chartId];
        if (chart) {
            chart.config.type = type;
            chart.update();
        }
    }
    
    exportChart(chartId) {
        const chart = this.charts[chartId];
        if (!chart) return;
        
        const link = document.createElement('a');
        link.download = `infiknight-${chartId}-${new Date().toISOString().slice(0,10)}.png`;
        link.href = chart.toBase64Image();
        link.click();
    }
    
    generateAnalyticsReport() {
        if (!this.data) return null;
        
        const report = {
            timestamp: new Date().toISOString(),
            prediction: this.data,
            charts: {},
            summary: this.generateSummary()
        };
        
        // Capture chart images
        Object.keys(this.charts).forEach(chartId => {
            const chart = this.charts[chartId];
            if (chart) {
                report.charts[chartId] = chart.toBase64Image();
            }
        });
        
        return report;
    }
    
    generateSummary() {
        if (!this.data) return {};
        
        const prediction = this.data;
        
        if (prediction.predictionType === 'solo') {
            const players = prediction.allPlayers || [];
            return {
                totalPlayers: players.length,
                avgRating: players.reduce((sum, p) => sum + (p.rating || 0), 0) / players.length,
                avgKills: players.reduce((sum, p) => sum + p.kills, 0) / players.length,
                avgDamage: players.reduce((sum, p) => sum + p.damage, 0) / players.length,
                winnerRating: prediction.winner?.rating || 0,
                confidence: prediction.confidence || 0
            };
        } else {
            const teams = prediction.allTeams || [];
            return {
                totalTeams: teams.length,
                avgTeamScore: teams.reduce((sum, t) => sum + (t.teamScore || 0), 0) / teams.length,
                avgSynergy: teams.reduce((sum, t) => sum + (t.synergyBonus || 0), 0) / teams.length,
                winningTeamScore: prediction.winningTeam?.teamScore || 0,
                confidence: prediction.confidence || 0
            };
        }
    }
}

// Initialize analytics manager
let analyticsManager = null;

document.addEventListener('DOMContentLoaded', () => {
    analyticsManager = new AnalyticsManager();
    analyticsManager.initialize();
});

// Export for global use
window.AnalyticsManager = AnalyticsManager;
window.analyticsManager = analyticsManager;