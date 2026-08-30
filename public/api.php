<?php
require_once __DIR__ . '/../includes/functions.php';

header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$pdo = get_db();

// --- Récupération des champs ---
$nom = clean($_POST['nom'] ?? '');
$prenoms = clean($_POST['prenoms'] ?? '');
$sexeGenre = clean($_POST['sexeGenre'] ?? '');
$dateNaissance = clean($_POST['dateNaissance'] ?? '');
$nationalite = clean($_POST['nationalite'] ?? '');
$villeCommune = clean($_POST['villeCommune'] ?? '');

$telephone = clean($_POST['telephone'] ?? '');
$whatsapp = clean($_POST['whatsapp'] ?? '');
$sameAsPhone = isset($_POST['sameAsPhone']) && $_POST['sameAsPhone'] !== 'false' ? 1 : 0;
$email = clean($_POST['email'] ?? '');
$organisation = clean($_POST['organisation'] ?? '');

$attentes = clean($_POST['attentes'] ?? '');
$domaineInteret = clean($_POST['domaineInteret'] ?? '');
$participationAnterieure = clean($_POST['participationAnterieure'] ?? '');
$besoinAssistance = clean($_POST['besoinAssistance'] ?? '');

$urgenceNomPrenoms = clean($_POST['urgenceNomPrenoms'] ?? '');
$urgenceLien = clean($_POST['urgenceLien'] ?? '');
$urgenceTelephone = clean($_POST['urgenceTelephone'] ?? '');

$consentementExactitude = isset($_POST['consentementExactitude']) && $_POST['consentementExactitude'] !== 'false' ? 1 : 0;
$consentementDonnees = isset($_POST['consentementDonnees']) && $_POST['consentementDonnees'] !== 'false' ? 1 : 0;
$consentementReglement = isset($_POST['consentementReglement']) && $_POST['consentementReglement'] !== 'false' ? 1 : 0;
$autorisationParentale = isset($_POST['autorisationParentale']) && $_POST['autorisationParentale'] !== 'false' ? 1 : 0;

$numeroPaiement = clean($_POST['numeroPaiement'] ?? '');

// --- Validation serveur (ne jamais faire confiance uniquement au JS) ---
$errors = [];
if ($nom === '') $errors[] = 'Le nom est obligatoire.';
if ($prenoms === '') $errors[] = 'Les prénoms sont obligatoires.';
if ($dateNaissance === '') $errors[] = 'La date de naissance est obligatoire.';
if ($nationalite === '') $errors[] = 'La nationalité est obligatoire.';
if ($villeCommune === '') $errors[] = 'La ville/commune est obligatoire.';
if ($telephone === '') $errors[] = 'Le téléphone est obligatoire.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Un email valide est obligatoire.';
if ($urgenceNomPrenoms === '') $errors[] = "Le nom du contact d'urgence est obligatoire.";
if ($urgenceTelephone === '') $errors[] = "Le téléphone du contact d'urgence est obligatoire.";
if (!$consentementExactitude || !$consentementDonnees || !$consentementReglement) {
    $errors[] = 'Vous devez accepter toutes les cases de consentement obligatoires.';
}

$age = calculate_age($dateNaissance);
if ($age !== null && $age < 18 && !$autorisationParentale) {
    $errors[] = "L'autorisation parentale est obligatoire pour les participants mineurs.";
}

if ($sameAsPhone) {
    $whatsapp = $telephone;
}

if (!empty($errors)) {
    json_response(['success' => false, 'message' => implode(' ', $errors)], 422);
}

// --- Anti-doublon simple : même téléphone + même nom déjà inscrits ---
$check = $pdo->prepare("SELECT registration_number FROM inscriptions WHERE telephone = ? AND nom = ? AND prenoms = ? LIMIT 1");
$check->execute([$telephone, $nom, $prenoms]);
if ($existing = $check->fetch()) {
    json_response([
        'success' => false,
        'message' => "Une inscription existe déjà pour ce numéro de téléphone (N° {$existing['registration_number']}). Consultez vos messages WhatsApp/email.",
    ], 409);
}

// --- Gestion de la photo (optionnelle) ---
$photoFilename = null;
if (!empty($_FILES['photoParticipant']['name']) && $_FILES['photoParticipant']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['photoParticipant']['tmp_name'];
    $originalName = $_FILES['photoParticipant']['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ext, $allowed, true) && filesize($tmpName) <= 5 * 1024 * 1024) {
        $photoFilename = uniqid('photo_', true) . '.' . $ext;
        move_uploaded_file($tmpName, UPLOADS_DIR . '/' . $photoFilename);
    }
}

// --- Insertion ---
$registrationNumber = generate_registration_number($pdo);

$stmt = $pdo->prepare("
    INSERT INTO inscriptions (
        registration_number, nom, prenoms, sexe_genre, date_naissance, age, nationalite, ville_commune,
        telephone, whatsapp, same_as_phone, email, organisation,
        attentes, domaine_interet, participation_anterieure, besoin_assistance,
        urgence_nom_prenoms, urgence_lien, urgence_telephone,
        consentement_exactitude, consentement_donnees, consentement_reglement, autorisation_parentale,
        numero_paiement, photo_participant
    ) VALUES (
        :registration_number, :nom, :prenoms, :sexe_genre, :date_naissance, :age, :nationalite, :ville_commune,
        :telephone, :whatsapp, :same_as_phone, :email, :organisation,
        :attentes, :domaine_interet, :participation_anterieure, :besoin_assistance,
        :urgence_nom_prenoms, :urgence_lien, :urgence_telephone,
        :consentement_exactitude, :consentement_donnees, :consentement_reglement, :autorisation_parentale,
        :numero_paiement, :photo_participant
    )
");

$stmt->execute([
    ':registration_number' => $registrationNumber,
    ':nom' => $nom,
    ':prenoms' => $prenoms,
    ':sexe_genre' => $sexeGenre,
    ':date_naissance' => $dateNaissance,
    ':age' => $age,
    ':nationalite' => $nationalite,
    ':ville_commune' => $villeCommune,
    ':telephone' => $telephone,
    ':whatsapp' => $whatsapp,
    ':same_as_phone' => $sameAsPhone,
    ':email' => $email,
    ':organisation' => $organisation,
    ':attentes' => $attentes,
    ':domaine_interet' => $domaineInteret,
    ':participation_anterieure' => $participationAnterieure,
    ':besoin_assistance' => $besoinAssistance,
    ':urgence_nom_prenoms' => $urgenceNomPrenoms,
    ':urgence_lien' => $urgenceLien,
    ':urgence_telephone' => $urgenceTelephone,
    ':consentement_exactitude' => $consentementExactitude,
    ':consentement_donnees' => $consentementDonnees,
    ':consentement_reglement' => $consentementReglement,
    ':autorisation_parentale' => $autorisationParentale,
    ':numero_paiement' => $numeroPaiement,
    ':photo_participant' => $photoFilename,
]);

json_response([
    'success' => true,
    'registrationNumber' => $registrationNumber,
]);
