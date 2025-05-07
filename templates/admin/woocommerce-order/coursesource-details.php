<?php
use Coursesource\Woocommerce\Woo\Order;
?>
<div id="coursesource_order_keys-inner">
<?php if (!$order_completed) : ?>
    <p><?= __("Update the Order Status to Completed to create any {$company_name} Enrolments or Course Keys.", 'coursesource_woocommerce') ?></p>
    <?php if( $data['coursesource_manager'] ) :
        $email = $data['coursesource_manager']->email ?? null;
        $group = $data['coursesource_manager']->group ?? null;
        ?>
    <p><?= sprintf(__("Enrolments will be linked to the WooCommerce account with the email address %s and made in the Group named %s"), "<a href='mailto:{$email}'>{$email}</a>", "<strong>{$group}</strong>"); ?></p>
   <?php endif;?>
<?php elseif (is_array($coursesource_data) || is_array($coursesource_ids) || is_array($coursesource_errors) ) :
    add_thickbox();
    ?>

    <?php if (is_array($coursesource_ids)) : ?>
        <div class="coursesource-order-course-ids">
            <table class="widefat fixed" cellspacing="0">
                <thead>
                <tr>
                    <th class="product-name"><?= __("Course Names", 'coursesource_woocommerce') ?></th>
                    <th class="product-keys"><?= __("Enrolment ID", 'coursesource_woocommerce') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($coursesource_ids as $coursesource_ids) : ?>
                    <tr>
                        <td class="product-reference"><?= $coursesource_ids["name"]; ?></td>
                        <td class="product-reference"><?= $coursesource_ids["enrol_id"] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if (is_array($coursesource_data)) : ?>
        <div class="coursesource-order-course-keys">
            <table class="widefat fixed" cellspacing="0">
                <thead>
                <tr>
                    <th class="product-name"><?= __("Course Names", 'coursesource_woocommerce') ?></th>
                    <th class="product-keys"><?= __("Course Keys", 'coursesource_woocommerce') ?></th>
                    <?php if( $coursesource_manager) : ?>
                        <th class="product-manager"><?= __("Management Details", 'coursesource_woocommerce') ?></th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($coursesource_data as $datum) : ?>
                    <tr>
                        <td class="product-reference"><?= implode("<br/>", $datum["CoursesInKey"]) ?></td>
                        <td class="product-reference"><?= implode("<br/>", $datum["Keys"]) ?></td>
                        <?php if( $coursesource_manager ) : ?>
                            <th class="product-manager">
                                <?php $groupUrl = Order::get_coursesourrce_group_url( $coursesource_manager->group ) ?>
                                <strong><?= __("Group", 'coursesource_woocommerce') ?></strong>: <em><a href="<?= $groupUrl ?>" target="_blank"><?= $coursesource_manager->group ?></a></em><br>
                                <strong><?= __("Manager", 'coursesource_woocommerce') ?></strong>: <em><a href="mailto:<?= esc_attr($coursesource_manager->email) ?>"><?= $coursesource_manager->email ?></a></em>
                            </th>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if (is_array($coursesource_errors)) : ?>
    <div class="coursesource-order-errors">
        <table class="widefat fixed" cellspacing="0">
            <thead>
            <tr>
                <th class="product-name"><?= __("Course Name", 'coursesource_woocommerce') ?></th>
                <th class="product-name"><?= __("Course ID", 'coursesource_woocommerce') ?></th>
                <?php if( $coursesource_manager) : ?>
                    <th class="product-manager"><?= __("Management Details", 'coursesource_woocommerce') ?></th>
                <?php endif; ?>
                <th class="product-keys"><?= __("Error Message", 'coursesource_woocommerce') ?></th>
                <th class="product-action"><?= __("Actions", 'coursesource_woocommerce') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($coursesource_errors as $error) : ?>
                <tr>
                    <td class="product-reference"><?= $error["course"]['product_name'] ?></td>
                    <td class="product-reference"><?= $error["course"]['CourseID'] ?></td>
                    <?php if( $coursesource_manager) : ?>
                        <th class="product-manager">
                            <?php $groupUrl = Order::get_coursesourrce_group_url( $coursesource_manager->group ) ?>
                            <strong><?= __("Group", 'coursesource_woocommerce') ?></strong>: <em><a href="<?= $groupUrl ?>" target="_blank"><?= $coursesource_manager->group ?></a></em><br>
                            <strong><?= __("Manager", 'coursesource_woocommerce') ?></strong>: <em><a href="mailto:<?= esc_attr($coursesource_manager->email) ?>"><?= $coursesource_manager->email ?></a></em>
                        </th>
                    <?php endif; ?>
                    <td class="product-reference"><?= implode(', ', $error["error_message"] ) ?></td>
                    <td class="product-action"><a href="" id="coursesource-licence-generate-retry" class="button coursesource-licence-generate-retry"><?= __('Retry Creating Licence Keys', 'coursesource_woocommerce' ) ?></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php else: ?>
    <p><?= __("No {$company_name} products were successfully sold as part of this order.", 'coursesource_woocommerce') ?></p>
    <a href="" id="coursesource-licence-generate-retry" class="button coursesource-licence-generate-retry"><?= __('Retry Creating Licence Keys', 'coursesource_woocommerce' ) ?></a>
<?php endif; ?>
</div>
