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

USE smu_game_hub;

CREATE TABLE aim_scores (
    score_id     INT AUTO_INCREMENT PRIMARY KEY,
    player_name  VARCHAR(30) NOT NULL,
    score        INT NOT NULL,
    accuracy     DECIMAL(5,2) NOT NULL,
    duration     INT NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);