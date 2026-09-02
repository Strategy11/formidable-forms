<?php
/**
 * The MCP section of the global settings page.
 *
 * @package Formidable
 *
 * @var bool        $mcp_enabled    Whether the MCP server and the Formidable abilities are turned on.
 * @var array|null  $connections    Recent MCP connections, or null when the adapter is unavailable.
 * @var string      $blocked_reason Why the adapter cannot run, or an empty string when nothing blocks it.
 * @var bool        $is_inherited   Whether the toggle is showing a value inherited from the API add-on.
 * @var string      $skill_url      Link the Download Skill button points at, nonced through admin-post.php.
 * @var array|false $skill_release  Version, publish date, and release page URL of the current skill release, or false when it cannot be read.
 * @var array|false $skill_download Time and version of this user's last skill download, or false when they have never downloaded it.
 * @var bool        $skill_is_stale Whether a release has come out since this user last downloaded the skill.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// frm_no_bottom_margin used to sit on this paragraph, which is only an alias for
// frm-mb-0, so the intro copy ran straight into the control below it. The
// toggle is taller than the checkbox it replaced and read cramped against the
// text, so the paragraph keeps its normal bottom margin now and supplies the
// spacing above the toggle from the standard rhythm rather than from a
// compensating utility on the toggle row.
?>
<p>
	<?php esc_html_e( 'Connect an AI assistant to this site so it can build and manage your forms, entries, and styles.', 'formidable' ); ?>
	<a href="<?php echo esc_url( FrmAppHelper::get_doc_url( 'connect-formidable-forms-to-your-ai-agent-with-mcp', 'mcp-global-settings' ) ); ?>" target="_blank" rel="noopener">
		<?php esc_html_e( 'Learn more', 'formidable' ); ?>
	</a>
</p>

<?php
// The marker is what tells FrmSettings::update_settings() the toggle was on
// screen. Without it, saving any other settings section would switch MCP off.
?>
<input type="hidden" name="frm_mcp_settings_shown" value="1" />

<div class="frm-flex frm-items-center frm-gap-xs frm-mb-sm">
	<?php
	// shared/toggle.php appends [] to any name that has no bracket, so the name is
	// written with the brackets here rather than letting the partial add them out of
	// sight. $_POST['frm_mcp'] is therefore an array, which is what
	// FrmSettings::update_settings() already reads through empty(): an unchecked
	// toggle posts nothing at all, and a checked one posts array( '1' ). Nothing on
	// the read side changes.
	FrmHtmlHelper::toggle(
		'frm_mcp',
		'frm_mcp[]',
		array(
			'echo'       => true,
			'value'      => 1,
			'checked'    => $mcp_enabled,
			'input_html' => array(
				// This is what shows and hides div.frm_mcp_options below. The handler in
				// admin.js is delegated on input[data-toggleclass], and the toggle's real
				// checkbox is still an input, so it keeps firing from inside the toggle.
				'data-toggleclass' => 'frm_mcp_options',
			),
		)
	);
	?>
	<label id="frm_mcp_label" for="frm_mcp">
		<?php esc_html_e( 'Enable the Formidable MCP server', 'formidable' ); ?>
	</label>
	<?php
	// frm-leading-none drops the span to the height of the icon it holds, so the
	// flex row centers the glyph on the toggle and the label instead of on the
	// taller line box the icon is drawn at the top of.
	FrmAppHelper::tooltip_icon(
		__( 'This is the switch for the whole AI surface. While it is off, the Formidable abilities are not registered either, so they are unavailable to the Abilities API and to any other MCP server on the site.', 'formidable' ),
		array( 'class' => 'frm-leading-none' )
	);
	?>
</div>

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

<?php
$options_class = 'frm_mcp_options frm_indent_opt';

if ( ! $mcp_enabled ) {
	$options_class .= ' frm_hidden';
}
?>
<div class="<?php echo esc_attr( $options_class ); ?>">
	<div class="frm-mb-md">
		<h3>
			<?php esc_html_e( 'Formidable Skill', 'formidable' ); ?>
			<?php if ( $skill_is_stale ) { ?>
				<span class="frm-meta-tag frm-orange-tag"><?php esc_html_e( 'Update available', 'formidable' ); ?></span>
			<?php } ?>
		</h3>
		<p class="description frm-mb-xs">
			<?php esc_html_e( 'Add this skill to your AI assistant so it knows how to build forms, views, and styles on your site.', 'formidable' ); ?>
		</p>
		<div class="frm-flex frm-flex-wrap frm-items-center frm-gap-sm">
			<a class="button frm-button-secondary frm-with-icon" href="<?php echo esc_url( $skill_url ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_download_icon frm_svg15', array( 'aria-hidden' => 'true' ) ); ?>
				<?php echo $skill_is_stale ? esc_html__( 'Download Update', 'formidable' ) : esc_html__( 'Download Skill', 'formidable' ); ?>
			</a>
			<?php if ( $skill_release || $skill_download ) { ?>
				<span class="frm-text-xs frm-text-grey-500">
					<?php
					$released_date   = $skill_release ? FrmMcpSettingsController::relative_skill_date( $skill_release['published'] ) : '';
					$downloaded_date = $skill_download ? FrmMcpSettingsController::relative_skill_date( $skill_download['time'] ) : '';
					$your_version    = $skill_download ? $skill_download['version'] : '';

					// The line leads with the release, linked to its notes. With no
					// release to name, the version already downloaded leads instead.
					if ( $skill_release ) {
						$version = esc_html( $skill_release['version'] );

						if ( $skill_release['url'] ) {
							$version = '<a href="' . esc_url( $skill_release['url'] ) . '" target="_blank" rel="noopener">' . $version . '</a>';
						}
					} else {
						$version = esc_html( $your_version );
					}

					// The version carries a link, so it is escaped as post HTML while the
					// rest of the line is escaped as text.
					if ( $skill_is_stale && $released_date ) {
						printf(
							/* translators: %1$s: The version of the skill that is available. %2$s: How long ago it was released, like "today". %3$s: The version this user downloaded. */
							esc_html__( '%1$s released %2$s — you have %3$s', 'formidable' ),
							wp_kses_post( $version ),
							esc_html( $released_date ),
							esc_html( $your_version )
						);
					} elseif ( $skill_is_stale ) {
						printf(
							/* translators: %1$s: The version of the skill that is available. %2$s: The version this user downloaded. */
							esc_html__( '%1$s available — you have %2$s', 'formidable' ),
							wp_kses_post( $version ),
							esc_html( $your_version )
						);
					} elseif ( $version && $downloaded_date ) {
						printf(
							/* translators: %1$s: The version of the skill. %2$s: How long ago this user downloaded it, like "today". */
							esc_html__( '%1$s — downloaded %2$s', 'formidable' ),
							wp_kses_post( $version ),
							esc_html( $downloaded_date )
						);
					} elseif ( $version && $released_date ) {
						printf(
							/* translators: %1$s: The version of the skill. %2$s: How long ago it was released, like "today". */
							esc_html__( '%1$s released %2$s', 'formidable' ),
							wp_kses_post( $version ),
							esc_html( $released_date )
						);
					} else {
						echo wp_kses_post( $version );
					}//end if
					?>
				</span>
			<?php
			}//end if
 ?>
		</div>
	</div>

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
