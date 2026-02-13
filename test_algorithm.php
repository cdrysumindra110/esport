<?php
// test_algorithm.php - Test InfiKnight Predictor
require_once 'lib/config.php';

echo "=== InfiKnight Predictor Test ===\n\n";

// Test 1: Solo match with 2 players
echo "Test 1: Solo match (2 players)\n";
echo "---------------------------------\n";

$player1 = [
    'name' => 'ProPlayer',
    'kd' => 5.2,
    'win_ratio' => 0.35,
    'top10_rate' => 0.65,
    'avg_damage' => 650,
    'headshot_rate' => 0.45,
    'accuracy' => 0.32
];

$player2 = [
    'name' => 'AverageJoe',
    'kd' => 2.1,
    'win_ratio' => 0.15,
    'top10_rate' => 0.35,
    'avg_damage' => 350,
    'headshot_rate' => 0.25,
    'accuracy' => 0.22
];

$teams = [[$player1], [$player2]];
$result = $predictor->predictWinner($teams, 'solo');

echo "Winner Index: " . $result['winner'] . "\n";
echo "Winner: " . $teams[$result['winner']][0]['name'] . "\n";
echo "Confidence: " . $result['confidence'] . "%\n";
echo "Team Scores:\n";
foreach ($result['teams'] as $idx => $score) {
    echo "  Team $idx ({$teams[$idx][0]['name']}): " . round($score, 2) . "\n";
}
echo "\n";

// Test 2: Duo match
echo "Test 2: Duo match (2 teams of 2)\n";
echo "---------------------------------\n";

$team1 = [
    [
        'name' => 'Player1',
        'kd' => 4.0,
        'win_ratio' => 0.25,
        'top10_rate' => 0.50,
        'avg_damage' => 500,
        'headshot_rate' => 0.35,
        'accuracy' => 0.28
    ],
    [
        'name' => 'Player2',
        'kd' => 3.5,
        'win_ratio' => 0.20,
        'top10_rate' => 0.45,
        'avg_damage' => 450,
        'headshot_rate' => 0.30,
        'accuracy' => 0.25
    ]
];

$team2 = [
    [
        'name' => 'Player3',
        'kd' => 2.5,
        'win_ratio' => 0.15,
        'top10_rate' => 0.35,
        'avg_damage' => 350,
        'headshot_rate' => 0.22,
        'accuracy' => 0.20
    ],
    [
        'name' => 'Player4',
        'kd' => 2.0,
        'win_ratio' => 0.12,
        'top10_rate' => 0.30,
        'avg_damage' => 300,
        'headshot_rate' => 0.18,
        'accuracy' => 0.18
    ]
];

$duoTeams = [$team1, $team2];
$result = $predictor->predictWinner($duoTeams, 'duo');

echo "Winner Index: " . $result['winner'] . "\n";
echo "Winning Team: Team " . ($result['winner'] + 1) . "\n";
echo "Confidence: " . $result['confidence'] . "%\n";
echo "Team Scores:\n";
foreach ($result['teams'] as $idx => $score) {
    echo "  Team " . ($idx + 1) . ": " . round($score, 2) . "\n";
}
echo "\n";

// Test 3: Match stats to career stats conversion
echo "Test 3: Match stats to career stats conversion\n";
echo "------------------------------------------------\n";

$matchStats = [
    'name' => 'TestPlayer',
    'kills' => 8,
    'damage' => 650,
    'survival' => 1200,
    'headshots' => 3,
    'assists' => 2
];

$careerStats = convertMatchToCareerStats($matchStats);
echo "Match Stats:\n";
echo "  Kills: {$matchStats['kills']}\n";
echo "  Damage: {$matchStats['damage']}\n";
echo "  Survival: {$matchStats['survival']}s\n";
echo "  Headshots: {$matchStats['headshots']}\n";
echo "  Assists: {$matchStats['assists']}\n";
echo "\nConverted Career Stats:\n";
echo "  K/D: {$careerStats['kd']}\n";
echo "  Win Ratio: " . round($careerStats['win_ratio'] * 100, 1) . "%\n";
echo "  Top10 Rate: " . round($careerStats['top10_rate'] * 100, 1) . "%\n";
echo "  Avg Damage: {$careerStats['avg_damage']}\n";
echo "  Headshot Rate: " . round($careerStats['headshot_rate'] * 100, 1) . "%\n";
echo "  Accuracy: " . round($careerStats['accuracy'] * 100, 1) . "%\n";
echo "\n";

// Test 4: OCR text extraction (if available)
echo "Test 4: OCR text extraction\n";
echo "----------------------------\n";

$sampleOCRText = "
Player Stats
K/D: 4.2
Win Rate: 28%
Top 10: 52%
Avg Damage: 580
Headshot: 38%
Accuracy: 29%
";

$extracted = $predictor->extractStats($sampleOCRText);
if (!empty($extracted)) {
    echo "Successfully extracted stats from OCR text:\n";
    foreach ($extracted as $key => $value) {
        echo "  $key: $value\n";
    }
} else {
    echo "No stats extracted (this is normal if OCR text format differs)\n";
}

echo "\n=== All tests completed ===\n";
