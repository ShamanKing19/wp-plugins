<?php
/**
 * Copyright (C) 2014-2020 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot jump here' );
}
?>

<div class="ai1wm-container">
	<div class="ai1wm-row">
		<div class="ai1wm-left">
			<div class="ai1wm-holder">
				<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'Amazon S3 Settings', AI1WMSE_PLUGIN_NAME ); ?></h1>
				<br />
				<br />

				<?php if ( Ai1wm_Message::has( 'error' ) ) : ?>
					<div class="ai1wm-message ai1wm-error-message">
						<p><?php echo Ai1wm_Message::get( 'error' ); ?></p>
					</div>
				<?php elseif ( Ai1wm_Message::has( 'success' ) ) : ?>
					<div class="ai1wm-message ai1wm-success-message">
						<p><?php echo Ai1wm_Message::get( 'success' ); ?></p>
					</div>
				<?php endif; ?>

				<div id="ai1wmse-credentials">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=ai1wmse_s3_connection' ) ); ?>">
						<div class="ai1wm-field">
							<label for="ai1wmse-s3-access-key">
								<?php _e( 'Access Key', AI1WMSE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter Amazon S3 Access Key', AI1WMSE_PLUGIN_NAME ); ?>" id="ai1wmse-s3-access-key" class="ai1wmse-settings-key" name="ai1wmse_s3_access_key" value="<?php echo esc_attr( $access_key ); ?>" />
							</label>
							<a
								href="https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_access-keys.html#Using_CreateAccessKey"
								target="_blank">
								<?php _e( 'How to find your Access Key', AI1WMSE_PLUGIN_NAME ); ?>
							</a>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmse-s3-secret-key">
								<?php _e( 'Secret Key', AI1WMSE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php ( $secret_key ) ? _e( 'Hidden', AI1WMSE_PLUGIN_NAME ) : _e( 'Enter Amazon S3 Secret Key', AI1WMSE_PLUGIN_NAME ); ?>" id="ai1wmse-s3-secret-key" class="ai1wmse-settings-key" name="ai1wmse_s3_secret_key" autocomplete="off" />
							</label>
							<a
								href="https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_access-keys.html#Using_CreateAccessKey"
								target="_blank">
								<?php _e( 'How to find your Secret Key', AI1WMSE_PLUGIN_NAME ); ?>
							</a>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmse-s3-https-protocol">
								<input type="checkbox" id="ai1wmse-s3-https-protocol" name="ai1wmse_s3_https_protocol" value="1" <?php echo empty( $https_protocol ) ? null : 'checked'; ?> />
								<?php _e( 'Use HTTPS protocol', AI1WMSE_PLUGIN_NAME ); ?>
							</label>
						</div>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmse_s3_update" id="ai1wmse-s3-link">
								<i class="ai1wm-icon-enter"></i>
								<?php _e( 'Update', AI1WMSE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			</div>

			<?php if ( $buckets !== false ) : ?>
				<div id="ai1wmse-backups" class="ai1wm-holder">
					<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'Amazon S3 Backups', AI1WMSE_PLUGIN_NAME ); ?></h1>
					<br />
					<br />

					<?php if ( Ai1wm_Message::has( 'bucket' ) ) : ?>
						<div class="ai1wm-message ai1wm-error-message">
							<p><?php echo Ai1wm_Message::get( 'bucket' ); ?></p>
						</div>
					<?php elseif ( Ai1wm_Message::has( 'settings' ) ) : ?>
						<div class="ai1wm-message ai1wm-success-message">
							<p><?php echo Ai1wm_Message::get( 'settings' ); ?></p>
						</div>
					<?php endif; ?>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=ai1wmse_s3_settings' ) ); ?>">
						<article class="ai1wmse-article">
							<h3><?php _e( 'Region name', AI1WMSE_PLUGIN_NAME ); ?></h3>
							<p>
								<?php if ( count( $regions ) > 0 ) : ?>
									<select class="ai1wmse-region-name" id="ai1wmse-s3-region-name" name="ai1wmse_s3_region_name">
										<?php foreach ( $regions as $key => $value ) : ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php echo $region_name === $key ? 'selected' : null; ?>><?php echo esc_html( $value ); ?></option>
										<?php endforeach; ?>
									</select>
								<?php else : ?>
									<input type="text" placeholder="<?php _e( 'Enter Amazon S3 Region Name', AI1WMSE_PLUGIN_NAME ); ?>" class="ai1wmse-region-name" name="ai1wmse_s3_region_name" id="ai1wmse-s3-region-name" value="<?php echo esc_attr( $region_name ); ?>" />
								<?php endif; ?>
							</p>
						</article>

						<article class="ai1wmse-article">
							<h3><?php _e( 'Bucket name', AI1WMSE_PLUGIN_NAME ); ?></h3>
							<p>
								<input type="text" placeholder="<?php _e( 'Enter Amazon S3 Bucket Name', AI1WMSE_PLUGIN_NAME ); ?>" class="ai1wmse-bucket-name" name="ai1wmse_s3_bucket_name" id="ai1wmse-s3-bucket-name" value="<?php echo esc_attr( $bucket_name ); ?>" />
							</p>
						</article>

						<article class="ai1wmse-article">
							<h3><?php _e( 'Folder name', AI1WMSE_PLUGIN_NAME ); ?></h3>
							<p>
								<input type="text" placeholder="<?php _e( 'Enter Folder Name (optional)', AI1WMSE_PLUGIN_NAME ); ?>" class="ai1wmse-folder-name" name="ai1wmse_s3_folder_name" id="ai1wmse-s3-folder-name" value="<?php echo esc_attr( $folder_name ); ?>" />
							</p>
						</article>

						<article class="ai1wmse-article">
							<h3><?php _e( 'Storage class', AI1WMSE_PLUGIN_NAME ); ?></h3>
							<ul>
								<li>
									<label for="ai1wmse-s3-storage-class-standard">
										<input type="radio" name="ai1wmse_s3_storage_class" id="ai1wmse-s3-storage-class-standard" value="STANDARD" <?php echo $storage_class === 'STANDARD' ? 'checked="checked"' : null; ?> />
										<?php _e( 'Standard', AI1WMSE_PLUGIN_NAME ); ?>
										<a href="https://aws.amazon.com/s3/storage-classes/#General_purpose" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMSE_PLUGIN_NAME ); ?></a>
									</label>
								</li>
								<li>
									<label for="ai1wmse-s3-storage-class-intelligent-tiering">
										<input type="radio" name="ai1wmse_s3_storage_class" id="ai1wmse-s3-storage-class-intelligent-tiering" value="INTELLIGENT_TIERING" <?php echo $storage_class === 'INTELLIGENT_TIERING' ? 'checked="checked"' : null; ?> />
										<?php _e( 'Intelligent-Tiering', AI1WMSE_PLUGIN_NAME ); ?>
										<a href="https://aws.amazon.com/s3/storage-classes/#Unknown_or_changing_access" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMSE_PLUGIN_NAME ); ?></a>
									</label>
								</li>
								<li>
									<label for="ai1wmse-s3-storage-class-standard-ia">
										<input type="radio" name="ai1wmse_s3_storage_class" id="ai1wmse-s3-storage-class-standard-ia" value="STANDARD_IA" <?php echo $storage_class === 'STANDARD_IA' ? 'checked="checked"' : null; ?> />
										<?php _e( 'Standard-Infrequent Access', AI1WMSE_PLUGIN_NAME ); ?>
										<a href="https://aws.amazon.com/s3/storage-classes/#Infrequent_access" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMSE_PLUGIN_NAME ); ?></a>
									</label>
								</li>
								<li>
									<label for="ai1wmse-s3-storage-class-onezone-ia">
										<input type="radio" name="ai1wmse_s3_storage_class" id="ai1wmse-s3-storage-class-onezone-ia" value="ONEZONE_IA" <?php echo $storage_class === 'ONEZONE_IA' ? 'checked="checked"' : null; ?> />
										<?php _e( 'One Zone-Infrequent Access', AI1WMSE_PLUGIN_NAME ); ?>
										<a href="https://aws.amazon.com/s3/storage-classes/#Infrequent_access" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMSE_PLUGIN_NAME ); ?></a>
									</label>
								</li>
								<li>
									<label for="ai1wmse-s3-storage-class-glacier">
										<input type="radio" name="ai1wmse_s3_storage_class" id="ai1wmse-s3-storage-class-glacier" value="GLACIER" <?php echo $storage_class === 'GLACIER' ? 'checked="checked"' : null; ?> />
										<?php _e( 'Glacier', AI1WMSE_PLUGIN_NAME ); ?>
										<a href="https://aws.amazon.com/s3/storage-classes/#Archive" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMSE_PLUGIN_NAME ); ?></a>
									</label>
								</li>
								<li>
									<label for="ai1wmse-s3-storage-class-deep-archive">
										<input type="radio" name="ai1wmse_s3_storage_class" id="ai1wmse-s3-storage-class-deep-archive" value="DEEP_ARCHIVE" <?php echo $storage_class === 'DEEP_ARCHIVE' ? 'checked="checked"' : null; ?> />
										<?php _e( 'Glacier Deep Archive', AI1WMSE_PLUGIN_NAME ); ?>
										<a href="https://aws.amazon.com/s3/storage-classes/#Archive" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMSE_PLUGIN_NAME ); ?></a>
									</label>
								</li>
								<li>
									<label for="ai1wmse-s3-storage-class-reduced-redundancy">
										<input type="radio" name="ai1wmse_s3_storage_class" id="ai1wmse-s3-storage-class-reduced-redundancy" value="REDUCED_REDUNDANCY" <?php echo $storage_class === 'REDUCED_REDUNDANCY' ? 'checked="checked"' : null; ?> />
										<?php _e( 'Reduced Redundancy', AI1WMSE_PLUGIN_NAME ); ?>
										<a href="https://aws.amazon.com/s3/reduced-redundancy/" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMSE_PLUGIN_NAME ); ?></a>
									</label>
								</li>
							</ul>
						</article>

						<article class="ai1wmse-article">
							<h3><?php _e( 'Encryption', AI1WMSE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmse-s3-encryption">
									<input type="checkbox" id="ai1wmse-s3-encryption" name="ai1wmse_s3_encryption" value="AES256" <?php echo empty( $encryption ) ? null : 'checked'; ?> />
									<?php _e( 'Protect data at rest by using Amazon S3 master-key', AI1WMSE_PLUGIN_NAME ); ?>
									<a href="http://docs.aws.amazon.com/AmazonS3/latest/dev/UsingEncryption.html" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMSE_PLUGIN_NAME ); ?></a>
								</label>
							</p>
						</article>

						<article class="ai1wmse-article">
							<h3><?php _e( 'Configure your backup plan', AI1WMSE_PLUGIN_NAME ); ?></h3>

							<p>
								<label for="ai1wmse-s3-cron-timestamp">
									<?php _e( 'Backup time:', AI1WMSE_PLUGIN_NAME ); ?>
									<input type="text" name="ai1wmse_s3_cron_timestamp" id="ai1wmse-s3-cron-timestamp" value="<?php echo esc_attr( get_date_from_gmt( date( 'Y-m-d H:i:s', $s3_cron_timestamp ), 'g:i a' ) ); ?>" autocomplete="off" />
									<code><?php echo ai1wm_get_timezone_string(); ?></code>
								</label>
							</p>

							<ul id="ai1wmse-s3-cron">
								<li>
									<label for="ai1wmse-s3-cron-hourly">
										<input type="checkbox" name="ai1wmse_s3_cron[]" id="ai1wmse-s3-cron-hourly" value="hourly" <?php echo in_array( 'hourly', $s3_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every hour', AI1WMSE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmse-s3-cron-daily">
										<input type="checkbox" name="ai1wmse_s3_cron[]" id="ai1wmse-s3-cron-daily" value="daily" <?php echo in_array( 'daily', $s3_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every day', AI1WMSE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmse-s3-cron-weekly">
										<input type="checkbox" name="ai1wmse_s3_cron[]" id="ai1wmse-s3-cron-weekly" value="weekly" <?php echo in_array( 'weekly', $s3_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every week', AI1WMSE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmse-s3-cron-monthly">
										<input type="checkbox" name="ai1wmse_s3_cron[]" id="ai1wmse-s3-cron-monthly" value="monthly" <?php echo in_array( 'monthly', $s3_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every month', AI1WMSE_PLUGIN_NAME ); ?>
									</label>
								</li>
							</ul>

							<p>
								<?php _e( 'Last backup date:', AI1WMSE_PLUGIN_NAME ); ?>
								<strong>
									<?php echo $last_backup_date; ?>
								</strong>
							</p>

							<p>
								<?php _e( 'Next backup date:', AI1WMSE_PLUGIN_NAME ); ?>
								<strong>
									<?php echo $next_backup_date; ?>
								</strong>
							</p>
						</article>

						<article class="ai1wmse-article">
							<h3><?php _e( 'Notification settings', AI1WMSE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmse-s3-notify-toggle">
									<input type="checkbox" id="ai1wmse-s3-notify-toggle" name="ai1wmse_s3_notify_toggle" <?php echo empty( $notify_ok_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email when a backup is complete', AI1WMSE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmse-s3-notify-error-toggle">
									<input type="checkbox" id="ai1wmse-s3-notify-error-toggle" name="ai1wmse_s3_notify_error_toggle" <?php echo empty( $notify_error_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email if a backup fails', AI1WMSE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmse-s3-notify-email">
									<?php _e( 'Email address', AI1WMSE_PLUGIN_NAME ); ?>
									<br />
									<input class="ai1wmse-email" type="email" id="ai1wmse-s3-notify-email" name="ai1wmse_s3_notify_email" value="<?php echo esc_attr( $notify_email ); ?>" />
								</label>
							</p>
						</article>

						<article class="ai1wmse-article">
							<h3><?php _e( 'Retention settings', AI1WMSE_PLUGIN_NAME ); ?></h3>
							<p>
								<div class="ai1wm-field">
									<label for="ai1wmse-s3-backups">
										<?php _e( 'Keep the most recent', AI1WMSE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmse_s3_backups" id="ai1wmse-s3-backups" value="<?php echo intval( $backups ); ?>" />
									</label>
									<?php _e( 'backups. <small>Default: <strong>0</strong> unlimited</small>', AI1WMSE_PLUGIN_NAME ); ?>
								</div>

								<div class="ai1wm-field">
									<label for="ai1wmse-s3-total">
										<?php _e( 'Limit the total size of backups to', AI1WMSE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmse_s3_total" id="ai1wmse-s3-total" value="<?php echo intval( $total ); ?>" />
									</label>
									<select style="margin-top: -2px;" name="ai1wmse_s3_total_unit" id="ai1wmse-s3-total-unit">
										<option value="MB" <?php echo strpos( $total, 'MB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'MB', AI1WMSE_PLUGIN_NAME ); ?></option>
										<option value="GB" <?php echo strpos( $total, 'GB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'GB', AI1WMSE_PLUGIN_NAME ); ?></option>
									</select>
									<?php _e( '<small>Default: <strong>0</strong> unlimited</small>', AI1WMSE_PLUGIN_NAME ); ?>
								</div>

								<div class="ai1wm-field">
									<label for="ai1wmse-s3-days">
										<?php _e( 'Remove backups older than ', AI1WMSE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmse_s3_days" id="ai1wmse-s3-days" value="<?php echo intval( $days ); ?>" />
									</label>
									<?php _e( 'days. <small>Default: <strong>0</strong> off</small>', AI1WMSE_PLUGIN_NAME ); ?>
								</div>
							</p>
						</article>

						<article class="ai1wmse-article">
							<h3><?php _e( 'Transfer settings', AI1WMSE_PLUGIN_NAME ); ?></h3>
							<div class="ai1wm-field">
								<label><?php _e( 'Slow Internet (Home)', AI1WMSE_PLUGIN_NAME ); ?></label>
								<input name="ai1wmse_s3_file_chunk_size" min="5242880" max="20971520" step="5242880" type="range" value="<?php echo $file_chunk_size; ?>" id="ai1wmse-s3-file-chunk-size" />
								<label><?php _e( 'Fast Internet (Internet Servers)', AI1WMSE_PLUGIN_NAME ); ?></label>
							</div>
						</article>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmse_s3_update" id="ai1wmse-s3-update">
								<i class="ai1wm-icon-database"></i>
								<?php _e( 'Update', AI1WMSE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			<?php endif; ?>
		</div>
		<div class="ai1wm-right">
			<div class="ai1wm-sidebar">
				<div class="ai1wm-segment">
					<?php if ( ! AI1WM_DEBUG ) : ?>
						<?php include AI1WM_TEMPLATES_PATH . '/common/share-buttons.php'; ?>
					<?php endif; ?>

					<h2><?php _e( 'Leave Feedback', AI1WMSE_PLUGIN_NAME ); ?></h2>

					<?php include AI1WM_TEMPLATES_PATH . '/common/leave-feedback.php'; ?>
				</div>
			</div>
		</div>
	</div>
</div>
