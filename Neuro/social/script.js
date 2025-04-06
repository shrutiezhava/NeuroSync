
const scenarios = [
    {
        scenario: "You're at a school event and see a classmate standing alone. What do you do?",
        options: [
            { 
                text: "Approach and ask if they'd like to join you", 
                feedback: "Great choice! This shows kindness and social awareness.",
                correct: true
            },
            { 
                text: "Walk away to avoid awkwardness", 
                feedback: "While it might feel comfortable, this misses an opportunity to connect.",
                correct: false
            },
            { 
                text: "Stare but don't say anything", 
                feedback: "This might make the other person uncomfortable.",
                correct: false
            }
        ]
    },
    {
        scenario: "Someone shares something personal with you. How do you respond?",
        options: [
            { 
                text: "Change the subject quickly", 
                feedback: "This might make the person feel unheard or dismissed.",
                correct: false
            },
            { 
                text: "Start talking about yourself immediately", 
                feedback: "This takes the focus away from the person sharing.",
                correct: false
            },
            { 
                text: "Listen actively and offer a simple, supportive response", 
                feedback: "Excellent! Showing you're listening is key to meaningful interactions.",
                correct: true
            }
        ]
    },
    {
        scenario: "Your friend asks for your help on a project when you are busy. How do you respond?",
        options: [
            { 
                text: "'I am busy at the moment, but I can help you sometime later today.'", 
                feedback: "Shows willingness and sets boundaries.",
                correct: true
            },
            { 
                text: "'Sorry, I can't help. You should figure it out yourself.'", 
                feedback: "Might strain the friendship.",
                correct: false
            },
            { 
                text: "'Of course, I will drop everything and help you right now.'", 
                feedback: "Overly accomodating, might lead to being taken for granted in the future.",
                correct: false
            }
        ]
    },
    {
        scenario: "You accidentally bump into someone in a crowded area. Choose your response: ",
        options: [
            { 
                text: "'Oops, my bad.'", 
                feedback: "Might seem indifferent depending on the tone.",
                correct: false
            },
            { 
                text: "'Oh, I am so sorry! Are you okay?'", 
                feedback: "Diffuses tension.",
                correct: true
            },
            { 
                text: "'Watch where you're going!'", 
                feedback: "Makes the situation aggressive.",
                correct: false
            }
        ]
    },
    {
        scenario: "You disagree with something a friend says. What's the best approach?",
        options: [
            { 
                text: "Argue loudly to prove your point", 
                feedback: "This can create tension and damage the friendship.",
                correct: false
            },
            { 
                text: "Listen to their perspective and share yours calmly", 
                feedback: "Great choice! Respectful communication builds stronger relationships.",
                correct: true
            },
            { 
                text: "Ignore them and stop talking", 
                feedback: "Avoiding conflict doesn't resolve the underlying difference.",
                correct: false
            }
            
        ]
    }
];

let currentScenarioIndex = 0;
let selectedOption = null;

const scenarioElement = document.getElementById('scenario');
const optionsElement = document.getElementById('options');
const feedbackElement = document.getElementById('feedback');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

function loadScenario(index) {
    const scenario = scenarios[index];
    
    // Reset previous state
    selectedOption = null;
    feedbackElement.textContent = '';
    feedbackElement.className = 'feedback';

    // Load scenario text with fade effect
    scenarioElement.style.opacity = '0';
    scenarioElement.textContent = scenario.scenario;
    setTimeout(() => {
        scenarioElement.style.opacity = '1';
    }, 50);

    // Clear previous options
    optionsElement.innerHTML = '';

    // Create option buttons with staggered animation
    scenario.options.forEach((option, optionIndex) => {
        const optionButton = document.createElement('button');
        optionButton.textContent = option.text;
        optionButton.className = 'option';
        optionButton.setAttribute('aria-label', option.text);
        optionButton.style.opacity = '0';
        optionButton.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            optionButton.style.opacity = '1';
            optionButton.style.transform = 'translateY(0)';
        }, optionIndex * 100);
        
        optionButton.addEventListener('click', () => {
            // Remove previous selections
            document.querySelectorAll('.option').forEach(btn => {
                btn.classList.remove('selected', 'correct', 'incorrect');
            });

            // Mark selected option
            optionButton.classList.add('selected');
            selectedOption = optionIndex;

            // Show feedback with animation
            feedbackElement.textContent = option.feedback;
            feedbackElement.className = 'feedback ' + (option.correct ? 'positive' : 'negative');
            feedbackElement.classList.add('visible');
        });

        optionsElement.appendChild(optionButton);
    });

    // Update navigation buttons
    prevBtn.disabled = (index === 0);
    nextBtn.disabled = (index === scenarios.length - 1);
}

// Add smooth transitions for navigation
prevBtn.addEventListener('click', () => {
    if (currentScenarioIndex > 0) {
        currentScenarioIndex--;
        loadScenario(currentScenarioIndex);
    }
});

nextBtn.addEventListener('click', () => {
    if (currentScenarioIndex < scenarios.length - 1) {
        currentScenarioIndex++;
        loadScenario(currentScenarioIndex);
    }
});

// Initial load with animation
setTimeout(() => {
    loadScenario(currentScenarioIndex);
}, 100);
