// User System //
function createUser() {
    var username = document.getElementById("username").value;
    var email = document.getElementById("email").value;
    var password = document.getElementById("password").value;
    var password2 = document.getElementById("password2").value;

    // Check email validity
    if (!confirmEmail(email)) {
        alert("Please enter a valid email address.");
        return;  // Stop further execution if email is invalid
    }

    // Check if username is valid
    if (username.length <= 3) {
        alert('Username must be at least 4 characters long.');
        return;
    }

    // Check if passwords are valid
    if (password.length <= 5 || password2.length <= 5) {
        alert('Password must be at least 6 characters long.');
        return;
    }

    if (password !== password2) {
        alert('Passwords do not match.');
        return;
    }

    // Check if the username already exists (asynchronous call)
    checkForUser(username, function(userExists) {
        if (userExists) {
            alert('User already exists.');
            return;
        }
        // Proceed to create the new user if validation passes
        addNewUser(username, password, email);
    });
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