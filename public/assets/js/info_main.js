// assets/js/info_main.js
// UX helpers for InfoVault landing

document.addEventListener('DOMContentLoaded', () => {

  /* ---------------------------
     Card entrance animation
  ---------------------------- */
  const cards = document.querySelectorAll('.action-card');

  cards.forEach((card, index) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(16px) scale(0.98)';

    setTimeout(() => {
      card.style.transition =
        'opacity .45s ease, transform .45s cubic-bezier(.2,.9,.2,1)';
      card.style.opacity = '1';
      card.style.transform = 'translateY(0) scale(1)';
    }, 120 + index * 90);
  });

  /* ---------------------------
     Keyboard shortcuts
  ---------------------------- */
  document.addEventListener('keydown', (e) => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

    switch (e.key.toLowerCase()) {
      case 'f':
        window.location.href = 'infovault_files.php';
        break;
      case 'c':
        window.location.href = 'infovault_flashcards.php';
        break;
      case 'm':
        window.location.href = 'infovault_mindmaps.php';
        break;
      case 'r':
        window.location.href = 'infovault_reports.php';
        break;
    }
  });

});
