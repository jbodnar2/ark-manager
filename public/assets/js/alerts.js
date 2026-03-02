/**
 * Alert Dismiss - Click [data-alert-close] buttons to dismiss alerts
 * Dispatches 'alert:dismiss' event on dismissed element
 */
document.addEventListener('click', (event) => {
  const closeButton = event.target.closest('[data-alert-close]');
  if (!closeButton) return;

  const alert = closeButton.closest('.alert');
  if (!alert) return;

  // Optional: Dispatch custom event for tracking/logging
  const dismissEvent = new CustomEvent('alert:dismiss', {
    bubbles: true,
    detail: {
      type: alert.className.match(/alert--(\w+)/)?.[1] || 'generic',
      element: alert,
    },
  });
  alert.dispatchEvent(dismissEvent);

  // Remove the alert with fade-out effect
  alert.style.opacity = '0';
  alert.style.transition = 'opacity 200ms ease-out';

  setTimeout(() => {
    alert.remove();
  }, 200);
});
