/// <reference path="../typings/jquery/jquery.d.ts"/>
var $blankModule;

function initPage() {
    var module_template;

    console.log("Initializing page...");

    $.ajax({
        type: 'GET',
        url: 'energy_template.html',
        async: false,
        contentType: 'text/html',
        dataType: 'html',
        success: function (theHtml) {
            console.log("Successfully loaded the energy template HTML.");
            module_template = theHtml;
        },
        error: function (xhr) {
            console.log("Error loading energy template: ", xhr);
        }
    });

    console.log("Cloning the template...");
    $blankModule = $(module_template);
    loadModules($blankModule);
}

function loadModules(template) {
    var modules = false;

    console.log("Loading energy modules...");

    theParams = {
        a: 'load_energy_modules',
        user_id: token
    };

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json', // Change from 'xml' to 'json'
        async: false,
        success: function (response) {
            console.log("Response received from the server:", response);

            // Check if the response contains modules
            if (response.modules && response.modules.length > 0) {
                modules = true;

                console.log("Modules found. Processing each module...");

                response.modules.forEach(function (module) {
                    console.log("Processing module:", module);
                    var newModule = template.clone(true),
                        active = false;

                    // Set module title
                    $(newModule).find('#module_title').text(" " + module.name);
                    console.log("Module title set:", module.name);

                    if (module.active == '1') {
                        $(newModule).find('#status_img').attr('src', 'img/online.png');
                        active = true;
                    }

                    $(newModule).find('#level_meter').attr('value', module.percent);
                    console.log("Level meter set to:", module.percent);

                    if (module.energy_type == 'RF') {
                        $(newModule).find('#energy_type').text("Redstone Flux (RF)");
                    } else if (module.energy_type == 'EU') {
                        $(newModule).find('#energy_type').text("Energy Unit (EU)");
                    } else {
                        $(newModule).find('#energy_type').text("Unknown energy type");
                    }

                    $(newModule).find('#percent').text(" " + module.percent + "%");
                    console.log("Energy type and percent set for module:", module.energy_type, module.percent);

                    var node = module;
                    $(newModule).find('#remove_link').click(function (e) {
                        console.log("Remove link clicked for module with token:", node.token);
                        if (removeModule(node.token)) {
                            console.log("Module removed, hiding the module...");
                            $(newModule).hide(500);
                        }
                        e.preventDefault();
                    });

                    $('#connected_modules').append($(newModule));

                    if (!active) {
                        console.log("Module is not active, blocking it...");
                        $(newModule).find('div.energy_module_block').block({
                            message: '<strong>module not loaded</strong>',
                            css: { border: '3px solid #a00' }
                        });
                    }

                });
            } else {
                console.log("No modules found in response.");
            }
        },
        error: function (xhr) {
            console.log("Error loading modules: ", xhr);
        }
    });

    if (modules) {
        console.log("Modules found, hiding 'no connected modules' message.");
        $('.no_connected_modules').hide();
    } else {
        console.log("No modules found, hiding 'module header'.");
        $('.module_header').hide();
    }
}

function removeModule(token) {
    var result = false;
    console.log("Attempting to remove module with token:", token);

    if (confirm('Are you sure you want to delete this module?')) {
        theParams = {
            a: 'remove_module',
            token: token
        };

        $.ajax({
            type: "POST",
            url: "code/main.php",
            data: theParams,
            dataType: 'json', // Change from 'xml' to 'json'
            async: false,
            success: function (response) {
                console.log("Response received from remove module:", response);
                if (response.success) {
                    console.log("Module successfully removed.");
                    result = true;
                } else {
                    console.log("Failed to remove module.");
                }
            },
            error: function (xhr) {
                console.log("Error removing module: ", xhr);
            }
        });
    } else {
        console.log("Module removal canceled by user.");
    }
    return result;
}

$(document).ready(function () {
    console.log("Document is ready. Initializing page...");
    initPage();
});
