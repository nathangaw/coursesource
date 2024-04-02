<?php

if( is_array($coursesource_data) && $order_completed ) :
	?>
	<div class="coursesource-order-course-keys" style="margin-bottom: 24px">
		<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
			<thead>
			<tr>
				<th class="woocommerce-table__course-name course-name"><?php echo __("Course Names", 'coursesource_woocommerce'); ?></th>
				<th class="woocommerce-table__course-keys course-keys"><?php echo __("Course Keys", 'coursesource_woocommerce'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $coursesource_data as $datum ) : ?>
				<tr class="woocommerce-table__course-key_item course-key_item">
					<td class="woocommerce-table__course-name course-name"><?php echo implode("<br/>", $datum["CoursesInKey"] ); ?></td>
					<td class="woocommerce-table__course-keys course-keys"><?php echo implode("<br/>", $datum["Keys"] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
