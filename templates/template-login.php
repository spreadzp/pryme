<?php
/**
 * Login page template — standalone HTML, no theme header/footer.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_user_logged_in() ) {
	wp_redirect( home_url( '/app/' ) );
	exit;
}

$login_error = '';

if ( isset( $_POST['prime_login'] ) && check_admin_referer( 'prime_login', 'prime_login_nonce' ) ) {
	$username = sanitize_text_field( wp_unslash( $_POST['log'] ?? '' ) );
	$password = wp_unslash( $_POST['pwd'] ?? '' );
	$remember = ! empty( $_POST['rememberme'] );

	$user = wp_signon(
		array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember,
		),
		false
	);

	if ( is_wp_error( $user ) ) {
		$login_error = $user->get_error_message();
	} else {
		wp_redirect( home_url( '/app/' ) );
		exit;
	}
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'Login — PRIME', 'prime-core' ); ?></title>
	<?php wp_head(); ?>
	<style>
		body {
			background: #f0f2f5;
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 100vh;
			margin: 0;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
		}
		.prime-login-card {
			background: #fff;
			border-radius: 12px;
			box-shadow: 0 2px 16px rgba(0,0,0,0.08);
			padding: 40px;
			max-width: 400px;
			width: 100%;
		}
		.prime-login-logo {
			text-align: center;
			font-size: 28px;
			font-weight: 700;
			color: #1a1a2e;
			margin-bottom: 32px;
			letter-spacing: -0.5px;
		}
		.prime-login-form input[type="text"],
		.prime-login-form input[type="password"],
		.prime-login-form input[type="email"],
		.prime-magic-form input[type="email"] {
			width: 100%;
			padding: 12px 16px;
			border: 1px solid #d9d9d9;
			border-radius: 8px;
			font-size: 15px;
			margin-bottom: 16px;
			box-sizing: border-box;
			transition: border-color 0.2s;
		}
		.prime-login-form input:focus,
		.prime-magic-form input:focus {
			border-color: #4f46e5;
			outline: none;
			box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
		}
		.prime-login-form input[type="submit"] {
			width: 100%;
			padding: 12px;
			background: #4f46e5;
			color: #fff;
			border: none;
			border-radius: 8px;
			font-size: 15px;
			font-weight: 600;
			cursor: pointer;
			transition: background 0.2s;
		}
		.prime-login-form input[type="submit"]:hover {
			background: #4338ca;
		}
		.prime-login-error {
			background: #fee2e2;
			color: #991b1b;
			padding: 12px 16px;
			border-radius: 8px;
			margin-bottom: 16px;
			font-size: 14px;
		}
		.prime-login-divider {
			text-align: center;
			color: #9ca3af;
			font-size: 13px;
			margin: 24px 0;
			position: relative;
		}
		.prime-login-divider::before,
		.prime-login-divider::after {
			content: '';
			position: absolute;
			top: 50%;
			width: 40%;
			height: 1px;
			background: #e5e7eb;
		}
		.prime-login-divider::before { left: 0; }
		.prime-login-divider::after { right: 0; }
		.prime-login-magic-btn {
			width: 100%;
			padding: 12px;
			background: #fff;
			color: #4f46e5;
			border: 1px solid #4f46e5;
			border-radius: 8px;
			font-size: 15px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.2s;
		}
		.prime-login-magic-btn:hover {
			background: #4f46e5;
			color: #fff;
		}
		.prime-login-back {
			text-align: center;
			margin-top: 24px;
		}
		.prime-login-back a {
			color: #6b7280;
			text-decoration: none;
			font-size: 14px;
		}
		.prime-login-back a:hover {
			color: #4f46e5;
		}
		.prime-login-toggle { display: none; }
		.prime-login-toggle:checked ~ .prime-login-form { display: none; }
		.prime-login-toggle:checked ~ .prime-magic-form { display: block; }
		.prime-login-toggle:checked ~ .prime-login-toggle-label { display: none; }
		.prime-login-toggle:not(:checked) ~ .prime-magic-form { display: none; }
		.prime-login-toggle-label {
			text-align: center;
			margin-top: 16px;
		}
		.prime-login-toggle-label label {
			color: #4f46e5;
			font-size: 14px;
			cursor: pointer;
			text-decoration: none;
		}
	</style>
</head>
<body>
	<div class="prime-login-card">
		<div class="prime-login-logo">PRIME</div>

		<?php if ( $login_error ) : ?>
			<div class="prime-login-error"><?php echo esc_html( $login_error ); ?></div>
		<?php endif; ?>

		<input type="checkbox" id="prime-toggle" class="prime-login-toggle">

		<form class="prime-login-form" method="post" action="">
			<?php wp_nonce_field( 'prime_login', 'prime_login_nonce' ); ?>
			<input type="text" name="log" placeholder="<?php esc_attr_e( 'Email or Username', 'prime-core' ); ?>" required>
			<input type="password" name="pwd" placeholder="<?php esc_attr_e( 'Password', 'prime-core' ); ?>" required>
			<label style="display:flex; align-items:center; gap:8px; margin-bottom:16px; font-size:14px; color:#6b7280;">
				<input type="checkbox" name="rememberme" value="1"> <?php esc_html_e( 'Remember me', 'prime-core' ); ?>
			</label>
			<input type="submit" name="prime_login" value="<?php esc_attr_e( 'Login', 'prime-core' ); ?>">
		</form>

		<form class="prime-magic-form" method="post" action="">
			<?php wp_nonce_field( 'prime_magic_link', 'prime_magic_nonce' ); ?>
			<input type="email" name="magic_email" placeholder="<?php esc_attr_e( 'Client email', 'prime-core' ); ?>" required>
			<input type="hidden" name="action" value="prime_send_magic_link">
			<input type="submit" name="prime_magic_link" value="<?php esc_attr_e( 'Send Magic Link', 'prime-core' ); ?>" class="prime-login-magic-btn">
		</form>

		<div class="prime-login-toggle-label">
			<label for="prime-toggle"><?php esc_html_e( 'Client? Use Magic Link →', 'prime-core' ); ?></label>
		</div>

		<div class="prime-login-back">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">← <?php esc_html_e( 'Back to home', 'prime-core' ); ?></a>
		</div>
	</div>
	<?php wp_footer(); ?>
	<?php if ( ! is_user_logged_in() ) : ?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
	    var magicForm = document.querySelector('.prime-magic-form');
	    if ( ! magicForm ) return;

	    magicForm.addEventListener('submit', function(e) {
	        e.preventDefault();
	        var formData = new FormData(magicForm);
	        var submitBtn = magicForm.querySelector('input[type="submit"]');
	        var originalValue = submitBtn.value;

	        submitBtn.value = 'Sending...';
	        submitBtn.disabled = true;

	        fetch('<?php echo esc_url( admin_url( "admin-ajax.php" ) ); ?>', {
	            method: 'POST',
	            body: formData
	        })
	        .then(function(res) { return res.json(); })
	        .then(function(data) {
	            var errorDiv = document.querySelector('.prime-login-error');
	            if ( ! errorDiv ) {
	                errorDiv = document.createElement('div');
	                errorDiv.className = 'prime-login-error';
	                magicForm.parentNode.insertBefore(errorDiv, magicForm);
	            }
	            if (data.success) {
	                errorDiv.style.background = '#d1fae5';
	                errorDiv.style.color = '#065f46';
	                errorDiv.textContent = data.data.message;
	            } else {
	                errorDiv.style.background = '#fee2e2';
	                errorDiv.style.color = '#991b1b';
	                errorDiv.textContent = data.data.message || 'Error sending link.';
	            }
	        })
	        .catch(function() {
	            alert('Network error. Please try again.');
	        })
	        .finally(function() {
	            submitBtn.value = originalValue;
	            submitBtn.disabled = false;
	        });
	    });
	});
	</script>
	<?php endif; ?>
</body>
</html>
