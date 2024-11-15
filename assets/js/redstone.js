/// <reference path="../typings/jquery/jquery.d.ts"/>
var $blankModule;

function initPage() {
    var module_template;

    $.ajax({
        type: 'GET',
        url: 'assets/templates/redstone_template.html',
        async: false,
        contentType: 'text/html',
        dataType: 'html',
        success: function(theHtml) {
            module_template = theHtml;
        }
    });

    $blankModule = $(module_template);
    loadControls($blankModule);
}

function loadControls(template) {
    var modules = false;

    const theParams = {
        a: 'load_redstone_controls',
        user_id: token
    };

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: JSON.stringify(theParams),
        contentType: "application/json",
        dataType: 'json',
        async: false,
        success: function(response) {
            let counter = 1;

            response.controls.forEach(control => {
                var newModule = template.clone(true),
                    active = false;
                modules = true;

                // Set module title
                $(newModule).find('#module_title').text(" " + control.name);
                if (control.active == '1') {
                    $(newModule).find('#status_img').attr('src', 'img/online.png');
                    active = true;
                }

                // Set side names
                $(newModule).find('#top_name').text(control.top_name);
                $(newModule).find('#bottom_name').text(control.bottom_name);
                $(newModule).find('#front_name').text(control.front_name);
                $(newModule).find('#back_name').text(control.back_name);
                $(newModule).find('#left_name').text(control.left_name);
                $(newModule).find('#right_name').text(control.right_name);

                // Give toggle switches unique names
                for (let i = 0; i < 6; i++) {
                    $(newModule).find(`#cmn-toggle-${i+1}`).attr('id', `cmn-toggle-${counter + i}`);
                    $(newModule).find(`.toggle_label_${i+1}`).attr('for', `cmn-toggle-${counter + i}`);
                }

                // Switch toggles on that need to be
                if (control.top == '1') $(newModule).find(`#cmn-toggle-${counter}`).prop('checked', true);
                if (control.bottom == '1') $(newModule).find(`#cmn-toggle-${counter+1}`).prop('checked', true);
                if (control.front == '1') $(newModule).find(`#cmn-toggle-${counter+2}`).prop('checked', true);
                if (control.back == '1') $(newModule).find(`#cmn-toggle-${counter+3}`).prop('checked', true);
                if (control.left == '1') $(newModule).find(`#cmn-toggle-${counter+4}`).prop('checked', true);
                if (control.right == '1') $(newModule).find(`#cmn-toggle-${counter+5}`).prop('checked', true);

                // Handle toggle switch changes
                const redstone_token = control.token;
                for (let i = 0; i < 6; i++) {
                    $(newModule).find(`#cmn-toggle-${counter + i}`).click(function() {
                        const side = ['top', 'bottom', 'front', 'back', 'left_side', 'right_side'][i];
                        const value = $(this).prop('checked') ? 1 : 0;
                        updateOutput(redstone_token, side, value, 'int');
                    });
                }

                // Edit name buttons
                const sides = ['top', 'bottom', 'front', 'back', 'left', 'right'];
                sides.forEach(side => {
                    $(newModule).find(`#name_mouseover_${side}`).hide();
                    $(newModule).find(`#edit_${side}`).click(function(e) {
                        $(newModule).find(`#name_mouseover_${side}`).show(500);
                        e.preventDefault();
                    });
                    $(newModule).find(`#cancel_name_change_${side}`).click(function(e) {
                        $(newModule).find(`#name_mouseover_${side}`).hide(500);
                        e.preventDefault();
                    });
                    $(newModule).find(`#save_name_${side}`).click(function(e) {
                        const newName = $(newModule).find(`#new${capitalize(side)}Name`).val();
                        updateOutput(redstone_token, `${side}_name`, newName, 'string');
                        $(newModule).find(`#${side}_name`).text(newName);
                        $(newModule).find(`#new${capitalize(side)}Name`).val('');
                        $(newModule).find(`#name_mouseover_${side}`).hide(500);
                        e.preventDefault();
                    });
                });

                // Side inputs
                sides.forEach(side => {
                    const inputField = $(newModule).find(`#${side}_input`);
                    if (control[`${side}_input`] == '1') {
                        inputField.css('color', 'red').text('True');
                    } else {
                        inputField.css('color', '#999999').text('False');
                    }
                });

                $(newModule).find('#remove_link').click(function(e) {
                    if (removeModule(redstone_token)) {
                        $(newModule).hide(500);
                    }
                    e.preventDefault();
                });

                $('#connected_modules').append($(newModule));
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
            console.error("Error loading controls:", xhr.responseText);
        }
    });

    if (modules) {
        $('.no_connected_modules').hide();
    } else {
        $('.module_header').hide();
    }
}

function updateOutput(redstone_token, side, value, val_type) {
    const theParams = {
        a: 'setRedstoneOutput',
        token: redstone_token,
        side: side,
        value: value,
        val_type: val_type
    };

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: JSON.stringify(theParams),
        contentType: "application/json",
        dataType: 'json',
        async: true,
        success: function(response) {
            console.log("Output updated successfully.");
        },
        error: function(xhr) {
            console.error("Error updating output:", xhr.responseText);
        }
    });
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
