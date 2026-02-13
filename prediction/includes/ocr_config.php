<?php
// includes/ocr_config.php
// OCR.space API Configuration

namespace InfiKnight\OCR;

define('OCR_SPACE_API_KEY', 'K84753099788957');
define('OCR_SPACE_ENDPOINT', 'https://api.ocr.space/parse/image');
define('OCR_SPACE_TIMEOUT', 30);
define('OCR_SPACE_MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

class OCRConfig {
    public static $supportedLanguages = [
        'eng' => 'English',
        'ara' => 'Arabic',
        'bul' => 'Bulgarian',
        'chs' => 'Chinese Simplified',
        'cht' => 'Chinese Traditional',
        'hrv' => 'Croatian',
        'cze' => 'Czech',
        'dan' => 'Danish',
        'dut' => 'Dutch',
        'fin' => 'Finnish',
        'fre' => 'French',
        'ger' => 'German',
        'gre' => 'Greek',
        'hun' => 'Hungarian',
        'kor' => 'Korean',
        'ita' => 'Italian',
        'jpn' => 'Japanese',
        'pol' => 'Polish',
        'por' => 'Portuguese',
        'rus' => 'Russian',
        'slv' => 'Slovenian',
        'spa' => 'Spanish',
        'swe' => 'Swedish',
        'tur' => 'Turkish'
    ];
    
    public static $ocrEngine = [
        1 => 'Engine 1 (Default)',
        2 => 'Engine 2 (Better for tables)',
        3 => 'Engine 3 (Best for gaming stats)'
    ];
    
    public static $statPatterns = [
        'kd_ratio' => [
            'patterns' => [
                '/(\d+\.\d+)\s*K\/D\s*Ratio/i',
                '/K\/D[:\s]*(\d+\.\d+)/i',
                '/(\d+\.\d+)\s*KD/i',
                '/Kill Death[:\s]*(\d+\.\d+)/i',
                '/K\/D[:\s]*(\d+\.?\d*)/i'
            ],
            'type' => 'float',
            'min' => 0.1,
            'max' => 5.0,
            'weight' => 0.35
        ],
        'win_ratio' => [
            'patterns' => [
                '/(\d+\.?\d*)%\s*Win\s*Ratio/i',
                '/Win[:\s]*(\d+\.?\d*)%/i',
                '/(\d+\.?\d*)%\s*Win/i',
                '/Win Percentage[:\s]*(\d+\.?\d*)%/i'
            ],
            'type' => 'percentage',
            'min' => 0,
            'max' => 50,
            'weight' => 0.25
        ],
        'top10_rate' => [
            'patterns' => [
                '/(\d+\.?\d*)%\s*Top\s*10\s*Rate/i',
                '/Top\s*10[:\s]*(\d+\.?\d*)%/i',
                '/(\d+\.?\d*)%\s*Top10/i',
                '/Top 10 Percentage[:\s]*(\d+\.?\d*)%/i'
            ],
            'type' => 'percentage',
            'min' => 0,
            'max' => 100,
            'weight' => 0.15
        ],
        'avg_damage' => [
            'patterns' => [
                '/(\d+\.?\d*)\s*AVG\s*Damage/i',
                '/Avg Damage[:\s]*(\d+\.?\d*)/i',
                '/(\d+\.?\d*)\s*DMG\/Match/i',
                '/Average Damage[:\s]*(\d+\.?\d*)/i'
            ],
            'type' => 'float',
            'min' => 0,
            'max' => 800,
            'weight' => 0.12
        ],
        'headshot_rate' => [
            'patterns' => [
                '/(\d+\.?\d*)%\s*Headshot\s*Rate/i',
                '/Headshot[:\s]*(\d+\.?\d*)%/i',
                '/HS[:\s]*(\d+\.?\d*)%/i',
                '/Headshot Percentage[:\s]*(\d+\.?\d*)%/i'
            ],
            'type' => 'percentage',
            'min' => 0,
            'max' => 50,
            'weight' => 0.08
        ],
        'accuracy' => [
            'patterns' => [
                '/(\d+\.?\d*)%\s*Accuracy/i',
                '/Accuracy[:\s]*(\d+\.?\d*)%/i',
                '/Acc[:\s]*(\d+\.?\d*)%/i',
                '/Hit Rate[:\s]*(\d+\.?\d*)%/i'
            ],
            'type' => 'percentage',
            'min' => 0,
            'max' => 40,
            'weight' => 0.05
        ],
        'matches_played' => [
            'patterns' => [
                '/Matches\s*Played[:\s]*(\d+)/i',
                '/(\d+)\s*Matches\s*Played/i',
                '/Total Matches[:\s]*(\d+)/i',
                '/Games Played[:\s]*(\d+)/i'
            ],
            'type' => 'integer',
            'min' => 0,
            'max' => 50000
        ],
        'wins' => [
            'patterns' => [
                '/Wins[:\s]*(\d+)/i',
                '/(\d+)\s*Wins/i',
                '/Total Wins[:\s]*(\d+)/i',
                '/1st Place[:\s]*(\d+)/i'
            ],
            'type' => 'integer',
            'min' => 0,
            'max' => 10000
        ],
        'eliminations' => [
            'patterns' => [
                '/Eliminations[:\s]*(\d+)/i',
                '/(\d+)\s*Eliminations/i',
                '/Kills[:\s]*(\d+)/i',
                '/Total Kills[:\s]*(\d+)/i'
            ],
            'type' => 'integer',
            'min' => 0,
            'max' => 50000
        ],
        'headshots' => [
            'patterns' => [
                '/Headshots[:\s]*(\d+)/i',
                '/(\d+)\s*Headshots/i',
                '/HS Kills[:\s]*(\d+)/i'
            ],
            'type' => 'integer',
            'min' => 0,
            'max' => 15000
        ],
        'total_damage' => [
            'patterns' => [
                '/Total\s*Damage[:\s]*(\d+\.?\d*)/i',
                '/(\d+\.?\d*)\s*Total\s*Damage/i'
            ],
            'type' => 'float',
            'min' => 0,
            'max' => 5000000
        ],
        'most_eliminations' => [
            'patterns' => [
                '/Most\s*Eliminations[:\s]*(\d+)/i',
                '/(\d+)\s*Most\s*Eliminations/i',
                '/Max Kills[:\s]*(\d+)/i'
            ],
            'type' => 'integer',
            'min' => 0,
            'max' => 50
        ],
        'highest_damage' => [
            'patterns' => [
                '/Highest\s*Damage[:\s]*(\d+\.?\d*)/i',
                '/(\d+\.?\d*)\s*Highest\s*Damage/i',
                '/Max Damage[:\s]*(\d+\.?\d*)/i'
            ],
            'type' => 'float',
            'min' => 0,
            'max' => 10000
        ],
        'avg_assists' => [
            'patterns' => [
                '/Avg\.?\s*Assists[:\s]*(\d+\.?\d*)/i',
                '/(\d+\.?\d*)\s*Avg\.?\s*Assists/i',
                '/Average Assists[:\s]*(\d+\.?\d*)/i'
            ],
            'type' => 'float',
            'min' => 0,
            'max' => 5
        ],
        'total_assists' => [
            'patterns' => [
                '/Total\s*Assists[:\s]*(\d+)/i',
                '/(\d+)\s*Total\s*Assists/i',
                '/Assists[:\s]*(\d+)/i'
            ],
            'type' => 'integer',
            'min' => 0,
            'max' => 20000
        ]
    ];
}
?>