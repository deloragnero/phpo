(function () {
  const form = document.getElementById('inscriptionForm');
  const steps = Array.from(document.querySelectorAll('.step'));
  const indicatorItems = Array.from(document.querySelectorAll('#stepIndicator li'));
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitBtn');
  const messageBox = document.getElementById('formMessage');
  const totalSteps = steps.length;
  let currentStep = 1;

  const stepRequiredFields = {
    1: ['nom', 'prenoms', 'dateNaissance', 'nationalite', 'villeCommune'],
    2: ['telephone', 'email'],
    3: [],
    4: ['urgenceNomPrenoms', 'urgenceTelephone'],
    5: ['consentementExactitude', 'consentementDonnees', 'consentementReglement'],
  };

  // Sexe/genre et WhatsApp "même que téléphone"
  const sameAsPhone = document.getElementById('sameAsPhone');
  const whatsappField = document.getElementById('whatsappField');
  const whatsappInput = document.getElementById('whatsapp');
  sameAsPhone.addEventListener('change', () => {
    if (sameAsPhone.checked) {
      whatsappInput.value = document.getElementById('telephone').value;
      whatsappField.style.opacity = '.5';
      whatsappInput.setAttribute('disabled', 'disabled');
    } else {
      whatsappField.style.opacity = '1';
      whatsappInput.removeAttribute('disabled');
    }
  });

  // Autorisation parentale si mineur (calcul de l'âge à partir de la date de naissance)
  const dateNaissanceInput = document.getElementById('dateNaissance');
  const autorisationField = document.getElementById('autorisationParentaleField');
  const autorisationCheckbox = document.getElementById('autorisationParentale');
  function checkMinor() {
    if (!dateNaissanceInput.value) return;
    const dob = new Date(dateNaissanceInput.value);
    const now = new Date();
    let age = now.getFullYear() - dob.getFullYear();
    const m = now.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < dob.getDate())) age--;
    if (age < 18) {
      autorisationField.hidden = false;
      autorisationCheckbox.setAttribute('required', 'required');
      stepRequiredFields[5] = ['consentementExactitude', 'consentementDonnees', 'consentementReglement', 'autorisationParentale'];
    } else {
      autorisationField.hidden = true;
      autorisationCheckbox.removeAttribute('required');
      stepRequiredFields[5] = ['consentementExactitude', 'consentementDonnees', 'consentementReglement'];
    }
  }
  dateNaissanceInput.addEventListener('change', checkMinor);

  function showMessage(text, type) {
    messageBox.textContent = text;
    messageBox.className = 'form-message ' + type;
    messageBox.hidden = false;
    messageBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function validateStep(step) {
    const fields = stepRequiredFields[step] || [];
    for (const name of fields) {
      const el = form.elements[name];
      if (!el) continue;
      if (el.type === 'checkbox') {
        if (!el.checked) {
          showMessage("Veuillez cocher toutes les cases obligatoires (*) avant de continuer.", 'error');
          el.focus();
          return false;
        }
      } else if (!String(el.value || '').trim()) {
        showMessage("Veuillez remplir tous les champs obligatoires (*) avant de continuer.", 'error');
        el.focus();
        return false;
      }
    }
    messageBox.hidden = true;
    return true;
  }

  function goToStep(step) {
    steps.forEach(s => { s.hidden = Number(s.dataset.step) !== step; });
    indicatorItems.forEach(li => {
      const n = Number(li.dataset.step);
      li.classList.toggle('active', n === step);
      li.classList.toggle('done', n < step);
    });
    prevBtn.disabled = step === 1;
    nextBtn.hidden = step === totalSteps;
    submitBtn.hidden = step !== totalSteps;
    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  nextBtn.addEventListener('click', () => {
    if (validateStep(currentStep)) {
      goToStep(Math.min(currentStep + 1, totalSteps));
    }
  });

  prevBtn.addEventListener('click', () => {
    goToStep(Math.max(currentStep - 1, 1));
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!validateStep(currentStep)) return;

    submitBtn.disabled = true;
    submitBtn.textContent = 'Envoi en cours...';

    try {
      const formData = new FormData(form);
      const res = await fetch('api.php', { method: 'POST', body: formData });
      const result = await res.json();

      if (!res.ok || !result.success) {
        throw new Error(result.message || "L'inscription n'a pas pu être enregistrée.");
      }

      form.hidden = true;
      document.getElementById('stepIndicator').hidden = true;
      const confirmScreen = document.getElementById('confirmationScreen');
      document.getElementById('confirmName').textContent =
        form.elements['prenoms'].value + ' ' + form.elements['nom'].value;
      document.getElementById('confirmNumber').textContent = result.registrationNumber;
      confirmScreen.hidden = false;
      messageBox.hidden = true;
    } catch (err) {
      showMessage(err.message || "Une erreur est survenue. Veuillez réessayer.", 'error');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Soumettre mon inscription';
    }
  });

  goToStep(1);
})();
