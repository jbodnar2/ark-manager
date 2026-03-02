document.addEventListener('click', (event) => {
  if (event.target.hasAttribute('data-dialog')) {
    const dialogId = event.target.dataset.dialog;
    const dialog = document.querySelector(`#${dialogId}`);
    if (dialog) dialog.showModal();
  }
});

document.querySelectorAll('dialog').forEach((dialog) => {
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) {
      dialog.close();
    }
  });
});

document.addEventListener('click', (event) => {
  if (event.target.hasAttribute('data-dialog-cancel')) {
    event.target.closest('dialog')?.close();
  }
});
