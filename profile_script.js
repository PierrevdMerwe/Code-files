function togglePasswordVisibility() {
    var passwordInput1 = document.getElementById('password');
    var passwordInput2 = document.getElementById('newPassword');
    if (passwordInput1.type === "password" || passwordInput2.type === "password") {
        passwordInput1.type = "text";
        passwordInput2.type = "text";
    } else {
        passwordInput1.type = "password";
        passwordInput2.type = "password";
    }
}

var modal = document.getElementById("myModal");
var span = document.getElementsByClassName("close")[0];

function openModal() {
    modal.style.display = "block";
}

span.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

document.getElementById("signupForm").addEventListener("submit", function(event){
    var username = document.getElementById("newUsername").value;
    var password = document.getElementById("newPassword").value;

    if (username === "" || password === "") {
        alert("Username and password cannot be empty");
        event.preventDefault();
    }
});

$(document).ready(function() {
    // Check session on page load
    checkSession();

    // Handle the form submit event for sign in
    $('#signinForm').submit(function(e) {
        e.preventDefault();

        // Get the form data
        var formData = $(this).serialize();

        // Send the form data to the PHP script using AJAX
        $.ajax({
            type: 'POST',
            url: 'profile_connect.php',
            data: formData,
            success: function(response) {
                // Handle the response from the PHP script
                var data = JSON.parse(response);
                if (data.error) {
                    alert(data.error);  // Display an alert if there's an error
                    console.log(data.error);  // Log the error to the console
                } else {
                    checkSession();
                }
            }
        });
    });

    // Handle the form submit event for sign up
    $('#signupForm').submit(function(e) {
        e.preventDefault();

        // Get the form data
        var formData = $(this).serialize();

        // Send the form data to the PHP script using AJAX
        $.ajax({
            type: 'POST',
            url: 'profile_connect.php',
            data: formData,
            success: function(response) {
                // Handle the response from the PHP script
                var data = JSON.parse(response);
                if (data.error) {
                    alert(data.error);  // Display an alert if there's an error
                    console.log(data.error);  // Log the error to the console
                } else {
                    checkSession();
                }
            }
        });
    });

    function checkSession() {
        $.ajax({
            type: 'GET',
            url: 'check_session.php',
            success: function(response) {
                var data = JSON.parse(response);
                if (data.signedIn) {
                    window.location.href = "profile_signedin.html";  // Redirect to profile_signedin.html
                }
            }
        });
    }
});
