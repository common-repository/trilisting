<?php

namespace TRILISTING;

/**
 * User avatars.
 *
 * @since 1.0.0
 */
class Trilisting_User_Avatars {

	/**
	 * User ID
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private $user_id_being_edited;

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 * 
	 * @static
	 * 
	 * @since 1.0.0
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Initialize all the things
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Actions
		add_action( 'show_user_profile', [ $this, 'edit_user_profile'] );
		add_action( 'edit_user_profile', [ $this, 'edit_user_profile'] );
		add_action( 'personal_options_update', [ $this, 'edit_user_profile_update'] );
		add_action( 'edit_user_profile_update', [ $this, 'edit_user_profile_update'] );

		// Filters
		add_filter( 'get_avatar', [ $this, 'get_avatar'], 10, 5 );
		add_filter( 'avatar_defaults', [ $this, 'avatar_defaults' ] );
	}

	/**
	 * Filter the avatar WordPress returns
	 *
	 * @since 1.0.0
	 * @param string $avatar 
	 * @param int/string/object $id_or_email
	 * @param int $size 
	 * @param string $default
	 * @param boolean $alt 
	 * @return string
	 */
	public function get_avatar( $avatar = '', $id_or_email, $size = 96, $default = '', $alt = false ) {

		// Determine if we recive an ID or string
		if ( is_numeric( $id_or_email ) ) {
			$user_id = (int) $id_or_email;
		} elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ) {
			$user_id = $user->ID;
		} elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
			$user_id = (int) $id_or_email->user_id;
		}

		if ( empty( $user_id ) ) {
			return $avatar;
		}

		$local_avatars = get_user_meta( $user_id, 'trilisting_user_avatar', true );

		if ( empty( $local_avatars ) || empty( $local_avatars['full'] ) ) {
			return $avatar;
		}

		$size = (int) $size;

		if ( empty( $alt ) ) {
			$alt = get_the_author_meta( 'display_name', $user_id );
		}

		// Generate a new size
		if ( empty( $local_avatars[ $size ] ) ) {
			$upload_path      = wp_upload_dir();
			$avatar_full_path = str_replace( $upload_path['baseurl'], $upload_path['basedir'], $local_avatars['full'] );
			$image            = wp_get_image_editor( $avatar_full_path );
			$image_sized      = null;

			if ( ! is_wp_error( $image ) ) {
				$image->resize( $size, $size, true );
				$image_sized = $image->save();
			}

			// Deal with original being >= to original image (or lack of sizing ability).
			if ( empty( $image_sized ) || is_wp_error( $image_sized ) ) {
				$local_avatars[ $size ] = $local_avatars['full'];
			} else {
				$local_avatars[ $size ] = str_replace( $upload_path['basedir'], $upload_path['baseurl'], $image_sized['path'] );
			}

			// Save updated avatar sizes
			update_user_meta( $user_id, 'trilisting_user_avatar', $local_avatars );

		} elseif ( substr( $local_avatars[$size], 0, 4 ) != 'http' ) {
			$local_avatars[ $size ] = home_url( $local_avatars[ $size ] );
		}

		$author_class = is_author( $user_id ) ? ' current-author' : '' ;
		$avatar       = "<img alt='" . esc_attr( $alt ) . "' src='" . $local_avatars[ $size ] . "' class='avatar avatar-{$size}{$author_class} photo' height='{$size}' width='{$size}' />";

		return apply_filters( 'trilisting_user_avatar', $avatar, $user_id );
	}

	/**
	 * Form to display on the user profile edit screen
	 *
	 * @since 1.0.0
	 * @param object $profileuser
	 * @return
	 */
	public function edit_user_profile( $profileuser ) {
		?>
		<h3><?php _e( 'Avatar', 'trilisting' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="trilisting-user-avatar"><?php _e( 'Upload Avatar', 'trilisting' ); ?></label></th>
				<td style="width: 50px;" valign="top">
					<?php echo get_avatar( $profileuser->ID ); ?>
				</td>
				<td>
				<?php
				$options = get_option( 'trilisting_user_avatars_caps' );
				if ( empty( $options['trilisting_user_avatars_caps'] ) || current_user_can( 'upload_files' ) ) {
					// Nonce security ftw
					wp_nonce_field( 'trilisting_user_avatar_nonce', '_trilisting_user_avatar_nonce', false );
					
					// File upload input
					echo '<input type="file" name="trilisting-user-avatar" id="trilisting-local-avatar" /><br />';

					if ( empty( $profileuser->trilisting_user_avatar ) ) {
						echo '<span class="description">' . __( 'No local avatar is set. Use the upload field to add a local avatar.', 'trilisting' ) . '</span>';
					} else {
						echo '<input type="checkbox" name="trilisting-user-avatar-erase" value="1" /> ' . __( 'Delete local avatar', 'trilisting' ) . '<br />';
						echo '<span class="description">' . __( 'Replace the local avatar by uploading a new avatar, or erase the local avatar (falling back to a gravatar) by checking the delete option.', 'trilisting' ) . '</span>';
					}

				} else {
					if ( empty( $profileuser->trilisting_user_avatar ) ) {
						echo '<span class="description">' . __( 'No local avatar is set. Set up your avatar at Gravatar.com.', 'trilisting' ) . '</span>';
					} else {
						echo '<span class="description">' . __( 'You do not have media management permissions. To change your local avatar, contact the site administrator.', 'trilisting' ) . '</span>';
					}	
				}
				?>
				</td>
			</tr>
		</table>
		<script type="text/javascript">var form = document.getElementById('your-profile');form.encoding = 'multipart/form-data';form.setAttribute('enctype', 'multipart/form-data');</script>
		<?php
	}

	/**
	 * Update the user's avatar setting
	 *
	 * @since 1.0.0
	 * @param int $user_id
	 */
	public function edit_user_profile_update( $user_id ) {

		// Check for nonce otherwise bail
		if ( ! isset( $_POST['_trilisting_user_avatar_nonce'] ) || ! wp_verify_nonce( $_POST['_trilisting_user_avatar_nonce'], 'trilisting_user_avatar_nonce' ) ) {
			return;
		}

		if ( ! empty( $_FILES['trilisting-user-avatar']['name'] ) ) {

			// Allowed file extensions/types
			$mimes = [
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
			];

			// Front end support - shortcode, bbPress, etc
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			// Delete old images if successful
			trilisting_avatar_delete( $user_id );

			// Need to be more secure since low privelege users can upload
			if ( strstr( $_FILES['trilisting-user-avatar']['name'], '.php' ) ) {
				wp_die( 'For security reasons, the extension ".php" cannot be in your file name.' );
			}

			// Make user_id known to unique_filename_callback function
			$this->user_id_being_edited = $user_id; 
			$avatar = wp_handle_upload(
				$_FILES['trilisting-user-avatar'],
				[
					'mimes'                    => $mimes,
					'test_form'                => false,
					'unique_filename_callback' => [ $this, 'unique_filename_callback' ],
				]
			);

			// Handle failures
			if ( empty( $avatar['file'] ) ) {
				switch ( $avatar['error'] ) {
				case 'File type does not meet security guidelines. Try another.' :
					add_action( 'user_profile_update_errors', create_function( '$a', '$a->add("avatar_error",__("Please upload a valid image file for the avatar.","trilisting-user-avatars"));' ) );
					break;
				default :
					add_action( 'user_profile_update_errors', create_function( '$a', '$a->add("avatar_error","<strong>".__("There was an error uploading the avatar:","trilisting-user-avatars")."</strong> ' . esc_attr( $avatar['error'] ) . '");' ) );
				}
				return;
			}

			// Save user information (overwriting previous)
			update_user_meta( $user_id, 'trilisting_user_avatar', [ 'full' => $avatar['url'] ] );

		} elseif ( ! empty( $_POST['trilisting-user-avatar-erase'] ) ) {
			// Nuke the current avatar
			trilisting_avatar_delete( $user_id );
		}
	}

	/**
	 * Remove the custom get_avatar hook for the default avatar list output on 
	 * the Discussion Settings page.
	 *
	 * @since 1.0.0
	 * @param array $avatar_defaults
	 * @return array
	 */
	public function avatar_defaults( $avatar_defaults ) {
		remove_action( 'get_avatar', [ $this, 'get_avatar' ] );
		return $avatar_defaults;
	}

	/**
	 * File names are magic
	 *
	 * @since 1.0.0
	 * @param string $dir
	 * @param string $name
	 * @param string $ext
	 * @return string
	 */
	public function unique_filename_callback( $dir, $name, $ext ) {
		$user   = get_user_by( 'id', (int) $this->user_id_being_edited );
		$name   = $base_name = sanitize_file_name( $user->display_name . '_avatar' );
		$number = 1;

		while ( file_exists( $dir . "/$name$ext" ) ) {
			$name = $base_name . '_' . $number;
			$number++;
		}

		return $name . $ext;
	}
}

Trilisting_User_Avatars::instance();
