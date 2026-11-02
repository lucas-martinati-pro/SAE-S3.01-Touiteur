drop table if exists user2tag cascade;
drop table if exists note2touite cascade;
drop table if exists tag2touite cascade;
drop table if exists user2touite cascade;
drop table if exists follow cascade;
drop table if exists tag cascade;
drop table if exists touite cascade;
drop table if exists user cascade;

create table user (
    id int(11) AUTO_INCREMENT primary key,
    email varchar(256) NOT NULL,
    passwd varchar(256) NOT NULL,
    role int(1) NOT NULL default 1
);

create table touite (
    id int(50) AUTO_INCREMENT primary key,
    user_id int(11) NOT NULL,
    content varchar(235),
    note int(5),
    posted_at date NOT NULL
);

create table tag (
    libelle varchar(100) primary key
);

create table follow (
    follower_id int(11),
    followed_id int(11),
    primary key (follower_id, followed_id)
);

create table user2touite (
    user_id int(11),
    touite_id int(50),
    primary key (user_id, touite_id)
);

create table tag2touite (
    touite_id int(50),
    tag_libelle varchar(100),
    primary key (touite_id, tag_libelle)
);

create table note2touite (
    user_id int(11),
    touite_id int(50),
    state bool, -- False : -1, NULL : 0, True : +1
    primary key (user_id, touite_id)
);

create table user2tag (
    user_id int(11),
    tag_libelle varchar(100),
    primary key (user_id, tag_libelle)
);

-- Clés étrangères :
alter table user2tag add foreign key (user_id) references user(id);
alter table user2tag add foreign key (tag_libelle) references tag(libelle);
alter table note2touite add foreign key (user_id) references user(id);
alter table note2touite add foreign key (touite_id) references touite(id);
alter table tag2touite add foreign key (touite_id) references touite(id);
alter table tag2touite add foreign key (tag_libelle) references tag(libelle);
alter table user2touite add foreign key (user_id) references user(id);
alter table user2touite add foreign key (touite_id) references touite(id);
alter table follow add foreign key (follower_id) references user(id);
alter table follow add foreign key (followed_id) references user(id);
alter table touite add foreign key (user_id) references user(id);

-- Contraintes :
alter table follow add constraint no_self_follow check (follower_id != followed_id);

-- Contenu : (Contenu généré à l'aide de l'IA)

-- Comptes de test : les mots de passe correspondent aux valeurs indiquées
insert into user (id, email, passwd, role) values
    (1, 'user1@mail.com', '$2y$12$z7mHIyHACElCs8zE4hVvN.cPM4ala.FM6McIch4Zn7OakVGhMskvC', 1),
    (2, 'user2@mail.com', '$2y$12$JelzicyqC10xiJz0i/JWUuUqFLBr5fhos2omEJoV68yGpQVp1.U1.', 1),
    (3, 'user3@mail.com', '$2y$12$KYcd2LqjSdH/wvGzv8qB.OZiyPPqno1ac4IoRjbVqErUP0M.6V8La', 1),
    (4, 'user4@mail.com', '$2y$12$NyyVwilJWJtwEcTUXwLWceGWaztT1PtPsyswXx0t8dbMW0O8tngcu', 1),
    (5, 'admin@mail.com', '$2y$12$6KbKcQdgn.Sid6kvbunFmufU5d6KgArMGQ5EKXUnpwsQSOECmTv1W', 0);

insert into tag (libelle) values
    ('annonce'), ('cinema'), ('code'), ('culture'), ('musique'),
    ('nature'), ('sport'), ('technologie'), ('vacances'), ('lecture');

insert into touite (id, user_id, content, note, posted_at) values
    (1, 1, 'Je commence un nouveau projet de code, motive pour apprendre !', 2, '2026-09-01'),
    (2, 2, 'Quel film vous a le plus marque cette annee ?', 1, '2026-09-02'),
    (3, 3, 'Petite randonnee sous le soleil : la nature fait du bien.', 0, '2026-09-03'),
    (4, 4, 'Playlist du moment : beaucoup de musique et un peu de nostalgie.', 0, '2026-09-04'),
    (5, 5, 'Maintenance du site samedi de 10h a 11h. Merci pour votre patience.', 1, '2026-09-05'),
    (6, 1, 'Objectif de la semaine : lire un livre et faire du sport.', 1, '2026-09-06'),
    (7, 2, 'Des idees de destinations pour les prochaines vacances ?', 0, '2026-09-07'),
    (8, 3, 'Astuce technologie : pensez a sauvegarder vos fichiers importants.', 1, '2026-09-08');

insert into user2touite (user_id, touite_id) values
    (1, 1), (2, 2), (3, 3), (4, 4), (5, 5), (1, 6), (2, 7), (3, 8);

insert into tag2touite (touite_id, tag_libelle) values
    (1, 'code'), (1, 'technologie'),
    (2, 'cinema'), (2, 'culture'),
    (3, 'nature'), (3, 'sport'),
    (4, 'musique'), (4, 'culture'),
    (5, 'annonce'), (5, 'technologie'),
    (6, 'lecture'), (6, 'sport'),
    (7, 'vacances'), (7, 'nature'),
    (8, 'technologie'), (8, 'annonce');

insert into user2tag (user_id, tag_libelle) values
    (1, 'code'), (1, 'musique'), (1, 'vacances'),
    (2, 'cinema'), (2, 'sport'), (2, 'technologie'),
    (3, 'nature'), (3, 'lecture'), (3, 'culture'),
    (4, 'musique'), (4, 'cinema'), (4, 'vacances'),
    (5, 'annonce'), (5, 'technologie');

insert into follow (follower_id, followed_id) values
    (1, 2), (1, 3), (2, 1), (2, 4), (3, 1), (3, 5),
    (4, 2), (4, 3), (5, 1), (5, 4);

-- Les trois états sont représentés : true (+1), false (-1) et NULL (0).
insert into note2touite (user_id, touite_id, state) values
    (1, 2, true), (1, 3, true), (1, 4, false),
    (2, 1, true), (2, 3, false), (2, 5, true),
    (3, 1, true), (3, 2, false), (3, 6, true),
    (4, 1, false), (4, 2, true), (4, 7, null),
    (5, 1, true), (5, 4, true), (5, 8, true);
