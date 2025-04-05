function sendMessage() {
    let userInput = document.getElementById("user-input").value;
    let chatBox = document.getElementById("chat-box");

    if (userInput.trim() === "") return;

    chatBox.innerHTML += `<div class="user-message"><b>You:</b> ${userInput}</div>`;

    fetch("chatbot.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ user_input: userInput })
    })
    .then(response => response.json())
    .then(data => {
        let botResponse = data.response || "I'm here to listen. 💙";
        chatBox.innerHTML += `<div class="bot-message"><b>Bot:</b> ${botResponse}</div>`;
    })
    .catch(error => {
        chatBox.innerHTML += `<div class="error-message"><b>Error:</b> ${error}</div>`;
    });

    document.getElementById("user-input").value = "";
}

// 🎤 Google Assistant-Style Voice Recognition
function startVoiceRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        alert("Your browser does not support Speech Recognition.");
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = "en-IN";
    recognition.start();

    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        document.getElementById("user-input").value = transcript;
        sendMessage();
    };

    recognition.onerror = function(event) {
        console.error("Voice recognition error:", event.error);
        alert("Voice recognition error: " + event.error);
    };
}