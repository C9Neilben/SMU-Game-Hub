# SMU Game Hub

A PHP/MySQL web arcade portal built with VS Code and MAMP. Add self-contained games as folders, register them in the catalog, and they instantly appear on the homepage.

## Tech Stack

- **Backend:** PHP (PDO for MySQL)
- **Database:** MySQL (via MAMP)
- **Frontend:** Vanilla HTML/CSS/JavaScript (no frameworks)
- **Local server:** MAMP

## Folder Structure

```
smu-game-hub/
├── database/
│   └── schema.sql          # Full DB schema — run in phpMyAdmin to (re)build the database
├── hub/
│   ├── index.php           # Homepage — game catalog grid
│   ├── admin.php           # Add / edit / delete games in the catalog
│   ├── edit_game.php       # Edit an existing game's details/thumbnail
│   ├── delete_game.php     # Deletes a game (and its thumbnail file)
│   └── style.css
├── games/
│   ├── wordle/              # Word-guessing game
│   ├── tic-tac-toe/         # 2-player / vs-AI, with Best-of-3/5 match system
│   ├── memory-match/        # Photo-based card matching
│   ├── aim-trainer/         # Timed reflex/flick-click target practice
│   └── whack-a-mole/        # Timed photo-mole whacking, 12-hole grid
├── shared/
│   └── db.php               # Single shared PDO connection, used by hub + all games
└── assets/
    └── thumbs/               # Uploaded game thumbnail images
```

## Setup

1. **Start MAMP** and make sure your Apache/MySQL servers are running.
2. **Place the project** inside MAMP's `htdocs` folder, e.g. `htdocs/smu-game-hub/`.
3. **Create the database:**
   - Open phpMyAdmin (usually `http://localhost:8888/phpMyAdmin` — check your MAMP ports)
   - Go to the **SQL** tab
   - Paste the entire contents of `database/schema.sql` and click **Go**
   - This creates the `smu_game_hub` database along with the `games`, `aim_scores`, and `whack_scores` tables
4. **Check your MySQL credentials/port** in `shared/db.php` match your MAMP setup (defaults: user `root`, pass `root`, port `8889`).
5. **Visit the hub** at `http://localhost:8888/smu-game-hub/hub/index.php`.

## Adding a New Game

1. Create a new folder under `games/your-game-slug/`
2. Build the game as a self-contained PHP/JS/CSS unit inside that folder
3. Go to `hub/admin.php` and fill in the form:
   - **Title** — display name shown on the game card
   - **Slug** — must exactly match the folder name under `games/`
   - **Description** — short blurb shown on the card
   - **Thumbnail** — upload a square-ish image
4. The game instantly appears on the homepage — no other code changes needed

### Managing existing games
`hub/admin.php` also lists all currently added games with **Edit** and **Delete** buttons, so you can update details/thumbnails or remove a game (and its thumbnail file) without touching the database directly.

## Games Included

| Game | Description |
|---|---|
| **Wordle** | Classic 5-letter word guessing, 6 attempts, color-coded feedback |
| **Tic-Tac-Toe** | 2-player hotseat or vs-AI, with selectable Best-of-3/Best-of-5 match format and alternating first move each round |
| **Memory Match** | Flip cards to find matching photo pairs (4×4 grid, 8 pairs) |
| **Aim Trainer** | Click randomly-appearing photo targets before they vanish; choose a 15/30/60s session; scores saved to a leaderboard |
| **Whack-a-Mole** | Click the mole as it randomly pops up across a 12-hole grid, with randomized timing so it can't be predicted; scores saved to a leaderboard |

## Session Key Conventions

To avoid collisions between games sharing PHP sessions, each game prefixes its `$_SESSION` keys:
- Tic-Tac-Toe: `ttt_*`
- Memory Match: `mm_*`
- (Aim Trainer / Whack-a-Mole don't use sessions — game state lives entirely in JS, with only the final score sent to PHP)

## Notes

- No user accounts/login system — this is a shared local arcade, not a multi-user platform
- Leaderboards (Aim Trainer, Whack-a-Mole) are anonymous — players just type a name when saving a score
- Thumbnails are uploaded via `admin.php` and stored in `assets/thumbs/`; old thumbnails are automatically deleted when replaced or when a game is deleted
