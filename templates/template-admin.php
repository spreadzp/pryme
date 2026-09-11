<?php
/**
 * Admin panel template stub.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user        = wp_get_current_user();
$section     = get_query_var( 'prime_section' ) ?: 'dashboard';
$client_slug = get_query_var( 'prime_client_slug' );

get_header();
?>
<div class="prime-app-layout">
	<aside class="prime-sidebar">
		<div class="prime-sidebar-header">
			<a href="<?php echo esc_url( home_url( '/app/admin/' ) ); ?>">PRIME Admin</a>
		</div>
		<nav class="prime-sidebar-nav">
			<a href="<?php echo esc_url( home_url( '/app/admin/' ) ); ?>"
			   class="<?php echo 'dashboard' === $section ? 'active' : ''; ?>">
				<?php esc_html_e( 'Dashboard', 'prime-core' ); ?>
			</a>
			<a href="<?php echo esc_url( home_url( '/app/admin/clients/' ) ); ?>"
			   class="<?php echo 'clients' === $section ? 'active' : ''; ?>">
				<?php esc_html_e( 'Clients', 'prime-core' ); ?>
			</a>
			<a href="<?php echo esc_url( home_url( '/app/admin/employees/' ) ); ?>"
			   class="<?php echo 'employees' === $section ? 'active' : ''; ?>">
				<?php esc_html_e( 'Employees', 'prime-core' ); ?>
			</a>
			<?php if ( user_can( $user, 'manage_options' ) ) : ?>
			<a href="<?php echo esc_url( home_url( '/app/admin/billing/' ) ); ?>"
			   class="<?php echo 'billing' === $section ? 'active' : ''; ?>">
				<?php esc_html_e( 'Billing', 'prime-core' ); ?>
			</a>
			<?php endif; ?>
		</nav>
		<div class="prime-sidebar-footer">
			<span><?php echo esc_html( $user->display_name ); ?></span>
			<a href="<?php echo esc_url( wp_logout_url( home_url( '/login/' ) ) ); ?>">
				<?php esc_html_e( 'Logout', 'prime-core' ); ?>
			</a>
		</div>
	</aside>

	<main class="prime-content">
		<?php
		switch ( $section ) {
			case 'dashboard':
				echo '<h1>' . esc_html__( 'Admin Dashboard', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Dashboard widgets — filled in EPIC-3.', 'prime-core' ) . '</p>';
				break;

			case 'clients':
				echo '<h1>' . esc_html__( 'Clients', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Client list — filled in EPIC-3.', 'prime-core' ) . '</p>';
				break;

			case 'client_card':
				$client = get_page_by_path( $client_slug, OBJECT, 'client' );
				if ( $client ) {
					echo '<h1>' . esc_html( $client->post_title ) . '</h1>';
					echo '<p>' . esc_html__( 'Client card — filled in EPIC-3.', 'prime-core' ) . '</p>';
					echo '<p>Client ID: ' . esc_html( $client->ID ) . '</p>';
					$status = get_field( 'client_status', $client->ID );
					echo '<p>Status: ' . esc_html( $status ?: '—' ) . '</p>';
				} else {
					echo '<p>' . esc_html__( 'Client not found.', 'prime-core' ) . '</p>';
				}
				break;

			case 'employees':
				echo '<h1>' . esc_html__( 'Employees', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Employee list — filled in EPIC-3.', 'prime-core' ) . '</p>';
				break;

			case 'billing':
				echo '<h1>' . esc_html__( 'Billing', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Billing — filled in EPIC-3.', 'prime-core' ) . '</p>';
				break;

			default:
				echo '<h1>' . esc_html__( 'Admin Dashboard', 'prime-core' ) . '</h1>';
		}
		?>
	</main>
</div>
<?php
get_footer();
