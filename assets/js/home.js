/// <reference path="../typings/jquery/jquery.d.ts"/>

var $moduleTemplate,
    $blankEvent;

function initPage() {
    var module_template;
    $.ajax({
        type: 'GET',
        url: 'assets/templates/module_template.html',
        async: false,
        contentType: 'text/html',
        dataType: 'html',
        success: function (theHtml) {
            module_template = theHtml;
        }
    });

    var event_template;
    $.ajax({
        type: 'GET',
        url: 'assets/templates/event_template.html',
        async: false,
        contentType: 'text/html',
        dataType: 'html',
        success: function (theHtml) {
            event_template = theHtml;
        }
    });

    $blankEvent = $(event_template);
    loadEvents($blankEvent);

    $moduleTemplate = $(module_template);

    getUser();
    // getPlayerModules();
    getRedstoneModules($moduleTemplate);
    getFluidModules($moduleTemplate);
    getEnergyModules($moduleTemplate);
    getReactorModules($moduleTemplate);
}

function getUser() {
    theParams = {
        a: 'getUser',
        user_id: token
    }

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json',  // Expect JSON response
        async: false,
        success: function (response) {
           // //console.log("User Response:", response);

                $('#username').text(response.user.username);
                $('#welcome').text("Welcome, " + response.user.username);
        },
        error: function (xhr) {
            // alert("User Response:", xhr);
        }
    });
}

function loadEvents(template) {
    var counter = 0;

    theParams = {
        a: 'load_redstone_events',
        user_id: token
    }

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json',  // Expect JSON response
        async: false,
        success: function (response) {
            ////console.log("Events Response:", response);

            if (response.status === "success" && response.events) {
                $.each(response.data.events, function (index, event) {
                    var newModule = template.clone(true);

                    var output = event.output === '1' ? 'true' : 'false';
                    var inequality = event.event_type === '1' ? '>' : '<';

                    $(newModule).find('#event_title').text("When " + event.storage_module + " " + inequality + " " + event.trigger_value + "%, " + event.redstone_module + " " + event.side + " set to " + output);
                    $(newModule).find('#remove_link').click(function (e) {
                        if (removeEvent(event)) {
                            $(newModule).hide(500);
                        }
                        e.preventDefault();
                    });
                    $('#active_events').append($(newModule));

                    counter++;
                });
            }
        },
        error: function (xhr) {
            alert(xhr.responseText);
        }
    });

    if (counter > 0) {
        $('.no_events').hide();
    }
}



function removeEvent(event) {
    var result = false;
    if (confirm('Are you sure you want to delete this event?')) {
        theParams = {
            a: 'remove_event',
            event_id: event.event_id
        }

        $.ajax({
            type: "POST",
            url: "code/main.php",
            data: theParams,
            dataType: 'json',  // Expect JSON response
            async: false,
            success: function (response) {
                if (response.status === "success") {
                    result = true;
                }
            },
            error: function (xhr) {
                alert(xhr.responseText);
            }
        });
    }
    return result;
}


function getReactorModules(template) {
    theParams = {
        a: 'getConnections',
        user_id: token,
        module_type: '4'
    }

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json',  // Expect JSON response
        async: true,
        success: function (response) {

            // Adjusted to access the nested 'getConnections' structure
            if (response.getConnections && response.getConnections.status === "success") {
                const connections = response.getConnections.connections;
                let counter = 0;

                $.each(connections, function (index, connection) {
                    // //console.log("Connection:", connection);

                    const newModule = template.clone(true);
                    if (connection.active === true) {  // Use boolean comparison if 'active' is a boolean
                        $('#reactor_modules').append("<li><img src='img/online.png' style='width:10px'>" + " " + connection.name + "</li>");
                    } else {
                        $('#reactor_modules').append("<li><img src='img/offline.png' style='width:10px'>" + " " + connection.name + "</li>");
                    }
                    counter++;
                });

                if (counter > 0) {
                    $('#no_reactor_modules').hide();
                }
            } else {
                //console.log("No connections found or invalid response structure.");
            }
        
        },
        error: function (xhr) {
            alert(xhr.responseText);
        }
    });
}

function getRedstoneModules(template) {
    theParams = {
        a: 'getConnections',
        user_id: token,
        module_type: '3'
    }

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json',  // Expect JSON response
        async: true,
        success: function (response) {

            // Adjusted to access the nested 'getConnections' structure
            if (response.getConnections && response.getConnections.status === "success") {
                const connections = response.getConnections.connections;
                let counter = 0;

                $.each(connections, function (index, connection) {
                    // //console.log("Connection:", connection);

                    const newModule = template.clone(true);
                    if (connection.active === true) {  // Use boolean comparison if 'active' is a boolean
                        $('#redstone_modules').append("<li><img src='img/online.png' style='width:10px'>" + " " + connection.name + "</li>");
                    } else {
                        $('#redstone_modules').append("<li><img src='img/offline.png' style='width:10px'>" + " " + connection.name + "</li>");
                    }
                    counter++;
                });

                if (counter > 0) {
                    $('#no_redstone_modules').hide();
                }
            } else {
                //console.log("No connections found or invalid response structure.");
            }
        
        },
        error: function (xhr) {
            alert(xhr.responseText);
        }
    });
}


function getFluidModules(template) {
    theParams = {
        a: 'getConnections',
        user_id: token,
        module_type: '2'
    }

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json',  // Expect JSON response
        async: false,
        success: function (response) {
            ////console.log("Fluid Modules Response:", response);

            var counter = 0;
            if (response.status === "success" && response.data) {
                $.each(response.data.connections, function (index, connection) {
                    var newModule = template.clone(true);

                    if (connection.active === '1') {
                        $('#fluid_modules').append("<li><img src='img/online.png' style='width:10px'>" + " " + connection.name + "</li>");
                    } else {
                        $('#fluid_modules').append("<li><img src='img/offline.png' style='width:10px'>" + " " + connection.name + "</li>");
                    }
                    counter++;
                });
            }
            if (counter != 0) {
                $('#no_fluid_modules').hide();
            }
        },
        error: function (xhr) {
            alert(xhr.responseText);
        }
    });
}

function getEnergyModules(template) {
    const theParams = {
        a: 'getConnections',
        user_id: token,
        module_type: '1'
    };

    $.ajax({
        type: "POST",
        url: "code/main.php",
        data: theParams,
        dataType: 'json',  // Expect JSON response
        async: true,
        success: function (response) {
            // //console.log("Full Response:", response);

            // Adjusted to access the nested 'getConnections' structure
            if (response.getConnections && response.getConnections.status === "success") {
                const connections = response.getConnections.connections;
                let counter = 0;

                $.each(connections, function (index, connection) {
                    // //console.log("Connection:", connection);

                    const newModule = template.clone(true);
                    if (connection.active === true) {  // Use boolean comparison if 'active' is a boolean
                        $('#energy_modules').append("<li><img src='img/online.png' style='width:10px'>" + " " + connection.name + "</li>");
                    } else {
                        $('#energy_modules').append("<li><img src='img/offline.png' style='width:10px'>" + " " + connection.name + "</li>");
                    }
                    counter++;
                });

                if (counter > 0) {
                    $('#no_energy_modules').hide();
                }
            } else {
                //console.log("No connections found or invalid response structure.");
            }
        },
        error: function (xhr) {
            alert("Error: " + xhr.responseText);
        }
    });
}


$(document).ready(function () { 
    initPage();
});
