// User System //

$(document).ready(function() {
    // Bind the Enter key to trigger the createUser function for specific input fields
    $('#username, #email, #password, #password2').keypress(function(e) {
        // //console.log("Key pressed:", e.key); // Debugging log
        if (e.key === 'Enter') {
            //console.log("Enter key was pressed");
            createUser();  // Call createUser when Enter is pressed
            e.preventDefault(); // Prevent form submission
        }
    });

    // Alternatively, bind the "Create" button click to call createUser
    $('#login_btn').click(function() {
        //console.log("Create button was clicked");
        createUser();  // Call createUser when button is clicked
    });
});


function createUser() {
    //console.log("Inside createUser()");

    var username = document.getElementById("username").value;
    var email = document.getElementById("email").value;
    var password = document.getElementById("password").value;
    var password2 = document.getElementById("password2").value;

    // Check email validity
    //console.log("Checking email:", email);
    if (!confirmEmail(email)) {
        //console.log("Email is invalid.");
        alert("Please enter a valid email address.");
        return;  // Stop further execution if email is invalid
    }

    // Check if username is valid
    //console.log("Checking username:", username);
    if (username.length <= 3) {
        //console.log("Username too short.");
        alert('Username must be at least 4 characters long.');
        return;
    }

    // Check if passwords are valid
    //console.log("Checking passwords:", password, password2);
    if (password.length <= 5 || password2.length <= 5) {
        //console.log("Password too short.");
        alert('Password must be at least 6 characters long.');
        return;
    }

    if (password !== password2) {
        //console.log("Passwords do not match.");
        alert('Passwords do not match.');
        return;
    }

    //console.log("Validations passed, checking user existence...");
    
    // Check if the username already exists (asynchronous call)
    checkForUser(username, function(userExists) {
        if (userExists) {
            //console.log("User already exists.");
            alert('User already exists.');
            return;
        }
        //console.log("Creating user...");
        // Proceed to create the new user if validation passes
        addNewUser(username, password, email);
    });
}

function confirmEmail(email) {
    ////console.log("Checking email:", email);
    if (email == '') {
        if (confirm("Without entering an email you will not be able to recover lost passwords. Continue?")) {
            ////console.log("User chose to proceed without email");
            return true;
        } else {
            ////console.log("User chose to cancel email confirmation");
            return false;
        }
    } else {
        ////console.log("Email provided:", email);
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
        url: "api/main.php?v=1.2",
        data: theParams,
        dataType: 'json', // Changed to JSON
        async: true,
        success: function(response) {
            //console.log("Server Response:", response);

            $('#sidebar_create').hide();

            if (response.addNewUser && response.addNewUser.token) {
                //console.log("Received Token:", response.addNewUser.token);
                signIn(response.addNewUser.token);
            } else {
                //console.log("Error: Token not returned", response.addNewUser);
                alert('Error: Token not returned.');
            }
        },
        error: function(xhr) {
            console.error("Error Response:", xhr.responseText);
            alert(xhr.responseText);
        }
    });
}

function signIn(token) {
    // Store the token in cookies for session management
    document.cookie = 'logger_token=' + token + "; path=/";
    window.location.assign("https://craftnanny.org/home.php");
}


function checkForUser(username, callback) {
    //console.log("Checking if user exists:", username);
    
    const theParams = {
        a: 'doesUserExist',
        id: username,
        user_type: 'main'
    };

    $.ajax({
        type: "POST",
        url: "api/main.php",
        data: theParams,
        dataType: 'json', // Changed to JSON
        async: true,
        success: function(response) {
            //console.log("User existence check response:", response);

            const userExistResponse = JSON.parse(response.doesUserExist);
            
            if (userExistResponse.records == 0) {
                //console.log("User does not exist.");
                callback(false);  // User does not exist
            } else {
                //console.log("User exists.");
                callback(true);  // User exists
            }
        },
        error: function(xhr) {
            console.error("Error Response:", xhr.responseText);
            alert(xhr.responseText);
            callback(false);  // If there is an error, assume user doesn't exist
        }
    });
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


