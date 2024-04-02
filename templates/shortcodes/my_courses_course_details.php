<?php foreach ( $lessons as $lesson )  :
	$course_title = $course->CourseTitle;
	$lesson_title = '';
	if ( !empty( $lesson->LessonTitle ) && ( $course_title !== $lesson->LessonTitle ) ) {
		$lesson_title = " : <em class='cs-course-details-lesson-title-module'>{$lesson->LessonTitle}</em>";
	}
	?>
	<h3 class="cs-course-details-lesson-title"><?= $course_title . $lesson_title ?></h3>
	<table class="csTable openEnrolmentTable cs-course-details-lesson-table">
		<thead>
		<tr class="cs-course-details-lesson-row">
			<th class="cs-course-details-lesson-td cs-course-details-lesson-td__module"><?= __( 'Module', 'coursesource' ) ?></th>
			<th class="cs-course-details-lesson-td cs-course-details-lesson-td__access"><?= __( 'Last Access', 'coursesource' ) ?></th>
			<th class="cs-course-details-lesson-td cs-course-details-lesson-td__score"><?= __( 'Highest Score', 'coursesource' ) ?></th>
			<th class="cs-course-details-lesson-td cs-course-details-lesson-td__status"><?= __( 'Completion Status', 'coursesource' ) ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $lesson->LessonTutorials as $tutorial )  :
			$url = empty( $tutorial->HighBandwidthURL ) ? $tutorial->LowBandwidthURL : $tutorial->HighBandwidthURL;
			?>
			<tr class="csTableRow cs-course-details-lesson-row">
				<td class="cs-course-details-lesson-td cs-course-details-lesson-td__module"><a data-lesson-url="<?= esc_attr( $url ) ?>" class="tutorialOpen"><?= $tutorial->TutorialTitle ?></a></td>
				<td class="cs-course-details-lesson-td cs-course-details-lesson-td__access"><?= ( $tutorial->TimesAccessed > 0 ) ? $tutorial->LastAccessed : 'N/A' ?></td>
				<td class="cs-course-details-lesson-td cs-course-details-lesson-td__score"><?= ($tutorial->HighestScore > 0 ) ? $tutorial->HighestScore.'%' : 'N/A' ?></td>
				<td class="cs-course-details-lesson-td cs-course-details-lesson-td__status"><?= ( $tutorial->CompleteStatus ) ? __( 'Complete', 'coursesource' ) : __( 'Incomplete', 'coursesource' ) ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endforeach;
