// Student js
var myModal;

function loadEnrolment(enrolID)
{
	window.history.pushState({}, '', '#'+enrolID);
	myModal.open("#modalWindow");
	jQuery('#modalWindowContents').html('<div class="loading-notification">Loading course information...</div>');
	jQuery.ajax({
		url: '',
		method: 'POST',
		data: { coursesource_action: 'getMyCourseData', enrolID:enrolID, DOING_AJAX:true },
	}).done( function(rtn) {
		if ( rtn == '' || rtn == 0 ) {
			jQuery('.myCourseDataContainer').html('Course lesson data could not be found.');
		} else {
			jQuery('#modalWindowContents').html(rtn);
		}
	});
}

jQuery(document).on('ready', function() {

	myModal = new HystModal({
		afterClose: function(){
			window.history.pushState({}, '', '#');
		},
	});

	jQuery('#addEnrolmentKey').click(function(e) {
		jQuery('#modalWindowContents').html('<h2>Add Enrolment via Key</h2>\
			<p>If you have been sent an erolment key, enter it below to use it and add the course to your profile.</p>\
			<form id="addEnrolmentForm"><input type="text" id="enrolmentKeyInput" name="key" value=""/><span class="button alt" id="submitEnrolmentForm">Submit</span></form><div id="enrolmentFeedback"></div>');
		myModal.open("#modalWindow");
	});

	jQuery('#modalWindowContents').on('click', '#submitEnrolmentForm', function(e) {
		jQuery('#enrolmentFeedback').removeClass('alert error success').text('').hide();
		jQuery.post('',{ coursesource_action:'enrol', enrolmentKey: jQuery('#enrolmentKeyInput').val() },function(resultRaw){
			var result = JSON.parse(resultRaw);
			if (!result.result)
			{
				jQuery('#enrolmentFeedback').addClass('alert error').show().text(result.error);
			}
			else
			{
				jQuery('#enrolmentFeedback').addClass('alert success').show().text('Enrolment successful, reloading page.');
				window.location.reload();
			}
		});
		//console.log(jQuery('#enrolmentKeyInput').val());
	});

	jQuery('#modalWindowContents').on('click', '.tutorialOpen', function(e) {
		e.preventDefault();
		iframeURL = jQuery(this).attr('data-attr_url');
		
		returnURL = window.location.href;
		returnURL = returnURL.replace(/\#/g, '');
		
		if ( returnURL.indexOf('?') !== -1 ) {
			returnURL += '&coursesource_action=closeCourseWindow';
		} else {
			returnURL += '?coursesource_action=closeCourseWindow';
		}
		
		jQuery('#cs_overlay').html('<iframe class="cs_iframe" marginwidth="0" margin="0" height="0" scrolling="no" src="'+iframeURL+'&returnURL='+returnURL+'"></iframe>');
		jQuery('#cs_overlay').show();
	});
	
	if (parseInt(window.location.hash.replace('#',''))>0)
		loadEnrolment(parseInt(window.location.hash.replace('#','')));

	window.onhashchange = () => {
		if (parseInt(window.location.hash.replace('#',''))>0)
			loadEnrolment(parseInt(window.location.hash.replace('#','')));
	};
});