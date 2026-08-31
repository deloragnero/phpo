<?php require_once __DIR__ . '/../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inscription — Camp National ESVS 2026</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <div class="brand">Camp National <span>ESVS 2026</span></div>
    <div class="brand-sub">Réseau des Jeunes Ambassadeurs SR/PF Côte d'Ivoire</div>
  </div>
</header>

<main class="container form-page">

  <div class="form-intro">
    <span class="badge">Inscription</span>
    <h1>S'inscrire au Camp ESVS 2026</h1>
    <p>Remplissez le formulaire en 5 étapes pour réserver votre place et faciliter le suivi de votre paiement.</p>
    <div class="notice">
      ⓘ Si vous avez déjà soumis une inscription, veuillez ne pas soumettre à nouveau. Consultez vos messages WhatsApp ou email pour retrouver votre numéro.
    </div>
  </div>

  <ol class="step-indicator" id="stepIndicator">
    <li class="active" data-step="1"><span>1</span>Identité</li>
    <li data-step="2"><span>2</span>Coordonnées</li>
    <li data-step="3"><span>3</span>Participation</li>
    <li data-step="4"><span>4</span>Urgence</li>
    <li data-step="5"><span>5</span>Consentement</li>
  </ol>

  <div id="formMessage" class="form-message" hidden></div>

  <form id="inscriptionForm" class="card" novalidate>

    <!-- Étape 1 : Identité -->
    <section class="step" data-step="1">
      <h2>Étape 1 — Identité</h2>
      <p class="step-desc">Renseignez vos informations personnelles.</p>

      <div class="field">
        <label for="nom">Nom *</label>
        <input type="text" id="nom" name="nom" required>
      </div>

      <div class="field">
        <label for="prenoms">Prénoms *</label>
        <input type="text" id="prenoms" name="prenoms" required>
      </div>

      <div class="field">
        <label for="sexeGenre">Sexe / Genre <span class="optional">— optionnel, pour les statistiques du camp</span></label>
        <select id="sexeGenre" name="sexeGenre">
          <option value="">Sélectionner (optionnel)</option>
          <option value="Masculin">Masculin</option>
          <option value="Féminin">Féminin</option>
          <option value="Autre">Autre</option>
          <option value="Préfère ne pas préciser">Préfère ne pas préciser</option>
        </select>
      </div>

      <div class="field-row">
        <div class="field">
          <label for="dateNaissance">Date de naissance *</label>
          <input type="date" id="dateNaissance" name="dateNaissance" required>
        </div>
        <div class="field">
          <label for="nationalite">Nationalité *</label>
          <input type="text" id="nationalite" name="nationalite" value="Ivoirienne" required>
        </div>
      </div>

      <div class="field">
        <label for="villeCommune">Ville / Commune de résidence *</label>
        <input type="text" id="villeCommune" name="villeCommune" required>
      </div>

      <div class="field">
        <label for="photoParticipant">Photo du participant <span class="optional">— optionnel</span></label>
        <input type="file" id="photoParticipant" name="photoParticipant" accept="image/*">
      </div>
    </section>

    <!-- Étape 2 : Coordonnées -->
    <section class="step" data-step="2" hidden>
      <h2>Étape 2 — Coordonnées</h2>
      <p class="step-desc">Comment pouvons-nous vous joindre ?</p>

      <div class="field">
        <label for="telephone">Téléphone *</label>
        <input type="tel" id="telephone" name="telephone" required>
      </div>

      <div class="field checkbox-field">
        <label><input type="checkbox" id="sameAsPhone" name="sameAsPhone"> Mon numéro WhatsApp est le même que mon téléphone</label>
      </div>

      <div class="field" id="whatsappField">
        <label for="whatsapp">Numéro WhatsApp</label>
        <input type="tel" id="whatsapp" name="whatsapp">
      </div>

      <div class="field">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="field">
        <label for="organisation">Organisation / Structure <span class="optional">— optionnel</span></label>
        <input type="text" id="organisation" name="organisation">
      </div>
    </section>

    <!-- Étape 3 : Participation -->
    <section class="step" data-step="3" hidden>
      <h2>Étape 3 — Participation</h2>
      <p class="step-desc">Parlez-nous de vos attentes.</p>

      <div class="field">
        <label for="attentes">Vos attentes vis-à-vis du camp</label>
        <textarea id="attentes" name="attentes" rows="3"></textarea>
      </div>

      <div class="field">
        <label for="domaineInteret">Domaine d'intérêt</label>
        <input type="text" id="domaineInteret" name="domaineInteret" placeholder="Ex : Santé reproductive, plaidoyer, leadership...">
      </div>

      <div class="field">
        <label for="participationAnterieure">Avez-vous déjà participé à un camp ESVS ?</label>
        <select id="participationAnterieure" name="participationAnterieure">
          <option value="">Sélectionner</option>
          <option value="Oui">Oui</option>
          <option value="Non">Non</option>
        </select>
      </div>

      <div class="field">
        <label for="besoinAssistance">Besoin d'assistance particulière <span class="optional">— optionnel</span></label>
        <textarea id="besoinAssistance" name="besoinAssistance" rows="2" placeholder="Allergie, mobilité réduite, régime alimentaire..."></textarea>
      </div>
    </section>

    <!-- Étape 4 : Contact d'urgence -->
    <section class="step" data-step="4" hidden>
      <h2>Étape 4 — Contact d'urgence</h2>
      <p class="step-desc">Une personne à contacter en cas d'urgence pendant le camp.</p>

      <div class="field">
        <label for="urgenceNomPrenoms">Nom et prénoms *</label>
        <input type="text" id="urgenceNomPrenoms" name="urgenceNomPrenoms" required>
      </div>

      <div class="field">
        <label for="urgenceLien">Lien de parenté</label>
        <input type="text" id="urgenceLien" name="urgenceLien" placeholder="Ex : Père, mère, tuteur...">
      </div>

      <div class="field">
        <label for="urgenceTelephone">Téléphone *</label>
        <input type="tel" id="urgenceTelephone" name="urgenceTelephone" required>
      </div>
    </section>

    <!-- Étape 5 : Consentement -->
    <section class="step" data-step="5" hidden>
      <h2>Étape 5 — Consentement</h2>
      <p class="step-desc">Dernière étape avant l'envoi de votre inscription.</p>

      <div class="field">
        <label for="numeroPaiement">Numéro de transaction / référence de paiement <span class="optional">— si déjà effectué</span></label>
        <input type="text" id="numeroPaiement" name="numeroPaiement">
      </div>

      <div class="field checkbox-field">
        <label><input type="checkbox" id="consentementExactitude" name="consentementExactitude" required> Je certifie l'exactitude des informations fournies *</label>
      </div>
      <div class="field checkbox-field">
        <label><input type="checkbox" id="consentementDonnees" name="consentementDonnees" required> J'accepte le traitement de mes données personnelles *</label>
      </div>
      <div class="field checkbox-field">
        <label><input type="checkbox" id="consentementReglement" name="consentementReglement" required> J'accepte le règlement intérieur du camp *</label>
      </div>
      <div class="field checkbox-field" id="autorisationParentaleField" hidden>
        <label><input type="checkbox" id="autorisationParentale" name="autorisationParentale"> Autorisation parentale jointe / obtenue (obligatoire pour les moins de 18 ans) *</label>
      </div>
    </section>

    <div class="form-nav">
      <button type="button" id="prevBtn" class="btn btn-outline" disabled>Précédent</button>
      <button type="button" id="nextBtn" class="btn btn-primary">Suivant</button>
      <button type="submit" id="submitBtn" class="btn btn-accent" hidden>Soumettre mon inscription</button>
    </div>

  </form>

  <!-- Écran de confirmation -->
  <div id="confirmationScreen" class="card confirmation" hidden>
    <div class="check-icon">✓</div>
    <h2>Inscription enregistrée !</h2>
    <p id="confirmName"></p>
    <p>Votre numéro d'inscription :</p>
    <div class="reg-number" id="confirmNumber"></div>
    <p class="small">Conservez précieusement ce numéro. Il vous sera demandé pour le suivi de votre paiement et à votre arrivée au camp.</p>
  </div>

</main>

<footer class="site-footer">
  <div class="container">© 2026 JA SR/PF CI & Communauté des Jeunes Engagés. <a href="admin/index.php Tous droits réservés.</a></div>
</footer>

<script src="assets/form.js"></script>
</body>
</html>
