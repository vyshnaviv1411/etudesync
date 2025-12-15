/* ================================
   FocusFlow — Productivity Timer
   Works with collab.css
===================================*/

let ffTimer = null;
let ffRemainingSeconds = 0;
let ffIsRunning = false;

// Convert minutes → seconds
function ffStartTimer() {
    const minutesInput = document.getElementById("ff-minutes");
    const status = document.getElementById("ff-status");

    let minutes = parseInt(minutesInput.value);

    if (isNaN(minutes) || minutes <= 0) {
        alert("Enter a valid number of minutes.");
        return;
    }

    ffRemainingSeconds = minutes * 60;
    ffIsRunning = true;

    status.innerText = "Focus Mode Running...";
    ffUpdateDisplay();

    ffTimer = setInterval(() => {
        ffRemainingSeconds--;

        ffUpdateDisplay();

        if (ffRemainingSeconds <= 0) {
            clearInterval(ffTimer);
            ffIsRunning = false;
            status.innerText = "⏳ Session Complete!";
        }
    }, 1000);
}

// Format MM:SS beautifully
function ffUpdateDisplay() {
    const display = document.getElementById("ff-timer-display");

    let mins = Math.floor(ffRemainingSeconds / 60);
    let secs = ffRemainingSeconds % 60;

    secs = secs < 10 ? "0" + secs : secs;

    display.innerText = `${mins}:${secs}`;
}

// Pause
function ffPause() {
    if (!ffIsRunning) return;
    clearInterval(ffTimer);
    ffIsRunning = false;
    document.getElementById("ff-status").innerText = "⏸ Paused";
}

// Resume
function ffResume() {
    if (ffIsRunning || ffRemainingSeconds <= 0) return;

    ffIsRunning = true;
    document.getElementById("ff-status").innerText = "Focus Mode Running...";

    ffTimer = setInterval(() => {
        ffRemainingSeconds--;
        ffUpdateDisplay();

        if (ffRemainingSeconds <= 0) {
            clearInterval(ffTimer);
            ffIsRunning = false;
            document.getElementById("ff-status").innerText = "✔ Completed";
        }
    }, 1000);
}

// Stop
function ffStop() {
    clearInterval(ffTimer);
    ffIsRunning = false;
    ffRemainingSeconds = 0;

    document.getElementById("ff-status").innerText = "Stopped";
    document.getElementById("ff-timer-display").innerText = "0:00";
}

