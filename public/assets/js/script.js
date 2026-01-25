(function () {
  const images = [
    'assets/images/background1.jpg',
    'assets/images/background2.jpg',
    'assets/images/background3.jpg',
    'assets/images/background4.jpg'
  ];

  const container = document.getElementById('bg-slider');
  if (!container) return;

  let index = 0;

  images.forEach((src, i) => {
    const slide = document.createElement('div');
    slide.className = 'slide';
    slide.style.backgroundImage = `url(${src})`;
    if (i === 0) slide.classList.add('visible');
    container.appendChild(slide);
  });

  const slides = container.querySelectorAll('.slide');

  setInterval(() => {
    slides[index].classList.remove('visible');
    index = (index + 1) % slides.length;
    slides[index].classList.add('visible');
  }, 4000);
})();
