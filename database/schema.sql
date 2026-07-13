CREATE DATABASE IF NOT EXISTS smu_game_hub;
USE smu_game_hub;

CREATE TABLE games (
    game_id     INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255),
    thumbnail   VARCHAR(255),          -- e.g. 'assets/thumbs/wordle.png'
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);