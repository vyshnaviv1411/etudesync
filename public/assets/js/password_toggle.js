document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.toggle-eye').forEach(eye => {
    eye.addEventListener('click', () => {
      const input = eye.closest('.password-box').querySelector('input');
      if (!input) return;

      if (input.type === 'password') {
        input.type = 'text';
        eye.innerHTML = '🙈';
      } else {
        input.type = 'password';
        eye.innerHTML = '👁️';
      }
    });
  });
});
