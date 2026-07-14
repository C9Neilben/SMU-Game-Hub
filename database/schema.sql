-- SMU Game Hub — full database schema
-- Run this in phpMyAdmin's SQL tab to rebuild the entire database from scratch

CREATE DATABASE IF NOT EXISTS smu_game_hub;
USE smu_game_hub;

-- Game catalog (powers the hub homepage grid + admin panel)
CREATE TABLE IF NOT EXISTS games (
    game_id     INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255),
    thumbnail   VARCHAR(255),
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Aim Trainer leaderboard
CREATE TABLE IF NOT EXISTS aim_scores (
    score_id     INT AUTO_INCREMENT PRIMARY KEY,
    player_name  VARCHAR(30) NOT NULL,
    score        INT NOT NULL,
    accuracy     DECIMAL(5,2) NOT NULL,
    duration     INT NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Whack-a-Mole leaderboard
CREATE TABLE IF NOT EXISTS whack_scores (
    score_id     INT AUTO_INCREMENT PRIMARY KEY,
    player_name  VARCHAR(30) NOT NULL,
    score        INT NOT NULL,
    accuracy     DECIMAL(5,2) NOT NULL,
    duration     INT NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);