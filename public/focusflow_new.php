

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
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: var(--font-body);
            color: var(--neutral-900);
        }

        .focusflow-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-8) var(--space-6);
        }

        .focusflow-header {
            text-align: center;
            margin-bottom: var(--space-10);
            color: white;
        }

        .focusflow-header h1 {
            font-family: var(--font-display);
            font-size: 3rem;
            font-weight: 800;
            margin: 0 0 var(--space-3);
            text-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .focusflow-header p {
            font-size: 1.125rem;
            opacity: 0.95;
            font-weight: 300;
        }

        .focusflow-tabs {
            background: white;
            border-radius: var(--radius-2xl);
            padding: var(--space-2);
            margin-bottom: var(--space-8);
            box-shadow: var(--shadow-xl);
            display: flex;
            gap: var(--space-2);
            overflow-x: auto;
        }

        .tab-button {
            flex: 1;
            min-width: 140px;
            padding: var(--space-4);
            background: transparent;
            border: none;
            border-radius: var(--radius-lg);
            font-family: var(--font-body);
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--neutral-600);
            cursor: pointer;
            transition: all var(--transition-base);
        }

        .tab-button:hover {
            color: var(--neutral-900);
            background: var(--neutral-100);
        }

        .tab-button.active {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-purple));
            color: white;
            box-shadow: var(--shadow-md);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        /* Pomodoro Timer Styles */
        .pomodoro-section {
            background: white;
            border-radius: var(--radius-2xl);
            padding: var(--space-10);
            box-shadow: var(--shadow-2xl);
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .timer-display {
            font-family: var(--font-display);
            font-size: 6rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: var(--space-6) 0;
            line-height: 1;
        }

        .timer-controls {
            display: flex;
            gap: var(--space-4);
            justify-content: center;
            margin-top: var(--space-8);
            flex-wrap: wrap;
        }

        .timer-presets {
            display: flex;
            gap: var(--space-3);
            justify-content: center;
            margin-top: var(--space-6);
        }

        .preset-btn {
            padding: var(--space-2) var(--space-4);
            background: var(--neutral-100);
            border: 2px solid var(--neutral-200);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .preset-btn:hover {
            background: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
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
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-purple));
            color: white;
            padding: var(--space-2);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            margin-bottom: var(--space-1);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/header_dashboard.php'; ?>

    <div class="focusflow-container">
        <div class="focusflow-header">
            <h1>🎯 FocusFlow</h1>
            <p>Your Personal Productivity Zone - Stay Focused, Get Things Done</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="focusflow-tabs">
            <button class="tab-button active" onclick="switchTab('pomodoro')">⏱️ Pomodoro</button>
            <button class="tab-button" onclick="switchTab('todo')">✅ To-Do List</button>
            <button class="tab-button" onclick="switchTab('calendar')">📅 Calendar</button>
            <button class="tab-button" onclick="switchTab('planner')">📚 Study Planner</button>
            <button class="tab-button" onclick="switchTab('progress')">📊 Progress</button>
        </div>

        <!-- Pomodoro Tab -->
        <div id="tab-pomodoro" class="tab-content active">
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

        <!-- Todo Tab -->
        <div id="tab-todo" class="tab-content">
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

        <!-- Calendar Tab -->
        <div id="tab-calendar" class="tab-content">
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

        <!-- Planner Tab -->
        <div id="tab-planner" class="tab-content">
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

        <!-- Progress Tab -->
        <div id="tab-progress" class="tab-content">
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
    <div id="todo-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;" onclick="if(event.target === this) closeTodoModal()">
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
