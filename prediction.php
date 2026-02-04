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
        /* CSS Variables - Professional Color Scheme */
        :root {
            --primary: #4361ee;
            --secondary: #7209b7;
            --success: #06d6a0;
            --warning: #ffd60a;
            --danger: #ef476f;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --border: #cbd5e1;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --space-xs: 4px;
            --space-sm: 8px;
            --space-md: 16px;
            --space-lg: 24px;
            --space-xl: 32px;
        }

        /* Professional Typography */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7ff 0%, #ffffff 100%);
            color: var(--dark);
            line-height: 1.6;
        }

        /* Header - Professional Design */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: var(--space-lg) var(--space-lg);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(40px);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: var(--space-sm);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            letter-spacing: -0.5px;
        }

        .hero-title i {
            font-size: 1.75rem;
        }

        .hero-subtitle {
            font-size: 0.95rem;
            opacity: 0.95;
            margin-bottom: var(--space-md);
            font-weight: 500;
        }

        .version-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
            margin-bottom: var(--space-sm);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .game-badges {
            display: flex;
            gap: var(--space-sm);
            flex-wrap: wrap;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .bg-pubg { background: #2ecc71; color: white; }
        .bg-freefire { background: #e74c3c; color: white; }
        .bg-cod { background: #3498db; color: white; }
        .bg-apex { background: #e91e63; color: white; }

        /* Main Layout */
        .main-layout {
            display: flex;
            flex-direction: column;
            gap: var(--space-xl);
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Sidebar Cards */
        .sidebar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: var(--space-lg);
        }

        .sidebar-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--border);
        }
        .sidebar-card {
            padding: 0;
            overflow: hidden;
        }
        .sidebar-card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-2px);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: var(--space-md) var(--space-md);
            margin: 0;
        }

        .card-title i {
            font-size: 1.1rem;
        }

        /* Input Method Selector */
        .input-method-selector {
            padding: var(--space-lg);
        }

        .method-tabs {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin-bottom: var(--space-lg);
            background: var(--light);
            padding: 4px;
            border-radius: var(--radius-md);
        }

        .method-tab {
            padding: var(--space-md);
            background: white;
            border: 2px solid transparent;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--gray);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .method-tab i {
            font-size: 1.25rem;
        }

        .method-tab.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .method-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .method-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Upload Zone */
        .upload-zone {
            border: 2px dashed var(--border);
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            text-align: center;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.05), rgba(114, 9, 183, 0.05));
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .upload-zone:hover {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.1));
            transform: scale(1.01);
        }

        .upload-zone.highlight {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.15), rgba(114, 9, 183, 0.15));
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: var(--space-sm);
        }

        .upload-zone h3 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 6px;
        }

        .upload-hint {
            color: var(--gray);
            font-size: 0.85rem;
            margin-bottom: var(--space-sm);
        }

        /* Form Elements */
        .form-group {
            margin-bottom: var(--space-md);
        }

        .form-group label {
            display: block;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--dark);
            margin-bottom: var(--space-xs);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-weight: 500;
            background: white;
            color: var(--dark);
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
            background: white;
        }

        .form-control:hover {
            border-color: var(--primary);
        }

        .form-control::placeholder {
            color: var(--gray);
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            border: none;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-transform: none;
            letter-spacing: 0.3px;
            line-height: 1.4;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            border: none;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: var(--light);
            color: var(--dark);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--border);
            border-color: var(--primary);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef476f, #d62828);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 71, 111, 0.3);
        }

        .btn-sm {
            padding: 8px 14px;
            font-size: 0.8rem;
        }

        .btn-lg {
            padding: 14px 32px;
            font-size: 1rem;
            width: 100%;
            font-weight: 700;
        }

        .btn-process {
            width: 100%;
            margin-top: var(--space-md);
            font-size: 1.05rem;
            padding: 16px;
        }

        /* Match Type Selector */
        .match-type-selector {
            margin-bottom: var(--space-lg);
        }

        .match-type-selector h3 {
            font-weight: 700;
            margin-bottom: var(--space-md);
            color: var(--dark);
            font-size: 0.95rem;
        }

        .match-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-sm);
        }

        .match-option input {
            display: none;
        }

        .match-option .option-content {
            padding: var(--space-md);
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .match-option input:checked + .option-content {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.05));
            color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .match-option .option-content i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .match-option .option-content span {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .match-option .option-content small {
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* Team Configuration */
        .team-config-section {
            margin-bottom: var(--space-lg);
            padding: var(--space-md);
            background: var(--light);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--primary);
        }

        .team-config-section h3 {
            font-weight: 700;
            margin-bottom: var(--space-md);
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-size: 0.95rem;
        }

        .team-controls {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: var(--space-sm);
            margin-bottom: var(--space-md);
            align-items: end;
        }

        .team-controls .form-group {
            margin-bottom: 0;
        }

        /* Team Inputs Container */
        .team-inputs-container {
            max-height: 700px;
            overflow-y: auto;
            padding: var(--space-sm);
            margin-top: var(--space-md);
        }

        .team-players-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-md);
            margin-top: var(--space-md);
        }

        @media (max-width: 992px) {
            .team-players-grid {
                grid-template-columns: 1fr;
            }
        }

        .team-section {
            margin-bottom: var(--space-lg);
            padding: var(--space-lg);
            background: linear-gradient(to bottom, white 0%, #f8fafc 100%);
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .team-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .team-section:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }

        .team-header {
            margin-bottom: var(--space-md);
            padding-bottom: var(--space-sm);
            border-bottom: 2px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }

        .team-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: var(--space-md);
            flex-wrap: wrap;
        }

        .team-header h4 {
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
        }

        .team-header h4 i {
            color: var(--primary);
            font-size: 1rem;
        }

        .team-name-group {
            flex: 1;
            min-width: 200px;
            max-width: 400px;
        }

        .team-name-group label {
            font-weight: 600;
            font-size: 0.75rem;
            color: var(--gray);
            text-transform: uppercase;
            display: block;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .team-name-input {
            width: 100%;
            padding: 11px 13px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }

        .team-name-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .remove-team-btn {
            background: linear-gradient(135deg, #ef476f, #d62828);
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            box-shadow: var(--shadow-sm);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .remove-team-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 71, 111, 0.3);
        }

        .remove-team-btn:active {
            transform: translateY(0);
        }

        .team-player-input {
            margin-bottom: 0;
            padding: var(--space-md);
            background: white;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .team-player-input::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            border-radius: var(--radius-sm) 0 0 var(--radius-sm);
        }

        .team-player-input:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
            transform: translateX(2px);
        }

        .team-player-input h5 {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: var(--space-sm);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .team-player-input h5 i {
            font-size: 0.85rem;
        }

        .team-player-name,
        .team-player-kills,
        .team-player-damage,
        .team-player-survival-min,
        .team-player-survival-sec,
        .team-player-headshots,
        .team-player-assists {
            padding: 10px 12px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-weight: 600;
            background: var(--light);
            color: var(--dark);
            transition: all 0.3s ease;
            font-family: inherit;
            width: 100%;
        }

        .team-player-name:focus,
        .team-player-kills:focus,
        .team-player-damage:focus,
        .team-player-survival-min:focus,
        .team-player-survival-sec:focus,
        .team-player-headshots:focus,
        .team-player-assists:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            background: white;
            transform: translateY(-1px);
        }

        .team-player-name:hover:not(:focus),
        .team-player-kills:hover:not(:focus),
        .team-player-damage:hover:not(:focus),
        .team-player-survival-min:hover:not(:focus),
        .team-player-survival-sec:hover:not(:focus),
        .team-player-headshots:hover:not(:focus),
        .team-player-assists:hover:not(:focus) {
            border-color: #94a3b8;
            background: white;
        }

        .team-player-name::placeholder,
        .team-player-kills::placeholder,
        .team-player-damage::placeholder,
        .team-player-survival-min::placeholder,
        .team-player-survival-sec::placeholder,
        .team-player-headshots::placeholder,
        .team-player-assists::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        /* Input Grid */
        .input-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-sm) var(--space-md);
            flex: 1;
        }

        .input-group {
            display: flex;
            flex-direction: column;
        }

        .input-group label {
            font-weight: 600;
            font-size: 0.7rem;
            color: var(--gray);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .input-group input {
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-weight: 600;
            background: var(--light);
            color: var(--dark);
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            background: white;
            transform: translateY(-1px);
        }

        .input-group input:hover:not(:focus) {
            border-color: #94a3b8;
            background: white;
        }

        .input-group input::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        /* Survival Time Wrapper */
        .survival-time-wrapper {
            display: flex;
            gap: 12px;
            align-items: center;
            width: 40%;
            justify-content: flex-start;
        }

        .survival-time-wrapper input {
            flex: 0 0 auto;
            min-width: 70px;
            width: 70px;
            padding: 10px 12px;
            text-align: center;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0px;
            box-sizing: border-box;
        }

        .survival-time-wrapper span {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.2rem;
            line-height: 1;
            flex-shrink: 0;
            width: 12px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Player Input Section */
        .player-input-section {
            margin-bottom: 0;
            padding: var(--space-md);
            background: white;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
            position: relative;
            width: 100%;
        }

        .player-input-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            border-radius: var(--radius-sm) 0 0 var(--radius-sm);
        }

        .player-input-section:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
            transform: translateX(2px);
        }

        .player-input-section h4 {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: var(--space-sm);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .player-input-section h4 i {
            font-size: 0.85rem;
        }

        .player-name,
        .player-kills,
        .player-damage,
        .player-survival-min,
        .player-survival-sec,
        .player-headshots,
        .player-assists {
            padding: 10px 12px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-weight: 600;
            background: var(--light);
            color: var(--dark);
            transition: all 0.3s ease;
            font-family: inherit;
            width: 100%;
        }

        .player-name:focus,
        .player-kills:focus,
        .player-damage:focus,
        .player-survival-min:focus,
        .player-survival-sec:focus,
        .player-headshots:focus,
        .player-assists:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            background: white;
            transform: translateY(-1px);
        }

        .player-name:hover:not(:focus),
        .player-kills:hover:not(:focus),
        .player-damage:hover:not(:focus),
        .player-survival-min:hover:not(:focus),
        .player-survival-sec:hover:not(:focus),
        .player-headshots:hover:not(:focus),
        .player-assists:hover:not(:focus) {
            border-color: #94a3b8;
            background: white;
        }

        .player-name::placeholder,
        .player-kills::placeholder,
        .player-damage::placeholder,
        .player-survival-min::placeholder,
        .player-survival-sec::placeholder,
        .player-headshots::placeholder,
        .player-assists::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        .btn i {
            font-size: 1rem;
            flex-shrink: 0;
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Player Inputs Container */
        .player-inputs {
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
        }

        /* Process Actions */
        .process-actions {
            margin-top: var(--space-lg);
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }

        /* Recent Predictions */
        /* .recent-list {
            max-height: 400px;
            overflow-y: auto;
        } */

        .recent-loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: var(--space-lg);
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Main Content */
        .main-content {
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
        }

        .card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-2px);
        }

        .card h2,
        .card h3 {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: var(--space-md);
        }

        .card h2 {
            font-size: 1.5rem;
        }

        .card h3 {
            font-size: 1.25rem;
        }

        /* Processing Section */
        .processing-section {
            padding: var(--space-xl);
        }

        .processing-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-lg);
        }

        .processing-time {
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .processing-steps {
            margin-bottom: var(--space-lg);
        }

        .step {
            display: grid;
            grid-template-columns: 50px 1fr 50px;
            gap: var(--space-md);
            align-items: center;
            padding: var(--space-md);
            margin-bottom: var(--space-md);
            background: var(--light);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--border);
            transition: all 0.3s ease;
        }

        .step.active {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.05));
            border-left-color: var(--primary);
        }

        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .step-content h4 {
            font-weight: 700;
            margin-bottom: 4px;
            color: var(--dark);
        }

        .step-content p {
            font-size: 0.9rem;
            color: var(--gray);
            margin: 0;
        }

        .step-status {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: var(--gray);
            box-shadow: var(--shadow-sm);
        }

        .step.active .step-status {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        /* Progress Bar */
        .progress-container {
            height: 8px;
            background: var(--light);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: var(--space-lg);
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 4px;
            transition: width 0.3s ease;
            width: 0%;
        }

        /* Results Section */
        .results-section {
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: var(--space-lg);
        }

        .result-meta {
            display: flex;
            gap: var(--space-lg);
            margin-top: var(--space-sm);
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .confidence-badge {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: var(--space-md) var(--space-lg);
            border-radius: var(--radius-md);
            text-align: center;
            min-width: 140px;
            box-shadow: var(--shadow-lg);
        }

        .confidence-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .confidence-label {
            font-size: 0.8rem;
            margin-top: 4px;
            opacity: 0.9;
        }

        /* Winner Section */
        .winner-section {
            padding: var(--space-xl);
        }

        .winner-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-lg);
        }

        .winner-tag {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .winner-card {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.05), rgba(114, 9, 183, 0.05));
            border: 2px solid var(--primary);
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: var(--space-xl);
            align-items: center;
            margin-bottom: var(--space-lg);
        }

        .winner-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .winner-info h4 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: var(--space-md);
        }

        .winner-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: var(--space-md);
        }

        .winner-stats .stat {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            color: var(--gray);
        }

        .winner-stats .stat i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        .winner-stats .stat strong {
            color: var(--dark);
            font-size: 1.1rem;
        }

        /* Performance Grid */
        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--space-lg);
            padding: var(--space-lg);
        }

        .performance-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.5), rgba(248, 250, 252, 1));
            border: 2px solid var(--border);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .performance-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .performance-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-xl);
            transform: translateY(-4px);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            margin-bottom: var(--space-md);
            padding-bottom: var(--space-md);
            border-bottom: 2px solid var(--border);
        }

        .card-header i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .card-header h4 {
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .card-body {
            position: relative;
            z-index: 1;
        }

        .performer-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: var(--space-md);
        }

        .performer-stats {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .performer-stats .stat {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .performer-stats .stat strong {
            color: var(--dark);
            font-weight: 700;
        }

        .synergy-score {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: var(--space-md);
        }

        .score-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
        }

        .score-label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .synergy-meter {
            height: 6px;
            background: var(--light);
            border-radius: 3px;
            overflow: hidden;
            margin-top: var(--space-sm);
        }

        .meter-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 3px;
            transition: width 0.5s ease;
        }

        /* Error Section */
        .error-section {
            padding: var(--space-xl);
            background: linear-gradient(135deg, rgba(239, 71, 111, 0.05), rgba(214, 40, 40, 0.05));
            border-left: 4px solid #ef476f;
        }

        .error-header {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
            color: #ef476f;
        }

        .error-header i {
            font-size: 1.5rem;
        }

        .error-header h3 {
            color: #ef476f;
        }

        .error-body p {
            color: var(--gray);
            margin-bottom: var(--space-lg);
        }

        .error-actions {
            display: flex;
            gap: var(--space-md);
        }

        /* New Prediction Section */
        .new-prediction-section {
            text-align: center;
            padding: var(--space-xl);
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }

        .new-prediction-section h3 {
            color: white;
        }

        /* Team Members */
        .team-members {
            margin-top: var(--space-lg);
            padding-top: var(--space-lg);
            border-top: 2px solid var(--border);
        }

        .team-members h5 {
            font-weight: 700;
            margin-bottom: var(--space-md);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .team-performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-md);
        }

        .team-performance-card {
            padding: var(--space-md);
            border-radius: var(--radius-md);
            background: var(--light);
            border: 2px solid var(--border);
            transition: all 0.3s ease;
        }

        .team-performance-card.winner {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .team-rank {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .team-name {
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: var(--space-sm);
        }

        .team-stats {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 0.9rem;
        }

        /* Utility Classes */
        .hidden {
            display: none !important;
        }

        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        .text-center {
            text-align: center;
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: var(--radius-md);
            color: white;
            font-weight: 600;
            box-shadow: var(--shadow-xl);
            z-index: 9999;
            opacity: 0;
            transform: translateX(400px);
            transition: all 0.3s ease;
            max-width: 400px;
        }

        .notification.show {
            opacity: 1;
            transform: translateX(0);
        }

        .notification-success {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .notification-error {
            background: linear-gradient(135deg, var(--danger), #d62828);
        }

        .notification-info {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification-content i {
            font-size: 1.2rem;
        }

        /* OCR Preview Styles */
        .ocr-results {
            background: var(--light);
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            margin-top: var(--space-lg);
        }

        .ocr-header {
            margin-bottom: var(--space-lg);
            padding-bottom: var(--space-md);
            border-bottom: 2px solid var(--border);
        }

        .ocr-header h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: var(--space-sm);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ocr-header h4 i {
            color: var(--success);
            font-size: 1.3rem;
        }

        .ocr-meta {
            display: flex;
            gap: var(--space-md);
            flex-wrap: wrap;
            margin-top: var(--space-sm);
        }

        .ocr-meta .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: var(--gray);
        }

        .ocr-meta .meta-item i {
            color: var(--primary);
        }

        .confidence-display {
            background: white;
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            border-left: 4px solid var(--primary);
        }

        .confidence-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .confidence-value {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: var(--space-sm);
        }

        .confidence-value.high {
            color: var(--success);
        }

        .confidence-value.medium {
            color: var(--warning);
        }

        .confidence-value.low {
            color: var(--danger);
        }

        .confidence-bar {
            height: 6px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
        }

        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 3px;
            transition: width 0.5s ease;
        }

        .career-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }

        .stat-card {
            background: white;
            padding: var(--space-md);
            border-radius: var(--radius-md);
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: var(--space-md);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.05));
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--primary);
            flex-shrink: 0;
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--dark);
        }

        .raw-text-preview {
            background: white;
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            border-left: 4px solid var(--border);
        }

        .raw-text-preview h5 {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: var(--space-sm);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .raw-text-preview h5 i {
            color: var(--primary);
        }

        .text-preview {
            background: var(--light);
            padding: var(--space-md);
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            color: var(--gray);
            font-family: 'Courier New', monospace;
            max-height: 150px;
            overflow-y: auto;
            line-height: 1.5;
        }

        .ocr-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--space-md);
            margin-top: var(--space-lg);
        }

        .ocr-actions .btn {
            width: 100%;
            padding: 12px 20px;
        }

        @media (max-width: 768px) {
            .ocr-actions {
                grid-template-columns: 1fr;
            }
        }

        .ocr-error {
            background: linear-gradient(135deg, rgba(239, 71, 111, 0.05), rgba(214, 40, 40, 0.05));
            padding: var(--space-xl);
            border-radius: var(--radius-lg);
            border-left: 4px solid var(--danger);
            text-align: center;
        }

        .error-icon {
            margin-bottom: var(--space-md);
        }

        .ocr-error h4 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: var(--space-md);
        }

        .ocr-error p {
            margin-bottom: var(--space-lg);
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }

        .error-actions .btn {
            width: 100%;
        }

        .ocr-processing {
            padding: var(--space-xl);
        }

        .ocr-processing .spinner {
            margin: 0 auto;
        }

        .ocr-processing p {
            text-align: center;
            color: var(--gray);
            margin-bottom: var(--space-lg);
        }

        .career-note {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.05), rgba(114, 9, 183, 0.05));
            border-left: 4px solid var(--primary);
            padding: var(--space-md);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: var(--space-md);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .career-note i {
            color: var(--primary);
            flex-shrink: 0;
        }

        .btn-outline-secondary {
            background: white;
            color: var(--dark);
            border: 2px solid var(--border);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(6, 214, 160, 0.3);
            border: none;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 214, 160, 0.4);
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                grid-template-columns: 1fr;
            }

            .hero-title {
                font-size: 1.75rem;
            }

            .match-options {
                grid-template-columns: 1fr;
            }

            .input-grid {
                grid-template-columns: 1fr;
            }

            .performance-grid {
                grid-template-columns: 1fr;
            }

            .winner-card {
                grid-template-columns: 1fr;
                gap: var(--space-md);
                text-align: center;
            }

            .winner-icon {
                width: 80px;
                height: 80px;
            }

            .performance-grid {
                grid-template-columns: 1fr;
            }

            .results-header {
                flex-direction: column;
                gap: var(--space-md);
            }

            .method-tabs {
                grid-template-columns: 1fr;
            }

            .team-controls {
                grid-template-columns: 1fr;
                align-items: stretch;
            }

            .team-header {
                gap: var(--space-sm);
            }

            .team-header-row {
                flex-direction: column;
                align-items: stretch;
            }

            .team-name-group {
                max-width: 100%;
            }
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
                                    <h4><i class="fas fa-user-circle"></i> Player 1</h4>
                                    <div class="input-grid">
                                        <div class="input-group">
                                            <label>Player Name</label>
                                            <input type="text" class="player-name" 
                                                   placeholder="Enter name" value="Player 1">
                                        </div>
                                        <div class="input-group">
                                            <label>Kills</label>
                                            <input type="number" class="player-kills" 
                                                   min="0" max="50" placeholder="0" value="5">
                                        </div>
                                        <div class="input-group">
                                            <label>Damage</label>
                                            <input type="number" class="player-damage" 
                                                   min="0" max="5000" placeholder="0" value="250">
                                        </div>
                                        <div class="input-group">
                                            <label>Survival Time</label>
                                            <div class="survival-time-wrapper">
                                                <input type="number" class="player-survival-min" 
                                                       min="0" max="30" placeholder="MM" value="7" title="Minutes">
                                                <span>:</span>
                                                <input type="number" class="player-survival-sec" 
                                                       min="0" max="59" placeholder="SS" value="30" title="Seconds">
                                            </div>
                                        </div>
                                        <div class="input-group">
                                            <label>Headshots</label>
                                            <input type="number" class="player-headshots" 
                                                   min="0" max="50" placeholder="0" value="2">
                                        </div>
                                        <div class="input-group">
                                            <label>Assists</label>
                                            <input type="number" class="player-assists" 
                                                   min="0" max="20" placeholder="0" value="0">
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
                
                <!-- Recent Predictions
                <div class="sidebar-card" id="recentPredictions">
                    <h2 class="card-title">
                        <i class="fas fa-history"></i> Recent Predictions
                    </h2>
                    <div class="recent-list" id="recentList">
                        <div class="recent-loading">
                            <div class="spinner" style="width: 20px; height: 20px;"></div>
                        </div>
                    </div>
                </div> -->
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
    
    // 3. Add Drag & Drop
    addDragAndDrop();
    
    // 4. Initialize Event Listeners
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

// 2.5 Add Drag & Drop Function
function addDragAndDrop() {
    const uploadZone = document.getElementById('uploadZone');
    if (!uploadZone) return;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        uploadZone.classList.add('highlight');
    }
    
    function unhighlight() {
        uploadZone.classList.remove('highlight');
    }
    
    uploadZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            const fileInput = document.getElementById('statsFile');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            fileInput.files = dataTransfer.files;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
            
            showNotification('File dropped successfully!', 'success');
        }
    }
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
    
    // B. File Input Change - FIXED
    if (statsFile) {
        statsFile.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                console.log('File selected:', fileName);
                showOCRProcessing();
                // Call processOCRFile directly
                processOCRFile(e.target.files[0]);
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
        teamDiv.innerHTML = `
            <div class="team-header">
                <div class="team-header-row">
                    <h4><i class="fas fa-users"></i> Team ${i + 1}</h4>
                    <button class="remove-team-btn" onclick="removeTeam(this)" title="Remove this team">
                        <i class="fas fa-trash-alt"></i>
                        Remove Team
                    </button>
                </div>
                <div class="input-group team-name-group">
                    <label>Team Name</label>
                    <input type="text" class="form-control team-name-input" 
                        placeholder="Enter team name" 
                        value="Team ${i + 1}">
                </div>
            </div>
            <div class="team-players-grid">
            </div>
        `;
        
        // Add players to the grid container
        const playersGrid = teamDiv.querySelector('.team-players-grid');
        for (let j = 0; j < playersPerTeam; j++) {
            playersGrid.appendChild(createTeamPlayerInput(i, j));
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
        <h5><i class="fas fa-user-circle"></i> Player ${playerIndex + 1}</h5>
        <div class="input-grid">
            <div class="input-group">
                <label>Name</label>
                <input type="text" class="team-player-name" 
                    placeholder="Player name" 
                    value="Player ${playerIndex + 1}">
            </div>
            <div class="input-group">
                <label>Kills</label>
                <input type="number" class="team-player-kills" 
                    min="0" max="50" placeholder="0" value="${Math.floor(Math.random() * 10)}">
            </div>
            <div class="input-group">
                <label>Damage</label>
                <input type="number" class="team-player-damage" 
                    min="0" max="5000" placeholder="0" value="${Math.floor(Math.random() * 500) + 100}">
            </div>
            <div class="input-group">
                <label>Survival Time</label>
                <div class="survival-time-wrapper">
                    <input type="number" class="team-player-survival-min" 
                        min="0" max="30" placeholder="MM" value="${Math.floor(Math.random() * 10 + 5)}" 
                        title="Minutes">
                    <span>:</span>
                    <input type="number" class="team-player-survival-sec" 
                        min="0" max="59" placeholder="SS" value="${Math.floor(Math.random() * 60)}" 
                        title="Seconds">
                </div>
            </div>
            <div class="input-group">
                <label>Headshots</label>
                <input type="number" class="team-player-headshots" 
                    min="0" max="50" placeholder="0" value="${Math.floor(Math.random() * 5)}">
            </div>
            <div class="input-group">
                <label>Assists</label>
                <input type="number" class="team-player-assists" 
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
        <h4><i class="fas fa-user-circle"></i> Player ${index + 1}</h4>
        <div class="input-grid">
            <div class="input-group">
                <label>Player Name</label>
                <input type="text" class="player-name" 
                       placeholder="Enter name" value="Player ${index + 1}">
            </div>
            <div class="input-group">
                <label>Kills</label>
                <input type="number" class="player-kills" 
                       min="0" max="50" placeholder="0" value="${Math.floor(Math.random() * 10)}">
            </div>
            <div class="input-group">
                <label>Damage</label>
                <input type="number" class="player-damage" 
                       min="0" max="5000" placeholder="0" value="${Math.floor(Math.random() * 500) + 100}">
            </div>
            <div class="input-group">
                <label>Survival Time</label>
                <div class="survival-time-wrapper">
                    <input type="number" class="player-survival-min" 
                        min="0" max="30" placeholder="MM" value="${Math.floor(Math.random() * 10 + 5)}" 
                        title="Minutes">
                    <span>:</span>
                    <input type="number" class="player-survival-sec" 
                        min="0" max="59" placeholder="SS" value="${Math.floor(Math.random() * 60)}" 
                        title="Seconds">
                </div>
            </div>
            <div class="input-group">
                <label>Headshots</label>
                <input type="number" class="player-headshots" 
                       min="0" max="50" placeholder="0" value="${Math.floor(Math.random() * 5)}">
            </div>
            <div class="input-group">
                <label>Assists</label>
                <input type="number" class="player-assists" 
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
            h4.innerHTML = `<i class="fas fa-user-circle"></i> Player ${index + 1}`;
        }
        
        // Update placeholder name if empty
        const nameInput = section.querySelector('.player-name');
        if (nameInput && !nameInput.value) {
            nameInput.placeholder = `Player ${index + 1}`;
        }
    });
}

// 10a. Remove Team
function removeTeam(button) {
    const teamSection = button.closest('.team-section');
    if (!teamSection) return;
    
    const teamInputs = document.getElementById('teamInputs');
    const sections = teamInputs.querySelectorAll('.team-section');
    
    // Don't remove if there are only 2 teams
    if (sections.length <= 2) {
        showNotification('Cannot have less than 2 teams!', 'error');
        return;
    }
    
    teamSection.remove();
    
    // Renumber remaining teams
    renumberTeams();
    
    showNotification('Team removed successfully!', 'info');
}

// 10b. Renumber Teams
function renumberTeams() {
    const teamInputs = document.getElementById('teamInputs');
    const sections = teamInputs.querySelectorAll('.team-section');
    
    sections.forEach((section, index) => {
        // Update team heading
        const h4 = section.querySelector('.team-header h4');
        if (h4) {
            h4.innerHTML = `<i class="fas fa-users"></i> Team ${index + 1}`;
        }
        
        // Update team name placeholder if it's the default value
        const teamNameInput = section.querySelector('.team-name-input');
        if (teamNameInput && teamNameInput.value.startsWith('Team ')) {
            teamNameInput.value = `Team ${index + 1}`;
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
    const uploadZone = document.getElementById('uploadZone');
    const ocrPreview = document.getElementById('ocrPreview');
    
    if (ocrPreview) {
        ocrPreview.classList.remove('hidden');
        ocrPreview.innerHTML = `
            <div class="ocr-processing">
                <div class="spinner" style="margin: 0 auto; margin-bottom: 20px;"></div>
                <p>Uploading and analyzing image...</p>
                <div class="progress-container" style="margin-top: 20px;">
                    <div class="progress-bar" style="width: 0%; transition: width 0.3s;"></div>
                </div>
            </div>
        `;
    }
    
    // Hide upload zone
    if (uploadZone) {
        uploadZone.style.display = 'none';
    }
    
    // Scroll to OCR preview
    setTimeout(() => {
        if (ocrPreview) {
            ocrPreview.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 100);
}

// 13. NEW: Process OCR file with backend - FIXED VERSION
async function processOCRFile(file) {
    try {
        console.log('Starting OCR processing for file:', file.name, file.size);
        
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
        
        // Send to backend with proper error handling
        console.log('Sending to backend...');
        const response = await fetch('ocr_backend.php', {
            method: 'POST',
            body: formData
        });
        
        clearInterval(progressInterval);
        if (progressBar) progressBar.style.width = '100%';
        
        console.log('Response status:', response.status);
        
        // Get response as text first to debug
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        // Try to parse JSON
        let result;
        try {
            result = JSON.parse(responseText);
            console.log('Parsed JSON:', result);
        } catch (e) {
            console.error('JSON parse error:', e);
            console.log('Response text (first 500 chars):', responseText.substring(0, 500));
            showOCRError(`Server returned invalid JSON. Check if PHP errors are showing. Response: ${responseText.substring(0, 200)}...`);
            return;
        }
        
        if (result.success) {
            console.log('OCR successful, displaying stats...');
            displayCareerStats(result.data);
        } else {
            console.error('OCR failed:', result.error);
            showOCRError(result.error || 'OCR processing failed');
        }
        
    } catch (error) {
        console.error('OCR Network Error:', error);
        showOCRError('Network error: ' + error.message);
    }
}

// 14. NEW: Show OCR Error
function showOCRError(message) {
    const ocrPreview = document.getElementById('ocrPreview');
    if (!ocrPreview) return;
    
    ocrPreview.classList.remove('hidden');
    ocrPreview.innerHTML = `
        <div class="ocr-error" style="text-align: center; padding: 30px;">
            <div class="error-icon" style="font-size: 48px; color: #f44336; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h4 style="color: #f44336; margin-bottom: 15px;">OCR Processing Failed</h4>
            <p style="color: #666; margin-bottom: 20px;">${message}</p>
            <div class="error-actions">
                <button class="btn btn-secondary" onclick="switchToManualInput()">
                    <i class="fas fa-keyboard"></i> Enter Data Manually
                </button>
                <button class="btn btn-outline-primary" onclick="document.getElementById('statsFile').click()">
                    <i class="fas fa-redo"></i> Try Another Image
                </button>
            </div>
        </div>
    `;
    
    console.error('OCR Error:', message);
}

// 15. NEW: Display career stats instead of player list
function displayCareerStats(data) {
    const ocrPreview = document.getElementById('ocrPreview');
    if (!ocrPreview) return;
    
    const stats = data.extracted_data?.career_stats || {};
    const confidence = data.confidence || 0;
    const ocrText = data.ocr_text || '';
    
    let html = `
        <div class="ocr-results">
            <div class="ocr-header">
                <h4><i class="fas fa-user-circle text-success"></i> Career Stats Analyzed</h4>
                <div class="ocr-meta">
                    <span class="meta-item">
                        <i class="fas fa-database"></i> Source: Career Screenshot
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-gamepad"></i> PUBG Mobile
                    </span>
                </div>
            </div>
            
            <div class="confidence-display">
                <div class="confidence-label">Extraction Confidence:</div>
                <div class="confidence-value ${confidence > 80 ? 'high' : confidence > 60 ? 'medium' : 'low'}">
                    ${confidence}%
                </div>
                <div class="confidence-bar">
                    <div class="confidence-fill" style="width: ${confidence}%"></div>
                </div>
            </div>
            
            <div class="career-stats-grid">
    `;
    
    // Key stats to display
    const keyStats = [
        { key: 'matches_played', label: 'Matches Played', icon: 'fa-gamepad', format: v => v.toLocaleString() },
        { key: 'wins', label: 'Wins', icon: 'fa-trophy', format: v => v.toLocaleString() },
        { key: 'win_rate', label: 'Win Rate', icon: 'fa-chart-line', format: v => v + '%' },
        { key: 'eliminations', label: 'Total Kills', icon: 'fa-skull', format: v => v.toLocaleString() },
        { key: 'kd_ratio', label: 'K/D Ratio', icon: 'fa-balance-scale', format: v => v },
        { key: 'avg_kills', label: 'Avg Kills/Match', icon: 'fa-crosshairs', format: v => v },
        { key: 'total_damage', label: 'Total Damage', icon: 'fa-fire', format: v => formatNumber(v) },
        { key: 'avg_damage', label: 'Avg Damage', icon: 'fa-bullseye', format: v => v },
        { key: 'headshot_rate', label: 'Headshot Rate', icon: 'fa-crosshairs', format: v => v + '%' },
        { key: 'accuracy', label: 'Accuracy', icon: 'fa-bullseye', format: v => v + '%' },
        { key: 'assists', label: 'Assists', icon: 'fa-handshake', format: v => v.toLocaleString() },
        { key: 'most_eliminations', label: 'Most Kills (Match)', icon: 'fa-crown', format: v => v }
    ];
    
    keyStats.forEach(stat => {
        if (stats[stat.key] !== undefined) {
            html += `
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas ${stat.icon}"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">${stat.label}</div>
                        <div class="stat-value">${stat.format(stats[stat.key])}</div>
                    </div>
                </div>
            `;
        }
    });
    
    html += `</div>`;
    
    // Raw text preview (for debugging)
    if (ocrText) {
        html += `
            <div class="raw-text-preview">
                <h5><i class="fas fa-file-alt"></i> Extracted Text Preview</h5>
                <div class="text-preview">
                    ${ocrText.substring(0, 300).replace(/\n/g, '<br>')}...
                </div>
            </div>
        `;
    }
    
    html += `
        <div class="ocr-actions">
            <button class="btn btn-success" onclick="useCareerStatsForPrediction(${JSON.stringify(stats).replace(/"/g, '&quot;')})">
                <i class="fas fa-robot"></i> Generate Career-Based Prediction
            </button>
            <button class="btn btn-primary" onclick="createPlayerFromCareerStats(${JSON.stringify(stats).replace(/"/g, '&quot;')})">
                <i class="fas fa-user-plus"></i> Create Virtual Player
            </button>
            <button class="btn btn-outline-secondary" onclick="switchToManualInput()">
                <i class="fas fa-keyboard"></i> Enter Match Stats Manually
            </button>
        </div>
    </div>`;
    
    ocrPreview.innerHTML = html;
    
    // Store for later use
    window.careerStats = stats;
    window.ocrData = data;
    
    showNotification('Career stats extracted successfully!', 'success');
}

// 16. NEW: Format large numbers
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toLocaleString();
}

// 17. NEW: Create virtual player from career stats
function createPlayerFromCareerStats(stats) {
    // Switch to manual input tab
    document.querySelector('.method-tab[data-method="manual"]').click();
    
    // Clear existing inputs
    const playerInputs = document.getElementById('playerInputs');
    playerInputs.innerHTML = '';
    
    // Calculate estimated per-match stats
    const estimatedKills = stats.avg_kills || (stats.kd_ratio * 2) || 5;
    const estimatedDamage = stats.avg_damage || 300;
    const estimatedSurvival = estimateSurvivalTime(stats);
    const estimatedHeadshots = Math.round(estimatedKills * ((stats.headshot_rate || 15) / 100));
    const estimatedAssists = stats.avg_assists || 1.5;
    
    // Create player input
    const playerDiv = document.createElement('div');
    playerDiv.className = 'player-input-section fade-in';
    playerDiv.innerHTML = `
        <h4><i class="fas fa-user-crown"></i> Career Player (Estimated)</h4>
        <div class="input-grid">
            <div class="input-group">
                <label>Player Name</label>
                <input type="text" class="form-control player-name" 
                       value="Career Pro" placeholder="Career Player">
            </div>
            <div class="input-group">
                <label>Kills (Estimated)</label>
                <input type="number" class="form-control player-kills" 
                       min="0" max="50" value="${Math.round(estimatedKills)}">
            </div>
            <div class="input-group">
                <label>Damage (Estimated)</label>
                <input type="number" class="form-control player-damage" 
                       min="0" max="5000" value="${Math.round(estimatedDamage)}">
            </div>
            <div class="input-group">
                <label>Survival Time (Estimated)</label>
                <div class="survival-time-wrapper">
                    <input type="number" class="form-control player-survival-min" 
                           min="0" max="30" placeholder="MM" value="${Math.floor(estimatedSurvival / 60)}" title="Minutes">
                    <span>:</span>
                    <input type="number" class="form-control player-survival-sec" 
                           min="0" max="59" placeholder="SS" value="${estimatedSurvival % 60}" title="Seconds">
                </div>
            </div>
            <div class="input-group">
                <label>Headshots (Estimated)</label>
                <input type="number" class="form-control player-headshots" 
                       min="0" max="50" value="${estimatedHeadshots}">
            </div>
            <div class="input-group">
                <label>Assists (Estimated)</label>
                <input type="number" class="form-control player-assists" 
                       min="0" max="20" value="${Math.round(estimatedAssists)}">
            </div>
        </div>
        <div class="career-note">
            <i class="fas fa-info-circle"></i> Stats estimated from career data: ${stats.matches_played || 0} matches, ${stats.win_rate || 0}% win rate
        </div>
    `;
    
    playerInputs.appendChild(playerDiv);
    
    // Add opponent players for comparison
    addOpponentPlayers(stats);
    
    showNotification('Virtual player created from career stats! Add opponents for prediction.', 'success');
}

// 18. NEW: Estimate survival time from career stats
function estimateSurvivalTime(stats) {
    // Base survival time (in seconds)
    let survival = 300; // 5 minutes base
    
    // Add based on win rate
    if (stats.win_rate) {
        survival += stats.win_rate * 3; // +3 seconds per % win rate
    }
    
    // Add based on KD ratio
    if (stats.kd_ratio) {
        survival += stats.kd_ratio * 20; // +20 seconds per KD point
    }
    
    // Add based on top 10 rate
    if (stats.top10_rate) {
        survival += stats.top10_rate * 2; // +2 seconds per % top 10 rate
    }
    
    return Math.min(1200, Math.max(300, Math.round(survival))); // Between 5-20 minutes
}

// 19. NEW: Add opponent players for comparison
function addOpponentPlayers(careerStats) {
    const playerInputs = document.getElementById('playerInputs');
    
    // Create 3 opponent players with stats based on career player
    const opponentCount = 3;
    
    for (let i = 1; i <= opponentCount; i++) {
        // Generate opponent stats (slightly worse than career player)
        const opponentKills = Math.max(1, Math.round((careerStats.avg_kills || 5) * (0.5 + Math.random() * 0.7)));
        const opponentDamage = Math.round((careerStats.avg_damage || 300) * (0.6 + Math.random() * 0.8));
        const opponentSurvival = Math.round(estimateSurvivalTime(careerStats) * (0.7 + Math.random() * 0.6));
        
        const opponentDiv = document.createElement('div');
        opponentDiv.className = 'player-input-section fade-in';
        opponentDiv.innerHTML = `
            <h4><i class="fas fa-user"></i> Opponent ${i}</h4>
            <div class="input-grid">
                <div class="input-group">
                    <label>Player Name</label>
                    <input type="text" class="form-control player-name" 
                           value="Opponent ${i}" placeholder="Opponent ${i}">
                </div>
                <div class="input-group">
                    <label>Kills</label>
                    <input type="number" class="form-control player-kills" 
                           min="0" max="50" value="${opponentKills}">
                </div>
                <div class="input-group">
                    <label>Damage</label>
                    <input type="number" class="form-control player-damage" 
                           min="0" max="5000" value="${opponentDamage}">
                </div>
                <div class="input-group">
                    <label>Survival Time</label>
                    <div class="survival-time-wrapper">
                        <input type="number" class="form-control player-survival-min" 
                               min="0" max="30" placeholder="MM" value="${Math.floor(opponentSurvival / 60)}" title="Minutes">
                        <span>:</span>
                        <input type="number" class="form-control player-survival-sec" 
                               min="0" max="59" placeholder="SS" value="${opponentSurvival % 60}" title="Seconds">
                    </div>
                </div>
                <div class="input-group">
                    <label>Headshots</label>
                    <input type="number" class="form-control player-headshots" 
                           min="0" max="50" value="${Math.round(opponentKills * 0.2)}">
                </div>
                <div class="input-group">
                    <label>Assists</label>
                    <input type="number" class="form-control player-assists" 
                           min="0" max="20" value="${Math.round(opponentKills * 0.3)}">
                </div>
            </div>
            <button class="btn btn-danger btn-sm remove-player" onclick="removePlayerInput(this)">
                <i class="fas fa-times"></i> Remove Player
            </button>
        `;
        
        playerInputs.appendChild(opponentDiv);
    }
    
    updateRemoveButtons();
}

// 20. NEW: Use career stats directly for prediction
function useCareerStatsForPrediction(stats) {
    // Create virtual match with career player vs average opponents
    const players = [];
    
    // Career player
    players.push({
        name: 'Career Pro',
        kills: Math.round(stats.avg_kills || (stats.kd_ratio * 2) || 5),
        damage: Math.round(stats.avg_damage || 300),
        survival: estimateSurvivalTime(stats),
        headshots: Math.round((stats.avg_kills || 5) * ((stats.headshot_rate || 15) / 100)),
        assists: Math.round(stats.avg_assists || 1.5),
        is_career_player: true,
        career_stats: stats
    });
    
    // Add 3 opponents
    for (let i = 1; i <= 3; i++) {
        const skillMultiplier = 0.5 + (Math.random() * 0.7); // 50-120% of career player
        
        players.push({
            name: `Opponent ${i}`,
            kills: Math.max(1, Math.round(players[0].kills * skillMultiplier)),
            damage: Math.round(players[0].damage * skillMultiplier),
            survival: Math.round(players[0].survival * (0.7 + Math.random() * 0.6)),
            headshots: Math.round(players[0].headshots * skillMultiplier),
            assists: Math.round(players[0].assists * skillMultiplier)
        });
    }
    
    // Process the prediction
    processVirtualMatch(players, stats);
}

// 21. NEW: Process virtual match
function processVirtualMatch(players, careerStats) {
    // Show processing section
    document.getElementById('processingSection').classList.remove('hidden');
    document.getElementById('ocrPreview').classList.add('hidden');
    
    // Update processing message for career stats
    document.querySelector('.processing-steps .step:nth-child(2) .step-content p').textContent = 
        'Analyzing career statistics...';
    document.querySelector('.processing-steps .step:nth-child(3) .step-content p').textContent = 
        'Generating career-based prediction...';
    
    // Start processing animation
    startProcessingAnimation();
    
    // Store players for results
    window.virtualMatchPlayers = players;
    window.careerStatsForPrediction = careerStats;
}

// 22. Switch to Manual Input
function switchToManualInput() {
    // Switch to manual input tab
    document.querySelector('.method-tab[data-method="manual"]').click();
    showNotification('Please enter your data manually for accurate predictions.', 'info');
}

// 23. Import from API
function importFromAPI() {
    const playerIds = document.getElementById('playerIds')?.value;
    if (!playerIds || playerIds.trim() === '') {
        showNotification('Please enter player IDs or match codes', 'error');
        return;
    }
    
    showNotification('API import would fetch real data here. Enter data manually for now.', 'info');
}

// 24. Process Data
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

// 25. Validate Inputs - IMPROVED
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
            const survivalMin = input.querySelector('.player-survival-min')?.value;
            const survivalSec = input.querySelector('.player-survival-sec')?.value;
            
            if (!name) {
                showNotification(`Please enter name for Player ${index + 1}`, 'error');
                hasErrors = true;
            }
            
            if (kills === '' || damage === '' || survivalMin === '' || survivalSec === '') {
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
            const teamNameInput = team.querySelector('.team-name-input');
            const teamName = teamNameInput?.value.trim();
            
            if (!teamName) {
                showNotification(`Please enter a name for Team ${teamIndex + 1}`, 'error');
                hasErrors = true;
            }
            
            const playerInputs = team.querySelectorAll('.team-player-input');
            
            playerInputs.forEach((input, playerIndex) => {
                const name = input.querySelector('.team-player-name')?.value.trim();
                const kills = input.querySelector('.team-player-kills')?.value;
                
                if (!name) {
                    showNotification(`Please enter name for Player ${playerIndex + 1} in ${teamName || 'Team ' + (teamIndex + 1)}`, 'error');
                    hasErrors = true;
                }
                
                if (kills === '') {
                    showNotification(`Please enter kills for Player ${playerIndex + 1} in ${teamName || 'Team ' + (teamIndex + 1)}`, 'error');
                    hasErrors = true;
                }
            });
        });
    }
    
    return !hasErrors;
}

// 26. Start Processing Animation
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

// 27. Show Results
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
        
        // Scroll to results smoothly
        setTimeout(() => {
            resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);
    }
}

// 28. Update Results - IMPROVED for all modes
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

// 29. Update Solo Results
function updateSoloResults() {
    const playerInputs = document.querySelectorAll('.player-input-section');
    const players = [];
    
    // Collect player data
    playerInputs.forEach((input, index) => {
        const name = input.querySelector('.player-name')?.value.trim() || `Player ${index + 1}`;
        const kills = parseInt(input.querySelector('.player-kills')?.value) || 0;
        const damage = parseInt(input.querySelector('.player-damage')?.value) || 0;
        const survivalMin = parseInt(input.querySelector('.player-survival-min')?.value) || 0;
        const survivalSec = parseInt(input.querySelector('.player-survival-sec')?.value) || 0;
        const survival = (survivalMin * 60) + survivalSec;
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

// 30. Update Team Results - NEW IMPLEMENTATION
function updateTeamResults() {
    const teamSections = document.querySelectorAll('.team-section');
    const matchType = document.querySelector('input[name="matchType"]:checked').value;
    const teams = [];
    
    // Collect team data
    teamSections.forEach((teamSection, teamIndex) => {
        // Get team name from input
        const teamNameInput = teamSection.querySelector('.team-name-input');
        const teamName = teamNameInput?.value.trim() || `Team ${teamIndex + 1}`;
        
        const playerInputs = teamSection.querySelectorAll('.team-player-input');
        const teamPlayers = [];
        let teamTotalKills = 0;
        let teamTotalDamage = 0;
        let teamTotalSurvival = 0;
        let teamTotalHeadshots = 0;
        let teamTotalAssists = 0;
        
        playerInputs.forEach((input, playerIndex) => {
            const name = input.querySelector('.team-player-name')?.value.trim() || `${teamName} Player ${playerIndex + 1}`;
            const kills = parseInt(input.querySelector('.team-player-kills')?.value) || 0;
            const damage = parseInt(input.querySelector('.team-player-damage')?.value) || 0;
            const survivalMin = parseInt(input.querySelector('.team-player-survival-min')?.value) || 0;
            const survivalSec = parseInt(input.querySelector('.team-player-survival-sec')?.value) || 0;
            const survival = (survivalMin * 60) + survivalSec;
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
            name: teamName,
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

// 31. Calculate Player Rating
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

// 32. Calculate Team Rating
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

// 33. Calculate Team Synergy
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

// 34. Get Synergy Description
function getSynergyDescription(synergy) {
    if (synergy >= 8) return 'Excellent team coordination';
    if (synergy >= 6) return 'Good team synergy';
    if (synergy >= 4) return 'Average coordination';
    return 'Needs better teamwork';
}

// 35. Calculate Death Estimate (for K/D calculation)
function deathEstimate(kills, damage) {
    // Simple estimation based on average damage per kill
    const avgDamagePerKill = 150;
    return Math.max(1, Math.floor(damage / avgDamagePerKill));
}

// 36. Calculate Confidence
function calculateConfidence(players) {
    if (players.length < 2) return 85;
    
    const winnerRating = players[0].rating;
    const runnerUpRating = players[1].rating;
    const ratingDiff = winnerRating - runnerUpRating;
    
    let confidence = 75 + (ratingDiff * 15);
    confidence = Math.min(98, Math.max(60, confidence));
    
    return Math.round(confidence);
}

// 37. Calculate Team Confidence
function calculateTeamConfidence(teams) {
    if (teams.length < 2) return 85;
    
    const winnerRating = teams[0].teamRating;
    const runnerUpRating = teams[1].teamRating;
    const ratingDiff = winnerRating - runnerUpRating;
    
    let confidence = 70 + (ratingDiff * 20);
    confidence = Math.min(97, Math.max(55, confidence));
    
    return Math.round(confidence);
}

// 38. Reset Form
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
    
    // Show upload zone again
    const uploadZone = document.getElementById('uploadZone');
    if (uploadZone) {
        uploadZone.style.display = 'block';
    }
    
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
                <h4><i class="fas fa-user-circle"></i> Player 1</h4>
                <div class="input-grid">
                    <div class="input-group">
                        <label>Player Name</label>
                        <input type="text" class="player-name" 
                               placeholder="Enter name" value="Player 1">
                    </div>
                    <div class="input-group">
                        <label>Kills</label>
                        <input type="number" class="player-kills" 
                               min="0" max="50" placeholder="0" value="5">
                    </div>
                    <div class="input-group">
                        <label>Damage</label>
                        <input type="number" class="player-damage" 
                               min="0" max="5000" placeholder="0" value="250">
                    </div>
                    <div class="input-group">
                        <label>Survival Time</label>
                        <div class="survival-time-wrapper">
                            <input type="number" class="player-survival-min" 
                                   min="0" max="30" placeholder="MM" value="7" title="Minutes">
                            <span>:</span>
                            <input type="number" class="player-survival-sec" 
                                   min="0" max="59" placeholder="SS" value="30" title="Seconds">
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Headshots</label>
                        <input type="number" class="player-headshots" 
                               min="0" max="50" placeholder="0" value="2">
                    </div>
                    <div class="input-group">
                        <label>Assists</label>
                        <input type="number" class="player-assists" 
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
    
    // Scroll to upload section
    setTimeout(() => {
        const uploadSection = document.querySelector('.sidebar-card');
        if (uploadSection) {
            uploadSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 300);
    
    showNotification('Form reset successfully!', 'info');
}

// 39. Retry Processing
function retryProcessing() {
    document.getElementById('errorSection')?.classList.add('hidden');
    processData();
}

// 40. Show Notification
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