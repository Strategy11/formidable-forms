<?php
/**
 * The MCP section of the global settings page.
 *
 * @package Formidable
 *
 * @var bool        $mcp_enabled    Whether the MCP server and the Formidable abilities are turned on.
 * @var array|null  $connections    Recent MCP connections, one row per user and client, or null when the adapter is unavailable.
 * @var string      $blocked_reason Why the adapter cannot run, or an empty string when nothing blocks it.
 * @var bool        $is_inherited   Whether the toggle is showing a value inherited from the API add-on.
 * @var string      $skill_url      Link the Download Skill button points at, nonced through admin-post.php.
 * @var bool        $skill_is_stale Whether a release has come out since this user last downloaded the skill.
 * @var array       $skill_passwords Application passwords created for this user's MCP skill downloads, with formatted creation dates.
 * @var bool        $env_available   Whether this user can create an Application Password.
 * @var string      $admin_post_url  WordPress handler URL for the env download and revocation buttons.
 * @var string      $options_class   Classes for the settings shown when MCP is enabled.
 * @var bool        $env_created     Whether this user has downloaded at least one env file.
 * @var int         $last_used       When a skill password last authenticated, or 0 when none has.
 * @var string      $connection_text Whether an assistant has connected, in words.
 * @var array       $connection_prompts Prompts for each AI client to finish setup.
 * @var string      $skill_status    Escaped release summary HTML.
 * @var array       $docs_urls       MCP documentation URLs for this page.
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
	<a href="<?php echo esc_url( $docs_urls['overview'] ); ?>" target="_blank" rel="noopener">
		<?php esc_html_e( 'Learn more about MCP', 'formidable' ); ?>
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
		__( 'Controls AI access for the whole site. While it is off, no MCP server can use Formidable\'s abilities.', 'formidable' ),
		array( 'class' => 'frm-leading-none' )
	);
	?>
</div>

<?php if ( $is_inherited ) { ?>
	<p class="frm_warning_style">
		<?php esc_html_e( 'This setting comes from the Formidable API add-on. Save this page to manage it here. The add-on will follow it from then on.', 'formidable' ); ?>
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

<div class="<?php echo esc_attr( $options_class ); ?>">
	<?php
	// Each step is checked off as soon as the page can tell it is done. The env
	// file step is done once a skill password exists, and the connect step once
	// one of those passwords has been used, which settings.js keeps polling for.
	$step_states  = array(
		'env'     => $env_created,
		'connect' => (bool) $last_used,
	);
	$step_classes = array();

	foreach ( $step_states as $step_key => $is_complete ) {
		$step_classes[ $step_key ] = $is_complete ? 'frm-mcp-step frm-mcp-step-complete' : 'frm-mcp-step';
	}

	$steps_attrs = array(
		'id'                  => 'frm_mcp_steps',
		'class'               => 'frm-mcp-steps',
		'data-complete-label' => __( 'Complete', 'formidable' ),
	);

	if ( $env_created && ! $last_used ) {
		// Picked up on load, so a reload after downloading keeps listening.
		$steps_attrs['data-waiting'] = '1';
	}
	?>
	<ol<?php FrmAppHelper::array_to_html_params( $steps_attrs, true ); ?>>
		<li id="frm_mcp_step_env" class="<?php echo esc_attr( $step_classes['env'] ); ?>">
			<div class="frm-mcp-step-body">
				<h3 class="frm-mcp-step-title">
					<span class="frm-mcp-step-marker" aria-hidden="true">
						<span class="frm-mcp-step-number">1</span>
						<svg class="frmsvg frm-mcp-step-check"><use href="#frm_checkmark_icon"></use></svg>
					</span>
					<?php esc_html_e( 'Download your connection file', 'formidable' ); ?>
					<span class="screen-reader-text js-frm-mcp-step-state"><?php
					if ( $env_created ) {
						esc_html_e( 'Complete', 'formidable' );
					}
					?></span>
				</h3>
				<?php if ( $env_available ) { ?>
					<p class="description">
						<?php esc_html_e( 'Each download creates a new Application Password for Formidable MCP only. Keep it private and out of version control.', 'formidable' ); ?>
						<a href="<?php echo esc_url( $docs_urls['env'] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'About connection files', 'formidable' ); ?>
						</a>
					</p>
					<?php wp_nonce_field( FrmMcpSkillEnvController::DOWNLOAD_ACTION, FrmMcpSkillEnvController::DOWNLOAD_ACTION . '_nonce', false ); ?>
					<?php
					// Once a file exists, step 2's copy button becomes the primary action.
					$download_class = $env_created ? 'frm-button-secondary' : 'frm-button-primary';
					?>
					<button type="submit" class="button <?php echo esc_attr( $download_class ); ?> frm-with-icon js-frm-mcp-download-env" formaction="<?php echo esc_url( $admin_post_url ); ?>" formmethod="post" name="action" value="<?php echo esc_attr( FrmMcpSkillEnvController::DOWNLOAD_ACTION ); ?>" data-new-label="<?php esc_attr_e( 'Download a new file', 'formidable' ); ?>">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_file_download_icon', array( 'aria-hidden' => 'true' ) ); ?>
						<span class="js-frm-mcp-download-label"><?php echo $env_created ? esc_html__( 'Download a new file', 'formidable' ) : esc_html__( 'Download frm-mcp.env', 'formidable' ); ?></span>
					</button>
				<?php } else { ?>
					<p class="description"><?php esc_html_e( 'To create this file, your account needs Application Passwords, and public sites need HTTPS.', 'formidable' ); ?></p>
				<?php
				}//end if
 ?>
				<?php if ( $skill_passwords ) { ?>
					<?php wp_nonce_field( FrmMcpSkillEnvController::REVOKE_ACTION, FrmMcpSkillEnvController::REVOKE_ACTION . '_nonce', false ); ?>
					<details class="frm-mcp-disclosure">
						<summary>
							<svg class="frmsvg frm-mcp-disclosure-chevron" aria-hidden="true" focusable="false"><use href="#frm_arrowdown6_icon"></use></svg>
							<?php
							printf(
								/* translators: %s: Number of connection files this user has downloaded. */
								esc_html( _n( 'Manage %s connection file', 'Manage %s connection files', count( $skill_passwords ), 'formidable' ) ),
								esc_html( number_format_i18n( count( $skill_passwords ) ) )
							);
							?>
						</summary>
						<p class="frm-mcp-note"><?php esc_html_e( 'Revoke a file to disconnect its assistant. Revoke all files before you deactivate Formidable, or they will keep working as regular Application Passwords.', 'formidable' ); ?></p>
						<ul class="frm-mcp-credentials">
							<?php foreach ( $skill_passwords as $skill_password ) { ?>
								<li>
									<span>
										<?php
										printf(
											/* translators: %s: Date and time the skill credential was created. */
											esc_html__( 'Created %s', 'formidable' ),
											esc_html( $skill_password['created_at'] )
										);
										?>
										<span class="frm-mcp-note">
											<?php
											if ( $skill_password['last_used'] ) {
												printf(
													/* translators: %s: How long ago the downloaded skill credential was last used. */
													esc_html__( 'Last used %s ago', 'formidable' ),
													esc_html( human_time_diff( $skill_password['last_used'] ) )
												);
											} else {
												esc_html_e( 'Not used yet', 'formidable' );
											}
											?>
										</span>
									</span>
									<button type="submit" class="button frm-button-secondary frm-button-sm frm-shrink-0" formaction="<?php echo esc_url( add_query_arg( 'frm_mcp_password_uuid', $skill_password['uuid'], $admin_post_url ) ); ?>" formmethod="post" name="action" value="<?php echo esc_attr( FrmMcpSkillEnvController::REVOKE_ACTION ); ?>">
										<?php esc_html_e( 'Revoke', 'formidable' ); ?>
										<span class="screen-reader-text">
											<?php
											printf(
												/* translators: %s: Date and time the skill credential was created. */
												esc_html__( 'connection file created %s', 'formidable' ),
												esc_html( $skill_password['created_at'] )
											);
											?>
										</span>
									</button>
								</li>
							<?php }//end foreach ?>
						</ul>
					</details>
				<?php }//end if ?>
			</div>
		</li>

		<li id="frm_mcp_step_connect" class="<?php echo esc_attr( $step_classes['connect'] ); ?>">
			<div class="frm-mcp-step-body">
				<h3 class="frm-mcp-step-title">
					<span class="frm-mcp-step-marker" aria-hidden="true">
						<span class="frm-mcp-step-number">2</span>
						<svg class="frmsvg frm-mcp-step-check"><use href="#frm_checkmark_icon"></use></svg>
					</span>
					<?php esc_html_e( 'Connect your AI assistant', 'formidable' ); ?>
					<span class="screen-reader-text js-frm-mcp-step-state"><?php
					if ( $last_used ) {
						esc_html_e( 'Complete', 'formidable' );
					}
					?></span>
				</h3>
				<?php
				$prompt_clients = array(
					'claude' => array(
						'name'  => __( 'Claude Code', 'formidable' ),
						'guide' => __( 'Claude Code setup guide', 'formidable' ),
					),
					'codex'  => array(
						'name'  => __( 'Codex', 'formidable' ),
						'guide' => __( 'Codex setup guide', 'formidable' ),
					),
				);
				?>
				<p class="description" id="frm_mcp_client_label"><?php esc_html_e( 'Pick your assistant, then paste the setup prompt into it. The prompt installs the skill, adds your file, and tests the connection.', 'formidable' ); ?></p>
				<?php
				// Nothing is selected by default, and autocomplete is off so the browser
				// cannot restore a choice on reload either. The parent settings form sets
				// autocomplete because radio inputs do not support it. settings.js shows the
				// prompt for whichever radio is checked. The radios stay in the tab
				// order (visually hidden, not display:none) so the picker works from
				// the keyboard.
				?>
				<div class="frm-mcp-clients" role="radiogroup" aria-labelledby="frm_mcp_client_label">
					<?php foreach ( $prompt_clients as $client => $prompt_client ) { ?>
						<input type="radio" name="frm_mcp_client_view" id="<?php echo esc_attr( 'frm-mcp-client-' . $client ); ?>" value="<?php echo esc_attr( $client ); ?>" />
						<label for="<?php echo esc_attr( 'frm-mcp-client-' . $client ); ?>">
							<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/mcp-' . $client . '.svg' ); ?>" width="20" height="20" alt="" />
							<?php echo esc_html( $prompt_client['name'] ); ?>
						</label>
					<?php } ?>
				</div>
				<?php
				// Primary only between downloading a file and connecting.
				foreach ( $prompt_clients as $client => $prompt_client ) {
					?>
					<div id="<?php echo esc_attr( 'frm_mcp_prompt_client_' . $client ); ?>" class="frm-mcp-prompt frm_hidden">
						<div class="frm-mcp-prompt-actions">
							<button type="button" class="button <?php echo esc_attr( $env_created && ! $last_used ? 'frm-button-primary' : 'frm-button-secondary' ); ?> frm-with-icon js-frm-mcp-copy" data-frm-copy="<?php echo esc_attr( $connection_prompts[ $client ] ); ?>" data-copied-label="<?php esc_attr_e( 'Copied', 'formidable' ); ?>">
								<svg class="frmsvg" aria-hidden="true" focusable="false"><use href="#frm-copy-icon"></use></svg>
								<span class="js-frm-mcp-copy-label"><?php esc_html_e( 'Copy setup prompt', 'formidable' ); ?></span>
							</button>
							<a href="<?php echo esc_url( $docs_urls[ $client ] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $prompt_client['guide'] ); ?>
							</a>
						</div>
						<details class="frm-mcp-disclosure">
							<summary>
								<svg class="frmsvg frm-mcp-disclosure-chevron" aria-hidden="true" focusable="false"><use href="#frm_arrowdown6_icon"></use></svg>
								<?php esc_html_e( 'Show the setup prompt', 'formidable' ); ?>
							</summary>
							<pre class="frm-mcp-prompt-text"><?php echo esc_html( $connection_prompts[ $client ] ); ?></pre>
						</details>
					</div>
					<?php
				}//end foreach
				?>
				<?php
				// The live region is always on the page, even before there is anything
				// to say, so the message settings.js writes into it is announced.
				?>
				<p id="frm_mcp_connection_status" class="frm-mcp-connection-status" role="status" aria-live="polite"><?php echo $env_created ? esc_html( $connection_text ) : ''; ?></p>
				<details class="frm-mcp-disclosure frm-mcp-manual-skill"<?php echo $skill_is_stale ? ' open' : ''; ?>>
					<summary>
						<svg class="frmsvg frm-mcp-disclosure-chevron" aria-hidden="true" focusable="false"><use href="#frm_arrowdown6_icon"></use></svg>
						<?php esc_html_e( 'Install the skill manually', 'formidable' ); ?>
						<?php if ( $skill_is_stale ) { ?>
							<span class="frm-meta-tag frm-orange-tag"><?php esc_html_e( 'Update available', 'formidable' ); ?></span>
						<?php } ?>
					</summary>
					<p class="frm-mcp-manual-skill-row">
						<a href="<?php echo esc_url( $skill_url ); ?>" class="button frm-button-secondary frm-button-sm frm-with-icon">
							<?php FrmAppHelper::icon_by_class( 'frmfont frm_file_download_icon', array( 'aria-hidden' => 'true' ) ); ?>
							<?php echo $skill_is_stale ? esc_html__( 'Download the skill update', 'formidable' ) : esc_html__( 'Download the skill', 'formidable' ); ?>
						</a>
						<?php if ( $skill_status ) { ?>
							<span class="frm-mcp-note"><?php echo wp_kses_post( $skill_status ); ?></span>
						<?php } ?>
					</p>
				</details>
			</div>
		</li>
	</ol>

	<h3><?php esc_html_e( 'MCP Connections', 'formidable' ); ?></h3>
	<?php
	// One description for every state, then the content: the table, or a short
	// status where the table would be.
	?>
	<p class="description">
		<?php esc_html_e( 'Who has connected, and what they asked for most recently.', 'formidable' ); ?>
	</p>

	<?php if ( null === $connections ) { ?>
		<p><strong><?php esc_html_e( 'Available once the MCP server is running', 'formidable' ); ?></strong></p>
	<?php } elseif ( ! $connections ) { ?>
		<p><strong><?php esc_html_e( 'No connections yet', 'formidable' ); ?></strong></p>
	<?php } else { ?>
		<table class="widefat striped frm-border frm-mcp-connections">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Client', 'formidable' ); ?></th>
					<th scope="col"><?php esc_html_e( 'User', 'formidable' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Last Active', 'formidable' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Recent Requests', 'formidable' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $connections as $connection ) {
					$client_label = '' === $connection['client'] ? __( 'N/A', 'formidable' ) : $connection['client'];
					?>
					<tr>
						<td>
							<span class="frm-mcp-client">
								<?php if ( $connection['client_icon'] ) { ?>
									<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/mcp-' . $connection['client_icon'] . '.svg' ); ?>" width="20" height="20" alt="" />
								<?php } else { ?>
									<span class="frm-mcp-client-initial" aria-hidden="true"><?php echo esc_html( '' === $connection['client'] ? '?' : strtoupper( substr( $connection['client'], 0, 1 ) ) ); ?></span>
								<?php } ?>
								<?php echo esc_html( $client_label ); ?>
							</span>
						</td>
						<td>
							<span class="frm-mcp-user"><?php echo esc_html( $connection['display_name'] ); ?></span>
							<?php if ( $connection['display_name'] !== $connection['user_login'] ) { ?>
								<span class="description"><?php echo esc_html( $connection['user_login'] ); ?></span>
							<?php } ?>
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
									<?php
									foreach ( array_slice( array_keys( $connection['endpoints'] ), 0, 3 ) as $endpoint ) {
										// The namespace is the same for nearly every ability, so the pill shows the
										// ability's own name and the full name stays in the tooltip.
										$endpoint_parts = explode( '/', (string) $endpoint );
										?>
										<li class="frm-meta-tag frm-grey-tag" title="<?php echo esc_attr( (string) $endpoint ); ?>"><?php echo esc_html( end( $endpoint_parts ) ); ?></li>
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
