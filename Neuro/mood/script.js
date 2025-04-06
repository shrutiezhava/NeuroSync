// Function to set the theme
function setTheme(theme) {
    document.body.classList.remove('light-theme', 'dark-theme');
    document.body.classList.add(`${theme}-theme`);
    localStorage.setItem('preferred-theme', theme);
}

// Check for saved theme preference or default to light theme
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('preferred-theme') || 'light';
    setTheme(savedTheme);
});

// Font size management
let currentFontSize = 100; // 100% is default
const minFontSize = 80; // 80% minimum
const maxFontSize = 200; // 200% maximum
const stepSize = 10; // 10% increment/decrement

function changeFontSize(action) {
    if (action === 'increase' && currentFontSize < maxFontSize) {
        currentFontSize += stepSize;
    } else if (action === 'decrease' && currentFontSize > minFontSize) {
        currentFontSize -= stepSize;
    }

    // Apply the font size to the html element
    document.documentElement.style.fontSize = `${currentFontSize}%`;
    
    // Save the preference
    localStorage.setItem('preferred-font-size', currentFontSize);
}

// Initialize font size from saved preference
document.addEventListener('DOMContentLoaded', () => {
    const savedFontSize = localStorage.getItem('preferred-font-size');
    if (savedFontSize) {
        currentFontSize = parseInt(savedFontSize);
        document.documentElement.style.fontSize = `${currentFontSize}%`;
    }
});

// Dyslexic font toggle
function toggleDyslexicFont() {
    const button = document.getElementById('dyslexicFont');
    const isEnabled = document.documentElement.classList.toggle('dyslexic-font');
    
    // Update button state
    button.classList.toggle('active', isEnabled);
    button.setAttribute('aria-pressed', isEnabled);
    
    // Save preference
    localStorage.setItem('dyslexic-font-enabled', isEnabled);
}

// Initialize dyslexic font preference
document.addEventListener('DOMContentLoaded', () => {
    const dyslexicFontEnabled = localStorage.getItem('dyslexic-font-enabled') === 'true';
    if (dyslexicFontEnabled) {
        document.documentElement.classList.add('dyslexic-font');
        const button = document.getElementById('dyslexicFont');
        button.classList.add('active');
        button.setAttribute('aria-pressed', 'true');
    }
});

// Audio context and sounds
let audioContext;
let noiseNodes = {};
let volumeNode;

// Initialize audio context
function initAudioContext() {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    volumeNode = audioContext.createGain();
    volumeNode.connect(audioContext.destination);
}

// Generate noise
function createNoise(type) {
    const bufferSize = 4096;
    const noiseNode = audioContext.createScriptProcessor(bufferSize, 1, 1);
    
    noiseNode.onaudioprocess = (e) => {
        const output = e.outputBuffer.getChannelData(0);
        
        for (let i = 0; i < bufferSize; i++) {
            switch(type) {
                case 'brown':
                    output[i] = Math.random() * 2 - 1;
                    output[i] = (output[i] + previousValue) / 2;
                    previousValue = output[i];
                    break;
                case 'pink':
                    output[i] = (Math.random() * 2 - 1) * 0.5;
                    break;
                case 'white':
                    output[i] = Math.random() * 2 - 1;
                    break;
            }
        }
    };

    return noiseNode;
}

// Handle feature clicks
function handleFeature(feature) {
    const card = document.querySelector(`[onclick*="${feature}"]`);
    card.style.animation = 'cardPulse 0.5s ease';
    
    setTimeout(() => {
        card.style.animation = '';
    }, 500);

    switch(feature) {
        case 'chatbot':
            toggleChatInterface('bot');
            break;
        case 'chatroom':
            toggleChatInterface('room');
            break;
        case 'audioTools':
            toggleAudioControls();
            break;
    }
}

// Toggle audio controls
function toggleAudioControls() {
    const audioControls = document.querySelector('.audio-controls');
    audioControls.classList.toggle('hidden');
    
    if (!audioContext) {
        initAudioContext();
    }
}

// Play/Stop sound
function toggleSound(type) {
    if (noiseNodes[type]?.playing) {
        noiseNodes[type].node.disconnect();
        noiseNodes[type].playing = false;
        document.querySelector(`[data-sound="${type}"]`).classList.remove('active');
    } else {
        if (noiseNodes[type]?.node) {
            noiseNodes[type].node.connect(volumeNode);
        } else {
            noiseNodes[type] = {
                node: createNoise(type),
                playing: false
            };
            noiseNodes[type].node.connect(volumeNode);
        }
        noiseNodes[type].playing = true;
        document.querySelector(`[data-sound="${type}"]`).classList.add('active');
    }
}

// Volume control
document.querySelector('.volume-slider')?.addEventListener('input', (e) => {
    const volume = e.target.value / 100;
    volumeNode.gain.value = volume;
});

// Initialize audio buttons
document.querySelectorAll('.audio-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        toggleSound(btn.dataset.sound);
    });
});

// Add animation keyframes
const styleSheet = document.styleSheets[0];
styleSheet.insertRule(`
    @keyframes cardPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
`, styleSheet.cssRules.length);

// Auth functionality
function toggleAuthModal(type) {
    const modal = document.getElementById('authModal');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    modal.classList.remove('hidden');
    
    if (type === 'login') {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
    } else {
        registerForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
    }
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    modal.classList.add('hidden');
    loginForm.classList.add('hidden');
    registerForm.classList.add('hidden');
}

function switchForm(type) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    if (type === 'login') {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
    } else {
        registerForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
    }
}

// Update login form handler
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('email', this.email.value);
    formData.append('password', this.password.value);
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                closeAuthModal();
                location.reload();
            }
        } else {
            alert(data.message);
        }
    });
});

document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'register');
    formData.append('username', this.username.value);
    formData.append('email', this.email.value);
    formData.append('password', this.password.value);
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            switchForm('login');
            alert('Registration successful! Please login.');
        } else {
            alert(data.message);
        }
    });
}); 