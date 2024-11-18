function loginUser() {
    var username_input = $('#username_login').val(),
        password_input = $('#password_login').val();

    // Clear inputs after capturing values
    $('#username_login').val('');
    $('#password_login').val('');

    // Prepare data to send in POST request
    var theParams = {
        a: 'signIn',
        username: username_input,
        password: password_input
    };

    // Log the parameters to ensure correct data is being sent
    //console.log("Sending the following data to the server:", theParams);

    // Send the request
    $.ajax({
        type: "POST",
        url: "code/main.php",  // Make sure this is the correct URL for your backend
        data: theParams,
        dataType: 'json',
        async: true,  // Default is true, no need to specify 'false'
        success: function(response) {
            //console.log("Response from server:", response);

            // Parse the response from signIn and extract the token
            var signInResponse = JSON.parse(response.signIn);
            //console.log("Parsed signInResponse:", signInResponse);

            // Check if the response contains a successful token
            if (signInResponse.status === "success" && signInResponse.data.logger_token) {
                signIn(signInResponse.data.logger_token);
            } else {
                alert('Username and password combination not found.');
                //console.log(signInResponse);  // Log the full response for debugging
            }
        },
        error: function(xhr, status, error) {
            alert("Error: " + xhr.responseText);
            //console.log("Error Details:", xhr, status, error);
        }
    });
}

function signIn(token) {
    // Store the token in cookies for session management
    document.cookie = 'logger_token=' + token + "; path=/";
    window.location.assign("https://craftnanny.org/home.php");
}

$(document).ready(function() {
    // Trigger login on button click
    $('#login_btn').click(function(e) {
        loginUser();
        e.preventDefault();  // Prevent default form submission behavior
    });
});
