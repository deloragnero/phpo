# Camp ESVS 2026 — Inscription (version PHP + SQLite)

Version légère et rapide du site d'inscription (remplace la version Node/Next.js + MongoDB),
pensée pour tenir un pic de visiteurs sur un hébergement mutualisé classique (type Hostinger).

## Contenu

```
campesvs-php/
├── index.php                 → redirige vers public/ (si le docroot pointe sur la racine)
├── config/db.php             → connexion SQLite + création automatique des tables
├── includes/functions.php    → fonctions utilitaires
├── includes/MiniPdf.php      → générateur PDF interne (sans dépendance)
├── data/database.sqlite      → base de données (créée automatiquement au 1er accès)
├── public/                   → À POINTER COMME RACINE WEB (document root) si possible
│   ├── inscription.php       → formulaire public en 5 étapes
│   ├── api.php                → réception et validation des inscriptions
│   ├── assets/                → CSS + JS
│   └── uploads/                → photos des participants
└── admin/                    → interface d'administration
    ├── login.php / logout.php
    ├── index.php              → liste, recherche, statistiques
    ├── view.php                → fiche détaillée d'une inscription
    ├── export_csv.php          → export Excel (CSV avec BOM UTF-8)
    ├── export_pdf.php          → export PDF (tableau)
    └── export_docx.php         → export Word (.docx)
```

## Prérequis

- PHP 8.0 ou supérieur
- Extension **pdo_sqlite** activée (présente par défaut chez la plupart des hébergeurs, y compris Hostinger)
- Extension **zip** activée (pour l'export Word) — également standard
- Aucune dépendance Composer, aucune base MySQL à créer : tout fonctionne « à l'upload ».

## Installation (Hostinger ou hébergement mutualisé)

1. Compressez le dossier `campesvs-php/` et envoyez-le via le gestionnaire de fichiers ou FTP.
2. **Idéalement**, configurez le document root du (sous-)domaine sur `campesvs-php/public`.
   - Si ce n'est pas possible (hébergement basique sans réglage de docroot), déposez tout le
     contenu à la racine `public_html/` : le fichier `index.php` racine redirige automatiquement
     vers `public/inscription.php`. Dans ce cas, l'admin sera accessible via `/admin/login.php`.
3. Donnez les droits d'écriture (775) aux dossiers `data/` et `public/uploads/`.
4. Ouvrez `public/inscription.php` (ou juste le domaine) : la base SQLite et le compte admin
   par défaut sont créés automatiquement au premier chargement.
5. Connectez-vous à `/admin/` :
   - **Identifiant :** `admin`
   - **Mot de passe :** `ESVS2026admin`
   - **⚠️ Changez ce mot de passe immédiatement** (voir ci-dessous).

## Changer le mot de passe admin

Le plus simple : exécutez une fois ce script PHP (puis supprimez-le), en remplaçant `NouveauMotDePasse` :

```php
<?php
require_once 'config/db.php';
$pdo = get_db();
$hash = password_hash('NouveauMotDePasse', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = 'admin'")->execute([$hash]);
echo "Mot de passe mis à jour.";
```

## Pourquoi cette version est plus rapide

- Pas de serveur Node.js à faire tourner en permanence (donc pas de "cold start" ni de process
  qui sature en RAM sous un pic de trafic) — PHP est exécuté à la demande par le serveur web,
  qui sait gérer nativement des centaines de requêtes concurrentes.
- SQLite est un fichier local unique, sans latence réseau vers une base externe (contrairement à
  MongoDB Atlas) — les lectures/écritures sont quasi instantanées pour ce volume (quelques
  centaines à quelques milliers d'inscriptions).
- Aucune étape de build (`next build`), aucun bundle JS lourd à télécharger : la page est un
  simple HTML/CSS/JS ultra-léger.

## Limites à connaître

- SQLite gère très bien la lecture concurrente, mais les écritures sont sérialisées (un seul
  écrivain à la fois). Pour un camp de 200 participants avec des pics de quelques centaines de
  connexions simultanées, ce n'est absolument pas un problème — SQLite tient sans souci des
  milliers d'écritures par minute.
- Le PDF est généré par un petit moteur interne (police Helvetica standard) : suffisant pour un
  tableau de données propre, mais sans mise en page avancée (logos, couleurs riches). Si un PDF
  plus élaboré est nécessaire plus tard, on pourra brancher une vraie librairie (Dompdf, TCPDF)
  via Composer.
- L'export Word (.docx) est généré directement au format OOXML minimal (pas de PhpWord) : il
  s'ouvre normalement dans Word/LibreOffice/Google Docs.

## Champs repris du formulaire d'origine

Identité (nom, prénoms, sexe/genre, date de naissance, nationalité, ville/commune, photo),
Coordonnées (téléphone, WhatsApp, email, organisation), Participation (attentes, domaine
d'intérêt, participation antérieure, besoin d'assistance particulière), Contact d'urgence (nom,
lien, téléphone), Consentement (exactitude, données personnelles, règlement intérieur,
autorisation parentale si mineur) et numéro/référence de paiement.

Le numéro d'inscription généré suit le même format que l'original : `ESVS2026-XXXXXX`.
