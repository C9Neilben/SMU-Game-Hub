<?php
session_start();
require 'config.php';

if (!isset($_SESSION['game_id'])) {
    header('Location: index.php');
    exit;
}
$gameId = $_SESSION['game_id'];

$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$gameId]);
$game = $stmt->fetch();
if (!$game) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM players WHERE game_id = ? ORDER BY seat_order");
$stmt->execute([$gameId]);
$players = $stmt->fetchAll();
$numPlayers = count($players);
$isSolo = $numPlayers === 1;

$prompter = $players[$game['current_prompter_seat']];
$stage = $_SESSION['stage'] ?? 'category';

function scoreRound(PDO $pdo, int $roundId, int $prompterId): void {
    $stmt = $pdo->prepare("SELECT * FROM rounds WHERE id = ?");
    $stmt->execute([$roundId]);
    $round = $stmt->fetch();
    $promptAnswerLower = strtolower(trim($round['prompter_answer']));

    $stmt = $pdo->prepare("SELECT * FROM answers WHERE round_id = ?");
    $stmt->execute([$roundId]);
    $answers = $stmt->fetchAll();

    $promptGainsPoint = false;
    foreach ($answers as $a) {
        $isMatch = (strtolower(trim($a['answer_text'])) === $promptAnswerLower) ? 1 : 0;
        $pdo->prepare("UPDATE answers SET is_match = ? WHERE id = ?")->execute([$isMatch, $a['id']]);
        if ($isMatch) {
            $promptGainsPoint = true;
        } else {
            $pdo->prepare("UPDATE players SET score = score + 1 WHERE id = ?")->execute([$a['player_id']]);
        }
    }
    if ($promptGainsPoint) {
        $pdo->prepare("UPDATE players SET score = score + 1 WHERE id = ?")->execute([$prompterId]);
    }
}

// ---- Handle actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($stage === 'category' && isset($_POST['category'], $_POST['prompter_answer'])) {
        $category = trim($_POST['category']);
        $answer = trim($_POST['prompter_answer']);

        if ($category !== '' && $answer !== '') {
            $stmt = $pdo->prepare("INSERT INTO rounds (game_id, round_number, prompter_player_id, category, prompter_answer) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$gameId, $game['round_number'], $prompter['id'], $category, $answer]);
            $_SESSION['current_round_id'] = $pdo->lastInsertId();

            if ($isSolo) {
                // Solo mode: avoid repeating any answer you've given before in this game
                $stmt = $pdo->prepare("SELECT answer_text FROM answers a JOIN rounds r ON a.round_id = r.id WHERE r.game_id = ? AND a.player_id = ?");
                $stmt->execute([$gameId, $prompter['id']]);
                $pastAnswers = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
                $isRepeat = in_array(strtolower($answer), $pastAnswers, true);

                $stmt = $pdo->prepare("INSERT INTO answers (round_id, player_id, answer_text, is_match) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['current_round_id'], $prompter['id'], $answer, $isRepeat ? 1 : 0]);

                if (!$isRepeat) {
                    $pdo->prepare("UPDATE players SET score = score + 1 WHERE id = ?")->execute([$prompter['id']]);
                }
                $_SESSION['stage'] = 'reveal';
            } else {
                $order = [];
                foreach ($players as $p) {
                    if ($p['id'] != $prompter['id']) $order[] = $p['id'];
                }
                $_SESSION['turn_order'] = $order;
                $_SESSION['turn_index'] = 0;
                $_SESSION['stage'] = 'answering';
            }
        }
        header('Location: game.php');
        exit;
    }

    if ($stage === 'answering' && isset($_POST['answer'])) {
        $answer = trim($_POST['answer']);
        $currentPlayerId = $_SESSION['turn_order'][$_SESSION['turn_index']];
        if ($answer !== '') {
            $stmt = $pdo->prepare("INSERT INTO answers (round_id, player_id, answer_text) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['current_round_id'], $currentPlayerId, $answer]);
            $_SESSION['turn_index']++;
            if ($_SESSION['turn_index'] >= count($_SESSION['turn_order'])) {
                scoreRound($pdo, $_SESSION['current_round_id'], $prompter['id']);
                $_SESSION['stage'] = 'reveal';
            }
        }
        header('Location: game.php');
        exit;
    }

    if ($stage === 'reveal' && isset($_POST['next_round'])) {
        $nextSeat = ($game['current_prompter_seat'] + 1) % $numPlayers;
        $pdo->prepare("UPDATE games SET current_prompter_seat = ?, round_number = round_number + 1 WHERE id = ?")
            ->execute([$nextSeat, $gameId]);
        unset($_SESSION['turn_order'], $_SESSION['turn_index'], $_SESSION['current_round_id']);
        $_SESSION['stage'] = 'category';
        header('Location: game.php');
        exit;
    }

    if ($stage === 'reveal' && isset($_POST['end_game'])) {
        $_SESSION['stage'] = 'final';
        header('Location: game.php');
        exit;
    }
}

$stage = $_SESSION['stage'] ?? 'category'; // refresh after possible update
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Avoid Saying the Same Thing</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">

    <div class="scoreboard">
        <?php foreach ($players as $p): ?>
            <span class="score-pill<?= ($p['id'] == $prompter['id'] && $stage !== 'final') ? ' prompter' : '' ?>">
                <?= htmlspecialchars($p['name']) ?>: <?= $p['score'] ?>
            </span>
        <?php endforeach; ?>
    </div>

    <?php if ($stage === 'category'): ?>

        <h2>Round <?= $game['round_number'] ?> — Pass the device to <?= htmlspecialchars($prompter['name']) ?></h2>
        <p>
            <?php if ($isSolo): ?>
                You're up! Give yourself a category and an answer — try not to repeat a past answer.
            <?php else: ?>
                🤫 <?= htmlspecialchars($prompter['name']) ?> is the secret Prompter this round.
                Everyone else will try to avoid matching your answer!
            <?php endif; ?>
        </p>
        <form method="POST">
            <label>Category / Question:</label>
            <input type="text" name="category" placeholder="e.g. Name a zoo animal" required autofocus>
            <label>Your secret answer:</label>
            <input type="text" name="prompter_answer" placeholder="Only you can see this right now" required>
            <button type="submit">Lock it in</button>
        </form>

    <?php elseif ($stage === 'answering'):
        $currentPlayerId = $_SESSION['turn_order'][$_SESSION['turn_index']];
        $currentPlayer = null;
        foreach ($players as $p) {
            if ($p['id'] == $currentPlayerId) $currentPlayer = $p;
        }
        $stmt = $pdo->prepare("SELECT category FROM rounds WHERE id = ?");
        $stmt->execute([$_SESSION['current_round_id']]);
        $category = $stmt->fetchColumn();
    ?>

        <h2>Pass the device to <?= htmlspecialchars($currentPlayer['name']) ?></h2>
        <p class="category-box">Category: <strong><?= htmlspecialchars($category) ?></strong></p>
        <form method="POST">
            <label>Your answer (avoid matching the Prompter's secret answer!):</label>
            <input type="text" name="answer" required autofocus>
            <button type="submit">Submit</button>
        </form>
        <p class="hint">Player <?= $_SESSION['turn_index'] + 1 ?> of <?= count($_SESSION['turn_order']) ?></p>

    <?php elseif ($stage === 'reveal'):
        $stmt = $pdo->prepare("SELECT * FROM rounds WHERE id = ?");
        $stmt->execute([$_SESSION['current_round_id']]);
        $round = $stmt->fetch();
        $stmt = $pdo->prepare("SELECT a.*, p.name FROM answers a JOIN players p ON a.player_id = p.id WHERE a.round_id = ?");
        $stmt->execute([$_SESSION['current_round_id']]);
        $answers = $stmt->fetchAll();
    ?>

        <h2>Reveal!</h2>
        <p class="category-box">Category: <strong><?= htmlspecialchars($round['category']) ?></strong></p>
        <p class="prompter-answer">
            🤫 <?= htmlspecialchars($prompter['name']) ?>'s secret answer was:
            <strong><?= htmlspecialchars($round['prompter_answer']) ?></strong>
        </p>
        <ul class="answer-list">
            <?php foreach ($answers as $a): ?>
                <li class="<?= $a['is_match'] ? 'match' : 'safe' ?>">
                    <?= htmlspecialchars($a['name']) ?>: "<?= htmlspecialchars($a['answer_text']) ?>"
                    — <?= $a['is_match'] ? ($isSolo ? '🔁 Repeat!' : '💥 Matched!') : '✅ Safe!' ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <form method="POST" style="display:inline;">
            <button type="submit" name="next_round" value="1">Next Round ➜</button>
        </form>
        <form method="POST" style="display:inline;">
            <button type="submit" name="end_game" value="1" class="secondary">End Game</button>
        </form>

    <?php elseif ($stage === 'final'):
        $sorted = $players;
        usort($sorted, fn($a, $b) => $b['score'] - $a['score']);
    ?>

        <h2>🏆 Final Scores</h2>
        <ol class="final-list">
            <?php foreach ($sorted as $p): ?>
                <li><?= htmlspecialchars($p['name']) ?> — <?= $p['score'] ?> pts</li>
            <?php endforeach; ?>
        </ol>
        <form action="reset.php" method="POST">
            <button type="submit">Play Again</button>
        </form>

    <?php endif; ?>

</div>
</body>
</html>
