-- MariaDB
-- Datas
DROP TABLE IF EXISTS user2tag;
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
    image_description VARCHAR(235),
    image_path VARCHAR(256),
    posted_at DATE NOT NULL,
    users_id INT NOT NULL
);

create table users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(256) NOT NULL,
    passwd VARCHAR(256) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    counter_touite INT DEFAULT 0 NOT NULL
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

create table user2tag (
    users_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (users_id, tag_id)
);

ALTER TABLE users
    ADD CONSTRAINT uq_users_email UNIQUE (email),
    ADD CONSTRAINT chk_counter_touite_positiv CHECK (counter_touite >= 0);
ALTER TABLE tag
    ADD CONSTRAINT uq_tag_libelle UNIQUE (libelle);
ALTER TABLE user2tag
    ADD CONSTRAINT fk_user2tag_user FOREIGN KEY (users_id) REFERENCES users(id),
    ADD CONSTRAINT fk_user2tag_tag FOREIGN KEY (tag_id) REFERENCES tag(id);
ALTER TABLE note2touite
    ADD CONSTRAINT fk_note_user FOREIGN KEY (users_id) REFERENCES users(id),
    ADD CONSTRAINT fk_note_touite FOREIGN KEY (touite_id) REFERENCES touite(id),
    ADD CONSTRAINT chk_note_bool CHECK (state IN (-1, 0, 1));
ALTER TABLE tag2touite
    ADD CONSTRAINT fk_tag2touite_touite FOREIGN KEY (touite_id) REFERENCES touite(id),
    ADD CONSTRAINT fk_tag2touite_tag FOREIGN KEY (tag_id) REFERENCES tag(id);
ALTER TABLE follow
    ADD CONSTRAINT fk_follow_follower FOREIGN KEY (follower_id) REFERENCES users(id),
    ADD CONSTRAINT fk_follow_followed FOREIGN KEY (followed_id) REFERENCES users(id),
    ADD CONSTRAINT chk_no_self_follow CHECK (follower_id != followed_id);
ALTER TABLE touite
    ADD CONSTRAINT fk_touite_user FOREIGN KEY (users_id) REFERENCES users(id);

-- Contenu : (Contenu généré à l'aide de l'IA)
INSERT INTO users (email, passwd, nom, prenom, counter_touite) VALUES ('user1@mail.com', '$2y$12$z7mHIyHACElCs8zE4hVvN.cPM4ala.FM6McIch4Zn7OakVGhMskvC', 'Martin', 'Lucas', 2);
INSERT INTO users (email, passwd, nom, prenom, counter_touite) VALUES ('user2@mail.com', '$2y$12$JelzicyqC10xiJz0i/JWUuUqFLBr5fhos2omEJoV68yGpQVp1.U1.', 'Durand', 'Emma', 2);
INSERT INTO users (email, passwd, nom, prenom, counter_touite) VALUES ('user3@mail.com', '$2y$12$KYcd2LqjSdH/wvGzv8qB.OZiyPPqno1ac4IoRjbVqErUP0M.6V8La', 'Bernard', 'Hugo', 2);
INSERT INTO users (email, passwd, nom, prenom, counter_touite) VALUES ('user4@mail.com', '$2y$12$NyyVwilJWJtwEcTUXwLWceGWaztT1PtPsyswXx0t8dbMW0O8tngcu', 'Petit', 'Chloe', 1);
INSERT INTO users (email, passwd, nom, prenom, counter_touite) VALUES ('admin@mail.com', '$2y$12$6KbKcQdgn.Sid6kvbunFmufU5d6KgArMGQ5EKXUnpwsQSOECmTv1W', 'Administrateur', 'Admin', 1);

INSERT INTO tag (id, libelle, description) VALUES (1, 'annonce', 'Informations et annonces importantes.');
INSERT INTO tag (id, libelle, description) VALUES (2, 'cinema', 'Films, series et actualites du cinema.');
INSERT INTO tag (id, libelle, description) VALUES (3, 'code', 'Programmation, developpement et informatique.');
INSERT INTO tag (id, libelle, description) VALUES (4, 'culture', 'Actualites et discussions culturelles.');
INSERT INTO tag (id, libelle, description) VALUES (5, 'musique', 'Musique, artistes et playlists.');
INSERT INTO tag (id, libelle, description) VALUES (6, 'nature', 'Nature, environnement et biodiversite.');
INSERT INTO tag (id, libelle, description) VALUES (7, 'sport', 'Sport, activite physique et competitions.');
INSERT INTO tag (id, libelle, description) VALUES (8, 'technologie', 'Technologies, numerique et innovations.');
INSERT INTO tag (id, libelle, description) VALUES (9, 'vacances', 'Voyages, loisirs et destinations.');
INSERT INTO tag (id, libelle, description) VALUES (10, 'lecture', 'Livres, auteurs et recommandations de lecture.');

INSERT INTO touite (users_id, content, note, image_description, image_path, posted_at) VALUES (1, 'Je commence un nouveau projet de code, motive pour apprendre !', 2, 'Illustration du projet de code.', 'image/favicon.png', '2026-09-01');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (2, 'Quel film vous a le plus marque cette annee ?', 1, '2026-09-02');
INSERT INTO touite (users_id, content, note, image_description, image_path, posted_at) VALUES (3, 'Petite randonnee sous le soleil : la nature fait du bien.', 0, 'Souvenir de randonnee.', 'image/favicon.png', '2026-09-03');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (4, 'Playlist du moment : beaucoup de musique et un peu de nostalgie.', 0, '2026-09-04');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (5, 'Maintenance du site samedi de 10h a 11h. Merci pour votre patience.', 1, '2026-09-05');
INSERT INTO touite (users_id, content, note, image_description, image_path, posted_at) VALUES (1, 'Objectif de la semaine : lire un livre et faire du sport.', 1, 'Illustration de lecture et de sport.', 'image/favicon.png', '2026-09-06');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (2, 'Des idees de destinations pour les prochaines vacances ?', 0, '2026-09-07');
INSERT INTO touite (users_id, content, note, image_description, image_path, posted_at) VALUES (3, 'Astuce technologie : pensez a sauvegarder vos fichiers importants.', 1, 'Illustration sur la technologie.', 'image/favicon.png', '2026-09-08');

INSERT INTO tag2touite (touite_id, tag_id) VALUES (1, 3);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (1, 8);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (2, 2);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (2, 4);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (3, 6);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (3, 7);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (4, 5);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (4, 4);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (5, 1);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (5, 8);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (6, 10);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (6, 7);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (7, 9);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (7, 6);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (8, 8);
INSERT INTO tag2touite (touite_id, tag_id) VALUES (8, 1);

INSERT INTO user2tag (users_id, tag_id) VALUES (1, 3);
INSERT INTO user2tag (users_id, tag_id) VALUES (1, 5);
INSERT INTO user2tag (users_id, tag_id) VALUES (1, 9);
INSERT INTO user2tag (users_id, tag_id) VALUES (2, 2);
INSERT INTO user2tag (users_id, tag_id) VALUES (2, 7);
INSERT INTO user2tag (users_id, tag_id) VALUES (2, 8);
INSERT INTO user2tag (users_id, tag_id) VALUES (3, 6);
INSERT INTO user2tag (users_id, tag_id) VALUES (3, 10);
INSERT INTO user2tag (users_id, tag_id) VALUES (3, 4);
INSERT INTO user2tag (users_id, tag_id) VALUES (4, 5);
INSERT INTO user2tag (users_id, tag_id) VALUES (4, 2);
INSERT INTO user2tag (users_id, tag_id) VALUES (4, 9);
INSERT INTO user2tag (users_id, tag_id) VALUES (5, 1);
INSERT INTO user2tag (users_id, tag_id) VALUES (5, 8);

INSERT INTO follow (follower_id, followed_id) VALUES (1, 2);
INSERT INTO follow (follower_id, followed_id) VALUES (1, 3);
INSERT INTO follow (follower_id, followed_id) VALUES (2, 1);
INSERT INTO follow (follower_id, followed_id) VALUES (2, 4);
INSERT INTO follow (follower_id, followed_id) VALUES (3, 1);
INSERT INTO follow (follower_id, followed_id) VALUES (3, 5);
INSERT INTO follow (follower_id, followed_id) VALUES (4, 2);
INSERT INTO follow (follower_id, followed_id) VALUES (4, 3);
INSERT INTO follow (follower_id, followed_id) VALUES (5, 1);
INSERT INTO follow (follower_id, followed_id) VALUES (5, 4);

-- Les trois états sont représentés : true (+1), false (-1) et NULL (0).
INSERT INTO note2touite (users_id, touite_id, state) VALUES (1, 2, 1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (1, 3, 0);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (1, 4, -1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (2, 1, 1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (2, 3, -1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (2, 5, 1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (3, 1, 1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (3, 2, -1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (3, 6, 1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (4, 1, -1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (4, 2, 1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (4, 7, 0);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (5, 1, 1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (5, 4, 1);
INSERT INTO note2touite (users_id, touite_id, state) VALUES (5, 8, 1);
commit;

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

DELIMITER ;