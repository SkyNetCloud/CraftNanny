/// <reference path="../typings/jquery/jquery.d.ts"/>
var $blankModule;

function initPage() {
    var module_template;

    $.ajax({
        type: 'GET',
        url: 'energy_template.html',
        async: false,
        contentType: 'text/html',
        dataType: 'html',
        success: function (theHtml) {
            module_template = theHtml;
        }
    });

    $blankModule = $(module_template);
    loadModules($blankModule);
}

function loadModules(template) {
    var modules = false;

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
            // Check if the response contains modules
            if (response.modules && response.modules.length > 0) {
                modules = true;

                response.modules.forEach(function (module) {
                    var newModule = template.clone(true),
                        active = false;

                    // Set module title
                    $(newModule).find('#module_title').text(" " + module.name);

                    if (module.active == '1') {
                        $(newModule).find('#status_img').attr('src', 'img/online.png');
                        active = true;
                    }
                    $(newModule).find('#level_meter').attr('value', module.percent);

                    if (module.energy_type == 'RF') {
                        $(newModule).find('#energy_type').text("Redstone Flux (RF)");
                    } else if (module.energy_type == 'EU') {
                        $(newModule).find('#energy_type').text("Energy Unit (EU)");
                    } else {
                        $(newModule).find('#energy_type').text("Unknown energy type");
                    }

                    $(newModule).find('#percent').text(" " + module.percent + "%");

                    var node = module;
                    $(newModule).find('#remove_link').click(function (e) {
                        if (removeModule(node.token)) {
                            $(newModule).hide(500);
                        }
                        e.preventDefault();
                    });

                    $('#connected_modules').append($(newModule));

                    if (!active) {
                        $(newModule).find('div.energy_module_block').block({
                            message: '<strong>module not loaded</strong>',
                            css: { border: '3px solid #a00' }
                        });
                    }

                });
            }
        },
        error: function (xhr) {
            // Handle error
        }
    });

    if (modules) {
        $('.no_connected_modules').hide();
    } else {
        $('.module_header').hide();
    }
}

function removeModule(token) {
    var result = false;
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
                if (response.success) {
                    result = true;
                }
            },
            error: function (xhr) {
                // Handle error
            }
        });
    }
    return result;
}

$(document).ready(function () {
    initPage();
});
