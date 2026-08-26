<?php
/**
 * The MCP section of the global settings page.
 *
 * @package Formidable
 *
 * @var bool        $mcp_enabled    Whether the MCP server and the Formidable abilities are turned on.
 * @var array|null  $connections    Recent MCP connections, or null when the adapter is unavailable.
 * @var string      $server_url     Full URL of the Formidable MCP server.
 * @var string      $blocked_reason Why the adapter cannot run, or an empty string when nothing blocks it.
 * @var bool        $is_inherited   Whether the toggle is showing a value inherited from the API add-on.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<p class="frm_no_bottom_margin">
	<?php esc_html_e( 'Connect an AI assistant to this site so it can build and manage your forms, entries, and styles.', 'formidable' ); ?>
</p>

<?php
// The marker is what tells FrmSettings::update_settings() the toggle was on
// screen. Without it, saving any other settings section would switch MCP off.
?>
<input type="hidden" name="frm_mcp_settings_shown" value="1" />

<p>
	<label>
		<input type="checkbox" name="frm_mcp" id="frm_mcp" value="1" data-toggleclass="frm_mcp_options" <?php checked( $mcp_enabled, true ); ?> />
		<?php esc_html_e( 'Enable the Formidable MCP server', 'formidable' ); ?>
	</label>
	<span class="frm_help frmfont frm_tooltip_icon" title="<?php esc_attr_e( 'This is the switch for the whole AI surface. While it is off, the Formidable abilities are not registered either, so they are unavailable to the Abilities API and to any other MCP server on the site.', 'formidable' ); ?>"></span>
</p>

<?php if ( $is_inherited ) { ?>
	<p class="frm_warning_style">
		<?php esc_html_e( 'This setting is currently coming from the Formidable API add-on. Saving here takes it over, and the add-on will follow this setting from then on.', 'formidable' ); ?>
	</p>
<?php } ?>

<?php if ( '' !== $blocked_reason ) { ?>
	<p class="frm_error_style">
		<?php
		esc_html_e( 'MCP is turned on but not running.', 'formidable' );
		echo ' ' . esc_html( $blocked_reason );
		?>
	</p>
<?php } ?>

<div class="frm_mcp_options frm_indent_opt<?php echo $mcp_enabled ? '' : ' frm_hidden'; ?>">
	<p>
		<label for="frm_mcp_server_url"><?php esc_html_e( 'Server URL', 'formidable' ); ?></label>
		<input type="text" id="frm_mcp_server_url" value="<?php echo esc_attr( $server_url ); ?>" readonly onfocus="this.select()" />
		<span class="description">
			<?php esc_html_e( 'Point your AI assistant here. It signs in with a WordPress application password for your user, so it can only do what that user is allowed to do.', 'formidable' ); ?>
		</span>
	</p>

	<h3><?php esc_html_e( 'MCP Connections', 'formidable' ); ?></h3>
	<p class="description">
		<?php esc_html_e( 'Who has connected, and what they asked for most recently.', 'formidable' ); ?>
	</p>

	<?php if ( null === $connections ) { ?>
		<p class="description">
			<?php esc_html_e( 'Connections appear here once the MCP server is running.', 'formidable' ); ?>
		</p>
	<?php } elseif ( ! $connections ) { ?>
		<p><strong><?php esc_html_e( 'No connections yet', 'formidable' ); ?></strong></p>
		<p class="description"><?php esc_html_e( "When someone connects an AI assistant to your site, it'll appear here.", 'formidable' ); ?></p>
	<?php } else { ?>
		<table class="widefat striped frm-mcp-connections">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'User', 'formidable' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Last Active', 'formidable' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Recent Endpoints', 'formidable' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $connections as $connection ) { ?>
					<tr>
						<td>
							<?php echo esc_html( $connection['display_name'] ); ?>
							<span class="description">(<?php echo esc_html( $connection['user_login'] ); ?>)</span>
						</td>
						<td>
							<span title="<?php echo esc_attr( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $connection['last_request'] ) ); ?>">
								<?php
								printf(
									/* translators: %s: Human readable time difference, like "5 mins". */
									esc_html__( '%s ago', 'formidable' ),
									esc_html( human_time_diff( $connection['last_request'] ) )
								);
								?>
							</span>
						</td>
						<td>
							<?php if ( $connection['endpoints'] ) { ?>
								<ul class="frm-mcp-endpoints">
									<?php foreach ( array_slice( array_keys( $connection['endpoints'] ), 0, 3 ) as $endpoint ) { ?>
										<li><?php echo esc_html( $endpoint ); ?></li>
									<?php } ?>
								</ul>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>
					</tr>
				<?php
				}//end foreach
 ?>
			</tbody>
		</table>
	<?php
	}//end if
 ?>
</div>
