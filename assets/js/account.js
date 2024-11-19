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


function deleteAccount() {
    // Retrieve the token from cookies to authenticate the request
    var token = getCookie('logger_token');

    if (!token) {
        alert('You must be logged in to delete your account.');
        return;
    }

    // Send the deletion request to the backend
    const theParams = {
        a: 'deleteUser',
        user_id: token
    };

    $.ajax({
        type: "POST",
        url: "code/main.php?v=1.2", // Adjust the URL accordingly
        data: theParams,
        dataType: 'json', // Expect a JSON response
        async: true,
        success: function(response) {
            console.log("Server Feedback: ", response);

            // Check if the deletion was successful
            if (response.deleteUser && response.deleteUser.status === 'success') {
                // Notify the user and log them out or redirect them
                alert('Your account has been deleted successfully.');
                del_cookie('logger_token');
                window.location.assign("https://craftnanny.org/index.php"); // Redirect to logout page
            } else {
                // Show an error message if something went wrong
                alert('Error: ' + (response.deleteUser.message || 'Something went wrong.'));
            }
        },
        error: function(xhr) {
            console.error("Error Response:", xhr.responseText);
            alert('An error occurred while trying to delete your account. Please try again.');
        }
    });
}



function del_cookie(name) {
    //console.log("Deleting cookie:", name);
    document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
}

$(document).ready(function() {
    // Bind the Delete Account button click to trigger the deleteAccount function
    $('#delete_account_btn').click(function() {
        // Confirm with the user if they really want to delete the account
        if (confirm("Are you sure you want to delete your account? This action cannot be undone.")) {
            deleteAccount();  // Call the deleteAccount function
        }
    });
});