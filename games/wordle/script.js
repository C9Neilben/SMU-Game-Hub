const board = document.getElementById('board');
const messageEl = document.getElementById('message');
const newGameBtn = document.getElementById('new-game-btn');
const keyboardEl = document.getElementById('keyboard');
const modalOverlay = document.getElementById('modal-overlay');
const modalContent = document.getElementById('modal-content');

let currentGuess = '';
let currentRow = 0;
let gameOver = false;

const KEY_ROWS = [
    ['Q','W','E','R','T','Y','U','I','O','P'],
    ['A','S','D','F','G','H','J','K','L'],
    ['ENTER','Z','X','C','V','B','N','M','BACK']
];

// Tracks the best status seen so far for each letter (correct > present > absent)
const keyStatus = {};

// ---------- Board & Keyboard setup ----------

function buildBoard() {
    board.innerHTML = '';
    for (let r = 0; r < 6; r++) {
        const row = document.createElement('div');
        row.classList.add('row');
        row.id = `row-${r}`;
        for (let c = 0; c < 5; c++) {
            const tile = document.createElement('div');
            tile.classList.add('tile');
            tile.id = `tile-${r}-${c}`;
            row.appendChild(tile);
        }
        board.appendChild(row);
    }
}

function buildKeyboard() {
    keyboardEl.innerHTML = '';
    KEY_ROWS.forEach(rowKeys => {
        const row = document.createElement('div');
        row.classList.add('key-row');
        rowKeys.forEach(key => {
            const btn = document.createElement('button');
            btn.classList.add('key');
            if (key === 'ENTER' || key === 'BACK') btn.classList.add('key-wide');
            btn.textContent = key === 'BACK' ? '⌫' : key;
            btn.dataset.key = key;
            btn.addEventListener('click', () => handleKey(key));
            row.appendChild(btn);
        });
        keyboardEl.appendChild(row);
    });
}

function updateRowDisplay() {
    const row = document.getElementById(`row-${currentRow}`);
    for (let c = 0; c < 5; c++) {
        const tile = row.children[c];
        tile.textContent = currentGuess[c] || '';
    }
}

function handleKey(key) {
    if (gameOver) return;

    if (key === 'ENTER') {
        submitGuess();
    } else if (key === 'BACK') {
        currentGuess = currentGuess.slice(0, -1);
        updateRowDisplay();
    } else if (currentGuess.length < 5) {
        currentGuess += key;
        updateRowDisplay();
    }
}

function updateKeyboardColors(guessLetters, result) {
    const priority = { absent: 0, present: 1, correct: 2 };
    guessLetters.forEach((letter, i) => {
        const status = result[i];
        if (!keyStatus[letter] || priority[status] > priority[keyStatus[letter]]) {
            keyStatus[letter] = status;
        }
    });

    document.querySelectorAll('.key').forEach(btn => {
        const letter = btn.dataset.key;
        btn.classList.remove('correct', 'present', 'absent');
        if (keyStatus[letter]) {
            btn.classList.add(keyStatus[letter]);
        }
    });
}

// ---------- Guess submission ----------

async function submitGuess() {
    if (currentGuess.length !== 5 || gameOver) return;

    const res = await fetch('check.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `guess=${currentGuess}`
    });
    const data = await res.json();

    if (data.error) {
        messageEl.textContent = data.error;
        return;
    }

    const row = document.getElementById(`row-${currentRow}`);
    data.result.forEach((status, i) => {
        row.children[i].classList.add(status);
    });
    updateKeyboardColors(currentGuess.split(''), data.result);

    if (data.won) {
        gameOver = true;
        setTimeout(() => showWinModal(data.attempts_used), 500);
    } else if (data.lost) {
        gameOver = true;
        setTimeout(() => showLoseModal(data.secret_word), 500);
    } else {
        currentRow++;
        currentGuess = '';
    }
}

// ---------- Modal helpers ----------

function openModal() {
    modalOverlay.classList.remove('hidden');
}

function closeModal() {
    modalOverlay.classList.add('hidden');
    modalContent.innerHTML = '';
}

// ---------- Win modal: bonus math problem ----------

function generateMathProblem(attemptsUsed) {
    let a, b, op, answer;

    if (attemptsUsed <= 2) {
        // Hard: two-digit multiplication
        a = Math.floor(Math.random() * 12) + 11; // 11-22
        b = Math.floor(Math.random() * 9) + 2;    // 2-10
        op = '×';
        answer = a * b;
    } else if (attemptsUsed <= 4) {
        // Medium: two-digit addition/subtraction
        a = Math.floor(Math.random() * 60) + 20;
        b = Math.floor(Math.random() * 40) + 10;
        op = Math.random() < 0.5 ? '+' : '-';
        answer = op === '+' ? a + b : a - b;
    } else {
        // Easy: single-digit addition
        a = Math.floor(Math.random() * 9) + 1;
        b = Math.floor(Math.random() * 9) + 1;
        op = '+';
        answer = a + b;
    }

    // Build 3 plausible wrong answers close to the real one
    const choices = new Set([answer]);
    while (choices.size < 4) {
        const offset = Math.floor(Math.random() * 10) - 5;
        const wrong = answer + (offset === 0 ? 3 : offset);
        choices.add(wrong);
    }

    const shuffled = Array.from(choices).sort(() => Math.random() - 0.5);
    return { question: `${a} ${op} ${b}`, answer, choices: shuffled };
}

function showWinModal(attemptsUsed) {
    const problem = generateMathProblem(attemptsUsed);

    modalContent.innerHTML = `
        <h2>🎉 You got it in ${attemptsUsed}!</h2>
        <p class="modal-subtitle">Bonus Brain Buster</p>
        <p class="math-question">${problem.question} = ?</p>
        <div class="math-choices">
            ${problem.choices.map(c => `<button class="math-choice" data-value="${c}">${c}</button>`).join('')}
        </div>
        <p id="math-feedback"></p>
        <button id="modal-play-again" class="hidden">Play Again</button>
    `;

    openModal();

    document.querySelectorAll('.math-choice').forEach(btn => {
        btn.addEventListener('click', () => {
            const chosen = parseInt(btn.dataset.value, 10);
            const feedback = document.getElementById('math-feedback');
            document.querySelectorAll('.math-choice').forEach(b => b.disabled = true);

            if (chosen === problem.answer) {
                btn.classList.add('correct');
                feedback.textContent = 'Correct! Your brain checks out. 🧠';
            } else {
                btn.classList.add('absent');
                feedback.textContent = `Not quite — it was ${problem.answer}.`;
            }
            document.getElementById('modal-play-again').classList.remove('hidden');
        });
    });

    document.getElementById('modal-play-again').addEventListener('click', () => {
        closeModal();
        startNewGame();
    });
}

// ---------- Lose modal: banana screen ----------

function showLoseModal(secretWord) {
    modalContent.innerHTML = `
        <h2>Murag bugo pa kaayo ka para ani bai, ari oh saging 🍌</h2>
        <div class="banana-wrap">
            <svg viewBox="0 0 200 200" class="banana-svg">
                <path d="M50 150 C30 120, 40 70, 90 40 C120 22, 150 25, 165 40
                         C170 55, 150 60, 130 65 C95 75, 75 100, 70 140
                         C68 155, 60 160, 50 150 Z"
                      fill="#f4d03f" stroke="#caa416" stroke-width="3"/>
                <path d="M90 40 C95 30, 105 25, 115 28" fill="none" stroke="#7a5b0a" stroke-width="4" stroke-linecap="round"/>
            </svg>
        </div>
        <p class="modal-subtitle">The word was: <strong>${secretWord}</strong></p>
        <button id="modal-try-again">Try Again</button>
    `;

    openModal();

    document.getElementById('modal-try-again').addEventListener('click', () => {
        closeModal();
        startNewGame();
    });
}

// ---------- New game (shared by button + modal buttons) ----------

async function startNewGame() {
    await fetch('new_game.php');
    currentGuess = '';
    currentRow = 0;
    gameOver = false;
    messageEl.textContent = '';
    buildBoard();
    for (const key in keyStatus) delete keyStatus[key];
    buildKeyboard();
}

// ---------- Input listeners ----------

document.addEventListener('keydown', (e) => {
    if (gameOver) return;

    if (e.key === 'Enter') {
        e.preventDefault();
        handleKey('ENTER');
    } else if (e.key === 'Backspace') {
        handleKey('BACK');
    } else if (/^[a-zA-Z]$/.test(e.key)) {
        handleKey(e.key.toUpperCase());
    }
});

newGameBtn.addEventListener('click', async () => {
    await startNewGame();
    newGameBtn.blur();
});

buildBoard();
buildKeyboard();
