drop table users2tag cascade constraint;
drop table note2touite cascade constraint;
drop table tag2touite cascade constraint;
drop table users2touite cascade constraint;
drop table follow cascade constraint;
drop table tag cascade constraint;
drop table touite cascade constraint;
drop table users cascade constraint;

create table users (
    -- le GENERATED ALWAYS AS IDENTITY pour remplacer le AUTO_INCREMENT
    id number(11) GENERATED ALWAYS AS IDENTITY primary key,
    email varchar2(256) NOT NULL,
    passwd varchar2(256) NOT NULL
);

create table touite (
    -- le GENERATED ALWAYS AS IDENTITY pour remplacer le AUTO_INCREMENT
    id number(38) GENERATED ALWAYS AS IDENTITY primary key,
    users_id number(11) NOT NULL,
    content varchar2(235),
    note number(5),
    posted_at date NOT NULL
);

create table tag (
    libelle varchar2(100) primary key
);

create table follow (
    follower_id number(11),
    followed_id number(11),
    primary key (follower_id, followed_id)
);

create table users2touite (
    users_id number(11),
    touite_id number(38),
    primary key (users_id, touite_id)
);

create table tag2touite (
    touite_id number(38),
    tag_libelle varchar2(100),
    primary key (touite_id, tag_libelle)
);

create table note2touite (
    users_id number(11),
    touite_id number(38),
    -- -1 : négatif, 1 : POSITIF
    state number(1),
    primary key (users_id, touite_id)
);

create table users2tag (
    users_id number(11),
    tag_libelle varchar2(100),
    primary key (users_id, tag_libelle)
);

-- Clés étrangères :
alter table users2tag add foreign key (users_id) references users(id);
alter table users2tag add foreign key (tag_libelle) references tag(libelle);
alter table note2touite add foreign key (users_id) references users(id);
alter table note2touite add foreign key (touite_id) references touite(id);
alter table tag2touite add foreign key (touite_id) references touite(id);
alter table tag2touite add foreign key (tag_libelle) references tag(libelle);
alter table users2touite add foreign key (users_id) references users(id);
alter table users2touite add foreign key (touite_id) references touite(id);
alter table follow add foreign key (follower_id) references users(id);
alter table follow add foreign key (followed_id) references users(id);
alter table touite add foreign key (users_id) references users(id);

-- Contraintes :
alter table follow add constraint no_self_follow check (follower_id != followed_id);
alter table note2touite add constraint chk_note_bool CHECK (state IN (-1, 0, 1));

-- Contenu : (Contenu généré à l'aide de l'IA)

-- Comptes de test : les mots de passe correspondent aux valeurs indiquées
INSERT INTO users (email, passwd) VALUES ('users1@mail.com', '$2y$12$z7mHIyHACElCs8zE4hVvN.cPM4ala.FM6McIch4Zn7OakVGhMskvC');
INSERT INTO users (email, passwd) VALUES ('users2@mail.com', '$2y$12$JelzicyqC10xiJz0i/JWUuUqFLBr5fhos2omEJoV68yGpQVp1.U1.');
INSERT INTO users (email, passwd) VALUES ('users3@mail.com', '$2y$12$KYcd2LqjSdH/wvGzv8qB.OZiyPPqno1ac4IoRjbVqErUP0M.6V8La');
INSERT INTO users (email, passwd) VALUES ('users4@mail.com', '$2y$12$NyyVwilJWJtwEcTUXwLWceGWaztT1PtPsyswXx0t8dbMW0O8tngcu');
INSERT INTO users (email, passwd) VALUES ('admin@mail.com', '$2y$12$6KbKcQdgn.Sid6kvbunFmufU5d6KgArMGQ5EKXUnpwsQSOECmTv1W');

INSERT INTO tag (libelle) VALUES ('annonce');
INSERT INTO tag (libelle) VALUES ('cinema');
INSERT INTO tag (libelle) VALUES ('code');
INSERT INTO tag (libelle) VALUES ('culture');
INSERT INTO tag (libelle) VALUES ('musique');
INSERT INTO tag (libelle) VALUES ('nature');
INSERT INTO tag (libelle) VALUES ('sport');
INSERT INTO tag (libelle) VALUES ('technologie');
INSERT INTO tag (libelle) VALUES ('vacances');
INSERT INTO tag (libelle) VALUES ('lecture');

INSERT INTO touite (users_id, content, note, posted_at) VALUES (1, 'Je commence un nouveau projet de code, motive pour apprendre !', 2, DATE '2026-09-01');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (2, 'Quel film vous a le plus marque cette annee ?', 1, DATE '2026-09-02');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (3, 'Petite randonnee sous le soleil : la nature fait du bien.', 0, DATE '2026-09-03');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (4, 'Playlist du moment : beaucoup de musique et un peu de nostalgie.', 0, DATE '2026-09-04');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (5, 'Maintenance du site samedi de 10h a 11h. Merci pour votre patience.', 1, DATE '2026-09-05');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (1, 'Objectif de la semaine : lire un livre et faire du sport.', 1, DATE '2026-09-06');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (2, 'Des idees de destinations pour les prochaines vacances ?', 0, DATE '2026-09-07');
INSERT INTO touite (users_id, content, note, posted_at) VALUES (3, 'Astuce technologie : pensez a sauvegarder vos fichiers importants.', 1, DATE '2026-09-08');

INSERT INTO users2touite (users_id, touite_id) VALUES (1, 1);
INSERT INTO users2touite (users_id, touite_id) VALUES (2, 2);
INSERT INTO users2touite (users_id, touite_id) VALUES (3, 3);
INSERT INTO users2touite (users_id, touite_id) VALUES (4, 4);
INSERT INTO users2touite (users_id, touite_id) VALUES (5, 5);
INSERT INTO users2touite (users_id, touite_id) VALUES (1, 6);
INSERT INTO users2touite (users_id, touite_id) VALUES (2, 7);
INSERT INTO users2touite (users_id, touite_id) VALUES (3, 8);

INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (1, 'code');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (1, 'technologie');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (2, 'cinema');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (2, 'culture');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (3, 'nature');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (3, 'sport');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (4, 'musique');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (4, 'culture');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (5, 'annonce');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (5, 'technologie');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (6, 'lecture');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (6, 'sport');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (7, 'vacances');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (7, 'nature');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (8, 'technologie');
INSERT INTO tag2touite (touite_id, tag_libelle) VALUES (8, 'annonce');

INSERT INTO users2tag (users_id, tag_libelle) VALUES (1, 'code');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (1, 'musique');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (1, 'vacances');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (2, 'cinema');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (2, 'sport');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (2, 'technologie');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (3, 'nature');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (3, 'lecture');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (3, 'culture');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (4, 'musique');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (4, 'cinema');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (4, 'vacances');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (5, 'annonce');
INSERT INTO users2tag (users_id, tag_libelle) VALUES (5, 'technologie');

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