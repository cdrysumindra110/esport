<?php
// PUBG Mobile-specific OCR configuration
class PUBGConfig {
    // Focus on specific stats needed for prediction
    const REQUIRED_STATS = [
        'matches_played' => 'Played',
        'wins' => 'Wins',
        'top_ten' => 'Top 10', 
        'kills' => 'Eliminations',
        'kd_ratio' => 'K/D Ratio',
        'damage' => 'Total Damage',
        'headshots' => 'Headshots',
        'headshot_rate' => 'Headshot Rate',
        'assists' => 'Total Assists',
        'avg_damage' => 'AVG Damage',
        'accuracy' => 'Accuracy'
    ];
    
    // OCR patterns for PUBG Mobile career screens
    const PUBG_PATTERNS = [
        'stat_blocks' => [
            '/(\d{1,6})\s*-\s*(Played|Matches)/i',
            '/(\d{1,4})\s*-\s*Wins/i',
            '/(\d{1,6})\s*-\s*(Eliminations|Kills)/i',
            '/([\d\.]+)\s*-\s*K\/D Ratio/i',
            '/(\d{1,9}[KMG]?)\s*-\s*Total Damage/i',
            '/(\d{1,6})\s*-\s*Headshots/i',
            '/([\d\.]+%)\s*-\s*Headshot Rate/i',
            '/(\d{1,6})\s*-\s*Total Assists/i',
            '/([\d\.]+)\s*-\s*AVG Damage/i',
            '/([\d\.]+%)\s*-\s*Accuracy/i'
        ],
        
        // For individual match screens (showing multiple players)
        'player_stats' => [
            '/([A-Za-z0-9_]{3,20})\s+(\d+)\s+(\d+)\s+(\d+)\s+([\d\.]+)/', // Name Kills Damage Survival Rating
            '/(Player\d+|[A-Za-z]+)\s*[\|:]\s*K:(\d+)\s*D:(\d+)\s*S:(\d+)/i'
        ]
    ];
    
    // Weightings for prediction calculation (from extracted stats)
    const PREDICTION_WEIGHTS = [
        'kd_ratio' => 0.30,      // Most important
        'avg_damage' => 0.25,    // Consistent performance
        'headshot_rate' => 0.15, // Aim skill
        'accuracy' => 0.10,      // Shooting precision  
        'win_rate' => 0.10,      // Match success
        'matches_played' => 0.05, // Experience
        'top_ten_rate' => 0.05   // Survival ability
    ];
}
?>