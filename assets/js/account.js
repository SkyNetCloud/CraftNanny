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
    window.location.assign("https://craftnanny.org/index.php");
}

function del_cookie(name) {
    //console.log("Deleting cookie:", name);
    document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
}
