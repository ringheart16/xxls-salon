document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
      if (!window.confirm(form.dataset.confirm || 'Are you sure?')) event.preventDefault();
    });
  });
  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => document.body.classList.remove('nav-open'));
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') document.body.classList.remove('nav-open');
  });
  const dateInput = document.querySelector('input[type="date"][name="appointment_date"]');
  if (dateInput) dateInput.min = new Date().toLocaleDateString('en-CA');
});
