document.addEventListener('DOMContentLoaded', () => {

  const audio = document.getElementById('bgMusic');
  const toggleBtn = document.getElementById('musicToggle');
  const icon = document.getElementById('musicIcon');

  if (!audio || !toggleBtn || !icon) return;

  const tracks = window.TRACKS_FROM_ADMIN || [];
  if (!tracks.length) {
    console.warn('No background music tracks found');
    return;
  }

  let trackIndex = parseInt(localStorage.getItem('musicTrack')) || 0;
  let shouldPlay = localStorage.getItem('musicPlaying') === 'true';
  let savedTime = parseFloat(localStorage.getItem('musicTime')) || 0;

  trackIndex = trackIndex % tracks.length;

  audio.src = tracks[trackIndex];
  audio.volume = 0.4;
  audio.currentTime = savedTime;

  toggleBtn.addEventListener('click', () => {
    if (audio.paused) {
      audio.play().then(() => {
        localStorage.setItem('musicPlaying', 'true');
        setPauseIcon();
      }).catch(err => {
        console.warn('Audio play blocked:', err);
      });
    } else {
      audio.pause();
      localStorage.setItem('musicPlaying', 'false');
      setPlayIcon();
    }
  });

  audio.addEventListener('timeupdate', () => {
    localStorage.setItem('musicTime', audio.currentTime);
  });

  audio.addEventListener('ended', () => {
    trackIndex = (trackIndex + 1) % tracks.length;
    localStorage.setItem('musicTrack', trackIndex);
    audio.src = tracks[trackIndex];
    audio.currentTime = 0;
    audio.play();
  });

  if (shouldPlay) {
    audio.play().catch(() => {});
    setPauseIcon();
  } else {
    setPlayIcon();
  }

  function setPlayIcon() {
    icon.innerHTML =
      `<path d="M7 6v12l10-6L7 6z" fill="currentColor"></path>`;
  }

  function setPauseIcon() {
    icon.innerHTML =
      `<path d="M6 5h4v14H6zM14 5h4v14h-4z" fill="currentColor"></path>`;
  }

});
