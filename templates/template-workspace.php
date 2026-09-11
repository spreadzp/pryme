<?php
/**
 * Workspace template stub.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user    = wp_get_current_user();
$section = get_query_var( 'prime_section' ) ?: 'dashboard';

get_header();
?>
<div class="prime-app-layout">
	<aside class="prime-sidebar">
		<div class="prime-sidebar-header">
			<a href="<?php echo esc_url( home_url( '/app/workspace/' ) ); ?>">PRIME Workspace</a>
		</div>
		<nav class="prime-sidebar-nav">
			<a href="<?php echo esc_url( home_url( '/app/workspace/' ) ); ?>"
			   class="<?php echo 'dashboard' === $section ? 'active' : ''; ?>">
				<?php esc_html_e( 'Dashboard', 'prime-core' ); ?>
			</a>
			<a href="<?php echo esc_url( home_url( '/app/workspace/tasks/' ) ); ?>"
			   class="<?php echo 'tasks' === $section ? 'active' : ''; ?>">
				<?php esc_html_e( 'Tasks', 'prime-core' ); ?>
			</a>
			<a href="<?php echo esc_url( home_url( '/app/workspace/clients/' ) ); ?>"
			   class="<?php echo 'clients' === $section ? 'active' : ''; ?>">
				<?php esc_html_e( 'Clients', 'prime-core' ); ?>
			</a>
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
				echo '<h1>' . esc_html__( 'Workspace Dashboard', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Dashboard widgets — filled in EPIC-4.', 'prime-core' ) . '</p>';
				break;

			case 'tasks':
				echo '<h1>' . esc_html__( 'Tasks', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'FluentBoards integration — filled in EPIC-4.', 'prime-core' ) . '</p>';
				break;

			case 'clients':
				echo '<h1>' . esc_html__( 'Assigned Clients', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Client list — filled in EPIC-4.', 'prime-core' ) . '</p>';
				break;

			default:
				echo '<h1>' . esc_html__( 'Workspace Dashboard', 'prime-core' ) . '</h1>';
		}
		?>
	</main>
</div>
<?php
get_footer();
