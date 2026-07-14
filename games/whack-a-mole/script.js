const startScreen    = document.getElementById('start-screen');
const gameScreen      = document.getElementById('game-screen');
const resultsScreen   = document.getElementById('results-screen');
const holes           = document.querySelectorAll('.hole');
const timerText       = document.getElementById('timer-text');
const scoreText       = document.getElementById('score-text');
const durationBtns    = document.querySelectorAll('.duration-btn');
const leaderboardList = document.getElementById('leaderboard-list');
const resultsSummary  = document.getElementById('results-summary');
const playerNameInput = document.getElementById('player-name');
const saveScoreBtn    = document.getElementById('save-score-btn');
const playAgainBtn    = document.getElementById('play-again-btn');

const MOLE_LIFESPAN_MIN = 500;  // ms — fastest a mole might stay up
const MOLE_LIFESPAN_MAX = 1100; // ms — slowest a mole might stay up
const GAP_MIN           = 150;  // ms — shortest pause before the next mole appears
const GAP_MAX           = 700;  // ms — longest pause before the next mole appears
const HOLE_COUNT        = 12;

function randomBetween(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

let duration       = 30;
let timeLeft        = duration;
let score           = 0;
let hits            = 0;
let misses          = 0;
let timerInterval   = null;
let moleTimeout     = null;
let activeHoleIndex = null;
let gameActive      = false;

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

    popMole();

    timerInterval = setInterval(() => {
        timeLeft--;
        timerText.textContent = `Time: ${timeLeft}`;
        if (timeLeft <= 0) {
            endGame();
        }
    }, 1000);
}

function popMole() {
    if (!gameActive) return;

    // Hide current mole if one is still up
    if (activeHoleIndex !== null) {
        holes[activeHoleIndex].querySelector('.mole').classList.add('hidden');
    }

    // Pick a new hole, different from the last one so it doesn't feel repetitive
    let newIndex;
    do {
        newIndex = Math.floor(Math.random() * HOLE_COUNT);
    } while (newIndex === activeHoleIndex && HOLE_COUNT > 1);

    activeHoleIndex = newIndex;
    holes[newIndex].querySelector('.mole').classList.remove('hidden');

    const lifespan = randomBetween(MOLE_LIFESPAN_MIN, MOLE_LIFESPAN_MAX);
    moleTimeout = setTimeout(() => {
        handleMiss();
    }, lifespan);
}

function popMoleAfterGap() {
    if (!gameActive) return;
    const gap = randomBetween(GAP_MIN, GAP_MAX);
    setTimeout(() => {
        if (gameActive) popMole();
    }, gap);
}

function handleHit(holeIndex) {
    if (!gameActive || holeIndex !== activeHoleIndex) return;

    clearTimeout(moleTimeout);

    hits++;
    score += 10;
    scoreText.textContent = `Score: ${score}`;

    holes[holeIndex].querySelector('.mole').classList.add('hidden');
    activeHoleIndex = null;

    popMoleAfterGap();
}

function handleMiss() {
    if (!gameActive) return;

    misses++;

    if (activeHoleIndex !== null) {
        holes[activeHoleIndex].querySelector('.mole').classList.add('hidden');
        activeHoleIndex = null;
    }

    popMoleAfterGap();
}

function endGame() {
    gameActive = false;
    clearInterval(timerInterval);
    clearTimeout(moleTimeout);

    if (activeHoleIndex !== null) {
        holes[activeHoleIndex].querySelector('.mole').classList.add('hidden');
        activeHoleIndex = null;
    }

    const totalAttempts = hits + misses;
    const accuracy = totalAttempts > 0 ? ((hits / totalAttempts) * 100).toFixed(2) : '0.00';

    resultsSummary.textContent = `Score: ${score} | Hits: ${hits} | Misses: ${misses} | Accuracy: ${accuracy}%`;

    gameScreen.classList.add('hidden');
    resultsScreen.classList.remove('hidden');
    showResultModal('win', `Session Complete — ${score} pts!`);

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

// Click handling per hole
holes.forEach((hole, index) => {
    hole.addEventListener('click', () => handleHit(index));
});

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