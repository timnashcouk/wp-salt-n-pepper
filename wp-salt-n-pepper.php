<?php

/*
Plugin Name: WP Salt and Pepper
Plugin URI: http://www.timnash.co.uk/salt-n-pepper/
Git URI: https://github.com/timnashcouk/wp-salt-n-pepper
Description: Adds user specific salts and versioning for salts
Version: 1.0
Author: Tim Nash
Author URI: http://www.timnash.co.uk
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Provides: wp-salt-n-pepper

*/
/*
 	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !function_exists('wp_set_password') ) :
/**
 * Overrides wp_set_password which "Updates the user's password with a new encrypted one."
 *
 *
 * @uses $wpdb WordPress database object for queries
 * @uses wp_generate_password to generate a random passwordesque string for the unique hash
 * @uses wp_hash_password() Used to encrypt the user's password before passing to the database
 * @uses update_user_meta() Adds the unique user salt to the user_meta table and version control for salt
 *
 * @param string $password The plaintext new user password
 * @param int $user_id User ID
 */
function wp_set_password( $password, $user_id ) {
	global $wpdb;

	//Generate a unique random hash to append to password
	$unique_hash = wp_generate_password();
	$hash = wp_hash_password( $password.$unique_hash );
	$wpdb->update($wpdb->users, array('user_pass' => $hash, 'user_activation_key' => ''), array('ID' => $user_id) );
	
	//Update usermeta with unique salt and version
	update_user_meta($user_id,'unique_user_salt',$unique_hash);
	update_user_meta($user_id.'salt_n_peper_version',1);

	wp_cache_delete($user_id, 'users');
}
endif;

if ( !function_exists('wp_check_password') ) :
/**
 * Overrides wp_set_password which "Checks the plaintext password against the encrypted Password.""
 *
 *
 * @global object $wp_hasher PHPass object used for checking the password
 *	against the $hash + $password+$unique_user_salt
 * @uses PasswordHash::CheckPassword
 *
 * @param string $password Plaintext user's password
 * @param string $hash Hash of the user's password to check against.
 * @return bool False, if the $password+$unique_user_salt does not match the hashed password
 */
function wp_check_password($password, $hash, $user_id = '') {
	global $wp_hasher;

	// If the hash is still md5...
	if ( strlen($hash) <= 32 ) {
		$check = ( $hash == md5($password) );
		if ( $check && $user_id ) {
			// Rehash using new hash.
			wp_set_password($password, $user_id);
			$hash = wp_hash_password($password);
		}

		return apply_filters('check_password', $check, $password, $hash, $user_id);
	}

	// If the stored hash is longer than an MD5, presume the
	// new style phpass portable hash.
	if ( empty($wp_hasher) ) {
		require_once( ABSPATH . 'wp-includes/class-phpass.php');
		// By default, use the portable hash from phpass
		$wp_hasher = new PasswordHash(8, true);
	}
	// Get both unique user salt and the version being used
	$unique_user_salt = get_user_meta($user_id,'unique_user_salt',true);
	$salt_n_peper_version = get_user_meta($user_id,'salt_n_peper_version',true);
	//Check that the current user has a unique salt and version
	// @todo extend versioning to allow specify alternate methods for different versions
	if(!empty($unique_user_salt) || !empty($salt_n_peper_version)){
		$check = $wp_hasher->CheckPassword($password, $hash);
	}
	else{
		$check = $wp_hasher->CheckPassword($password.$unique_user_salt, $hash);
	}

	return apply_filters('check_password', $check, $password, $hash, $user_id);
}
endif;