<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = 'FocusFlow - Productivity Zone';
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
        /* CSS Design Tokens - matching homepage */
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
        .focusflow-bg {
            position: fixed;
            inset: 0;
            z-index: -120;
            overflow: hidden;
            pointer-events: none;
            background-image: url('assets/images/focusflow-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .focusflow-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(2,6,23,0.45), rgba(2,6,23,0.25));
            z-index: 1;
        }

        .focusflow-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            backdrop-filter: blur(0.5px);
            z-index: 2;
        }

        .focusflow-container {
            min-height: calc(100vh - 120px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            position: relative;
            z-index: 3;
        }

        /* Hero glass card matching CollabSphere EXACTLY */
        .focusflow-header {
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

        .focusflow-header h1 {
            font-family: 'Poppins', var(--font-display);
            font-size: 1.9rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: #fff;
            line-height: 1.2;
            text-shadow: 0 4px 14px rgba(0,0,0,0.45);
        }

        .focusflow-header h1 .focus-user {
            color: #a8d8ff;
        }

        .focusflow-header p {
            max-width: 700px;
            margin: 0 auto 18px auto;
            font-size: 1.05rem;
            color: rgba(255,255,255,0.85);
        }

        /* Module Grid - matching dashboard style */
        .focusflow-modules-grid {
            width: 100%;
            max-width: 1000px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            align-items: stretch;
        }

        /* Module Cards - matching dashboard module-card style */
        .focusflow-module-card {
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

        .focusflow-module-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 22px 60px rgba(124,77,255,0.35);
            filter: brightness(1.12);
        }

        .focusflow-module-icon {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .focusflow-module-name {
            font-family: 'Poppins', var(--font-display);
            font-size: 1rem;
            font-weight: 700;
            text-align: center;
            margin-top: 4px;
        }

        .focusflow-module-desc {
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

        /* Pomodoro Timer - white card matching CollabSphere size */
        .pomodoro-section {
            width: 100%;
            max-width: 1000px;
            background: #fff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.45);
            text-align: center;
            margin: 0 auto var(--spacing-md) auto;
        }

        .pomodoro-section h2 {
            font-family: 'Poppins', var(--font-display);
            font-size: var(--h2-size);
            font-weight: 700;
            color: var(--neutral-900);
        }

        .timer-display {
            font-family: 'Poppins', var(--font-display);
            font-size: 6rem;
            font-weight: 800;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: var(--spacing-md) 0;
            line-height: 1;
        }

        .timer-controls {
            display: flex;
            gap: var(--spacing-sm);
            justify-content: center;
            margin-top: var(--spacing-md);
            flex-wrap: wrap;
        }

        .timer-presets {
            display: flex;
            gap: var(--spacing-sm);
            justify-content: center;
            margin-top: var(--spacing-md);
            margin-bottom: var(--spacing-md);
        }

        .preset-btn {
            padding: 10px 20px;
            background: var(--neutral-100);
            border: 2px solid var(--neutral-200);
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: 'Inter', var(--font-body);
            color: var(--neutral-700);
        }

        .preset-btn:hover {
            background: var(--accent-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(124,77,255,0.25);
        }

        /* Todo List Styles */
        .todo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-6);
        }

        .todo-item {
            background: white;
            border: 2px solid var(--neutral-200);
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            margin-bottom: var(--space-3);
            transition: all var(--transition-base);
        }

        .todo-item:hover {
            border-color: var(--primary-blue);
            box-shadow: var(--shadow-md);
        }

        .todo-item.completed {
            opacity: 0.6;
            background: var(--neutral-50);
        }

        .todo-item.completed .todo-title {
            text-decoration: line-through;
        }

        /* Calendar Styles */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: var(--space-2);
            margin-top: var(--space-6);
        }

        .calendar-day-header {
            text-align: center;
            font-weight: 700;
            color: var(--neutral-700);
            padding: var(--space-3);
            font-size: 0.875rem;
        }

        .calendar-day {
            aspect-ratio: 1;
            background: white;
            border: 2px solid var(--neutral-200);
            border-radius: var(--radius-md);
            padding: var(--space-2);
            cursor: pointer;
            transition: all var(--transition-fast);
            position: relative;
        }

        .calendar-day:hover {
            border-color: var(--primary-blue);
            transform: scale(1.05);
        }

        .calendar-day.today {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-purple));
            color: white;
            border-color: transparent;
        }

        .calendar-day.has-events::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background: var(--accent-orange);
            border-radius: 50%;
        }

        /* Study Planner Styles */
        .planner-grid {
            display: grid;
            grid-template-columns: auto repeat(7, 1fr);
            gap: 1px;
            background: var(--neutral-300);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        .planner-time,
        .planner-cell {
            background: white;
            padding: var(--space-4);
            min-height: 80px;
        }

        .planner-time {
            font-weight: 600;
            color: var(--neutral-600);
            display: flex;
            align-items: center;
        }

        .planner-event {
            background: var(--accent-gradient);
            color: white;
            padding: var(--space-2);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            margin-bottom: var(--space-1);
            cursor: pointer;
        }

        /* Responsive breakpoints matching dashboard */
        @media (max-width: 980px) {
            .focusflow-header h1 {
                font-size: 1.7rem;
            }

            .focusflow-header p {
                font-size: 1rem;
            }

            .focusflow-header,
            .pomodoro-section,
            .modern-card {
                padding: 26px;
            }

            .timer-display {
                font-size: 5rem;
            }

            /* Grid becomes 2 columns on tablets */
            .focusflow-modules-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
        }

        @media (max-width: 560px) {
            .focusflow-container {
                padding: 20px 12px;
            }

            .focusflow-header,
            .pomodoro-section,
            .modern-card {
                padding: 22px;
            }

            .focusflow-header h1 {
                font-size: 1.5rem;
            }

            .focusflow-header p {
                font-size: 0.95rem;
            }

            /* Grid becomes 1 column on mobile */
            .focusflow-modules-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .timer-display {
                font-size: 3.5rem;
            }

            .timer-controls {
                flex-direction: column;
                gap: var(--spacing-sm);
            }

            .timer-controls button {
                width: 100%;
            }
        }

        /* Stat cards with color gradients */
        .stat-card {
            position: relative;
            padding: 24px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--accent1), var(--accent2));
            color: white;
            box-shadow: 0 10px 25px rgba(124,77,255,0.25);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(124,77,255,0.35);
        }

        .stat-value {
            font-family: 'Poppins', var(--font-display);
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 8px 0;
            line-height: 1;
        }

        .stat-label {
            font-family: 'Inter', var(--font-body);
            font-size: 0.875rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        /* Premium badge pill style */
        .premium-badge {
            position: absolute;
            right: 10px;
            top: 10px;
            background: var(--accent-gradient);
            color: #fff;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Modern card variations matching CollabSphere */
        .modern-card {
            width: 100%;
            max-width: 1000px;
            background: #fff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.45);
            margin: 0 auto var(--spacing-md) auto;
        }

        .modern-card-title {
            font-family: 'Poppins', var(--font-display);
            font-size: var(--h2-size);
            font-weight: 700;
            color: var(--neutral-900);
        }

        /* Modern button system matching homepage */
        .modern-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-family: 'Inter', var(--font-body);
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.18s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .modern-btn-primary {
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 8px 20px rgba(124,77,255,0.3);
        }

        .modern-btn-primary:hover:not(:disabled) {
            box-shadow: 0 12px 30px rgba(124,77,255,0.4);
            transform: translateY(-2px);
        }

        .modern-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .modern-btn-secondary {
            background: var(--neutral-100);
            color: var(--neutral-700);
            border: 2px solid var(--neutral-200);
        }

        .modern-btn-secondary:hover:not(:disabled) {
            background: var(--neutral-200);
            border-color: var(--neutral-300);
        }

        .modern-btn-lg {
            padding: 14px 32px;
            font-size: 1rem;
        }

        /* Input styling improvements */
        .modern-input {
            width: 100%;
            padding: 12px 16px;
            font-family: 'Inter', var(--font-body);
            font-size: 0.9375rem;
            color: var(--neutral-900);
            background: white;
            border: 2px solid var(--neutral-300);
            border-radius: 10px;
            transition: all 0.15s ease;
        }

        .modern-input:focus {
            outline: none;
            border-color: var(--accent1);
            box-shadow: 0 0 0 3px rgba(124,77,255,0.1);
        }

        /* Grid system for stat cards */
        .modern-grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        @media (max-width: 767px) {
            .modern-grid-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/header_dashboard.php'; ?>

    <!-- Background gradient overlay -->
    <div class="focusflow-bg"></div>

    <div class="focusflow-container">
        <div class="focusflow-header">
            <h1>Good to see you, <span class="focus-user"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?></span></h1>
            <p>Your Personal Productivity Zone — Stay Focused, Get Things Done</p>
        </div>

        <!-- Module Cards Grid -->
        <div class="focusflow-modules-grid">
            <a href="javascript:void(0)" class="focusflow-module-card" onclick="openModule('pomodoro')">
                <div class="focusflow-module-icon" style="background: linear-gradient(135deg, #ff6b6b, #ff8787); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);">
                    🍅
                </div>
                <div class="focusflow-module-name">Pomodoro Timer</div>
                <div class="focusflow-module-desc">⏱️ Focus sessions</div>
            </a>

            <a href="javascript:void(0)" class="focusflow-module-card" onclick="openModule('todo')">
                <div class="focusflow-module-icon" style="background: linear-gradient(135deg, #ffb3ba, #ffc9ce); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; box-shadow: 0 8px 20px rgba(255, 179, 186, 0.4);">
                    📋
                </div>
                <div class="focusflow-module-name">My Tasks</div>
                <div class="focusflow-module-desc">✅ Task management</div>
            </a>

            <a href="javascript:void(0)" class="focusflow-module-card" onclick="openModule('calendar')">
                <div class="focusflow-module-icon" style="background: linear-gradient(135deg, #d4a574, #c99365); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; box-shadow: 0 8px 20px rgba(212, 165, 116, 0.4);">
                    📅
                </div>
                <div class="focusflow-module-name">Calendar</div>
                <div class="focusflow-module-desc">📅 Schedule view</div>
            </a>

            <a href="javascript:void(0)" class="focusflow-module-card" onclick="openModule('planner')">
                <div class="focusflow-module-icon" style="background: linear-gradient(135deg, #b8d4f1, #c5b3e6); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; box-shadow: 0 8px 20px rgba(184, 212, 241, 0.4);">
                    📓
                </div>
                <div class="focusflow-module-name">Study Planner</div>
                <div class="focusflow-module-desc">📚 Weekly plan</div>
            </a>

            <a href="javascript:void(0)" class="focusflow-module-card" onclick="openModule('progress')">
                <div class="focusflow-module-icon" style="background: linear-gradient(135deg, #ffd93d, #ffb347); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; box-shadow: 0 8px 20px rgba(255, 217, 61, 0.4);">
                    📊
                </div>
                <div class="focusflow-module-name">Progress</div>
                <div class="focusflow-module-desc">📊 Your stats</div>
            </a>
        </div>

        <!-- Module Content Pages (initially hidden) -->
        <div id="module-pomodoro" class="module-page" style="display: none;">
            <button class="modern-btn modern-btn-secondary" onclick="closeModule()" style="margin-bottom: 20px;">
                ← Back to Modules
            </button>
            <div class="pomodoro-section">
                <h2 style="margin-top:0;">Pomodoro Timer</h2>
                <p style="color: var(--neutral-500);">Stay focused with the Pomodoro Technique</p>

                <div class="timer-presets">
                    <button class="preset-btn" onclick="setTimer(25)">25 min</button>
                    <button class="preset-btn" onclick="setTimer(15)">15 min</button>
                    <button class="preset-btn" onclick="setTimer(5)">5 min</button>
                    <button class="preset-btn" onclick="setTimer(1)">1 min</button>
                </div>

                <div class="timer-display" id="timer-display">25:00</div>

                <div class="modern-input-group" style="max-width: 300px; margin: 0 auto;">
                    <label class="modern-label">Custom Duration (minutes)</label>
                    <input type="number" id="custom-minutes" class="modern-input" placeholder="25" min="1" max="120">
                </div>

                <div id="timer-status" style="color: var(--neutral-500); margin: var(--space-4) 0;">Ready to start</div>

                <!-- Notification Permission Banner -->
                <div id="notification-banner" style="display: none; background: rgba(255, 193, 7, 0.1); border: 2px solid #ffc107; border-radius: 12px; padding: 12px 16px; margin: 16px 0; text-align: center;">
                    <p style="margin: 0 0 8px 0; color: #f57c00; font-weight: 600;">🔔 Enable notifications to get alerted when timer completes</p>
                    <button class="modern-btn modern-btn-secondary" onclick="requestNotificationPermission()" style="font-size: 0.875rem; padding: 8px 16px;">
                        Enable Notifications
                    </button>
                </div>

                <div class="timer-controls">
                    <button class="modern-btn modern-btn-primary modern-btn-lg" id="start-btn" onclick="startTimer()">
                        ▶️ Start
                    </button>
                    <button class="modern-btn modern-btn-secondary modern-btn-lg" id="pause-btn" onclick="pauseTimer()" disabled>
                        ⏸️ Pause
                    </button>
                    <button class="modern-btn modern-btn-secondary modern-btn-lg" id="reset-btn" onclick="resetTimer()">
                        🔄 Reset
                    </button>
                </div>

                <div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 2px solid var(--neutral-100);">
                    <h3 style="margin-bottom: var(--space-4);">Session Stats Today</h3>
                    <div class="modern-grid modern-grid-3">
                        <div class="stat-card">
                            <div class="stat-value" id="sessions-today">0</div>
                            <div class="stat-label">Sessions</div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(135deg, var(--success), var(--primary-teal));">
                            <div class="stat-value" id="minutes-today">0</div>
                            <div class="stat-label">Minutes</div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(135deg, var(--accent-orange), var(--accent-pink));">
                            <div class="stat-value" id="streak-count">0</div>
                            <div class="stat-label">Day Streak</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Todo Module -->
        <div id="module-todo" class="module-page" style="display: none;">
            <button class="modern-btn modern-btn-secondary" onclick="closeModule()" style="margin-bottom: 20px;">
                ← Back to Modules
            </button>
            <div class="modern-card">
                <div class="todo-header">
                    <div>
                        <h2 class="modern-card-title">My Tasks</h2>
                        <p class="modern-card-subtitle">Organize your work and track progress</p>
                    </div>
                    <button class="modern-btn modern-btn-primary" onclick="showAddTodoModal()">+ Add Task</button>
                </div>

                <div class="modern-tabs">
                    <button class="modern-tab active" onclick="filterTodos('all')">All</button>
                    <button class="modern-tab" onclick="filterTodos('pending')">Pending</button>
                    <button class="modern-tab" onclick="filterTodos('in_progress')">In Progress</button>
                    <button class="modern-tab" onclick="filterTodos('completed')">Completed</button>
                </div>

                <div id="todo-list">
                    <!-- Todos loaded via JavaScript -->
                </div>
            </div>
        </div>

        <!-- Calendar Module -->
        <div id="module-calendar" class="module-page" style="display: none;">
            <button class="modern-btn modern-btn-secondary" onclick="closeModule()" style="margin-bottom: 20px;">
                ← Back to Modules
            </button>
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="flex-between">
                        <div>
                            <h2 class="modern-card-title" id="calendar-month-year">December 2025</h2>
                            <p class="modern-card-subtitle">View your schedule and deadlines</p>
                        </div>
                        <div class="flex-gap-2">
                            <button class="modern-btn modern-btn-secondary" onclick="changeMonth(-1)">← Prev</button>
                            <button class="modern-btn modern-btn-secondary" onclick="changeMonth(0)">Today</button>
                            <button class="modern-btn modern-btn-secondary" onclick="changeMonth(1)">Next →</button>
                        </div>
                    </div>
                </div>

                <div class="calendar-grid">
                    <div class="calendar-day-header">Sun</div>
                    <div class="calendar-day-header">Mon</div>
                    <div class="calendar-day-header">Tue</div>
                    <div class="calendar-day-header">Wed</div>
                    <div class="calendar-day-header">Thu</div>
                    <div class="calendar-day-header">Fri</div>
                    <div class="calendar-day-header">Sat</div>
                    <div id="calendar-days">
                        <!-- Calendar days loaded via JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Planner Module -->
        <div id="module-planner" class="module-page" style="display: none;">
            <button class="modern-btn modern-btn-secondary" onclick="closeModule()" style="margin-bottom: 20px;">
                ← Back to Modules
            </button>
            <div class="modern-card">
                <div class="todo-header">
                    <div>
                        <h2 class="modern-card-title">Weekly Study Planner</h2>
                        <p class="modern-card-subtitle">Create and manage your study schedule</p>
                    </div>
                    <button class="modern-btn modern-btn-primary" onclick="showAddPlanModal()">+ Add Study Block</button>
                </div>

                <div id="weekly-planner">
                    <!-- Planner loaded via JavaScript -->
                </div>
            </div>
        </div>

        <!-- Progress Module -->
        <div id="module-progress" class="module-page" style="display: none;">
            <button class="modern-btn modern-btn-secondary" onclick="closeModule()" style="margin-bottom: 20px;">
                ← Back to Modules
            </button>
            <div class="modern-card mb-6">
                <h2 class="modern-card-title">Progress Overview</h2>
                <p class="modern-card-subtitle mb-6">Track your productivity metrics</p>

                <div class="modern-grid modern-grid-4 mb-6">
                    <div class="stat-card">
                        <div class="stat-value" id="total-tasks">0</div>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, var(--success), var(--primary-teal));">
                        <div class="stat-value" id="completed-tasks">0</div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, var(--accent-orange), var(--accent-pink));">
                        <div class="stat-value" id="completion-rate">0%</div>
                        <div class="stat-label">Completion Rate</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));">
                        <div class="stat-value" id="total-pomodoros">0</div>
                        <div class="stat-label">Pomodoros</div>
                    </div>
                </div>

                <div class="modern-grid md-grid-2">
                    <div>
                        <h3 class="mb-4">Tasks This Week</h3>
                        <canvas id="tasks-chart" style="max-height: 300px;"></canvas>
                    </div>
                    <div>
                        <h3 class="mb-4">Pomodoro Sessions</h3>
                        <canvas id="pomodoro-chart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Todo Modal -->
    <div id="todo-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;" onclick="if(event.target === this) closeTodoModal()">
        <div class="modern-card" style="width:90%; max-width:600px; max-height:90vh; overflow-y:auto;" onclick="event.stopPropagation()">
            <h2 class="modern-card-title">Add New Task</h2>
            <form id="todo-form" onsubmit="addTodo(event)">
                <div class="modern-input-group">
                    <label class="modern-label">Task Title*</label>
                    <input type="text" id="todo-title" class="modern-input" required>
                </div>
                <div class="modern-input-group">
                    <label class="modern-label">Description</label>
                    <textarea id="todo-description" class="modern-textarea"></textarea>
                </div>
                <div class="modern-grid md-grid-2">
                    <div class="modern-input-group">
                        <label class="modern-label">Due Date</label>
                        <input type="date" id="todo-due-date" class="modern-input">
                    </div>
                    <div class="modern-input-group">
                        <label class="modern-label">Priority</label>
                        <select id="todo-priority" class="modern-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>
                <div class="flex-gap-4" style="justify-content: flex-end;">
                    <button type="button" class="modern-btn modern-btn-secondary" onclick="closeTodoModal()">Cancel</button>
                    <button type="submit" class="modern-btn modern-btn-primary">Add Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Plan Modal -->
    <div id="plan-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;" onclick="if(event.target === this) closePlanModal()">
        <div class="modern-card" style="width:90%; max-width:600px;" onclick="event.stopPropagation()">
            <h2 class="modern-card-title">Add Study Block</h2>
            <form id="plan-form" onsubmit="addPlan(event)">
                <div class="modern-input-group">
                    <label class="modern-label">Subject/Title*</label>
                    <input type="text" id="plan-title" class="modern-input" required>
                </div>
                <div class="modern-input-group">
                    <label class="modern-label">Description</label>
                    <textarea id="plan-description" class="modern-textarea" rows="3"></textarea>
                </div>
                <div class="modern-grid md-grid-2">
                    <div class="modern-input-group">
                        <label class="modern-label">Day*</label>
                        <select id="plan-day" class="modern-select" required>
                            <option value="0">Sunday</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                        </select>
                    </div>
                    <div class="modern-input-group">
                        <label class="modern-label">Subject</label>
                        <input type="text" id="plan-subject" class="modern-input" placeholder="e.g., Mathematics">
                    </div>
                </div>
                <div class="modern-grid md-grid-2">
                    <div class="modern-input-group">
                        <label class="modern-label">Start Time*</label>
                        <input type="time" id="plan-start-time" class="modern-input" required>
                    </div>
                    <div class="modern-input-group">
                        <label class="modern-label">End Time*</label>
                        <input type="time" id="plan-end-time" class="modern-input" required>
                    </div>
                </div>
                <div class="flex-gap-4" style="justify-content: flex-end;">
                    <button type="button" class="modern-btn modern-btn-secondary" onclick="closePlanModal()">Cancel</button>
                    <button type="submit" class="modern-btn modern-btn-primary">Add to Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="assets/js/focusflow_complete.js"></script>
</body>
</html>
