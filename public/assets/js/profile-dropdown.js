document.addEventListener('DOMContentLoaded', () => {
  const trigger = document.getElementById('profileTrigger');
  const menu = document.querySelector('.profile-menu');
  const dropdown = document.getElementById('profileDropdown');

  if (!trigger || !menu || !dropdown) return;

  trigger.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    menu.classList.toggle('open');
  });

  dropdown.addEventListener('click', (e) => {
    e.stopPropagation(); // allow clicking links
  });

  document.addEventListener('click', () => {
    menu.classList.remove('open');
  });
});
