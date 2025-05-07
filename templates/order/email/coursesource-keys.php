<?php

if ( !is_null( $coursesource_data ) ) : ?>
	<h2><?= $company_name ?></h2>
	<div style="margin-bottom: 40px;">
		<table class="td" cellspacing="0" cellpadding="6"
					 style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
			<thead>
			<tr>
				<th class="td" scope="col"
						style="text-align: left"><?php esc_html_e( 'Courses', 'woocommerce' ); ?></th>
				<th class="td" scope="col"
						style="text-align: left"><?php esc_html_e( 'Enrolment Keys', 'woocommerce' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $coursesource_data as $datum) : ?>
				<tr class="order_item">
					<td class="td "
							style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
						<?php echo implode("<br/>", $datum["CoursesInKey"] ); ?>
					</td>
					<td class="td"
							style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
						<?php echo implode("<br/>", $datum["Keys"] ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

    <?php if ( !empty( $portal_url ) ) : ?>
        <div style="margin-bottom: 40px;">
            <p><?= __('You can access your purchased courses via our online learning portal.', 'coursesource') ?><br><br>
                <a href="<?= esc_attr( $portal_url) ?>"><?= $portal_url ?></a>
            </p>
        </div>
    <?php endif; ?>

<?php endif; ?>
