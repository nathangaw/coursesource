<?php

if (!$order_completed) : ?>
    <?= __("Update the Order Status to Completed to create any {$company_name} Enrolments or Course Keys.", 'coursesource_woocommerce'); ?>
<?php elseif (is_array($coursesource_data) || is_array($coursesource_ids) || is_array($coursesource_errors) ) :
    add_thickbox();
    ?>

    <?php if (is_array($coursesource_ids)) : ?>
        <div class="coursesource-order-course-ids">
            <table class="widefat fixed" cellspacing="0">
                <thead>
                <tr>
                    <th class="product-name"><?= __("Course Names", 'coursesource_woocommerce'); ?></th>
                    <th class="product-keys"><?= __("Enrolment ID", 'coursesource_woocommerce'); ?></th>
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
                    <th class="product-name"><?= __("Course Names", 'coursesource_woocommerce'); ?></th>
                    <th class="product-keys"><?= __("Course Keys", 'coursesource_woocommerce'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($coursesource_data as $datum) : ?>
                    <tr>
                        <td class="product-reference"><?= implode("<br/>", $datum["CoursesInKey"]); ?></td>
                        <td class="product-reference"><?= implode("<br/>", $datum["Keys"]); ?></td>
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
                <th class="product-name"><?= __("Course Name", 'coursesource_woocommerce'); ?></th>
                <th class="product-name"><?= __("Course ID", 'coursesource_woocommerce'); ?></th>
                <th class="product-keys"><?= __("Error Message", 'coursesource_woocommerce'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($coursesource_errors as $error) : ?>
                <tr>
                    <td class="product-reference"><?= $error["course"]['product_name'] ?></td>
                    <td class="product-reference"><?= $error["course"]['CourseID'] ?></td>
                    <td class="product-reference"><?= $error["error_message"] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php else: ?>
    <p><?= __("No {$company_name} products were successfully sold as part of this order.", 'coursesource_woocommerce'); ?></p>
<?php endif; ?>
