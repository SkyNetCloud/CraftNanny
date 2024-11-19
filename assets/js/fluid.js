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
    let modulesLoaded = false;

    theParams = {
        a: 'load_fluid_modules',
        user_id: token
    }

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json', // Expect JSON response
        success: function(response) {
            ////console.log("Response received from the server:", response);
    

            if (response.load_fluid_modules && Array.isArray(response.load_fluid_modules) && response.load_fluid_modules.length > 0) {
                modulesLoaded = true; // Set flag to true when modules are loaded
    
                response.load_fluid_modules.forEach(function (module) {
                    var newModule = template.clone(true);
                    var active = module.active === "true"; // Explicitly check for "true"
    
                    // Set module title
                    $(newModule).find('#module_title').text(" " + module.name);
    
                    // Set active status image
                    if (active) {
                        $(newModule).find('#status_img').attr('src', 'img/online.png');
                    } else {
                        $(newModule).find('#status_img').attr('src', 'img/offline.png');
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
    
                    // Attach remove functionality
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
    
            // Hide .no_connected_modules if modules were loaded, show if no modules were loaded
            if (modulesLoaded) {
                console.log("Modules found, hiding 'no connected modules' message.");
                $('.no_connected_modules').hide();
            } else {
                console.log("No modules found, hiding 'module header'.");
                $('.module_header').hide();
            }
        },
        error: function(xhr) {
            console.error("Error loading modules:", xhr.responseText);
            alert("Error loading modules. Please check console for details.");
        }
    });
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
			dataType: 'json',
			async: false,
			success: function(json) {
				//alert((new XMLSerializer()).serializeToString(xml));
				result = true;
                $('.no_connected_modules').show();
                $('.module_header').hide();
			},
			error: function(xhr) {
			 // alert(xhr.responseText);

			}
		});
	}
	return result;
}



$(document).ready(function() {
    initPage();
});
