const modeSelect   = document.getElementById('mode-select');
const gameScreen    = document.getElementById('game-screen');
const cells         = document.querySelectorAll('.cell');
const statusText    = document.getElementById('status-text');
const restartBtn    = document.getElementById('restart-btn');
const nextRoundBtn  = document.getElementById('next-round-btn');
const mode2pBtn     = document.getElementById('mode-2player');
const modeAiBtn     = document.getElementById('mode-vs-ai');
const scoreXEl      = document.getElementById('score-x');
const scoreOEl      = document.getElementById('score-o');
const roundInfoEl   = document.getElementById('round-info');

let roundOver = false;
let matchOver = false;

function getSelectedBestOf() {
    return document.querySelector('input[name="best_of"]:checked').value;
}

async function startMatch(mode) {
    roundOver = false;
    matchOver = false;
    const bestOf = getSelectedBestOf();

    const res = await fetch('new_game.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `mode=${mode}&best_of=${bestOf}`
    });
    const data = await res.json();

    renderBoard(data.board);
    updateStatus(`Turn: ${data.turn}`);
    updateScoreboard(data.score_x, data.score_o, data.round, bestOf);
    nextRoundBtn.classList.add('hidden');

    modeSelect.classList.add('hidden');
    gameScreen.classList.remove('hidden');
}

function renderBoard(board) {
    cells.forEach((cell, i) => {
        cell.textContent = board[i] || '';
        cell.classList.toggle('taken', board[i] !== '');
    });
}

function updateStatus(text) {
    statusText.textContent = text;
}

function updateScoreboard(scoreX, scoreO, round, bestOf) {
    scoreXEl.textContent = `X: ${scoreX}`;
    scoreOEl.textContent = `O: ${scoreO}`;
    roundInfoEl.textContent = `Round ${round} of ${bestOf}`;
}

async function handleCellClick(e) {
    if (roundOver || matchOver) return;

    const index = e.target.dataset.index;
    if (e.target.classList.contains('taken')) return;

    const res = await fetch('move.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `position=${index}`
    });
    const data = await res.json();

    if (data.error) {
        updateStatus(data.error);
        return;
    }

    renderBoard(data.board);
    updateScoreboard(data.score_x, data.score_o, data.round, data.best_of);

    if (data.game_over) {
        roundOver = true;

        if (data.match_over) {
            matchOver = true;
            const msg = data.match_winner === 'draw'
                ? "Match tied!"
                : `${data.match_winner} wins the match!`;
            updateStatus(msg);
        } else {
            const roundMsg = data.winner === 'draw' ? "Round draw!" : `${data.winner} wins the round!`;
            updateStatus(roundMsg);
            nextRoundBtn.classList.remove('hidden');
        }
    } else {
        updateStatus(`Turn: ${data.turn}`);
    }
}

async function goToNextRound() {
    const res = await fetch('next_round.php', { method: 'POST' });
    const data = await res.json();

    if (data.error) {
        updateStatus(data.error);
        return;
    }

    roundOver = false;
    renderBoard(data.board);
    updateStatus(`Turn: ${data.turn}`);
    roundInfoEl.textContent = `Round ${data.round}`;
    nextRoundBtn.classList.add('hidden');
}

mode2pBtn.addEventListener('click', () => startMatch('2player'));
modeAiBtn.addEventListener('click', () => startMatch('vs_ai'));
cells.forEach(cell => cell.addEventListener('click', handleCellClick));
nextRoundBtn.addEventListener('click', goToNextRound);

restartBtn.addEventListener('click', () => {
    gameScreen.classList.add('hidden');
    modeSelect.classList.remove('hidden');
    nextRoundBtn.classList.add('hidden');
});