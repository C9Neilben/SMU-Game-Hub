<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Avoid Saying the Same Thing</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h1>🙊 Avoid Saying the Same Thing</h1>
    <p>Each round, one player secretly becomes the <strong>Prompter</strong> and gives a
    category plus a hidden answer. Everyone else answers the same category, trying
    to land on something <em>different</em> from the Prompter's secret answer.</p>

    <form action="start.php" method="POST" id="setupForm">
        <label for="numPlayers">Number of players (1–7):</label>
        <select name="numPlayers" id="numPlayers" onchange="renderNameFields()" required>
            <option value="">-- choose --</option>
            <?php for ($i = 1; $i <= 7; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
        </select>

        <div id="nameFields"></div>

        <button type="submit" id="startBtn" style="display:none;">Start Game</button>
    </form>
</div>

<script>
function renderNameFields() {
    const n = parseInt(document.getElementById('numPlayers').value, 10);
    const container = document.getElementById('nameFields');
    container.innerHTML = '';
    if (!n) {
        document.getElementById('startBtn').style.display = 'none';
        return;
    }
    for (let i = 1; i <= n; i++) {
        const label = document.createElement('label');
        label.textContent = 'Player ' + i + ' name:';
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'playerName[]';
        input.required = true;
        input.maxLength = 30;
        input.value = (n === 1) ? 'Me' : ('Player ' + i);
        container.appendChild(label);
        container.appendChild(input);
    }
    document.getElementById('startBtn').style.display = 'inline-block';
}
</script>
</body>
</html>
