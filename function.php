<?php
function add_recaptcha_v3() {
    echo '<script src="https://www.google.com/recaptcha/api.js?render=YOUR_SITE_KEY"></script>';
}
add_action('wp_head', 'add_recaptcha_v3');

function add_recaptcha_v3_to_registration_form() {
    ?>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('YOUR_SITE_KEY', {action: 'register'}).then(function(token) {
                var recaptchaResponse = document.getElementById('g-recaptcha-response');
                recaptchaResponse.value = token;
            });
        });
    </script>
    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
    <?php
}
add_action('woocommerce_register_form', 'add_recaptcha_v3_to_registration_form');

function verify_recaptcha_v3($username, $email, $validation_errors) {
    if (isset($_POST['g-recaptcha-response'])) {
        $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);
        
        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            array(
                'body' => array(
                    'secret' => 'YOUR_SECRET_KEY',
                    'response' => $recaptcha_response,
                ),
            )
        );

        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body);

        if (!$result->success || $result->score < 0.5) {
            $validation_errors->add('recaptcha_error', __('reCAPTCHA verification failed, please try again.', 'woocommerce'));
        }
    } else {
        $validation_errors->add('recaptcha_error', __('reCAPTCHA verification failed, please try again.', 'woocommerce'));
    }
}
add_action('woocommerce_register_post', 'verify_recaptcha_v3', 10, 3);
