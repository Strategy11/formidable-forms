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
 * @var bool        $skill_is_stale Whether a release has come out since this user last downloaded the skill.
 * @var array       $skill_passwords Application passwords created for this user's MCP skill downloads, with formatted creation dates.
 * @var bool        $env_available   Whether this user can create an Application Password.
 * @var string      $admin_post_url  WordPress handler URL for the env download and revocation buttons.
 * @var string      $options_class   Classes for the settings shown when MCP is enabled.
 * @var array       $claude_commands Claude Code commands to copy.
 * @var string      $skill_repository_path Codex skill repository path.
 * @var string      $setup_command   Skill setup command to copy.
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

<div class="<?php echo esc_attr( $options_class ); ?>">
	<p class="frm_primary_label" id="frm_mcp_client_label">
		<?php esc_html_e( 'What tool are you trying to connect?', 'formidable' ); ?>
	</p>
	<div class="frm_captchas frm-long-icon-buttons" role="radiogroup" aria-labelledby="frm_mcp_client_label">
		<input type="radio" name="frm_mcp_client_view" id="frm-mcp-client-claude" value="claude" data-frmhide="#frm_mcp_codex_instructions" data-frmshow="#frm_mcp_claude_instructions" checked="checked" />
		<label for="frm-mcp-client-claude">
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/mcp-claude.svg' ); ?>" width="21" height="21" alt="" />
			<?php esc_html_e( 'Claude Code', 'formidable' ); ?>
		</label>
		<input type="radio" name="frm_mcp_client_view" id="frm-mcp-client-codex" value="codex" data-frmhide="#frm_mcp_claude_instructions" data-frmshow="#frm_mcp_codex_instructions" />
		<label for="frm-mcp-client-codex">
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/mcp-codex.svg' ); ?>" width="21" height="21" alt="" />
			<?php esc_html_e( 'Codex', 'formidable' ); ?>
		</label>
	</div>

	<div id="frm_mcp_claude_instructions" class="frm-mb-md">
		<h3><?php esc_html_e( 'Install the Formidable Skill in Claude Code', 'formidable' ); ?></h3>
		<p class="description"><?php esc_html_e( 'In Claude Code, install the skill with these commands:', 'formidable' ); ?></p>
		<?php foreach ( $claude_commands as $command ) { ?>
			<div class="frm-mcp-copy-row">
				<code><?php echo esc_html( $command ); ?></code>
				<button type="button" class="frm-mcp-copy-button js-frm-mcp-copy" data-frm-copy="<?php echo esc_attr( $command ); ?>" aria-label="<?php esc_attr_e( 'Copy command', 'formidable' ); ?>" data-copied-label="<?php esc_attr_e( 'Copied!', 'formidable' ); ?>">
					<svg class="frmsvg frm_svg20" aria-hidden="true" focusable="false"><use href="#frm_clone_icon"></use></svg>
				</button>
			</div>
		<?php } ?>
		<p class="description">
			<a href="<?php echo esc_url( $docs_urls['claude'] ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Formidable guide: Install the skill in Claude Code', 'formidable' ); ?>
			</a>
		</p>
	</div>

	<div id="frm_mcp_codex_instructions" class="frm-mb-md frm_hidden">
		<h3><?php esc_html_e( 'Install the Formidable Skill in Codex', 'formidable' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Ask Codex to install the skill from this repository path:', 'formidable' ); ?></p>
		<div class="frm-mcp-copy-row">
			<code><?php echo esc_html( $skill_repository_path ); ?></code>
			<button type="button" class="frm-mcp-copy-button js-frm-mcp-copy" data-frm-copy="<?php echo esc_attr( $skill_repository_path ); ?>" aria-label="<?php esc_attr_e( 'Copy repository path', 'formidable' ); ?>" data-copied-label="<?php esc_attr_e( 'Copied!', 'formidable' ); ?>">
				<svg class="frmsvg frm_svg20" aria-hidden="true" focusable="false"><use href="#frm_clone_icon"></use></svg>
			</button>
		</div>
		<p class="description">
			<a href="<?php echo esc_url( $docs_urls['codex'] ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Formidable guide: Install the skill in Codex', 'formidable' ); ?>
			</a>
		</p>
	</div>
	<div class="frm-mb-md">
		<h3>
			<?php esc_html_e( 'Skill Download', 'formidable' ); ?>
			<?php if ( $skill_is_stale ) { ?>
				<span class="frm-meta-tag frm-orange-tag"><?php esc_html_e( 'Update available', 'formidable' ); ?></span>
			<?php } ?>
		</h3>
		<p class="description frm-mb-xs"><?php esc_html_e( 'Download the complete skill repository for manual installation.', 'formidable' ); ?></p>
		<div class="frm-flex frm-flex-wrap frm-items-center frm-gap-sm">
			<a class="button frm-button-secondary frm-with-icon" href="<?php echo esc_url( $skill_url ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_download_icon frm_svg15', array( 'aria-hidden' => 'true' ) ); ?>
				<?php echo $skill_is_stale ? esc_html__( 'Download Update', 'formidable' ) : esc_html__( 'Download Skill', 'formidable' ); ?>
			</a>
			<?php if ( $skill_status ) { ?>
				<span class="frm-text-xs frm-text-grey-500">
					<?php echo wp_kses_post( $skill_status ); ?>
				</span>
			<?php } ?>
		</div>
	</div>
	<div class="frm-mcp-configured-skill frm-mb-lg">
		<h3><?php esc_html_e( 'Connect the skill to this site', 'formidable' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'For the Formidable Skill HTTP helper, download a file containing this site URL, your WordPress username, and a new Application Password. Place it at scripts/frm-mcp.env inside the installed skill, then run scripts/frm-mcp-setup.', 'formidable' ); ?>
		</p>
		<div class="frm-mcp-copy-row">
			<code><?php echo esc_html( $setup_command ); ?></code>
			<button type="button" class="frm-mcp-copy-button js-frm-mcp-copy" data-frm-copy="<?php echo esc_attr( $setup_command ); ?>" aria-label="<?php esc_attr_e( 'Copy setup command', 'formidable' ); ?>" data-copied-label="<?php esc_attr_e( 'Copied!', 'formidable' ); ?>">
				<svg class="frmsvg frm_svg20" aria-hidden="true" focusable="false"><use href="#frm_clone_icon"></use></svg>
			</button>
		</div>
		<p class="description">
			<a href="<?php echo esc_url( $docs_urls['env'] ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Formidable guide: Connect with the skill HTTP helper', 'formidable' ); ?>
			</a>
		</p>
		<?php if ( $env_available ) { ?>
			<?php wp_nonce_field( FrmMcpSkillEnvController::DOWNLOAD_ACTION, FrmMcpSkillEnvController::DOWNLOAD_ACTION . '_nonce' ); ?>
			<button type="submit" class="button frm-button-secondary" formaction="<?php echo esc_url( $admin_post_url ); ?>" formmethod="post" name="action" value="<?php echo esc_attr( FrmMcpSkillEnvController::DOWNLOAD_ACTION ); ?>">
				<?php esc_html_e( 'Generate Application Password and download frm-mcp.env', 'formidable' ); ?>
			</button>
			<p class="description frm-mcp-credential-note"><?php esc_html_e( 'Each download creates a new credential. Keep the file private and out of version control. This password is restricted to Formidable MCP while the plugin is active. Revoke it before deactivating Formidable.', 'formidable' ); ?></p>
		<?php } else { ?>
			<p class="description"><?php esc_html_e( 'Application Passwords must be available for your account, and public sites must use HTTPS, to create this file.', 'formidable' ); ?></p>
		<?php } ?>
		<?php if ( $skill_passwords ) { ?>
			<?php wp_nonce_field( FrmMcpSkillEnvController::REVOKE_ACTION, FrmMcpSkillEnvController::REVOKE_ACTION . '_nonce' ); ?>
			<h4 class="frm-text-md frm-mb-sm frm-mcp-credentials-heading"><?php esc_html_e( 'Downloaded skill credentials', 'formidable' ); ?></h4>
			<div>
				<?php foreach ( $skill_passwords as $skill_password ) { ?>
					<div class="frm-flex frm-flex-wrap frm-items-center frm-justify-between frm-gap-sm frm-p-sm frm-bt-200">
						<div class="frm-flex-col frm-gap-2xs frm-flex-full frm-min-w-0">
							<strong class="frm-text-sm"><?php esc_html_e( 'Formidable MCP skill', 'formidable' ); ?></strong>
							<span class="description frm-text-xs">
								<?php
								printf(
									/* translators: %s: Date and time the skill credential was created. */
									esc_html__( 'Created %s', 'formidable' ),
									esc_html( $skill_password['created_at'] )
								);
								?>
								<span aria-hidden="true"> · </span>
								<?php if ( $skill_password['last_used'] ) { ?>
									<?php
									printf(
										/* translators: %s: How long ago the downloaded skill credential was last used. */
										esc_html__( 'Last used %s ago', 'formidable' ),
										esc_html( human_time_diff( $skill_password['last_used'] ) )
									);
									?>
								<?php } else { ?>
									<?php esc_html_e( 'Not used yet', 'formidable' ); ?>
								<?php } ?>
							</span>
						</div>
						<button type="submit" class="button frm-button-secondary frm-button-sm frm-shrink-0" formaction="<?php echo esc_url( add_query_arg( 'frm_mcp_password_uuid', $skill_password['uuid'], $admin_post_url ) ); ?>" formmethod="post" name="action" value="<?php echo esc_attr( FrmMcpSkillEnvController::REVOKE_ACTION ); ?>">
							<?php esc_html_e( 'Revoke', 'formidable' ); ?>
							<span class="screen-reader-text">
								<?php
								printf(
									/* translators: %s: Date and time the skill credential was created. */
									esc_html__( 'skill credential created %s', 'formidable' ),
									esc_html( $skill_password['created_at'] )
								);
								?>
							</span>
						</button>
					</div>
				<?php }//end foreach ?>
			</div>
		<?php }//end if ?>
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
