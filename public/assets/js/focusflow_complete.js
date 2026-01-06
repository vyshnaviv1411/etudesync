// ================================================
// FocusFlow Complete - All Features
// ================================================

// ================================================
// DATE VALIDATION UTILITIES (CENTRALIZED)
// ================================================

/**
 * Get today's date at start of day (00:00:00) for accurate comparison
 * @returns {Date} Today's date with time set to 00:00:00
 */
function getToday() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return today;
}

/**
 * Get today's date in YYYY-MM-DD format
 * @returns {string} Today's date as YYYY-MM-DD
 */
function getTodayString() {
    const today = new Date();
    return today.toISOString().split('T')[0];
}

/**
 * Check if a date is in the past (before today)
 * @param {string|Date} dateInput - Date to check (YYYY-MM-DD string or Date object)
 * @returns {boolean} True if date is in the past, false otherwise
 */
function isPastDate(dateInput) {
    if (!dateInput) return false;

    const today = getToday();
    const inputDate = new Date(dateInput);
    inputDate.setHours(0, 0, 0, 0);

    return inputDate < today;
}

/**
 * Validate date and return error message if invalid
 * @param {string|Date} dateInput - Date to validate
 * @param {string} fieldName - Name of field for error message
 * @returns {string|null} Error message if invalid, null if valid
 */
function validateDate(dateInput, fieldName = 'date') {
    if (!dateInput) return null; // Optional dates are allowed

    if (isPastDate(dateInput)) {
        return `You can't select a past ${fieldName}. Please choose today or a future date.`;
    }

    return null;
}

/**
 * Show error notification to user
 * @param {string} message - Error message to display
 */
function showDateError(message) {
    // Create or get error container
    let errorDiv = document.getElementById('date-validation-error');

    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'date-validation-error';
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(238, 90, 82, 0.4);
            z-index: 10000;
            font-weight: 600;
            max-width: 400px;
            animation: slideInRight 0.3s ease;
        `;
        document.body.appendChild(errorDiv);
    }

    errorDiv.textContent = message;
    errorDiv.style.display = 'block';

    // Auto-hide after 4 seconds
    setTimeout(() => {
        errorDiv.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 300);
    }, 4000);
}

/**
 * Initialize date input with restrictions
 * @param {string} inputId - ID of date input element
 */
function initializeDateInput(inputId) {
    const input = document.getElementById(inputId);
    if (input && input.type === 'date') {
        // Set minimum date to today
        input.min = getTodayString();

        // Add change listener for additional validation
        input.addEventListener('change', function() {
            const error = validateDate(this.value, 'date');
            if (error) {
                showDateError(error);
                this.value = ''; // Clear invalid date
            }
        });
    }
}

// Global State
let timerInterval = null;
let timeRemaining = 25 * 60; // seconds
let timerRunning = false;
let currentFilter = 'all';
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

// Calendar Events State
let calendarEvents = {}; // { "2026-01-06": [{ id, title, type }] }
let nextEventId = 1;

// ================================================
// NOTIFICATION PERMISSION CHECK
// ================================================

function checkNotificationPermission() {
    if ('Notification' in window) {
        const banner = document.getElementById('notification-banner');
        if (Notification.permission === 'default') {
            // Show banner to request permission
            if (banner) banner.style.display = 'block';
        } else if (Notification.permission === 'granted') {
            // Hide banner if already granted
            if (banner) banner.style.display = 'none';
            console.log('✅ Notifications already enabled');
        } else {
            // Permission denied
            console.log('❌ Notification permission denied');
        }
    }
}

async function requestNotificationPermission() {
    if ('Notification' in window) {
        try {
            const permission = await Notification.requestPermission();
            const banner = document.getElementById('notification-banner');

            if (permission === 'granted') {
                if (banner) banner.style.display = 'none';
                console.log('✅ Notification permission granted!');

                // Show a test notification
                new Notification('🎉 Notifications Enabled!', {
                    body: 'You\'ll now get alerts when your Pomodoro timer completes.',
                    icon: 'assets/images/logo.jpg',
                    badge: 'assets/images/logo.jpg'
                });
            } else {
                console.log('❌ Notification permission denied');
                alert('Please enable notifications in your browser settings to get timer alerts.');
            }
        } catch (error) {
            console.error('Error requesting notification permission:', error);
        }
    } else {
        alert('Your browser does not support notifications.');
    }
}

// Check permission on page load
document.addEventListener('DOMContentLoaded', function() {
    checkNotificationPermission();
});

// ================================================
// MODULE NAVIGATION
// ================================================

function openModule(moduleName) {
    // Hide the module grid
    const grid = document.querySelector('.focusflow-modules-grid');
    if (grid) grid.style.display = 'none';

    // Hide all module pages
    document.querySelectorAll('.module-page').forEach(page => {
        page.style.display = 'none';
    });

    // Show selected module
    const moduleElement = document.getElementById(`module-${moduleName}`);
    if (moduleElement) {
        moduleElement.style.display = 'block';
    }

    // Load data for specific modules
    if (moduleName === 'pomodoro') {
        loadPomodoroStats();
    } else if (moduleName === 'todo') {
        loadTodos();
    } else if (moduleName === 'calendar') {
        loadEventsFromStorage(); // Load events first
        renderCalendar();
    } else if (moduleName === 'planner') {
        loadPlanner();
    } else if (moduleName === 'progress') {
        loadProgress();
    }

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function closeModule() {
    // Hide all module pages
    document.querySelectorAll('.module-page').forEach(page => {
        page.style.display = 'none';
    });

    // Show the module grid
    const grid = document.querySelector('.focusflow-modules-grid');
    if (grid) grid.style.display = 'grid';

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ================================================
// POMODORO TIMER
// ================================================

function setTimer(minutes) {
    if (timerRunning) return;
    timeRemaining = minutes * 60;
    updateTimerDisplay();
    document.getElementById('custom-minutes').value = minutes;
}

function updateTimerDisplay() {
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    document.getElementById('timer-display').textContent =
        `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function startTimer() {
    const customMin = parseInt(document.getElementById('custom-minutes').value);
    if (customMin && !timerRunning) {
        timeRemaining = customMin * 60;
    }

    timerRunning = true;
    document.getElementById('start-btn').disabled = true;
    document.getElementById('pause-btn').disabled = false;
    document.getElementById('timer-status').textContent = 'Focus time! 🎯';
    document.getElementById('timer-status').style.color = 'var(--success)';

    // Save to localStorage
    const startData = {
        startTime: Date.now(),
        duration: timeRemaining,
        running: true
    };
    localStorage.setItem('pomodoroState', JSON.stringify(startData));

    timerInterval = setInterval(() => {
        timeRemaining--;
        updateTimerDisplay();

        if (timeRemaining <= 0) {
            completeTimer();
        }

        // Update localStorage
        startData.duration = timeRemaining;
        localStorage.setItem('pomodoroState', JSON.stringify(startData));
    }, 1000);
}

function pauseTimer() {
    timerRunning = false;
    clearInterval(timerInterval);
    document.getElementById('start-btn').disabled = false;
    document.getElementById('pause-btn').disabled = true;
    document.getElementById('timer-status').textContent = 'Paused ⏸️';
    document.getElementById('timer-status').style.color = 'var(--warning)';

    localStorage.removeItem('pomodoroState');
}

function resetTimer() {
    pauseTimer();
    timeRemaining = 25 * 60;
    updateTimerDisplay();
    document.getElementById('timer-status').textContent = 'Ready to start';
    document.getElementById('timer-status').style.color = 'var(--neutral-500)';
    document.getElementById('custom-minutes').value = '';
}

async function completeTimer() {
    clearInterval(timerInterval);
    timerRunning = false;
    document.getElementById('timer-status').textContent = 'Session complete! 🎉';
    document.getElementById('timer-status').style.color = 'var(--success)';
    document.getElementById('start-btn').disabled = false;
    document.getElementById('pause-btn').disabled = true;

    // Show multiple types of notifications
    showTimerCompleteNotifications();

    // Save session to database
    const duration = parseInt(document.getElementById('custom-minutes').value) || 25;
    try {
        const response = await fetch('api/focusflow/save_pomodoro.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ duration_minutes: duration, completed: 1 })
        });

        if (response.ok) {
            loadPomodoroStats();
        }
    } catch (error) {
        console.error('Error saving session:', error);
    }

    localStorage.removeItem('pomodoroState');

    // Don't auto-reset, let user see completion message
    setTimeout(() => {
        if (!timerRunning) {
            resetTimer();
        }
    }, 5000);
}

function showTimerCompleteNotifications() {
    console.log('🔔 Showing timer complete notifications...');

    // 1. Browser notification
    if ('Notification' in window) {
        console.log('Notification permission:', Notification.permission);
        if (Notification.permission === 'granted') {
            try {
                const notification = new Notification('🎉 Pomodoro Complete!', {
                    body: 'Great job! Time for a well-deserved break.',
                    icon: 'assets/images/logo.jpg',
                    badge: 'assets/images/logo.jpg',
                    vibrate: [200, 100, 200],
                    requireInteraction: true,
                    tag: 'pomodoro-complete'
                });
                console.log('✅ Browser notification sent');

                // Close notification after 10 seconds
                setTimeout(() => notification.close(), 10000);
            } catch (error) {
                console.error('❌ Browser notification failed:', error);
            }
        } else if (Notification.permission !== 'denied') {
            console.log('⚠️ Requesting notification permission...');
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    new Notification('🎉 Pomodoro Complete!', {
                        body: 'Great job! Time for a well-deserved break.',
                        icon: 'assets/images/logo.jpg'
                    });
                }
            });
        } else {
            console.log('❌ Notification permission denied');
        }
    } else {
        console.log('❌ Browser does not support notifications');
    }

    // 2. Audio notification (system beep + custom sound)
    playCompletionSound();

    // 3. Visual notification modal
    showCompletionModal();

    // 4. Page title notification (for users in other tabs)
    startTitleNotification();
}

function playCompletionSound() {
    // Create audio context for beep sound
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();

        // Play three ascending beeps
        const frequencies = [523.25, 659.25, 783.99]; // C, E, G notes

        frequencies.forEach((freq, index) => {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = freq;
            oscillator.type = 'sine';

            const startTime = audioContext.currentTime + (index * 0.25);
            const duration = 0.2;

            gainNode.gain.setValueAtTime(0.4, startTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + duration);

            oscillator.start(startTime);
            oscillator.stop(startTime + duration);
        });

        console.log('✅ Audio notification played (3 beeps)');
    } catch (error) {
        console.error('❌ Audio notification failed:', error);

        // Fallback: Try using the built-in beep API
        try {
            if (window.navigator && window.navigator.vibrate) {
                window.navigator.vibrate([200, 100, 200, 100, 200]);
                console.log('✅ Vibration fallback triggered');
            }
        } catch (vibrateError) {
            console.error('❌ Vibration fallback failed:', vibrateError);
        }
    }
}

function showCompletionModal() {
    // Remove any existing modal first
    const existingModal = document.getElementById('completion-modal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal overlay
    const modal = document.createElement('div');
    modal.id = 'completion-modal';
    modal.style.cssText = `
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        animation: fadeIn 0.3s ease-out;
    `;

    modal.innerHTML = `
        <div style="
            background: linear-gradient(135deg, #7c4dff, #47d7d3);
            padding: 48px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.6);
            max-width: 500px;
            animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        ">
            <div style="font-size: 80px; margin-bottom: 20px; animation: bounce 1s infinite;">🎉</div>
            <h2 style="color: white; font-size: 2.5rem; margin: 0 0 16px 0; font-weight: 800; font-family: 'Poppins', sans-serif;">
                Timer Complete!
            </h2>
            <p style="color: rgba(255,255,255,0.95); font-size: 1.2rem; margin: 0 0 32px 0; font-family: 'Inter', sans-serif;">
                Great work! Time for a well-deserved break.
            </p>
            <button onclick="closeCompletionModal()" style="
                background: white;
                color: #7c4dff;
                border: none;
                padding: 14px 32px;
                border-radius: 12px;
                font-size: 1rem;
                font-weight: 700;
                cursor: pointer;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
                transition: transform 0.2s;
                font-family: 'Inter', sans-serif;
            " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 30px rgba(0, 0, 0, 0.4)'"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(0, 0, 0, 0.3)'">
                Awesome! 👍
            </button>
        </div>
    `;

    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
    `;
    document.head.appendChild(style);

    document.body.appendChild(modal);
    console.log('✅ Completion modal displayed');

    // Auto-close after 15 seconds
    setTimeout(() => {
        if (document.getElementById('completion-modal')) {
            closeCompletionModal();
        }
    }, 15000);
}

window.closeCompletionModal = function() {
    const modal = document.getElementById('completion-modal');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => modal.remove(), 300);
    }
};

function startTitleNotification() {
    const originalTitle = document.title;
    let count = 0;

    const titleInterval = setInterval(() => {
        document.title = count % 2 === 0 ? '🎉 Timer Complete!' : originalTitle;
        count++;

        if (count > 20) { // Stop after 10 blinks
            clearInterval(titleInterval);
            document.title = originalTitle;
        }
    }, 1000);

    // Stop when user focuses the tab
    window.addEventListener('focus', function stopTitleBlink() {
        clearInterval(titleInterval);
        document.title = originalTitle;
        window.removeEventListener('focus', stopTitleBlink);
    }, { once: true });
}

async function loadPomodoroStats() {
    try {
        const response = await fetch('api/focusflow/pomodoro_stats.php');
        const data = await response.json();

        if (data.success) {
            document.getElementById('sessions-today').textContent = data.sessions_today || 0;
            document.getElementById('minutes-today').textContent = data.minutes_today || 0;
            document.getElementById('streak-count').textContent = data.streak || 0;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

// Restore timer state on page load
window.addEventListener('load', () => {
    const savedState = localStorage.getItem('pomodoroState');
    if (savedState) {
        const state = JSON.parse(savedState);
        const elapsed = Math.floor((Date.now() - state.startTime) / 1000);
        timeRemaining = Math.max(0, state.duration - elapsed);

        if (timeRemaining > 0 && state.running) {
            updateTimerDisplay();
            // Ask if user wants to continue
            if (confirm('You have an active timer. Continue?')) {
                startTimer();
            } else {
                resetTimer();
            }
        } else {
            resetTimer();
        }
    }

    loadPomodoroStats();
});

// ================================================
// TODO LIST
// ================================================

async function loadTodos() {
    try {
        const response = await fetch(`api/focusflow/todo_list.php?filter=${currentFilter}`);
        const data = await response.json();

        const todoList = document.getElementById('todo-list');

        if (!data.success || data.todos.length === 0) {
            todoList.innerHTML = `
                <div style="text-align:center; padding:var(--space-10); color:var(--neutral-400);">
                    <p style="font-size:3rem; margin:0;">📝</p>
                    <p style="margin-top:var(--space-4);">No tasks found. Add your first task!</p>
                </div>
            `;
            return;
        }

        todoList.innerHTML = data.todos.map(todo => `
            <div class="todo-item ${todo.status === 'completed' ? 'completed' : ''}" data-id="${todo.id}">
                <div class="flex-between">
                    <div class="flex-gap-4" style="align-items:flex-start; flex:1;">
                        <input type="checkbox"
                            ${todo.status === 'completed' ? 'checked' : ''}
                            onchange="toggleTodo(${todo.id}, this.checked)"
                            style="width:20px; height:20px; cursor:pointer;">
                        <div style="flex:1;">
                            <h3 class="todo-title" style="margin:0 0 var(--space-2); font-size:1rem;">
                                ${escapeHtml(todo.title)}
                            </h3>
                            ${todo.description ? `<p style="color:var(--neutral-600); font-size:0.875rem; margin:0 0 var(--space-2);">${escapeHtml(todo.description)}</p>` : ''}
                            <div class="flex-gap-2" style="flex-wrap:wrap;">
                                ${todo.due_date ? `<span class="modern-badge modern-badge-info">📅 ${formatDate(todo.due_date)}</span>` : ''}
                                <span class="modern-badge modern-badge-${getPriorityColor(todo.priority)}">${todo.priority}</span>
                                <span class="modern-badge modern-badge-${getStatusColor(todo.status)}">${todo.status.replace('_', ' ')}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex-gap-2">
                        ${todo.status !== 'completed' ? `
                            <button class="modern-btn modern-btn-sm modern-btn-secondary" onclick="updateTodoStatus(${todo.id}, 'in_progress')">
                                In Progress
                            </button>
                        ` : ''}
                        <button class="modern-btn modern-btn-sm modern-btn-danger" onclick="deleteTodo(${todo.id})">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading todos:', error);
    }
}

function filterTodos(filter) {
    currentFilter = filter;

    // Update active tab
    document.querySelectorAll('.modern-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');

    loadTodos();
}

function showAddTodoModal() {
    document.getElementById('todo-modal').style.display = 'flex';
    document.getElementById('todo-form').reset();

    // Initialize date input with restrictions (prevent past dates)
    initializeDateInput('todo-due-date');
}

function closeTodoModal() {
    document.getElementById('todo-modal').style.display = 'none';
}

async function addTodo(event) {
    event.preventDefault();

    const title = document.getElementById('todo-title').value;
    const description = document.getElementById('todo-description').value;
    const dueDate = document.getElementById('todo-due-date').value || null;
    const priority = document.getElementById('todo-priority').value;

    // CRITICAL: Validate due date before submission
    if (dueDate) {
        const dateError = validateDate(dueDate, 'due date');
        if (dateError) {
            showDateError(dateError);
            return; // Block submission
        }
    }

    const formData = {
        title: title,
        description: description,
        due_date: dueDate,
        priority: priority
    };

    try {
        const response = await fetch('api/focusflow/todo_add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            closeTodoModal();
            loadTodos();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error adding todo:', error);
        alert('Failed to add task');
    }
}

async function toggleTodo(id, completed) {
    const status = completed ? 'completed' : 'pending';
    await updateTodoStatus(id, status);
}

async function updateTodoStatus(id, status) {
    try {
        const response = await fetch('api/focusflow/todo_update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status })
        });

        const result = await response.json();

        if (result.success) {
            loadTodos();
        }
    } catch (error) {
        console.error('Error updating todo:', error);
    }
}

async function deleteTodo(id) {
    if (!confirm('Delete this task?')) return;

    try {
        const response = await fetch('api/focusflow/todo_delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const result = await response.json();

        if (result.success) {
            loadTodos();
        }
    } catch (error) {
        console.error('Error deleting todo:', error);
    }
}

// ================================================
// CALENDAR - COMPLETE IMPLEMENTATION
// ================================================

/**
 * Generate calendar matrix (2D array of weeks)
 * This is the core data structure for the calendar
 */
function generateCalendarMatrix(year, month) {
    console.log('🗓️ Generating calendar matrix for:', month + 1, '/', year);

    const firstDay = new Date(year, month, 1).getDay(); // 0=Sunday, 6=Saturday
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Normalize for comparison

    console.log('📊 Calendar data:', {
        firstDay: firstDay,
        daysInMonth: daysInMonth,
        startDay: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][firstDay]
    });

    const weeks = [];
    let currentWeek = [];

    // Fill empty cells before month starts (for alignment)
    for (let i = 0; i < firstDay; i++) {
        currentWeek.push(null);
    }

    // Fill in the actual days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        date.setHours(0, 0, 0, 0);

        const isToday = date.getTime() === today.getTime();
        const isPast = date < today;

        currentWeek.push({
            day: day,
            date: date,
            dateStr: `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`,
            isToday: isToday,
            isPast: isPast,
            isCurrentMonth: true
        });

        // If week is complete (7 days), add to weeks array and start new week
        if (currentWeek.length === 7) {
            weeks.push(currentWeek);
            currentWeek = [];
        }
    }

    // Fill remaining cells to complete the last week
    if (currentWeek.length > 0) {
        while (currentWeek.length < 7) {
            currentWeek.push(null);
        }
        weeks.push(currentWeek);
    }

    console.log('✅ Matrix generated:', weeks.length, 'weeks,', firstDay + daysInMonth, 'total cells');
    return weeks;
}

/**
 * Render calendar from matrix
 * Builds HTML for the entire calendar grid
 */
function renderCalendarFromMatrix(weeks, eventsData) {
    console.log('🎨 Rendering calendar HTML from matrix...');

    let html = '';
    let cellCount = 0;

    weeks.forEach((week, weekIndex) => {
        week.forEach((cell, dayIndex) => {
            cellCount++;

            if (!cell) {
                // Empty cell (before month starts or after month ends)
                html += '<div class="calendar-day empty"></div>';
            } else {
                // Actual day cell
                const classes = ['calendar-day'];
                if (cell.isToday) classes.push('today');
                if (cell.isPast) classes.push('past');

                const events = eventsData[cell.dateStr] || [];

                html += `
                    <div class="${classes.join(' ')}"
                         data-date="${cell.dateStr}"
                         data-is-past="${cell.isPast}"
                         onclick="handleDateClick('${cell.dateStr}', ${cell.isPast}, event)">
                        <div class="calendar-date-number">${cell.day}</div>
                        <div class="calendar-events" onclick="event.stopPropagation()">
                            ${renderDayEvents(events, cell.dateStr)}
                        </div>
                    </div>
                `;
            }
        });
    });

    console.log('✅ HTML generated for', cellCount, 'cells');
    return html;
}

/**
 * Main calendar rendering function
 * This is called when opening the calendar or changing months
 */
async function renderCalendar() {
    console.log('🚀 ========== RENDER CALENDAR START ==========');
    console.log('📅 Current state: Month', currentMonth, 'Year', currentYear);

    try {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];

        // STEP 1: Update month header
        const headerElement = document.getElementById('calendar-month-year');
        if (headerElement) {
            headerElement.textContent = `${monthNames[currentMonth]} ${currentYear}`;
            console.log('✅ Header updated:', headerElement.textContent);
        } else {
            console.error('❌ Header element not found!');
        }

        // STEP 2: Generate calendar matrix (THIS IS THE CORE LOGIC)
        const weeks = generateCalendarMatrix(currentYear, currentMonth);

        if (!weeks || weeks.length === 0) {
            console.error('❌ CRITICAL: Calendar matrix is empty!');
            return;
        }

        // STEP 3: Fetch todos with due dates
        let todosData = {};
        try {
            const response = await fetch(`api/focusflow/calendar_events.php?month=${currentMonth + 1}&year=${currentYear}`);
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.events) {
                    todosData = data.events;
                    console.log('📌 Todos loaded for', Object.keys(todosData).length, 'dates');
                }
            }
        } catch (error) {
            console.warn('⚠️ Todos not loaded (calendar will still render):', error.message);
        }

        // STEP 4: Merge calendar events with todos
        const mergedEvents = mergeEventsWithTodos(todosData, calendarEvents);
        console.log('🔀 Merged events for', Object.keys(mergedEvents).length, 'dates');

        // STEP 5: Render HTML from matrix
        const calendarHTML = renderCalendarFromMatrix(weeks, mergedEvents);

        if (!calendarHTML) {
            console.error('❌ CRITICAL: Calendar HTML is empty!');
            return;
        }

        // STEP 5: Insert into DOM
        const calendarGrid = document.getElementById('calendar-grid');
        if (!calendarGrid) {
            console.error('❌ CRITICAL: calendar-grid element not found in DOM!');
            return;
        }

        console.log('🔄 Clearing existing calendar cells...');
        const existingDays = calendarGrid.querySelectorAll('.calendar-day');
        existingDays.forEach(day => day.remove());
        console.log('✅ Removed', existingDays.length, 'existing cells');

        console.log('📝 Inserting calendar HTML into DOM...');
        calendarGrid.insertAdjacentHTML('beforeend', calendarHTML);

        const newDays = calendarGrid.querySelectorAll('.calendar-day');
        console.log('✅ Calendar inserted:', newDays.length, 'cells now in DOM');

        console.log('🎉 ========== RENDER CALENDAR COMPLETE ==========');

    } catch (error) {
        console.error('💥 CRITICAL ERROR in renderCalendar():', error);
        console.error('📍 Stack trace:', error.stack);
        alert('Calendar rendering failed. Check console for details.');
    }
}

/**
 * Merge calendar events with todos
 */
function mergeEventsWithTodos(todosData, calendarEventsData) {
    const merged = {};

    // Add todos
    Object.keys(todosData).forEach(dateStr => {
        merged[dateStr] = todosData[dateStr].map(todo => ({
            ...todo,
            sourceType: 'todo'
        }));
    });

    // Add calendar events
    Object.keys(calendarEventsData).forEach(dateStr => {
        if (!merged[dateStr]) {
            merged[dateStr] = [];
        }
        calendarEventsData[dateStr].forEach(event => {
            merged[dateStr].push({
                ...event,
                dateStr: dateStr, // Include dateStr for editing
                sourceType: 'calendar'
            });
        });
    });

    return merged;
}

function renderDayEvents(events, dateStr) {
    try {
        if (!events || !Array.isArray(events) || events.length === 0) {
            return '';
        }

        const MAX_VISIBLE_EVENTS = 2; // Show max 2 events per cell
        let html = '';

        // Show first N events
        const visibleEvents = events.slice(0, MAX_VISIBLE_EVENTS);
        visibleEvents.forEach(event => {
            if (!event || !event.title) return;

            const eventTitle = String(event.title).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            let eventClass = 'calendar-event';
            let clickHandler = '';
            let deleteButton = '';

            if (event.sourceType === 'todo') {
                // Todo event styling (no delete button for todos)
                const priorityClass = event.priority ? `priority-${event.priority}` : '';
                const statusClass = event.status === 'completed' ? 'status-completed' : '';
                eventClass += ` ${priorityClass} ${statusClass}`;
                clickHandler = `showEventDetails(${event.id})`;
            } else {
                // Calendar event styling with delete button
                const typeClass = event.type ? `event-type-${event.type}` : '';
                eventClass += ` ${typeClass}`;

                // Get reference to the event pill for positioning the popover
                clickHandler = `
                    var targetElement = event.currentTarget;
                    editCalendarEvent('${dateStr}', ${event.id}, targetElement);
                `;

                // Add delete button (shows on hover)
                deleteButton = `
                    <button class="calendar-event-delete"
                            onclick="event.stopPropagation(); deleteCalendarEvent('${dateStr}', ${event.id}, event);"
                            title="Delete event">
                        ×
                    </button>
                `;
            }

            html += `
                <div class="${eventClass}"
                     title="${eventTitle}"
                     onclick="event.stopPropagation(); ${clickHandler}">
                    <span class="calendar-event-title">${eventTitle}</span>
                    ${deleteButton}
                </div>
            `;
        });

        // Show "+X more" if there are additional events
        const remainingCount = events.length - MAX_VISIBLE_EVENTS;
        if (remainingCount > 0) {
            html += `<div class="calendar-more-events">+${remainingCount} more</div>`;
        }

        return html;
    } catch (error) {
        console.error('Error rendering day events:', error);
        return '';
    }
}

function showEventDetails(eventId) {
    // Navigate to todo module and highlight the specific todo
    openModule('todo');
    // You can add logic here to highlight/scroll to the specific todo
    console.log('Show event details for ID:', eventId);
}

function changeMonth(offset) {
    if (offset === 0) {
        const today = new Date();
        currentMonth = today.getMonth();
        currentYear = today.getFullYear();
    } else {
        currentMonth += offset;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        } else if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
    }
    renderCalendar();
}

// ================================================
// CALENDAR EVENT MANAGEMENT
// ================================================

/**
 * Handle click on a date cell
 */
function handleDateClick(dateStr, isPast, event) {
    console.log('📅 Date clicked:', dateStr, 'isPast:', isPast);

    if (isPast) {
        showDateError('Cannot add events to past dates');
        return;
    }

    // Get the clicked cell position for popover positioning
    const clickedCell = event ? event.currentTarget : null;
    openEventPopover(dateStr, null, clickedCell);
}

/**
 * Open event popover for adding/editing event
 * Positioned relative to the clicked date cell
 */
function openEventPopover(dateStr, eventData = null, targetElement = null) {
    const popover = document.getElementById('calendar-event-popover');
    const form = document.getElementById('calendar-event-form');

    // Reset form
    form.reset();

    // Set form data
    document.getElementById('event-id').value = eventData ? eventData.id : '';
    document.getElementById('event-date').value = dateStr;
    document.getElementById('event-title').value = eventData ? eventData.title : '';
    document.getElementById('event-type').value = eventData ? (eventData.type || 'other') : 'assignment';

    // Position popover near the target element (date cell or event pill)
    if (targetElement) {
        const rect = targetElement.getBoundingClientRect();
        const popoverWidth = 320;
        const popoverHeight = 220; // Approximate height

        let left = rect.left + (rect.width / 2) - (popoverWidth / 2);
        let top = rect.bottom + 8; // 8px below the cell

        // Keep popover within viewport bounds
        if (left + popoverWidth > window.innerWidth) {
            left = window.innerWidth - popoverWidth - 16;
        }
        if (left < 16) {
            left = 16;
        }

        // If popover would go below viewport, show it above the cell
        if (top + popoverHeight > window.innerHeight) {
            top = rect.top - popoverHeight - 8;
        }

        popover.style.left = left + 'px';
        popover.style.top = top + 'px';
    } else {
        // Center on screen if no target element
        popover.style.left = '50%';
        popover.style.top = '50%';
        popover.style.transform = 'translate(-50%, -50%)';
    }

    // Show popover
    popover.style.display = 'block';

    // Auto-focus the title input
    setTimeout(() => {
        document.getElementById('event-title').focus();
    }, 100);

    // Setup outside click handler
    setupPopoverClickOutside();
}

/**
 * Close event popover
 */
function closeEventPopover() {
    const popover = document.getElementById('calendar-event-popover');
    popover.style.display = 'none';
    document.getElementById('calendar-event-form').reset();

    // Remove click outside listener
    document.removeEventListener('click', handlePopoverOutsideClick);
}

/**
 * Setup click outside to close popover
 */
function setupPopoverClickOutside() {
    // Remove existing listener first
    document.removeEventListener('click', handlePopoverOutsideClick);

    // Add listener after a small delay to prevent immediate closure
    setTimeout(() => {
        document.addEventListener('click', handlePopoverOutsideClick);
    }, 100);
}

/**
 * Handle click outside popover
 */
function handlePopoverOutsideClick(e) {
    const popover = document.getElementById('calendar-event-popover');
    if (popover && popover.style.display === 'block') {
        if (!popover.contains(e.target)) {
            closeEventPopover();
        }
    }
}

/**
 * Handle Escape key to close popover
 */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const popover = document.getElementById('calendar-event-popover');
        if (popover && popover.style.display === 'block') {
            closeEventPopover();
        }
    }
});

/**
 * Save calendar event (create or update)
 */
function saveCalendarEvent(e) {
    e.preventDefault();

    const eventId = document.getElementById('event-id').value;
    const dateStr = document.getElementById('event-date').value;
    const title = document.getElementById('event-title').value.trim();
    const type = document.getElementById('event-type').value;

    if (!title) {
        showDateError('Event title is required');
        return;
    }

    if (!calendarEvents[dateStr]) {
        calendarEvents[dateStr] = [];
    }

    if (eventId) {
        // Update existing event
        const eventIndex = calendarEvents[dateStr].findIndex(e => e.id == eventId);
        if (eventIndex !== -1) {
            calendarEvents[dateStr][eventIndex] = {
                id: parseInt(eventId),
                title: title,
                type: type
            };
            console.log('✅ Event updated:', title);
        }
    } else {
        // Create new event
        const newEvent = {
            id: nextEventId++,
            title: title,
            type: type
        };
        calendarEvents[dateStr].push(newEvent);
        console.log('✅ Event created:', title);
    }

    // Save to localStorage
    saveEventsToStorage();

    // Close popover and re-render calendar
    closeEventPopover();
    renderCalendar();
}

/**
 * Delete calendar event (no confirmation modal - instant delete)
 */
function deleteCalendarEvent(dateStr, eventId, e) {
    // Stop event propagation to prevent opening edit popover
    if (e) {
        e.stopPropagation();
    }

    if (calendarEvents[dateStr]) {
        calendarEvents[dateStr] = calendarEvents[dateStr].filter(ev => ev.id != eventId);

        // Remove date key if no events left
        if (calendarEvents[dateStr].length === 0) {
            delete calendarEvents[dateStr];
        }

        console.log('✅ Event deleted instantly');
    }

    // Save to localStorage
    saveEventsToStorage();

    // Re-render calendar
    renderCalendar();
}

/**
 * Edit an existing event
 */
function editCalendarEvent(dateStr, eventId, targetElement) {
    const event = calendarEvents[dateStr]?.find(e => e.id === eventId);
    if (event) {
        openEventPopover(dateStr, event, targetElement);
    }
}

/**
 * Save events to localStorage
 */
function saveEventsToStorage() {
    try {
        localStorage.setItem('focusflow_calendar_events', JSON.stringify(calendarEvents));
        localStorage.setItem('focusflow_next_event_id', nextEventId.toString());
        console.log('💾 Events saved to localStorage');
    } catch (error) {
        console.error('Failed to save events:', error);
    }
}

/**
 * Load events from localStorage
 */
function loadEventsFromStorage() {
    try {
        const savedEvents = localStorage.getItem('focusflow_calendar_events');
        const savedNextId = localStorage.getItem('focusflow_next_event_id');

        if (savedEvents) {
            calendarEvents = JSON.parse(savedEvents);
            console.log('📂 Loaded', Object.keys(calendarEvents).length, 'dates with events');
        }

        if (savedNextId) {
            nextEventId = parseInt(savedNextId);
        }
    } catch (error) {
        console.error('Failed to load events:', error);
        calendarEvents = {};
        nextEventId = 1;
    }
}

// ================================================
// STUDY PLANNER
// ================================================

async function loadPlanner() {
    try {
        const response = await fetch('api/focusflow/planner_list.php');
        const data = await response.json();

        const plannerDiv = document.getElementById('weekly-planner');

        if (!data.success || data.plans.length === 0) {
            plannerDiv.innerHTML = `
                <div style="text-align:center; padding:var(--space-10); color:var(--neutral-400);">
                    <p style="font-size:3rem; margin:0;">📚</p>
                    <p style="margin-top:var(--space-4);">No study blocks scheduled. Create your first one!</p>
                </div>
            `;
            return;
        }

        // Group by day
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const plansByDay = {};
        days.forEach((_, i) => plansByDay[i] = []);

        data.plans.forEach(plan => {
            plansByDay[plan.day_of_week].push(plan);
        });

        let html = '<div class="modern-grid md-grid-2" style="gap:var(--space-4);">';

        days.forEach((dayName, dayIndex) => {
            const dayPlans = plansByDay[dayIndex];
            html += `
                <div class="modern-card" style="background:var(--neutral-50);">
                    <h3 style="margin:0 0 var(--space-4); color:var(--neutral-900);">${dayName}</h3>
                    ${dayPlans.length === 0 ?
                        '<p style="color:var(--neutral-400); font-size:0.875rem;">No study blocks</p>' :
                        dayPlans.map(plan => `
                            <div class="planner-event" style="margin-bottom:var(--space-3);">
                                <div style="font-weight:700;">${escapeHtml(plan.title)}</div>
                                <div style="font-size:0.75rem; opacity:0.9; margin-top:4px;">
                                    ${formatTime(plan.start_time)} - ${formatTime(plan.end_time)}
                                </div>
                                ${plan.subject ? `<div style="font-size:0.75rem; opacity:0.8;">📖 ${escapeHtml(plan.subject)}</div>` : ''}
                            </div>
                        `).join('')
                    }
                </div>
            `;
        });

        html += '</div>';
        plannerDiv.innerHTML = html;
    } catch (error) {
        console.error('Error loading planner:', error);
    }
}

function showAddPlanModal() {
    document.getElementById('plan-modal').style.display = 'flex';
    document.getElementById('plan-form').reset();
}

function closePlanModal() {
    document.getElementById('plan-modal').style.display = 'none';
}

async function addPlan(event) {
    event.preventDefault();

    const formData = {
        title: document.getElementById('plan-title').value,
        description: document.getElementById('plan-description').value,
        day_of_week: parseInt(document.getElementById('plan-day').value),
        start_time: document.getElementById('plan-start-time').value,
        end_time: document.getElementById('plan-end-time').value,
        subject: document.getElementById('plan-subject').value
    };

    try {
        const response = await fetch('api/focusflow/planner_add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            closePlanModal();
            loadPlanner();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error adding plan:', error);
        alert('Failed to add study block');
    }
}

// ================================================
// PROGRESS TRACKING
// ================================================

let tasksChart = null;
let pomodoroChart = null;

async function loadProgress() {
    try {
        const response = await fetch('api/focusflow/todo_stats.php');
        const data = await response.json();

        if (data.success) {
            document.getElementById('total-tasks').textContent = data.total_tasks || 0;
            document.getElementById('completed-tasks').textContent = data.completed_tasks || 0;
            const rate = data.total_tasks > 0 ?
                Math.round((data.completed_tasks / data.total_tasks) * 100) : 0;
            document.getElementById('completion-rate').textContent = rate + '%';
        }

        const pomodoroResponse = await fetch('api/focusflow/pomodoro_stats.php');
        const pomodoroData = await pomodoroResponse.json();

        if (pomodoroData.success) {
            document.getElementById('total-pomodoros').textContent = pomodoroData.total_sessions || 0;

            // Render charts
            renderCharts(data, pomodoroData);
        }
    } catch (error) {
        console.error('Error loading progress:', error);
    }
}

function renderCharts(todoData, pomodoroData) {
    // Tasks Chart
    const tasksCtx = document.getElementById('tasks-chart');
    if (tasksChart) tasksChart.destroy();

    tasksChart = new Chart(tasksCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'In Progress', 'Pending'],
            datasets: [{
                data: [
                    todoData.completed_tasks || 0,
                    todoData.in_progress_tasks || 0,
                    todoData.pending_tasks || 0
                ],
                backgroundColor: ['#10B981', '#F59E0B', '#6B7280']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Pomodoro Chart
    const pomodoroCtx = document.getElementById('pomodoro-chart');
    if (pomodoroChart) pomodoroChart.destroy();

    const weekData = pomodoroData.week_data || [];
    const labels = weekData.map(d => d.date);
    const values = weekData.map(d => d.sessions);

    pomodoroChart = new Chart(pomodoroCtx, {
        type: 'bar',
        data: {
            labels: labels.length > 0 ? labels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Sessions',
                data: values.length > 0 ? values : [0, 0, 0, 0, 0, 0, 0],
                backgroundColor: 'rgba(45, 91, 255, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

// ================================================
// UTILITY FUNCTIONS
// ================================================

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    const options = { month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function formatTime(timeStr) {
    const [hours, minutes] = timeStr.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const displayHour = h % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}

function getPriorityColor(priority) {
    const colors = { low: 'info', medium: 'warning', high: 'danger' };
    return colors[priority] || 'info';
}

function getStatusColor(status) {
    const colors = { pending: 'info', in_progress: 'warning', completed: 'success' };
    return colors[status] || 'info';
}
