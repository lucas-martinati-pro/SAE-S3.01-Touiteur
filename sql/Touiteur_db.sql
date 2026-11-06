-- MariaDB
-- Datas
DROP TABLE IF EXISTS users2tag;
DROP TABLE IF EXISTS note2touite;
DROP TABLE IF EXISTS tag2touite;
DROP TABLE IF EXISTS follow;
DROP TABLE IF EXISTS tag;
DROP TABLE IF EXISTS touite;
DROP TABLE IF EXISTS users;

create table touite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content VARCHAR(235) NOT NULL,
    note INT,
    media_description VARCHAR(235),
    media_path VARCHAR(256),
    posted_at DATETIME NOT NULL,
    users_id INT NOT NULL
);

create table users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(256) NOT NULL,
    passwd VARCHAR(256) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    counter_touite INT DEFAULT 0 NOT NULL,
    role INT(11) NOT NULL DEFAULT 1
);

create table tag (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL,
    description VARCHAR(256) NOT NULL
);

create table follow (
    follower_id INT NOT NULL,
    followed_id INT NOT NULL,
    PRIMARY KEY (follower_id, followed_id)
);

create table tag2touite (
    touite_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (touite_id, tag_id)
);

create table note2touite (
    users_id INT NOT NULL,
    touite_id INT NOT NULL,
    -- -1 : négatif, 1 : POSITIF
    state INT NOT NULL,
    PRIMARY KEY (users_id, touite_id)
);

create table users2tag (
    users_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (users_id, tag_id)
);

ALTER TABLE users
    ADD CONSTRAINT uq_users_email UNIQUE (email),
    ADD CONSTRAINT chk_counter_touite_positiv CHECK (counter_touite >= 0);
ALTER TABLE tag
    ADD CONSTRAINT uq_tag_libelle UNIQUE (libelle);
ALTER TABLE users2tag
    ADD CONSTRAINT fk_users2tag_user FOREIGN KEY (users_id) REFERENCES users(id),
    ADD CONSTRAINT fk_users2tag_tag FOREIGN KEY (tag_id) REFERENCES tag(id);
ALTER TABLE note2touite
    ADD CONSTRAINT fk_note_user FOREIGN KEY (users_id) REFERENCES users(id),
    ADD CONSTRAINT fk_note_touite FOREIGN KEY (touite_id) REFERENCES touite(id),
    ADD CONSTRAINT chk_note_bool CHECK (state IN (-1, 1));
ALTER TABLE tag2touite
    ADD CONSTRAINT fk_tag2touite_touite FOREIGN KEY (touite_id) REFERENCES touite(id),
    ADD CONSTRAINT fk_tag2touite_tag FOREIGN KEY (tag_id) REFERENCES tag(id);
ALTER TABLE follow
    ADD CONSTRAINT fk_follow_follower FOREIGN KEY (follower_id) REFERENCES users(id),
    ADD CONSTRAINT fk_follow_followed FOREIGN KEY (followed_id) REFERENCES users(id),
    ADD CONSTRAINT chk_no_self_follow CHECK (follower_id != followed_id);
ALTER TABLE touite
    ADD CONSTRAINT fk_touite_user FOREIGN KEY (users_id) REFERENCES users(id);

-- Déclencheurs MariaDB
DELIMITER //

CREATE TRIGGER count_touite_users_insert
AFTER INSERT ON touite
FOR EACH ROW
BEGIN
    UPDATE users
    SET counter_touite = counter_touite + 1
    WHERE id = NEW.users_id;
END//

CREATE TRIGGER count_touite_users_delete
AFTER DELETE ON touite
FOR EACH ROW
BEGIN
    UPDATE users
    SET counter_touite = counter_touite - 1
    WHERE id = OLD.users_id;
END//

-- Un utilisateur ne peut pas se suivre lui-même ou suivre un utilisateur ayant le même email :
-- (Une contrainte existe déjà)
CREATE TRIGGER trigger_no_self_follow_insert
BEFORE INSERT ON follow
FOR EACH ROW
BEGIN
    IF NEW.follower_id = NEW.followed_id THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Un utilisateur ne peut pas se suivre lui-meme';
    END IF;
END//

CREATE TRIGGER trigger_no_self_follow_update
BEFORE UPDATE ON follow
FOR EACH ROW
BEGIN
    IF NEW.follower_id = NEW.followed_id THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Un utilisateur ne peut pas se suivre lui-meme';
    END IF;
END//

-- Empêcher la modification de l’auteur d’un touite : une fois publié, un touite conserve son auteur initial. Il est interdit de transférer une publication à un autre utilisateur :
CREATE TRIGGER not_edit_author
BEFORE UPDATE ON touite
FOR EACH ROW
BEGIN
    IF OLD.users_id != NEW.users_id THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Il est interdit de transferer une publication a un autre utilisateur';
    END IF;
END//

-- Un utilisateur est limité à 50 touites par jour, pour éviter que des bots ne saturent la plateforme :
CREATE TRIGGER limite_50_touites
BEFORE INSERT ON touite
FOR EACH ROW
BEGIN
    DECLARE nb_touites INT;

    SELECT COUNT(*)
    INTO nb_touites
    FROM touite
    WHERE users_id = NEW.users_id
    AND posted_at = NEW.posted_at;

    IF nb_touites >= 50 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Un utilisateur ne peut pas publier plus de 50 touites par jour';
    END IF;
END//

-- Mettre à jour automatiquement la note d'un touite lors de l'ajout d'une note :
CREATE TRIGGER update_note_insert
AFTER INSERT ON note2touite
FOR EACH ROW
BEGIN
    UPDATE touite
    SET note = COALESCE(note, 0) + NEW.state
    WHERE id = NEW.touite_id;
END//

-- Prendre en compte le remplacement d'une note existante :
CREATE TRIGGER update_note_update
AFTER UPDATE ON note2touite
FOR EACH ROW
BEGIN
    UPDATE touite
    SET note = COALESCE(note, 0) + NEW.state - OLD.state
    WHERE id = NEW.touite_id;
END//

-- Prendre en compte l'annulation d'une note :
CREATE TRIGGER update_note_delete
AFTER DELETE ON note2touite
FOR EACH ROW
BEGIN
    UPDATE touite
    SET note = COALESCE(note, 0) - OLD.state
    WHERE id = OLD.touite_id;
END//

DELIMITER ;
