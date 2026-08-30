<?php
/**
 * Connexion à la base SQLite + création automatique des tables si besoin.
 * ESVS 2026 - Camp National (version PHP + SQLite)
 */

// Dossier de données (en dehors du webroot idéalement — ici pour simplicité on le protège via .htaccess)
define('DATA_DIR', __DIR__ . '/../data');
define('DB_PATH', DATA_DIR . '/database.sqlite');
define('UPLOADS_DIR', __DIR__ . '/../public/uploads');

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0775, true);
}
if (!is_dir(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0775, true);
}

function get_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        init_schema($pdo);
    }
    return $pdo;
}

function init_schema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS inscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            registration_number TEXT UNIQUE NOT NULL,

            -- Étape 1 : Identité
            nom TEXT NOT NULL,
            prenoms TEXT NOT NULL,
            sexe_genre TEXT,
            date_naissance TEXT NOT NULL,
            age INTEGER,
            nationalite TEXT,
            ville_commune TEXT NOT NULL,

            -- Étape 2 : Coordonnées
            telephone TEXT NOT NULL,
            whatsapp TEXT,
            same_as_phone INTEGER DEFAULT 0,
            email TEXT,
            organisation TEXT,

            -- Étape 3 : Participation
            attentes TEXT,
            domaine_interet TEXT,
            participation_anterieure TEXT,
            besoin_assistance TEXT,

            -- Étape 4 : Contact d'urgence
            urgence_nom_prenoms TEXT NOT NULL,
            urgence_lien TEXT,
            urgence_telephone TEXT NOT NULL,

            -- Étape 5 : Consentement
            consentement_exactitude INTEGER DEFAULT 0,
            consentement_donnees INTEGER DEFAULT 0,
            consentement_reglement INTEGER DEFAULT 0,
            autorisation_parentale INTEGER DEFAULT 0,

            -- Divers
            numero_paiement TEXT,
            statut_paiement TEXT DEFAULT 'en_attente',
            photo_participant TEXT,

            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Compte admin par défaut si aucun n'existe (à changer immédiatement après le déploiement !)
    $count = $pdo->query("SELECT COUNT(*) AS c FROM admins")->fetch()['c'];
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
        $stmt->execute(['admin', password_hash('ESVS2026admin', PASSWORD_DEFAULT)]);
    }
}
