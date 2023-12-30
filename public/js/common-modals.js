$(document).ready(function () {
    modalAnimation = (animation,id) => {
	  $(id+' .modal-dialog').attr('class', 'modal-dialog  animate__animated ' + animation + ' animate__faster');
	};
	$('.modalAnimate').on('show.bs.modal', (elem) => {
	  modalAnimation('animate__fadeInDown','#'+elem.target.id);
	});
	$(".modal_close_btn, #modal_close_btn, #modal_close_x").on('click', (elem) => {
		modal_id = '#myModal';
		if($(elem.target).data('modal')){
			modal_id = $(elem.target).data('modal');
		}

		time_animate = ($(modal_id+' .modal-dialog').css('animation-duration').replace('s','')*1000)*.9;
    	modalAnimation('animate__fadeOutUp',modal_id);
    	setTimeout(()=>{ $(modal_id).modal('hide') }, time_animate);
    });
});