
$('.edit_button').on('click', function(e){
	e.preventDefault();
	var href = $(e.target).attr('href');
	$.get(href, function(data){
		if(data.project !== null) {
			$('input[name="id"]').val(data.project.id_project);
			$('input[name="name"]').val(data.project.name);
			$('input[name="route"]').val(data.project.project_route);
			$('input[name="database"]').val(data.project.database_name);
			$('input[name="scripts"]').val(data.project.scripts);
		}
	}, "json");
});


$('.delete_button').on('click', function(e) {
	e.preventDefault();
	var id = $(e.target).attr('data-id');
	var href = $(e.target).attr('href');
	$.post(href, {'id': id}, function(data) {
		if(data == 1) {
			$('tr[data-id=' + id +']').remove();
		}
		else {
			alert(data);
		}
	});
});
