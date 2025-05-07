;(function ($) {

    var myModal;

    $(document).ready(function () {
        var courseDetails = $('#myCourseDataContainer');
        var myCoursesTable = $('#myCoursesTable');
        var ajaxNonce = myCoursesTable.attr('data-nonce');

        myModal = new HystModal({
            afterClose: function () {
                window.history.pushState({}, '', '#');
            },
        });

        $('.openEnrolment').on('click', function (e) {
            var enrolmentID = $(this).attr('data-enrolment_id');
            loadEnrolment(enrolmentID);
        });

        function loadEnrolment(enrolID) {
            window.history.pushState({}, '', '#' + enrolID);
            $.ajax({
                url: CoursesourceFrontend.ajaxurl,
                method: 'POST',
                data: {
                    action: 'cs_course_table_details',
                    enrolment_id: enrolID,
                    nonce: ajaxNonce,
                },
            }).done(function (result) {
                if (result == '' || result == 0) {
                    courseDetails.html('Course lesson data could not be found.');
                } else {
                    courseDetails.html(result);
                }
                $('html,body').animate({scrollTop: courseDetails.offset().top}, 'slow');
            });
        }


        $('#addEnrolmentKey').click(function (e) {
            $('#modalWindowContents').html('<h2>Add Enrolment via Key</h2>\
			<p>If you have been sent an enrolment key, enter it below to use it and add the course to your profile.</p>\
			<form id="addEnrolmentForm"><input type="text" id="enrolmentKeyInput" name="key" value=""/><span class="button alt" id="submitEnrolmentForm">Submit</span></form><div id="enrolmentFeedback"></div>');
            myModal.open("#modalWindow");
        });

        $('#modalWindowContents').on('click', '#submitEnrolmentForm', function (e) {
            e.preventDefault();
            var enrolmentFeedBack = $('#enrolmentFeedback');
            enrolmentFeedBack.removeClass('alert error success').text('').hide();
            $.ajax({
                url: CoursesourceFrontend.ajaxurl,
                method: 'POST',
                data: {
                    action: 'course_enrole_from_key',
                    key: $('#enrolmentKeyInput').val(),
                    nonce: ajaxNonce,
                },
            }).done(function (result) {
                if (result.success) {
                    enrolmentFeedBack.addClass('alert success').show().text('Enrolment successful, reloading page.');
                    window.location.reload();
                } else if (result.error) {
                    enrolmentFeedBack.addClass('alert error').show().text(result.error.join('\n'));
                }
            });
        });

        $('#myCourseDataContainer').on('click', '.tutorialOpen', function (e) {
            e.preventDefault();
            var iframeURL = $(e.target).attr('data-lesson-url');
            var returnURL = window.location.href;
            returnURL = returnURL.replace(/\#/g, '');

            if (returnURL.indexOf('?') !== -1) {
                returnURL += '&coursesource_action=closeCourseWindow';
            } else {
                returnURL += '?coursesource_action=closeCourseWindow';
            }

            $('#cs_overlay').html('<iframe class="cs_iframe" marginwidth="0" margin="0" height="0" scrolling="no" src="' + iframeURL + '&returnURL=' + returnURL + '"></iframe><span class="cs_iframe-close">x</span>');
            $('#cs_overlay').show();
        });

        $('#cs_overlay').on('click', '.cs_iframe-close', function (e) {
            $('#cs_overlay').hide();
            $('#cs_overlay').html('');
        });

        if (parseInt(window.location.hash.replace('#', '')) > 0) {
            loadEnrolment(parseInt(window.location.hash.replace('#', '')));
        }

        window.onhashchange = () => {
            if (parseInt(window.location.hash.replace('#', '')) > 0) {
                loadEnrolment(parseInt(window.location.hash.replace('#', '')));
            }
        };

    });

})(jQuery);

