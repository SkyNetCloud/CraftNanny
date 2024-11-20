/// <reference path="../typings/jquery/jquery.d.ts"/>
var $blankEvent;

function initPage() {
	var event_template;
	
	$.ajax({
		type: 'GET', 
		url: 'assets/templates/event_template.html', 
		async: false, 
		contentType: 'text/html',
		dataType: 'html',
		success: function(theHtml) {
			event_template = theHtml;
		}
	}); 
	
	$blankEvent = $(event_template);
	loadEvents($blankEvent);
	
	populateDropdowns();
	
	$('#redstone_modules').change(function() {
		const theParams = {
			a: 'get_redstone_sides',
			token: $(this).val()
		};
	
		$.ajax({
			type: "POST",
			url: "api/main.php",
			data: theParams,
			dataType: 'json', // Changed to JSON
			async: false,
			success: function(data) {
				$('#module_side').empty();
				$('#module_side')
					.append($("<option></option>")
		         		.attr("value", "top_side")
		         		.text(data.top_name))
					.append($("<option></option>")
		         		.attr("value", "bottom_side")
		         		.text(data.bottom_name))
					.append($("<option></option>")
		         		.attr("value", "front_side")
		         		.text(data.front_name))
					.append($("<option></option>")
		         		.attr("value", "back_side")
		         		.text(data.back_name))
					.append($("<option></option>")
		         		.attr("value", "left_side")
		         		.text(data.left_name))
					.append($("<option></option>")
		         		.attr("value", "right_side")
		         		.text(data.right_name));
			},
			error: function(xhr) {
				alert(xhr.responseText);
			}
		});
	});
	
	$('#login_btn').click(function() {
		if (checkUserInput()) {
			if (isPercent($('#trigger_value').val())) {
				const theParams = {
					a: 'create_redstone_event',
					user_id: token,
					redstone_token: $('#redstone_modules').val(),
					storage_token: $('#storage_modules').val(),
					side: $('#module_side').val(),
					output_value: $('#output_value').val(),
					trigger_value: $('#trigger_value').val(),
					event_type: $('#event_type').val()
				};
			
				$.ajax({
					type: "POST",
					url: "api/main.php",
					data: theParams,
					dataType: 'json', // Changed to JSON
					async: false,
					success: function(data) {
						alert('Event Created.');
					},
					error: function(xhr) {
						alert(xhr.responseText);
					}
				});
			} else {
				alert("Enter an integer between 0 and 100 for the percent value.");
			}
		} else {
			alert("Fill in all the required fields.");
		}
		loadEvents($blankEvent);
	});
}

function loadEvents(template) {
	let counter = 0;
	
	const theParams = {
		a: 'load_redstone_events',
		user_id: token
	};

	$.ajax({
		type: "POST",
		url: "api/main.php",
		data: theParams,
		dataType: 'json', // Changed to JSON
		async: false,
		success: function(data) {
			data.events.forEach(event => {
				const newModule = template.clone(true);
				const output = event.output === '1' ? 'true' : 'false';
				const inequality = event.event_type === '1' ? '>' : '<';

				if (event.redstone_active === '1' && event.storage_active === '1') {
					$(newModule).find('#status_img').attr('src', 'img/online.png');
				}
				
				$(newModule).find('#event_title').text(
					`When ${event.storage_module} ${inequality} ${event.trigger_value}%, ${event.redstone_module} ${event.side} set to ${output}`
				);

				$(newModule).find('#remove_link').click(function(e) {
					if (removeEvent(event)) {
						$(newModule).hide(500);
					}
					e.preventDefault();
				});
				
				$('#active_events').append($(newModule));
				counter++;
			});
		},
		error: function(xhr) {
			alert(xhr.responseText);
		}
	});
	
	if (counter > 0) {
		$('.no_events').hide();
	}
}

function checkUserInput() {
	return $('#storage_modules').val() && 
		   $('#redstone_modules').val() && 
		   $('#module_side').val() && 
		   $('#output_value').val() && 
		   $('#trigger_value').val();
}

function isPercent(str) {
	const n = ~~Number(str);
	return String(n) === str && n >= 0 && n <= 100;
}

function populateDropdowns() {
	const theParams = {
		a: 'redstone_event_dropdowns',
		user_id: token
	};

	$.ajax({
		type: "POST",
		url: "api/main.php",
		data: theParams,
		dataType: 'json', // Changed to JSON
		async: false,
		success: function(data) {
			$('#storage_modules').empty();
			if (data.storage_modules.length > 0) {
				data.storage_modules.forEach(module => {
					$('#storage_modules')
						.append($("<option></option>")
		         			.attr("value", module.token)
		         			.text(module.name));
				});
			} else {
				$('#storage_modules')
					.append($("<option></option>")
					.attr("value", null)
		         	.text('No Storage Modules Connected'));
			}
			
			$('#redstone_modules').empty();
			if (data.redstone_modules.length > 0) {
				data.redstone_modules.forEach(module => {
					$('#redstone_modules')
						.append($("<option></option>")
		         			.attr("value", module.token)
		         			.text(module.name));
				});
			} else {
				$('#redstone_modules')
					.append($("<option></option>")
					.attr("value", null)
		         	.text('No Redstone Modules Connected'));
			}
		},
		error: function(xhr) {
			alert(xhr.responseText);
		}
	});
}

function removeEvent(event) {
	let result = false;
	if (confirm('Are you sure you want to delete this event?')) {
		const theParams = {
			a: 'remove_event',
			event_id: event.event_id
		};
	
		$.ajax({
			type: "POST",
			url: "api/main.php",
			data: theParams,
			dataType: 'json', // Changed to JSON
			async: false,
			success: function(data) {
				result = true;
			},
			error: function(xhr) {
				alert(xhr.responseText);
			}
		});
	} 
	return result;
}

$(document).ready(function() {
	initPage();
});
