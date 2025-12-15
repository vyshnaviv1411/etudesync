document.addEventListener('DOMContentLoaded', () => {
let duration = 25 * 60;  // default 25 min
let timeLeft = duration;
let timerInterval = null;

const timerDisplay = document.getElementById("timer");
const streakDisplay = document.getElementById("streak");

// Load saved data
if (localStorage.getItem("timer_timeLeft")) {
  timeLeft = parseInt(localStorage.getItem("timer_timeLeft"));
}
if (localStorage.getItem("timer_streak")) {
  streakDisplay.innerText = localStorage.getItem("timer_streak");
}

function updateDisplay() {
  let minutes = Math.floor(timeLeft / 60);
  let seconds = timeLeft % 60;
  timerDisplay.innerText =
    `${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
}

function startTimer() {
  if (timerInterval) return;

  timerInterval = setInterval(() => {
    timeLeft--;
    updateDisplay();

    localStorage.setItem("timer_timeLeft", timeLeft);

    if (timeLeft <= 0) {
      clearInterval(timerInterval);
      timerInterval = null;

      let streak = parseInt(localStorage.getItem("timer_streak")) || 0;
      streak++;
      localStorage.setItem("timer_streak", streak);
      streakDisplay.innerText = streak;

      alert("Pomodoro complete!");
    }
  }, 1000);
}

function pauseTimer() {
  clearInterval(timerInterval);
  timerInterval = null;
}

function resetTimer() {
  pauseTimer();
  timeLeft = duration;
  updateDisplay();
  localStorage.setItem("timer_timeLeft", timeLeft);
}

document.getElementById("startBtn").addEventListener("click", startTimer);
document.getElementById("pauseBtn").addEventListener("click", pauseTimer);
document.getElementById("resetBtn").addEventListener("click", resetTimer);

updateDisplay();
