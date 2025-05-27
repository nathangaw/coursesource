<h3>Login details</h3>
<?php if ( $cart_content == 'all' )  : ?>
    <p>
      You will receive an email confirmation of your order, following payment, with a link to our Online Training Academy and an enrolment key for each learner/course in your order. If you have purchased for others, please issue the enrolment keys to your learners. If you encounter any problems, please email <a href="support@elearningmarketplace.co.uk">support@elearningmarketplace.co.uk</a>.
    </p>
<?php elseif( $cart_content == 'none' ) : ?>
    <p>
        You will receive an email confirmation of your order following payment. Then within one working day a further email from the course publisher with details on how to access your training. If you encounter any problems, please email <a href="support@elearningmarketplace.co.uk">support@elearningmarketplace.co.uk</a>
    </p>
<?php elseif( $cart_content == 'mixed' ) : ?>
    <p>
        Your order contains a mix of immediate access and one day access products.
    </p>
    <p>
        <strong>Immediate access products</strong>
    </p>
    <p>
      You will receive an email confirmation of your order, following payment, with a link to our Online Training Academy and an enrolment key for each learner/course in your order. If you have purchased for others, please issue the enrolment keys to your learners. If you encounter any problems, please email <a href="support@elearningmarketplace.co.uk">support@elearningmarketplace.co.uk</a>.
    </p>
    <p>
        <strong>One day access products</strong>
    </p>
    <p>
        You will receive an email confirmation of your order following payment. Then within one working day a further email from the course publisher with details on how to access your training. If you encounter any problems, please email <a href="support@elearningmarketplace.co.uk">support@elearningmarketplace.co.uk</a>
    </p>
<?php endif; ?>
