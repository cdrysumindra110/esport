// js/ocr.js - Client-side OCR Integration
class OCRProcessor {
    constructor() {
        this.tesseract = window.Tesseract;
        this.isInitialized = false;
        this.worker = null;
    }
    
    async initialize() {
        if (!this.tesseract) {
            console.warn('Tesseract.js not loaded. OCR functionality will be limited.');
            return false;
        }
        
        try {
            this.worker = await this.tesseract.createWorker({
                logger: m => console.log('OCR Progress:', m)
            });
            
            await this.worker.loadLanguage('eng');
            await this.worker.initialize('eng');
            
            this.isInitialized = true;
            console.log('OCR Processor initialized successfully');
            return true;
        } catch (error) {
            console.error('Failed to initialize OCR:', error);
            return false;
        }
    }
    
    async processImage(imageFile) {
        if (!this.isInitialized) {
            throw new Error('OCR processor not initialized');
        }
        
        try {
            console.log('Starting OCR processing...');
            
            const result = await this.worker.recognize(imageFile);
            
            console.log('OCR Result:', {
                text: result.data.text,
                confidence: result.data.confidence,
                words: result.data.words?.length || 0
            });
            
            return {
                text: result.data.text,
                confidence: result.data.confidence,
                words: result.data.words || []
            };
        } catch (error) {
            console.error('OCR processing failed:', error);
            throw new Error('Failed to process image with OCR');
        }
    }
    
    async detectGameStatistics(text) {
        const patterns = {
            playerName: /([A-Za-z0-9_]+)\s*[\|:]\s*(\d+)\s*[\|:]\s*(\d+)\s*[\|:]\s*(\d+)/gi,
            killDamage: /(\d+)\s*kills?\s*[\|:]\s*(\d+)\s*damage/gi,
            survival: /(\d+)\s*sec(?:onds?)?/gi,
            headshots: /(\d+)\s*headshots?/gi,
            assists: /(\d+)\s*assists?/gi
        };
        
        const players = [];
        const lines = text.split('\n');
        
        for (const line of lines) {
            if (line.trim().length < 5) continue;
            
            // Try different parsing patterns
            const player = this.parseLine(line);
            if (player) {
                players.push(player);
            }
        }
        
        // If no players found, try alternative parsing
        if (players.length === 0) {
            const numbers = text.match(/\d+/g);
            if (numbers && numbers.length >= 15) {
                // Assume 5 stats per player (kills, damage, survival, headshots, assists)
                const playerCount = Math.floor(numbers.length / 5);
                for (let i = 0; i < playerCount; i++) {
                    players.push({
                        name: `Player_${i + 1}`,
                        kills: parseInt(numbers[i * 5] || 0),
                        damage: parseInt(numbers[i * 5 + 1] || 0),
                        survival: parseInt(numbers[i * 5 + 2] || 0),
                        headshots: parseInt(numbers[i * 5 + 3] || 0),
                        assists: parseInt(numbers[i * 5 + 4] || 0)
                    });
                }
            }
        }
        
        // Filter out invalid players
        const validPlayers = players.filter(p => 
            p.kills >= 0 && p.kills <= 50 && 
            p.damage >= 0 && p.damage <= 5000 &&
            p.survival >= 0 && p.survival <= 1800
        );
        
        return {
            players: validPlayers,
            confidence: this.calculateParsingConfidence(validPlayers, text)
        };
    }
    
    parseLine(line) {
        const patterns = [
            // Pattern: PlayerName | Kills | Damage | Survival | Headshots | Assists
            /^([^|\n]+?)\s*\|?\s*(\d+)\s*\|?\s*(\d+)\s*\|?\s*(\d+)\s*\|?\s*(\d+)\s*\|?\s*(\d+)$/i,
            
            // Pattern: PlayerName: Kills, Damage, Survival, Headshots, Assists
            /^([^:\n]+?)\s*:?\s*(\d+)\s*,?\s*(\d+)\s*,?\s*(\d+)\s*,?\s*(\d+)\s*,?\s*(\d+)$/i,
            
            // Pattern with labels
            /^([A-Za-z0-9_]+)\s+Kills?:\s*(\d+)\s+Damage?:\s*(\d+)\s+Time?:\s*(\d+)/i,
            
            // Simple number sequence
            /^([A-Za-z0-9_]+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)$/
        ];
        
        for (const pattern of patterns) {
            const match = line.match(pattern);
            if (match) {
                return {
                    name: match[1].trim(),
                    kills: parseInt(match[2]) || 0,
                    damage: parseInt(match[3]) || 0,
                    survival: parseInt(match[4]) || 0,
                    headshots: parseInt(match[5]) || 0,
                    assists: parseInt(match[6]) || 0
                };
            }
        }
        
        return null;
    }
    
    calculateParsingConfidence(players, originalText) {
        if (players.length === 0) return 0;
        
        // Calculate confidence based on:
        // 1. Number of players detected
        const playerScore = Math.min(100, players.length * 20);
        
        // 2. Data validity
        const validPlayers = players.filter(p => 
            p.kills > 0 && p.damage > 0 && p.survival > 0
        );
        const validityScore = (validPlayers.length / players.length) * 100;
        
        // 3. Text coverage
        const totalNumbers = (originalText.match(/\d+/g) || []).length;
        const expectedNumbers = players.length * 5; // 5 stats per player
        const coverageScore = totalNumbers > 0 ? 
            Math.min(100, (expectedNumbers / totalNumbers) * 100) : 0;
        
        return Math.round((playerScore * 0.3) + (validityScore * 0.4) + (coverageScore * 0.3));
    }
    
    async preprocessImage(imageData) {
        // Simple image preprocessing for better OCR results
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        return new Promise((resolve) => {
            img.onload = () => {
                canvas.width = img.width;
                canvas.height = img.height;
                
                // Draw image
                ctx.drawImage(img, 0, 0);
                
                // Convert to grayscale
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const data = imageData.data;
                
                for (let i = 0; i < data.length; i += 4) {
                    const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
                    data[i] = avg;     // red
                    data[i + 1] = avg; // green
                    data[i + 2] = avg; // blue
                }
                
                ctx.putImageData(imageData, 0, 0);
                
                // Increase contrast
                ctx.filter = 'contrast(1.5) brightness(1.1)';
                ctx.drawImage(canvas, 0, 0);
                
                resolve(canvas.toDataURL('image/png'));
            };
            
            img.src = imageData;
        });
    }
    
    async terminate() {
        if (this.worker) {
            await this.worker.terminate();
            this.worker = null;
            this.isInitialized = false;
        }
    }
}

// Global OCR instance
let ocrProcessor = null;

// Initialize OCR when page loads
document.addEventListener('DOMContentLoaded', async () => {
    if (typeof Tesseract !== 'undefined') {
        ocrProcessor = new OCRProcessor();
        const initialized = await ocrProcessor.initialize();
        
        if (initialized) {
            console.log('Client-side OCR ready');
            // Enable advanced OCR features
            document.getElementById('uploadZone').classList.add('ocr-enabled');
        }
    }
});

// Export for use in prediction.js
window.OCRProcessor = OCRProcessor;
window.ocrProcessor = ocrProcessor;