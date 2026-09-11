<?php
/**
 * Client portal template stub.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user        = wp_get_current_user();
$client_slug = get_query_var( 'prime_client_slug' );
$section     = get_query_var( 'prime_section' ) ?: 'dashboard';
$client_id   = prime_get_client_id_by_slug( $client_slug );

if ( ! $client_id ) {
	get_header();
	echo '<h1>' . esc_html__( 'Client not found', 'prime-core' ) . '</h1>';
	get_footer();
	return;
}

$client_title = get_the_title( $client_id );

$base_sections = array(
	'dashboard' => __( 'Dashboard', 'prime-core' ),
	'tasks'     => __( 'Tasks', 'prime-core' ),
	'files'     => __( 'Files', 'prime-core' ),
	'access'    => __( 'Access', 'prime-core' ),
	'team'      => __( 'Team', 'prime-core' ),
);

$optional_modules = array(
	'chat'           => __( 'Chat', 'prime-core' ),
	'gantt'          => __( 'Gantt', 'prime-core' ),
	'invoices'       => __( 'Invoices', 'prime-core' ),
	'knowledge_base' => __( 'Knowledge Base', 'prime-core' ),
	'time_tracking'  => __( 'Time Tracking', 'prime-core' ),
	'reports'        => __( 'Reports', 'prime-core' ),
);

get_header();
?>
<div class="prime-app-layout">
	<aside class="prime-sidebar">
		<div class="prime-sidebar-header">
			<a href="<?php echo esc_url( home_url( "/app/client/{$client_slug}/" ) ); ?>">
				<?php echo esc_html( $client_title ); ?>
			</a>
		</div>
		<nav class="prime-sidebar-nav">
			<?php
			foreach ( $base_sections as $key => $label ) :
				$url_suffix = 'dashboard' === $key ? '' : $key . '/';
			?>
				<a href="<?php echo esc_url( home_url( "/app/client/{$client_slug}/{$url_suffix}" ) ); ?>"
				   class="<?php echo $section === $key ? 'active' : ''; ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>

			<?php
			$enabled_modules = prime_get_client_modules( $client_id );
			$enabled_keys    = wp_list_pluck( $enabled_modules, 'module_key' );
			foreach ( $optional_modules as $key => $label ) :
				if ( in_array( $key, $enabled_keys, true ) ) :
			?>
				<a href="<?php echo esc_url( home_url( "/app/client/{$client_slug}/{$key}/" ) ); ?>"
				   class="<?php echo $section === $key ? 'active' : ''; ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php
				endif;
			endforeach;
			?>
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
				echo '<h1>' . esc_html( $client_title ) . '</h1>';
				echo '<p>' . esc_html__( 'Client dashboard widgets — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			case 'tasks':
				echo '<h1>' . esc_html__( 'Taskboard', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Kanban board — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			case 'files':
				echo '<h1>' . esc_html__( 'Files', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'File manager — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			case 'access':
				echo '<h1>' . esc_html__( 'Access & Keys', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Access references — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			case 'team':
				echo '<h1>' . esc_html__( 'Team', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Team members — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			case 'chat':
				echo '<h1>' . esc_html__( 'Chat', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Chat module — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			case 'gantt':
				echo '<h1>' . esc_html__( 'Gantt Chart', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Gantt module — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			case 'invoices':
				echo '<h1>' . esc_html__( 'Invoices', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Invoices module — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			case 'knowledge_base':
				echo '<h1>' . esc_html__( 'Knowledge Base', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'KB module — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			case 'time_tracking':
				echo '<h1>' . esc_html__( 'Time Tracking', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Time tracking module — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			case 'reports':
				echo '<h1>' . esc_html__( 'Reports', 'prime-core' ) . '</h1>';
				echo '<p>' . esc_html__( 'Reports module — filled in EPIC-5.', 'prime-core' ) . '</p>';
				break;

			default:
				echo '<h1>' . esc_html( $client_title ) . '</h1>';
		}
		?>
	</main>
</div>
<?php
get_footer();
