<?php ?>
<div class="cs-courses-table-wrapper">
    <table id="myCoursesTable" class="csTable myCoursesTable cs-courses-table" data-nonce="<?= wp_create_nonce('course_table_details') ?>">
        <thead>
        <tr class="cs-courses-table-row">
            <th class="cs-courses-table-td cs-courses-table-td-title"><?= __( 'Title', 'coursesource' ) ?></th>
            <th class="cs-courses-table-td cs-courses-table-td-publisher"><?= __( 'Publisher', 'coursesource' ) ?></th>
            <th class="cs-courses-table-td cs-courses-table-td-expires" ><?= __( 'Expires', 'coursesource' ) ?></th>
            <th class="cs-courses-table-td cs-courses-table-td-access"><?= __( 'Last Access', 'coursesource' ) ?></th>
            <th class="cs-courses-table-td cs-courses-table-td-status"><?= __( 'Status', 'coursesource' ) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ( $courses as $course ) :
            $CourseInfo = $api->getCourseInfo($course->CourseID);
            $VendorID = $CourseInfo->VendorID;
            $VendorName = $vendors[$VendorID];
            ?>
            <tr class="enrolmentRow cs-courses-table-row">
                <td class="cs-courses-table-td cs-courses-table-td-title"><a data-enrolment_id="<?= esc_attr( $course->EnrollID ) ?>" class="openEnrolment"><?= $course->CourseTitle ?></a></td>
                <td class="cs-courses-table-td cs-courses-table-td-publisher"><?= $VendorName ?></td>
                <td class="cs-courses-table-td cs-courses-table-td-expires"><?= $course->EndDate ?></td>
                <td class="cs-courses-table-td cs-courses-table-td-access"><?= $course->LastAccessedDate ?></td>
                <td class="cs-courses-table-td cs-courses-table-td-status"><?= ( $course->CompleteStatus ) ? __('Complete', 'coursesource' ) : __('Incomplete', 'coursesource' ) ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
<div id="myCourseDataContainer" class="cs-course-details-lessons-container"></div>
