<?php
/**
 * URL generation toolkit.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/managers
 */

/**
 *
 * (c) Copyright Ascensio System SIA 2023
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * URL generation toolkit.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Url_Manager {

	private const PATH_EDITOR_URL = '/onlyoffice/editorurl/';
	private const PATH_CALLBACK   = '/onlyoffice/callback/';
	private const PATH_DOWNLOAD   = '/onlyoffice/getfile/';
	private const PATH_EDITOR     = '/onlyoffice/editor/';
	private const PATH_API_JS     = 'web-apps/apps/api/documents/api.js';

	/**
	 * Return the URL to get the URL to the editor page.
	 *
	 * @return string
	 */
	public static function get_url_to_editor_url() {
		if ( get_option( 'permalink_structure' ) ) {
			return get_option( 'siteurl' ) . '/index.php?rest_route=' . self::PATH_EDITOR_URL;
		} else {
			return get_option( 'siteurl' ) . '/wp-json' . self::PATH_EDITOR_URL;
		}
	}

	/**
	 * Return the URL to api.js.
	 *
	 * @return string
	 */
	public static function get_api_js_url() {
		$doc_server_url = self::get_doc_server_url();

		return self::append_slash( $doc_server_url ) . self::PATH_API_JS;
	}

	/**
	 * Return the URL to document editing server.
	 *
	 * @return string
	 */
	public static function get_doc_server_url() {
		$onlyoffice_settings = get_option( 'onlyoffice_settings' );
		return $onlyoffice_settings[ Onlyoffice_Plugin_Settings::DOCSERVER_URL ];
	}

	/**
	 * Return the URL to saving attachment.
	 *
	 * @param string $attachment_id The attachment ID.
	 * @return string
	 */
	public static function get_callback_url( $attachment_id ) {
		$hidden_id = self::encode_openssl_data( $attachment_id );

		if ( get_option( 'permalink_structure' ) ) {
			return get_option( 'siteurl' ) . '/index.php?rest_route=' . self::PATH_CALLBACK . $hidden_id;
		} else {
			return get_option( 'siteurl' ) . '/wp-json' . self::PATH_CALLBACK . $hidden_id;
		}
	}

	/**
	 * Return the URL to download attachment.
	 *
	 * @param string $attachment_id The attachment ID.
	 * @return string
	 */
	public static function get_download_url( $attachment_id ) {
		$data = wp_json_encode(
			array(
				'attachment_id' => $attachment_id,
				'user_id'       => wp_get_current_user()->ID,
			)
		);

		$hidden_id = self::encode_openssl_data( $data );

		if ( get_option( 'permalink_structure' ) ) {
			return get_option( 'siteurl' ) . '/index.php?rest_route=' . self::PATH_DOWNLOAD . $hidden_id;
		} else {
			return get_option( 'siteurl' ) . '/wp-json' . self::PATH_DOWNLOAD . $hidden_id;
		}
	}

	/**
	 * Return the URL to editor.
	 *
	 * @param string $attachment_id The attachment ID.
	 * @return string
	 */
	public static function get_editor_url( $attachment_id ) {
		$hidden_id = self::encode_openssl_data( $attachment_id );

		if ( get_option( 'permalink_structure' ) ) {
			return get_option( 'siteurl' ) . '/index.php?rest_route=' . self::PATH_EDITOR . $hidden_id;
		} else {
			return get_option( 'siteurl' ) . '/wp-json' . self::PATH_EDITOR . $hidden_id;
		}
	}

	/**
	 * Encrypts data.
	 *
	 * @param string $data The data.
	 *
	 * @return false|string
	 */
	private static function encode_openssl_data( $data ) {
		$passphrase = get_option( 'onlyoffice-plugin-uuid' );
		$iv         = hex2bin( get_option( 'onlyoffice-plugin-bytes' ) );

		$encrypt = openssl_encrypt( $data, 'aes-256-ctr', $passphrase, $options = 0, $iv );

		return str_replace( '%', ',', rawurlencode( $encrypt ) );
	}

	/**
	 * Decrypts data.
	 *
	 * @param string $data The data.
	 * @return false|string
	 */
	public static function decode_openssl_data( $data ) {
		$passphrase = get_option( 'onlyoffice-plugin-uuid' );
		$iv         = hex2bin( get_option( 'onlyoffice-plugin-bytes' ) );

		$data = urldecode( str_replace( ',', '%', $data ) );

		return openssl_decrypt( $data, 'aes-256-ctr', $passphrase, $options = 0, $iv );
	}

	/**
	 * Append slash to the end of the url if missing.
	 *
	 * @param string $url The URL.
	 * @return string
	 */
	private static function append_slash( $url ) {
		return str_ends_with( $url, '/' ) ? $url : $url . '/';
	}

}
