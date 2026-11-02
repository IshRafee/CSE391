// =========================================================
// Question 2: Stopwatch (35 Marks)
// Requirements:
// 1. Timer counts in steps of 3 seconds (3, 6, 9, ... 30).
// 2. Timer automatically stops at 30 seconds unless reset.
// 3. User Controls:
//    - Start Button: Begins the timer.
//    - Stop Button: Pauses the timer (resumes from last recorded time).
//    - Reset Button: Resets the timer to 0.
// =========================================================

// Step 1: Select HTML Elements
const display = document.getElementById("display");
const statusMessage = document.getElementById("status");
const startBtn = document.getElementById("startBtn");
const stopBtn = document.getElementById("stopBtn");
const resetBtn = document.getElementById("resetBtn");

// Step 2: Initialize Variables & Constants
let seconds = 0;               // Tracks the recorded time
let timerId = null;            // Stores the setInterval reference
const STEP_SECONDS = 3;        // Multiples of 3 seconds
const MAX_SECONDS = 30;        // Maximum time limit
const INTERVAL_MS = 3000;      // 3 seconds in milliseconds (real-time interval)

// Step 3: Helper function to update the display
function updateDisplay() {
  display.textContent = seconds;
}

// Step 4: Start Timer Function
function startTimer() {
  // Prevent multiple intervals if already running
  if (timerId !== null) {
    return;
  }

  // If already reached the 30-second limit, ask user to reset
  if (seconds >= MAX_SECONDS) {
    statusMessage.textContent = "Timer reached 30s. Click Reset to start over.";
    statusMessage.className = "status-message finished";
    return;
  }

  // Update status message
  statusMessage.textContent = "Timer is running...";
  statusMessage.className = "status-message running";

  // Start the timer to increment in multiples of 3 seconds every 3000ms
  timerId = setInterval(function () {
    seconds += STEP_SECONDS;
    updateDisplay();

    // Automatically stop when reaching 30 seconds
    if (seconds >= MAX_SECONDS) {
      clearInterval(timerId);
      timerId = null;
      statusMessage.textContent = "Stopped automatically at 30 seconds!";
      statusMessage.className = "status-message finished";
    }
  }, INTERVAL_MS);
}

// Step 5: Stop (Pause) Timer Function
function stopTimer() {
  // If timer is currently running, pause it
  if (timerId !== null) {
    clearInterval(timerId);
    timerId = null;
    statusMessage.textContent = `Paused at ${seconds}s. Click Start to resume.`;
    statusMessage.className = "status-message paused";
  }
}

// Step 6: Reset Timer Function
function resetTimer() {
  // Clear any active running timer
  if (timerId !== null) {
    clearInterval(timerId);
    timerId = null;
  }

  // Reset seconds to 0 and update display
  seconds = 0;
  updateDisplay();

  // Reset status text
  statusMessage.textContent = "Press \"Start\" to begin";
  statusMessage.className = "status-message";
}

// Step 7: Attach Click Event Listeners to Buttons
startBtn.addEventListener("click", startTimer);
stopBtn.addEventListener("click", stopTimer);
resetBtn.addEventListener("click", resetTimer);
