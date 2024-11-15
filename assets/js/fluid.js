/// <reference path="../typings/jquery/jquery.d.ts"/>
var $blankModule;

function initPage() {
    var module_template;
    
    $.ajax({
        type: 'GET', 
        url: 'assets/templates/fluid_template.html', 
        async: false, 
        contentType   :  'text/html',
        dataType      :  'html',
        success: function(theHtml) {
            module_template = theHtml;
        }
    });

    $blankModule = $(module_template);
    loadModules($blankModule);
}

function loadModules(template) {
    var modules = false;

    theParams = {
        a: 'load_fluid_modules',
        user_id: token
    }

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json', // Change to expect JSON response
        async: false,
        success: function(response) {
            // Check if response.modules exists and is an array
            if (response.load_energy_modules && response.load_energy_modules.length > 0) {
                modules = true;

                response.load_energy_modules.forEach(function(module) {
                    var newModule = template.clone(true);
                    var active = false;

                    // Set module title
                    $(newModule).find('#module_title').text(" " + module.name);
                    if (load_energy_modules.active) {
                        $(newModule).find('#status_img').attr('src', 'img/online.png');
                        active = true;
                    }

                    // Set fluid level and percent
                    $(newModule).find('#level_meter').attr('value', module.percent);
                    $(newModule).find('#percent').text(" " + module.percent + "%");

                    // Set fluid type
                    $(newModule).find('#fluid_type').text(" " + module.fluid_type);

                    // Set bucket image based on fluid type
                    var bucketImgSrc = '';
                    switch (module.fluid_type) {
                        case 'Creosote Oil':
                            bucketImgSrc = 'img/buckets/creosote.png';
                            break;
                        case 'Water':
                            bucketImgSrc = 'img/buckets/water.png';
                            break;
                        case 'Lava':
                            bucketImgSrc = 'img/buckets/lava.png';
                            break;
                        case 'Destabilized Redstone':
                            bucketImgSrc = 'img/buckets/redstone.png';
                            break;
                    }
                    $(newModule).find('#bucket_img').attr('src', bucketImgSrc);

                    var node = module;
                    $(newModule).find('#remove_link').click(function(e) {
                        if (removeModule(node.token)) {
                            $(newModule).hide(500);
                        }
                        e.preventDefault();
                    });

                    $('#connected_modules').append($(newModule));

                    // If module is not active, apply block overlay
                    if (!active) {
                        $(newModule).find('div.fluid_module_block').block({
                            message: '<strong>module not loaded</strong>',
                            css: { border: '3px solid #a00' }
                        });
                    }
                });
            }
        },
        error: function(xhr) {
            alert("Error loading modules: " + xhr.responseText);
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
        }

        $.ajax({
            type: "POST",
            url: "code/main.php",
            data: theParams,
            dataType: 'json', // Expect JSON response for removal
            async: false,
            success: function(response) {
                if (response.success) {
                    result = true;
                } else {
                    alert("Failed to remove module.");
                }
            },
            error: function(xhr) {
                alert("Error removing module: " + xhr.responseText);
            }
        });
    }
    return result;
}

$(document).ready(function() {
    initPage();
});
