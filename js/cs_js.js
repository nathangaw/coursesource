// Admin js
var buttonContainer;

jQuery(document).on('ready', function() {
    jQuery('.import_course').on('click', function(e) {
        e.preventDefault();
        
        buttonContainer = jQuery(this).closest('td');
        jQuery(this).hide();
        buttonContainer.append('<span>Loading, please wait...</span>');

        var course_id = [jQuery(this).attr('data-course_id')];
        jQuery.ajax({
            url: '',
            method: 'POST',
            data: { coursesource_action: 'import_course', import_id: course_id, DOING_AJAX:true },
        }).done( function(rtn) {
            if ( rtn.success ) {
                buttonContainer.find('span').text('Import successful, reloading page');
                window.location.reload();
            } else {
                if ( rtn.error ) { alert(rtn.error); } 
                else { alert('import failed!'); }

                buttonContainer.find('span').remove();
                buttonContainer.find('.import_course').show();
            }
        });
    });
    
    jQuery('.sync_selected').on('click', function(e) {
        e.preventDefault();
        
        var courses = [];
        jQuery('input[name="courses[]"]:checked').each(function() {
                courses.push(jQuery(this).parent().parent().find('.action_import').attr('data-course_id'));
        });
        jQuery.ajax({
            url: '',
            method: 'POST',
            data: { coursesource_action: 'import_course', import_id: courses, DOING_AJAX:true },
        }).done( function(rtn) {
            if ( rtn.success ) {
                alert('import successful!');
                location.reload();
            } else {
                if ( rtn.error ) { alert(rtn.error); } 
                else { alert('import failed!'); }
            }
        });
    });
    
    jQuery('.import_entire_library').on('click', function(e) {
        e.preventDefault();
        jQuery('.import_entire_library_form').submit();
    });
    
    jQuery('#select_vendor').on('change', function() {
        window.location.href = jQuery(this).val();
    });
    
    
    jQuery('.sync_entire_library').on('click', function(e) {
        e.preventDefault();
       if ( !confirm('Are you sure? This will take some time, and overwrite any products that have been changed, and disable all products beginning with the CourseSource SKU prefix that aren\'t found in the library')) {
           return false;
       } else {
           $('.sync_entire_library').next('.sync_status').html('<img src="spinner.gif" /> Loading courses into memory.');
           var entire_library = load_library();
       }
    });
});