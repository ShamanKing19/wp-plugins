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

<div class="ai1wm-modal-sites" role="dialog" tabindex="-1">
	<?php if ( count( $networks ) > 1 ) : ?>
		<p>
			<label for="ai1wmme-new-network-id">
				<?php _e( 'Network', AI1WMME_PLUGIN_NAME ); ?>
				<br />
				<select id="ai1wmme-new-network-id" name="options[network]">
					<?php foreach ( $networks as $network ) : ?>
						<option value="<?php echo esc_attr( $network['id'] ); ?>"><?php echo esc_html( $network['domain'] . untrailingslashit( $network['path'] ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>
	<?php else : ?>
		<?php if ( ( $network = get_current_site() ) ) : ?>
			<input type="hidden" name="options[network]" value="<?php echo esc_attr( $network->id ); ?>" />
		<?php endif; ?>
	<?php endif; ?>
	<p>
		<label for="ai1wmme-old-subsite-url">
			<?php _e( 'Old subsite URL', AI1WMME_PLUGIN_NAME ); ?>
			<br />
			<input type="url" id="ai1wmme-old-subsite-url" value="<?php echo esc_url( $sites[ $subsite ]['Old']['HomeURL'] ); ?>" disabled="disabled" />
		</label>
	</p>
	<p>
		<label for="ai1wmme-new-subsite-url">
			<?php _e( 'New subsite URL', AI1WMME_PLUGIN_NAME ); ?>
			<br />
			<input type="url" id="ai1wmme-new-subsite-url" name="options[sites][<?php echo esc_attr( $subsite ); ?>]" value="<?php echo esc_url( $sites[ $subsite ]['New']['HomeURL'] ); ?>" required="required" autocomplete="off" />
		</label>
	</p>
	<input type="hidden" name="options[subsite]" value="<?php echo esc_attr( $subsite ); ?>" />
</div>
