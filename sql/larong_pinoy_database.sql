-- ============================================================
-- LARONG PINOY PRESERVATION INFORMATION SYSTEM
-- Database Setup Script with Risk Analysis
-- Student: Catherine R. Bongaos
-- ============================================================

-- Drop database if exists and create new
DROP DATABASE IF EXISTS larong_pinoy_db;
CREATE DATABASE larong_pinoy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE larong_pinoy_db;

-- ============================================================
-- 1. USER MANAGEMENT (with Profile View feature)
-- ============================================================

CREATE TABLE User_Account (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    bio TEXT,
    profile_picture VARCHAR(255) DEFAULT 'default_avatar.png',
    location VARCHAR(100),
    birthdate DATE,
    favorite_game_id INT,
    games_played_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    role ENUM('admin', 'user') DEFAULT 'user',
    account_status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- ============================================================
-- 2. GAME MASTER DATA
-- ============================================================

CREATE TABLE Traditional_Game (
    game_id INT AUTO_INCREMENT PRIMARY KEY,
    game_name VARCHAR(100) NOT NULL,
    game_description TEXT,
    game_rules TEXT,
    setup_instructions TEXT,
    how_to_win TEXT,
    play_environment ENUM('Indoor', 'Outdoor', 'Both') NOT NULL,
    frequency_of_practice ENUM('Frequent', 'Occasional', 'Rare', 'Extinct') DEFAULT 'Occasional',
    video_link VARCHAR(255),
    image_gallery JSON,
    cultural_significance TEXT,
    origin_region VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE Physical_Requirement (
    requirement_id INT AUTO_INCREMENT PRIMARY KEY,
    physical_demand_level INT NOT NULL CHECK (physical_demand_level BETWEEN 1 AND 3),
    level_description VARCHAR(50),
    details TEXT
);

CREATE TABLE Equipment_Requirement (
    equipment_id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_level INT NOT NULL CHECK (equipment_level BETWEEN 0 AND 3),
    level_description VARCHAR(50),
    equipment_description TEXT
);

CREATE TABLE Age_Bracket (
    age_id INT AUTO_INCREMENT PRIMARY KEY,
    age_range VARCHAR(50) NOT NULL,
    age_description VARCHAR(100)
);

-- ============================================================
-- 3. JUNCTION TABLES (Many-to-Many Relationships)
-- ============================================================

CREATE TABLE Game_Requirement (
    game_req_id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    requirement_id INT,
    equipment_id INT,
    FOREIGN KEY (game_id) REFERENCES Traditional_Game(game_id) ON DELETE CASCADE,
    FOREIGN KEY (requirement_id) REFERENCES Physical_Requirement(requirement_id),
    FOREIGN KEY (equipment_id) REFERENCES Equipment_Requirement(equipment_id)
);

CREATE TABLE Game_Age (
    game_age_id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    age_id INT NOT NULL,
    FOREIGN KEY (game_id) REFERENCES Traditional_Game(game_id) ON DELETE CASCADE,
    FOREIGN KEY (age_id) REFERENCES Age_Bracket(age_id)
);

-- ============================================================
-- 4. RISK ANALYSIS SYSTEM
-- ============================================================

CREATE TABLE Risk_Assessment (
    risk_id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    risk_level ENUM('Low', 'Medium', 'High') NOT NULL,
    risk_score INT NOT NULL CHECK (risk_score BETWEEN 0 AND 100),
    risk_basis TEXT,
    physical_demand_factor INT DEFAULT 0,
    environment_factor INT DEFAULT 0,
    equipment_factor INT DEFAULT 0,
    frequency_factor INT DEFAULT 0,
    assessment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assessed_by INT,
    FOREIGN KEY (game_id) REFERENCES Traditional_Game(game_id) ON DELETE CASCADE,
    FOREIGN KEY (assessed_by) REFERENCES User_Account(user_id)
);

CREATE TABLE Preservation_Status (
    status_id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    preservation_status ENUM('Thriving', 'Stable', 'Declining', 'Critical', 'Extinct') DEFAULT 'Stable',
    last_documented DATE,
    documented_by INT,
    community_vitality_score INT CHECK (community_vitality_score BETWEEN 0 AND 100),
    notes TEXT,
    FOREIGN KEY (game_id) REFERENCES Traditional_Game(game_id) ON DELETE CASCADE,
    FOREIGN KEY (documented_by) REFERENCES User_Account(user_id)
);

-- ============================================================
-- 5. USER ENGAGEMENT FEATURES
-- ============================================================

CREATE TABLE Comment (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    comment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    comment_status ENUM('approved') NOT NULL DEFAULT 'approved',
    likes_count INT DEFAULT 0,
    parent_comment_id INT NULL,
    FOREIGN KEY (game_id) REFERENCES Traditional_Game(game_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES User_Account(user_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_comment_id) REFERENCES Comment(comment_id)
);

CREATE TABLE User_Favorite (
    fav_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    saved_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES User_Account(user_id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES Traditional_Game(game_id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, game_id)
);

CREATE TABLE Play_Log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    played_date DATE NOT NULL,
    location VARCHAR(255),
    players_count INT,
    duration_minutes INT,
    enjoyment_rating INT CHECK (enjoyment_rating BETWEEN 1 AND 5),
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES User_Account(user_id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES Traditional_Game(game_id) ON DELETE CASCADE
);

CREATE TABLE User_Activity_Log (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('login', 'view_game', 'play_game', 'comment', 'favorite', 'share') NOT NULL,
    game_id INT,
    details TEXT,
    activity_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User_Account(user_id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES Traditional_Game(game_id)
);

-- ============================================================
-- INSERT REFERENCE DATA
-- ============================================================

-- Physical Demand Levels
INSERT INTO Physical_Requirement (physical_demand_level, level_description, details) VALUES
(1, 'Low', 'Minimal physical activity, can be played while seated or standing still'),
(2, 'Medium', 'Moderate movement, walking, light running, or hand-eye coordination'),
(3, 'High', 'Intense physical activity, running, jumping, climbing, or sustained exertion');

-- Equipment Levels
INSERT INTO Equipment_Requirement (equipment_level, level_description, equipment_description) VALUES
(0, 'None', 'No equipment needed, uses body or natural surroundings'),
(1, 'Minimal', 'Simple items like chalk, stones, or readily available objects'),
(2, 'Moderate', 'Specific items like slippers, cans, or improvised materials'),
(3, 'Specialized', 'Custom-made equipment like sungka boards, sipa, or traditional items');

-- Age Brackets
INSERT INTO Age_Bracket (age_range, age_description) VALUES
('Children (5-12)', 'Elementary school age, basic motor skills'),
('Teens (13-17)', 'Secondary school age, developed coordination'),
('Adults (18+)', 'Fully developed physical capability'),
('All Ages', 'Suitable for any age group');

-- Admin User (password: admin123 - hashed)
INSERT INTO User_Account (username, email, password_hash, first_name, last_name, role, account_status) VALUES
('admin', 'admin@larongpinoy.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active');

-- Sample Regular Users
INSERT INTO User_Account (username, email, password_hash, first_name, last_name, location, bio, games_played_count) VALUES
('maria_santos', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria', 'Santos', 'Manila', 'Loves traditional games from my childhood!', 5),
('juan_cruz', 'juan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan', 'Cruz', 'Cebu', 'Preserving culture one game at a time', 8),
('lola_remedios', 'lola@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Remedios', 'Garcia', 'Bicol', 'Grandmother sharing traditional games', 15);

-- ============================================================
-- INSERT 50 TRADITIONAL GAMES (from your Excel file)
-- ============================================================

INSERT INTO Traditional_Game (game_name, game_description, game_rules, setup_instructions, how_to_win, play_environment, frequency_of_practice, video_link, cultural_significance, origin_region) VALUES
('Patintero', 'A Filipino tag-and-crossing game played on a rectangular grid drawn on the ground.', 
'1. Guards stay on the lines of the grid. 2. Runners wait at the starting side. 3. On the signal, runners try to cross to the opposite side without being tagged. 4. Guards move only along the lines and try to block or tag runners. 5. A tagged runner is out or switches role. 6. The round continues until all runners are tagged or time ends.',
'Two teams are formed. A rectangular grid is drawn using chalk or tape. One team becomes guards, the other runners.',
'Runners win by crossing and returning successfully more often than the guards stop them.',
'Outdoor', 'Frequent', 'Search: Patintero tutorial',
'Teaches teamwork, strategy, and agility. Often played during recess and barangay fiestas.',
'Tagalog Region'),

('Tumbang Preso', 'A traditional game where players throw slippers to knock down a can while avoiding being tagged.',
'1. Place the can in the center. 2. Choose one guard near the can. 3. Other players stand outside the throwing area. 4. Players take turns throwing slippers at the can. 5. If the can falls, the guard must reset it quickly. 6. While the guard resets, players try to retrieve slippers. 7. The guard may tag players who get too close. 8. A tagged player may become the next guard.',
'One empty can is placed upright. Each player has a slipper. One player is chosen as guard.',
'Players win by knocking down the can and retrieving slippers without being tagged.',
'Outdoor', 'Frequent', 'Search: Tumbang Preso tutorial',
'Develops accuracy and quick reflexes. Popular street game using everyday items.',
'Nationwide'),

('Luksong Tinik', 'A jumping game where players leap over a growing human obstacle made from hands and feet.',
'1. Two players create the first barrier by placing hands and feet together. 2. The jumper stands behind the starting point. 3. The jumper must clear the obstacle without touching it. 4. After a successful jump, the barrier becomes higher or harder. 5. The next player takes a turn. 6. If a player touches the barrier, that player may lose the turn or be out. 7. The game continues as the barrier grows more difficult.',
'Two players form the tinik. The other players line up and take turns jumping.',
'The winner is the player who clears the highest barrier successfully.',
'Outdoor', 'Occasional', 'Search: Luksong Tinik tutorial',
'Builds trust, cooperation, and physical courage. The tinik players must work together.',
'Tagalog Region'),

('Luksong Baka', 'A jumping game where players leap over a crouching player acting as the obstacle.',
'1. The first player crouches with the back facing the jumpers. 2. Other players line up and take turns jumping over the crouched player. 3. Each successful round may make the obstacle harder by lowering or changing position. 4. Jumpers must clear the body without touching it. 5. A player who touches the obstacle may be out or lose the turn. 6. The game continues until players can no longer jump safely.',
'One player crouches low as the baka, and the others line up behind a starting point.',
'The winner is the player who successfully clears the highest or most difficult jump.',
'Outdoor', 'Occasional', 'Search: Luksong Baka tutorial',
'Similar to Luksong Tinik but uses one person as obstacle. Teaches bravery and respect.',
'Nationwide'),

('Piko', 'A Filipino hopscotch game using a drawn grid and a small marker.',
'1. Draw the piko boxes on the ground. 2. The first player throws the marker into the first square. 3. The player hops through the boxes, skipping the square with the marker. 4. The player must keep balance, usually on one foot for single boxes. 5. At the far end, the player turns around and returns. 6. On the return trip, the player picks up the marker without stepping on the lines. 7. The next player repeats the same process on the next square.',
'A hopscotch pattern is drawn on the ground. Players use a small stone, piece of tile, or marker.',
'The winner is the player who completes all assigned squares with the fewest mistakes.',
'Outdoor', 'Frequent', 'Search: Piko tutorial',
'Develops balance, coordination, and patience. Often played by young girls.',
'Nationwide'),

('Sipa', 'A kicking game where players keep a small object airborne using the feet.',
'1. The player drops or starts the sipa from hand level. 2. The player kicks it upward with the foot. 3. The goal is to keep it in the air for as many kicks as possible. 4. The player may use alternating feet depending on the agreed rules. 5. If the sipa touches the ground, the round ends. 6. In group play, turns may continue until every player has played.',
'Players use a sipa or improvised kicker made from washer, straw, or string.',
'The winner is the player with the highest number of consecutive kicks.',
'Outdoor', 'Occasional', 'Search: Sipa tutorial',
'National sport of the Philippines in traditional form. Develops foot-eye coordination.',
'Nationwide'),

('Sungka', 'A traditional Filipino board game where shells are moved around a board with holes.',
'1. Fill the small houses on the board with equal pieces. 2. Leave the scoring houses empty at the start. 3. The first player chooses one house on their side and picks up all its pieces. 4. The pieces are sown one by one into the following holes in order. 5. The player continues around the board according to the rules. 6. Special capture or extra-turn rules apply depending on where the last piece lands. 7. The turn ends after the move is complete. 8. The game continues until one side is empty or the board condition ends the game.',
'The game uses a sungka board with small holes and shells, stones, or seeds.',
'The winner is the player who collects the most pieces in the scoring house.',
'Both', 'Occasional', 'https://www.youtube.com/watch?v=A5HoS5AjfeE',
'Ancient game with roots in Southeast Asian culture. Teaches strategy and counting.',
'Nationwide'),

('Jack en Poy', 'The Filipino version of rock-paper-scissors.',
'1. Players count together or use a chant to prepare the throw. 2. On the final signal, both players show rock, paper, or scissors at the same time. 3. Rock beats scissors, scissors beat paper, and paper beats rock. 4. If both players show the same sign, the round is repeated. 5. The winner of each round may earn a point or determine who goes first in another game.',
'No special equipment is needed. Two players face each other.',
'The winner is the player who wins the required number of rounds.',
'Both', 'Frequent', 'Search: Jack en Poy tutorial',
'Universal decision-making game. Often used to settle disputes or choose teams.',
'Nationwide'),

('Agawan Base', 'A team game where players try to capture the opponent\'s base.',
'1. Mark the two bases clearly. 2. Assign each team its own base to protect. 3. On the signal, players may run into the opponent\'s territory. 4. Attackers try to reach and capture the enemy base. 5. Defenders try to tag attackers or stop them from entering. 6. A player caught inside enemy territory may be tagged out or held depending on the rule set. 7. The round continues until one team captures the base or completes the agreed condition.',
'Two bases are marked on opposite sides. Two teams are formed.',
'The winner is the team that captures the enemy base or meets the objective first.',
'Outdoor', 'Occasional', 'Search: Agawan Base tutorial',
'Teaches strategy, teamwork, and territorial defense. Popular during school events.',
'Nationwide'),

('Agawang Panyo', 'A chase game centered on grabbing a handkerchief first.',
'1. Divide the group into two teams and assign numbers to players. 2. Place a handkerchief on the center line or at a marked point. 3. The caller announces a number. 4. The two players with the called number run toward the handkerchief. 5. The first one to grab it must run back to their side without being tagged. 6. If the player is tagged before returning, the opposing team may get the point. 7. The round repeats with another number.',
'Two teams line up facing each other, with a handkerchief placed in the middle.',
'The winner is the team with the most successful grabs and returns.',
'Outdoor', 'Occasional', 'Search: Agawang Panyo tutorial',
'Develops speed and reflexes. Often played during PE classes and barangay events.',
'Nationwide');

-- Continue with remaining games (simplified for brevity, add more as needed)
INSERT INTO Traditional_Game (game_name, game_description, game_rules, setup_instructions, how_to_win, play_environment, frequency_of_practice, cultural_significance, origin_region) VALUES
('Agawang Sulok', 'Players race to claim corners while avoiding being tagged.', 'Standard chase and tag mechanics.', 'Mark four corners. One player is it.', 'Claim the most corners.', 'Outdoor', 'Occasional', 'Teaches spatial awareness.', 'Nationwide'),
('Taguan', 'Classic hide and seek game.', 'One player counts while others hide. Seeker finds hidden players.', 'Choose a seeker and set boundaries.', 'Last hider found wins or seeker finds all.', 'Outdoor', 'Frequent', 'Develops stealth and patience.', 'Nationwide'),
('Habulan', 'Simple chase and tag game.', 'One player is it and chases others. Tagged player becomes new it.', 'Choose who is it.', 'Last player not tagged wins.', 'Outdoor', 'Frequent', 'Basic running and evasion game.', 'Nationwide'),
('Lawin at Sisiw', 'Hawk and chicken game with protective roles.', 'The lawin tries to catch sisiw while the mother hen protects.', 'Assign roles: lawin, hen, chicks.', 'Lawin catches all chicks or time runs out.', 'Outdoor', 'Occasional', 'Teaches protection and strategy.', 'Nationwide'),
('Bulong Pari', 'Whispering game with a priest figure.', 'Players whisper messages around a circle.', 'Form a circle with one priest in center.', 'Message accuracy or elimination.', 'Both', 'Occasional', 'Develops listening skills.', 'Nationwide'),
('Pabitin', 'Suspended prizes game for fiestas.', 'Prizes hang from a grid. Players jump to grab them.', 'Set up grid with hanging prizes.', 'Grab the most prizes.', 'Outdoor', 'Occasional', 'Fiesta tradition, rewards agility.', 'Nationwide'),
('Pukpok Palayok', 'Pot hitting game with blindfold.', 'Blindfolded player breaks a clay pot with a stick.', 'Hang clay pot, blindfold player.', 'Break the pot to get prizes inside.', 'Outdoor', 'Occasional', 'Fiesta game, tests spatial sense.', 'Nationwide'),
('Palo Sebo', 'Greased pole climbing game.', 'Players climb a greased bamboo pole to get a prize.', 'Erect bamboo pole, grease it, attach prize.', 'First to reach the prize wins.', 'Outdoor', 'Rare', 'Traditional fiesta game, tests determination.', 'Nationwide'),
('Kadang-Kadang', 'Stilt walking race game.', 'Players walk on bamboo stilts and race.', 'Prepare bamboo stilts.', 'First to finish the race wins.', 'Outdoor', 'Rare', 'Develops balance and courage.', 'Visayas'),
('Luksong Lubid', 'Jump rope game with various patterns.', 'Players jump over a swinging rope.', 'Two players swing the ends of a long rope.', 'Complete the most jumps or complex patterns.', 'Outdoor', 'Occasional', 'Develops rhythm and timing.', 'Nationwide'),
('Holen', 'Marble shooting game.', 'Players shoot marbles to hit targets or other marbles.', 'Draw a circle or set up targets.', 'Collect the most marbles or hit targets.', 'Outdoor', 'Occasional', 'Develops precision and aim.', 'Nationwide'),
('Jolen', 'Filipino marble game variant.', 'Similar to holen with local rules.', 'Prepare playing area and marbles.', 'Win by collecting opponent marbles.', 'Outdoor', 'Occasional', 'Popular street game.', 'Nationwide'),
('Teks', 'Card flipping game using collectible cards.', 'Players flip cards to win opponent\'s cards.', 'Each player places cards on the ground.', 'Win the most cards by flipping.', 'Both', 'Occasional', 'Collectible card game culture.', 'Nationwide'),
('Chato', 'Local variant of tag or chase games.', 'Standard chase mechanics with local variations.', 'Set boundaries and choose it.', 'Win based on agreed conditions.', 'Outdoor', 'Occasional', 'Regional variation of tag.', 'Various'),
('Chinese Garter', 'Jumping game over a stretched garter.', 'Players jump over a garter held at increasing heights.', 'Two players hold ends of a garter.', 'Clear the highest height.', 'Outdoor', 'Occasional', 'Develops flexibility and jumping skill.', 'Nationwide'),
('Lastiko', 'Rubber band shooting game.', 'Players shoot rubber bands at targets.', 'Prepare rubber bands and targets.', 'Hit the most targets.', 'Outdoor', 'Rare', 'Simple projectile game.', 'Nationwide'),
('Ten-Two-Four', 'Card game with specific rules.', 'Follow the ten-two-four card rules.', 'Use standard deck of cards.', 'Win based on card combinations.', 'Both', 'Occasional', 'Social card game.', 'Nationwide'),
('Nanay, Tatay', 'Role-playing family game.', 'Children mimic family roles and activities.', 'Assign family roles.', 'Cooperative play, no winners.', 'Both', 'Occasional', 'Teaches family values.', 'Nationwide'),
('Siklot', 'Coin tossing and guessing game.', 'Players toss and guess coin positions.', 'Prepare coins and playing surface.', 'Correct guesses win points.', 'Both', 'Occasional', 'Develops probability sense.', 'Nationwide'),
('Baseball-bola', 'Filipino baseball variant.', 'Simplified baseball with local rules.', 'Prepare bat, ball, and bases.', 'Score the most runs.', 'Outdoor', 'Occasional', 'Adaptation of American baseball.', 'Nationwide'),
('Luksong Pari', 'Jumping over a crouching player.', 'Similar to luksong baka.', 'One player crouches as obstacle.', 'Clear the obstacle.', 'Outdoor', 'Occasional', 'Religious-themed variant name.', 'Nationwide'),
('Salakot Game', 'Hat tossing or balancing game.', 'Players toss or balance salakot hats.', 'Prepare salakot hats.', 'Best toss or longest balance wins.', 'Outdoor', 'Rare', 'Cultural hat tradition.', 'Nationwide'),
('Bati-kobra', 'Snake and strike game.', 'Players avoid the kobra or strike back.', 'Assign roles and set boundaries.', 'Eliminate the kobra or survive.', 'Outdoor', 'Occasional', 'Teaches evasion and courage.', 'Nationwide'),
('Tiyakad', 'Bamboo stilt racing game.', 'Race while walking on bamboo stilts.', 'Prepare bamboo stilts.', 'First to finish wins.', 'Outdoor', 'Rare', 'Tests balance and speed.', 'Nationwide'),
('Dampa', 'Finger wrestling game.', 'Players lock fingers and try to pin opponent.', 'Face each other and lock fingers.', 'Pin opponent\'s hand to surface.', 'Both', 'Occasional', 'Develops finger strength.', 'Nationwide'),
('Pato-patong', 'Duck duck goose variant.', 'Players sit in circle, one walks around tapping heads.', 'Form circle and choose walker.', 'Caught player becomes new walker.', 'Both', 'Occasional', 'Social circle game.', 'Nationwide'),
('Sikaran', 'Filipino foot fighting game.', 'Players kick at each other following rules.', 'Set boundaries and rules.', 'Score points or last standing wins.', 'Outdoor', 'Rare', 'Martial arts tradition.', 'Nationwide'),
('Sungkaban', 'Variant of sungka with different board.', 'Similar to sungka with local variations.', 'Prepare sungkaban board.', 'Collect the most pieces.', 'Both', 'Rare', 'Regional board game variant.', 'Various'),
('Dampa-dampa', 'Enhanced finger wrestling.', 'More complex finger wrestling rules.', 'Face each other, specific hand positions.', 'Win by pinning or technique.', 'Both', 'Rare', 'Advanced finger game.', 'Nationwide'),
('Sangkayaw', 'Group coordination game.', 'Players move in coordinated patterns.', 'Form groups and assign patterns.', 'Best coordination wins.', 'Outdoor', 'Occasional', 'Develops teamwork.', 'Nationwide'),
('Bahay-Bahayan', 'House role-playing game.', 'Children play house with assigned roles.', 'Set up pretend house area.', 'Cooperative imaginative play.', 'Both', 'Occasional', 'Teaches domestic roles.', 'Nationwide'),
('Pusoy ng Laro', 'Card game with bluffing.', 'Players bluff and bet on hands.', 'Use standard deck.', 'Best hand or best bluff wins.', 'Both', 'Occasional', 'Develops strategy and poker face.', 'Nationwide'),
('Sipa-Tira', 'Kicking and hitting game.', 'Kick and hit object to targets.', 'Prepare ball or object and targets.', 'Hit most targets.', 'Outdoor', 'Occasional', 'Foot coordination game.', 'Nationwide'),
('Tiyangge Taguan', 'Market-themed hide and seek.', 'Hide in market or simulated market area.', 'Set up market area with hiding spots.', 'Last found wins.', 'Both', 'Occasional', 'Market culture integration.', 'Nationwide'),
('Tuhog Game', 'Threading or skewering game.', 'Thread objects onto a stick or string.', 'Prepare stick/string and objects.', 'Thread the most objects fastest.', 'Both', 'Rare', 'Develops fine motor skills.', 'Nationwide'),
('Pasa-Pasa', 'Passing game with object or message.', 'Pass object around, eliminate on mistakes.', 'Form circle, prepare object.', 'Last player remaining wins.', 'Both', 'Occasional', 'Develops attention and speed.', 'Nationwide'),
('Tago-Taguan', 'Advanced hide and seek variant.', 'Complex hiding with multiple seekers.', 'Set large boundaries, multiple seekers.', 'Last hider wins.', 'Outdoor', 'Frequent', 'Popular group game.', 'Nationwide'),
('Bangka-Bangka', 'Boat racing game on land.', 'Players race while in boat positions.', 'Form teams in boat formations.', 'First team to finish wins.', 'Outdoor', 'Occasional', 'Simulates fishing boat racing.', 'Coastal Areas'),
('Karera ng Sakong', 'Slipper racing game.', 'Race while wearing oversized slippers.', 'Prepare large slippers.', 'First to finish wins.', 'Outdoor', 'Rare', 'Funny, develops balance.', 'Nationwide'),
('Limbo Rock', 'Limbo dancing game.', 'Dance under a lowering bar.', 'Hold bar, play music.', 'Clear the lowest bar.', 'Both', 'Occasional', 'Party game, flexibility test.', 'Nationwide');

-- ============================================================
-- LINK GAMES TO REQUIREMENTS (Sample for first 10 games)
-- ============================================================

INSERT INTO Game_Requirement (game_id, requirement_id, equipment_id) VALUES
(1, 3, 1),  -- Patintero: High physical, minimal equipment
(2, 3, 2),  -- Tumbang Preso: High physical, moderate equipment
(3, 3, 1),  -- Luksong Tinik: High physical, minimal equipment
(4, 3, 1),  -- Luksong Baka: High physical, minimal equipment
(5, 2, 1),  -- Piko: Medium physical, minimal equipment
(6, 2, 2),  -- Sipa: Medium physical, moderate equipment
(7, 1, 3),  -- Sungka: Low physical, specialized equipment
(8, 1, 1),  -- Jack en Poy: Low physical, no equipment
(9, 3, 1),  -- Agawan Base: High physical, minimal equipment
(10, 3, 1); -- Agawang Panyo: High physical, minimal equipment

-- Link age brackets (sample)
INSERT INTO Game_Age (game_id, age_id) VALUES
(1, 1), (1, 2),  -- Patintero: Children, Teens
(2, 1), (2, 2),  -- Tumbang Preso: Children, Teens
(3, 1), (3, 2),  -- Luksong Tinik: Children, Teens
(4, 1), (4, 2),  -- Luksong Baka: Children, Teens
(5, 1),          -- Piko: Children
(6, 1), (6, 2),  -- Sipa: Children, Teens
(7, 1), (7, 2), (7, 3), -- Sungka: All ages
(8, 4),          -- Jack en Poy: All ages
(9, 1), (9, 2),  -- Agawan Base: Children, Teens
(10, 1), (10, 2); -- Agawang Panyo: Children, Teens

-- ============================================================
-- RISK ASSESSMENTS (Auto-calculated based on factors)
-- ============================================================

-- Risk calculation: Physical(30%) + Environment(25%) + Equipment(25%) + Frequency(20%)
-- High physical=30, Outdoor=25, High equipment=25, Rare=20 = 100 (High)
-- Low physical=10, Indoor=10, No equipment=0, Frequent=0 = 20 (Low)

INSERT INTO Risk_Assessment (game_id, risk_level, risk_score, risk_basis, physical_demand_factor, environment_factor, equipment_factor, frequency_factor, assessed_by) VALUES
(1, 'High', 85, 'High physical demand, outdoor space required, frequent but declining in urban areas', 30, 25, 5, 25, 1),
(2, 'Medium', 60, 'High physical but uses common items, still played in provinces', 30, 25, 15, 10, 1),
(3, 'High', 80, 'High physical, outdoor, requires cooperative players, declining practice', 30, 25, 0, 25, 1),
(4, 'High', 75, 'Similar to Luksong Tinik but slightly more accessible', 30, 25, 0, 20, 1),
(5, 'Medium', 45, 'Medium physical, minimal equipment, still common in schools', 20, 25, 5, 5, 1),
(6, 'Medium', 55, 'Medium physical, needs specific equipment, occasional practice', 20, 25, 20, 10, 1),
(7, 'Low', 30, 'Low physical, indoor, specialized board but durable and passed down', 10, 10, 25, 5, 1),
(8, 'Low', 15, 'No equipment, any environment, very frequent, universal', 10, 10, 0, 0, 1),
(9, 'High', 70, 'High physical, large outdoor space, team coordination needed', 30, 25, 5, 10, 1),
(10, 'Medium', 50, 'High physical but simple setup, occasional play', 30, 25, 5, 10, 1);

-- ============================================================
-- PRESERVATION STATUS
-- ============================================================

INSERT INTO Preservation_Status (game_id, preservation_status, last_documented, documented_by, community_vitality_score, notes) VALUES
(1, 'Declining', '2024-01-15', 1, 45, 'Still played in rural areas but declining in cities due to lack of open space'),
(2, 'Stable', '2024-02-20', 1, 65, 'Common in barangays, easy to organize with minimal materials'),
(3, 'Declining', '2024-01-10', 1, 40, 'Requires cooperative players and open ground, harder to organize'),
(4, 'Declining', '2024-03-05', 1, 42, 'Similar challenges as Luksong Tinik'),
(5, 'Stable', '2024-02-28', 1, 70, 'Still popular in schools, easy to draw on pavement'),
(6, 'Declining', '2024-01-20', 1, 50, 'Equipment harder to find, replaced by modern sports'),
(7, 'Thriving', '2024-03-15', 1, 85, 'Strong cultural value, boards passed through generations'),
(8, 'Thriving', '2024-03-20', 1, 95, 'Universal, no barriers to play'),
(9, 'Declining', '2024-02-10', 1, 35, 'Large space requirements, safety concerns in urban areas'),
(10, 'Stable', '2024-02-25', 1, 60, 'Simple setup, still played during gatherings');

-- ============================================================
-- SAMPLE COMMENTS
-- ============================================================

INSERT INTO Comment (game_id, user_id, comment_text, comment_status, likes_count) VALUES
(1, 2, 'Naaalala ko pa noong bata ako, tuwing hapon naglalaro kami ng patintero sa kalsada. Sana hindi ito mawala.', 'approved', 12),
(6, 3, 'Tinuruan ko ang mga apo ko maglaro ng sipa. Masaya sila at nag-eenjoy! Dapat ipagpatuloy natin ito.', 'approved', 8),
(7, 2, 'Ang sungka ay hindi lang laro, ito ay pag-aaral ng diskarte at pagtitiis. Mahalaga ito sa ating kultura.', 'approved', 15),
(1, 4, 'We used to play this every recess! Now the schoolyard is too small. :(', 'approved', 5),
(3, 2, 'Mahirap ngayon maglaro ng Luksong Tinik kasi wala nang maluwag na lupa sa subdivision.', 'approved', 0);

-- ============================================================
-- SAMPLE USER ACTIVITIES (for Profile View)
-- ============================================================

INSERT INTO User_Favorite (user_id, game_id, notes) VALUES
(2, 1, 'My childhood favorite!'),
(2, 7, 'Love the strategy involved'),
(3, 6, 'Teaching this to my grandchildren'),
(3, 7, 'Passed down from my grandmother'),
(4, 1, 'Best game ever');

INSERT INTO Play_Log (user_id, game_id, played_date, location, players_count, duration_minutes, enjoyment_rating, notes) VALUES
(2, 1, '2024-03-01', 'Barangay Court, Manila', 8, 45, 5, 'So much fun with neighbors!'),
(2, 7, '2024-03-05', 'Home, Quezon City', 2, 30, 4, 'Played with my sister'),
(3, 6, '2024-03-10', 'Backyard, Bicol', 4, 20, 5, 'Teaching the kids'),
(4, 1, '2024-03-15', 'School Grounds', 10, 60, 5, 'Organized a tournament');

INSERT INTO User_Activity_Log (user_id, activity_type, game_id, details) VALUES
(2, 'login', NULL, 'User login'),
(2, 'view_game', 1, 'Viewed Patintero details'),
(2, 'play_game', 1, 'Logged Patintero play session'),
(2, 'comment', 1, 'Commented on Patintero'),
(3, 'login', NULL, 'User login'),
(3, 'view_game', 6, 'Viewed Sipa details'),
(3, 'play_game', 6, 'Logged Sipa play session'),
(4, 'login', NULL, 'User login'),
(4, 'favorite', 1, 'Added Patintero to favorites');

-- ============================================================
-- VIEWS FOR EASY QUERYING (Profile View Feature)
-- ============================================================

-- User Profile Summary View
CREATE VIEW vw_User_Profile AS
SELECT 
    u.user_id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.bio,
    u.profile_picture,
    u.location,
    u.birthdate,
    u.games_played_count,
    u.comments_count,
    u.role,
    u.created_at,
    u.last_login,
    (SELECT COUNT(*) FROM User_Favorite WHERE user_id = u.user_id) as favorites_count,
    (SELECT COUNT(*) FROM Play_Log WHERE user_id = u.user_id) as total_sessions
FROM User_Account u;

-- Game Detail View with Risk
CREATE VIEW vw_Game_Details AS
SELECT 
    g.game_id,
    g.game_name,
    g.game_description,
    g.play_environment,
    g.frequency_of_practice,
    p.physical_demand_level,
    p.level_description as physical_level_desc,
    e.equipment_level,
    e.level_description as equipment_level_desc,
    r.risk_level,
    r.risk_score,
    r.risk_basis,
    ps.preservation_status,
    ps.community_vitality_score
FROM Traditional_Game g
LEFT JOIN Game_Requirement gr ON g.game_id = gr.game_id
LEFT JOIN Physical_Requirement p ON gr.requirement_id = p.requirement_id
LEFT JOIN Equipment_Requirement e ON gr.equipment_id = e.equipment_id
LEFT JOIN Risk_Assessment r ON g.game_id = r.game_id
LEFT JOIN Preservation_Status ps ON g.game_id = ps.game_id;

-- User Dashboard View (for Profile Page)
CREATE VIEW vw_User_Dashboard AS
SELECT 
    u.user_id,
    u.username,
    u.profile_picture,
    COUNT(DISTINCT pl.game_id) as unique_games_played,
    COUNT(DISTINCT uf.game_id) as favorite_games_count,
    COUNT(DISTINCT c.comment_id) as total_comments,
    MAX(pl.played_date) as last_played_date
FROM User_Account u
LEFT JOIN Play_Log pl ON u.user_id = pl.user_id
LEFT JOIN User_Favorite uf ON u.user_id = uf.user_id
LEFT JOIN Comment c ON u.user_id = c.user_id
GROUP BY u.user_id, u.username, u.profile_picture;

-- ============================================================
-- STORED PROCEDURES
-- ============================================================

DELIMITER //

-- Calculate Risk Score Procedure
CREATE PROCEDURE sp_CalculateRiskScore(IN game_id_param INT)
BEGIN
    DECLARE phys_score INT;
    DECLARE env_score INT;
    DECLARE equip_score INT;
    DECLARE freq_score INT;
    DECLARE total_score INT;
    DECLARE risk_level VARCHAR(10);

    -- Get physical demand score (1=10, 2=20, 3=30)
    SELECT CASE p.physical_demand_level 
        WHEN 1 THEN 10 
        WHEN 2 THEN 20 
        WHEN 3 THEN 30 
    END INTO phys_score
    FROM Game_Requirement gr
    JOIN Physical_Requirement p ON gr.requirement_id = p.requirement_id
    WHERE gr.game_id = game_id_param;

    -- Get environment score (Indoor=10, Both=15, Outdoor=25)
    SELECT CASE g.play_environment 
        WHEN 'Indoor' THEN 10 
        WHEN 'Both' THEN 15 
        WHEN 'Outdoor' THEN 25 
    END INTO env_score
    FROM Traditional_Game g
    WHERE g.game_id = game_id_param;

    -- Get equipment score (0=0, 1=5, 2=15, 3=25)
    SELECT CASE e.equipment_level 
        WHEN 0 THEN 0 
        WHEN 1 THEN 5 
        WHEN 2 THEN 15 
        WHEN 3 THEN 25 
    END INTO equip_score
    FROM Game_Requirement gr
    JOIN Equipment_Requirement e ON gr.equipment_id = e.equipment_id
    WHERE gr.game_id = game_id_param;

    -- Get frequency score (Frequent=0, Occasional=10, Rare=20, Extinct=30)
    SELECT CASE g.frequency_of_practice 
        WHEN 'Frequent' THEN 0 
        WHEN 'Occasional' THEN 10 
        WHEN 'Rare' THEN 20 
        WHEN 'Extinct' THEN 30 
    END INTO freq_score
    FROM Traditional_Game g
    WHERE g.game_id = game_id_param;

    SET total_score = phys_score + env_score + equip_score + freq_score;

    -- Determine risk level
    SET risk_level = CASE 
        WHEN total_score >= 70 THEN 'High'
        WHEN total_score >= 40 THEN 'Medium'
        ELSE 'Low'
    END;

    -- Update or insert risk assessment
    INSERT INTO Risk_Assessment (game_id, risk_level, risk_score, risk_basis, 
        physical_demand_factor, environment_factor, equipment_factor, frequency_factor)
    VALUES (game_id_param, risk_level, total_score, 
        CONCAT('Auto-calculated: Physical=', phys_score, ', Env=', env_score, ', Equip=', equip_score, ', Freq=', freq_score),
        phys_score, env_score, equip_score, freq_score)
    ON DUPLICATE KEY UPDATE
        risk_level = risk_level,
        risk_score = total_score,
        risk_basis = CONCAT('Auto-calculated: Physical=', phys_score, ', Env=', env_score, ', Equip=', equip_score, ', Freq=', freq_score),
        physical_demand_factor = phys_score,
        environment_factor = env_score,
        equipment_factor = equip_score,
        frequency_factor = freq_score,
        assessment_date = CURRENT_TIMESTAMP;
END //

-- Update User Stats Procedure
CREATE PROCEDURE sp_UpdateUserStats(IN user_id_param INT)
BEGIN
    UPDATE User_Account u
    SET 
        games_played_count = (SELECT COUNT(DISTINCT game_id) FROM Play_Log WHERE user_id = user_id_param),
        comments_count = (SELECT COUNT(*) FROM Comment WHERE user_id = user_id_param)
    WHERE u.user_id = user_id_param;
END //

DELIMITER ;

-- ============================================================
-- TRIGGERS
-- ============================================================

DELIMITER //

-- Auto-update user stats when play is logged
CREATE TRIGGER trg_AfterPlayLogInsert
AFTER INSERT ON Play_Log
FOR EACH ROW
BEGIN
    CALL sp_UpdateUserStats(NEW.user_id);
END //

-- Auto-update user stats when a comment is added or removed
CREATE TRIGGER trg_AfterCommentInsert
AFTER INSERT ON Comment
FOR EACH ROW
BEGIN
    CALL sp_UpdateUserStats(NEW.user_id);
END //

CREATE TRIGGER trg_AfterCommentDelete
AFTER DELETE ON Comment
FOR EACH ROW
BEGIN
    CALL sp_UpdateUserStats(OLD.user_id);
END //

DELIMITER ;

-- ============================================================
-- FINAL SETUP
-- ============================================================

-- Run risk calculation for all games
CALL sp_CalculateRiskScore(1);
CALL sp_CalculateRiskScore(2);
CALL sp_CalculateRiskScore(3);
CALL sp_CalculateRiskScore(4);
CALL sp_CalculateRiskScore(5);
CALL sp_CalculateRiskScore(6);
CALL sp_CalculateRiskScore(7);
CALL sp_CalculateRiskScore(8);
CALL sp_CalculateRiskScore(9);
CALL sp_CalculateRiskScore(10);

-- Update all user stats
CALL sp_UpdateUserStats(2);
CALL sp_UpdateUserStats(3);
CALL sp_UpdateUserStats(4);

SELECT 'Database setup complete!' AS Status;
