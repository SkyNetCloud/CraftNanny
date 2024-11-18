// User System //
function createUser() {
    var usernameInput = $('#username').val(),
        passwordInput = $('#password').val(),
        passwordInput2 = $('#password2').val(),
        email = $('#email').val();

    //console.log("Creating user with username:", usernameInput, "and email:", email);
    
    if (confirmEmail(email)) {
        //console.log("Email confirmed");

        if (usernameInput.length > 3) {
            //console.log("Username length is valid");
            if (passwordInput.length > 2 && passwordInput2.length > 2) {
                //console.log("Password length is valid");

                if (passwordInput == passwordInput2) {
                    //console.log("Passwords match");
                    if (checkForUser(usernameInput)) {
                        alert('User already exists');
                        //console.log("User already exists");
                    } else {
                        //console.log("User does not exist, creating new user");
                        addNewUser(usernameInput, passwordInput, email);
                    }
                } else {
                    alert('Passwords did not match!');
                    //console.log("Passwords did not match");
                }
            } else {
                alert('Password is too short.');
                //console.log("Password is too short");
            }
        } else {
            alert('Username is too short');
            //console.log("Username is too short");
        }
    } else {
        //console.log("Email confirmation failed");
    }
}

function confirmEmail(email) {
    //console.log("Checking email:", email);
    if (email == '') {
        if (confirm("Without entering an email you will not be able to recover lost passwords. Continue?")) {
            //console.log("User chose to proceed without email");
            return true;
        } else {
            //console.log("User chose to cancel email confirmation");
            return false;
        }
    } else {
        //console.log("Email provided:", email);
        return true;
    }
}

function addNewUser(name, pwd, email) {
    //console.log("Sending request to add new user:", name);

    const theParams = {
        a: 'addNewUser',
        username: name,
        password: pwd,
        email: email
    };

    $.ajax({
        type: "POST",
        url: "code/main.php?v=1.2",
        data: theParams,
        dataType: 'json', // Changed to JSON
        async: true,
        success: function(response) {
            ////console.log("Server Response: ", response);
            
            $('#sidebar_create').hide();
            
            // Log the token or response properties
            if (response.addNewUser && response.addNewUser.token) {
                //console.log("Received Token: ", response.addNewUser.token);
                signIn(response.addNewUser.token);
            } else {
                //console.log("Error: Token not returned", response.addNewUser);
                alert('Error: Token not returned.');
            }
        },
        error: function(xhr) {
            console.error("Error Response: ", xhr.responseText);
            // alert(xhr.responseText);
        }
    });
}

function loginUser() {
    var username_input = $('#username_login').val(),
        password_input = $('#password_login').val();

    //console.log("Attempting to login with username:", username_input);
    
    $('#username_login').val('');
    $('#password_login').val('');
    
    const theParams = {
        a: 'signIn',
        username: username_input,
        password: password_input
    };

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json', // Changed to JSON
        async: true,
        success: function(response) {
            //console.log("Server Response: ", response);

            var token = response.token;
            if (token) {
                //console.log("Login successful, received token:", token);
                signIn(token);
            } else {
                alert('Username and password combination not found.');
                //console.log("Login failed, invalid username/password");
            }
        },
        error: function(xhr) {
            console.error("Error Response: ", xhr.responseText);
            alert(xhr.responseText);
        }
    });
}

function signIn(token) {
    //console.log("Signing in with token:", token);
    document.cookie = 'logger_token' + "=" + token + "; path=/";
    window.location.assign("https://craftnanny.org/home.php");
}

function signOut() {
    //console.log("Signing out...");
    signedIn = false;
    $('#logout_btn').hide();
    $('#submitBtn').hide();
    $('#viewSubmit').hide();
    $('#user_data').hide();
    $('#login_btn').show();
    $('#signup_btn').show();
    $('#instructions').show();
    username = '';
    user_id = 0;

    del_cookie('logger_token');
}

function checkForUser(username) {
    //console.log("Checking if user exists:", username);
    
    var result = false;

    const theParams = {
        a: 'doesUserExist',
        id: username,
        user_type: 'main'
    };

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json', // Changed to JSON
        async: true,
        success: function(response) {
            //console.log("User existence check response:", response);
            
            if (response.records == 0) {
                //console.log("User does not exist");
                result = false;
            } else {
                //console.log("User exists");
                result = true;
            }
        },
        error: function(xhr) {
            console.error("Error Response: ", xhr.responseText);
            alert(xhr.responseText);
        }
    });

    return result;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function del_cookie(name) {
    //console.log("Deleting cookie:", name);
    document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
}

$(document).ready(function() {
    // You can add additional functions or checks here if necessary
    //console.log("Document is ready");
});