// Toggle password visibility
function togglePassword(inputId, elem) {
    var passInput = document.getElementById(inputId);
    if (passInput.type === "password") {
        passInput.type = "text";
        elem.textContent = "Hide";
    } else {
        passInput.type = "password";
        elem.textContent = "Show";
    }
}

function closePopup() {
    var popup = document.getElementById("popup");
    popup.style.display = "none";
}

function showPopup(message) {
    document.getElementById("popupText").textContent = message;
    document.getElementById("popup").style.display = "flex";
}

function showConfirmPopup(message) {
    var confirmPopup = document.getElementById("confirmPopup");
    var confirmPopupText = document.getElementById("confirmPopupText");
    confirmPopupText.textContent = message;
    confirmPopup.style.display = "flex";

    var yesButton = document.getElementById("confirmYes");
    var noButton = document.getElementById("confirmNo");

    yesButton.onclick = function() {
        var confirmContent = document.querySelector("#confirmPopup .confirm-popup-content");
        // Replace the content with a spinner while submitting the registration
        confirmContent.innerHTML = '<div class="spinner"></div>';
        submitRegistration();
    };

    noButton.onclick = function() {
        // Simply hide the confirm popup without modifying the form data
        confirmPopup.style.display = "none";
    };
}

function submitRegistration() {
    var phoneInput = document.getElementById("phone");
    var iti = window.intlTelInputGlobals.getInstance(phoneInput);
    var phone = iti.getNumber();

    var formData = new FormData(document.getElementById("registrationForm"));
    formData.set("cus_phone", phone);
    formData.append("action", "register");

    fetch(window.location.href, {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("confirmPopup").style.display = "none";
        if (data.status === "OK") {

            window.location.href = "login";
        } else {
            showPopup("Registration failed.");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showPopup("An error occurred.");
    });
}

function validateRegister() {
    var name = document.getElementById("name").value.trim();
    var email = document.getElementById("email").value.trim();
    var phoneInput = document.getElementById("phone");
    var iti = window.intlTelInputGlobals.getInstance(phoneInput);
    var phone = iti.getNumber();
    var password = document.getElementById("pass").value.trim();
    var confirmPassword = document.getElementById("confirm-pass").value.trim();

    if (!name || !email || !phone || !password || !confirmPassword) {
        showPopup("Please fill in all fields.");
        return;
    }
    if (password !== confirmPassword) {
        showPopup("Passwords do not match.");
        return;
    }

    showConfirmPopup("Are you sure you want to register with this account?");
}

var input = document.querySelector("#phone");
window.intlTelInput(input, {
    initialCountry: "id",
    preferredCountries: ["id", "us", "gb"],
    separateDialCode: true,
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
});
