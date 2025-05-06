function togglePassword(elem) {
    var passInput = document.getElementById("pass");
    if (passInput.type === "password") {
        passInput.type = "text";
        elem.classList.add("active");
        elem.textContent = "Hide";
    } else {
        passInput.type = "password";
        elem.classList.remove("active");
        elem.textContent = "Show";
    }
}

var roleSelect = document.getElementById('roleSelect');
var registerBtn = document.getElementById('registerBtn');
var hiddenRoleInput = document.getElementById('hiddenRoleInput');
let tooltipTimeout;

roleSelect.addEventListener('change', function() {
    hiddenRoleInput.value = this.value;
    if (this.value === 'operator' || this.value === 'admin') {
        registerBtn.disabled = true;
        registerBtn.classList.add("btn-disabled");
    } else {
        registerBtn.disabled = false;
        registerBtn.classList.remove("btn-disabled");
    }
});

function closePopup() {
    var popup = document.getElementById("popup");
    popup.classList.add("fade-out");
    setTimeout(function() {
        popup.style.display = "none";
        popup.classList.remove("fade-out");
        document.getElementById("popupText").textContent = "";
    }, 500);
}

function showPopup(message) {
    var popup = document.getElementById("popup");
    var popupText = document.getElementById("popupText");
    popupText.textContent = message;
    popup.style.display = "flex";
}

function showConfirmPopup(message) {
    var confirmPopup = document.getElementById("confirmPopup");
    var confirmPopupText = document.getElementById("confirmPopupText");
    confirmPopupText.textContent = message;
    confirmPopup.style.display = "flex";

    document.getElementById("confirmYes").onclick = function() {
        var confirmContent = document.querySelector("#confirmPopup .confirm-popup-content");
        confirmContent.innerHTML = '<div class="spinner"></div>';
        
        var email = document.getElementById("email").value.trim();
        var password = document.getElementById("pass").value.trim();
        var role = document.getElementById("hiddenRoleInput").value;

        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                action: role === 'admin' ? 'login_admin' : 
                       role === 'operator' ? 'login_operator' : 'login_customer',
                email: email,
                password: password
            })
        })
        .then(response => {
            console.log("Raw response:", response);
            if (!response.ok) {
                console.error("Network response not OK");
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log("Response data:", data);
            if (data.status === 'OK') {
                console.log("Redirecting to:", data.redirect);
                window.location.href = data.redirect;
            } else {
                showPopup(data.error || "Login failed");
                resetConfirmPopup();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showPopup("Error: " + error.message);
            resetConfirmPopup();
        });
    };

    document.getElementById("confirmNo").onclick = resetConfirmPopup;
}

function resetConfirmPopup() {
    var confirmPopup = document.getElementById("confirmPopup");
    confirmPopup.classList.add("fade-out");
    setTimeout(function() {
        confirmPopup.style.display = "none";
        confirmPopup.classList.remove("fade-out");
        confirmPopup.innerHTML = `
            <div class="confirm-popup-content" style="min-width: 300px; min-height: 125px; position: relative;">
                <p id="confirmPopupText"></p>
                <div class="confirm-popup-buttons" id="confirmPopupButtons">
                    <button class="btn-yes" id="confirmYes">Yes</button>
                    <button class="btn-no" id="confirmNo">No</button>
                </div>
            </div>`;
    }, 500);
}

function validateLogin() {
    var email = document.getElementById("email").value.trim();
    var pass = document.getElementById("pass").value.trim();

    if (!email || !pass) {
        showPopup("Email and password are required.");
        return;
    }
    showConfirmPopup("Are you sure you want to login with these credentials?");
}

document.addEventListener("DOMContentLoaded", function() {
    const dropdownBtn = document.querySelector(".dropdown-btn");
    const dropdownMenu = document.querySelector(".dropdown-menu");
    const dropdownItems = document.querySelectorAll(".dropdown-item");
    const roleInput = document.getElementById("roleSelect");
    const hiddenRoleInput = document.getElementById("hiddenRoleInput");
    const registerBtn = document.getElementById("registerBtn");

    dropdownBtn.addEventListener("click", function(e) {
        if (dropdownMenu.style.display === "block") {
            dropdownMenu.classList.remove("show");
            dropdownBtn.classList.remove("active");
            setTimeout(() => {
                dropdownMenu.style.display = "none";
            }, 300);
        } else {
            dropdownMenu.style.display = "block";
            setTimeout(() => {
                dropdownMenu.classList.add("show");
                dropdownBtn.classList.add("active");
            }, 10);
        }
    });

    dropdownItems.forEach(item => {
        item.addEventListener("click", function() {
            dropdownBtn.textContent = this.textContent;
            roleInput.value = this.dataset.value;
            hiddenRoleInput.value = this.dataset.value;
            dropdownMenu.classList.remove("show");
            dropdownBtn.classList.remove("active");
            dropdownMenu.style.display = "none";

            const registerTooltip = document.getElementById("registerTooltip");
            if (this.dataset.value === "operator" || this.dataset.value === "admin") {
                registerBtn.disabled = true;
                registerBtn.classList.add("btn-disabled");
                if (this.dataset.value === "operator") {
                    registerTooltip.textContent = "Operator cannot make a new account. Please contact the admin.";
                } else if (this.dataset.value === "admin") {
                    registerTooltip.textContent = "Error 804: Admin account cannot be registered.";
                }
            } else {
                registerBtn.disabled = false;
                registerBtn.classList.remove("btn-disabled");
                registerTooltip.textContent = "";
            }
        });
    });
    
    registerBtn.addEventListener('mouseenter', function() {
        if (this.classList.contains('btn-disabled')) {
            const tooltip = document.getElementById('registerTooltip');
            tooltip.style.opacity = '1';
        }
    });

    registerBtn.addEventListener('mouseleave', function() {
        const tooltip = document.getElementById('registerTooltip');
        tooltip.style.opacity = '0';
    });

    document.addEventListener("click", function(e) {
        if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove("show");
            dropdownBtn.classList.remove("active");
            setTimeout(() => {
                dropdownMenu.style.display = "none";
            }, 300);
        }
    });
});