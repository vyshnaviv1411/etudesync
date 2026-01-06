/**
 * MindPlay Games Module
 * Implements all 5 productivity games:
 * 1. Sudoku
 * 2. XO (Tic-Tac-Toe)
 * 3. Memory Match
 * 4. Quick Math
 * 5. Word Unscramble
 */

// =====================================================
// SUDOKU GAME
// =====================================================
MindPlay.sudokuData = {
    board: [],
    solution: [],
    givenCells: [],
    selectedCell: null,
    difficulty: 'medium',
    startTime: null,
    timerInterval: null
};

MindPlay.initSudoku = function() {
    const gameArea = document.getElementById('game-area');
    gameArea.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; margin: 0;">Sudoku</h3>
            <button class="btn secondary" onclick="MindPlay.backToGames()">← Back</button>
        </div>

        <div style="text-align: center; margin-bottom: 20px;">
            <label style="margin-right: 12px;">Difficulty:</label>
            <select id="sudoku-difficulty" onchange="MindPlay.startSudoku()" style="padding: 8px 12px; border-radius: 8px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                <option value="easy">Easy</option>
                <option value="medium" selected>Medium</option>
                <option value="hard">Hard</option>
            </select>
        </div>

        <div class="timer" id="sudoku-timer">00:00</div>

        <div id="sudoku-grid-container"></div>

        <div style="display: grid; grid-template-columns: repeat(9, 1fr); gap: 8px; max-width: 500px; margin: 20px auto;">
            ${[1,2,3,4,5,6,7,8,9].map(n => `
                <button class="btn secondary" style="padding: 12px;" onclick="MindPlay.sudokuInputNumber(${n})">${n}</button>
            `).join('')}
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <button class="btn secondary" onclick="MindPlay.sudokuInputNumber(0)">Clear</button>
            <button class="btn primary" onclick="MindPlay.checkSudoku()">Check Solution</button>
        </div>
    `;

    this.startSudoku();
};

MindPlay.startSudoku = function() {
    const difficulty = document.getElementById('sudoku-difficulty').value;
    this.sudokuData.difficulty = difficulty;

    // Generate Sudoku puzzle
    const puzzle = this.generateSudoku(difficulty);
    this.sudokuData.board = puzzle.board.map(row => [...row]);
    this.sudokuData.solution = puzzle.solution.map(row => [...row]);
    this.sudokuData.givenCells = [];

    // Mark given cells
    for (let i = 0; i < 9; i++) {
        for (let j = 0; j < 9; j++) {
            if (puzzle.board[i][j] !== 0) {
                this.sudokuData.givenCells.push(`${i}-${j}`);
            }
        }
    }

    this.renderSudokuGrid();
    this.startSudokuTimer();
};

MindPlay.generateSudoku = function(difficulty) {
    // Start with a solved board
    const solution = this.generateSolvedSudoku();

    // Remove cells based on difficulty
    const cellsToRemove = difficulty === 'easy' ? 35 : difficulty === 'medium' ? 45 : 55;
    const board = solution.map(row => [...row]);

    let removed = 0;
    while (removed < cellsToRemove) {
        const row = Math.floor(Math.random() * 9);
        const col = Math.floor(Math.random() * 9);

        if (board[row][col] !== 0) {
            board[row][col] = 0;
            removed++;
        }
    }

    return { board, solution };
};

MindPlay.generateSolvedSudoku = function() {
    const board = Array(9).fill(0).map(() => Array(9).fill(0));

    // Fill diagonal 3x3 boxes first (they don't affect each other)
    for (let box = 0; box < 9; box += 3) {
        const nums = [1,2,3,4,5,6,7,8,9].sort(() => Math.random() - 0.5);
        let idx = 0;
        for (let i = 0; i < 3; i++) {
            for (let j = 0; j < 3; j++) {
                board[box + i][box + j] = nums[idx++];
            }
        }
    }

    // Solve the rest
    this.solveSudoku(board);
    return board;
};

MindPlay.solveSudoku = function(board) {
    const empty = this.findEmptyCell(board);
    if (!empty) return true;

    const [row, col] = empty;
    const nums = [1,2,3,4,5,6,7,8,9].sort(() => Math.random() - 0.5);

    for (const num of nums) {
        if (this.isValidSudokuMove(board, row, col, num)) {
            board[row][col] = num;

            if (this.solveSudoku(board)) {
                return true;
            }

            board[row][col] = 0;
        }
    }

    return false;
};

MindPlay.findEmptyCell = function(board) {
    for (let i = 0; i < 9; i++) {
        for (let j = 0; j < 9; j++) {
            if (board[i][j] === 0) {
                return [i, j];
            }
        }
    }
    return null;
};

MindPlay.isValidSudokuMove = function(board, row, col, num) {
    // Check row
    for (let j = 0; j < 9; j++) {
        if (board[row][j] === num) return false;
    }

    // Check column
    for (let i = 0; i < 9; i++) {
        if (board[i][col] === num) return false;
    }

    // Check 3x3 box
    const boxRow = Math.floor(row / 3) * 3;
    const boxCol = Math.floor(col / 3) * 3;
    for (let i = 0; i < 3; i++) {
        for (let j = 0; j < 3; j++) {
            if (board[boxRow + i][boxCol + j] === num) return false;
        }
    }

    return true;
};

MindPlay.renderSudokuGrid = function() {
    const container = document.getElementById('sudoku-grid-container');
    let html = '<div class="sudoku-grid">';

    for (let i = 0; i < 9; i++) {
        for (let j = 0; j < 9; j++) {
            const value = this.sudokuData.board[i][j];
            const isGiven = this.sudokuData.givenCells.includes(`${i}-${j}`);
            const cellClass = isGiven ? 'sudoku-cell given' : 'sudoku-cell';

            html += `
                <div class="${cellClass}" data-row="${i}" data-col="${j}"
                     onclick="MindPlay.selectSudokuCell(${i}, ${j})">
                    ${value !== 0 ? value : ''}
                </div>
            `;
        }
    }

    html += '</div>';
    container.innerHTML = html;
};

MindPlay.selectSudokuCell = function(row, col) {
    if (this.sudokuData.givenCells.includes(`${row}-${col}`)) return;

    document.querySelectorAll('.sudoku-cell').forEach(cell => {
        cell.classList.remove('selected');
    });

    const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
    cell.classList.add('selected');
    this.sudokuData.selectedCell = [row, col];
};

MindPlay.sudokuInputNumber = function(num) {
    if (!this.sudokuData.selectedCell) return;

    const [row, col] = this.sudokuData.selectedCell;
    this.sudokuData.board[row][col] = num;
    this.renderSudokuGrid();

    if (this.sudokuData.selectedCell) {
        this.selectSudokuCell(row, col);
    }
};

MindPlay.startSudokuTimer = function() {
    if (this.sudokuData.timerInterval) {
        clearInterval(this.sudokuData.timerInterval);
    }

    this.sudokuData.startTime = Date.now();
    this.sudokuData.timerInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - this.sudokuData.startTime) / 1000);
        const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
        const seconds = (elapsed % 60).toString().padStart(2, '0');
        document.getElementById('sudoku-timer').textContent = `${minutes}:${seconds}`;
    }, 1000);
};

MindPlay.checkSudoku = async function() {
    clearInterval(this.sudokuData.timerInterval);

    const isComplete = this.sudokuData.board.every((row, i) =>
        row.every((cell, j) => cell === this.sudokuData.solution[i][j])
    );

    const duration = Math.floor((Date.now() - this.sudokuData.startTime) / 1000);

    if (isComplete) {
        alert(`Congratulations! You solved the puzzle in ${Math.floor(duration / 60)}m ${duration % 60}s`);

        // Save session
        await this.saveGameSession('sudoku', duration, duration, {
            difficulty: this.sudokuData.difficulty,
            completed: true
        });

        this.startSudoku();
    } else {
        alert('Not quite right. Keep trying!');
        this.startSudokuTimer();
    }
};

// =====================================================
// XO (TIC-TAC-TOE) GAME
// =====================================================
MindPlay.xoData = {
    board: Array(9).fill(null),
    currentPlayer: 'X',
    gameOver: false,
    vsComputer: true
};

MindPlay.initXO = function() {
    const gameArea = document.getElementById('game-area');
    gameArea.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; margin: 0;">XO (Tic-Tac-Toe)</h3>
            <button class="btn secondary" onclick="MindPlay.backToGames()">← Back</button>
        </div>

        <div style="text-align: center; margin-bottom: 20px;">
            <div id="xo-status" style="font-size: 20px; font-weight: 600; margin-bottom: 16px;">Your turn (X)</div>
        </div>

        <div class="xo-grid" id="xo-grid"></div>

        <div style="text-align: center; margin-top: 24px;">
            <button class="btn primary" onclick="MindPlay.resetXO()">New Game</button>
        </div>
    `;

    this.resetXO();
};

MindPlay.resetXO = function() {
    this.xoData = {
        board: Array(9).fill(null),
        currentPlayer: 'X',
        gameOver: false,
        vsComputer: true
    };
    this.renderXOGrid();
    document.getElementById('xo-status').textContent = 'Your turn (X)';
};

MindPlay.renderXOGrid = function() {
    const grid = document.getElementById('xo-grid');
    grid.innerHTML = '';

    this.xoData.board.forEach((cell, index) => {
        const cellDiv = document.createElement('div');
        cellDiv.className = 'xo-cell' + (cell ? ' taken' : '');
        cellDiv.textContent = cell || '';
        cellDiv.onclick = () => this.xoMove(index);
        grid.appendChild(cellDiv);
    });
};

MindPlay.xoMove = function(index) {
    if (this.xoData.gameOver || this.xoData.board[index]) return;

    this.xoData.board[index] = this.xoData.currentPlayer;
    this.renderXOGrid();

    const winner = this.checkXOWinner();
    if (winner) {
        this.endXOGame(winner);
        return;
    }

    if (this.xoData.board.every(cell => cell !== null)) {
        this.endXOGame('draw');
        return;
    }

    // Switch player
    this.xoData.currentPlayer = this.xoData.currentPlayer === 'X' ? 'O' : 'X';

    // Computer move
    if (this.xoData.vsComputer && this.xoData.currentPlayer === 'O') {
        document.getElementById('xo-status').textContent = 'Computer thinking...';
        setTimeout(() => {
            this.xoComputerMove();
        }, 500);
    }
};

MindPlay.xoComputerMove = function() {
    const bestMove = this.xoMinimaxMove();
    this.xoData.board[bestMove] = 'O';
    this.renderXOGrid();

    const winner = this.checkXOWinner();
    if (winner) {
        this.endXOGame(winner);
        return;
    }

    if (this.xoData.board.every(cell => cell !== null)) {
        this.endXOGame('draw');
        return;
    }

    this.xoData.currentPlayer = 'X';
    document.getElementById('xo-status').textContent = 'Your turn (X)';
};

MindPlay.xoMinimaxMove = function() {
    let bestScore = -Infinity;
    let bestMove = null;

    for (let i = 0; i < 9; i++) {
        if (!this.xoData.board[i]) {
            this.xoData.board[i] = 'O';
            const score = this.xoMinimax(this.xoData.board, 0, false);
            this.xoData.board[i] = null;

            if (score > bestScore) {
                bestScore = score;
                bestMove = i;
            }
        }
    }

    return bestMove !== null ? bestMove : this.xoData.board.findIndex(cell => !cell);
};

MindPlay.xoMinimax = function(board, depth, isMaximizing) {
    const winner = this.checkXOWinner(board);

    if (winner === 'O') return 10 - depth;
    if (winner === 'X') return depth - 10;
    if (board.every(cell => cell !== null)) return 0;

    if (isMaximizing) {
        let bestScore = -Infinity;
        for (let i = 0; i < 9; i++) {
            if (!board[i]) {
                board[i] = 'O';
                bestScore = Math.max(bestScore, this.xoMinimax(board, depth + 1, false));
                board[i] = null;
            }
        }
        return bestScore;
    } else {
        let bestScore = Infinity;
        for (let i = 0; i < 9; i++) {
            if (!board[i]) {
                board[i] = 'X';
                bestScore = Math.min(bestScore, this.xoMinimax(board, depth + 1, true));
                board[i] = null;
            }
        }
        return bestScore;
    }
};

MindPlay.checkXOWinner = function(board = this.xoData.board) {
    const lines = [
        [0,1,2], [3,4,5], [6,7,8], // rows
        [0,3,6], [1,4,7], [2,5,8], // cols
        [0,4,8], [2,4,6]            // diagonals
    ];

    for (const [a, b, c] of lines) {
        if (board[a] && board[a] === board[b] && board[a] === board[c]) {
            return board[a];
        }
    }

    return null;
};

MindPlay.endXOGame = async function(result) {
    this.xoData.gameOver = true;

    let message, score;
    if (result === 'X') {
        message = 'You win!';
        score = 1;
    } else if (result === 'O') {
        message = 'Computer wins!';
        score = 0;
    } else {
        message = "It's a draw!";
        score = -1;
    }

    document.getElementById('xo-status').textContent = message;

    // Save session
    await this.saveGameSession('xo', score, 0, {
        result: result === 'X' ? 'win' : result === 'O' ? 'loss' : 'draw',
        moves: this.xoData.board.filter(c => c !== null).length
    });
};

// =====================================================
// MEMORY MATCH GAME
// =====================================================
MindPlay.memoryData = {
    cards: [],
    flippedCards: [],
    matchedPairs: 0,
    attempts: 0,
    startTime: null
};

MindPlay.initMemoryMatch = function() {
    const gameArea = document.getElementById('game-area');
    gameArea.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; margin: 0;">Memory Match</h3>
            <button class="btn secondary" onclick="MindPlay.backToGames()">← Back</button>
        </div>

        <div class="stats-display">
            <div class="stat-box">
                <div class="label">Attempts</div>
                <div class="value" id="memory-attempts">0</div>
            </div>
            <div class="stat-box">
                <div class="label">Pairs Found</div>
                <div class="value" id="memory-pairs">0/8</div>
            </div>
        </div>

        <div class="memory-grid" id="memory-grid"></div>

        <div style="text-align: center; margin-top: 24px;">
            <button class="btn primary" onclick="MindPlay.resetMemoryMatch()">New Game</button>
        </div>
    `;

    this.resetMemoryMatch();
};

MindPlay.resetMemoryMatch = function() {
    const emojis = ['🍎','🍌','🍒','🍇','🍉','🍊','🍓','🥝'];
    const cards = [...emojis, ...emojis].sort(() => Math.random() - 0.5);

    this.memoryData = {
        cards: cards.map((emoji, i) => ({ id: i, emoji, flipped: false, matched: false })),
        flippedCards: [],
        matchedPairs: 0,
        attempts: 0,
        startTime: Date.now()
    };

    this.renderMemoryGrid();
    document.getElementById('memory-attempts').textContent = '0';
    document.getElementById('memory-pairs').textContent = '0/8';
};

MindPlay.renderMemoryGrid = function() {
    const grid = document.getElementById('memory-grid');
    grid.innerHTML = '';

    this.memoryData.cards.forEach(card => {
        const cardDiv = document.createElement('div');
        cardDiv.className = 'memory-card';

        if (card.flipped || card.matched) {
            cardDiv.classList.add('flipped');
            cardDiv.textContent = card.emoji;
        }

        if (card.matched) {
            cardDiv.classList.add('matched');
        }

        cardDiv.onclick = () => this.flipMemoryCard(card.id);
        grid.appendChild(cardDiv);
    });
};

MindPlay.flipMemoryCard = function(cardId) {
    const card = this.memoryData.cards.find(c => c.id === cardId);

    if (card.matched || card.flipped || this.memoryData.flippedCards.length >= 2) {
        return;
    }

    card.flipped = true;
    this.memoryData.flippedCards.push(card);
    this.renderMemoryGrid();

    if (this.memoryData.flippedCards.length === 2) {
        this.memoryData.attempts++;
        document.getElementById('memory-attempts').textContent = this.memoryData.attempts;

        setTimeout(() => this.checkMemoryMatch(), 800);
    }
};

MindPlay.checkMemoryMatch = async function() {
    const [card1, card2] = this.memoryData.flippedCards;

    if (card1.emoji === card2.emoji) {
        card1.matched = true;
        card2.matched = true;
        this.memoryData.matchedPairs++;
        document.getElementById('memory-pairs').textContent = `${this.memoryData.matchedPairs}/8`;

        if (this.memoryData.matchedPairs === 8) {
            const duration = Math.floor((Date.now() - this.memoryData.startTime) / 1000);
            alert(`Congratulations! Completed in ${this.memoryData.attempts} attempts (${duration}s)`);

            await this.saveGameSession('memory_match', this.memoryData.attempts, duration, {
                grid_size: '4x4',
                pairs: 8
            });
        }
    } else {
        card1.flipped = false;
        card2.flipped = false;
    }

    this.memoryData.flippedCards = [];
    this.renderMemoryGrid();
};

// =====================================================
// QUICK MATH GAME
// =====================================================
MindPlay.mathData = {
    currentQuestion: null,
    score: 0,
    totalQuestions: 10,
    questionNumber: 0,
    startTime: null
};

MindPlay.initQuickMath = function() {
    const gameArea = document.getElementById('game-area');
    gameArea.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; margin: 0;">Quick Math</h3>
            <button class="btn secondary" onclick="MindPlay.backToGames()">← Back</button>
        </div>

        <div class="stats-display">
            <div class="stat-box">
                <div class="label">Question</div>
                <div class="value" id="math-question-num">0/10</div>
            </div>
            <div class="stat-box">
                <div class="label">Score</div>
                <div class="value" id="math-score">0</div>
            </div>
        </div>

        <div style="text-align: center; margin: 40px 0;">
            <div style="font-size: 48px; font-weight: 700; margin-bottom: 32px;" id="math-question">
                Click Start to begin
            </div>

            <input type="number" id="math-answer" placeholder="Your answer"
                   style="width: 200px; padding: 16px; font-size: 24px; text-align: center; border-radius: 12px;
                          background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); color: #fff;"
                   onkeypress="if(event.key==='Enter') MindPlay.submitMathAnswer()">

            <div style="margin-top: 24px;">
                <button class="btn primary" onclick="MindPlay.submitMathAnswer()">Submit</button>
            </div>
        </div>

        <div style="text-align: center;">
            <button class="btn secondary" onclick="MindPlay.startQuickMath()">Start Game</button>
        </div>
    `;
};

MindPlay.startQuickMath = function() {
    this.mathData = {
        currentQuestion: null,
        score: 0,
        totalQuestions: 10,
        questionNumber: 0,
        startTime: Date.now()
    };

    document.getElementById('math-score').textContent = '0';
    this.nextMathQuestion();
};

MindPlay.nextMathQuestion = function() {
    if (this.mathData.questionNumber >= this.mathData.totalQuestions) {
        this.endQuickMath();
        return;
    }

    this.mathData.questionNumber++;
    document.getElementById('math-question-num').textContent =
        `${this.mathData.questionNumber}/${this.mathData.totalQuestions}`;

    const operators = ['+', '-', '×', '÷'];
    const operator = operators[Math.floor(Math.random() * operators.length)];

    let num1, num2, answer;

    switch(operator) {
        case '+':
            num1 = Math.floor(Math.random() * 50) + 1;
            num2 = Math.floor(Math.random() * 50) + 1;
            answer = num1 + num2;
            break;
        case '-':
            num1 = Math.floor(Math.random() * 50) + 20;
            num2 = Math.floor(Math.random() * num1);
            answer = num1 - num2;
            break;
        case '×':
            num1 = Math.floor(Math.random() * 12) + 1;
            num2 = Math.floor(Math.random() * 12) + 1;
            answer = num1 * num2;
            break;
        case '÷':
            num2 = Math.floor(Math.random() * 10) + 2;
            answer = Math.floor(Math.random() * 10) + 1;
            num1 = num2 * answer;
            break;
    }

    this.mathData.currentQuestion = { num1, num2, operator, answer };
    document.getElementById('math-question').textContent = `${num1} ${operator} ${num2} = ?`;
    document.getElementById('math-answer').value = '';
    document.getElementById('math-answer').focus();
};

MindPlay.submitMathAnswer = function() {
    const userAnswer = parseInt(document.getElementById('math-answer').value);

    if (isNaN(userAnswer)) {
        alert('Please enter a number');
        return;
    }

    if (userAnswer === this.mathData.currentQuestion.answer) {
        this.mathData.score++;
        document.getElementById('math-score').textContent = this.mathData.score;
    }

    this.nextMathQuestion();
};

MindPlay.endQuickMath = async function() {
    const duration = Math.floor((Date.now() - this.mathData.startTime) / 1000);
    const accuracy = Math.round((this.mathData.score / this.mathData.totalQuestions) * 100);

    alert(`Game Over!\nScore: ${this.mathData.score}/${this.mathData.totalQuestions}\nAccuracy: ${accuracy}%`);

    await this.saveGameSession('quick_math', this.mathData.score, duration, {
        total_questions: this.mathData.totalQuestions,
        correct: this.mathData.score,
        accuracy: accuracy
    });

    document.getElementById('math-question').textContent = 'Click Start to play again';
};

// =====================================================
// WORD UNSCRAMBLE GAME
// =====================================================
MindPlay.wordData = {
    words: [
        'STUDENT', 'TEACHER', 'LIBRARY', 'KNOWLEDGE', 'LEARNING',
        'COMPUTER', 'SCIENCE', 'HISTORY', 'CULTURE', 'LANGUAGE',
        'CREATIVE', 'PRACTICE', 'EDUCATION', 'RESEARCH', 'PROGRESS'
    ],
    currentWord: null,
    scrambled: null,
    score: 0,
    totalWords: 5,
    wordNumber: 0,
    startTime: null
};

MindPlay.initWordUnscramble = function() {
    const gameArea = document.getElementById('game-area');
    gameArea.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; margin: 0;">Word Unscramble</h3>
            <button class="btn secondary" onclick="MindPlay.backToGames()">← Back</button>
        </div>

        <div class="stats-display">
            <div class="stat-box">
                <div class="label">Word</div>
                <div class="value" id="word-num">0/5</div>
            </div>
            <div class="stat-box">
                <div class="label">Score</div>
                <div class="value" id="word-score">0</div>
            </div>
        </div>

        <div style="text-align: center; margin: 40px 0;">
            <div style="font-size: 48px; font-weight: 700; margin-bottom: 32px; letter-spacing: 8px;" id="word-scrambled">
                Click Start
            </div>

            <input type="text" id="word-answer" placeholder="Unscramble the word"
                   style="width: 300px; padding: 16px; font-size: 20px; text-align: center; border-radius: 12px;
                          background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); color: #fff;
                          text-transform: uppercase;"
                   onkeypress="if(event.key==='Enter') MindPlay.submitWordAnswer()">

            <div style="margin-top: 24px;">
                <button class="btn secondary" onclick="MindPlay.wordHint()">💡 Hint</button>
                <button class="btn primary" onclick="MindPlay.submitWordAnswer()">Submit</button>
                <button class="btn secondary" onclick="MindPlay.skipWord()">Skip</button>
            </div>
        </div>

        <div style="text-align: center;">
            <button class="btn secondary" onclick="MindPlay.startWordUnscramble()">Start Game</button>
        </div>
    `;
};

MindPlay.startWordUnscramble = function() {
    this.wordData.score = 0;
    this.wordData.wordNumber = 0;
    this.wordData.startTime = Date.now();

    document.getElementById('word-score').textContent = '0';
    this.nextWord();
};

MindPlay.nextWord = function() {
    if (this.wordData.wordNumber >= this.wordData.totalWords) {
        this.endWordUnscramble();
        return;
    }

    this.wordData.wordNumber++;
    document.getElementById('word-num').textContent = `${this.wordData.wordNumber}/${this.wordData.totalWords}`;

    const availableWords = this.wordData.words.filter(w => w !== this.wordData.currentWord);
    this.wordData.currentWord = availableWords[Math.floor(Math.random() * availableWords.length)];
    this.wordData.scrambled = this.scrambleWord(this.wordData.currentWord);

    document.getElementById('word-scrambled').textContent = this.wordData.scrambled;
    document.getElementById('word-answer').value = '';
    document.getElementById('word-answer').focus();
};

MindPlay.scrambleWord = function(word) {
    const arr = word.split('');
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr.join('');
};

MindPlay.submitWordAnswer = function() {
    const userAnswer = document.getElementById('word-answer').value.toUpperCase().trim();

    if (!userAnswer) {
        alert('Please enter an answer');
        return;
    }

    if (userAnswer === this.wordData.currentWord) {
        this.wordData.score++;
        document.getElementById('word-score').textContent = this.wordData.score;
        alert('Correct! 🎉');
    } else {
        alert(`Incorrect! The word was: ${this.wordData.currentWord}`);
    }

    this.nextWord();
};

MindPlay.wordHint = function() {
    if (!this.wordData.currentWord) return;

    const hint = this.wordData.currentWord[0] + '...' + this.wordData.currentWord[this.wordData.currentWord.length - 1];
    alert(`Hint: ${hint}\nLength: ${this.wordData.currentWord.length} letters`);
};

MindPlay.skipWord = function() {
    alert(`Skipped! The word was: ${this.wordData.currentWord}`);
    this.nextWord();
};

MindPlay.endWordUnscramble = async function() {
    const duration = Math.floor((Date.now() - this.wordData.startTime) / 1000);

    alert(`Game Over!\nWords Solved: ${this.wordData.score}/${this.wordData.totalWords}\nTime: ${duration}s`);

    await this.saveGameSession('word_unscramble', this.wordData.score, duration, {
        total_words: this.wordData.totalWords,
        words_solved: this.wordData.score
    });

    document.getElementById('word-scrambled').textContent = 'Click Start to play again';
};

// =====================================================
// SAVE GAME SESSION (Common function)
// =====================================================
MindPlay.saveGameSession = async function(gameType, score, duration, metadata) {
    try {
        const response = await fetch('api/mindplay/game_session_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                game_type: gameType,
                score: score,
                duration: duration,
                metadata: metadata
            })
        });

        const data = await response.json();
        if (!data.success) {
            console.error('Error saving game session:', data.message);
        }
    } catch (error) {
        console.error('Error saving game session:', error);
    }
};
