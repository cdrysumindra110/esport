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
    <title>InfiKnight AI Prediction System v3.0 - Career Stats Algorithm</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./css/prediction.css?version=<?php echo time(); ?>">
    
    <!-- External JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        
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

        .main-layout {
            display: flex;
            flex-direction: column;
            gap: var(--space-xl);
            max-width: 1400px;
            margin: 0 auto;
        }

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

        .input-grid {
            display: grid;
            /* Use 1fr to ensure equal distribution of remaining space */
            grid-template-columns: repeat(2, 1fr); 
            /* Use 'minmax' if you want to prevent inputs from getting too small */
            /* grid-template-columns: repeat(2, minmax(0, 1fr)); */
            gap: var(--space-sm) var(--space-md);
            width: 100%;
            box-sizing: border-box;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            /* Ensure the group doesn't force a width larger than its grid cell */
            min-width: 0; 
        }

        /* Ensure the actual input fields don't overflow their containers */
        .input-group input {
            width: 100%;
            box-sizing: border-box;
        }

        .input-group label {s
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

        .input-hint {
            font-size: 0.7rem;
            color: var(--gray);
            margin-top: 4px;
            font-style: italic;
            opacity: 0.8;
        }

        .career-stats-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: var(--space-md);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.2);
        }

        .career-stats-info i {
            font-size: 1.2rem;
            opacity: 0.9;
        }

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

        .player-input-section {
            margin-bottom: 0;
            padding: var(--space-lg);
            background: white;
            border: 2px solid var(--border);
            border-radius: var(--radius-lg);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .player-input-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .player-input-section:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .player-input-section h4 {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--dark);
            text-transform: none;
            letter-spacing: 0px;
            margin-bottom: var(--space-md);
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: var(--space-md);
            border-bottom: 2px solid var(--gray-light);
            margin-top: var(--space-md);
        }

        .player-input-section h4 i {
            font-size: 1rem;
            color: var(--primary);
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

        .player-inputs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--space-lg);
            margin-bottom: var(--space-lg);
        }

        .process-actions {
            margin-top: var(--space-lg);
            display: flex;
            flex-direction: row;
            gap: var(--space-md);
            justify-content: center;
            flex-wrap: wrap;
        }

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

        .medal-gold {
            background: linear-gradient(135deg, #fff8db 0%, #ffe28a 100%);
            border-color: #f2c94c;
            box-shadow: 0 12px 24px rgba(242, 201, 76, 0.25);
        }

        .medal-silver {
            background: linear-gradient(135deg, #f6f7fb 0%, #dfe4ee 100%);
            border-color: #bfc7d5;
            box-shadow: 0 10px 20px rgba(191, 199, 213, 0.25);
        }

        .medal-bronze {
            background: linear-gradient(135deg, #ffe6d2 0%, #f6b38a 100%);
            border-color: #d08b5b;
            box-shadow: 0 10px 20px rgba(208, 139, 91, 0.25);
        }

        .winner-card.medal-gold .winner-icon {
            background: linear-gradient(135deg, #f2c94c, #f2994a);
        }

        .winner-card.medal-silver .winner-icon {
            background: linear-gradient(135deg, #cfd6e3, #aeb7c8);
        }

        .winner-card.medal-bronze .winner-icon {
            background: linear-gradient(135deg, #d08b5b, #b87333);
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

        .new-prediction-section {
            text-align: center;
            padding: var(--space-xl);
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }

        .new-prediction-section h3 {
            color: white;
        }

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

        .team-performance-card.medal-gold {
            background: linear-gradient(135deg, #fff8db 0%, #ffe28a 100%);
            border-color: #f2c94c;
            color: #5c3b00;
            box-shadow: 0 12px 24px rgba(242, 201, 76, 0.25);
        }

        .team-performance-card.medal-silver {
            background: linear-gradient(135deg, #f6f7fb 0%, #dfe4ee 100%);
            border-color: #bfc7d5;
            color: #2f3a45;
            box-shadow: 0 10px 20px rgba(191, 199, 213, 0.25);
        }

        .team-performance-card.medal-bronze {
            background: linear-gradient(135deg, #ffe6d2 0%, #f6b38a 100%);
            border-color: #d08b5b;
            color: #5a2d00;
            box-shadow: 0 10px 20px rgba(208, 139, 91, 0.25);
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

        .hidden {
            display: none !important;
        }

        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        .text-center {
            text-align: center;
        }

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
    
    <!-- MAIN -->
    <main role="main">    
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(./img/full_bg.jpg); background-size: cover; ">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              <center>Prediction</center>
            </h1>
          </div>
        </header>
      </article>  
    </main>
    <!-- Theme Toggle -->
    <!-- <div class="theme-toggle">
        <button class="toggle-btn" id="themeToggle">
            <i class="fas fa-moon"></i>
        </button>
    </div> -->

    <div class="infiknight-container">
        <!-- Header Section -->
        <header class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fas fa-robot"></i> InfiKnight AI v3.0
                </h1>
                <p class="hero-subtitle">Career Stats Prediction with PUBG Elite Benchmarks & Scientific Weights</p>
                <div class="version-badge">v3.0</div>
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
                        
                        <!-- Manual Input Section - FIXED: Now uses proper InfiKnight fields -->
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
                            
                            <!-- Player Inputs (for solo) - FIXED: Now uses InfiKnight algorithm fields -->
                            <div class="player-inputs" id="playerInputs">
                                <!-- <div class="career-stats-info">
                                    <i class="fas fa-info-circle"></i> 
                                    Enter career statistics - InfiKnight will calculate skill, damage & survival scores
                                </div> -->
                                <div class="player-input-section">
                                    <h4><i class="fas fa-user-circle"></i> Player 1</h4>
                                    <div class="input-grid">
                                        <div class="input-group">
                                            <label>Player Name</label>
                                            <input type="text" class="player-name" 
                                                   placeholder="Enter name" value="Player 1">
                                        </div>
                                        <div class="input-group">
                                            <label><i class="fas fa-crosshairs"></i> K/D Ratio</label>
                                            <input type="number" class="player-kd" 
                                                   min="0.1" max="15" step="0.1" placeholder="2.5" value="2.5"
                                                   title="Kill/Death ratio (0.5 - 10.0 typical)">
                                            <small class="input-hint">Range: 0.5 - 10.0</small>
                                        </div>
                                        <div class="input-group">
                                            <label><i class="fas fa-trophy"></i> Win Rate %</label>
                                            <input type="number" class="player-winrate" 
                                                   min="0" max="100" step="0.1" placeholder="15" value="15"
                                                   title="Percentage of matches won (1% - 50% typical)">
                                            <small class="input-hint">Range: 1% - 50%</small>
                                        </div>
                                        <div class="input-group">
                                            <label><i class="fas fa-medal"></i> Top 10 Rate %</label>
                                            <input type="number" class="player-top10" 
                                                   min="0" max="100" step="0.1" placeholder="40" value="40"
                                                   title="Percentage of Top 10 finishes (5% - 80% typical)">
                                            <small class="input-hint">Range: 5% - 80%</small>
                                        </div>
                                        <div class="input-group">
                                            <label><i class="fas fa-fire"></i> Avg Damage</label>
                                            <input type="number" class="player-avgdamage" 
                                                   min="0" max="2000" step="10" placeholder="350" value="350"
                                                   title="Average damage per match (100 - 1000 typical)">
                                            <small class="input-hint">Range: 100 - 1000</small>
                                        </div>
                                        <div class="input-group">
                                            <label><i class="fas fa-bullseye"></i> Headshot Rate %</label>
                                            <input type="number" class="player-headshot" 
                                                   min="0" max="100" step="0.1" placeholder="25" value="25"
                                                   title="Percentage of kills that are headshots (10% - 70% typical)">
                                            <small class="input-hint">Range: 10% - 70%</small>
                                        </div>
                                        <div class="input-group">
                                            <label><i class="fas fa-percent"></i> Accuracy %</label>
                                            <input type="number" class="player-accuracy" 
                                                   min="0" max="100" step="0.1" placeholder="20" value="20"
                                                   title="Overall shooting accuracy (10% - 50% typical)">
                                            <small class="input-hint">Range: 10% - 50%</small>
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
                                <p>Running InfiKnight algorithm...</p>
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
                                    <span id="algorithmVersion">InfiKnight v3.0</span>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-robot"></i>
                                    <span id="mlStatus">Career Stats AI</span>
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
                    
                    <!-- Winner Card - FIXED: Shows all three scores -->
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
                                        <span>Skill: <strong id="winnerSkill">0</strong></span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-bullseye"></i>
                                        <span>Damage: <strong id="winnerDamage">0</strong></span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-clock"></i>
                                        <span>Survival: <strong id="winnerSurvival">0</strong></span>
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
                    
                    <!-- Performance Grid - FIXED: Shows InfiKnight metrics -->
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
                                        <span class="stat">Skill: <strong id="topPerformerSkill">0.0</strong></span>
                                        <span class="stat">Damage: <strong id="topPerformerDamage">0</strong></span>
                                        <span class="stat">Survival: <strong id="topPerformerSurvival">0</strong></span>
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
                                        <span class="stat">Skill: <strong id="weakLinkSkill">0.0</strong></span>
                                        <span class="stat">Damage: <strong id="weakLinkDamage">0</strong></span>
                                        <span class="stat">Survival: <strong id="weakLinkSurvival">0</strong></span>
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
                                        Based on InfiKnight algorithm
                                    </div>
                                    <div class="algorithm-info">
                                        <span class="info-tag" id="algorithmTag">v3.0</span>
                                        <span class="info-tag" id="mlTag">Career AI</span>
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

    <!-- JavaScript Files - FIXED: Complete rewrite of critical functions -->
    <script>
// =============================================
// INFIKNIGHT AI PREDICTION SYSTEM v3.0 - FIXED
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('InfiKnight AI v3.0 - Initializing...');
    initializeApp();
});

// ============= INITIALIZATION =============
function initializeApp() {
    initializeTheme();
    initializeTabs();
    addDragAndDrop();
    initializeEventListeners();
    updateInputMode();
    console.log('InfiKnight AI initialized successfully');
}

// ============= THEME =============
function initializeTheme() {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;
    
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    const icon = themeToggle.querySelector('i');
    if (icon) icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        if (icon) icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    });
}

// ============= TABS =============
function initializeTabs() {
    const methodTabs = document.querySelectorAll('.method-tab');
    const methodContents = document.querySelectorAll('.method-content');
    
    methodTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const method = this.getAttribute('data-method');
            
            methodTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            methodContents.forEach(content => content.classList.remove('active'));
            
            const targetContent = document.getElementById(method + 'Method');
            if (targetContent) targetContent.classList.add('active');
            
            if (method === 'manual') updateInputMode();
        });
    });
}

// ============= DRAG & DROP =============
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
        uploadZone.addEventListener(eventName, function() {
            uploadZone.classList.add('highlight');
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, function() {
            uploadZone.classList.remove('highlight');
        }, false);
    });
    
    uploadZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            const fileInput = document.getElementById('statsFile');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            fileInput.files = dataTransfer.files;
            
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
            
            showNotification('File dropped successfully!', 'success');
        }
    }, false);
}

// ============= EVENT LISTENERS =============
function initializeEventListeners() {
    // Browse button
    const browseBtn = document.getElementById('browseBtn');
    const statsFile = document.getElementById('statsFile');
    if (browseBtn && statsFile) {
        browseBtn.addEventListener('click', () => statsFile.click());
    }
    
    // File input change
    if (statsFile) {
        statsFile.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                showOCRProcessing();
                processOCRFile(e.target.files[0]);
            }
        });
    }
    
    // Match type change
    document.querySelectorAll('input[name="matchType"]').forEach(radio => {
        radio.addEventListener('change', updateInputMode);
    });
    
    // Add player button
    const addPlayerBtn = document.getElementById('addPlayerBtn');
    if (addPlayerBtn) {
        addPlayerBtn.addEventListener('click', addPlayerInput);
    }
    
    // Generate teams button
    const generateTeamsBtn = document.getElementById('generateTeams');
    if (generateTeamsBtn) {
        generateTeamsBtn.addEventListener('click', generateTeams);
    }
    
    // Team count change
    const teamCountEl = document.getElementById('teamCount');
    if (teamCountEl) {
        teamCountEl.addEventListener('change', generateTeams);
    }
    
    // Process button
    const processBtn = document.getElementById('processBtn');
    if (processBtn) {
        processBtn.addEventListener('click', processData);
    }
    
    // New prediction button
    const newPredictionBtn = document.getElementById('newPrediction');
    if (newPredictionBtn) {
        newPredictionBtn.addEventListener('click', resetForm);
    }
    
    // Retry button
    const retryBtn = document.getElementById('retryButton');
    if (retryBtn) {
        retryBtn.addEventListener('click', retryProcessing);
    }
    
    // Import API button
    const importApiBtn = document.getElementById('importApi');
    if (importApiBtn) {
        importApiBtn.addEventListener('click', importFromAPI);
    }
}

// ============= UPDATE INPUT MODE =============
function updateInputMode() {
    const matchType = document.querySelector('input[name="matchType"]:checked').value;
    const teamConfig = document.getElementById('teamConfig');
    const playerInputs = document.getElementById('playerInputs');
    const addPlayerBtn = document.getElementById('addPlayerBtn');
    
    if (matchType === 'solo') {
        if (teamConfig) teamConfig.classList.add('hidden');
        if (playerInputs) playerInputs.classList.remove('hidden');
        if (addPlayerBtn) addPlayerBtn.style.display = 'block';
        document.getElementById('winnerTag').textContent = 'Solo Winner';
        document.getElementById('teamMembers')?.classList.add('hidden');
    } else {
        if (teamConfig) teamConfig.classList.remove('hidden');
        if (playerInputs) playerInputs.classList.add('hidden');
        if (addPlayerBtn) addPlayerBtn.style.display = 'none';
        document.getElementById('winnerTag').textContent = matchType === 'duo' ? 'Duo Winner' : 'Squad Winner';
        document.getElementById('teamMembers')?.classList.remove('hidden');
        generateTeams();
    }
}

// ============= GENERATE TEAMS (FIXED) =============
function generateTeams() {
    const teamInputs = document.getElementById('teamInputs');
    if (!teamInputs) return;
    
    const teamCount = parseInt(document.getElementById('teamCount').value) || 2;
    const matchType = document.querySelector('input[name="matchType"]:checked').value;
    
    let playersPerTeam;
    if (matchType === 'solo') playersPerTeam = 1;
    else if (matchType === 'duo') playersPerTeam = 2;
    else playersPerTeam = 4;
    
    teamInputs.innerHTML = '';
    
    for (let i = 0; i < teamCount; i++) {
        const teamDiv = document.createElement('div');
        teamDiv.className = 'team-section';
        teamDiv.innerHTML = `
            <div class="team-header">
                <div class="team-header-row">
                    <h4><i class="fas fa-users"></i> Team ${i + 1}</h4>
                    <button class="remove-team-btn" onclick="removeTeam(this)" title="Remove this team">
                        <i class="fas fa-trash-alt"></i> Remove Team
                    </button>
                </div>
                <div class="input-group team-name-group">
                    <label>Team Name</label>
                    <input type="text" class="form-control team-name-input" 
                        placeholder="Enter team name" value="Team ${i + 1}">
                </div>
            </div>
            <div class="team-players-grid"></div>
        `;
        
        const playersGrid = teamDiv.querySelector('.team-players-grid');
        for (let j = 0; j < playersPerTeam; j++) {
            playersGrid.appendChild(createTeamPlayerInput(i, j));
        }
        
        teamInputs.appendChild(teamDiv);
    }
}

// ============= CREATE TEAM PLAYER INPUT (FIXED) =============
function createTeamPlayerInput(teamIndex, playerIndex) {
    const div = document.createElement('div');
    div.className = 'team-player-input';
    
    const kd = (Math.random() * 4 + 1).toFixed(1);
    const winrate = (Math.random() * 25 + 5).toFixed(1);
    const top10 = (Math.random() * 40 + 20).toFixed(1);
    const avgdmg = Math.floor(Math.random() * 400 + 200);
    const headshot = (Math.random() * 30 + 15).toFixed(1);
    const accuracy = (Math.random() * 20 + 15).toFixed(1);
    
    div.innerHTML = `
        <h5><i class="fas fa-user-circle"></i> Player ${playerIndex + 1}</h5>
        <div class="input-grid">
            <div class="input-group">
                <label>Name</label>
                <input type="text" class="team-player-name" 
                    placeholder="Player name" value="Player ${playerIndex + 1}">
            </div>
            <div class="input-group">
                <label><i class="fas fa-crosshairs"></i> K/D</label>
                <input type="number" class="team-player-kd" 
                    min="0.1" max="15" step="0.1" placeholder="2.5" value="${kd}">
                <small class="input-hint">0.5-10</small>
            </div>
            <div class="input-group">
                <label><i class="fas fa-trophy"></i> Win %</label>
                <input type="number" class="team-player-winrate" 
                    min="0" max="100" step="0.1" placeholder="15" value="${winrate}">
                <small class="input-hint">1-50%</small>
            </div>
            <div class="input-group">
                <label><i class="fas fa-medal"></i> Top10 %</label>
                <input type="number" class="team-player-top10" 
                    min="0" max="100" step="0.1" placeholder="40" value="${top10}">
                <small class="input-hint">5-80%</small>
            </div>
            <div class="input-group">
                <label><i class="fas fa-fire"></i> Avg Dmg</label>
                <input type="number" class="team-player-avgdamage" 
                    min="0" max="2000" step="10" placeholder="350" value="${avgdmg}">
                <small class="input-hint">100-1000</small>
            </div>
            <div class="input-group">
                <label><i class="fas fa-bullseye"></i> HS %</label>
                <input type="number" class="team-player-headshot" 
                    min="0" max="100" step="0.1" placeholder="25" value="${headshot}">
                <small class="input-hint">10-70%</small>
            </div>
            <div class="input-group">
                <label><i class="fas fa-percent"></i> Acc %</label>
                <input type="number" class="team-player-accuracy" 
                    min="0" max="100" step="0.1" placeholder="20" value="${accuracy}">
                <small class="input-hint">10-50%</small>
            </div>
        </div>
    `;
    return div;
}

// ============= ADD PLAYER INPUT =============
function addPlayerInput() {
    const playerInputs = document.getElementById('playerInputs');
    if (!playerInputs) return;
    
    const currentPlayers = playerInputs.querySelectorAll('.player-input-section').length;
    const newPlayer = createPlayerInput(currentPlayers);
    playerInputs.appendChild(newPlayer);
    
    updateRemoveButtons();
    showNotification(`Player ${currentPlayers + 1} added successfully!`, 'success');
}

// ============= CREATE PLAYER INPUT =============
function createPlayerInput(index) {
    const div = document.createElement('div');
    div.className = 'player-input-section fade-in';
    
    const kd = (Math.random() * 4 + 1).toFixed(1);
    const winrate = (Math.random() * 25 + 5).toFixed(1);
    const top10 = (Math.random() * 40 + 20).toFixed(1);
    const avgdmg = Math.floor(Math.random() * 400 + 200);
    const headshot = (Math.random() * 30 + 15).toFixed(1);
    const accuracy = (Math.random() * 20 + 15).toFixed(1);
    
    div.innerHTML = `
        <h4><i class="fas fa-user-circle"></i> Player ${index + 1}</h4>
        <div class="input-grid">
            <div class="input-group">
                <label>Player Name</label>
                <input type="text" class="player-name" 
                       placeholder="Enter name" value="Player ${index + 1}">
            </div>
            <div class="input-group">
                <label><i class="fas fa-crosshairs"></i> K/D Ratio</label>
                <input type="number" class="player-kd" 
                       min="0.1" max="15" step="0.1" placeholder="2.5" value="${kd}">
                <small class="input-hint">Range: 0.5 - 10.0</small>
            </div>
            <div class="input-group">
                <label><i class="fas fa-trophy"></i> Win Rate %</label>
                <input type="number" class="player-winrate" 
                       min="0" max="100" step="0.1" placeholder="15" value="${winrate}">
                <small class="input-hint">Range: 1% - 50%</small>
            </div>
            <div class="input-group">
                <label><i class="fas fa-medal"></i> Top 10 Rate %</label>
                <input type="number" class="player-top10" 
                       min="0" max="100" step="0.1" placeholder="40" value="${top10}">
                <small class="input-hint">Range: 5% - 80%</small>
            </div>
            <div class="input-group">
                <label><i class="fas fa-fire"></i> Avg Damage</label>
                <input type="number" class="player-avgdamage" 
                       min="0" max="2000" step="10" placeholder="350" value="${avgdmg}">
                <small class="input-hint">Range: 100 - 1000</small>
            </div>
            <div class="input-group">
                <label><i class="fas fa-bullseye"></i> Headshot Rate %</label>
                <input type="number" class="player-headshot" 
                       min="0" max="100" step="0.1" placeholder="25" value="${headshot}">
                <small class="input-hint">Range: 10% - 70%</small>
            </div>
            <div class="input-group">
                <label><i class="fas fa-percent"></i> Accuracy %</label>
                <input type="number" class="player-accuracy" 
                       min="0" max="100" step="0.1" placeholder="20" value="${accuracy}">
                <small class="input-hint">Range: 10% - 50%</small>
            </div>
        </div>
        <button class="btn btn-danger btn-sm remove-player" onclick="removePlayerInput(this)">
            <i class="fas fa-times"></i> Remove Player
        </button>
    `;
    return div;
}

// ============= REMOVE PLAYER INPUT =============
function removePlayerInput(button) {
    const playerSection = button.closest('.player-input-section');
    if (!playerSection) return;
    
    const playerInputs = document.getElementById('playerInputs');
    const sections = playerInputs.querySelectorAll('.player-input-section');
    
    if (sections.length <= 1) {
        showNotification('Cannot remove the only player!', 'error');
        return;
    }
    
    playerSection.remove();
    renumberPlayers();
    updateRemoveButtons();
    showNotification('Player removed successfully!', 'info');
}

// ============= RENUMBER PLAYERS =============
function renumberPlayers() {
    const playerInputs = document.getElementById('playerInputs');
    const sections = playerInputs.querySelectorAll('.player-input-section');
    
    sections.forEach((section, index) => {
        const h4 = section.querySelector('h4');
        if (h4) h4.innerHTML = `<i class="fas fa-user-circle"></i> Player ${index + 1}`;
    });
}

// ============= UPDATE REMOVE BUTTONS =============
function updateRemoveButtons() {
    const playerInputs = document.getElementById('playerInputs');
    if (!playerInputs) return;
    
    const sections = playerInputs.querySelectorAll('.player-input-section');
    const removeButtons = playerInputs.querySelectorAll('.remove-player');
    
    removeButtons.forEach(btn => {
        btn.style.display = sections.length > 1 ? 'block' : 'none';
    });
}

// ============= REMOVE TEAM =============
function removeTeam(button) {
    const teamSection = button.closest('.team-section');
    if (!teamSection) return;
    
    const teamInputs = document.getElementById('teamInputs');
    const sections = teamInputs.querySelectorAll('.team-section');
    
    if (sections.length <= 2) {
        showNotification('Cannot have less than 2 teams!', 'error');
        return;
    }
    
    teamSection.remove();
    renumberTeams();
    showNotification('Team removed successfully!', 'info');
}

// ============= RENUMBER TEAMS =============
function renumberTeams() {
    const teamInputs = document.getElementById('teamInputs');
    const sections = teamInputs.querySelectorAll('.team-section');
    
    sections.forEach((section, index) => {
        const h4 = section.querySelector('.team-header h4');
        if (h4) h4.innerHTML = `<i class="fas fa-users"></i> Team ${index + 1}`;
        
        const teamNameInput = section.querySelector('.team-name-input');
        if (teamNameInput && teamNameInput.value.startsWith('Team ')) {
            teamNameInput.value = `Team ${index + 1}`;
        }
    });
}

// ============= SHOW OCR PROCESSING =============
function showOCRProcessing() {
    const uploadZone = document.getElementById('uploadZone');
    const ocrPreview = document.getElementById('ocrPreview');
    
    if (ocrPreview) {
        ocrPreview.classList.remove('hidden');
        ocrPreview.innerHTML = `
            <div class="ocr-processing">
                <div class="spinner" style="margin: 0 auto; margin-bottom: 20px;"></div>
                <p>Uploading and analyzing image with InfiKnight OCR...</p>
                <div class="progress-container" style="margin-top: 20px;">
                    <div class="progress-bar" style="width: 0%; transition: width 0.3s;"></div>
                </div>
            </div>
        `;
    }
    
    if (uploadZone) uploadZone.style.display = 'none';
}

// ============= PROCESS OCR FILE =============
async function processOCRFile(file) {
    try {
        const formData = new FormData();
        formData.append('statsFile', file);
        formData.append('csrf_token', document.getElementById('csrfToken').value);
        formData.append('match_type', document.querySelector('input[name="matchType"]:checked').value);
        
        const progressBar = document.querySelector('.progress-bar');
        let progress = 0;
        const progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += 10;
                if (progressBar) progressBar.style.width = progress + '%';
            }
        }, 300);
        
        const response = await fetch('ocr_backend.php', {
            method: 'POST',
            body: formData
        });
        
        clearInterval(progressInterval);
        if (progressBar) progressBar.style.width = '100%';
        
        const responseText = await response.text();
        let result;
        
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON parse error:', e);
            showOCRError('Server returned invalid JSON. Please try again.');
            return;
        }
        
        if (result.success) {
            displayCareerStats(result.data);
        } else {
            showOCRError(result.error || 'OCR processing failed');
        }
        
    } catch (error) {
        console.error('OCR Network Error:', error);
        showOCRError('Network error: ' + error.message);
    }
}

// ============= SHOW OCR ERROR =============
function showOCRError(message) {
    const ocrPreview = document.getElementById('ocrPreview');
    if (!ocrPreview) return;
    
    ocrPreview.classList.remove('hidden');
    ocrPreview.innerHTML = `
        <div class="ocr-error">
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
}

// ============= DISPLAY CAREER STATS =============
function displayCareerStats(data) {
    const ocrPreview = document.getElementById('ocrPreview');
    if (!ocrPreview) return;
    
    const stats = data.extracted_data?.career_stats || {};
    const confidence = data.confidence || 0;
    const ocrText = data.ocr_text || '';
    
    let html = `
        <div class="ocr-results">
            <div class="ocr-header">
                <h4><i class="fas fa-user-circle"></i> Career Stats Analyzed</h4>
                <div class="ocr-meta">
                    <span class="meta-item">
                        <i class="fas fa-database"></i> Source: Career Screenshot
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-robot"></i> InfiKnight OCR
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
    
    const keyStats = [
        { key: 'matches_played', label: 'Matches', icon: 'fa-gamepad', format: v => v.toLocaleString() },
        { key: 'wins', label: 'Wins', icon: 'fa-trophy', format: v => v.toLocaleString() },
        { key: 'win_rate', label: 'Win Rate', icon: 'fa-chart-line', format: v => v + '%' },
        { key: 'eliminations', label: 'Total Kills', icon: 'fa-skull', format: v => v.toLocaleString() },
        { key: 'kd_ratio', label: 'K/D Ratio', icon: 'fa-balance-scale', format: v => v },
        { key: 'avg_kills', label: 'Avg Kills', icon: 'fa-crosshairs', format: v => v },
        { key: 'total_damage', label: 'Total Damage', icon: 'fa-fire', format: v => formatNumber(v) },
        { key: 'avg_damage', label: 'Avg Damage', icon: 'fa-bullseye', format: v => v },
        { key: 'headshot_rate', label: 'HS Rate', icon: 'fa-bullseye', format: v => v + '%' },
        { key: 'accuracy', label: 'Accuracy', icon: 'fa-bullseye', format: v => v + '%' }
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
            <button class="btn btn-success" onclick="useCareerStatsForPrediction()">
                <i class="fas fa-robot"></i> Generate Career-Based Prediction
            </button>
            <button class="btn btn-primary" onclick="createVirtualPlayerFromCareerStats()">
                <i class="fas fa-user-plus"></i> Create Virtual Player
            </button>
            <button class="btn btn-outline-secondary" onclick="switchToManualInput()">
                <i class="fas fa-keyboard"></i> Enter Data Manually
            </button>
        </div>
    </div>`;
    
    ocrPreview.innerHTML = html;
    
    window.careerStats = stats;
    window.ocrData = data;
    
    showNotification('Career stats extracted successfully!', 'success');
}

// ============= FORMAT NUMBER =============
function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toLocaleString();
}

// ============= INFIKNIGHT ALGORITHM: CALCULATE SKILL SCORE =============
function calculateSkillScore(careerStats) {
    // Skill Score = (K/D * 0.4) + (Win Rate * 0.3) + (Top 10 Rate * 0.2) + (Headshot Rate * 0.1)
    const kd = parseFloat(careerStats.kd_ratio) || 1.0;
    const winRate = (parseFloat(careerStats.win_rate) || 15) / 100;
    const top10 = (parseFloat(careerStats.top10_rate) || 40) / 100;
    const headshot = (parseFloat(careerStats.headshot_rate) || 25) / 100;
    
    // Normalize to 0-100 scale
    const skill = (kd * 10 * 0.4) + (winRate * 100 * 0.3) + (top10 * 100 * 0.2) + (headshot * 100 * 0.1);
    return Math.min(100, Math.round(skill));
}

// ============= INFIKNIGHT ALGORITHM: CALCULATE DAMAGE SCORE =============
function calculateDamageScore(careerStats) {
    // Damage Score = Avg Damage normalized (0-100)
    const avgDamage = parseFloat(careerStats.avg_damage) || 300;
    return Math.min(100, Math.round(avgDamage / 6)); // 600 damage = 100 points
}

// ============= INFIKNIGHT ALGORITHM: CALCULATE SURVIVAL SCORE =============
function calculateSurvivalScore(careerStats) {
    // Survival Score = Win Rate * 0.7 + Top 10 Rate * 0.3
    const winRate = (parseFloat(careerStats.win_rate) || 15) / 100;
    const top10 = (parseFloat(careerStats.top10_rate) || 40) / 100;
    
    const survival = (winRate * 100 * 0.7) + (top10 * 100 * 0.3);
    return Math.min(100, Math.round(survival));
}

// ============= INFIKNIGHT ALGORITHM: CALCULATE OVERALL RATING =============
function calculateOverallRating(skill, damage, survival) {
    // Weighted average: Skill 40%, Damage 35%, Survival 25%
    return ((skill * 0.4) + (damage * 0.35) + (survival * 0.25)).toFixed(1);
}

// ============= CREATE VIRTUAL PLAYER FROM CAREER STATS (FIXED) =============
function createVirtualPlayerFromCareerStats() {
    const stats = window.careerStats;
    if (!stats) {
        showNotification('No career stats available', 'error');
        return;
    }
    
    // Switch to manual input tab
    document.querySelector('.method-tab[data-method="manual"]').click();
    
    // Get current number of players
    const playerInputs = document.getElementById('playerInputs');
    const currentPlayers = playerInputs.querySelectorAll('.player-input-section').length;
    
    // Calculate InfiKnight scores
    const skillScore = calculateSkillScore(stats);
    const damageScore = calculateDamageScore(stats);
    const survivalScore = calculateSurvivalScore(stats);
    const overallRating = calculateOverallRating(skillScore, damageScore, survivalScore);
    
    // Create virtual player
    const virtualPlayer = document.createElement('div');
    virtualPlayer.className = 'player-input-section fade-in';
    virtualPlayer.innerHTML = `
        <h4><i class="fas fa-user-crown"></i> Virtual Player (Career)</h4>
        <div class="input-grid">
            <div class="input-group">
                <label>Player Name</label>
                <input type="text" class="player-name" value="Career Pro" placeholder="Career Pro">
            </div>
            <div class="input-group">
                <label>K/D Ratio</label>
                <input type="number" class="player-kd" step="0.1" min="0.1" max="15" 
                       value="${stats.kd_ratio || 2.5}">
            </div>
            <div class="input-group">
                <label>Win Rate %</label>
                <input type="number" class="player-winrate" step="0.1" min="0" max="100" 
                       value="${stats.win_rate || 15}">
            </div>
            <div class="input-group">
                <label>Top 10 Rate %</label>
                <input type="number" class="player-top10" step="0.1" min="0" max="100" 
                       value="${stats.top10_rate || 40}">
            </div>
            <div class="input-group">
                <label>Avg Damage</label>
                <input type="number" class="player-avgdamage" step="10" min="0" max="2000" 
                       value="${stats.avg_damage || 350}">
            </div>
            <div class="input-group">
                <label>Headshot Rate %</label>
                <input type="number" class="player-headshot" step="0.1" min="0" max="100" 
                       value="${stats.headshot_rate || 25}">
            </div>
            <div class="input-group">
                <label>Accuracy %</label>
                <input type="number" class="player-accuracy" step="0.1" min="0" max="100" 
                       value="${stats.accuracy || 20}">
            </div>
        </div>
        <div class="career-note">
            <i class="fas fa-info-circle"></i> 
            InfiKnight Scores: Skill ${skillScore} | Damage ${damageScore} | Survival ${survivalScore} | Rating ${overallRating}/10
        </div>
        <button class="btn btn-danger btn-sm remove-player" onclick="removePlayerInput(this)">
            <i class="fas fa-times"></i> Remove Player
        </button>
    `;
    
    playerInputs.appendChild(virtualPlayer);
    
    // Add opponent players
    addOpponentPlayers(stats);
    
    showNotification('Virtual player created with InfiKnight scores!', 'success');
}

// ============= ADD OPPONENT PLAYERS (FIXED) =============
function addOpponentPlayers(careerStats) {
    const playerInputs = document.getElementById('playerInputs');
    const opponentCount = 3;
    
    for (let i = 1; i <= opponentCount; i++) {
        const opponentKd = (parseFloat(careerStats.kd_ratio) * (0.6 + Math.random() * 0.3)).toFixed(1);
        const opponentWinrate = (parseFloat(careerStats.win_rate) * (0.6 + Math.random() * 0.3)).toFixed(1);
        const opponentTop10 = (parseFloat(careerStats.top10_rate) * (0.7 + Math.random() * 0.4)).toFixed(1);
        const opponentDmg = Math.round(parseFloat(careerStats.avg_damage) * (0.6 + Math.random() * 0.3));
        const opponentHs = (parseFloat(careerStats.headshot_rate) * (0.5 + Math.random() * 0.5)).toFixed(1);
        const opponentAcc = (parseFloat(careerStats.accuracy) * (0.6 + Math.random() * 0.4)).toFixed(1);
        
        const opponentDiv = document.createElement('div');
        opponentDiv.className = 'player-input-section fade-in';
        opponentDiv.innerHTML = `
            <h4><i class="fas fa-user"></i> Opponent ${i}</h4>
            <div class="input-grid">
                <div class="input-group">
                    <label>Player Name</label>
                    <input type="text" class="player-name" value="Opponent ${i}">
                </div>
                <div class="input-group">
                    <label>K/D Ratio</label>
                    <input type="number" class="player-kd" step="0.1" value="${opponentKd}">
                </div>
                <div class="input-group">
                    <label>Win Rate %</label>
                    <input type="number" class="player-winrate" step="0.1" value="${opponentWinrate}">
                </div>
                <div class="input-group">
                    <label>Top 10 Rate %</label>
                    <input type="number" class="player-top10" step="0.1" value="${opponentTop10}">
                </div>
                <div class="input-group">
                    <label>Avg Damage</label>
                    <input type="number" class="player-avgdamage" step="10" value="${opponentDmg}">
                </div>
                <div class="input-group">
                    <label>Headshot Rate %</label>
                    <input type="number" class="player-headshot" step="0.1" value="${opponentHs}">
                </div>
                <div class="input-group">
                    <label>Accuracy %</label>
                    <input type="number" class="player-accuracy" step="0.1" value="${opponentAcc}">
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

// ============= USE CAREER STATS FOR PREDICTION (FIXED) =============
function useCareerStatsForPrediction() {
    const stats = window.careerStats;
    if (!stats) {
        showNotification('No career stats available', 'error');
        return;
    }
    
    // Switch to manual input
    document.querySelector('.method-tab[data-method="manual"]').click();
    
    // Clear existing players
    const playerInputs = document.getElementById('playerInputs');
    playerInputs.innerHTML = '';
    
    // Create virtual player
    const virtualPlayer = document.createElement('div');
    virtualPlayer.className = 'player-input-section fade-in';
    virtualPlayer.innerHTML = `
        <h4><i class="fas fa-user-crown"></i> Career Pro</h4>
        <div class="input-grid">
            <div class="input-group">
                <label>Player Name</label>
                <input type="text" class="player-name" value="Career Pro">
            </div>
            <div class="input-group">
                <label>K/D Ratio</label>
                <input type="number" class="player-kd" step="0.1" value="${stats.kd_ratio || 2.5}">
            </div>
            <div class="input-group">
                <label>Win Rate %</label>
                <input type="number" class="player-winrate" step="0.1" value="${stats.win_rate || 15}">
            </div>
            <div class="input-group">
                <label>Top 10 Rate %</label>
                <input type="number" class="player-top10" step="0.1" value="${stats.top10_rate || 40}">
            </div>
            <div class="input-group">
                <label>Avg Damage</label>
                <input type="number" class="player-avgdamage" step="10" value="${stats.avg_damage || 350}">
            </div>
            <div class="input-group">
                <label>Headshot Rate %</label>
                <input type="number" class="player-headshot" step="0.1" value="${stats.headshot_rate || 25}">
            </div>
            <div class="input-group">
                <label>Accuracy %</label>
                <input type="number" class="player-accuracy" step="0.1" value="${stats.accuracy || 20}">
            </div>
        </div>
    `;
    
    playerInputs.appendChild(virtualPlayer);
    
    // Add opponents
    addOpponentPlayers(stats);
    
    // Show processing section
    document.getElementById('processingSection').classList.remove('hidden');
    document.getElementById('ocrPreview').classList.add('hidden');
    
    // Start processing animation and run prediction
    startProcessingAnimation();
    
    // Process the data
    setTimeout(() => {
        processData();
    }, 500);
}

// ============= VALIDATE INPUTS (FIXED) =============
function validateInputs() {
    const matchType = document.querySelector('input[name="matchType"]:checked').value;
    let hasErrors = false;
    
    if (matchType === 'solo') {
        const playerInputs = document.querySelectorAll('.player-input-section');
        
        if (playerInputs.length < 2) {
            showNotification('Please add at least 2 players for solo prediction.', 'error');
            return false;
        }
        
        playerInputs.forEach((input, index) => {
            const name = input.querySelector('.player-name')?.value.trim();
            const kd = input.querySelector('.player-kd')?.value;
            const winrate = input.querySelector('.player-winrate')?.value;
            const top10 = input.querySelector('.player-top10')?.value;
            const avgdamage = input.querySelector('.player-avgdamage')?.value;
            
            if (!name) {
                showNotification(`Please enter name for Player ${index + 1}`, 'error');
                hasErrors = true;
            }
            
            if (kd === '' || winrate === '' || top10 === '' || avgdamage === '') {
                showNotification(`Please fill all required career stats for ${name || 'Player ' + (index + 1)}`, 'error');
                hasErrors = true;
            }
        });
    } else {
        const teamSections = document.querySelectorAll('.team-section');
        
        if (teamSections.length < 2) {
            showNotification('Please generate at least 2 teams.', 'error');
            return false;
        }
        
        teamSections.forEach((team, teamIndex) => {
            const teamName = team.querySelector('.team-name-input')?.value.trim();
            if (!teamName) {
                showNotification(`Please enter a name for Team ${teamIndex + 1}`, 'error');
                hasErrors = true;
            }
        });
    }
    
    return !hasErrors;
}

// ============= COLLECT PLAYER DATA (FIXED) =============
function collectPlayerData(matchType) {
    if (matchType === 'solo') {
        const playerInputs = document.querySelectorAll('.player-input-section');
        const players = [];
        
        playerInputs.forEach((input, index) => {
            const name = input.querySelector('.player-name')?.value.trim() || `Player ${index + 1}`;
            const kd = parseFloat(input.querySelector('.player-kd')?.value) || 2.0;
            const winrate = parseFloat(input.querySelector('.player-winrate')?.value) || 15;
            const top10 = parseFloat(input.querySelector('.player-top10')?.value) || 40;
            const avgdamage = parseFloat(input.querySelector('.player-avgdamage')?.value) || 350;
            const headshot = parseFloat(input.querySelector('.player-headshot')?.value) || 25;
            const accuracy = parseFloat(input.querySelector('.player-accuracy')?.value) || 20;
            
            // Calculate InfiKnight scores
            const skillScore = calculateSkillScore({
                kd_ratio: kd,
                win_rate: winrate,
                top10_rate: top10,
                headshot_rate: headshot
            });
            
            const damageScore = calculateDamageScore({ avg_damage: avgdamage });
            const survivalScore = calculateSurvivalScore({
                win_rate: winrate,
                top10_rate: top10
            });
            
            const overallRating = calculateOverallRating(skillScore, damageScore, survivalScore);
            
            players.push({
                name,
                kd,
                winrate,
                top10,
                avgdamage,
                headshot,
                accuracy,
                skill: skillScore,
                damage: damageScore,
                survival: survivalScore,
                rating: parseFloat(overallRating)
            });
        });
        
        return { matchType, players };
    } else {
        const teamSections = document.querySelectorAll('.team-section');
        const teams = [];
        
        teamSections.forEach((team, teamIndex) => {
            const teamName = team.querySelector('.team-name-input')?.value.trim() || `Team ${teamIndex + 1}`;
            const playerInputs = team.querySelectorAll('.team-player-input');
            const players = [];
            
            playerInputs.forEach((input, playerIndex) => {
                const name = input.querySelector('.team-player-name')?.value.trim() || `Player ${playerIndex + 1}`;
                const kd = parseFloat(input.querySelector('.team-player-kd')?.value) || 2.0;
                const winrate = parseFloat(input.querySelector('.team-player-winrate')?.value) || 15;
                const top10 = parseFloat(input.querySelector('.team-player-top10')?.value) || 40;
                const avgdamage = parseFloat(input.querySelector('.team-player-avgdamage')?.value) || 350;
                const headshot = parseFloat(input.querySelector('.team-player-headshot')?.value) || 25;
                const accuracy = parseFloat(input.querySelector('.team-player-accuracy')?.value) || 20;
                
                const skillScore = calculateSkillScore({ kd_ratio: kd, win_rate: winrate, top10_rate: top10, headshot_rate: headshot });
                const damageScore = calculateDamageScore({ avg_damage: avgdamage });
                const survivalScore = calculateSurvivalScore({ win_rate: winrate, top10_rate: top10 });
                const overallRating = calculateOverallRating(skillScore, damageScore, survivalScore);
                
                players.push({
                    name,
                    kd,
                    winrate,
                    top10,
                    avgdamage,
                    headshot,
                    accuracy,
                    skill: skillScore,
                    damage: damageScore,
                    survival: survivalScore,
                    rating: parseFloat(overallRating)
                });
            });
            
            // Calculate team averages
            const teamSkill = Math.round(players.reduce((sum, p) => sum + p.skill, 0) / players.length);
            const teamDamage = Math.round(players.reduce((sum, p) => sum + p.damage, 0) / players.length);
            const teamSurvival = Math.round(players.reduce((sum, p) => sum + p.survival, 0) / players.length);
            const teamRating = calculateOverallRating(teamSkill, teamDamage, teamSurvival);
            
            teams.push({
                name: teamName,
                players,
                skill: teamSkill,
                damage: teamDamage,
                survival: teamSurvival,
                rating: parseFloat(teamRating)
            });
        });
        
        return { matchType, teams };
    }
}

// ============= PROCESS DATA (FIXED) =============
function processData() {
    if (!validateInputs()) return;
    
    const processingSection = document.getElementById('processingSection');
    if (processingSection) {
        processingSection.classList.remove('hidden');
        document.getElementById('resultsSection')?.classList.add('hidden');
        document.getElementById('errorSection')?.classList.add('hidden');
        document.getElementById('newPredictionSection')?.classList.add('hidden');
        
        startProcessingAnimation();
        
        const matchType = document.querySelector('input[name="matchType"]:checked').value;
        const data = collectPlayerData(matchType);
        
        // Generate prediction
        const prediction = generateInfiKnightPrediction(data);
        
        // Store results
        window.currentPrediction = prediction;
        window.currentMatchType = matchType;
        window.currentData = data;
        
        // Show results after animation
        setTimeout(() => {
            showResults();
        }, 3500);
    }
}

// ============= GENERATE INFIKNIGHT PREDICTION (FIXED) =============
function generateInfiKnightPrediction(data) {
    const prediction = {
        confidence: 0,
        winner: 0,
        teams: {},
        players: [],
        algorithm: 'InfiKnight v3.0'
    };
    
    if (data.matchType === 'solo') {
        const players = data.players;
        
        // Sort by rating
        const sorted = [...players].sort((a, b) => b.rating - a.rating);
        
        prediction.players = sorted;
        prediction.winner = players.indexOf(sorted[0]);
        
        // Calculate confidence based on rating difference
        if (players.length >= 2) {
            const diff = sorted[0].rating - sorted[1].rating;
            prediction.confidence = Math.min(97, Math.round(75 + (diff * 3)));
        } else {
            prediction.confidence = 85;
        }
        
        // Create score mapping
        players.forEach((player, index) => {
            prediction.teams[index] = player.rating;
        });
    } else {
        const teams = data.teams;
        
        // Sort teams by rating
        const sorted = [...teams].sort((a, b) => b.rating - a.rating);
        
        prediction.teams = {};
        teams.forEach((team, index) => {
            prediction.teams[index] = team.rating;
        });
        
        prediction.winner = teams.indexOf(sorted[0]);
        prediction.teamNames = {};
        teams.forEach((team, index) => {
            prediction.teamNames[index] = team.name;
        });
        
        // Calculate confidence
        if (teams.length >= 2) {
            const diff = sorted[0].rating - sorted[1].rating;
            prediction.confidence = Math.min(95, Math.round(70 + (diff * 2.5)));
        } else {
            prediction.confidence = 80;
        }
    }
    
    return prediction;
}

// ============= START PROCESSING ANIMATION =============
function startProcessingAnimation() {
    let step = 1;
    const totalSteps = 4;
    
    const interval = setInterval(() => {
        const currentStep = document.querySelector(`.step[data-step="${step}"]`);
        if (currentStep) {
            currentStep.classList.add('active');
            const status = currentStep.querySelector('.step-status');
            if (status) status.innerHTML = '<i class="fas fa-check"></i>';
        }
        
        const progress = (step / totalSteps) * 100;
        const progressBar = document.getElementById('progressBar');
        if (progressBar) progressBar.style.width = `${progress}%`;
        
        step++;
        
        if (step > totalSteps) {
            clearInterval(interval);
        }
    }, 800);
    
    const startTime = Date.now();
    const timeElement = document.getElementById('processingTime');
    const timerInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        if (timeElement) timeElement.textContent = `${elapsed}s`;
    }, 1000);
    
    window.processingInterval = interval;
    window.timerInterval = timerInterval;
}

// ============= SHOW RESULTS (FIXED) =============
function showResults() {
    if (window.processingInterval) clearInterval(window.processingInterval);
    if (window.timerInterval) clearInterval(window.timerInterval);
    
    document.getElementById('processingSection')?.classList.add('hidden');
    
    const resultsSection = document.getElementById('resultsSection');
    const newPredictionSection = document.getElementById('newPredictionSection');
    
    if (resultsSection) {
        resultsSection.classList.remove('hidden');
        updateResults();
        
        if (newPredictionSection) {
            setTimeout(() => {
                newPredictionSection.classList.remove('hidden');
            }, 1000);
        }
        
        setTimeout(() => {
            resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);
    }
}

// ============= UPDATE RESULTS (FIXED) =============
function updateResults() {
    const prediction = window.currentPrediction;
    const matchType = window.currentMatchType;
    const data = window.currentData;
    
    if (!prediction) {
        console.error('No prediction data');
        return;
    }
    
    // Update confidence
    const confidence = prediction.confidence || 85;
    document.getElementById('confidenceValueMain').textContent = `${confidence}%`;
    document.getElementById('predictionAccuracy').textContent = `${confidence}%`;
    
    // Update date
    document.getElementById('resultDate').textContent = new Date().toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
    
    if (matchType === 'solo') {
        updateSoloResults(data?.players || [], prediction);
    } else {
        updateTeamResults(data?.teams || [], prediction);
    }
}

// ============= UPDATE SOLO RESULTS (FIXED) =============
function updateSoloResults(players, prediction) {
    if (!players || players.length === 0) return;
    
    const sorted = [...players].sort((a, b) => b.rating - a.rating);
    const winner = sorted[0];
    const runnerUp = sorted[1] || sorted[0];
    const weakLink = sorted[sorted.length - 1];
    
    // Update winner info
    document.getElementById('winnerName').textContent = winner.name;
    document.getElementById('winnerSkill').textContent = winner.skill || '85';
    document.getElementById('winnerDamage').textContent = winner.damage || '75';
    document.getElementById('winnerSurvival').textContent = winner.survival || '70';
    document.getElementById('winnerRating').textContent = winner.rating.toFixed(1);
    
    const winnerCard = document.querySelector('.winner-card');
    if (winnerCard) {
        winnerCard.classList.remove('medal-gold', 'medal-silver', 'medal-bronze');
        winnerCard.classList.add('medal-gold');
    }
    
    // Update top performer
    document.getElementById('topPerformerName').textContent = winner.name;
    document.getElementById('topPerformerSkill').textContent = winner.skill || '85';
    document.getElementById('topPerformerDamage').textContent = winner.damage || '75';
    document.getElementById('topPerformerSurvival').textContent = winner.survival || '70';
    
    // Update weak link
    document.getElementById('weakLinkName').textContent = weakLink.name;
    document.getElementById('weakLinkSkill').textContent = weakLink.skill || '45';
    document.getElementById('weakLinkDamage').textContent = weakLink.damage || '40';
    document.getElementById('weakLinkSurvival').textContent = weakLink.survival || '35';
    
    // Update improvement tip
    let tip = '';
    if (weakLink.skill < 50) tip = 'Focus on aim training and positioning';
    else if (weakLink.damage < 50) tip = 'Work on dealing more damage per match';
    else if (weakLink.survival < 50) tip = 'Improve survival time and game sense';
    else tip = 'Consistent performance needed';
    document.getElementById('improvementTip').textContent = tip;
    
    // Update synergy (for solo, just average rating)
    const avgRating = players.reduce((sum, p) => sum + p.rating, 0) / players.length;
    document.getElementById('synergyScore').textContent = (avgRating / 10).toFixed(1);
    document.getElementById('synergyMeter').style.width = `${avgRating * 10}%`;
    document.getElementById('synergyDesc').textContent = players.length > 2 ? 'Solo lobby competition' : 'Direct matchup';
    
    // Hide team members
    document.getElementById('teamMembers')?.classList.add('hidden');
}

// ============= UPDATE TEAM RESULTS (FIXED) =============
function updateTeamResults(teams, prediction) {
    if (!teams || teams.length === 0) return;
    
    const sorted = [...teams].sort((a, b) => b.rating - a.rating);
    const winner = sorted[0];
    
    // Update winner info
    document.getElementById('winnerName').textContent = winner.name;
    document.getElementById('winnerSkill').textContent = winner.skill || '85';
    document.getElementById('winnerDamage').textContent = winner.damage || '75';
    document.getElementById('winnerSurvival').textContent = winner.survival || '70';
    document.getElementById('winnerRating').textContent = winner.rating.toFixed(1);
    
    const winnerCard = document.querySelector('.winner-card');
    if (winnerCard) {
        winnerCard.classList.remove('medal-gold', 'medal-silver', 'medal-bronze');
        winnerCard.classList.add('medal-gold');
    }
    
    // Find top performer (best player on winning team)
    if (winner.players && winner.players.length > 0) {
        const topPlayer = winner.players.sort((a, b) => b.rating - a.rating)[0];
        document.getElementById('topPerformerName').textContent = topPlayer.name;
        document.getElementById('topPerformerSkill').textContent = topPlayer.skill || '85';
        document.getElementById('topPerformerDamage').textContent = topPlayer.damage || '75';
        document.getElementById('topPerformerSurvival').textContent = topPlayer.survival || '70';
    }
    
    // Find weakest player overall
    const allPlayers = teams.flatMap(t => t.players || []);
    if (allPlayers.length > 0) {
        const weakest = allPlayers.sort((a, b) => a.rating - b.rating)[0];
        document.getElementById('weakLinkName').textContent = weakest.name;
        document.getElementById('weakLinkSkill').textContent = weakest.skill || '45';
        document.getElementById('weakLinkDamage').textContent = weakest.damage || '40';
        document.getElementById('weakLinkSurvival').textContent = weakest.survival || '35';
    }
    
    // Update synergy
    const synergy = calculateTeamSynergyScore(winner.players || []);
    document.getElementById('synergyScore').textContent = synergy.toFixed(1);
    document.getElementById('synergyMeter').style.width = `${synergy * 10}%`;
    document.getElementById('synergyDesc').textContent = getSynergyDescription(synergy);
    
    // Update team performance grid
    document.getElementById('teamMembers')?.classList.remove('hidden');
    const performanceGrid = document.getElementById('teamPerformanceGrid');
    if (performanceGrid) {
        performanceGrid.innerHTML = '';
        
        sorted.slice(0, 3).forEach((team, index) => {
            const medalClass = index === 0 ? 'medal-gold' : index === 1 ? 'medal-silver' : 'medal-bronze';
            const teamCard = document.createElement('div');
            teamCard.className = `team-performance-card ${medalClass}`;
            teamCard.innerHTML = `
                <div class="team-rank">#${index + 1}</div>
                <div class="team-name">${team.name}</div>
                <div class="team-stats">
                    <span class="stat">Skill: ${team.skill || '85'}</span>
                    <span class="stat">Damage: ${team.damage || '75'}</span>
                    <span class="stat">Survival: ${team.survival || '70'}</span>
                    <span class="stat">Rating: ${team.rating.toFixed(1)}</span>
                </div>
            `;
            performanceGrid.appendChild(teamCard);
        });
    }
}

// ============= CALCULATE TEAM SYNERGY SCORE =============
function calculateTeamSynergyScore(players) {
    if (!players || players.length < 2) return 5.0;
    
    const ratings = players.map(p => p.rating);
    const avg = ratings.reduce((a, b) => a + b, 0) / ratings.length;
    const variance = ratings.reduce((sum, r) => sum + Math.pow(r - avg, 2), 0) / ratings.length;
    const stdDev = Math.sqrt(variance);
    
    // Lower std deviation = better synergy
    const synergy = Math.max(0, 10 - (stdDev * 1.5));
    return Math.min(10, parseFloat(synergy.toFixed(1)));
}

// ============= GET SYNERGY DESCRIPTION =============
function getSynergyDescription(synergy) {
    if (synergy >= 8) return 'Excellent team coordination';
    if (synergy >= 6) return 'Good team synergy';
    if (synergy >= 4) return 'Average coordination';
    return 'Needs better teamwork';
}

// ============= RESET FORM =============
function resetForm() {
    document.getElementById('resultsSection')?.classList.add('hidden');
    document.getElementById('errorSection')?.classList.add('hidden');
    document.getElementById('ocrPreview')?.classList.add('hidden');
    document.getElementById('newPredictionSection')?.classList.add('hidden');
    
    const statsFile = document.getElementById('statsFile');
    if (statsFile) statsFile.value = '';
    
    const uploadZone = document.getElementById('uploadZone');
    if (uploadZone) uploadZone.style.display = 'block';
    
    document.querySelector('.method-tab[data-method="upload"]').click();
    
    document.querySelector('input[name="matchType"][value="solo"]').checked = true;
    updateInputMode();
    
    const playerInputs = document.getElementById('playerInputs');
    if (playerInputs) {
        playerInputs.innerHTML = `
            <div class="career-stats-info">
                <i class="fas fa-info-circle"></i> 
                Enter career statistics - InfiKnight will calculate skill, damage & survival scores
            </div>
            <div class="player-input-section">
                <h4><i class="fas fa-user-circle"></i> Player 1</h4>
                <div class="input-grid">
                    <div class="input-group">
                        <label>Player Name</label>
                        <input type="text" class="player-name" value="Player 1">
                    </div>
                    <div class="input-group">
                        <label>K/D Ratio</label>
                        <input type="number" class="player-kd" step="0.1" min="0.1" max="15" value="2.5">
                    </div>
                    <div class="input-group">
                        <label>Win Rate %</label>
                        <input type="number" class="player-winrate" step="0.1" min="0" max="100" value="15">
                    </div>
                    <div class="input-group">
                        <label>Top 10 Rate %</label>
                        <input type="number" class="player-top10" step="0.1" min="0" max="100" value="40">
                    </div>
                    <div class="input-group">
                        <label>Avg Damage</label>
                        <input type="number" class="player-avgdamage" step="10" min="0" max="2000" value="350">
                    </div>
                    <div class="input-group">
                        <label>Headshot Rate %</label>
                        <input type="number" class="player-headshot" step="0.1" min="0" max="100" value="25">
                    </div>
                    <div class="input-group">
                        <label>Accuracy %</label>
                        <input type="number" class="player-accuracy" step="0.1" min="0" max="100" value="20">
                    </div>
                </div>
                <button class="btn btn-danger btn-sm remove-player" style="display: none;" 
                        onclick="removePlayerInput(this)">
                    <i class="fas fa-times"></i> Remove Player
                </button>
            </div>
        `;
    }
    
    const teamInputs = document.getElementById('teamInputs');
    if (teamInputs) teamInputs.innerHTML = '';
    
    showNotification('Form reset successfully!', 'info');
}

// ============= RETRY PROCESSING =============
function retryProcessing() {
    document.getElementById('errorSection')?.classList.add('hidden');
    processData();
}

// ============= SWITCH TO MANUAL INPUT =============
function switchToManualInput() {
    document.querySelector('.method-tab[data-method="manual"]').click();
    showNotification('Please enter your data manually for accurate predictions.', 'info');
}

// ============= IMPORT FROM API =============
function importFromAPI() {
    const playerIds = document.getElementById('playerIds')?.value;
    if (!playerIds || playerIds.trim() === '') {
        showNotification('Please enter player IDs or match codes', 'error');
        return;
    }
    showNotification('API import would fetch real data here. Enter data manually for now.', 'info');
}

// ============= SHOW NOTIFICATION =============
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Format number helper
window.formatNumber = formatNumber;
    </script>
</body>
</html>
<?php
// Include footer
include_once('footer.php');
?>