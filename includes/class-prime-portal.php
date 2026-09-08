<?php
/**
 * Portal shortcode and shell rendering.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Portal shortcode and shell rendering.
 *
 * @package prime-core
 */
class Prime_Portal {

	/**
	 * Singleton instance.
	 *
	 * @var Prime_Portal|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Prime_Portal
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — register shortcode.
	 */
	private function __construct() {
		add_shortcode( 'prime_portal', array( $this, 'portal_shell' ) );
	}

	/**
	 * Render the portal shell with welcome cards.
	 *
	 * @return string HTML output.
	 */
	public function portal_shell() {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user  = wp_get_current_user();
		$first = $user->first_name ? $user->first_name : $user->display_name;
		$staff = Prime_Roles::instance()->is_staff( $user );
		$role  = $staff ? ( user_can( $user, 'manage_options' ) ? 'Administrator' : 'Employee' ) : 'Client';

		$cards = $staff
			? array(
				array( 'Clients', 'Every client account, their projects and their files. Arrives with the admin panel.' ),
				array( 'Tasks', 'The team board — what is in progress, what is waiting on the client.' ),
				array( 'Enquiries', 'New enquiries from the website, ready to turn into clients.' ),
			)
			: array(
				array( 'Your project', 'Progress, what we are working on now and what is next.' ),
				array( 'Tasks', 'What is done, what is in progress, and anything waiting on you.' ),
				array( 'Files', 'Deliverables, drafts and anything you send us.' ),
			);

		ob_start();
		?>
		<div class="prime-portal">
			<p class="prime-portal__eyebrow"><?php echo esc_html( $role ); ?></p>
			<h1 class="prime-portal__title">Welcome back, <?php echo esc_html( $first ); ?>.</h1>
			<p class="prime-portal__lead">This is your workspace. The modules below are being built now — you will see them fill in over the next few weeks.</p>
			<div class="prime-portal__grid">
				<?php foreach ( $cards as $card ) : ?>
					<div class="prime-card">
						<h2><?php echo esc_html( $card[0] ); ?></h2>
						<p><?php echo esc_html( $card[1] ); ?></p>
						<span class="prime-card__soon">Coming soon</span>
					</div>
				<?php endforeach; ?>
			</div>
			<p class="prime-portal__foot">
				Signed in as <?php echo esc_html( $user->user_email ); ?> ·
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
			</p>
		</div>
		<style>
		.prime-portal{max-width:1100px;margin:0 auto;padding:3rem 1.5rem 4rem}
		.prime-portal__eyebrow{letter-spacing:.14em;text-transform:uppercase;font-size:.75rem;font-weight:700;color:#f26522;margin:0 0 .5rem}
		.prime-portal__title{font-size:clamp(2rem,4vw,3rem);line-height:1.1;margin:0 0 .75rem;color:#0d1b2a}
		.prime-portal__lead{font-size:1.05rem;color:#48566a;max-width:46rem;margin:0 0 2.5rem}
		.prime-portal__grid{display:grid;gap:1.25rem;grid-template-columns:repeat(auto-fit,minmax(255px,1fr))}
		.prime-card{border:1px solid #e2e8f0;border-radius:14px;padding:1.5rem;background:#fff}
		.prime-card h2{font-size:1.15rem;margin:0 0 .5rem;color:#0d1b2a}
		.prime-card p{font-size:.95rem;color:#5a6779;margin:0 0 1rem}
		.prime-card__soon{display:inline-block;font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#f26522;background:#fdf0e8;border-radius:999px;padding:.3rem .7rem}
		.prime-portal__foot{margin-top:2.5rem;font-size:.85rem;color:#78849a}
		</style>
		<?php
		return ob_get_clean();
	}
}
