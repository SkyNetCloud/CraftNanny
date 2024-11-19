

function loginUser() {
    var username_input = $('#username_login').val(),
        password_input = $('#password_login').val();

    $('#username_login').val('');
    $('#password_login').val('');

    var theParams = {
        a: 'signIn',
        username: username_input,
        password: password_input
    };

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json',
        async: true,
        success: function(response) {

            if (signInResponse.signIn === "success" && signInResponse.signIn.logger_token) {
                signIn(signInResponse.signIn.logger_token);
            } else {
                alert('Username and password combination not found.');

            }
        },
        error: function(xhr, status, error) {
            alert("Error: " + xhr.responseText);

        }
    });
}

function signIn(token) {
    document.cookie = 'logger_token=' + token + "; path=/";
    window.location.assign("https://craftnanny.org/home.php");
}

$(document).ready(function() {

    $('#login_btn').click(function(e) {
        loginUser();
        e.preventDefault();
    });
});
