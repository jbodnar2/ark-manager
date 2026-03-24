import './dialogs.js';
import './alerts.js';

document.addEventListener('click', (event) => {
  const lockButton = event.target.closest('[data-form-lock');
  if (!lockButton) return;

  const field = lockButton.closest('.form__field').querySelector('input');
  if (!field) return;

  const isDisabled = field.disabled === true;
  field.disabled = !isDisabled;

  if (isDisabled) {
    field.style.fontWeight = 'bold';

    lockButton.querySelector('span').classList.toggle('icon--unlock');
    lockButton.querySelector('span').classList.toggle('icon--lock');
  } else {
    field.style.fontWeight = 'normal';
    lockButton.querySelector('span').classList.toggle('icon--unlock');
    lockButton.querySelector('span').classList.toggle('icon--lock');
  }
});
