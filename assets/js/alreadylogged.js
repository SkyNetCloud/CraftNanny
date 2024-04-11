function checkLoggedIn() {
    // Retrieve the value of the login cookie
    var loginCookie = getCookie("logger_token");
    
    // Check if the login cookie exists and has a non-empty value
    if (loginCookie && loginCookie.trim() !== "") {
        return true; // User is logged in
    } else {
        return false; // User is not logged in
    }
}

// Function to retrieve the value of a cookie by its name
function getCookie(cookieName) {
    var name = cookieName + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var cookieArray = decodedCookie.split(';');
    for(var i = 0; i < cookieArray.length; i++) {
        var cookie = cookieArray[i];
        while (cookie.charAt(0) === ' ') {
            cookie = cookie.substring(1);
        }
        if (cookie.indexOf(name) === 0) {
            return cookie.substring(name.length, cookie.length);
        }
    }
    return null;
}

// Check if user is on the signin.php page and already logged in
if (checkLoggedIn()) {
    // Show the login form if user is already logged in on other pages
    window.location.href = "home.php";
}