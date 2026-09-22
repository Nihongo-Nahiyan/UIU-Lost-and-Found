const sendButton = document.getElementById("sendButton");
const messageInput = document.getElementById("messageInput");
const messageBody = document.getElementById("messageBody");

// Get the conversation information from the URL
const urlParams = new URLSearchParams(window.location.search);

const currentReceiverId = urlParams.get("with");
const currentFoundId = urlParams.get("found");

sendButton.addEventListener("click", function () {

    const message = messageInput.value.trim();

    if (message === "") {
        return;
    }

    if (!currentReceiverId || !currentFoundId) {
        alert("Conversation information is missing.");
        return;
    }

    const formData = new FormData();

    formData.append("send_message", "1");
    formData.append("receiver_id", currentReceiverId);
    formData.append("found_id", currentFoundId);
    formData.append("message", message);

    sendButton.disabled = true;

    fetch("messages.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        if (data.success) {

            messageInput.value = "";

            location.reload();

        } else {

            alert("Message could not be sent.");

        }

    })
    .catch(error => {

        console.error(error);

        alert("Something went wrong while sending the message.");

    })
    .finally(() => {

        sendButton.disabled = false;

    });

});


// SEND MESSAGE WHEN ENTER KEY IS PRESSED

messageInput.addEventListener("keydown", function (event) {

    if (event.key === "Enter") {

        event.preventDefault();

        sendButton.click();
    }

});
// DELETE MESSAGE

document.querySelectorAll(".delete-message-btn").forEach(function (button) {

    button.addEventListener("click", function () {

        const messageId = this.dataset.messageId;

        const confirmDelete = confirm("Do you want to delete this message?");

        if (!confirmDelete) {
            return;
        }

        const formData = new FormData();

        formData.append("delete_message", "1");
        formData.append("message_id", messageId);

        fetch("messages.php", {

            method: "POST",
            body: formData

        })
        .then(response => response.json())
        .then(data => {

            if (data.success) {

                location.reload();

            } else {

                alert("Message could not be deleted.");

            }

        })
        .catch(error => {

            console.error(error);

            alert("Something went wrong while deleting the message.");

        });

    });

});