// public/assets/js/accessarena.js

document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll(".action-card");

  cards.forEach(card => {
    card.addEventListener("mouseenter", () => {
      card.style.cursor = "pointer";
    });
  });
});
