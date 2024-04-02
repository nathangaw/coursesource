;(function ($) {

    var ajaxNonce = CoursesourceListTable.import_courses_nonce;
    var thickboxID = CoursesourceListTable.thickbox_id;

    var stopCourseImporting = false;

    //Load Course details in a thickbox on Course List Table
    var thickboxLoader = function( text ) {
        return $('<div style="padding: 3rem; text-align: center;"><p>' + text + '</p><p style="margin-top: 2rem;text-align: center"><img src="/wp-includes/images/spinner-2x.gif" alt="" /></p></div>');
    }

    var createProductEditButton = function (importBtn, productId) {
        var productEditUrl = "/wp-admin/post.php?post=" + productId + "&amp;action=edit";
        var courseId = importBtn.attr('data-course_id');
        var editButton = $("<a href='" + productEditUrl + "'class='action_view view_course button' data-course_id='" + courseId + "'>Edit Product</a>");
        return editButton;
    }

    var importCourseLibrary = function () {
        $.ajax({
            url   : CoursesourceListTable.ajaxurl,
            method: 'POST',
            data  : {
                action            : 'get_course_library',
                get_course_library: 1,
                nonce             : ajaxNonce,
            },
        }).done(function (result) {
            var thickBoxContent = "";

            if (result.courses) {
                var courses = result.courses;
                var course_id = null;
                var coursesUpdated = [];
                var coursesFailed = [];
                var courseImportData = {
                    'processed' : 0,
                    'courses' : courses,
                    'coursesUpdated' : coursesUpdated,
                    'coursesFailed' : coursesFailed
                }
                thickBoxContent = thickboxLoader( 'Found ' + courses.length + ' Courses');
                $("#TB_ajaxContent").html(thickBoxContent);
                //Recursively load the next courses...
                loadProductFromLibrary( courseImportData );

            } else {
                thickBoxContent = $('<div><p>Error loading the Course Library</p></div>');
                $("#TB_ajaxContent").html(thickBoxContent);
                console.log('Reason for failure...');
            }
        });
    }

    var loadProductFromLibrary = function ( import_data ) {
        if( stopCourseImporting === true ){
            return;
        }
        // Get the next course to process
        var position = import_data.processed;
        course_id = import_data.courses[position].CourseID;
        var course_ids = [ course_id ];

        $.ajax({
            url   : CoursesourceListTable.ajaxurl,
            method: 'POST',
            data  : {
                action   : 'cs_course_import',
                course_id: course_ids,
                nonce    : ajaxNonce,
            },
        }).done(function (result) {
            import_data.processed++;
            if (result.success) {
                import_data.coursesUpdated.push( course_id );
            } else {
                import_data.coursesFailed.push( course_id );
            }
            thickBoxContent = thickboxLoader( 'Processed ' + import_data.processed + ' of ' + import_data.courses.length + ' Courses.<br/>Completed: ' + import_data.coursesUpdated.length + ', Failed: ' + import_data.coursesFailed.length );
            $("#TB_ajaxContent").html( thickBoxContent );

            // Close the thickbox and reload the page...
            if( import_data.processed == import_data.courses.length ){
                $('body').trigger('coursesource-thickbox-close');
            }

            //Now call recursively...
            loadProductFromLibrary( import_data );
        });
    }

    $(document).ready(function () {

        // Filter the Courses by Vendor
        $('#select_vendor').on('change', function () {
            $('#search-submit').trigger('click');
        });

        /**
         * Show individual course details
         */
        $('.coursetitle').click(function (e) {
            e.preventDefault();

            var self = $(this);
            var course_id = self.attr('data-course-id');
            var course_title = self.text();
            $("#" + thickboxID).html(thickboxLoader( 'Processing..' ));

            tb_show(
                'Course Details: ' + course_title,
                '#TB_inline?&width=600&height=550&inlineId=' + thickboxID
            );

            $.ajax({
                url   : CoursesourceListTable.ajaxurl,
                method: 'POST',
                data  : {
                    action   : 'cs_course_details',
                    course_id: course_id,
                    nonce    : ajaxNonce,
                },
            }).done(function (result) {
                var course = result.course;
                var thickBoxContent = "";
                if (result.course) {
                    thickBoxContent = $('<table class="csTable horizontal">\
                        <tr><th>Title</th><td>' + course.CourseInfo.Course_Title + '</td></tr>\
                        ' + (course.CourseInfo.Hours_of_Training != undefined ? '<tr><th>Length</th><td>' + course.CourseInfo.Hours_of_Training + '</td></tr>' : '') + '\
                        <tr><th>Price</th><td>' + course.BuyPriceCurrency + '' + course.BuyPrice + '</td></tr>\
                        <tr><td colspan="2">' + course.Outline.Introduction + '</td></tr>\
                    </table>');
                } else {
                    thickBoxContent = $('<div><p>Error loading the Course content</p></div>');
                    console.log('Reason for failure...');
                }
                $("#TB_ajaxContent").html(thickBoxContent);
            });

        });


        /**
         * Import a single course
         */
        $('.import_course').on('click', function (e) {
            e.preventDefault();
            var self = $(this);
            var original_btn_label = self.text();
            var course_id = self.attr('data-course_id');
            var course_imported = parseInt(self.attr('data-course-imported'));

            if (!self.attr('data-loading')) {
                self.text('Saving, please wait...');
                self.attr('data-loading', true);
                var course_ids = [course_id];
                $.ajax({
                    url   : CoursesourceListTable.ajaxurl,
                    method: 'POST',
                    data  : {
                        action   : 'cs_course_import',
                        course_id: course_ids,
                        nonce    : ajaxNonce,
                    },
                }).done(function (result) {
                    if (result.success) {
                        self.text(original_btn_label);
                        self.removeAttr('data-loading');
                        if (course_imported !== 1) {
                            var editButton = createProductEditButton(self, result.success);
                            self.before(editButton);
                        }
                    } else {
                        if (result.error) {
                            alert(result.error);
                        } else {
                            alert('Import failed! Please try again in a moment');
                        }
                    }
                    $(self).text('Re-Import');
                    $(this).removeAttr('data-loading');
                });
            }
        });

        /**
         * Import multiple selected Courses
         */
        $('#cs-courses-table-sync__selected').on('click', function (e) {
            e.preventDefault();
            var self = $(this);
            self.text('Processing...');

            $("#" + thickboxID).html(thickboxLoader('Importing Courses...') );

            tb_show(
                'Importing Courses...',
                '#TB_inline?&width=600&height=340&inlineId=' + thickboxID
            );

            var original_btn_label = self.text();
            var course_ids = [];
            $('input[name="courses[]"]:checked').each(function () {
                var course_id = $(this).val();
                course_ids.push(course_id);
            });
            $.ajax({
                url   : CoursesourceListTable.ajaxurl,
                method: 'POST',
                data  : {
                    action   : 'cs_course_import',
                    course_id: course_ids,
                    nonce    : ajaxNonce,
                },
            }).done(function (result) {
                if (result.success) {
                    self.text(original_btn_label);
                    location.reload();
                } else {
                    if (result.error) {
                        alert(result.error);
                    } else {
                        alert('Import failed! Please try again in a moment');
                    }
                }
            });
        });

        /**
         * Sync all Courses data...
         */
        $('#cs-courses-table-sync__entire_library').on('click', function (e) {
            e.preventDefault();
            if (!confirm('Are you sure? This will take some time, and overwrite any products that have been changed, and disable all products beginning with the CourseSource SKU prefix that aren\'t found in the library')) {
                return false;
            } else {
                $('#cs-courses-table-sync-spinner').html('Loading courses into memory... <img src="/wp-admin/images/spinner.gif" />');
                $("#" + thickboxID).html( thickboxLoader('Loading all Courses. Please be patient...') );
                tb_show(
                    'Importing all Courses...',
                    '#TB_inline?&width=400&height=260&inlineId=' + thickboxID
                );
                importCourseLibrary();
            }
        });

        /**
         *
         */
        $( 'body.product_page_course-source-import' ).on( 'thickbox:removed', function() {
            stopCourseImporting = true;
            alert('Stopping importing Courses');
            location.reload();
        });

        /**
         *
         */
        $('body.product_page_course-source-import').on('coursesource-thickbox-close', function(){
            alert('All Courses imported');
            location.reload();
        });


    });

})(jQuery);
