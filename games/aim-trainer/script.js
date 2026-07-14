const startScreen    = document.getElementById('start-screen');
const gameScreen      = document.getElementById('game-screen');
const resultsScreen   = document.getElementById('results-screen');
const range           = document.getElementById('range');
const timerText       = document.getElementById('timer-text');
const scoreText       = document.getElementById('score-text');
const durationBtns    = document.querySelectorAll('.duration-btn');
const leaderboardList = document.getElementById('leaderboard-list');
const resultsSummary  = document.getElementById('results-summary');
const playerNameInput = document.getElementById('player-name');
const saveScoreBtn    = document.getElementById('save-score-btn');
const playAgainBtn    = document.getElementById('play-again-btn');

const TARGET_LIFESPAN = 800; // ms before a target disappears if not clicked
const TARGET_SIZE     = 60;  // px
const IMAGE_COUNT     = 4;

let duration        = 30;
let timeLeft         = duration;
let score            = 0;
let hits             = 0;
let misses           = 0;
let timerInterval    = null;
let targetTimeout    = null;
let currentTargetEl  = null;
let gameActive       = false;

async function loadLeaderboard(dur) {
    leaderboardList.textContent = 'Loading...';
    const res = await fetch(`get_leaderboard.php?duration=${dur}`);
    const data = await res.json();

    if (data.scores.length === 0) {
        leaderboardList.innerHTML = '<p>No scores yet. Be the first!</p>';
        return;
    }

    leaderboardList.innerHTML = data.scores.map((s, i) =>
        `<div class="leaderboard-row">
            <span>#${i + 1}</span>
            <span>${escapeHtml(s.player_name)}</span>
            <span>${s.score} pts</span>
            <span>${s.accuracy}%</span>
        </div>`
    ).join('');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function startGame(dur) {
    duration   = dur;
    timeLeft   = dur;
    score      = 0;
    hits       = 0;
    misses     = 0;
    gameActive = true;

    scoreText.textContent = `Score: 0`;
    timerText.textContent = `Time: ${timeLeft}`;

    startScreen.classList.add('hidden');
    resultsScreen.classList.add('hidden');
    gameScreen.classList.remove('hidden');

    spawnTarget();

    timerInterval = setInterval(() => {
        timeLeft--;
        timerText.textContent = `Time: ${timeLeft}`;
        if (timeLeft <= 0) {
            endGame();
        }
    }, 1000);
}

function spawnTarget() {
    if (!gameActive) return;

    if (currentTargetEl) {
        currentTargetEl.remove();
        currentTargetEl = null;
    }

    const rangeRect = range.getBoundingClientRect();
    const maxX = rangeRect.width - TARGET_SIZE;
    const maxY = rangeRect.height - TARGET_SIZE;

    const x = Math.random() * maxX;
    const y = Math.random() * maxY;
    const imageNum = Math.floor(Math.random() * IMAGE_COUNT) + 1;

    const target = document.createElement('img');
    target.src = `images/${imageNum}.jpg`;
    target.className = 'target';
    target.style.left = `${x}px`;
    target.style.top = `${y}px`;

    target.addEventListener('click', handleHit);

    range.appendChild(target);
    currentTargetEl = target;

    targetTimeout = setTimeout(() => {
        if (currentTargetEl === target) {
            handleMiss();
        }
    }, TARGET_LIFESPAN);
}

function handleHit(e) {
    if (!gameActive) return;
    clearTimeout(targetTimeout);

    hits++;
    score += 10;
    scoreText.textContent = `Score: ${score}`;

    e.target.remove();
    currentTargetEl = null;

    spawnTarget();
}

function handleMiss() {
    if (!gameActive) return;

    misses++;

    if (currentTargetEl) {
        currentTargetEl.remove();
        currentTargetEl = null;
    }

    spawnTarget();
}

function endGame() {
    gameActive = false;
    clearInterval(timerInterval);
    clearTimeout(targetTimeout);

    if (currentTargetEl) {
        currentTargetEl.remove();
        currentTargetEl = null;
    }

    const totalAttempts = hits + misses;
    const accuracy = totalAttempts > 0 ? ((hits / totalAttempts) * 100).toFixed(2) : '0.00';

    resultsSummary.textContent = `Score: ${score} | Hits: ${hits} | Misses: ${misses} | Accuracy: ${accuracy}%`;

    gameScreen.classList.add('hidden');
    resultsScreen.classList.remove('hidden');

    resultsScreen.dataset.score = score;
    resultsScreen.dataset.accuracy = accuracy;
    resultsScreen.dataset.duration = duration;
}

async function saveScore() {
    const name     = playerNameInput.value.trim() || 'Anonymous';
    const score    = resultsScreen.dataset.score;
    const accuracy = resultsScreen.dataset.accuracy;
    const dur      = resultsScreen.dataset.duration;

    await fetch('save_score.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `player_name=${encodeURIComponent(name)}&score=${score}&accuracy=${accuracy}&duration=${dur}`
    });

    saveScoreBtn.disabled = true;
    saveScoreBtn.textContent = 'Saved!';

    loadLeaderboard(parseInt(dur));
}

durationBtns.forEach(btn => {
    btn.addEventListener('click', () => startGame(parseInt(btn.dataset.duration)));
});

saveScoreBtn.addEventListener('click', saveScore);

playAgainBtn.addEventListener('click', () => {
    resultsScreen.classList.add('hidden');
    startScreen.classList.remove('hidden');
    saveScoreBtn.disabled = false;
    saveScoreBtn.textContent = 'Save Score';
    playerNameInput.value = '';
    loadLeaderboard(duration);
});

// Initial load — show 30s leaderboard by default on page load
loadLeaderboard(30);