// shared/result-modal.js — reusable win/lose video modal for all games
// Usage: showResultModal('win') or showResultModal('lose')
// Requires: a <link> to shared/result-modal.css in the game's <head>,
// and this script included on the page (paths below assume games/{name}/ depth).

function initResultModal() {
    if (document.getElementById('result-modal-overlay')) return; // already injected

    const overlay = document.createElement('div');
    overlay.id = 'result-modal-overlay';
    overlay.className = 'hidden';
    overlay.innerHTML = `
        <div id="result-modal-card">
            <h2 id="result-modal-title"></h2>
            <video id="result-modal-video" playsinline></video>
            <button id="result-modal-close">Close</button>
        </div>
    `;
    document.body.appendChild(overlay);

    document.getElementById('result-modal-close').addEventListener('click', hideResultModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) hideResultModal(); // click outside card closes it
    });
}

function showResultModal(type, customTitle) {
    initResultModal();

    const overlay = document.getElementById('result-modal-overlay');
    const title = document.getElementById('result-modal-title');
    const video = document.getElementById('result-modal-video');

    const isWin = type === 'win';
    title.textContent = customTitle || (isWin ? 'You Win! 🎉' : 'You Lose 😵');
    video.src = isWin ? '../../shared/videos/win.mp4' : '../../shared/videos/lose.mp4';

    overlay.classList.remove('hidden');
    video.currentTime = 0;
    video.play().catch(() => {
        // Autoplay might be blocked by the browser — that's fine, user can press play manually
    });
}

function hideResultModal() {
    const overlay = document.getElementById('result-modal-overlay');
    const video = document.getElementById('result-modal-video');
    if (video) video.pause();
    if (overlay) overlay.classList.add('hidden');
}