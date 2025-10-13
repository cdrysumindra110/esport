<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battle Royale Tournament 2025</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .tournament-info {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .groups-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .group-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .group-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .group-header {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .teams-table {
            width: 100%;
            border-collapse: collapse;
        }

        .teams-table th {
            background-color: var(--light-color);
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .teams-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #eee;
        }

        .teams-table tr:last-child td {
            border-bottom: none;
        }

        .team-name {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .team-flag {
            width: 24px;
            height: 16px;
            border-radius: 2px;
            object-fit: cover;
        }

        .qualified {
            background-color: rgba(46, 204, 113, 0.1);
            font-weight: bold;
            border-left: 5px solid var(--success-color);
        }

        .eliminated {
            opacity: 0.6;
        }

        .position {
            font-weight: bold;
            width: 30px;
            text-align: center;
        }

        .points {
            font-weight: bold;
            color: var(--secondary-color);
        }

        .matches-container {
            margin-top: 40px;
        }

        .matches-header {
            text-align: center;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .matches-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .match-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 15px;
        }

        .match-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
            color: #777;
        }

        .match-summary p {
            margin: 8px 0;
            font-size: 0.95rem;
        }

        .match-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #777;
            margin-top: 10px;
        }

        .status-live {
            color: var(--accent-color);
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-live::before {
            content: "";
            width: 8px;
            height: 8px;
            background: var(--accent-color);
            border-radius: 50%;
            display: inline-block;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            background: var(--secondary-color);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
        }

        .btn-outline:hover {
            background: var(--secondary-color);
            color: white;
        }

        footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            color: #777;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .groups-container {
                grid-template-columns: 1fr;
            }
            
            .matches-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .match-summary p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Battle Royale Championship 2025</h1>
            <p>Group Stage • Points Table & Match Results</p>
            <div class="tournament-info">
                <div class="info-item">16 Teams</div>
                <div class="info-item">4 Groups</div>
                <div class="info-item">Top 2 Advance</div>
            </div>
        </header>

        <div class="controls">
            <button class="btn">Simulate Matches</button>
            <button class="btn btn-outline">Add Custom Team</button>
        </div>

        <div class="groups-container">
            <!-- Group A -->
            <div class="group-card">
                <div class="group-header">Group A</div>
                <table class="teams-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Team</th>
                            <th>Matches</th>
                            <th>Kills</th>
                            <th>Placement Pts</th>
                            <th>Total Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="qualified">
                            <td class="position">1</td>
                            <td>
                                <div class="team-name">
                                    <img src="https://flagcdn.com/w40/in.png" class="team-flag" alt="India">
                                    Team Hydra
                                </div>
                            </td>
                            <td>5</td>
                            <td>45</td>
                            <td>35</td>
                            <td class="points">80</td>
                        </tr>
                        <tr class="qualified">
                            <td class="position">2</td>
                            <td>
                                <div class="team-name">
                                    <img src="https://flagcdn.com/w40/kr.png" class="team-flag" alt="Korea">
                                    Team Nova
                                </div>
                            </td>
                            <td>5</td>
                            <td>38</td>
                            <td>30</td>
                            <td class="points">68</td>
                        </tr>
                        <tr>
                            <td class="position">3</td>
                            <td>
                                <div class="team-name">
                                    <img src="https://flagcdn.com/w40/us.png" class="team-flag" alt="USA">
                                    Team Ghost
                                </div>
                            </td>
                            <td>5</td>
                            <td>28</td>
                            <td>25</td>
                            <td class="points">53</td>
                        </tr>
                        <tr class="eliminated">
                            <td class="position">4</td>
                            <td>
                                <div class="team-name">
                                    <img src="https://flagcdn.com/w40/br.png" class="team-flag" alt="Brazil">
                                    Team Valkyrie
                                </div>
                            </td>
                            <td>5</td>
                            <td>20</td>
                            <td>15</td>
                            <td class="points">35</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Group B -->
            <div class="group-card">
                <div class="group-header">Group B</div>
                <table class="teams-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Team</th>
                            <th>Matches</th>
                            <th>Kills</th>
                            <th>Placement Pts</th>
                            <th>Total Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="qualified">
                            <td class="position">1</td>
                            <td>
                                <div class="team-name">
                                    <img src="https://flagcdn.com/w40/jp.png" class="team-flag" alt="Japan">
                                    Team Samurai
                                </div>
                            </td>
                            <td>5</td>
                            <td>50</td>
                            <td>40</td>
                            <td class="points">90</td>
                        </tr>
                        <tr class="qualified">
                            <td class="position">2</td>
                            <td>
                                <div class="team-name">
                                    <img src="https://flagcdn.com/w40/de.png" class="team-flag" alt="Germany">
                                    Team Blitz
                                </div>
                            </td>
                            <td>5</td>
                            <td>42</td>
                            <td>33</td>
                            <td class="points">75</td>
                        </tr>
                        <tr>
                            <td class="position">3</td>
                            <td>
                                <div class="team-name">
                                    <img src="https://flagcdn.com/w40/ru.png" class="team-flag" alt="Russia">
                                    Team Apex
                                </div>
                            </td>
                            <td>5</td>
                            <td>30</td>
                            <td>27</td>
                            <td class="points">57</td>
                        </tr>
                        <tr class="eliminated">
                            <td class="position">4</td>
                            <td>
                                <div class="team-name">
                                    <img src="https://flagcdn.com/w40/fr.png" class="team-flag" alt="France">
                                    Team Phantom
                                </div>
                            </td>
                            <td>5</td>
                            <td>18</td>
                            <td>14</td>
                            <td class="points">32</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="matches-container">
            <h2 class="matches-header">Recent & Upcoming Matches</h2>
            <div class="matches-grid">
                <div class="match-card">
                    <div class="match-header">
                        <span>Group A - Match 3</span>
                        <span>Erangel</span>
                    </div>
                    <div class="match-summary">
                        <p><strong>1st:</strong> Team Hydra (20 pts)</p>
                        <p><strong>2nd:</strong> Team Nova (17 pts)</p>
                        <p><strong>3rd:</strong> Team Ghost (14 pts)</p>
                        <p><strong>MVP:</strong> SniperX (7 kills)</p>
                    </div>
                    <div class="match-status">
                        <span>Completed</span>
                        <span>Oct 10, 2025</span>
                    </div>
                </div>

                <div class="match-card">
                    <div class="match-header">
                        <span>Group B - Match 3</span>
                        <span>Bermuda</span>
                    </div>
                    <div class="match-summary">
                        <p><strong>1st:</strong> Team Samurai (22 pts)</p>
                        <p><strong>2nd:</strong> Team Blitz (19 pts)</p>
                        <p><strong>3rd:</strong> Team Apex (15 pts)</p>
                        <p><strong>MVP:</strong> DragonSlayer (8 kills)</p>
                    </div>
                    <div class="match-status">
                        <span>Completed</span>
                        <span>Oct 11, 2025</span>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            <p>Battle Royale Tournament • © 2025 All Rights Reserved</p>
        </footer>
    </div>

    <script>
        document.querySelector('.btn').addEventListener('click', function() {
            alert('Match simulation would run here, updating points and rankings!');
        });
    </script>
</body>
</html>
