<?php

namespace iutnc\touiteur\repository;

class TouiteurRepository {
    protected \PDO $pdo;
    protected static array $config = [];

    public function __construct() {
        $this->pdo = new \PDO(self::$config['dsn'], self::$config['user'], self::$config['pass'],
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }

    public static function setConfig(string $file) : void {
        $conf = parse_ini_file($file);
        if ($conf === false) throw new \Exception("Error reading configuration file");
        self::$config = [
            'dsn' => "{$conf['driver']}:host={$conf['host']};dbname={$conf['database']}",
            'user' => $conf['username'],
            'pass' => $conf['password']
        ];
    }

    protected function rowToTrack(array $res) : Touite {
        if ($res['type'] === 'A') {
            $track = new AlbumTrack(
                $res['titre'],
                $res['filename'],
                $res['titre_album'] ?? '',
                (!empty($res['numero_album'])) ? (int) $res['numero_album'] : 1
            );
            if (!empty($res['artiste_album'])) {
                $track->setArtist($res['artiste_album']);
            }
            if (!empty($res['annee_album'])) {
                $track->setYear((int) $res['annee_album']);
            }
        } else {
            $track = new PodcastTrack(
                $res['titre'],
                $res['filename'],
                $res['auteur_podcast'] ?? null,
                $res['date_podcast'] ?? null
            );
        }

        $track->setId((int) $res['id']);
        if (!empty($res['duree']) && (int) $res['duree'] > 0) {
            $track->setDuration((int) $res['duree']);
        }
        if (!empty($res['genre'])) {
            $track->setGenre($res['genre']);
        }
        if (!empty($res['image'])) {
            $track->setImage($res['image']);
        }

        return $track;
    }
}