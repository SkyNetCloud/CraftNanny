function getMenuModules(template){
    if (!template) {
        console.error("No template provided for getLogoutModules.");
        return;
    }
    $(template).load('assets/templates/footer_template.html', function(response, status, xhr) {
        if (status === "success") {
        
        }
    });



    $(document).ready(function () { 
        getMenuModules('#footer-placeholder');
    });
}
