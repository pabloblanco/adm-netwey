jQuery.validator.addMethod("emailValidate", function (value, element) {
	return this.optional(element) || /^([a-zA-Z0-9_ñÑ\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/gi.test(value)
}, 'Email inválido.');

/*jQuery.validator.addMethod("curpValidate", function (value, element) {  Comentado debido a Telmovpay
	return this.optional(element) || /^[a-zA-Z]{4}[0-9]{2}[0-1][0-9][0-3][0-9][a-zA-Z]{6}[a-zA-Z0-9]{2}$/gi.test(value)
}, 'CURP inválido.');*/

//para esta validacion el input debe usar el pluggin intl-tel-input
jQuery.validator.addMethod("phoneValidate", function (value, element) {
	return this.optional(element) || ($(element).intlTelInput("isValidNumber") && /^[-.0-9\s]+$/g.test(value));
}, 'Nro Teléfonico inválido.');

jQuery.validator.addMethod("codDepBVValidate", function (value, element) {
	return this.optional(element) || /^[a-z]{2}\d{4}$/i.test(value);
}, 'Código no válido.');

function formatPhone(elem, natmode = false) {
	$(elem).intlTelInput("setCountry", 'mx'); // se establece mx siempre en caso de que el usuario coloque el cod de otro pais
	if (natmode == true) {
		var num = $(elem).intlTelInput("getNumber", intlTelInputUtils.numberFormat.NATIONAL); //formato nacional (999 999 9999)
	}
	if (natmode == false) {
		var num = $(elem).intlTelInput("getNumber"); //formato internacional (+529999999999)
	}
	num = num.replace('+52', '');
	$(elem).val(num);
}


$.fn.selectizeData = function (name) { //retorna el data-attr de la opcion seleccionada
	obj = ($(this)[0].selectize.options[$(this)[0].selectize.items[0]]);
	for (const property in obj) {
		if (property == name)
			return obj[property];
	}
	return undefined;
}

function addSelectizeDataAttr(elem) {
	var s = elem;
	elem.revertSettings.$children.each(function () { //agregando data atributes a selectize
		$.extend(s.options[this.value], $(this).data());
	});
}

//Valida que el formulario de datos del usuario se haya completado de forma correcta
let isFormval = true;

if($('#perfiltab').length){
	$('#perfiltab').on('show.bs.tab', function(e){
		if($('#datos').is(':visible')){
			isFormval = $('#user_form').valid();
		}
	});
}

if($('#codDeptab').length){
	$('#codDeptab').on('show.bs.tab', function(e){
		if($('#datos').is(':visible')){
			isFormval = $('#user_form').valid();
		}
	});
}

if($('#user-data').length){
	$('#user-data').on('show.bs.tab', function(e){
		isFormval = true;
	});
}

function save() {
	//Se usan las clases de bootstrap para colocar la vista a los Datos
	//Se valida las clases y se ajustan (Vista)
	if(!$("#datos" ).hasClass( "active" )){
		$('#datos').addClass('active');
	}
	if(!$("#datos" ).hasClass( "show" )){
		$('#datos').addClass('show');
	}
	if($("#perfil" ).hasClass( "active" )){
		$('#perfil').removeClass('active');
	}
	if($("#perfil" ).hasClass( "show" )){
		$('#perfil').removeClass('show');
	}
	$('#datos').attr('aria-expanded', true);
	$('#perfil').attr('aria-expanded', false);

	//Se valida las clases y se ajustan (TabBar)
	if(!$("#user-data" ).hasClass( "active" )){
		$('#user-data').addClass('active');
	}
	if($("#perfiltab" ).hasClass( "active" )){
		$('#perfiltab').removeClass('active');
	$('#user-data').attr('aria-expanded', true);
	$('#perfiltab').attr('aria-expanded', false);
	}

	//Plazo de Tiempo para que cargue la vista.
	setTimeout(saveAfter(), 4000);
	
}

function saveAfter(){
	if ($('#user_form').valid() && isFormval) {
		$('#perfil input[type=checkbox]').each(function () {
			if ($(this).is(':checked')) {
				$(this).val('1');
			} else {
				$(this).attr('checked', 'checked');
				$(this).val('0');
			}
		});
		$('#perfil input[type=text]').each(function () {
			if ($(this).val() == null || $(this).val() == undefined || $(this).val() == '') {
				$(this).val('0');
			}
		});
		if($("#datos" ).hasClass( "show" )){
			sav('#user_form', function (res) {
				if (res == 'error 01') {
					alert('El usuario ya se encuentra registrado en el sistema');
				} else {
					alert(res);
					getview('users');
				}
			},
				function (res) {
					alert('Ocurrio un error al realizar su operación');
				}
			);
		}else{
			alert('Ocurrio un error al realizar su operación');
		}
	} else {
		if($('#user_form').validate().errorList.length){
			alert('Debes completar los datos del usuario ('+$('#'+$('#user_form').validate().errorList[0].element.id).siblings('label')[0].textContent+')');	
		}else{
			alert('Debes completar los datos del usuario');	
		}
		$('#user_form').submit(function (e) {
			e.preventDefault();
		})
	}
}

function update(user) {
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		async: true,
		url: 'api/user/getUserPolicy/' + user,
		method: 'GET',
		success: function (res) {
			setModal((res));
			$("#myModal").modal({ backdrop: 'static', keyboard: false });
		},
		error: function (res) {
			alert('Ocurrio un error al conectar con el servidor. Por favor intente mas tarde.');
		}
	});
}

function get_profile(type) {
	$('#perfil input[type=checkbox]').prop('checked', false);
	$('#perfil input[type=checkbox]').val('0');
	if (type !== '') {
		if (type == '1') {
			$('#perfil input[type=checkbox]').prop('checked', true);
			$('#perfil input[type=checkbox]').val('1');
		} else {
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				async: true,
				url: 'api/user/get/profile/' + type,
				method: 'GET',
				success: function (res) {
					$.each(res, function (key, value) {
						$('#' + value.item).show();
						$('#' + value.panel).show();
						if (value.type == 'CH') {
							if (value.policy != 'value_17154') {//Politica de desbloque de equipos
								$('#' + value.policy).prop('checked', true);
								$('#' + value.policy).val('1');
							}
						} else {
							$('#' + value.policy).val( value.value );
							$('#perfiltab').show();
						}
					});
				},
				error: function (res) {
					alert('Ocurrio un error al conectar con el servidor. Por favor intente mas tarde');
				}
			});
		}
	}
}
function NewelementHTML($name, $row, $col, $value, $placeholder) {
	var temp = document.createElement("input");
	temp.placeholder = $placeholder;
	temp.name = $name;
	temp.id = $name;
	temp.rows = $row;
	temp.cols = $col;
	temp.type = 'password',
		temp.autocomplete = 'off',
		temp.value = $value;
	return temp;
}
function deleteData(email) {
	$(".preloader").fadeIn();
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		url: 'api/user/get/is-removable',
		type: 'post',
		data: {
			email: email
		},
		success: function (res) {
			if (res) {
				if (res.is_removable) {

					if (res.parent_email && res.subordinates) {
						var msg = 'Al eliminar al usuario ' + email + ', sus subordinados pasaran a cargo de ' + res.parent_email + ', ¿Confirna que desea eliminar a este usuario?';
					}
					else {
						var msg = 'Se va eliminar el usuario "' + email + '"';
					}
					msg += '\n\n La eliminación es un cambio que no se puede revertir.';

					areaText = NewelementHTML('passwodConfirm', '1', '35', '', "Escribe su contrasena");
					swal({
						title: "Estas seguro de continuar con la eliminación del usuario?",
						text: msg,
						content: {
							element: areaText,
						},
						icon: "warning",
						buttons: true,
						dangerMode: true,
					})
						.then((willDelete) => {
							if (willDelete) {
								/*Verifico que la clave es correcta*/
								var pass = $('#passwodConfirm').val();

								if (!(pass === "")) {
									$.ajax({
										url: 'api/user/chekingPass',
										type: 'POST',
										data: {
											_token: $('meta[name="csrf-token"]').attr('content'),
											myEmail: $('#myUser').val(),
											pass: pass
										},
										dataType: "json",

										success: function (res2) {
											if (res2.success) {
												/*Verifico que no posea inventario activo*/
												$.ajax({
													url: 'api/user/chekingInv',
													type: 'POST',
													data: {
														_token: $('meta[name="csrf-token"]').attr('content'),
														user_email: email
													},
													dataType: "json",

													success: function (res3) {
														if (res3.success) {
															swal({
																title: 'No se puede eliminar el usuario: "' + email + '"',
																text: "Debes primero remover el inventario activo asociado al usuario para continuar con el proceso de eliminacion",
																icon: "warning",
															});
														} else {
															/*Si no posee inventario activo lo puedo eliminar*/
															$(".preloader").fadeIn();
															request('api/user/'.concat(email),
																'DELETE',
																null,
																function (res4) {
																	$(".preloader").fadeOut();
																	swal({
																		title: 'Eliminacion exitosa del usuario: "' + email + '"',
																		icon: "success",
																	});
																	getview('users');
																},
																function (res4) {
																	$(".preloader").fadeOut();
																	console.log('del(error)', res4);
																	swal({
																		title: 'Error al tratar de eliminar el usuario: "' + email + '"',
																		icon: "warning",
																	});
																}
															);
														}
													},
													error: function (res) {
														swal("No se pudo verificar el inventario, intente nuevamente!", {
															icon: "warning",
														});
														$(".preloader").fadeOut();
													}
												});
											} else {
												swal("Contrasena incorrecta!", {
													icon: "warning",
												});
											}
										},
										error: function (res) {
											swal("No se pudo verificar el usuario, intente nuevamente!", {
												icon: "warning",
											});
											$(".preloader").fadeOut();
										}
									});
								} else {
									swal("Debes ingresar tu contrasena!", {
										icon: "warning",
									});
								}
							} else {
								//swal("cancelaste!");
							}
						});
				}
				else {
					swal({
						title: 'No se puede eliminar el usuario: "' + email + '"',
						text: 'El usuario: "' + email + '" posee subordinados que no se pueden asignar automaticamente a otro usuario',
						icon: "warning",
					});
					//alert('El usuario: "'+email+'" no se puede eliminar porque posee subordinados que no se pueden asignar automaticamente a otro usuario')
				}
			}
			$(".preloader").fadeOut();
		},
		error: function (res) {
			$(".preloader").fadeOut();
		}
	});
}

function chpass(email) {
	$('#chpass_form').attr('action', 'api/user/chpass/' + email);
	$('#ch_password').val('');
	$('#ch_re_password').val('');
	$('#chpassword').modal({ backdrop: 'static', keyboard: false });
}

function savechpass() {
	if ($('#chpass_form').valid()) {
		if (confirm('¿Esta seguro de cambiar la contraseña?')) {
			sav('#chpass_form', function (res) {
				$('#chpass_close_btn').click();
				alert(res);
			},
				function (res) {
					alert('Ocurrio un error al realizar su operación');
					console.log('error');
					console.log(res);
				}
			);
		} else {
			$('#chpass_form').submit(function (e) {
				e.preventDefault();
			})
		}
	}
}

function setModalPolicies(policies) {
	for (i = 0; i < policies.length; i++) {
		var role = "".concat(policies[i].roles_id);
		var policy = "".concat(policies[i].policies_id);
		var id = '#'.concat('value_'.concat(role.concat(policy)));
		if (policies[i].type == 'CH') {
			$(id).prop('checked', ((policies[i].value == 0) || (policies[i].value == '0')) ? false : true);
			$(id).val(policies[i].value);
		} else {
			$(id).val(policies[i].value);
		}

	}
}

function setModal(user) {

	$('#distributor_name_content').hide();

	if (user != null) {

		sessionStorage.setItem('setModal', true);

		$('h4.modal-title').text('editar datos: ' + user.email);

		if (user.parent_email){
			sessionStorage.setItem('modalParentEmail', user.parent_email);
		}

		$('#name').val(user.name);
		$('#last_name').val(user.last_name);
		$('#email').val(user.email);
		$('#email').prop('readonly', true);
		$('#password').val('');
		$('#re_password').val('');
		$('#password_row').hide();
		setSelect('commission', user.charger_com);
		$('#dni').val(user.dni);
		setSelect('platform', user.platform);
		$('#phone').val(user.phone);
		$('#phone_job').val(user.phone_job);
		$('#profession').val(user.profession);
		$('#position').val(user.position);
		$('#delivery-content').attr('hidden', true);
		//$('#curp').val(user.code_curp ? user.code_curp : '');  Comentado debido a Telmovpay

		if (user.platform == 'coordinador') {
			$('.tab-cod').attr('hidden', null);

			//Dirección para envío de inventario - prova
			if(user.delivery){
				$('#street').val(user.delivery.street ? user.delivery.street : '');
				$('#colony').val(user.delivery.colony ? user.delivery.colony : '');
				$('#municipality').val(user.delivery.municipality ? user.delivery.municipality : '');
				$('#state').val(user.delivery.state ? user.delivery.state : '');
				$('#pc').val(user.delivery.postal_code ? user.delivery.postal_code : '');
				$('#ext_number').val(user.delivery.ext_number ? user.delivery.ext_number : '');
				$('#int_number').val(user.delivery.int_number ? user.delivery.int_number : '');
				$('#reference').val(user.delivery.reference ? user.delivery.reference : '');
			}
			$('#delivery-content').attr('hidden', null);
		} else {
			$('.tab-cod').attr('hidden', true);
		}

		if(user.platform == 'admin'){
			$('#second_pass_area').show();
		}

		//Seteando códigos de depósitos
		if (user.depositCodes && user.depositCodes.length) {
			user.depositCodes.forEach(function (e) {
				if (e.group == 'BV') {
					$('#codBV').val(e.id_deposit);

					$('#btn-del-bv').data('cod', e.id_deposit);
					$('#btn-del-bv').data('bank', e.id_bank);
					$('#btn-del-bv span').html(e.id_deposit);
					$('#delcod-bv-content').attr('hidden', null);
				}

				if (e.group == 'AZ') {
					$('#bankAccount').val(e.id_bank);

					let selcodba = $('#codBA')[0].selectize;

					selcodba.addOption({
						value: e.id_deposit,
						text: e.id_deposit
					});

					setSelect('codBA', e.id_deposit);

					$('#btn-del-az').data('cod', e.id_deposit);
					$('#btn-del-az').data('bank', e.id_bank);
					$('#btn-del-az span').html(e.id_deposit);
					$('#delcod-az-content').attr('hidden', null);
				}
			});
		}

		if (user.profile) {
			sessionStorage.setItem('modalProfile', user.profile.id);
			if (user_log == 0) {
				$('.poliClass').hide();
				$('#perfiltab').hide();
			}
		}
		var sel = $('#ware_org')[0].selectize;
		sel.clearOptions();

		var selwh = [];

		if (user.whRetail) {
			user.whRetail.forEach(function (c) {
				var optVal = {
					value: c.id,
					text: c.name
				};

				sel.addOption(optVal);
				sel.addItem(optVal.value);

				selwh.push(c.id);
			});
		}

		if (user.whRetailOrg) {
			user.whRetailOrg.forEach(function (c) {
				var optVal = {
					value: c.id,
					text: c.name
				};

				sel.addOption(optVal);
			});
		}

		if (selwh.length > 0) {
			sessionStorage.setItem('modalWareOrg', selwh);
		}

		$('#ware_org_cont').removeAttr('hidden');

		setSelect('organization', user.id_org);
		$('#address').val(user.address);
		setSelect('status', user.status);

		setModalPolicies(user.policies);


		if(user.id_org == 1){
			
			$('#distributor_name_content').show();
			if(user.distributor != null){
				
				$('#distributor_name').val(user.distributor.name);

			}else{
				$('#distributor_name').val('*No Posee*');				
			}
			if(user.profile.id == 16){

				$('#distributor_name_content').hide();
				
				var whileiff = setInterval(() => {
					if($('#distributor_select').attr('loaded') == 'true'){
						if(user.distributor != null){
							$('#distributor_select')[0].selectize.setValue(user.distributor.distributor_id);
							$('#distributor_select').attr('loaded', 'false');

						}
						clearInterval(whileiff);
					}
				}, 500);
			}
		}



		if (user.esquema_comercial_id) {
			if (user.type_esquema_comercial == 'D') {

				var whileif = setInterval(() => {
					if($('#division_select').attr('loaded') == 'true'){
						$('#division_select')[0].selectize.setValue(user.esquema_comercial_id);
						$('#division_select').attr('loaded', 'false');
						clearInterval(whileif);
					}
				}, 500);

			} else if (user.type_esquema_comercial == 'R') {
				var whileif = setInterval(() => {
					if($('#region_select').attr('loaded') == 'true'){
						$('#region_select')[0].selectize.setValue(user.esquema_comercial_id);
						$('#region_select').attr('loaded', 'false');
						clearInterval(whileif);
					}
				}, 500);
			} else {
				var whileif = setInterval(() => {
					if($('#coordinacion_select').attr('loaded') == 'true'){
						$('#coordinacion_select')[0].selectize.setValue(user.esquema_comercial_id);
						$('#coordinacion_select').attr('loaded', 'false');
						clearInterval(whileif);
					}
				}, 500);
			}
		}

		

		$('#user_form').attr('action', 'api/user/' + user.email);
		$('#user_form').attr('method', 'PUT');

	} else {
		$('h4.modal-title').text('Crear Usuario');
		setSelect('parent_email', '');
		$('#name').val('');
		$('#last_name').val('');
		$('#email').val('');
		$('#email').prop('readonly', false);
		$('#password').val('');
		$('#re_password').val('');
		$('#password_row').show();
		$('#dni').val('');
		$('#phone').val('');
		$('#phone_job').val('');
		$('#profession').val('');
		$('#position').val('');
		$('#address').val('');
		$('#street').val('');
		$('#colony').val('');
		$('#municipality').val('');
		$('#state').val('');
		$('#pc').val('');
		$('#ext_number').val('');
		$('#int_number').val('');
		$('#reference').val('');
		$('#delivery-content').attr('hidden', true);
		setSelect('status', 'A');
		setSelect('profile', '');
		if (user_org) {
			setSelect('organization', user_org);
		} else {
			setSelect('organization', '');
		}
		setSelect('commission', '0');
		$('#user_form').attr('action', 'api/user/store');
		$('#user_form').attr('method', 'POST');

		$('#perfil input[type=checkbox]').each(function () {
			$(this).attr('checked', false);
			$(this).val('0');
		});

		$('#perfil input[type=text]').each(function () {
			$(this).val('0');
		});

		$('#ware_org')[0].selectize.clearOptions();
		$('#platform')[0].selectize.setValue();
		$('#ware_org_cont').attr('hidden', 'hidden');

		$('#codBV').val('');
		$('#delcod-az-content').attr('hidden', true);
		$('#delcod-bv-content').attr('hidden', true);
		if ($('#bankAccount').length) {
			$('#bankAccount')[0].selectize.trigger('change');
		}

	}
}

$('#myModal').on('hidden.bs.modal', function () {
	$('.nav-tabs a[href="#datos"]').trigger('click');
	setModal(null);
});

/*Funcion para mostrar listado de usuarios (Tabla)*/
function drawTable() {
	$(".preloader").fadeIn();

	if ($.fn.DataTable.isDataTable('#myTable')) {
		$('#myTable').DataTable().destroy();
	}

	var dataFilter = {
		org: $('#orgS').val(),
		coord: $('#supervisorS').val(),
		status: $('#statusS').val(),
		profile: $('#profileS').val(),
		userType: $('#userTypeS').val(),
		distributor: $('#distributor').val()
	};

	var tableUsers = $('#myTable').DataTable({
		language: {
			sProcessing: "Procesando...",
			sLengthMenu: "Mostrar _MENU_ registros",
			sZeroRecords: "No se encontraron resultados",
			sEmptyTable: "Ningún dato disponible en esta tabla",
			sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
			sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
			sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
			sInfoPostFix: "",
			sSearch: "Buscar:",
			sUrl: "",
			sInfoThousands: ",",
			sLoadingRecords: "Cargando...",
			oPaginate: {
				sFirst: "Primero",
				sLast: "Último",
				sNext: "Siguiente",
				sPrevious: "Anterior"
			},
			oAria: {
				sSortAscending: ": Activar para ordenar la columna de manera ascendente",
				sSortDescending: ": Activar para ordenar la columna de manera descendente"
			}
		},
		processing: true,
		serverSide: true,
		order: [[1, "asc"]],
		//ordering: false,
		ajax: {
			url: 'api/user/get/datatable',
			data: function (d) {
				d.filter = dataFilter;
				d._token = $('meta[name="csrf-token"]').attr('content');
			},
			type: "POST"
		},
		initComplete: function (settings, json) {
			$('#content-table-users').show();
			$(".preloader").fadeOut();
		},
		deferRender: true,
		columns: [
			{
				data: 'last_name', render: function (data, type, row, meta) {
					var html = '';
					if (row.edit == '1') {
						html += '<button type="button" class="btn btn-warning btn-md button" onclick="update(\'' + row.email + '\')">Editar</button>'
					}
					if (row.chpass == '1') {
						html += '<button type="button" class="btn btn-primary btn-md button" onclick="chpass(\'' + row.email + '\')">Editar contraseña</button>'
					}
					if (row.delete == '1') {
						html += '<button type="button" class="btn btn-danger btn-md button" onclick="deleteData(\'' + row.email + '\')">Eliminar</button>'
					}
					return html;
				}, searchable: false, orderable: false
			},
			{ data: 'name', searchable: false },
			{ data: 'email', searchable: false },
			{ data: 'platform', searchable: false },
			{ data: 'dni', searchable: false, orderable: false },
			{ data: 'phone', searchable: false },
			{ data: 'phone_job', searchable: false },
			{ data: 'id_org', searchable: false },
			{ data: 'profession', searchable: false, orderable: false },
			{ data: 'position', searchable: false, orderable: false },
			{ data: 'address', searchable: false, orderable: false },
			{ 
				data: 'distributor', render: function (data, type, row, meta) {

					if(row.distributor == null){
						return 'N/A';
					}else{
						return row.distributor;
					}
				}, searchable: false, orderable: false
			}
		]
	});

	tableUsers.on('search.dt', function () {
		window.location.hash = tableUsers.search();
	});

	var serachHash = window.location.hash ? window.location.hash.split('#') : false;
	if (serachHash && serachHash.length > 1) {
		$('#myTable').DataTable().search(
			decodeURI(serachHash[1]),
			true,
			true
		).draw();
	}
}

function profileSChange() {
	var df = {
		org: $('#orgS').val(),
		pro: $('#profileS').val()
	};
	$(".preloader").fadeIn();
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		url: 'api/user/get/filter-supervisors',
		type: 'post',
		data: df,
		success: function (res) {
			if (res) {
				valact = $('#supervisorS').val();

				$('#supervisorS')[0].selectize.destroy();
				$("#supervisorS [value!='']").remove();
				res.cs.forEach(function (c) {
					var optVal = {
						value: c.email,
						text: c.name + ' ' + c.last_name
					}
					$('#supervisorS').append($('<option>', optVal));
				});
				$('#supervisorS').selectize();
				$('#supervisorS')[0].selectize.setValue(valact, true);
			}
			$(".preloader").fadeOut();
		},
		error: function (res) {
			console.log(res);
			$(".preloader").fadeOut();
		}
	});
}

function initSelectizeProfileS() {
	$('#profileS').selectize({
		onChange: function (e) {
			profileSChange();
		}
	});
}

function getFilterProfile(data) {
	$(".preloader").fadeIn();
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		url: 'api/user/get/filter-profiles',
		type: 'post',
		data: data,
		success: function (res) {
			if (res) {
				valact = $('#profileS').val();

				$('#profileS')[0].selectize.destroy();
				$("#profileS [value!='']").remove();
				res.cs.forEach(function (c) {
					var optVal = {
						value: c.id,
						text: c.name
					}
					$('#profileS').append($('<option>', optVal));
				});
				initSelectizeProfileS();
				$('#profileS')[0].selectize.setValue(valact, true);
				profileSChange();
			}
			$(".preloader").fadeOut();
		},
		error: function (res) {
			$(".preloader").fadeOut();
		}
	});
}

function toggleWereHouse() {
	var type = $('#organization').selectizeData('type');
	var org = $('#organization').val();
	var plat = $('#platform').val();
	var pro = $('#profile').val();

	var sel = $('#ware_org')[0].selectize;
	sel.clearOptions();

	if (type == 'R' && plat == 'vendor' && pro == 11) {
		$('#ware_org_cont').removeAttr('hidden');

		$(".preloader").fadeIn();

		//Politica de desbloque de equipos
		$('#value_17154').prop('checked', true);
		$('#value_17154').val('1');

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: 'api/user/get/warehouses',
			type: 'post',
			data: { 'org': org },
			success: function (res) {
				if (res && !res.error) {
					res.data.forEach(function (c) {
						var optVal = {
							value: c.id,
							text: c.name
						};

						sel.addOption(optVal);
					});

					if (sessionStorage.getItem('modalWareOrg')) {
						setSelect('ware_org', sessionStorage.getItem('modalWareOrg').split(','));
						sessionStorage.removeItem('modalWareOrg');
					}
				} else {
					alert('No se encontraron bodegas asignadas a esta organización')
				}
				$(".preloader").fadeOut();
			},
			error: function (res) {
				console.log(res);
				$(".preloader").fadeOut();
			}
		});
	}
	else {
		$('#value_17154').prop('checked', null);
		$('#value_17154').val('0');
		$('#ware_org_cont').attr('hidden', 'hidden');
	}
}

function getSupervisors() {
	if ($('#profile').val() != "" && $('#organization').val() != "") {
		var data = {
			org: $('#organization').val(),
			pro: $('#profile').val()
		};

		$(".preloader").fadeIn();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: 'api/user/get/supervisors',
			type: 'post',
			data: data,
			success: function (res) {
				if (res) {
					bd = 0;
					if (sessionStorage.getItem('modalParentEmail')) {
						valact = sessionStorage.getItem('modalParentEmail');
						sessionStorage.removeItem('modalParentEmail');
						bd = 1;
					}
					else {
						valact = $('#parent_email').val();
					}

					$('#parent_email')[0].selectize.destroy();
					$("#parent_email [value!='']").remove();
					res.cs.forEach(function (c) {
						var optVal = {
							value: c.email,
							text: c.name + ' ' + c.last_name + '	|	' + (c.distributor_name != null ? c.distributor_name : 'Sin Distribuidor')
						}
						$('#parent_email').append($('<option>', optVal));
					});
					$('#parent_email').selectize();
					$('#parent_email')[0].selectize.setValue(valact, true);

					if (bd == 1 && $('#replacement').val() != '' && $('#parent_email').val() != '') {
						$('#parent_email')[0].selectize.lock()
					}
				}
				$(".preloader").fadeOut();
			},
			error: function (res) {
				console.log(res);
				$(".preloader").fadeOut();
			}
		});
	}
	else {
		$('#parent_email')[0].selectize.destroy();
		$("#parent_email [value!='']").remove();
		$('#parent_email').selectize();
	}
}

function getReplacements() {
	if ($('#profile').val() != "") {
		var data = {
			org: $('#organization').val(),
			pro: $('#profile').val()
		};

		$(".preloader").fadeIn();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: 'api/user/get/replacements',
			type: 'post',
			data: data,
			success: function (res) {
				if (res) {
					valact = $('#replacement').val();

					$('#replacement')[0].selectize.destroy();
					$("#replacement [value!='']").remove();
					res.cs.forEach(function (c) {
						var optVal = {
							value: c.email,
							text: c.name + ' ' + c.last_name,
							'data-parent': c.parent_email,
							'data-org': c.id_org,
						}
						$('#replacement').append($('<option>', optVal));
					});

					$('#replacement').val(valact);
					initSelectizeReplacement();
				}
				$(".preloader").fadeOut();
			},
			error: function (res) {
				console.log(res);
				$(".preloader").fadeOut();
			}
		});
	}
	else {
		$('#replacement')[0].selectize.destroy();
		$("#replacement [value!='']").remove();
		initSelectizeReplacement();
	}
}

function initSelectizeReplacement() {
	$('#replacement').selectize({
		onInitialize: function () {
			addSelectizeDataAttr(this);
		},
		onChange: function () {
			if ($('#replacement').val() != "" && $('#user_form').attr('method') == 'POST') {
				var parent = $('#replacement').selectizeData('parent'); //obteniendo data-parent de option seleccionado
				var org = $('#replacement').selectizeData('org'); //obteniendo data-org de option seleccionado
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					async: true,
					url: 'api/user/getUserPolicy/' + $('#replacement').val() + '/N',
					method: 'GET',
					success: function (res) {
						if (res.policies) {
							setModalPolicies(res.policies);
						}
					},
					error: function (res) {
						alert('Ocurrio un error al conectar con el servidor. Por favor intente mas tarde.');
					}
				});


				sessionStorage.setItem('modalParentEmail', parent);
				setSelect('organization', org);
			}
			else {
				$('#parent_email')[0].selectize.unlock();
			}
		}
	});

	valact = $('#replacement').val();
	$('#replacement')[0].selectize.setValue(valact, true);
	if ($('#replacement').val() == '') {
		$('#parent_email')[0].selectize.unlock();
	}
}

function initSelectizeProfile() {
	$('#profile').selectize({
		onInitialize: function () {
			addSelectizeDataAttr(this);
		},
		onChange: function () {

			var profile = $('#profile');

			if (profile.val() != "") {

				var platform = profile.selectizeData('platform'); //obteniendo data-platforn de option seleccionado

				$('#platform')[0].selectize.setValue(platform, true);

				if ($('#platform').val() != 'admin') {

					$('#second_pass_area').hide();
					$('.pass_cont').removeClass('col-md-4').addClass('col-md-6');

				} else {

					$('#second_pass_area').show();
					$('.pass_cont').addClass('col-md-4').removeClass('col-md-6');

				}

				if ($('#platform').val() == 'vendor' || $('#platform').val() == 'coordinador')
					$("#chclass").removeAttr('hidden');

				var proftype = profile.selectizeData('type');
				$('#organization_area').show();

				if (profile.val() == 1 || profile.val() == '1' ||
					profile.val() == 2 || profile.val() == '2' ||
					profile.val() == 3 || profile.val() == '3' ||
					profile.val() == 4 || profile.val() == '4' ||
					profile.val() == 5 || profile.val() == '5' ||
					profile.val() == 20 || profile.val() == '20')
					$('#organization_area').hide();

				if (user_log == 0)
					$('#perfiltab').hide();

			}

			getSupervisors();
			getReplacements();

			if (sessionStorage.getItem('setModal'))
				sessionStorage.removeItem('setModal');
			else
				get_profile(profile.val());

			toggleWereHouse();

		}
	});

	if (sessionStorage.getItem('modalProfile')) {
		setSelect('profile', sessionStorage.getItem('modalProfile'));
		sessionStorage.removeItem('modalProfile');
	}

}

function getProfilesByPlatform(data) {
	$(".preloader").fadeIn();
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		url: 'api/user/get/profiles-by-platform',
		type: 'post',
		data: data,
		success: function (res) {
			if (res) {
				$('#profile')[0].selectize.destroy();
				$("#profile [value!='']").remove();
				res.profiles.forEach(function (profile) {
					var optVal = {
						value: profile.id,
						text: profile.name,
						'data-platform': profile.platform,
						'data-hassup': profile.has_supervisor,
						'data-type': profile.type,
					}
					$('#profile').append($('<option>', optVal));;
				});
				initSelectizeProfile();
			}
			$(".preloader").fadeOut();
		},
		error: function (res) {
			$(".preloader").fadeOut();
		}
	});
}

initCodes = function () {
	$('#bankAccount').selectize({
		onChange: function () {
			$(".preloader").fadeIn();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				url: 'api/user/bank/cod-dep',
				type: 'post',
				data: { bank: $('#bankAccount').val().trim() },
				success: function (res) {
					if (!res.error) {
						$('#codBA')[0].selectize.clearOptions();
						$('#codBA')[0].selectize.destroy();
						$('#codBA').html('')

						res.codes.forEach(function (c) {
							let optVal = {
								value: c,
								text: c
							};

							$('#codBA').append($('<option>', optVal));
						});

						$('#codBA').append($('<option>', { value: '', text: 'Seleccione un código' }));

						$('#codBA').selectize();
						setSelect('codBA', '');
					} else {
						alert('No se pudo actualizar la lista de códigos de depósitos');
					}

					$(".preloader").fadeOut();
				},
				error: function (res) {
					alert('No se pudo actualizar la lista de códigos de depósitos');
					$(".preloader").fadeOut();
				}
			});
		}
	});
}

function loadDivision() {
	$("#division_select").find('option').not(':first').remove();
	$("#distributor_select").find('option').not(':first').remove();
	$.ajax({
		type: "POST",
		url: 'api/user/getdivision',
		data: {
			_token: $('meta[name="csrf-token"]').attr('content')
		},
		dataType: "json",
		success: function (response) {
			divisionss = document.getElementById('division_select');
			distributorss = document.getElementById('distributor_select');

			if (response.success) {

				$(response.divisions).each(function(){
					$('#division_select')[0].selectize.addOption({value:this.id,text:this.name});
					$('#division_select')[0].selectize.addItem(this.id); 
				});

				$(response.distributors).each(function(){
					$('#distributor_select')[0].selectize.addOption({value:this.id,text:this.description});
					$('#distributor_select')[0].selectize.addItem(this.id); 
				});

			}

			$('#division_select').attr('loaded', 'true');
			$('#distributor_select').attr('loaded', 'true');
			$('#division_content').show();
			$('#distributor_select')[0].selectize.setValue('');

			if($('#organization').val() == 1){
				$('#distributor_content').show();
			}else{
				$('#distributor_content').hide();
			}
		},
		error: function (err) {
			console.log("error al crear el listado: ", err);
		}
	});
}

function loadRegion() {
	$("#region_select").find('option').not(':first').remove();
	$.ajax({
		type: "POST",
		url: 'api/user/getregions',
		data: {
			_token: $('meta[name="csrf-token"]').attr('content'),
			division: $('#division_select').val()
		},
		dataType: "json",
		success: function (responseR) {

			if (responseR.success) {
				for (var i = 0; i < responseR.data.length; i++) {
					$('#region_select')[0].selectize.addOption({value:responseR.data[i].id, text:responseR.data[i].name});
					$('#region_select')[0].selectize.addItem(responseR.data[i].id); 
				}
			}
			$('#region_select').attr('loaded', 'true');
		},
		error: function (err) {
			console.log("error al crear el listado: ", err);
		}
	});
}

function loadCoordinacion() {
	$("#coordinacion_select").find('option').not(':first').remove();
	$.ajax({
		type: "POST",
		url: 'api/user/getcoordinacion',
		data: {
			_token: $('meta[name="csrf-token"]').attr('content'),
			regions: $('#region_select').val()
		},
		dataType: "json",
		success: function (responseC) {
			//console.log(responseC.data.length);
			selectC = document.getElementById('coordinacion_select');
			if (responseC.success) {
				for (var i = 0; i < responseC.data.length; i++) {

					$('#coordinacion_select')[0].selectize.addOption({value:responseC.data[i].id, text:responseC.data[i].name});
					$('#coordinacion_select')[0].selectize.addItem(responseC.data[i].id); 
				}
			}
			$('#coordinacion_select').attr('loaded', 'true');
		},
		error: function (err) {
			console.log("error al crear el listado: ", err);
		}
	});
}


$(document).ready(function () {
	$(".preloader").fadeOut();
	$('[data-toggle="tooltip"]').tooltip();
	$('#ware_org').selectize();

	if ($('#bankAccount').length) {
		initCodes();

		if ($('#codBA')[0].selectize == undefined) {
			$('#codBA').selectize();
		}
	}

	if ($('#address')[0]) {
		let autocomplete = new google.maps.places.Autocomplete($('#address')[0]);
		google.maps.event.addListener(autocomplete, 'place_changed', function() {
			if($('#platform').val() == 'coordinador'){
				let place = autocomplete.getPlace();
				if(place.address_components.length){
					place.address_components.forEach((comp) => {
						if(comp.types.includes('route')){
							$('#street').val(comp.long_name);
						}

						if(comp.types.includes('sublocality_level_1') && comp.types.includes('sublocality')){
							$('#colony').val(comp.long_name);
						}

						if(comp.types.includes('locality')){
							$('#municipality').val(comp.long_name);
						}

						if(comp.types.includes('administrative_area_level_1')){
							$('#state').val(comp.long_name);
						}

						if(comp.types.includes('postal_code')){
							$('#pc').val(comp.long_name);
						}
					});
				}
			}
		});
	}

	//Evento para botones de liminar códigos de depositos
	$('.btn-del-cod').on('click', function (e) {
		let btn = $(this);
		let bank = btn.data('bank');
		let cod = btn.data('cod');
		let email = $('#email').val();

		if (bank && cod && email) {
			swal({
				title: '¿Estás seguro de eliminar la asignación del código de depósito?',
				text: 'Esta acción no tiene reverso',
				icon: 'warning',
				buttons: true,
				dangerMode: true,
			})
				.then((willDelete) => {
					if (willDelete) {
						$(".preloader").fadeIn();

						$.ajax({
							headers: {
								'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							},
							url: 'api/user/delete-cod-dep',
							type: 'post',
							data: { bank: bank, cod: cod, user: email },
							success: function (res) {
								if (!res.error) {
									if (btn.attr('id') == 'btn-del-bv') {
										$('#delcod-bv-content').attr('hidden', true);
										$('#codBV').val('');
									}

									if (btn.attr('id') == 'btn-del-az') {
										$('#delcod-az-content').attr('hidden', true);
										if ($('#bankAccount').length) {
											$('#bankAccount')[0].selectize.trigger('change');
										}
									}
								} else {
									alert('No se pudo eliminar la asignación del códigos de depósito');
								}

								$(".preloader").fadeOut();
							},
							error: function (res) {
								alert('No se pudo eliminar la asignación del códigos de depósito');
								$(".preloader").fadeOut();
							}
						});
					}
				});
		}
	});

	$('#division_content').hide();
	$('#distributor_content').hide();
	$('#region_content').hide();
	$('#coordinacion_content').hide();

	$('.searchSelect').select2({
		selectOnClose: true
	});


	$('#platform').change(function (e) {
		$('#division_content').hide();
		$('#distributor_content').hide();
		$('#region_content').hide();
		$('#coordinacion_content').hide();
	});

	$('#profile').change(function (e) {
		$("#division_select").find('option').not(':first').remove();
		$("#distributor_select").find('option').not(':first').remove();
		$("#region_select").find('option').not(':first').remove();
		$("#coordinacion_select").find('option').not(':first').remove();
		$('#division_select').prop('required', false);
		$("#region_select").prop('required', false);
		$("#coordinacion_select").prop('required', false);

		if ($('#profile').val() == 16) { //division

			loadDivision();
			$('#region_select').val('');
			$('#coordinacion_select').val('');
			$('#region_content').hide();
			$('#coordinacion_content').hide();
			$("#division_select").prop('required', true);


		} else if ($('#profile').val() == 17) { // region

			loadRegion();
			$('#division_content').hide();
			$('#distributor_content').hide();
			$('#division_select')[0].selectize.setValue('');
			$('#distributor_select')[0].selectize.setValue('');
			$('#region_content').show();
			$('#coordinacion_select')[0].selectize.setValue('');
			$('#coordinacion_content').hide();
			$("#region_select").prop('required', true);


		} else if ($('#profile').val() == 10 || $('#profile').val() == 18) {  //coordinacion

			loadCoordinacion();
			$('#division_content').hide();
			$('#distributor_content').hide();
			$('#division_select')[0].selectize.setValue('');
			$('#distributor_select')[0].selectize.setValue('');
			$('#region_content').hide();
			$('#region_select')[0].selectize.setValue('');

			$('#coordinacion_content').show();
			$("#coordinacion_select").prop('required', true);
		} else {
			//Reseteo y vacio campos
			$('#division_content').hide();
			$('#distributor_content').hide();
			$('#region_content').hide();
			$('#coordinacion_content').hide();

			$('#division_select')[0].selectize.setValue('');
			$('#distributor_select')[0].selectize.setValue('');
			$('#region_select')[0].selectize.setValue('');
			$('#coordinacion_select')[0].selectize.setValue('');
		}
	});

	$('#downloadCSV').on('click', function (e) {
		$(".preloader").fadeIn();
		e.preventDefault();
		var url = $('#downloadCSV').attr('href');

		if (url) {
			var dataFilter = {
				org: $('#orgS').val(),
				coord: $('#supervisorS').val(),
				status: $('#statusS').val(),
				profile: $('#profileS').val(),
				userType: $('#userTypeS').val(),
				distributor: $('#distributor').val()
			};

			$.ajax({
				type: "POST",
				url: url,
				data: { filter: dataFilter, _token: $('meta[name="csrf-token"]').attr('content') },
				dataType: "text",
				success: function (response) {
					$(".preloader").fadeOut();
					var uri = 'data:application/csv;charset=UTF-8,' + encodeURIComponent(response);

					var link = document.createElement('a');
					link.href = uri;

					if (link.download !== undefined) {
						link.download = "usuarios.csv";
					}

					if (document.createEvent) {
						var e = document.createEvent('MouseEvents');
						e.initEvent('click', true, true);
						link.dispatchEvent(e);
						return true;
					}


					window.open(uri, 'usuarios.csv');
				},
				error: function (err) {
					$(".preloader").fadeOut();
				}
			});
		}
	})

	$('#user_form').validate({
		//siguiente linea es necesaria para poder validar los selects que trabajan con selectize.js
		ignore: ':hidden:not([class~=selectized]),:hidden > .selectized, .selectize-control .selectize-input input',
		errorPlacement: function (error, element) {
			var array = ['parent_email', 'platform', 'profile', 'organization', 'status'];
			var idx = array.indexOf(element.attr("name"));
			if (idx != -1) {
				$(element).parent().find('.selectize-control').addClass("select_" + element.attr("name"));
				error.appendTo(".selectize-control.select_" + element.attr("name"));
			} else {
				error.insertAfter(element)
			}
		},
		rules: {
			name: {
				required: true
			},
			last_name: {
				required: true
			},
			email: {
				required: true,
				emailValidate: true
			},
			/*curp: { Comentado debido a Telmovpay
				required: true,
				curpValidate: true
			},	*/		
			re_password: {
				equalTo: "#password"
			},
			profession: {
				required: true
			},
			position: {
				required: true
			},
			address: {
				required: true
			},
			platform: {
				required: true
			},
			profile: {
				required: true
			},
			commission: {
				number: true,
				max: 1.000,
				min: 0
			},
			phone: {
				phoneValidate: true
			},
			phone_job: {
				phoneValidate: true
			},
			street: {
				required: function () {
					if ($('#platform').val() == 'coordinador') {
						return true;
					} else {
						return false;
					}
				}
			},
			colony: {
				required: function () {
					if ($('#platform').val() == 'coordinador') {
						return true;
					} else {
						return false;
					}
				}
			},
			municipality: {
				required: function () {
					if ($('#platform').val() == 'coordinador') {
						return true;
					} else {
						return false;
					}
				}
			},
			state: {
				required: function () {
					if ($('#platform').val() == 'coordinador') {
						return true;
					} else {
						return false;
					}
				}
			},
			pc: {
				required: function () {
					if ($('#platform').val() == 'coordinador') {
						return true;
					} else {
						return false;
					}
				},
				minlength: 5,
        maxlength: 5,
        digits: true
			},
			ext_number: {
				required: function () {
					if ($('#platform').val() == 'coordinador') {
						return true;
					} else {
						return false;
					}
				}
			},
			int_number: {
				required: function () {
					if ($('#platform').val() == 'coordinador') {
						return true;
					} else {
						return false;
					}
				}
			},
			reference: {
				required: function () {
					if ($('#platform').val() == 'coordinador') {
						return true;
					} else {
						return false;
					}
				}
			},
			organization: {
				required: function () {

					let profile = $('select[name="profile"] :selected');
					return !(profile.val() == 1 || profile.val() == '1' ||
						profile.val() == 2 || profile.val() == '2' ||
						profile.val() == 3 || profile.val() == '3' ||
						profile.val() == 4 || profile.val() == '4' ||
						profile.val() == 5 || profile.val() == '5' ||
						profile.val() == 20 || profile.val() == '20');

				}
			},
			parent_email: {
				required: function () {
					var has_sup = $('#profile').selectizeData('hassup'); //obteniendo data-hassup de option seleccionado
					if (has_sup == 'N') {
						return false;
					} else {
						return true;
					}
				}
			},
			codBV: {
				codDepBVValidate: true,
				remote: {
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					url: 'api/user/bank/check-code',
					type: 'POST',
					data: {
						user: function () {
							return $('#email').val().trim();
						}
					}
				}
			}
		},
		messages: {
			name: "Por favor especifique el nombre",
			last_name: "Por Favor especifique el apellido",
			email: {
				required: "Por favor especifique el email",
				emailValidate: "Ingrese una dirección de correo valida"
			},
			/*curp: {  Comentado debido a Telmovpay
				required: "Por favor especifique el curp",
				curpValidate: "Ingrese un CURP valido"
			},	*/		
			re_password: {
				equalTo: "Las contraseñas no son iguales"
			},
			profession: "Por favor especifique la profesión",
			position: "Por favor especifique el cargo",
			address: "Por favor especifique la direccón",
			platform: "Seleccione un tipo de usuario",
			profile: "Seleccione el perfil de usuario",
			commission: {
				number: "Ingrese un valor decimal comprendido desde 0 hasta 1 (1 = 100%)",
				max: "El valor es mayor a 1",
				min: "El valor es menor a 0"
			},
			parent_email: "Por favor seleccione el supervisor",
		}
	});

	$('#chpass_form').validate({
		rules: {
			ch_password: {
				required: true
			},
			ch_re_password: {
				required: true,
				equalTo: "#ch_password"
			}
		},
		messages: {
			ch_password: "Introduzca una contraseña",
			ch_re_password: {
				required: "Introduzca nuevamente la contraseña",
				equalTo: "Las contraseñas no coinciden"
			}
		}
	});

	$('#platform').selectize({
		onChange: function () {
			var data = {
				platform: $('#platform').val()
			};
			getProfilesByPlatform(data);

			if (this.getValue() != 'admin') {
				$('#second_pass_area').hide();
				$('.pass_cont').removeClass('col-md-4').addClass('col-md-6')
			} else {
				$('#second_pass_area').show();
				$('.pass_cont').addClass('col-md-4').removeClass('col-md-6')
			}
			if (this.getValue() == 'vendor' || this.getValue() == 'coordinador') {
				$("#chclass").removeAttr('hidden');
			}
			else {
				$("#chclass").attr('hidden', 'true');
			}

			$('#delivery-content').attr('hidden', true);
			if (this.getValue() == 'coordinador') {
				$('.tab-cod').attr('hidden', null);
				$('#delivery-content').attr('hidden', null);
			} else {
				$('.tab-cod').attr('hidden', true);
			}

			toggleWereHouse();
		}
	});

	initSelectizeProfile();

	$('#orgS').selectize({
		onChange: function (e) {
			if($('#orgS').val() == 1 || $('#orgS').val() == ''){
				$('#distributor-content').show();
			}else{
				$('#distributor')[0].selectize.setValue();
				$('#distributor-content').hide();
			}
			var df = {
				org: $('#orgS').val()
			};
			getFilterProfile(df);
		}
	});

	initSelectizeProfileS();

	$('#supervisorS').selectize();
	$('#statusS').selectize();
	$('#userTypeS').selectize();
	$('#distributor').selectize();
	$('#distributor_select').selectize();
	$('#division_select').selectize();
	$('#region_select').selectize();
	$('#coordinacion_select').selectize();

	$("#open_modal_btn").on('click', (elem) => {
		modal_id = '#myModal';
		$('#distributor_name_content').hide();
		if ($(elem.target).data('modal')) {
			modal_id = $(elem.target).data('modal');
		}
		$(modal_id).modal({ backdrop: 'static', keyboard: false });
	});

	$("#parent_email").selectize();
	$('#organization').selectize({
		onInitialize: function () {
			addSelectizeDataAttr(this);
		},
		onChange: function () {
			
			if($('#organization').val() == 1 && $('#user_form').attr('method') == 'PUT'){
				if($('#profile').val() == 16){
					$('#distributor_content').show();
				}else{
					$('#distributor_name_content').show();

				}
			}else{
				$('#distributor_content').hide();
				$('#distributor_name_content').hide();
			}

			if($('#organization').val() == 1 && $('#user_form').attr('method') == 'POST' && $('#profile').val() == 16){
				$('#distributor_content').show();
			}else{
				$('#distributor_select')[0].selectize.setValue('');
				if($('#profile').val() != 16){
					$('#distributor_content').hide();

				}
			}
			getSupervisors();
			getReplacements();
			toggleWereHouse();
		}
	});
	$('#commission').selectize();
	$("#status").selectize();

	initSelectizeReplacement();

	$('#myModal').on('shown.bs.modal', function () {
		$('#phone,#phone_job').intlTelInput({
			onlyCountries: ["mx"], //solo selecciona mexico
			initialCountry: "mx", //inicio la lista con mexico
			separateDialCode: true, // le doy separacion a los num
			// preferredCountries: ["mx"],//De toda la lista elijo mexico
			utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.0/js/utils.js"
		});
		$('.iti__selected-flag').css('height', $('#phone').css('height'));
		$('.iti--allow-dropdown').css('width', '100%');
	});

	$('#phone,#phone_job').on('change', function (e) {
		formatPhone($(this), true); //formateo de numero en formato nacional
	});

});