document.querySelectorAll('.dashboard-actions-card a').forEach((link) => {
  link.addEventListener('focus', () => link.classList.add('is-focused'));
  link.addEventListener('blur', () => link.classList.remove('is-focused'));
});
