/// <reference path="../typings/jquery/jquery.d.ts"/>

var $blankReactor;

function initPage() {
    var module_template;

    // Load the reactor template HTML
    $.ajax({
        type: 'GET',
        url: 'assets/templates/reactor_template.html',
        async: false,
        contentType: 'text/html',
        dataType: 'html',
        success: function (theHtml) {
            // Successfully loaded the reactor template HTML
            module_template = theHtml;
        },
        error: function (xhr) {
            console.error("Error loading reactor template: ", xhr);
        }
    });

    // Clone the reactor template and start loading modules
    if (module_template) {
        $blankModule = $(module_template);
        loadReactors($blankModule);
    } else {
        console.error("Template loading failed!");
    }
}

function loadReactors(template) {
    let reactorsFound = false;

    const theParams = {
        a: 'load_reactor_status',
        user_id: token
    };

    $.ajax({
        type: "POST",
        url: "api/main.php",
        data: theParams,
        dataType: 'json',
        async: false,
        success: function (response) {
            console.log("Server Response: ", response);
            if (response.load_reactor_status && response.load_reactor_status.length > 0) {
                reactorsFound = true;

                response.load_reactor_status.forEach(function (reactor) {
                    const newReactor = template.clone(true);

                    $(newReactor).find('#module_title').text(" " + reactor.name);

                    const statusImage = reactor.active ? 'img/online.png' : 'img/offline.png';
                    $(newReactor).find('#status_img').attr('src', statusImage);

                    const reactorStatusSpan = $(newReactor).find('#reactor_status');
                    if (reactor.active === true || reactor.active === "true") {
                        reactorStatusSpan.text("Online").css("color", "#00ff00");
                    } else {
                        reactorStatusSpan.text("Offline").css("color", "#ff0000");
                    }

                    $(newReactor).find('#reactor_temperature').text("Temperature: " + reactor.temperature + "°C");
                    $(newReactor).find('#burn_rate').text("Burn Rate: " + reactor.burn_rate);

                    const fuelPercentage = reactor.fuel_percentage
                        ? (parseFloat(reactor.fuel_percentage) * 100).toFixed(2) + "%"
                        : '--%';
                    const coolantPercentage = reactor.coolant_percentage
                        ? (parseFloat(reactor.coolant_percentage) * 100).toFixed(2) + "%"
                        : '--%';

                    $(newReactor).find('#fuel_percentage').text("Fuel Level: " + fuelPercentage);
                    $(newReactor).find('#coolant_percentage').text("Coolant Level: " + coolantPercentage);

                    const wasteLevel = reactor.waste_percentage
                        ? (parseFloat(reactor.waste_percentage) * 100).toFixed(2) + "%"
                        : '--%';
                    $(newReactor).find('#waste_percentage').text("Waste Level: " + wasteLevel);

                    const wasteDisposal = reactor.waste
                        ? `${reactor.waste}`
                        : '--';
                    $(newReactor).find('#waste_disp_percent').text("Waste Disposal: " + wasteDisposal);

                    // Add burn rate adjustment
                    const burnRateInput = $(newReactor).find('#burn_rate_input'); // Ensure this is in your template
                    burnRateInput.val(reactor.burn_rate); // Pre-fill with current burn rate


                    $(newReactor).find('#remove_link').click(function(e) {
                        if (removeModule(reactor.token)) {
                            $(newModule).hide(500);
                        }
                        e.preventDefault();
                    });

                    setBurnRateEditing(newReactor, reactor.token);


                    $('#connected_modules').append($(newReactor));

                    // if (!(reactor.active === true || reactor.active === "true")) {
                    //     $(newReactor).find('div.reactor_module_block').block({
                    //         message: '<strong>Module not loaded</strong>',
                    //         css: { border: '3px solid #a00' }
                    //     });
                    // }
                });
            }
        },
        error: function (xhr) {
            console.error("Error loading reactors: ", xhr);
        }
    });

    if (reactorsFound) {
        $('.no_connected_modules').hide();
    } else {
        $('.module_header').hide();
    }
}


function setBurnRateEditing(newModule, reactor_token) {
    // Initially hide the burn rate editing field
    $(newModule).find('#burn_rate_mouseover').hide();

    // Show the editing interface when the 'change rate' button is clicked
    $(newModule).find('#edit_burn_rate').click(function (e) {
        $(newModule).find('#burn_rate_mouseover').show(500);
        e.preventDefault();
    });

    // Hide the editing interface when 'Cancel' is clicked
    $(newModule).find('#cancel_burn_rate_change').click(function (e) {
        $(newModule).find('#burn_rate_mouseover').hide(500);
        e.preventDefault();
    });

    // Save the updated burn rate when 'Save' is clicked
    $(newModule).find('#save_burn_rate').click(function (e) {
        const newBurnRate = $(newModule).find('#newBurnRate').val();
    
        // Validate and parse input
        const parsedBurnRate = parseFloat(newBurnRate);
        if (isNaN(parsedBurnRate) || parsedBurnRate < 0) {
            alert('Please enter a valid positive number for the burn rate.');
            return;
        }
    
        console.log('Parsed burn rate:', parsedBurnRate); // Debug log


        $(newModule).find('#burn_rate').text("Burn Rate: " + newBurnRate);
        $(newModule).find('#newBurnRate').val('');
        $(newModule).find('#burn_rate_mouseover').hide(500);
    
        // Send the updated burn rate to the server
        updateBurnRate(reactor_token, parsedBurnRate);
    
        e.preventDefault();
    });
}

function updateBurnRate(reactor_token, newBurnRate) {
    // Prepare data to send to the server
    const data = {
        a: 'update_burn_rate',  // Action type, you can change this as needed
        user_id: reactor_token,
        burn_rate: newBurnRate
    };

    // Send an AJAX request to update the burn rate on the server
    $.ajax({
        type: "GET",
        url: "api/main.php",  // Update with your actual file path
        data: data,
        dataType: 'json',
        success: function(response) {
            console.log(response)
        },
        error: function(xhr, status, error) {
            console.error('AJAX request failed: ' + error);
        }
    });
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
            url: "api/main.php",
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
    initPage();
});
