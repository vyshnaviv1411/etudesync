<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student';
$page_title = 'AssessArena - Quiz Platform';
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
        /* CSS Design Tokens - matching FocusFlow exactly */
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

        /* Background with custom desk image */
        .assessarena-bg {
            position: fixed;
            inset: 0;
            z-index: -120;
            overflow: hidden;
            pointer-events: none;
            background-image: url('assets/images/assessarena-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .assessarena-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(2,6,23,0.45), rgba(2,6,23,0.25));
            z-index: 1;
        }

        .assessarena-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            backdrop-filter: blur(0.5px);
            z-index: 2;
        }

        .assessarena-container {
            min-height: calc(100vh - 120px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            position: relative;
            z-index: 3;
        }

        /* Hero glass card matching FocusFlow EXACTLY */
        .assessarena-header {
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

        .assessarena-header h1 {
            font-family: 'Poppins', var(--font-display);
            font-size: 1.9rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: #fff;
            line-height: 1.2;
            text-shadow: 0 4px 14px rgba(0,0,0,0.45);
        }

        .assessarena-header h1 .arena-user {
            color: #a8d8ff;
        }

        .assessarena-header p {
            max-width: 700px;
            margin: 0 auto 18px auto;
            font-size: 1.05rem;
            color: rgba(255,255,255,0.85);
        }

        /* Back to Hub button */
        .back-to-hub {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: var(--spacing-sm);
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.18s ease;
        }

        .back-to-hub:hover {
            background: rgba(255,255,255,0.15);
            transform: translateX(-4px);
        }

        /* Module Grid - matching dashboard style */
        .assessarena-modules-grid {
            width: 100%;
            max-width: 1000px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            align-items: stretch;
        }

        /* Module Cards - matching dashboard module-card style */
        .assessarena-module-card {
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

        .assessarena-module-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 22px 60px rgba(124,77,255,0.35);
            filter: brightness(1.12);
        }

        .assessarena-module-icon {
            font-size: 48px;
            line-height: 1;
        }

        .assessarena-module-name {
            font-family: 'Poppins', var(--font-display);
            font-size: 1rem;
            font-weight: 700;
            text-align: center;
            margin-top: 4px;
        }

        .assessarena-module-desc {
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

        /* White content card for module pages */
        .content-card {
            width: 100%;
            background: #fff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.45);
            margin-bottom: var(--spacing-md);
        }

        .content-card h2 {
            font-family: 'Poppins', var(--font-display);
            font-size: var(--h2-size);
            font-weight: 700;
            color: var(--neutral-900);
            margin-bottom: var(--spacing-md);
        }

        /* Form Inputs */
        .modern-input {
            width: 100%;
            padding: 12px 16px;
            font-family: var(--font-body);
            font-size: 0.9375rem;
            color: var(--neutral-900);
            background: white;
            border: 2px solid var(--neutral-300);
            border-radius: 8px;
            transition: all 150ms;
            margin-bottom: var(--spacing-sm);
        }

        .modern-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(45, 91, 255, 0.1);
        }

        .modern-label {
            display: block;
            font-family: var(--font-body);
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--neutral-700);
            margin-bottom: 8px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-family: 'Inter', var(--font-body);
            font-size: 0.9375rem;
            font-weight: 700;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.18s ease;
            text-decoration: none;
        }

        .btn.primary {
            background: linear-gradient(90deg, #7c4dff, #47d7d3);
            color: #fff;
            box-shadow: 0 14px 40px rgba(76,52,156,0.16);
        }

        .btn.primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 28px 70px rgba(76,52,156,0.22);
        }

        .btn.secondary {
            background: var(--neutral-100);
            color: var(--neutral-700);
            border: 2px solid var(--neutral-200);
        }

        .btn.secondary:hover {
            background: var(--neutral-200);
            border-color: var(--neutral-300);
        }

        .btn.danger {
            background: var(--error);
            color: white;
        }

        .btn.danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Quiz List */
        .quiz-item, .attempt-item {
            padding: 16px;
            border: 2px solid var(--neutral-200);
            border-radius: 12px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.15s ease;
        }

        .quiz-item:hover, .attempt-item:hover {
            border-color: var(--primary-blue);
            background: var(--neutral-50);
        }

        .quiz-info h3 {
            font-family: 'Poppins', var(--font-display);
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--neutral-900);
            margin-bottom: 4px;
        }

        .quiz-meta {
            font-size: 0.875rem;
            color: var(--neutral-500);
        }

        .quiz-code {
            display: inline-block;
            padding: 4px 12px;
            background: var(--primary-blue);
            color: white;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.875rem;
            font-family: monospace;
        }

        .score-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 1rem;
        }

        .score-badge.excellent {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
        }

        .score-badge.good {
            background: linear-gradient(135deg, #3B82F6, #2563EB);
            color: white;
        }

        .score-badge.average {
            background: linear-gradient(135deg, #F59E0B, #D97706);
            color: white;
        }

        .score-badge.poor {
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
        }

        /* Question Builder */
        .question-builder {
            padding: 20px;
            border: 2px solid var(--neutral-200);
            border-radius: 12px;
            margin-bottom: 16px;
            background: var(--neutral-50);
        }

        .question-builder h4 {
            font-family: 'Poppins', var(--font-display);
            font-size: 1rem;
            font-weight: 700;
            color: var(--neutral-900);
            margin-bottom: 12px;
        }

        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        /* Quiz Taking Interface */
        .question-card {
            padding: 24px;
            border: 2px solid var(--neutral-200);
            border-radius: 12px;
            margin-bottom: 20px;
            background: white;
        }

        .question-number {
            display: inline-block;
            padding: 6px 14px;
            background: var(--primary-blue);
            color: white;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.875rem;
            margin-bottom: 12px;
        }

        .question-text {
            font-family: 'Poppins', var(--font-display);
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--neutral-900);
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .option-button {
            width: 100%;
            padding: 16px;
            margin-bottom: 10px;
            border: 2px solid var(--neutral-300);
            border-radius: 10px;
            background: white;
            text-align: left;
            font-family: 'Inter', var(--font-body);
            font-size: 0.9375rem;
            color: var(--neutral-900);
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .option-button:hover {
            border-color: var(--primary-blue);
            background: var(--neutral-50);
        }

        .option-button.selected {
            border-color: var(--primary-blue);
            background: rgba(45, 91, 255, 0.1);
            font-weight: 600;
        }

        .option-button.correct {
            border-color: var(--success);
            background: rgba(16, 185, 129, 0.1);
        }

        .option-button.incorrect {
            border-color: var(--error);
            background: rgba(239, 68, 68, 0.1);
        }

        .option-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--neutral-200);
            font-weight: 700;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .option-button.selected .option-label {
            background: var(--primary-blue);
            color: white;
        }

        /* Timer */
        .quiz-timer {
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 16px 24px;
            background: rgba(15,20,30,0.9);
            backdrop-filter: blur(12px);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: white;
            font-family: 'Poppins', var(--font-display);
            font-size: 1.5rem;
            font-weight: 700;
            z-index: 100;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }

        .quiz-timer.warning {
            background: rgba(245, 158, 11, 0.9);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Leaderboard */
        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
        }

        .leaderboard-table th {
            background: var(--neutral-100);
            padding: 12px;
            text-align: left;
            font-family: 'Poppins', var(--font-display);
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--neutral-700);
            border-bottom: 2px solid var(--neutral-300);
        }

        .leaderboard-table td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--neutral-200);
            font-size: 0.9375rem;
        }

        .leaderboard-table tr:hover {
            background: var(--neutral-50);
        }

        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .rank-badge.rank-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
        }

        .rank-badge.rank-2 {
            background: linear-gradient(135deg, #C0C0C0, #A8A8A8);
            color: #000;
        }

        .rank-badge.rank-3 {
            background: linear-gradient(135deg, #CD7F32, #B8860B);
            color: #fff;
        }

        .rank-badge.rank-other {
            background: var(--neutral-200);
            color: var(--neutral-700);
        }

        /* Responsive */
        @media (max-width: 980px) {
            .assessarena-modules-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .options-grid {
                grid-template-columns: 1fr;
            }

            .quiz-timer {
                top: 80px;
                right: 10px;
                padding: 12px 16px;
                font-size: 1.2rem;
            }
        }

        @media (max-width: 560px) {
            .assessarena-modules-grid {
                grid-template-columns: 1fr;
            }

            .assessarena-header {
                padding: 24px;
            }

            .content-card {
                padding: 20px;
            }

            .quiz-timer {
                position: static;
                margin-bottom: 16px;
                width: 100%;
                text-align: center;
            }
        }

        /* Hide/Show sections */
        .hidden {
            display: none !important;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--neutral-500);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* Loading state */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Background -->
    <div class="assessarena-bg"></div>

    <!-- Main Container -->
    <div class="assessarena-container">

        <!-- Module Hub View (default) -->
        <div id="module-hub">
            <div class="assessarena-header">
                <a href="dashboard.php" class="back-to-hub">← Back to Dashboard</a>
                <h1>Welcome to <span class="arena-user">AssessArena</span></h1>
                <p>Create quizzes, challenge yourself, and climb the leaderboard.</p>
            </div>

            <div class="assessarena-modules-grid">
                <div class="assessarena-module-card" onclick="showModule('create-quiz')">
                    <div class="assessarena-module-icon">📝</div>
                    <div class="assessarena-module-name">Create Quiz</div>
                    <div class="assessarena-module-desc">Build custom quizzes</div>
                </div>

                <div class="assessarena-module-card" onclick="showModule('take-quiz')">
                    <div class="assessarena-module-icon">🎯</div>
                    <div class="assessarena-module-name">Take Quiz</div>
                    <div class="assessarena-module-desc">Answer quiz questions</div>
                </div>

                <div class="assessarena-module-card" onclick="showModule('my-quizzes')">
                    <div class="assessarena-module-icon">📚</div>
                    <div class="assessarena-module-name">My Quizzes</div>
                    <div class="assessarena-module-desc">View created quizzes</div>
                </div>

                <div class="assessarena-module-card" onclick="showModule('history')">
                    <div class="assessarena-module-icon">📊</div>
                    <div class="assessarena-module-name">History</div>
                    <div class="assessarena-module-desc">View past attempts</div>
                </div>

                <div class="assessarena-module-card" onclick="showModule('leaderboard')">
                    <div class="assessarena-module-icon">🏆</div>
                    <div class="assessarena-module-name">Leaderboard</div>
                    <div class="assessarena-module-desc">Top performers</div>
                </div>

                <div class="assessarena-module-card" onclick="showModule('stats')">
                    <div class="assessarena-module-icon">📈</div>
                    <div class="assessarena-module-name">My Stats</div>
                    <div class="assessarena-module-desc">Performance overview</div>
                </div>
            </div>
        </div>

        <!-- CREATE QUIZ MODULE -->
        <div id="create-quiz-module" class="module-page hidden">
            <div class="content-card">
                <button class="btn secondary" onclick="showHub()" style="margin-bottom: 16px;">← Back to Hub</button>
                <h2>Create New Quiz</h2>

                <div id="quiz-setup" class="hidden">
                    <label class="modern-label">Quiz Title</label>
                    <input type="text" id="quiz-title" class="modern-input" placeholder="Enter quiz title">

                    <label class="modern-label">Time Limit (minutes, optional)</label>
                    <input type="number" id="quiz-time-limit" class="modern-input" placeholder="Leave blank for no limit" min="1">

                    <div style="margin-bottom: 16px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="quiz-shuffle" style="width: auto;">
                            <span class="modern-label" style="margin: 0;">Shuffle questions for each attempt</span>
                        </label>
                    </div>

                    <button class="btn primary" onclick="createQuiz()">Create Quiz & Add Questions</button>
                </div>

                <div id="question-builder-section" class="hidden">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <h3 style="margin: 0; font-size: 1.2rem;">Quiz: <span id="current-quiz-title"></span></h3>
                            <p style="margin: 4px 0 0 0; color: var(--neutral-500); font-size: 0.875rem;">
                                Code: <span id="current-quiz-code" class="quiz-code"></span>
                            </p>
                        </div>
                        <button class="btn secondary" onclick="finishQuiz()">Finish Quiz</button>
                    </div>

                    <div class="question-builder">
                        <h4>Add Question <span id="question-count">#1</span></h4>

                        <label class="modern-label">Question Text</label>
                        <textarea id="question-text" class="modern-input" rows="3" placeholder="Enter your question"></textarea>

                        <label class="modern-label">Options</label>
                        <div class="options-grid">
                            <div>
                                <input type="text" id="option-a" class="modern-input" placeholder="Option A">
                            </div>
                            <div>
                                <input type="text" id="option-b" class="modern-input" placeholder="Option B">
                            </div>
                            <div>
                                <input type="text" id="option-c" class="modern-input" placeholder="Option C">
                            </div>
                            <div>
                                <input type="text" id="option-d" class="modern-input" placeholder="Option D">
                            </div>
                        </div>

                        <label class="modern-label">Correct Answer</label>
                        <select id="correct-option" class="modern-input">
                            <option value="">Select correct option</option>
                            <option value="A">Option A</option>
                            <option value="B">Option B</option>
                            <option value="C">Option C</option>
                            <option value="D">Option D</option>
                        </select>

                        <button class="btn primary" onclick="addQuestion()" style="margin-top: 12px;">Add Question</button>
                    </div>

                    <div id="added-questions">
                        <h4 style="margin-bottom: 12px;">Added Questions (<span id="total-questions">0</span>)</h4>
                        <div id="questions-list"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAKE QUIZ MODULE -->
        <div id="take-quiz-module" class="module-page hidden">
            <div class="content-card">
                <button class="btn secondary" onclick="showHub()" style="margin-bottom: 16px;">← Back to Hub</button>
                <h2>Take Quiz</h2>

                <div id="quiz-code-input">
                    <label class="modern-label">Enter Quiz Code</label>
                    <input type="text" id="join-quiz-code" class="modern-input" placeholder="Enter 8-character code" maxlength="8" style="text-transform: uppercase;">
                    <button class="btn primary" onclick="loadQuiz()">Load Quiz</button>
                </div>

                <div id="quiz-interface" class="hidden">
                    <div id="quiz-timer" class="quiz-timer hidden"></div>

                    <div style="margin-bottom: 20px;">
                        <h3 id="quiz-title-display" style="margin: 0 0 8px 0; font-size: 1.3rem;"></h3>
                        <p style="margin: 0; color: var(--neutral-500); font-size: 0.875rem;">
                            <span id="quiz-question-count"></span> |
                            <span id="quiz-time-info"></span>
                        </p>
                    </div>

                    <div id="questions-container"></div>

                    <button class="btn primary" onclick="submitQuiz()" style="width: 100%; padding: 16px; font-size: 1.1rem;">
                        Submit Quiz
                    </button>
                </div>

                <div id="quiz-results" class="hidden">
                    <div style="text-align: center; padding: 40px 20px;">
                        <div style="font-size: 72px; margin-bottom: 16px;">🎉</div>
                        <h2 style="margin-bottom: 16px;">Quiz Completed!</h2>
                        <div id="score-display" style="font-size: 3rem; font-weight: 800; margin-bottom: 24px;"></div>
                        <div id="result-details" style="margin-bottom: 24px;"></div>
                        <div id="answers-review"></div>
                        <button class="btn primary" onclick="showHub()">Back to Hub</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MY QUIZZES MODULE -->
        <div id="my-quizzes-module" class="module-page hidden">
            <div class="content-card">
                <button class="btn secondary" onclick="showHub()" style="margin-bottom: 16px;">← Back to Hub</button>
                <h2>My Created Quizzes</h2>
                <div id="my-quizzes-list"></div>
            </div>
        </div>

        <!-- HISTORY MODULE -->
        <div id="history-module" class="module-page hidden">
            <div class="content-card">
                <button class="btn secondary" onclick="showHub()" style="margin-bottom: 16px;">← Back to Hub</button>
                <h2>My Attempt History</h2>
                <div id="history-list"></div>
            </div>
        </div>

        <!-- LEADERBOARD MODULE -->
        <div id="leaderboard-module" class="module-page hidden">
            <div class="content-card">
                <button class="btn secondary" onclick="showHub()" style="margin-bottom: 16px;">← Back to Hub</button>
                <h2>Leaderboard</h2>

                <div style="margin-bottom: 20px;">
                    <label class="modern-label">Quiz Code (optional - leave blank for your stats)</label>
                    <div style="display: flex; gap: 12px;">
                        <input type="text" id="leaderboard-code" class="modern-input" placeholder="Enter quiz code" maxlength="8" style="text-transform: uppercase; flex: 1;">
                        <button class="btn primary" onclick="loadLeaderboard()">Load</button>
                    </div>
                </div>

                <div id="leaderboard-content"></div>
            </div>
        </div>

        <!-- STATS MODULE -->
        <div id="stats-module" class="module-page hidden">
            <div class="content-card">
                <button class="btn secondary" onclick="showHub()" style="margin-bottom: 16px;">← Back to Hub</button>
                <h2>My Performance Stats</h2>
                <div id="stats-content"></div>
            </div>
        </div>

    </div>

    <script src="assets/js/assessarena.js"></script>
</body>
</html>
