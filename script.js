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