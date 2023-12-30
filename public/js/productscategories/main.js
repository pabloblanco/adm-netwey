function save () {
	sav ('#category_form', function (res) {
		alert(res);
		getview('categories');
	},
	function (res) {
	});
}

function update (object) {
	setModal(JSON.parse(object));
	$('#open_modal_btn').click();
}

function deleteData (id, name) {
	if (confirm('¿desea eliminar la Categoría: '+name+'?')){
		request ('api/categories/'.concat(id), 'DELETE', null,
			function (res) {
				if (res){
					alert('fue eliminado satisfactoriamente la Categoría: '+name);
					getview('categories');
				}else{
					alert('error al eliminar la Categoría: '+name);
				}
			},
			function (res) {
				console.log('error: '.concat(res));
			});
	}
}

function setModal(object) {
	if (object != null) {
		$('h4.modal-title').text('Editar datos: '.concat(object.id));
		$('#title').val(object.title);
		$('#description').val(object.description);
		$('#status').val(object.status);
		$('#category_form').attr('action', 'api/categories/'.concat(object.id));
		$('#category_form').attr('method', 'PUT');
	} else {
		$('h4.modal-title').text('Crear Categoría de producto');
		$('#title').val('');
		$('#description').val('');
		$('#status').val('A');
		$('#category_form').attr('action', 'api/categories/store');
		$('#category_form').attr('method', 'POST');
	}
}

$('#myModal').on('hide.bs.modal', function () { 
    setModal(null);
});

$(document).ready(function () {
	$(".preloader").fadeOut();
});