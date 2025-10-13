<?php 
include('header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BR Tournament Group Stage</title>
<style>
:root {
    --br-primary: #1f1f2e;
    --br-secondary: #ff6f61;
    --br-accent: #ffd700;
    --br-light: #f4f4f9;
    --br-dark: #0f0f1a;
    --br-success: #2ecc71;
    --br-warning: #f39c12;
    --br-border-radius: 10px;
    --br-box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    --br-transition: all 0.3s ease;
}

* { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', sans-serif; }

/* body {
    background-color: #12121f;
    color: var(--br-light);
    padding: 20px;
    line-height: 1.6;
}

.br-container { max-width: 1200px; margin: 0 auto; } */

.br-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, var(--br-primary), var(--br-secondary));
    color: var(--br-light);
    border-radius: var(--br-border-radius);
    box-shadow: var(--br-box-shadow);
}

.br-header h1 { font-size: 2.5rem; margin-bottom: 10px; }
.br-tournament-info { display:flex; justify-content:center; gap:15px; flex-wrap:wrap; margin-top:15px; }
.br-info-item { background: rgba(255,255,255,0.1); padding:8px 15px; border-radius:20px; font-size:0.9rem; }

.br-groups { display:grid; grid-template-columns: repeat(auto-fill, minmax(300px,1fr)); gap:25px; margin-bottom:30px; }
.br-group-card { background: var(--br-dark); border-radius: var(--br-border-radius); box-shadow: var(--br-box-shadow); overflow:hidden; transition: var(--br-transition); }
.br-group-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.35); }

.br-group-header { background: var(--br-primary); color: var(--br-light); padding:15px; text-align:center; font-weight:bold; font-size:1.2rem; }

.br-teams-table { width:100%; border-collapse: collapse; }
.br-teams-table th { background-color: var(--br-dark); padding:12px 8px; text-align:left; font-weight:600; font-size:0.9rem; color: var(--br-light);}
.br-teams-table td { padding:12px 8px; border-bottom: 1px solid #333; }

.br-team-name { display:flex; align-items:center; gap:10px; }
.br-team-flag { width:24px; height:16px; border-radius:2px; object-fit:cover; }

.br-qualified { background-color: rgba(46,204,113,0.1); font-weight:bold; }
.br-qualified .br-position { color: var(--br-success); }
.br-eliminated { opacity:0.5; }

.br-position { font-weight:bold; width:30px; text-align:center; }
.br-points { font-weight:bold; color: var(--br-secondary); }

.br-matches { margin-top:40px; }
.br-matches-header { text-align:center; margin-bottom:20px; color: var(--br-secondary); font-size:1.5rem; }

.br-matches-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(350px,1fr)); gap:20px; }
.br-match-card { background: var(--br-dark); border-radius: var(--br-border-radius); box-shadow: var(--br-box-shadow); padding:15px; display:flex; flex-direction:column; gap:10px; transition: var(--br-transition); }
.br-match-card:hover { transform: translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,0.3); }

.br-match-header { display:flex; justify-content:space-between; align-items:center; padding-bottom:10px; border-bottom:1px solid #333; font-size:0.9rem; color:#aaa; }
.br-match-teams { display:flex; justify-content:space-between; align-items:center; }
.br-team { display:flex; align-items:center; gap:10px; width:45%; }
.br-team-home { justify-content:flex-start; }
.br-team-away { justify-content:flex-end; flex-direction:row-reverse; text-align:right; }
.br-team-logo { width:32px; height:32px; border-radius:50%; object-fit:cover; }
.br-score { font-weight:bold; font-size:1.2rem; min-width:60px; text-align:center; padding:5px 10px; background: var(--br-dark); border-radius: var(--br-border-radius); }
.br-match-status { display:flex; justify-content:space-between; align-items:center; font-size:0.85rem; color:#aaa; }
.br-status-live { color: var(--br-accent); font-weight:bold; display:flex; align-items:center; gap:5px; }
.br-status-live::before { content:""; width:8px; height:8px; background: var(--br-accent); border-radius:50%; display:inline-block; animation:pulse 1.5s infinite; }
@keyframes pulse { 0%{opacity:1;}50%{opacity:0.5;}100%{opacity:1;} }

.br-controls { display:flex; justify-content:center; gap:15px; margin:30px 0; flex-wrap:wrap; }
.br-btn { padding:10px 20px; border:none; border-radius:var(--br-border-radius); background: var(--br-secondary); color:white; font-weight:bold; cursor:pointer; display:flex; align-items:center; gap:8px; transition: var(--br-transition);}
.br-btn:hover { background: #e65c50; transform: translateY(-2px); }
.br-btn-outline { background:transparent; border:2px solid var(--br-secondary); color:var(--br-secondary); }
.br-btn-outline:hover { background: var(--br-secondary); color:white; }

footer { text-align:center; margin-top:50px; padding:20px; color:#777; font-size:0.9rem; }

@media(max-width:768px){ .br-groups{ grid-template-columns:1fr; } .br-matches-grid{ grid-template-columns:1fr; } }
@media(max-width:480px){ .br-match-teams{ flex-direction:column; gap:10px; } .br-team{ width:100%; justify-content:center; } .br-team-away{ flex-direction:row; text-align:left; } .br-score{ order:-1; } }

</style>
</head>
<body>

<div class="br-container">

    <header class="br-header">
        <h1>BR Royale Tournament 2025</h1>
        <p>Group Stage Standings & Matches</p>
        <div class="br-tournament-info">
            <div class="br-info-item">32 Teams</div>
            <div class="br-info-item">8 Groups</div>
            <div class="br-info-item">Top 2 Advance</div>
        </div>
    </header>

    <div class="br-controls">
        <button class="br-btn">Simulate Matches</button>
        <button class="br-btn br-btn-outline">Add Custom Team</button>
    </div>

    <div class="br-groups">
        <!-- Example Group Card -->
        <div class="br-group-card">
            <div class="br-group-header">Group A</div>
            <table class="br-teams-table">
                <thead>
                    <tr>
                        <th>#</th><th>Team</th><th>P</th><th>W</th><th>D</th><th>L</th><th>GD</th><th>PTS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="br-qualified">
                        <td class="br-position">1</td>
                        <td><div class="br-team-name"><img src="https://flagcdn.com/w40/gb.png" class="br-team-flag" alt="UK"> Alpha Squad</div></td>
                        <td>4</td><td>3</td><td>1</td><td>0</td><td>+5</td><td class="br-points">10</td>
                    </tr>
                    <tr class="br-qualified">
                        <td class="br-position">2</td>
                        <td><div class="br-team-name"><img src="https://flagcdn.com/w40/es.png" class="br-team-flag" alt="ES"> Bravo Unit</div></td>
                        <td>4</td><td>2</td><td>2</td><td>0</td><td>+3</td><td class="br-points">8</td>
                    </tr>
                    <tr>
                        <td class="br-position">3</td>
                        <td><div class="br-team-name"><img src="https://flagcdn.com/w40/it.png" class="br-team-flag" alt="IT"> Charlie Crew</div></td>
                        <td>4</td><td>1</td><td>1</td><td>2</td><td>-1</td><td class="br-points">4</td>
                    </tr>
                    <tr class="br-eliminated">
                        <td class="br-position">4</td>
                        <td><div class="br-team-name"><img src="https://flagcdn.com/w40/nl.png" class="br-team-flag" alt="NL"> Delta Force</div></td>
                        <td>4</td><td>0</td><td>0</td><td>4</td><td>-7</td><td class="br-points">0</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Add more groups here... -->
    </div>

    <div class="br-matches">
        <h2 class="br-matches-header">Recent & Upcoming Matches</h2>
        <div class="br-matches-grid">
            <!-- Example Match -->
            <div class="br-match-card">
                <div class="br-match-header"><span>Group A</span><span>Matchday 4</span></div>
                <div class="br-match-teams">
                    <div class="br-team br-team-home"><img src="https://upload.wikimedia.org/wikipedia/en/thumb/7/7a/Manchester_United_FC_crest.svg/1200px-Manchester_United_FC_crest.svg.png" class="br-team-logo" alt="Alpha"> Alpha Squad</div>
                    <div class="br-score">2 - 0</div>
                    <div class="br-team br-team-away"><img src="https://upload.wikimedia.org/wikipedia/en/thumb/4/47/FC_Barcelona_%28crest%29.svg/1200px-FC_Barcelona_%28crest%29.svg.png" class="br-team-logo" alt="Bravo"> Bravo Unit</div>
                </div>
                <div class="br-match-status"><span>Completed</span><span>Oct 10, 2025</span></div>
            </div>
        </div>
    </div>

    <footer>
        <p>BR Royale Tournament 2025 • All rights reserved © 2025</p>
    </footer>

</div>

<script>
// Simulation placeholder
document.querySelector('.br-btn').addEventListener('click', function() {
    alert('Simulating BR matches...');
});
</script>

</body>
</html>

<?php include('footer.php'); ?>
