function sendMessage() {
    let userInput = document.getElementById("user-input").value.trim();
    let chatBox = document.getElementById("chat-box");

    if (userInput === "") return;

    // Display user message
    chatBox.innerHTML += `<div class="user-message"><b>You:</b> ${userInput}</div>`;

    // Send request to backend
    fetch("chatbot.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ user_input: userInput })
    })
    .then(response => response.json())
    .then(data => {
        let botResponse = data.response || "I'm sorry, I couldn't understand.";
        chatBox.innerHTML += `<div class="bot-message"><b>Bot:</b> ${botResponse}</div>`;
        chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll to the latest message
    })
    .catch(error => {
        chatBox.innerHTML += `<div class="error-message"><b>Error:</b> Unable to reach server.</div>`;
    });

    // Clear input field
    document.getElementById("user-input").value = "";
}
