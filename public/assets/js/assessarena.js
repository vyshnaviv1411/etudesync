// AssessArena JavaScript
// Handles all quiz creation, taking, and viewing functionality

// Global state
let currentQuizId = null;
let currentQuizCode = null;
let currentQuizData = null;
let questionCounter = 0;
let quizStartTime = null;
let timerInterval = null;
let userAnswers = {};

// Module Navigation
function showModule(moduleName) {
    // Hide hub
    document.getElementById('module-hub').classList.add('hidden');

    // Hide all modules
    document.querySelectorAll('.module-page').forEach(page => {
        page.classList.add('hidden');
    });

    // Show selected module
    const moduleId = moduleName + '-module';
    const moduleElement = document.getElementById(moduleId);
    if (moduleElement) {
        moduleElement.classList.remove('hidden');

        // Load data for specific modules
        if (moduleName === 'my-quizzes') {
            loadMyQuizzes();
        } else if (moduleName === 'history') {
            loadHistory();
        } else if (moduleName === 'stats') {
            loadStats();
        } else if (moduleName === 'create-quiz') {
            resetCreateQuiz();
        } else if (moduleName === 'take-quiz') {
            resetTakeQuiz();
        }
    }
}

function showHub() {
    // Reset all module states
    resetCreateQuiz();
    resetTakeQuiz();

    // Hide all modules
    document.querySelectorAll('.module-page').forEach(page => {
        page.classList.add('hidden');
    });

    // Show hub
    document.getElementById('module-hub').classList.remove('hidden');
}

// CREATE QUIZ FUNCTIONS
function resetCreateQuiz() {
    currentQuizId = null;
    currentQuizCode = null;
    questionCounter = 0;

    // Reset form
    document.getElementById('quiz-title').value = '';
    document.getElementById('quiz-time-limit').value = '';
    document.getElementById('quiz-shuffle').checked = false;

    // Show setup, hide builder
    document.getElementById('quiz-setup').classList.remove('hidden');
    document.getElementById('question-builder-section').classList.add('hidden');

    // Clear questions
    document.getElementById('questions-list').innerHTML = '';
    document.getElementById('total-questions').textContent = '0';
}

async function createQuiz() {
    const title = document.getElementById('quiz-title').value.trim();
    const timeLimit = document.getElementById('quiz-time-limit').value;
    const shuffle = document.getElementById('quiz-shuffle').checked;

    if (!title) {
        alert('Please enter a quiz title');
        return;
    }

    const formData = new FormData();
    formData.append('title', title);
    if (timeLimit) formData.append('time_limit', timeLimit);
    formData.append('shuffle_questions', shuffle ? '1' : '0');

    try {
        const response = await fetch('api/assessarena/quiz_create.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.ok) {
            currentQuizId = data.quiz_id;
            currentQuizCode = data.code;

            // Update UI
            document.getElementById('current-quiz-title').textContent = title;
            document.getElementById('current-quiz-code').textContent = data.code;
            document.getElementById('quiz-setup').classList.add('hidden');
            document.getElementById('question-builder-section').classList.remove('hidden');

            questionCounter = 0;
            updateQuestionCounter();
        } else {
            alert('Error: ' + data.msg);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to create quiz');
    }
}

function updateQuestionCounter() {
    document.getElementById('question-count').textContent = '#' + (questionCounter + 1);
}

async function addQuestion() {
    const text = document.getElementById('question-text').value.trim();
    const optionA = document.getElementById('option-a').value.trim();
    const optionB = document.getElementById('option-b').value.trim();
    const optionC = document.getElementById('option-c').value.trim();
    const optionD = document.getElementById('option-d').value.trim();
    const correct = document.getElementById('correct-option').value;

    if (!text || !optionA || !optionB || !optionC || !optionD || !correct) {
        alert('Please fill in all fields');
        return;
    }

    const formData = new FormData();
    formData.append('quiz_id', currentQuizId);
    formData.append('text', text);
    formData.append('option_a', optionA);
    formData.append('option_b', optionB);
    formData.append('option_c', optionC);
    formData.append('option_d', optionD);
    formData.append('correct_option', correct);

    try {
        const response = await fetch('api/assessarena/question_add.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.ok) {
            questionCounter++;
            updateQuestionCounter();

            // Add to list
            const questionsList = document.getElementById('questions-list');
            const questionItem = document.createElement('div');
            questionItem.style.cssText = 'padding: 12px; border: 1px solid var(--neutral-200); border-radius: 8px; margin-bottom: 8px; background: white;';
            questionItem.innerHTML = `
                <strong>Q${questionCounter}:</strong> ${text}<br>
                <small style="color: var(--neutral-500);">Correct: Option ${correct}</small>
            `;
            questionsList.appendChild(questionItem);

            document.getElementById('total-questions').textContent = questionCounter;

            // Clear form
            document.getElementById('question-text').value = '';
            document.getElementById('option-a').value = '';
            document.getElementById('option-b').value = '';
            document.getElementById('option-c').value = '';
            document.getElementById('option-d').value = '';
            document.getElementById('correct-option').value = '';

            // Show success
            showToast('Question added successfully!');
        } else {
            alert('Error: ' + data.msg);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to add question');
    }
}

function finishQuiz() {
    if (questionCounter === 0) {
        alert('Please add at least one question before finishing');
        return;
    }

    alert(`Quiz created successfully!\n\nQuiz Code: ${currentQuizCode}\n\nShare this code with others to let them take your quiz.`);
    showHub();
}

// TAKE QUIZ FUNCTIONS
function resetTakeQuiz() {
    currentQuizData = null;
    quizStartTime = null;
    userAnswers = {};

    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }

    document.getElementById('join-quiz-code').value = '';
    document.getElementById('quiz-code-input').classList.remove('hidden');
    document.getElementById('quiz-interface').classList.add('hidden');
    document.getElementById('quiz-results').classList.add('hidden');
    document.getElementById('quiz-timer').classList.add('hidden');
}

async function loadQuiz() {
    const code = document.getElementById('join-quiz-code').value.trim().toUpperCase();

    if (!code || code.length !== 8) {
        alert('Please enter a valid 8-character quiz code');
        return;
    }

    try {
        const response = await fetch(`api/assessarena/quiz_get.php?code=${code}`);
        const data = await response.json();

        if (data.ok) {
            currentQuizData = data;
            userAnswers = {};
            quizStartTime = new Date().toISOString().slice(0, 19).replace('T', ' ');

            // Hide code input, show quiz
            document.getElementById('quiz-code-input').classList.add('hidden');
            document.getElementById('quiz-interface').classList.remove('hidden');

            // Set quiz info
            document.getElementById('quiz-title-display').textContent = data.quiz.title;
            document.getElementById('quiz-question-count').textContent = `${data.quiz.total_questions} Questions`;

            const timeInfo = data.quiz.time_limit_minutes
                ? `Time Limit: ${data.quiz.time_limit_minutes} minutes`
                : 'No time limit';
            document.getElementById('quiz-time-info').textContent = timeInfo;

            // Start timer if there's a time limit
            if (data.quiz.time_limit_minutes) {
                startTimer(data.quiz.time_limit_minutes);
            }

            // Render questions
            renderQuestions(data.questions);
        } else {
            alert('Error: ' + data.msg);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load quiz');
    }
}

function renderQuestions(questions) {
    const container = document.getElementById('questions-container');
    container.innerHTML = '';

    questions.forEach((question, index) => {
        const questionCard = document.createElement('div');
        questionCard.className = 'question-card';
        questionCard.innerHTML = `
            <div class="question-number">Question ${index + 1}</div>
            <div class="question-text">${escapeHtml(question.text)}</div>
            <div class="options">
                ${renderOption(question.id, 'A', question.option_a)}
                ${renderOption(question.id, 'B', question.option_b)}
                ${renderOption(question.id, 'C', question.option_c)}
                ${renderOption(question.id, 'D', question.option_d)}
            </div>
        `;
        container.appendChild(questionCard);
    });
}

function renderOption(questionId, label, text) {
    return `
        <button class="option-button" onclick="selectOption(${questionId}, '${label}', this)">
            <span class="option-label">${label}</span>
            <span>${escapeHtml(text)}</span>
        </button>
    `;
}

function selectOption(questionId, option, button) {
    // Deselect all options for this question
    const questionCard = button.closest('.question-card');
    questionCard.querySelectorAll('.option-button').forEach(btn => {
        btn.classList.remove('selected');
    });

    // Select this option
    button.classList.add('selected');
    userAnswers[questionId] = option;
}

function startTimer(minutes) {
    const timerElement = document.getElementById('quiz-timer');
    timerElement.classList.remove('hidden');

    let totalSeconds = minutes * 60;

    timerInterval = setInterval(() => {
        totalSeconds--;

        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        timerElement.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;

        // Warning at 1 minute
        if (totalSeconds === 60) {
            timerElement.classList.add('warning');
        }

        // Time's up
        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            timerElement.textContent = 'Time\'s Up!';
            submitQuiz();
        }
    }, 1000);
}

async function submitQuiz() {
    if (Object.keys(userAnswers).length === 0) {
        if (!confirm('You haven\'t answered any questions. Submit anyway?')) {
            return;
        }
    }

    if (timerInterval) {
        clearInterval(timerInterval);
    }

    const formData = new FormData();
    formData.append('quiz_id', currentQuizData.quiz.id);
    formData.append('started_at', quizStartTime);

    // Add all answers
    for (const [questionId, answer] of Object.entries(userAnswers)) {
        formData.append(`answers[${questionId}]`, answer);
    }

    try {
        const response = await fetch('api/assessarena/attempt_submit.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.ok) {
            showResults(data);
        } else {
            alert('Error: ' + data.msg);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to submit quiz');
    }
}

function showResults(data) {
    // Hide quiz interface
    document.getElementById('quiz-interface').classList.add('hidden');
    document.getElementById('quiz-timer').classList.add('hidden');

    // Show results
    const resultsDiv = document.getElementById('quiz-results');
    resultsDiv.classList.remove('hidden');

    // Score badge
    const scoreClass = getScoreClass(data.percentage);
    document.getElementById('score-display').innerHTML = `
        <span class="score-badge ${scoreClass}">
            ${data.score} / ${data.total_questions}
        </span>
    `;

    // Details
    const minutes = Math.floor(data.duration_seconds / 60);
    const seconds = data.duration_seconds % 60;
    document.getElementById('result-details').innerHTML = `
        <p style="font-size: 1.2rem; margin-bottom: 8px;">Score: ${data.percentage}%</p>
        <p style="color: var(--neutral-500);">Time taken: ${minutes}m ${seconds}s</p>
    `;

    // Answers review
    const reviewHtml = data.results.map((result, index) => {
        const isCorrect = result.is_correct;
        const icon = isCorrect ? '✅' : '❌';
        const color = isCorrect ? 'var(--success)' : 'var(--error)';

        return `
            <div style="padding: 12px; border-left: 4px solid ${color}; background: var(--neutral-50); border-radius: 8px; margin-bottom: 8px;">
                ${icon} <strong>Question ${index + 1}</strong><br>
                <small style="color: var(--neutral-600);">
                    Your answer: ${result.user_answer || 'Not answered'} |
                    Correct answer: ${result.correct_answer}
                </small>
            </div>
        `;
    }).join('');

    document.getElementById('answers-review').innerHTML = `
        <h3 style="margin-bottom: 12px;">Answer Review</h3>
        ${reviewHtml}
    `;
}

function getScoreClass(percentage) {
    if (percentage >= 80) return 'excellent';
    if (percentage >= 60) return 'good';
    if (percentage >= 40) return 'average';
    return 'poor';
}

// MY QUIZZES FUNCTIONS
async function loadMyQuizzes() {
    try {
        const response = await fetch('api/assessarena/quiz_list.php');
        const data = await response.json();

        const container = document.getElementById('my-quizzes-list');

        if (data.ok && data.quizzes.length > 0) {
            container.innerHTML = data.quizzes.map(quiz => `
                <div class="quiz-item">
                    <div class="quiz-info">
                        <h3>${escapeHtml(quiz.title)}</h3>
                        <div class="quiz-meta">
                            Code: <span class="quiz-code">${quiz.code}</span> |
                            ${quiz.question_count} questions |
                            Created: ${formatDate(quiz.created_at)}
                            ${quiz.time_limit_minutes ? `| Time: ${quiz.time_limit_minutes} min` : ''}
                        </div>
                    </div>
                    <div>
                        <button class="btn secondary" onclick="copyCode('${quiz.code}')">Copy Code</button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <p>You haven't created any quizzes yet.</p>
                    <button class="btn primary" onclick="showModule('create-quiz')" style="margin-top: 16px;">Create Your First Quiz</button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('my-quizzes-list').innerHTML = '<p style="color: var(--error);">Failed to load quizzes</p>';
    }
}

// HISTORY FUNCTIONS
async function loadHistory() {
    try {
        const response = await fetch('api/assessarena/attempt_history.php');
        const data = await response.json();

        const container = document.getElementById('history-list');

        if (data.ok && data.attempts.length > 0) {
            container.innerHTML = data.attempts.map(attempt => {
                const scoreClass = getScoreClass(attempt.percentage);
                return `
                    <div class="attempt-item">
                        <div class="quiz-info">
                            <h3>${escapeHtml(attempt.quiz_title)}</h3>
                            <div class="quiz-meta">
                                Code: <span class="quiz-code">${attempt.quiz_code}</span> |
                                ${formatDate(attempt.submitted_at)} |
                                Time: ${attempt.duration_formatted}
                            </div>
                        </div>
                        <div class="score-badge ${scoreClass}">
                            ${attempt.score}/${attempt.total_questions}
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">📊</div>
                    <p>No quiz attempts yet.</p>
                    <button class="btn primary" onclick="showModule('take-quiz')" style="margin-top: 16px;">Take a Quiz</button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('history-list').innerHTML = '<p style="color: var(--error);">Failed to load history</p>';
    }
}

// LEADERBOARD FUNCTIONS
async function loadLeaderboard() {
    const code = document.getElementById('leaderboard-code').value.trim().toUpperCase();
    const url = code
        ? `api/assessarena/leaderboard.php?quiz_code=${code}`
        : 'api/assessarena/leaderboard.php';

    try {
        const response = await fetch(url);
        const data = await response.json();

        const container = document.getElementById('leaderboard-content');

        if (data.ok) {
            if (data.leaderboard) {
                // Specific quiz leaderboard
                if (data.leaderboard.length > 0) {
                    container.innerHTML = `
                        <h3 style="margin-bottom: 16px;">Quiz: ${code}</h3>
                        <table class="leaderboard-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>User</th>
                                    <th>Best Score</th>
                                    <th>Fastest Time</th>
                                    <th>Attempts</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.leaderboard.map((entry, index) => {
                                    const rank = index + 1;
                                    const rankClass = rank <= 3 ? `rank-${rank}` : 'rank-other';
                                    return `
                                        <tr>
                                            <td><span class="rank-badge ${rankClass}">${rank}</span></td>
                                            <td><strong>${escapeHtml(entry.user_name)}</strong></td>
                                            <td>${entry.best_score}/${entry.total_questions} (${entry.percentage}%)</td>
                                            <td>${entry.fastest_time_formatted}</td>
                                            <td>${entry.attempts_count}</td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    `;
                } else {
                    container.innerHTML = '<p class="empty-state">No attempts for this quiz yet.</p>';
                }
            } else if (data.user_stats) {
                // User stats
                if (data.user_stats.length > 0) {
                    container.innerHTML = `
                        <h3 style="margin-bottom: 16px;">Your Best Performances</h3>
                        <table class="leaderboard-table">
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th>Code</th>
                                    <th>Best Score</th>
                                    <th>Attempts</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.user_stats.map(stat => `
                                    <tr>
                                        <td><strong>${escapeHtml(stat.title)}</strong></td>
                                        <td><span class="quiz-code">${stat.code}</span></td>
                                        <td>${stat.best_score}/${stat.total_questions} (${stat.percentage}%)</td>
                                        <td>${stat.attempts_count}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                } else {
                    container.innerHTML = '<p class="empty-state">No quiz attempts yet. Take a quiz to see your stats!</p>';
                }
            }
        } else {
            container.innerHTML = `<p style="color: var(--error);">Error: ${data.msg}</p>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<p style="color: var(--error);">Failed to load leaderboard</p>';
    }
}

// STATS FUNCTIONS
async function loadStats() {
    try {
        const response = await fetch('api/assessarena/leaderboard.php');
        const data = await response.json();

        const container = document.getElementById('stats-content');

        if (data.ok && data.user_stats && data.user_stats.length > 0) {
            const totalAttempts = data.user_stats.reduce((sum, stat) => sum + parseInt(stat.attempts_count), 0);
            const avgScore = data.user_stats.reduce((sum, stat) => sum + parseFloat(stat.percentage), 0) / data.user_stats.length;

            container.innerHTML = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
                    <div style="padding: 24px; border-radius: 16px; background: linear-gradient(135deg, #7c4dff, #47d7d3); color: white; text-align: center; box-shadow: 0 10px 25px rgba(124,77,255,0.25);">
                        <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 8px;">${data.user_stats.length}</div>
                        <div style="font-size: 0.875rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Quizzes Taken</div>
                    </div>
                    <div style="padding: 24px; border-radius: 16px; background: linear-gradient(135deg, #10B981, #059669); color: white; text-align: center; box-shadow: 0 10px 25px rgba(16,185,129,0.25);">
                        <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 8px;">${totalAttempts}</div>
                        <div style="font-size: 0.875rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Total Attempts</div>
                    </div>
                    <div style="padding: 24px; border-radius: 16px; background: linear-gradient(135deg, #3B82F6, #2563EB); color: white; text-align: center; box-shadow: 0 10px 25px rgba(59,130,246,0.25);">
                        <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 8px;">${avgScore.toFixed(1)}%</div>
                        <div style="font-size: 0.875rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Avg Score</div>
                    </div>
                </div>

                <h3 style="margin-bottom: 12px;">Your Best Scores</h3>
                ${data.user_stats.map(stat => {
                    const scoreClass = getScoreClass(stat.percentage);
                    return `
                        <div class="quiz-item">
                            <div class="quiz-info">
                                <h3>${escapeHtml(stat.title)}</h3>
                                <div class="quiz-meta">
                                    Code: <span class="quiz-code">${stat.code}</span> |
                                    ${stat.attempts_count} attempts
                                </div>
                            </div>
                            <div class="score-badge ${scoreClass}">
                                ${stat.best_score}/${stat.total_questions}
                            </div>
                        </div>
                    `;
                }).join('')}
            `;
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">📈</div>
                    <p>No stats available yet. Take some quizzes to see your performance!</p>
                    <button class="btn primary" onclick="showModule('take-quiz')" style="margin-top: 16px;">Take a Quiz</button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('stats-content').innerHTML = '<p style="color: var(--error);">Failed to load stats</p>';
    }
}

// UTILITY FUNCTIONS
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        showToast('Code copied to clipboard!');
    }).catch(() => {
        alert(`Quiz Code: ${code}`);
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 16px 24px;
        background: rgba(15,20,30,0.95);
        color: white;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Add CSS animations for toast
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Show quiz setup by default
    document.getElementById('quiz-setup').classList.remove('hidden');
});
