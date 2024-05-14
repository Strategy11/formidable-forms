<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormMigratorsHelper {

	/**
	 * @return bool
	 */
	private static function is_dismissed( $form, $dismissed = null ) {
		if ( $dismissed === null ) {
			$dismissed = get_option( 'frm_dismissed' );
		}

		if ( ! empty( $dismissed ) && in_array( $form['class'], $dismissed ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return void
	 */
	public static function maybe_show_download_link() {
		$forms = self::import_links();
		foreach ( $forms as $form ) {
			if ( ! self::is_dismissed( $form ) ) {
				self::install_banner( $form );
			} else {
				echo '<span>';
				self::install_button( $form, 'auto' );
				echo '</span>';
			}
		}
	}

	/**
	 * @since 4.05
	 *
	 * @return void
	 */
	public static function maybe_add_to_inbox() {
		$forms = self::import_links();
		if ( ! $forms ) {
			return;
		}

		$inbox = new FrmInbox();
		foreach ( $forms as $form ) {
			$inbox->add_message(
				array(
					'key'     => $form['class'],
					'subject' => 'You have new importable forms',
					'message' => 'Did you know you can import your forms created in ' . esc_html( $form['name'] ) . '?',
					'cta'     => '<a href="' . esc_url( admin_url( 'admin.php?page=formidable-import' ) ) . '" class="button-primary frm-button-primary">' . esc_html__( 'Learn More', 'formidable' ) . '</a>',
					'icon'    => 'frm_cloud_upload_solid_icon',
					'type'    => 'news',
				)
			);
		}
	}

	/**
	 * @return array
	 */
	private static function import_links() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return array();
		}

		$forms = array();
		foreach ( self::importable_forms() as $form ) {
			if ( class_exists( $form['class'] ) || ! is_plugin_active( $form['plugin'] ) ) {
				// Either the importer is installed or the source plugin isn't.
				continue;
			}

			$installer         = new FrmInstallPlugin( array( 'plugin_file' => $form['importer'] ) );
			$form['installed'] = $installer->is_installed();
			$form['link']      = $installer->get_activate_link();

			$forms[] = $form;
		}
		return $forms;
	}

	/**
	 * @return string[][]
	 */
	private static function importable_forms() {
		return array(
			'gf' => array(
				'class'    => 'FrmGravityImporter',
				'plugin'   => 'gravityforms/gravityforms.php',
				'importer' => 'formidable-gravity-forms-importer/formidable-gravity-forms-importer.php',
				'name'     => 'Gravity Forms',
				'package'  => 'https://downloads.wordpress.org/plugin/formidable-gravity-forms-importer.zip',
			),
			'pf' => array(
				'class'    => 'FrmPirateImporter',
				'plugin'   => 'pirate-forms/pirate-forms.php',
				'importer' => 'formidable-import-pirate-forms/pf-to-frm.php',
				'name'     => 'Pirate Forms',
				'package'  => 'https://downloads.wordpress.org/plugin/formidable-import-pirate-forms.zip',
			),
		);
	}

	private static function install_banner( $install ) {
		if ( empty( $install['link'] ) ) {
			return '';
		}

		?>
		<div class="frm-feature-banner">
			<a href="#" class="dismiss alignright" id="<?php echo esc_attr( $install['class'] ); ?>" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
			</a>
			<div class="frm-big-icon">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_cloud_upload_solid_icon', array( 'aria-label' => 'Import' ) ); ?>
			</div>
			<p>Did you know you can import your forms created in <?php echo esc_html( $install['name'] ); ?>?</p>
			<?php self::install_button( $install ); ?>
		</div>
		<?php
	}

	/**
	 * @param array  $install
	 * @param string $label
	 *
	 * @return void
	 */
	private static function install_button( $install, $label = '' ) {
		$primary = 'button-secondary frm-button-secondary ';

		if ( empty( $label ) ) {
			$label   = __( 'Get Started', 'formidable' );
			$primary = 'button-primary frm-button-primary ';
		}

		if ( $install['installed'] ) {
			?>
			<a rel="<?php echo esc_attr( $install['importer'] ); ?>" class="button frm-activate-addon <?php echo esc_attr( $primary . ( empty( $install['link'] ) ? 'frm_hidden' : '' ) ); ?>">
			<?php
			if ( $label === 'auto' ) {
				/* translators: %s: Name of the plugin */
				$label = sprintf( __( 'Activate %s', 'formidable' ), $install['name'] );
			}
		} else {
			?>
			<a rel="<?php echo esc_attr( $install['package'] ); ?>" class="frm-install-addon button <?php echo esc_attr( $primary ); ?>" aria-label="<?php esc_attr_e( 'Install', 'formidable' ); ?>">
			<?php
			if ( $label === 'auto' ) {
				/* translators: %s: Name of the plugin */
				$label = sprintf( __( 'Install %s Importer', 'formidable' ), $install['name'] );
			}
		}
		?>
		<?php echo esc_html( $label ); ?>
		</a>
		<?php
	}

	/**
	 * @return void
	 */
	public static function dismiss_migrator() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		$dismissed = get_option( 'frm_dismissed' );
		if ( empty( $dismissed ) ) {
			$dismissed = array();
		}
		$dismissed[] = FrmAppHelper::get_param( 'plugin', '', 'post', 'sanitize_text_field' );
		update_option( 'frm_dismissed', array_filter( $dismissed ), 'no' );
		wp_die();
	}
}
