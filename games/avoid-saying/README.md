# Avoid Saying the Same Thing — Party Game

A local "pass the device" party game built with PHP + MySQL for MAMP.

## How it plays

- 1–7 players, hotseat style (one device passed around).
- Each round, one player is secretly the **Prompter**: they type a category
  (e.g. "Name a zoo animal") and a hidden answer.
- Everyone else answers the same category, trying to land on something
  **different** from the Prompter's hidden answer.
- On reveal: anyone who *matched* the Prompter gets called out (💥), and the
  Prompter scores a point for every match. Everyone who stayed safe (✅)
  scores a point too.
- The Prompter role rotates each round.
- **1-player mode** is a solo variant: try not to repeat any answer you've
  already given earlier in the same game.

## Setup in MAMP + VS Code

1. **Copy the project folder** (`avoid-game/`) into MAMP's `htdocs` directory.
   - macOS default: `/Applications/MAMP/htdocs/`
   - Windows default: `C:\MAMP\htdocs\`

2. **Start MAMP** (Start Servers button — Apache + MySQL).

3. **Create the database.**
   - Open `http://localhost:8888/phpMyAdmin/` (or click "Open WebStart page" in MAMP, then phpMyAdmin).
   - Go to the **SQL** tab, paste the contents of `schema.sql`, and run it.
   - This creates the `avoid_game` database and its tables.

4. **Check your ports in `config.php`.**
   - MAMP's MySQL port is usually `8889` (sometimes `3306`) and Apache is usually `8888`.
   - Confirm yours in the MAMP app under **Preferences → Ports**, and update
     `$DB_PORT` in `config.php` if it's different.

5. **Open the project in VS Code** to browse/edit the code:
   ```
   code /Applications/MAMP/htdocs/avoid-game
   ```
   The PHP Intelephense extension is handy for autocomplete, but not required.

6. **Play the game** by visiting:
   ```
   http://localhost:8888/avoid-game/
   ```
   (adjust the port to match your Apache setting).

## File overview

| File          | Purpose                                             |
|---------------|------------------------------------------------------|
| `schema.sql`  | Creates the database and tables                     |
| `config.php`  | Database connection settings                        |
| `index.php`   | Setup screen — pick player count & names             |
| `start.php`   | Creates the game + players in the DB                |
| `game.php`    | Main game loop (category → answering → reveal)       |
| `reset.php`   | Ends the session so you can start a new game         |
| `style.css`   | Styling                                              |

## Extending it

Some easy next steps if you want to keep building:
- A "skip category" button with a built-in list of preset categories.
- A timer per turn for extra pressure.
- Fuzzy matching (e.g. "Lion" vs "lions") instead of exact string match.
- A `games` history page showing past sessions from the database.
