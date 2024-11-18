
function getMenuModules(template){
    if (!template) {
        console.error("No template provided for getLogoutModules.");
        return;
    }

    // Load the sidebar template
    $(template).load('assets/templates/sidemenu.html', function(response, status, xhr) {
        if (status === "success") {
            //console.log("Sidebar menu loaded successfully.");
            
            // Call the function to highlight the active menu item
            highlightActiveMenuItem();
            
            // Load any associated JavaScript for the logout functionality
            $.getScript('assets/js/account.js')
                .done(function () {
                    //console.log("Account.js script loaded successfully.");
                })
                .fail(function (jqxhr, settings, exception) {
                    console.error("Failed to load account.js script:", exception);
                });
        } else {
            console.error("Error loading sidemenu.html:", xhr.status, xhr.statusText);
        }
    });
}


function highlightActiveMenuItem() {
    // Get the current URL path
    var currentPath = window.location.pathname;

    // Remove 'active' class from all menu items
    $('#cssmenu li').removeClass('active');

    // Add 'active' class to the corresponding menu item
    $('#cssmenu li a').each(function() {
        var linkPath = $(this).attr('href');
        if (currentPath.indexOf(linkPath) !== -1) {
            $(this).parent().addClass('active');
        }
    });
}

$(document).ready(function () { 
    getMenuModules('#account-menu-placeholder');
});