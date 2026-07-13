const board       = document.getElementById('board');
const cards        = document.querySelectorAll('.card');
const movesText    = document.getElementById('moves-text');
const restartBtn   = document.getElementById('restart-btn');

let boardLocked = false; // prevents clicks while a mismatched pair is showing
let firstCardEl  = null; // DOM element of first pick, so we can flip it back if needed

async function startGame() {
    boardLocked = false;
    firstCardEl = null;

    await fetch('new_game.php', { method: 'POST' });

    cards.forEach(card => {
        card.classList.remove('flipped', 'matched');
        card.querySelector('.card-front img').src = '';
    });

    movesText.textContent = 'Moves: 0';
}

async function handleCardClick(e) {
    if (boardLocked) return;

    const card = e.currentTarget;
    if (card.classList.contains('flipped') || card.classList.contains('matched')) return;

    const index = card.dataset.index;

    const res = await fetch('check.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `index=${index}`
    });
    const data = await res.json();

    if (data.error) {
        console.warn(data.error);
        return;
    }

    // Reveal this card's image and flip it face-up
    revealCard(card, data.image);

    if (!data.turn_done) {
        // This was the first pick of the turn — just remember it and wait
        firstCardEl = card;
        return;
    }

    // This was the second pick — we now know if it's a match
    movesText.textContent = `Moves: ${data.moves}`;
    boardLocked = true;

    if (data.matched) {
        firstCardEl.classList.add('matched');
        card.classList.add('matched');
        boardLocked = false;
        firstCardEl = null;

        if (data.game_over) {
            movesText.textContent = `Solved in ${data.moves} moves! 🎉`;
        }
    } else {
        // No match — briefly show both, then flip back down
        setTimeout(() => {
            hideCard(firstCardEl);
            hideCard(card);
            boardLocked = false;
            firstCardEl = null;
        }, 900);
    }
}

function revealCard(card, imageFile) {
    card.querySelector('.card-front img').src = `images/${imageFile}`;
    card.classList.add('flipped');
}

function hideCard(card) {
    card.classList.remove('flipped');
    card.querySelector('.card-front img').src = '';
}

cards.forEach(card => card.addEventListener('click', handleCardClick));
restartBtn.addEventListener('click', startGame);

startGame(); // auto-start on page load