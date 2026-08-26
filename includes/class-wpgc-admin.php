<?php
/**
 * The settings screen.
 *
 * @package WordPressGroupChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the form.
 *
 * Deliberately one screen with one save button. The whole point of the plugin is
 * that somebody who would not paste a script tag into their theme can still put
 * their Crowd on their site, so the form asks for one required thing and treats
 * everything else as optional.
 */
class WPGC_Admin {

	const PAGE_SLUG = 'wordpress-group-chat';

	/**
	 * Hook up the menu entry and its assets.
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Under Settings rather than a top-level menu: this is one screen of
	 * configuration, not a section of the admin.
	 */
	public static function add_menu() {
		add_options_page(
			__( 'WordPress Group Chat', 'wordpress-group-chat' ),
			__( 'WordPress Group Chat', 'wordpress-group-chat' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Load the screen's own assets, and only on this screen.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public static function enqueue( $hook_suffix ) {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'wordpress-group-chat-admin',
			WPGC_URL . 'assets/admin.css',
			array(),
			WPGC_VERSION
		);
		wp_enqueue_script(
			'wordpress-group-chat-admin',
			WPGC_URL . 'assets/admin.js',
			array(),
			WPGC_VERSION,
			true
		);
	}

	/**
	 * The settings page.
	 */
	public static function render() {
		// Belt and braces: add_options_page already gates on this capability,
		// but a callback should not assume it was reached the expected way.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'wordpress-group-chat' ) );
		}

		$settings = WPGC_Settings::get();
		$name     = WPGC_OPTION;
		?>
		<div class="wrap wpgc-wrap">
			<h1><?php esc_html_e( 'WordPress Group Chat', 'wordpress-group-chat' ); ?></h1>
			<p class="wpgc-intro">
				<?php esc_html_e( 'Add your Crowd to this site as a chat button. Enter your Crowd ID, save, and it appears in the corner of every page.', 'wordpress-group-chat' ); ?>
			</p>

			<form method="post" action="options.php">
				<?php settings_fields( 'wpgc' ); ?>

				<table class="form-table" role="presentation">
					<tbody>
					<tr>
						<th scope="row">
							<label for="wpgc-crowd"><?php esc_html_e( 'Crowd ID', 'wordpress-group-chat' ); ?></label>
						</th>
						<td>
							<input
								type="text"
								id="wpgc-crowd"
								class="regular-text code"
								name="<?php echo esc_attr( $name ); ?>[crowd]"
								value="<?php echo esc_attr( $settings['crowd'] ); ?>"
								placeholder="northside-runners"
								autocomplete="off"
								spellcheck="false"
							/>
							<button type="button" class="button-link wpgc-help-open" aria-expanded="false" aria-controls="wpgc-help">
								<?php esc_html_e( 'Where do I find this?', 'wordpress-group-chat' ); ?>
							</button>
							<p class="description">
								<?php esc_html_e( 'Required. Letters, numbers and dashes, as it appears on your Crowd.', 'wordpress-group-chat' ); ?>
							</p>
							<?php self::render_help(); ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Show the chat', 'wordpress-group-chat' ); ?></th>
						<td>
							<label for="wpgc-enabled">
								<input
									type="checkbox"
									id="wpgc-enabled"
									name="<?php echo esc_attr( $name ); ?>[enabled]"
									value="1"
									<?php checked( ! empty( $settings['enabled'] ) ); ?>
								/>
								<?php esc_html_e( 'Show the chat button on this site', 'wordpress-group-chat' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Leave this off while you set things up. Nothing appears on your site until it is on.', 'wordpress-group-chat' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Appearance', 'wordpress-group-chat' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'Appearance', 'wordpress-group-chat' ); ?></legend>

								<p>
									<label for="wpgc-theme"><?php esc_html_e( 'Theme', 'wordpress-group-chat' ); ?></label><br />
									<select id="wpgc-theme" name="<?php echo esc_attr( $name ); ?>[theme]">
										<option value="light" <?php selected( 'light', $settings['theme'] ); ?>>
											<?php esc_html_e( 'Light', 'wordpress-group-chat' ); ?>
										</option>
										<option value="dark" <?php selected( 'dark', $settings['theme'] ); ?>>
											<?php esc_html_e( 'Dark', 'wordpress-group-chat' ); ?>
										</option>
									</select>
									<span class="description">
										<?php esc_html_e( 'Where the chat starts. Visitors can switch it themselves.', 'wordpress-group-chat' ); ?>
									</span>
								</p>

								<p>
									<label for="wpgc-primary"><?php esc_html_e( 'Main colour', 'wordpress-group-chat' ); ?></label><br />
									<input
										type="text"
										id="wpgc-primary"
										class="regular-text code wpgc-colour"
										name="<?php echo esc_attr( $name ); ?>[primary]"
										value="<?php echo esc_attr( $settings['primary'] ); ?>"
										placeholder="#F4C32F"
										autocomplete="off"
										spellcheck="false"
									/>
									<span class="description">
										<?php esc_html_e( 'Optional. A hex colour such as #F4C32F. Leave empty to keep the chat’s own colours.', 'wordpress-group-chat' ); ?>
									</span>
								</p>

								<p>
									<label for="wpgc-secondary"><?php esc_html_e( 'Second colour', 'wordpress-group-chat' ); ?></label><br />
									<input
										type="text"
										id="wpgc-secondary"
										class="regular-text code wpgc-colour"
										name="<?php echo esc_attr( $name ); ?>[secondary]"
										value="<?php echo esc_attr( $settings['secondary'] ); ?>"
										placeholder="#111111"
										autocomplete="off"
										spellcheck="false"
									/>
									<span class="description">
										<?php esc_html_e( 'Optional. Used for the unread badge and headings. Defaults to the main colour.', 'wordpress-group-chat' ); ?>
									</span>
								</p>

								<p>
									<label for="wpgc-position"><?php esc_html_e( 'Button position', 'wordpress-group-chat' ); ?></label><br />
									<select id="wpgc-position" name="<?php echo esc_attr( $name ); ?>[position]">
										<option value="right" <?php selected( 'right', $settings['position'] ); ?>>
											<?php esc_html_e( 'Bottom right', 'wordpress-group-chat' ); ?>
										</option>
										<option value="left" <?php selected( 'left', $settings['position'] ); ?>>
											<?php esc_html_e( 'Bottom left', 'wordpress-group-chat' ); ?>
										</option>
									</select>
								</p>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Wording', 'wordpress-group-chat' ); ?></th>
						<td>
							<p>
								<label for="wpgc-label"><?php esc_html_e( 'Button label', 'wordpress-group-chat' ); ?></label><br />
								<input
									type="text"
									id="wpgc-label"
									class="regular-text"
									name="<?php echo esc_attr( $name ); ?>[label]"
									value="<?php echo esc_attr( $settings['label'] ); ?>"
									placeholder="<?php esc_attr_e( 'Open chat', 'wordpress-group-chat' ); ?>"
									maxlength="<?php echo esc_attr( WPGC_Settings::MAX_LABEL_LENGTH ); ?>"
								/>
								<span class="description">
									<?php esc_html_e( 'Read out by screen readers. Defaults to “Open chat”.', 'wordpress-group-chat' ); ?>
								</span>
							</p>

							<p>
								<label for="wpgc-welcome"><?php esc_html_e( 'Welcome line', 'wordpress-group-chat' ); ?></label><br />
								<input
									type="text"
									id="wpgc-welcome"
									class="large-text"
									name="<?php echo esc_attr( $name ); ?>[welcome]"
									value="<?php echo esc_attr( $settings['welcome'] ); ?>"
									placeholder="<?php esc_attr_e( 'Say hello to the crew', 'wordpress-group-chat' ); ?>"
									maxlength="<?php echo esc_attr( WPGC_Settings::MAX_WELCOME_LENGTH ); ?>"
								/>
								<span class="description">
									<?php esc_html_e( 'Optional. Shown above the sign-in field.', 'wordpress-group-chat' ); ?>
								</span>
							</p>
						</td>
					</tr>
					</tbody>
				</table>

				<?php submit_button(); ?>
			</form>

			<?php self::render_status( $settings ); ?>
		</div>
		<?php
	}

	/**
	 * The "where do I find this" panel.
	 *
	 * Inline and hidden rather than fetched, so it works with no network access
	 * and cannot break if a docs page moves.
	 */
	private static function render_help() {
		?>
		<div id="wpgc-help" class="wpgc-help" hidden>
			<p><strong><?php esc_html_e( 'Two ways to find your Crowd ID', 'wordpress-group-chat' ); ?></strong></p>
			<p><strong><?php esc_html_e( 'In the Brane app', 'wordpress-group-chat' ); ?></strong><br />
				<?php esc_html_e( 'Open your Crowd, tap the menu, then choose “Embed on Website”. The ID is the data-crowd value in the line it shows you.', 'wordpress-group-chat' ); ?>
			</p>
			<p><strong><?php esc_html_e( 'By email', 'wordpress-group-chat' ); ?></strong><br />
				<?php esc_html_e( 'From the same menu you can have the full instructions emailed to you. It only ever goes to the email address on your own account.', 'wordpress-group-chat' ); ?>
			</p>
			<p class="wpgc-help-note">
				<?php esc_html_e( 'You must be an admin of the Crowd to see this option.', 'wordpress-group-chat' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( 'https://brane.app/embed/' ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Full instructions', 'wordpress-group-chat' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * What the plugin is currently doing, and what it is putting on the page.
	 *
	 * Shown because "I saved it and I cannot tell whether it worked" is the
	 * likeliest way for this to waste somebody's afternoon.
	 *
	 * @param array $settings Current settings.
	 */
	private static function render_status( array $settings ) {
		$active = WPGC_Settings::is_active();
		?>
		<hr />
		<h2><?php esc_html_e( 'Status', 'wordpress-group-chat' ); ?></h2>
		<?php if ( '' === $settings['crowd'] ) : ?>
			<p class="wpgc-status wpgc-status-idle">
				<?php esc_html_e( 'No Crowd ID yet, so nothing is being added to your site.', 'wordpress-group-chat' ); ?>
			</p>
		<?php elseif ( ! $active ) : ?>
			<p class="wpgc-status wpgc-status-idle">
				<?php esc_html_e( 'Saved, but switched off. Tick “Show the chat button on this site” when you are ready.', 'wordpress-group-chat' ); ?>
			</p>
		<?php else : ?>
			<p class="wpgc-status wpgc-status-live">
				<?php esc_html_e( 'The chat button is live on your site.', 'wordpress-group-chat' ); ?>
			</p>
			<p><?php esc_html_e( 'This is what the plugin adds to your pages:', 'wordpress-group-chat' ); ?></p>
			<pre class="wpgc-snippet"><code><?php echo esc_html( WPGC_Embed::preview_snippet() ); ?></code></pre>
			<p class="description">
				<?php esc_html_e( 'You do not need to copy this anywhere. It is shown so you can see exactly what is on your page.', 'wordpress-group-chat' ); ?>
			</p>
		<?php endif; ?>
		<?php
	}
}
