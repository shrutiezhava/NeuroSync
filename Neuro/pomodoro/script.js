// DOM Elements
const minutesEl = document.getElementById('minutes');
const secondsEl = document.getElementById('seconds');
const startBtn = document.getElementById('start-btn');
const resetBtn = document.getElementById('reset-btn');
const pomodoroBtn = document.getElementById('pomodoro-btn');
const breakBtn = document.getElementById('break-btn');
const longBreakBtn = document.getElementById('long-break-btn');
const timerCirclePath = document.querySelector('.timer-circle-path');
const addTaskBtn = document.getElementById('add-task-btn');
const taskInput = document.getElementById('task-input');
const currentTaskEl = document.getElementById('current-task');
const sessionCountEl = document.getElementById('session-count');

// Settings inputs
const pomodoroTimeEl = document.getElementById('pomodoro-time');
const shortBreakTimeEl = document.getElementById('short-break-time');
const longBreakTimeEl = document.getElementById('long-break-time');
const sessionsUntilLongBreakEl = document.getElementById('sessions-until-long-break');

// Timer state variables
let timer;
let minutes;
let seconds;
let totalSeconds;
let originalTotalSeconds;
let isRunning = false;
let currentMode = 'pomodoro';
let completedSessions = 0;
let sessionDots = [];

// Initialize timer settings
let pomodoroTime = 25;
let shortBreakTime = 5;
let longBreakTime = 15;
let sessionsUntilLongBreak = 4;

// Circumference of the circle (2πr)
const circumference = 2 * Math.PI * 117;
timerCirclePath.style.strokeDasharray = circumference;
timerCirclePath.style.strokeDashoffset = 0;

// Audio for notifications
const timerEndSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBIAAAAHAAEAQB8AAEAfAAABAAgAAABMSVNUHAAAAElORk9JU0ZUWgAAAENQUklHSFTK/v//aHR0cDovL3d3dy5mcmVlc291bmQuAExJU1QSBAAAZGlzcAEAAAfGjOA2mAAAJXBkdGEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8CAAA/QEBAwP/AAQBA////AAAAP0BAQMDAAAABAACAwP/AwAD//wAAAAABAQH///8AAP//AAAAPwAAwD8AAAAAwMAAAMD/AACA//8AAAAA/z///wEBAD8AAAAA//8AAP//AAD///7+/v7+/gAAAAD//wAAAAAAAAAA//8AAAEA/v8AAAAA/v7+/v7+/v4CAAEA/v4AAP//AQEBAQEA/v7+/v//AAABAQAAAQEAAAAA//8BAf//AAAAAAEA//8BAf//AAAAAAAA//8BAf//AAAAAP//AAD//wAA//8AAP7+AAABAQEA//8AAAAAAAD//wAAAAD//wEBAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AQH//wAAAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8BAQAA//8AAP//AAAAAAAAAQEAAP//AAD//wAA//8AAP//AQH+/gAA//8AAP//AAD//wAA//8AAAAA//8AAP//AAD//wAAAAAAAAAAAAD//wAA//8BAf7+AAD//wEBAAD//wAA//8AAP//AQEAAP//AQEAAP//AAD//wAA//8BAf//AAAAAP//AAD//wAA//8AAAAAAAAAAP//AQEBAQEAAQEAAP//AAABAQEAAQEAAP//AAAAAAAA//8AAAAA//8AAP//AAD//wAAAAD//wAA//8AAP//AAD//wAAAQEBAQAA//8AAAAA//8AAAAAAAAAAAAAAQH//wAAAAD//wAA//8AAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAAAA//8AAP//AAD//wAA//8AAP//AQH+/gAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAABAQEA//8AAAAA//8AAP//AAD//wAA//8AAP//AQEAAP//AAD//wAA//8AAP//AQH//wAA//8AAP//AAD//wAA//8AAAAAAAD//wAA//8AAP//AQEAAP//AQH//wAAAAAAAAAA//8AAP//AQEAAP//AAAAAP//AAD//wEBAAD//wAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');

// Initialize the timer
function initTimer() {
  // Set initial time based on mode
  switch(currentMode) {
    case 'pomodoro':
      minutes = pomodoroTime;
      break;
    case 'break':
      minutes = shortBreakTime;
      break;
    case 'long-break':
      minutes = longBreakTime;
      break;
  }
  
  seconds = 0;
  totalSeconds = minutes * 60;
  originalTotalSeconds = totalSeconds;
  
  // Update display
  updateTimerDisplay();
  updateProgress(1);
  
  // Reset button text
  startBtn.textContent = '▶';
  isRunning = false;
  
  // Clear any existing intervals
  clearInterval(timer);
}

// Update the timer display
function updateTimerDisplay() {
  minutesEl.textContent = String(minutes).padStart(2, '0');
  secondsEl.textContent = String(seconds).padStart(2, '0');
}

// Update the circular progress indicator
function updateProgress(ratio) {
  const offset = circumference - (circumference * ratio);
  timerCirclePath.style.strokeDashoffset = offset;
}

// Start or pause the timer
function toggleTimer() {
  if (isRunning) {
    // Pause timer
    clearInterval(timer);
    startBtn.textContent = '▶';
    isRunning = false;
  } else {
    // Start timer
    startBtn.textContent = '⏸';
    isRunning = true;
    
    timer = setInterval(() => {
      if (seconds > 0) {
        seconds--;
      } else if (minutes > 0) {
        minutes--;
        seconds = 59;
      } else {
        // Timer complete
        clearInterval(timer);
        timerEndSound.play();
        isRunning = false;
        startBtn.textContent = '▶';
        
        // Handle session completion
        if (currentMode === 'pomodoro') {
          completedSessions++;
          updateSessionDots();
          
          // Check if we should go to a long break
          if (completedSessions % sessionsUntilLongBreak === 0) {
            switchMode('long-break');
          } else {
            switchMode('break');
          }
        } else {
          // After break, go back to pomodoro
          switchMode('pomodoro');
        }
        
        return;
      }
      
      updateTimerDisplay();
      
      // Update progress
      const currentTotalSeconds = (minutes * 60) + seconds;
      const ratio = currentTotalSeconds / originalTotalSeconds;
      updateProgress(ratio);
    }, 1000);
  }
}

// Switch between different timer modes
function switchMode(mode) {
  // Update active mode
  currentMode = mode;
  
  // Update UI to reflect the current mode
  document.body.classList.remove('pomodoro-mode', 'break-mode', 'long-break-mode');
  document.body.classList.add(`${mode}-mode`);
  
  // Update active button
  pomodoroBtn.classList.remove('active');
  breakBtn.classList.remove('active');
  longBreakBtn.classList.remove('active');
  
  switch(mode) {
    case 'pomodoro':
      pomodoroBtn.classList.add('active');
      break;
    case 'break':
      breakBtn.classList.add('active');
      break;
    case 'long-break':
      longBreakBtn.classList.add('active');
      break;
  }
  
  // Reset and initialize timer
  initTimer();
}

// Update session dots
function updateSessionDots() {
  // Clear existing dots
  sessionCountEl.innerHTML = '';
  sessionDots = [];
  
  // Create new dots based on sessionsUntilLongBreak setting
  for (let i = 0; i < sessionsUntilLongBreak; i++) {
    const dot = document.createElement('div');
    dot.classList.add('session-dot');
    
    // Mark completed sessions
    if (i < (completedSessions % sessionsUntilLongBreak)) {
      dot.classList.add('completed');
    }
    
    sessionCountEl.appendChild(dot);
    sessionDots.push(dot);
  }
}

// Set current task
function setCurrentTask() {
  const taskText = taskInput.value.trim();
  if (taskText) {
    currentTaskEl.textContent = taskText;
    taskInput.value = '';
  }
}

// Update settings
function updateSettings() {
  pomodoroTime = parseInt(pomodoroTimeEl.value) || 25;
  shortBreakTime = parseInt(shortBreakTimeEl.value) || 5;
  longBreakTime = parseInt(longBreakTimeEl.value) || 15;
  sessionsUntilLongBreak = parseInt(sessionsUntilLongBreakEl.value) || 4;
  
  // Update session dots
  updateSessionDots();
  
  // Reinitialize timer with new settings
  initTimer();
}

// Event listeners
startBtn.addEventListener('click', toggleTimer);
resetBtn.addEventListener('click', initTimer);
pomodoroBtn.addEventListener('click', () => switchMode('pomodoro'));
breakBtn.addEventListener('click', () => switchMode('break'));
longBreakBtn.addEventListener('click', () => switchMode('long-break'));
addTaskBtn.addEventListener('click', setCurrentTask);
taskInput.addEventListener('keypress', (e) => {
  if (e.key === 'Enter') {
    setCurrentTask();
  }
});

// Settings event listeners
pomodoroTimeEl.addEventListener('change', updateSettings);
shortBreakTimeEl.addEventListener('change', updateSettings);
longBreakTimeEl.addEventListener('change', updateSettings);
sessionsUntilLongBreakEl.addEventListener('change', updateSettings);

// Initialize the app
initTimer();
updateSessionDots();

// Set the circumference for the SVG circle
timerCirclePath.style.strokeDasharray = circumference;
timerCirclePath.style.strokeDashoffset = '0';