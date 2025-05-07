<h3>Login details</h3>
<?php if ( $cart_content == 'all' )  : ?>
    <p>
        The login details to the course/s you have purchased will follow in a separate email in the next 30 minutes.
        If you do not receive the email please check your spam/junk folders.
        If you are still unable to locate the email please contact
        <a href="mailto:<?= esc_attr( get_option('admin_email') ) ?>"><?= get_option('admin_email') ?></a>.
    </p>
<?php elseif( $cart_content == 'none' ) : ?>
    <p>
        You will receive an email from the seller of this product within one working day with your access details.
        If you do not receive this email please get in contact with us.
        Please quote your order number and product ID in communication with us.
    </p>
<?php elseif( $cart_content == 'mixed' ) : ?>
    <p>
        Your order contains a mix of immediate access and one day access products.
    </p>
    <p>
        <strong>Immediate access products</strong>
    </p>
    <p>The login details to the course/s you have purchased will follow in a separate email in the next 30 minutes.
        If you do not receive the email please check your spam/junk folders.
        If you are still unable to locate the email please contact
        <a href="mailto:<?= esc_attr( get_option('admin_email') ) ?>"><?= get_option('admin_email') ?></a>.
    </p>
    <p>
        <strong>One day access products</strong>
    </p>
    <p>
        You will receive an email from the seller of this product within one working day with your access details.
        If you do not receive this email please get in contact with us.
        Please quote your order number and product ID in communication with us.
    </p>
<?php endif;
