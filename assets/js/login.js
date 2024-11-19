function loginUser() {
    var username_input = $('#username_login').val().trim(),
        password_input = $('#password_login').val().trim();

    // Check if inputs are not empty
    if (!username_input || !password_input) {
        alert("Please enter both username and password.");
        return;
    }

    // Prepare data to send in POST request
    var theParams = {
        a: 'signIn',
        username: username_input,
        password: password_input
    };

    // Log the parameters to ensure correct data is being sent
    // console.log("Sending the following data to the server:", theParams);

    // Send the request
    $.ajax({
        type: "POST",
        url: "code/main.php",  // Make sure this is the correct URL for your backend
        data: theParams,
        dataType: 'json',
        async: true,  // Default is true, no need to specify 'false'
        success: function(response) {
            // Log the response from the server
            console.log("Response from server:", response); 

            if (response.signIn.status === "success" && response.signIn.logger_token) {
                signIn(response.signIn.logger_token)
            } else {
                alert('Username and password combination not found.');
            }
        },
        error: function(xhr, status, error) { 
            alert("Error: " + xhr.responseText); 
        }
    });

    // Clear inputs after sending the request
    $('#username_login').val('');
    $('#password_login').val('');
}

function signIn(token) {
    // Store the token in cookies for session management
    document.cookie = 'logger_token=' + token + "; path=/";
    window.location.assign("https://dev.craftnanny.org/home.php");
}

$(document).ready(function() {
    // Trigger login on button click
    $('#login_btn').click(function(e) {
        loginUser();
        e.preventDefault();  // Prevent default form submission behavior
    });

    // Trigger login when "Enter" is pressed on the input fields
    $('#username_login, #password_login').keypress(function(e) {
        if (e.key === 'Enter') {
            loginUser();
            e.preventDefault();  // Prevent form submission when Enter is pressed
        }
    });
});
