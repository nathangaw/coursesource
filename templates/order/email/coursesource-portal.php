<?php if ( !empty( $portal_url ) ) : ?>
    <div style="margin-bottom: 40px;">
        <p><?= __('You can access your purchased courses via our Online Learning Portal.', 'coursesource') ?><br><br>
            <a href="<?= esc_attr( $portal_url) ?>"><?= $portal_url ?></a>
        </p>
    </div>
<?php endif; ?>
