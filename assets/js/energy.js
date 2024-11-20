/// <reference path="../typings/jquery/jquery.d.ts"/>


var $blankModule;

function initPage() {
    var module_template;

    //console.log("Initializing page...");

    $.ajax({
        type: 'GET',
        url: 'assets/templates/energy_template.html',
        async: false,
        contentType: 'text/html',
        dataType: 'html',
        success: function (theHtml) {
            //console.log("Successfully loaded the energy template HTML.");
            module_template = theHtml;
        },
        error: function (xhr) {
            //console.log("Error loading energy template: ", xhr);
        }
    });

    ////console.log("Cloning the template...");
    $blankModule = $(module_template);
    loadModules($blankModule);
}

function loadModules(template) {
    let modulesFound = false;  // Make sure this starts as false.

    ////console.log("Loading energy modules...");

    const theParams = {
        a: 'load_energy_modules',
        user_id: token
    };

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json', // Expect JSON response
        async: false,
        success: function (response) {
            ////console.log("Response received from the server:", response);

            // Assuming `response.load_energy_modules` contains an array of module data
            if (response.load_energy_modules && response.load_energy_modules.length > 0) {
                modulesFound = true;  // Set this to true when modules are found

                response.load_energy_modules.forEach(function (module) {
                    //console.log("Processing module:", module);
                    const newModule = template.clone(true);

                    // Set module title
                    $(newModule).find('#module_title').text(" " + module.name);
                    //console.log("Module title set:", module.name);

                    // Set status image based on active state
                    const statusImage = module.active ? 'img/online.png' : 'img/offline.png';
                    $(newModule).find('#status_img').attr('src', statusImage);

                    // Set level meter and energy type
                    $(newModule).find('#level_meter').attr('value', module.percent);
                    $(newModule).find('#percent').text(" " + module.percent + "%");

                    if (module.energy_type) {
                        const energyLabel = module.energy_type === 'FE' ? "Forge Energy (FE)" :
                                            module.energy_type === 'RF' ? "Redstone Flux (RF)" : 
                                            "Unknown energy type";
                        $(newModule).find('#energy_type').text(energyLabel);
                    }

                    // Remove link functionality
                    const node = module;
                    $(newModule).find('#remove_link').click(function (e) {
                        ////console.log("Remove link clicked for module with token:", node.token);
                        if (removeModule(node.token)) {
                            $(newModule).hide(500);
                        }
                        e.preventDefault();
                    });

                    $('#connected_modules').append($(newModule));

                    if (!module.active) {
                       // //console.log("Module is not active, applying block overlay.");
                        $(newModule).find('div.energy_module_block').block({
                            message: '<strong>Module not loaded</strong>',
                            css: { border: '3px solid #a00' }
                        });
                    }
                });
            } else {
                //console.log("No modules found in response.");
            }
        },
        error: function (xhr) {
            //console.log("Error loading modules: ", xhr);
        }
    });

    // Now, use `modulesFound` to determine if we should hide the 'no connected modules' message
    if (modulesFound) {
        ////console.log("Modules found, hiding 'no connected modules' message.");
        $('.no_connected_modules').hide();
    } else {
        ////console.log("No modules found, hiding 'module header'.");
        $('.module_header').hide();
    }
}

function removeModule(token) {
    var result = false;

    // Confirm the deletion with the user
    if (confirm('Are you sure you want to delete this module?')) {
        var theParams = {
            a: 'remove_module',
            token: token
        };

        // Make the AJAX request
        $.ajax({
            type: "POST",
            url: "code/main.php",
            data: theParams,
            dataType: 'json',
            async: false,
            success: function(json) {
                // Handle successful removal
                result = true;

                // Check if there are no more modules connected
                if ($('.connected_module').length === 0) {
                    // Show 'no connected modules' message
                    $('.no_connected_modules').show();
                    $('.module_header').hide();
                } else {
                    // Hide 'no connected modules' message
                    $('.no_connected_modules').hide();
                    $('.module_header').show();
                }
            },
            error: function(xhr) {
                // Handle errors
                console.error("Error removing module:", xhr.responseText);
            }
        });
    }

    return result;
}



$(document).ready(function () {
    //console.log("Document is ready. Initializing page...");
    initPage();
});
