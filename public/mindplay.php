<?php
session_start();
require_once __DIR__ . '/../includes/db.php';


$disable_dashboard_bg = true;
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = 'MindPlay - Well-Being & Productivity';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - EtudeSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/modern.css">
    <style>
        /* CSS Design Tokens - matching FocusFlow EXACTLY */
        :root {
            --accent1: #7c4dff;
            --accent2: #47d7d3;
            --glass-bg: rgba(18,20,22,0.25);
            --glass-border: rgba(255,255,255,0.06);
            --glass-blur: 12px;
            --card-radius: 22px;
            --panel-radius: 28px;
            --max-width: 1200px;
            --container-padding: 32px;
            --spacing-sm: 12px;
            --spacing-md: 24px;
            --spacing-lg: 40px;
            --h1-size: 48px;
            --h2-size: 28px;
            --body-size: 16px;
            --accent-gradient: linear-gradient(90deg, var(--accent1), var(--accent2));
        }

        body {
            background: transparent;
            min-height: 100vh;
            font-family: 'Inter', 'Poppins', system-ui, Arial, sans-serif;
            color: var(--neutral-900);
            position: relative;
        }

        /* Background with MindPlay image - matching FocusFlow structure EXACTLY */
       /* Base background container */
.mindplay-bg {
    position: fixed;
    inset: 0;
    z-index: -120;
    overflow: hidden;
    pointer-events: none;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

/* Dashboard background */
.mindplay-bg.dashboard-bg {
    background-image: url('assets/images/mindplay-bg.jpeg');
}

/* Sub-feature background */
.mindplay-bg.subfeature-bg {
    background-image: url('assets/images/infovault_bg.jpg');
}

/* Overlay stays same */
/* ONE clean background treatment */
.mindplay-bg::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.06); /* very light veil */
    z-index: 1;
}

.mindplay-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    backdrop-filter: blur(0.2px);
    z-index: 2;
}
.mindplay-bg.subfeature-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    backdrop-filter: blur(0.6px) brightness(1.25) saturate(1.1);
    z-index: 2;
}  


        /* Container - matching FocusFlow EXACTLY */
        .mindplay-container {
            min-height: calc(100vh - 120px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            position: relative;
            z-index: 3;
        }

        /* Hero glass card - matching FocusFlow header EXACTLY */
        .mindplay-header {
            width: 100%;
            max-width: 1000px;
            padding: 32px;
            border-radius: 18px;
            background: rgba(15,20,30,0.45);
            backdrop-filter: blur(12px) saturate(160%);
            -webkit-backdrop-filter: blur(12px) saturate(160%);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 26px 70px rgba(0,0,0,0.55);
            text-align: center;
            position: relative;
            z-index: 5;
            margin-bottom: var(--spacing-lg);
        }

        .mindplay-header h1 {
            font-family: 'Poppins', var(--font-display);
            font-size: 1.9rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: #fff;
            line-height: 1.2;
            text-shadow: 0 4px 14px rgba(0,0,0,0.45);
        }

        .mindplay-header p {
            max-width: 700px;
            margin: 0 auto 18px auto;
            font-size: 1.05rem;
            color: rgba(255,255,255,0.85);
        }

        /* Module Grid - matching FocusFlow style EXACTLY */
        .mindplay-modules-grid {
            width: 100%;
            max-width: 1000px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            align-items: stretch;
        }

        /* Module Cards - matching FocusFlow module-card style EXACTLY */
        .mindplay-module-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            border-radius: 14px;
            background: rgba(255,255,255,0.035);
            border: 1px solid rgba(255,255,255,0.07);
            box-shadow: 0 14px 40px rgba(0,0,0,0.42);
            text-decoration: none;
            color: #fff;
            transition: transform 0.18s cubic-bezier(0.2, 0.9, 0.3, 1), box-shadow 0.18s ease, filter 0.18s ease;
            min-height: 140px;
            position: relative;
            cursor: pointer;
        }

        .mindplay-module-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 22px 60px rgba(124,77,255,0.35);
            filter: brightness(1.12);
        }

        .mindplay-module-icon {
            font-size: 48px;
            margin-bottom: 8px;
        }

        .mindplay-module-name {
            font-family: 'Poppins', var(--font-display);
            font-size: 1rem;
            font-weight: 700;
            text-align: center;
            margin-top: 4px;
        }

        .mindplay-module-desc {
            font-family: 'Inter', var(--font-body);
            font-size: 0.75rem;
            color: rgba(255,255,255,0.7);
            text-align: center;
        }

        /* Module Pages */
        .module-page {
            width: 100%;
            max-width: 1000px;
            animation: fadeInUp 0.3s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Individual Module Views */
        .module-view {
            display: none;
            width: 100%;
            max-width: 1000px;
        }

        .module-view.active {
            display: block;
        }

        /* Glass Panel - matching FocusFlow */
        .glass-panel {
            background: rgba(20,30,35,0.30);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px;
            padding: 32px;
            backdrop-filter: blur(12px) saturate(160%);
            -webkit-backdrop-filter: blur(12px) saturate(160%);
            box-shadow: 0 26px 70px rgba(0,0,0,0.55);
        }

        /* Module header with back button */
        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .module-header h2 {
            font-family: 'Poppins', var(--font-display);
            font-size: var(--h2-size);
            font-weight: 800;
            color: #fff;
            margin: 0;
        }

        /* Button Styles - matching FocusFlow */
        .btn {
            padding: 10px 18px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: transform 0.18s, box-shadow 0.18s;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
        }

        .btn.primary {
            background: linear-gradient(90deg, var(--accent1), var(--accent2));
            color: #fff;
            box-shadow: 0 12px 40px rgba(124,77,255,0.25);
        }

        .btn.primary:hover:not(:disabled) {
            transform: translateY(-4px);
            box-shadow: 0 28px 70px rgba(124,77,255,0.35);
        }

        .btn.secondary {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
        }

        .btn.secondary:hover:not(:disabled) {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Mood Tracker Styles */
        .mood-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 16px;
            margin: 24px 0;
        }

        .mood-btn {
            background: rgba(255,255,255,0.06);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 20px;
            font-size: 48px;
            cursor: pointer;
            transition: all 0.18s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .mood-btn span {
            font-size: 12px;
            text-transform: capitalize;
            color: rgba(255,255,255,0.7);
        }

        .mood-btn:hover:not(.disabled) {
            background: rgba(124,77,255,0.2);
            border-color: var(--accent1);
            transform: scale(1.05);
        }

        .mood-btn.selected {
            background: rgba(124,77,255,0.3);
            border-color: var(--accent1);
        }

        .mood-btn.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Journal Styles */
        .journal-container {
            margin: 24px 0;
        }

        .journal-editor {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 20px;
        }

        .journal-textarea {
            width: 100%;
            min-height: 400px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 16px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            line-height: 1.6;
            resize: vertical;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.25);
        }

        .journal-textarea:focus {
            outline: none;
            border-color: rgba(124,77,255,0.4);
            box-shadow: 0 0 12px rgba(124,77,255,0.4);
        }

        .journal-textarea:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .journal-controls {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .date-nav {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .date-nav button {
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: 'Inter', sans-serif;
        }

        .date-nav button:hover:not(:disabled) {
            background: rgba(255,255,255,0.1);
        }

        .date-nav button:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        /* Games Styles */
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin: 24px 0;
        }

        .game-card {
            background: rgba(255,255,255,0.035);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .game-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 22px 60px rgba(124,77,255,0.35);
            filter: brightness(1.12);
        }

        .game-card h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            margin: 8px 0 4px 0;
            color: #fff;
        }

        .game-card p {
            font-family: 'Inter', sans-serif;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.7);
            margin: 0;
        }

        .game-area {
            margin: 24px 0;
        }

        /* Sudoku Grid */
        .sudoku-grid {
            display: grid;
            grid-template-columns: repeat(9, 1fr);
            gap: 1px;
            max-width: 500px;
            margin: 24px auto;
            background: rgba(255,255,255,0.2);
            padding: 1px;
        }

        .sudoku-cell {
            aspect-ratio: 1;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            cursor: pointer;
            color: #fff;
        }

        .sudoku-cell.given {
            background: rgba(124,77,255,0.2);
            cursor: not-allowed;
        }

        .sudoku-cell.selected {
            background: rgba(71,215,211,0.3);
        }

        /* XO Grid */
        .xo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            max-width: 400px;
            margin: 24px auto;
        }

        .xo-cell {
            aspect-ratio: 1;
            background: rgba(255,255,255,0.06);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            color: #fff;
        }

        .xo-cell:hover:not(.taken) {
            background: rgba(255,255,255,0.1);
            transform: scale(1.05);
        }

        .xo-cell.taken {
            cursor: not-allowed;
        }

        /* Memory Match Grid */
        .memory-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            max-width: 500px;
            margin: 24px auto;
        }

        .memory-card {
            aspect-ratio: 1;
            background: rgba(255,255,255,0.06);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .memory-card.flipped {
            background: rgba(124,77,255,0.2);
        }

        .memory-card.matched {
            background: rgba(16,185,129,0.2);
            cursor: not-allowed;
        }

        /* Stats Display */
        .stats-display {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .stat-box {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 16px 24px;
            text-align: center;
        }

        .stat-box .label {
            font-size: 12px;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.7);
        }

        .stat-box .value {
            font-size: 28px;
            font-weight: 700;
            margin-top: 4px;
            color: #fff;
        }

        /* Messages */
        .message {
            padding: 12px 20px;
            border-radius: 10px;
            margin: 16px 0;
            font-weight: 500;
        }

        .message.success {
            background: rgba(16,185,129,0.2);
            border: 1px solid rgba(16,185,129,0.3);
            color: #10b981;
        }

        .message.error {
            background: rgba(239,68,68,0.2);
            border: 1px solid rgba(239,68,68,0.3);
            color: #ef4444;
        }

        .message.info {
            background: rgba(124,77,255,0.2);
            border: 1px solid rgba(124,77,255,0.3);
            color: #7c4dff;
        }

        .hidden {
            display: none !important;
        }

        /* Timer Display */
        .timer {
            font-size: 32px;
            font-weight: 700;
            text-align: center;
            margin: 16px 0;
            font-family: 'Courier New', monospace;
            color: #fff;
        }

        /* Reports Charts */
        .chart-container {
            background: rgba(255,255,255,0.04);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }

        .chart-container h3 {
            color: #fff;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 16px;
        }

        .chart-bar {
            background: var(--accent-gradient);
            height: 24px;
            border-radius: 6px;
            margin: 8px 0;
        }

        .chart-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 14px;
            color: rgba(255,255,255,0.85);
        }

        /* Responsive - matching FocusFlow */
        @media (max-width: 980px) {
            .mindplay-modules-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .mindplay-header h1 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 560px) {
            .mindplay-modules-grid {
                grid-template-columns: 1fr;
            }

            .games-grid {
                grid-template-columns: 1fr;
            }

            .mindplay-header {
                padding: 24px;
            }

            .mindplay-header h1 {
                font-size: 1.3rem;
            }

            .glass-panel {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Background with MindPlay image -->
    <div id="page-bg" class="mindplay-bg dashboard-bg"></div>


    <?php require_once __DIR__ . '/../includes/header_dashboard.php'; ?>

    <!-- Main Container - matching FocusFlow structure -->
    <div class="mindplay-container">
        <!-- Hub View (Default) -->
        <div id="hub-view" class="module-view active">
            <div class="mindplay-header">
               
            

            <div class="mindplay-modules-grid">
                <div class="mindplay-module-card" onclick="MindPlay.showModule('mood')">
                    <div class="mindplay-module-icon">😊</div>
                    <div class="mindplay-module-name">Mood Tracker</div>
                    <div class="mindplay-module-desc">Track your daily mood</div>
                </div>

                <div class="mindplay-module-card" onclick="MindPlay.showModule('journal')">
                    <div class="mindplay-module-icon">📝</div>
                    <div class="mindplay-module-name">Journal</div>
                    <div class="mindplay-module-desc">Daily reflection & notes</div>
                </div>

                <div class="mindplay-module-card" onclick="MindPlay.showModule('games')">
                    <div class="mindplay-module-icon">🎮</div>
                    <div class="mindplay-module-name">Games</div>
                    <div class="mindplay-module-desc">Mindful productivity games</div>
                </div>

                <div class="mindplay-module-card" onclick="MindPlay.showModule('reports')">
                    <div class="mindplay-module-icon">📊</div>
                    <div class="mindplay-module-name">Reports</div>
                    <div class="mindplay-module-desc">Insights & analytics</div>
                </div>
            </div>
            </div>
        </div>

        <!-- Mood Tracker Module -->
        <div id="mood-view" class="module-view module-page">
            <div class="glass-panel">
                <div class="module-header">
                    <h2>Mood Tracker</h2>
                    <button class="btn secondary" onclick="MindPlay.showHub()">← Back</button>
                </div>

                <div id="mood-message"></div>

                <p style="color: rgba(255,255,255,0.85); margin-bottom: 24px;">How are you feeling today?</p>

                <div class="mood-selector" id="mood-selector">
                    <button class="mood-btn" data-mood="happy" onclick="MindPlay.selectMood('happy')">
                        😊<span>Happy</span>
                    </button>
                    <button class="mood-btn" data-mood="sad" onclick="MindPlay.selectMood('sad')">
                        😢<span>Sad</span>
                    </button>
                    <button class="mood-btn" data-mood="neutral" onclick="MindPlay.selectMood('neutral')">
                        😐<span>Neutral</span>
                    </button>
                    <button class="mood-btn" data-mood="excited" onclick="MindPlay.selectMood('excited')">
                        🤩<span>Excited</span>
                    </button>
                    <button class="mood-btn" data-mood="anxious" onclick="MindPlay.selectMood('anxious')">
                        😰<span>Anxious</span>
                    </button>
                    <button class="mood-btn" data-mood="calm" onclick="MindPlay.selectMood('calm')">
                        😌<span>Calm</span>
                    </button>
                    <button class="mood-btn" data-mood="energetic" onclick="MindPlay.selectMood('energetic')">
                        ⚡<span>Energetic</span>
                    </button>
                    <button class="mood-btn" data-mood="tired" onclick="MindPlay.selectMood('tired')">
                        😴<span>Tired</span>
                    </button>
                </div>

                <button id="mood-save-btn" class="btn primary" onclick="MindPlay.saveMood()" style="margin-top: 24px;">
                    Save Mood
                </button>
            </div>
        </div>

        <!-- Journal Module -->
        <div id="journal-view" class="module-view module-page">
            <div class="glass-panel">
                <div class="module-header">
                    <h2>Journal</h2>
                    <button class="btn secondary" onclick="MindPlay.showHub()">← Back</button>
                </div>

                <div id="journal-message"></div>

                <div class="journal-controls">
                    <div class="date-nav">
                        <button onclick="MindPlay.navigateJournalDate(-1)">← Previous</button>
                        <span id="journal-date" style="font-weight: 600; color: #fff;"></span>
                        <button id="journal-next-btn" onclick="MindPlay.navigateJournalDate(1)" disabled>Next →</button>
                    </div>
                </div>

                <div class="journal-container">
                    <div class="journal-editor">
                        <textarea
                            id="journal-content"
                            class="journal-textarea"
                            placeholder="Write your thoughts..."
                        ></textarea>

                        <div style="display: flex; gap: 12px; margin-top: 16px;">
                            <button id="journal-submit-btn" class="btn primary" onclick="MindPlay.submitJournal()">
                                Submit Entry
                            </button>
                            <button class="btn secondary" onclick="MindPlay.autoSaveJournal()">
                                Auto-Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Games Module -->
        <div id="games-view" class="module-view module-page">
            <div class="glass-panel">
                <div class="module-header">
                    <h2>Games</h2>
                    <button class="btn secondary" onclick="MindPlay.showHub()">← Back</button>
                </div>

                <div id="games-hub">
                    <p style="color: rgba(255,255,255,0.85); margin-bottom: 24px;">Choose a game to play:</p>

                    <div class="games-grid">
                        <div class="game-card" onclick="MindPlay.showGame('sudoku')">
                            <div style="font-size: 48px;">🧩</div>
                            <h3>Sudoku</h3>
                            <p>Number puzzle</p>
                        </div>

                        <div class="game-card" onclick="MindPlay.showGame('xo')">
                            <div style="font-size: 48px;">❌⭕</div>
                            <h3>XO</h3>
                            <p>Tic-Tac-Toe</p>
                        </div>

                        <div class="game-card" onclick="MindPlay.showGame('memory')">
                            <div style="font-size: 48px;">🧠</div>
                            <h3>Memory Match</h3>
                            <p>Card matching</p>
                        </div>

                        <div class="game-card" onclick="MindPlay.showGame('math')">
                            <div style="font-size: 48px;">➕</div>
                            <h3>Quick Math</h3>
                            <p>Arithmetic challenge</p>
                        </div>

                        <div class="game-card" onclick="MindPlay.showGame('word')">
                            <div style="font-size: 48px;">🔤</div>
                            <h3>Word Unscramble</h3>
                            <p>Word puzzle</p>
                        </div>
                    </div>
                </div>

                <!-- Individual Game Areas -->
                <div id="game-area" class="hidden"></div>
            </div>
        </div>

        <!-- Reports Module -->
        <div id="reports-view" class="module-view module-page">
            <div class="glass-panel">
                <div class="module-header">
                    <h2>Reports & Insights</h2>
                    <button class="btn secondary" onclick="MindPlay.showHub()">← Back</button>
                </div>

                <div id="reports-content">
                    <p style="text-align: center; color: rgba(255,255,255,0.7);">Loading reports...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // MindPlay Application
        const MindPlay = {
            currentModule: 'hub',
            currentGame: null,
            selectedMood: null,
            currentJournalDate: new Date().toISOString().split('T')[0],
            journalAutoSaveInterval: null,

            // Initialize
            init() {
                console.log('MindPlay initialized');
                this.showHub();
            },
            setDashboardBg() {
    const bg = document.getElementById('page-bg');
    bg.classList.remove('subfeature-bg');
    bg.classList.add('dashboard-bg');
},

setSubFeatureBg() {
    const bg = document.getElementById('page-bg');
    bg.classList.remove('dashboard-bg');
    bg.classList.add('subfeature-bg');
},


            // Module Navigation
           showHub() {
    document.querySelectorAll('.module-view').forEach(v => v.classList.remove('active'));
    document.getElementById('hub-view').classList.add('active');
    this.currentModule = 'hub';

    // ✅ Dashboard background
    this.setDashboardBg();
},


        showModule(module) {
    document.querySelectorAll('.module-view').forEach(v => v.classList.remove('active'));
    const viewId = module + '-view';
    document.getElementById(viewId).classList.add('active');
    this.currentModule = module;

    // ✅ Sub-feature background
    this.setSubFeatureBg();

    switch(module) {
        case 'mood':
            this.loadMoodData();
            break;
        case 'journal':
            this.loadJournalData();
            this.startJournalAutoSave();
            break;
        case 'reports':
            this.loadReports();
            break;
    }
},


            // Message Display
            showMessage(containerId, message, type = 'info') {
                const container = document.getElementById(containerId);
                container.innerHTML = `<div class="message ${type}">${message}</div>`;
                setTimeout(() => {
                    container.innerHTML = '';
                }, 5000);
            },

            // =====================================================
            // MOOD TRACKER
            // =====================================================
            async loadMoodData() {
                try {
                    const response = await fetch('api/mindplay/mood_get.php');
                    const data = await response.json();

                    if (data.success && data.data.today_mood_set) {
                        // Disable all mood buttons and show selected mood
                        this.selectedMood = data.data.today_mood_value;
                        document.querySelectorAll('.mood-btn').forEach(btn => {
                            btn.classList.add('disabled');
                            if (btn.dataset.mood === this.selectedMood) {
                                btn.classList.add('selected');
                            }
                        });
                        document.getElementById('mood-save-btn').disabled = true;
                        this.showMessage('mood-message', 'You have already set your mood for today!', 'info');
                    }
                } catch (error) {
                    console.error('Error loading mood data:', error);
                }
            },

            selectMood(mood) {
                if (document.querySelector('.mood-btn.disabled')) return;

                this.selectedMood = mood;
                document.querySelectorAll('.mood-btn').forEach(btn => {
                    btn.classList.remove('selected');
                });
                document.querySelector(`[data-mood="${mood}"]`).classList.add('selected');
            },

            async saveMood() {
                if (!this.selectedMood) {
                    this.showMessage('mood-message', 'Please select a mood first!', 'error');
                    return;
                }

                try {
                    const response = await fetch('api/mindplay/mood_save.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mood_value: this.selectedMood })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showMessage('mood-message', data.message, 'success');
                        document.querySelectorAll('.mood-btn').forEach(btn => btn.classList.add('disabled'));
                        document.getElementById('mood-save-btn').disabled = true;
                    } else {
                        this.showMessage('mood-message', data.message, 'error');
                    }
                } catch (error) {
                    this.showMessage('mood-message', 'Error saving mood: ' + error.message, 'error');
                }
            },

            // =====================================================
            // JOURNAL
            // =====================================================
            async loadJournalData() {
    const dateDisplay = document.getElementById('journal-date');
    dateDisplay.textContent = this.currentJournalDate;

    const textarea = document.getElementById('journal-content');
    const submitBtn = document.getElementById('journal-submit-btn');

    try {
        const response = await fetch(
            `api/mindplay/journal_get.php?entry_date=${this.currentJournalDate}`
        );
        const data = await response.json();

        if (!data.success || !Array.isArray(data.data.entries)) {
            textarea.value = '';
            textarea.disabled = true;
            submitBtn.disabled = true;
            return;
        }

        const entry = data.data.entries.find(
            e => e.entry_date === this.currentJournalDate
        );

        if (entry) {
            textarea.value = entry.content || '';
            textarea.disabled = entry.is_locked;
            submitBtn.disabled = entry.is_locked;

            if (entry.is_locked) {
                this.showMessage(
                    'journal-message',
                    'This entry is read-only.',
                    'info'
                );
            }
        } else {
            textarea.value = '';
            textarea.disabled = true;
            submitBtn.disabled = true;

            this.showMessage(
                'journal-message',
                'No journal entry for this day.',
                'info'
            );
        }

        // Disable "Next" if today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('journal-next-btn').disabled =
            this.currentJournalDate >= today;

    } catch (error) {
        console.error('Error loading journal:', error);
    }
},


            navigateJournalDate(direction) {
                const currentDate = new Date(this.currentJournalDate);
                currentDate.setDate(currentDate.getDate() + direction);
                this.currentJournalDate = currentDate.toISOString().split('T')[0];
                this.loadJournalData();
            },

            async autoSaveJournal() {
                const content = document.getElementById('journal-content').value;

                try {
                    const response = await fetch('api/mindplay/journal_save.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            entry_date: this.currentJournalDate,
                            content: content,
                            is_submitted: 0
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.showMessage('journal-message', 'Auto-saved!', 'success');
                    }
                } catch (error) {
                    console.error('Auto-save error:', error);
                }
            },

            async submitJournal() {
                const content = document.getElementById('journal-content').value;

                if (!content.trim()) {
                    this.showMessage('journal-message', 'Please write something first!', 'error');
                    return;
                }

                try {
                    const response = await fetch('api/mindplay/journal_save.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            entry_date: this.currentJournalDate,
                            content: content,
                            is_submitted: 1
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showMessage('journal-message', data.message, 'success');
                        document.getElementById('journal-content').disabled = true;
                        document.getElementById('journal-submit-btn').disabled = true;
                    } else {
                        this.showMessage('journal-message', data.message, 'error');
                    }
                } catch (error) {
                    this.showMessage('journal-message', 'Error: ' + error.message, 'error');
                }
            },

            startJournalAutoSave() {
                // Auto-save every 30 seconds
                if (this.journalAutoSaveInterval) {
                    clearInterval(this.journalAutoSaveInterval);
                }
                this.journalAutoSaveInterval = setInterval(() => {
                    const textarea = document.getElementById('journal-content');
                    if (!textarea.disabled && textarea.value.trim()) {
                        this.autoSaveJournal();
                    }
                }, 30000);
            },

            // =====================================================
            // GAMES
            // =====================================================
            showGame(game) {
                document.getElementById('games-hub').classList.add('hidden');
                const gameArea = document.getElementById('game-area');
                gameArea.classList.remove('hidden');
                this.currentGame = game;

                switch(game) {
                    case 'sudoku':
                        this.initSudoku();
                        break;
                    case 'xo':
                        this.initXO();
                        break;
                    case 'memory':
                        this.initMemoryMatch();
                        break;
                    case 'math':
                        this.initQuickMath();
                        break;
                    case 'word':
                        this.initWordUnscramble();
                        break;
                }
            },

            backToGames() {
                document.getElementById('games-hub').classList.remove('hidden');
                document.getElementById('game-area').classList.add('hidden');
                this.currentGame = null;
            },

            // =====================================================
            // REPORTS
            // =====================================================
            async loadReports() {
                try {
                    const response = await fetch('api/mindplay/reports_get.php?days=30');
                    const data = await response.json();

                    if (data.success) {
                        this.renderReports(data.data);
                    }
                } catch (error) {
                    console.error('Error loading reports:', error);
                }
            },

            renderReports(reports) {
                const container = document.getElementById('reports-content');
                const score = reports.well_being_score.overall_score;

                container.innerHTML = `
                    <div class="stats-display">
                        <div class="stat-box">
                            <div class="label">Well-Being Score</div>
                            <div class="value">${score}/100</div>
                        </div>
                        <div class="stat-box">
                            <div class="label">Mood Entries</div>
                            <div class="value">${reports.mood_insights.total_entries}</div>
                        </div>
                        <div class="stat-box">
                            <div class="label">Journal Streak</div>
                            <div class="value">${reports.journal_insights.current_streak} days</div>
                        </div>
                        <div class="stat-box">
                            <div class="label">Games Played</div>
                            <div class="value">${reports.game_insights.total_games_played}</div>
                        </div>
                    </div>

                    <div class="chart-container">
                        <h3>Mood Distribution (Last 30 Days)</h3>
                        ${this.renderMoodChart(reports.mood_insights.mood_distribution)}
                    </div>

                    <div class="chart-container">
                        <h3>Game Statistics</h3>
                        ${this.renderGameStats(reports.game_insights.games)}
                    </div>
                `;
            },

            renderMoodChart(distribution) {
                if (!distribution || Object.keys(distribution).length === 0) {
                    return '<p style="opacity: 0.7; color: rgba(255,255,255,0.7);">No mood data yet</p>';
                }

                const total = Object.values(distribution).reduce((a, b) => a + b, 0);
                let html = '';

                for (const [mood, count] of Object.entries(distribution)) {
                    const percentage = (count / total * 100).toFixed(1);
                    html += `
                        <div>
                            <div class="chart-label">
                                <span style="text-transform: capitalize;">${mood}</span>
                                <span>${count} (${percentage}%)</span>
                            </div>
                            <div class="chart-bar" style="width: ${percentage}%"></div>
                        </div>
                    `;
                }

                return html;
            },

            renderGameStats(games) {
                if (!games || games.length === 0) {
                    return '<p style="opacity: 0.7; color: rgba(255,255,255,0.7);">No game data yet</p>';
                }

                let html = '<div class="stats-display" style="justify-content: flex-start;">';

                games.forEach(game => {
                    html += `
                        <div class="stat-box">
                            <div class="label">${game.game_type.replace('_', ' ')}</div>
                            <div class="value">${game.total_plays} plays</div>
                            <small style="opacity: 0.7; color: rgba(255,255,255,0.7);">Best: ${game.best_score}</small>
                        </div>
                    `;
                });

                html += '</div>';
                return html;
            }
            
        };

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            MindPlay.init();
        });
    </script>

    <!-- Games implementations -->
    <script src="assets/js/mindplay-games.js"></script>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>