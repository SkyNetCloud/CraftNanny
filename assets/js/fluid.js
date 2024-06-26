var $blankModule;

function initPage() {
	var module_template;
	
	$.ajax({
		type: 'GET', 
		url: 'fluid_template.html', 
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
		dataType: 'xml', 
		async: false,
		success: function(xml) {	
			
			//

			$(xml).find('modules').each(function() {
				var newModule = template.clone(true),
					active = false;
				modules = true;

				// Set module title
				$(newModule).find('#module_title').text(" " + $(this).attr('name'));
				if ($(this).attr('active') == '1') {
					$(newModule).find('#status_img').attr('src', 'assets/img/online.png');
					active = true;
				}
				$(newModule).find('#level_meter').attr('value', $(this).attr('percent'));
				
				$(newModule).find('#fluid_type').text(" " + $(this).attr('fluid_type'));
				$(newModule).find('#percent').text(" " + $(this).attr('percent') + "%");
				
				if ($(this).attr('fluid_type') == 'Creosote Oil') {
					$(newModule).find('#bucket_img').attr('src', 'assets/img/buckets/creosote.png');
				}
				if ($(this).attr('fluid_type') == 'Water') {
					$(newModule).find('#bucket_img').attr('src', 'assets/img/buckets/water.png');
				}
				if ($(this).attr('fluid_type') == 'Lava') {
					$(newModule).find('#bucket_img').attr('src', 'assets/img/buckets/lava.png');
				}
				if ($(this).attr('fluid_type') == 'Destabilized Redstone') {
					$(newModule).find('#bucket_img').attr('src', 'assets/img/buckets/redstone.png');
				}
				
				var node = $(this);
				$(newModule).find('#remove_link').click(function(e) {
					if (removeModule($(node).attr('token'))) {
						$(newModule).hide(500);
					}
					e.preventDefault();
				});
				
				$('#connected_modules').append($(newModule));
				
				if (!active) {
					$(newModule).find('div.fluid_module_block').block({
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
			dataType: 'xml', 
			async: false,
			success: function(xml) {	
				//
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