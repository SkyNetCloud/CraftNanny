/// <reference path="../typings/jquery/jquery.d.ts"/>

var $blankModule;

function initPage() {
    var module_template;

    // Load the template asynchronously
    $.ajax({
        type: 'GET',
        url: 'assets/templates/redstone_template.html',
        async: false,
        contentType: 'text/html',
        dataType: 'html',
        success: function(theHtml) {
            module_template = theHtml;
            //console.log("Template loaded:", module_template); // Debug log to check template loading
        },
        error: function(xhr, status, error) {
            console.error("Error loading template:", xhr.responseText);
            console.log("Status: ", status);
            console.log("Error: ", error);
        }
    });

    // If template is successfully loaded, load the controls
    if (module_template) {
        $blankModule = $(module_template);
        loadControls($blankModule);
    } else {
        console.error("Template loading failed!");
    }
}

function loadControls(template) {
    var modules = false;

    var theParams = {
        a: 'load_redstone_controls',
        user_id: token
    }

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json',
        async: false,
        success: function(response) {
            var loadRedstoneControls = JSON.parse(response.load_redstone_controls);
            var recordData = loadRedstoneControls.recorddata;

            console.log(recordData);

            var counter = 1;
            // Loop through each recordData in the response
            $(recordData).each(function() {
                var newModule = template.clone(true),
                    active = false;
                modules = true;

                // Set module title
                $(newModule).find('#module_title').text(" " + $(this).attr('name'));
                if ($(this).attr('active') == '1') {
                    $(newModule).find('#status_img').attr('src', 'img/online.png');
                    active = true;
                }

                // Set side names
                $(newModule).find('#top_name').text($(this).attr('top_name'));
                $(newModule).find('#bottom_name').text($(this).attr('bottom_name'));
                $(newModule).find('#front_name').text($(this).attr('front_name'));
                $(newModule).find('#back_name').text($(this).attr('back_name'));
                $(newModule).find('#left_name').text($(this).attr('left_name'));
                $(newModule).find('#right_name').text($(this).attr('right_name'));

                // Give toggle switches unique names
                $(newModule).find('#cmn-toggle-1').attr('id', 'cmn-toggle-' + counter);
                $(newModule).find('.toggle_label_1').attr('for', 'cmn-toggle-' + counter);
                $(newModule).find('#cmn-toggle-2').attr('id', 'cmn-toggle-' + (counter + 1));
                $(newModule).find('.toggle_label_2').attr('for', 'cmn-toggle-' + (counter + 1));
                $(newModule).find('#cmn-toggle-3').attr('id', 'cmn-toggle-' + (counter + 2));
                $(newModule).find('.toggle_label_3').attr('for', 'cmn-toggle-' + (counter + 2));
                $(newModule).find('#cmn-toggle-4').attr('id', 'cmn-toggle-' + (counter + 3));
                $(newModule).find('.toggle_label_4').attr('for', 'cmn-toggle-' + (counter + 3));
                $(newModule).find('#cmn-toggle-5').attr('id', 'cmn-toggle-' + (counter + 4));
                $(newModule).find('.toggle_label_5').attr('for', 'cmn-toggle-' + (counter + 4));
                $(newModule).find('#cmn-toggle-6').attr('id', 'cmn-toggle-' + (counter + 5));
                $(newModule).find('.toggle_label_6').attr('for', 'cmn-toggle-' + (counter + 5));

                // Set toggle state
                var moduleData = $(this); // Each individual record in recordData
                if (moduleData.attr('top') == '1') {
                    $(newModule).find('#cmn-toggle-' + counter).prop('checked', true);
                }
                if (moduleData.attr('bottom') == '1') {
                    $(newModule).find('#cmn-toggle-' + (counter + 1)).prop('checked', true);
                }
                if (moduleData.attr('front') == '1') {
                    $(newModule).find('#cmn-toggle-' + (counter + 2)).prop('checked', true);
                }
                if (moduleData.attr('back') == '1') {
                    $(newModule).find('#cmn-toggle-' + (counter + 3)).prop('checked', true);
                }
                if (moduleData.attr('left') == '1') {
                    $(newModule).find('#cmn-toggle-' + (counter + 4)).prop('checked', true);
                }
                if (moduleData.attr('right') == '1') {
                    $(newModule).find('#cmn-toggle-' + (counter + 5)).prop('checked', true);
                }
    
                // Handle toggle switch changes
                var redstone_token = $(this).attr('token');
                var sides = ['top', 'bottom', 'front', 'back', 'left_side', 'right_side'];

                sides.forEach(function(side, index) {
                    var toggleId = '#cmn-toggle-' + (counter + index);
                    
                    $(newModule).find(toggleId).change(function() {
                        updateOutput(redstone_token, side, $(this).prop('checked') ? 1 : 0, 'int');
                    });
                });
    
                // Set side input values
                setSideInput(newModule, 'top_input', $(this).attr('top_input'));
                setSideInput(newModule, 'bottom_input', $(this).attr('bottom_input'));
                setSideInput(newModule, 'front_input', $(this).attr('front_input'));
                setSideInput(newModule, 'back_input', $(this).attr('back_input'));
                setSideInput(newModule, 'left_input', $(this).attr('left_input'));
                setSideInput(newModule, 'right_input', $(this).attr('right_input'));
    
                // Name editing
                setNameEditing(newModule, 'top', redstone_token);
                setNameEditing(newModule, 'bottom', redstone_token);
                setNameEditing(newModule, 'front', redstone_token);
                setNameEditing(newModule, 'back', redstone_token);
                setNameEditing(newModule, 'left', redstone_token);
                setNameEditing(newModule, 'right', redstone_token);
    
                // Remove module functionality
                $(newModule).find('#remove_link').click(function(e) {
                    if (removeModule(redstone_token)) {
                        $(newModule).hide(500);
                    }
                    e.preventDefault();
                });
    
                $('#connected_modules').append(newModule);
                counter += 6;
    
                if (!active) {
                    $(newModule).find('div.redstone_block').block({
                        message: '<strong>module not loaded</strong>',
                        css: { border: '3px solid #a00' }
                    });
                }
            });
        },
        error: function(xhr) {
            alert(xhr.responseText);
        }
    });
    

    if (modules) {
        $('.no_connected_modules').hide();
    } else {
        $('.module_header').hide();
    }
}

function setSideInput(newModule, side, value) {
    if (value == '1') {
        $(newModule).find('#' + side).css('color', 'red');
        $(newModule).find('#' + side).text('True');
    } else {
        $(newModule).find('#' + side).css('color', '#999999');
        $(newModule).find('#' + side).text('False');
    }
}

function setNameEditing(newModule, side, redstone_token) {
    $(newModule).find('#name_mouseover_' + side).hide();
    $(newModule).find('#edit_' + side).click(function(e) {
        $(newModule).find('#name_mouseover_' + side).show(500);
        e.preventDefault();
    });
    $(newModule).find('#cancel_name_change_' + side).click(function(e) {
        $(newModule).find('#name_mouseover_' + side).hide(500);
        e.preventDefault();
    });
    $(newModule).find('#save_name_' + side).click(function(e) {
        updateOutput(redstone_token, side + '_name', $(newModule).find('#new' + capitalizeFirstLetter(side) + 'Name').val(), 'string');
        $(newModule).find('#' + side + '_name').text($(newModule).find('#new' + capitalizeFirstLetter(side) + 'Name').val());
        $(newModule).find('#new' + capitalizeFirstLetter(side) + 'Name').val('');
        $(newModule).find('#name_mouseover_' + side).hide(500);
        e.preventDefault();
    });
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function updateOutput(redstone_token, side, value, val_type) {
    var params = {
        a: 'setRedstoneOutput',
		token: redstone_token,
		side: side,
		value: value,
		val_type: val_type
    };
    $.ajax({
        type: 'POST',
        url: 'code/main.php',
        data: params,
        success: function(xhr) {
            
        },
        error: function(xhr) {
    
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
                // $('.no_connected_modules').show();
                // $('.module_header').hide();
			},
			error: function(xhr) {
			 // alert(xhr.responseText);

			}
		});
	}
	return result;
}




$(document).ready(function () {
    initPage();
});
