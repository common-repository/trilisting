<?php
/*
 * Frontend
 */

namespace TRILISTING\Frontend;

use WP_Query;
use TRILISTING\Trilisting_Info;
use TRILISTING\Trilisting_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Trilisting_Dashbord {
	/**
	 * Dashboard message.
	 *
	 * @access private
	 * @var string
	 */
	private $listing_dashboard_message = '';

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

	public function __construct() {
		add_filter( 'acf/pre_save_post' , [ $this, 'pre_save_post' ] );
		add_filter( 'ajax_query_attachments_args', [ $this, 'filter_media' ] );
		add_shortcode( 'trilisting_submit_form', [ $this, 'submit_form' ] );
		add_shortcode( 'trilisting_dashboard', [ $this, 'dashboard' ] );
		add_shortcode( 'trilisting_user_form', [ $this, 'user_form' ] );
	}

	/**
	 * This filter insures users only see their own media
	 */
	public function filter_media( $query ) {
		// admins get to see everything
		if ( ! current_user_can( 'manage_options' ) ) {
			$query['author'] = get_current_user_id();
		}

		return $query;
	}

	/**
	 * @since 1.0.0
	 * @param $atts
	 * @return string
	 */
	public function user_form( $atts ) {
		$a = shortcode_atts( [
			'field_group' => '',
		], $atts );

		$plugin_slug = Trilisting_Info::SLUG;
		wp_enqueue_script( 'jquery-validate' );
		wp_enqueue_script( $plugin_slug . '-user-form' );

		$uid = get_current_user_id();

		if ( ! empty ( $uid ) ) {
			$fields_groups = \TRILISTING\DB\Trilisting_DB_Query::get_user_profile_fields( $uid );
			if ( function_exists( 'acf_form' ) ) {
				$options = [
					'id'                 => 'trilisting-user-form',
					'post_id'            => 'user_' . $uid,
					'field_groups'       => $fields_groups,
					'form_attributes'    => [ 'enctype' => "multipart/form-data" ],
					'html_before_fields' => $this->get_user_form_html( $uid ),
					'return'             => add_query_arg( 'updated', 'true' ),
				];
			}

			$form = '<div class="trilisting-user-ptofile-wrap">';

			ob_start();

			do_action( 'trilisting/frontend/user/edit_form_before', $uid );

			if ( function_exists( 'acf_form' ) ) {
				acf_form( $options );
			}

			do_action( 'trilisting/frontend/user/edit_form_after', $uid );

			$form .= ob_get_contents();
			$form .= '</div>';

			ob_end_clean();
		} // End if
		
		return $form;
	}

	/**
	 * @since 1.0.0
	 * @param $user_id
	 * @return string
	 */
	public function get_user_form_html( $user_id ) {
		ob_start();
		?>
		<h3 class="trilisting-user-profile-title"><?php esc_html_e( 'My profile', 'trilisting' ); ?></h3>
		<p class="trilisting-form-username acf-input-wrap">
			<label for="trilisting-first-name"><?php esc_html_e( 'First Name', 'trilisting' ); ?></label>
			<input class="text-input trilisting-first-name" name="first-name" type="text" id="trilisting-first-name" value="<?php echo get_the_author_meta( 'first_name', $user_id ); ?>" />
		</p><!-- .trilisting-form-username -->
		<p class="trilisting-form-username acf-input-wrap">
			<label for="trilisting-last-name"><?php esc_html_e( 'Last Name', 'trilisting' ); ?></label>
			<input class="text-input trilisting-last-name" name="last-name" type="text" id="trilisting-last-name" value="<?php echo get_the_author_meta( 'last_name', $user_id ); ?>" />
		</p><!-- .trilisting-form-last-name -->
		<p class="trilisting-form-username acf-input-wrap">
			<label for="trilisting-nickname"><?php esc_html_e( 'Nickname', 'trilisting' ); ?></label>
			<input class="text-input trilisting-nickname" name="nickname" type="text" id="trilisting-nickname" value="<?php echo get_the_author_meta( 'nickname', $user_id ); ?>" />
		</p><!-- .trilisting-form-nickname -->
		<p class="trilisting-form-username acf-input-wrap">
			<label for="trilisting-display_name"><?php esc_html_e( 'Display name', 'trilisting' ); ?></label>
			<input class="text-input trilisting-display-name" name="display_name" type="text" id="trilisting-display_name" value="<?php echo get_the_author_meta( 'display_name', $user_id ); ?>" />
		</p><!-- .trilisting-form-display_name -->
		<p class="trilisting-form-email acf-input-wrap">
			<label for="trilisting-email"><?php esc_html_e( 'E-mail *', 'trilisting' ); ?></label>
			<input class="text-input trilisting-email" name="email" type="text" id="trilisting-email" value="<?php echo get_the_author_meta( 'user_email', $user_id ); ?>" />
		</p><!-- .trilisting-form-email -->
		<p class="trilisting-form-url acf-input-wrap">
			<label for="trilisting-url"><?php esc_html_e( 'Website', 'trilisting' ); ?></label>
			<input class="text-input trilisting-url" name="url" type="text" id="trilisting-url" value="<?php echo get_the_author_meta( 'user_url', $user_id ); ?>" />
		</p><!-- .trilisting-form-url -->
		<p class="trilisting-form-password acf-input-wrap">
			<label for="trilisting-pass1"><?php esc_html_e( 'Password *', 'trilisting' ); ?></label>
			<input class="text-input trilisting-pass1" name="pass1" type="password" id="trilisting-pass1" />
		</p><!-- .trilisting-form-password -->
		<p class="trilisting-form-password acf-input-wrap">
			<label for="trilisting-pass2"><?php esc_html_e( 'Repeat Password *', 'trilisting' ); ?></label>
			<input class="text-input trilisting-pass2" name="pass2" type="password" id="trilisting-pass2" />
		</p><!-- .trilisting-form-password -->
		<p class="trilisting-form-textarea acf-input-wrap">
			<label for="trilisting-description"><?php esc_html_e( 'Biographical Information', 'trilisting') ?></label>
			<textarea name="description" id="trilisting-description" rows="3" cols="50"><?php echo get_the_author_meta( 'description', $user_id ); ?></textarea>
		</p><!-- .trilisting-form-user-avatar -->
		<div class="trilisting-form-user-avatar acf-input-wrap">
			<label for="trilisting-user-avatar"><?php esc_html_e( 'User avatar', 'trilisting') ?></label>
			<div class="trilisting-avatar-section-wrap">
				<div class="trilisting-avatar-left-section">
					<?php echo get_avatar( $user_id, '150' ); ?>
				</div>
				<div class="trilisting-avatar-right-section">
					<input type="file" name="trilisting-user-avatar" id="trilisting-user-avatar" />
					<label for="trilisting-user-avatar-delete" class="trilisting-user-del-avatar-label trilisting-user-del-wrap">
						<?php esc_html_e( 'Delete avatar', 'trilisting' ); ?>
						<input id="trilisting-user-avatar-delete" class="trilisting-user-avatar-delete-btn" type="checkbox" name="trilisting-user-avatar-delete" value="1" />
					</label>
				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
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
		global $current_user;
		$number = 1;
		$user   = get_user_by( 'id', (int) $current_user->ID );
		$name   = $base_name = sanitize_file_name( $user->display_name . '_avatar' );

		while ( file_exists( $dir . "/$name$ext" ) ) {
			$name = $base_name . '_' . $number;
			$number++;
		}

		return $name . $ext;
	}

	/**
	 * @since 1.0.0
	 * @param $post_id
	 * @return mixed
	 */
	public function pre_save_post( $post_id ) {
		if ( substr( $post_id, 0 , 5 ) === 'user_' ) {
			/* Get user info. */
			global $current_user, $wp_roles;

			$error = [];
			/* If profile was saved, update profile. */
			if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
				$update_user = [
					'ID' => $current_user->ID,
				];
				/* Update user password. */
				if ( ! empty( $_POST['pass1'] ) && ! empty( $_POST['pass2'] ) ) {
					if ( $_POST['pass1'] == $_POST['pass2'] ) {
						$update_user['user_pass'] = esc_attr( $_POST['pass1'] );
					} else {
						$error[] = esc_html__( 'The passwords you entered do not match.  Your password was not updated.', 'trilisting' );
					}
				}

				/* Update user information. */
				if ( ! empty( $_POST['url'] ) ) {
					$update_user['user_url'] = esc_url( $_POST['url'] );
				}
				if ( ! empty( $_POST['nickname'] ) ) {
					$update_user['nickname'] = esc_attr( $_POST['nickname'] );
				}
				if ( ! empty( $_POST['display_name'] ) ) {
					$update_user['display_name'] = esc_attr( $_POST['display_name'] );
				}

				if ( ! empty( $_POST['email'] ) ) {
					if ( ! is_email( esc_attr( $_POST['email'] ) ) ) {
						$error[] = esc_html__( 'The Email you entered is not valid.  please try again.', 'trilisting' );
					} elseif ( email_exists( esc_attr( $_POST['email'] ) ) != $current_user->id ) {
						$error[] = esc_html__( 'This email is already used by another user.  try a different one.', 'trilisting' );
					} else {
						$update_user['user_email'] = esc_attr( $_POST['email'] );
					}
				}

				if ( ! empty( $update_user ) ) {
					wp_update_user( $update_user );
				}
				if ( ! empty( $_POST['first-name'] ) ) {
					update_user_meta( $current_user->ID, 'first_name', esc_attr( $_POST['first-name'] ) );
				}
				if ( ! empty( $_POST['last-name'] ) ) {
					update_user_meta( $current_user->ID, 'last_name', esc_attr( $_POST['last-name'] ) );
				}
				if ( ! empty( $_POST['description'] ) ) {
					update_user_meta( $current_user->ID, 'description', esc_attr( $_POST['description'] ) );
				}
				if ( isset( $_POST['trilisting-user-avatar-delete'] ) && ! empty( $_POST['trilisting-user-avatar-delete'] ) ) {
					trilisting_avatar_delete( $current_user->ID );
				} elseif ( ! empty( $_FILES['trilisting-user-avatar']['name'] ) ) {
					// Allowed file extensions/types
					$mimes = [
						'jpg|jpeg|jpe' => 'image/jpeg',
						'gif'          => 'image/gif',
						'png'          => 'image/png',
					];

					// Need to be more secure since low privelege users can upload
					if ( strstr( $_FILES['trilisting-user-avatar']['name'], '.php' ) ) {
						wp_die( 'For security reasons, the extension ".php" cannot be in your file name.' );
					}

					// Front end support - shortcode, bbPress, etc
					if ( ! function_exists( 'wp_handle_upload' ) ) {
						require_once ABSPATH . 'wp-admin/includes/file.php';
					}

					$avatar = wp_handle_upload( $_FILES['trilisting-user-avatar'],
						[
							'mimes'                    => $mimes,
							'test_form'                => false,
							'unique_filename_callback' => [ $this, 'unique_filename_callback' ],
						]
					);

					// Handle failures
					if ( empty( $avatar['file'] ) ) {
					} else {
						update_user_meta( $current_user->ID, 'trilisting_user_avatar', [ 'full' => $avatar['url'] ] );
					}
				}

				if ( 0 == count( $error ) ) {
					//action hook for plugins and extra fields saving
					do_action( 'edit_user_profile_update', $current_user->ID );
				}
			} // End if

			return $post_id;
		} // End if

		$post_status = 'pending';
		if ( current_user_can( 'edit_others_posts' ) || true == get_trilisting_option( 'allow_publish_posts' ) ) {
			$post_status = 'publish';
		}

		$post = [
			'post_status'  => $post_status,
			'post_title'   => wp_strip_all_tags( $_POST['listing_title'] ),
			'post_content' => isset( $_POST['trilisting_frontend_editor'] ) ? $_POST['trilisting_frontend_editor'] : '',
		];

		// check if this is to be a new post
		if ( 'new' === $post_id ) {
			$post['post_type'] = isset( $_POST['trilisting_post_type_submit'] ) ? $_POST['trilisting_post_type_submit'] : 'post';
			$post    = apply_filters( 'trilisting/frontend/insert/post_atts', $post );
			$post_id = wp_insert_post( $post );
		} else {
			$post['ID']        = $post_id;
			$post['post_type'] = get_post_type( $post_id );
			$post_id = wp_update_post( $post );
		}

		if ( $post_id ) {
			$_POST['return'] = get_permalink( $post_id );
		}

		if ( $post_id ) {
			$thumbnail_id = isset( $_POST['trilisting-upload-featured-image'] ) ? absint( $_POST['trilisting-upload-featured-image'] ) : '';

			if ( $thumbnail_id ) {
				set_post_thumbnail( $post_id, $thumbnail_id );
			}

			if ( isset( $_POST['trilisting_form_add_gallery'] ) ) {
				update_post_meta( $post_id, '_tril_post_gallery', $_POST['trilisting_form_add_gallery'] );
			}
		}

		// return the new ID
		return $post_id;
	}

	/**
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public function get_html_additional( $atts = [] ) {
		$post_type = isset( $atts['post_type'] ) ? $atts['post_type'] : '';
		$post_id   = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : '';

		$active_class   = '';
		$post_title     = '';
		$thumbnail_id   = '';
		$gallery_edit   = '';
		$thumbnail_url  = '#';
		$content_editor = '';
		if ( ! empty( $post_id ) ) {
			$post_obj       = get_post( $post_id );
			$post_title     = $post_obj->post_title;
			$content_editor = $post_obj->post_content;
			$thumbnail_id   = get_post_thumbnail_id( $post_id );
			$thumbnail_url  = get_the_post_thumbnail_url( $post_id, 'trilisting-gallery-preview' );
			$gallery_attach = get_post_meta( $post_id, '_tril_post_gallery', true );

			if ( ! empty( $thumbnail_id ) ) {
				$active_class = ' active has-value';
			}

			if ( ! empty( $gallery_attach ) ) {
				foreach ( $gallery_attach as $attach_id ) {
					$attach_url = wp_get_attachment_image_url( $attach_id, 'trilisting-gallery-preview' );
					$gallery_edit .= '<div class="trilisting-gallery-attachment" data-id="' . $attach_id . '">';
					$gallery_edit .= '<input type="hidden" value="' . $attach_id . '" name="trilisting_form_add_gallery[]">';
					$gallery_edit .= '<div class="margin"><div class="thumbnail"><img src="' . esc_url( $attach_url ) . '" alt="" title=""></div></div>';
					$gallery_edit .= '<div class="actions"><a href="#" class="acf-icon trilisting-gallery-remove acf-button-delete acf-icon -cancel dark" data-id="' . $attach_id . '"></a></div>';
					$gallery_edit .= '</div>'; // End acf-gallery-attachment
				}
			}
		}

		$btn_go_back = '';
		if ( isset( $atts['go_back_btn'] ) && $atts['go_back_btn'] ) {
			$btn_go_back = '<a class="trilisting-btn trilisting-return-back" href="javascript:history.go(-1)">' . esc_html__( 'Return back', 'trilisting' ) . '</a>';
		}

		$gallery_output = '';
		if ( 1 == get_trilisting_option( 'enable_gallery_listing' ) ) {
			// Gallery
			$gallery_output = '<div class="acf-field acf-field-gallery">';
			$gallery_output .= '<div class="acf-label "><label for="trilisitng-form-add-gallery">' . esc_html__( 'Gallery', 'trilisting' ) . '</label></div>';
			$gallery_output .= '<div class="acf-input">';
			$gallery_output .= '<div id="trilisitng-form-add-gallery" class="acf-gallery ui-resizable" style="height:400px">';

			$gallery_output .= '<div class="acf-gallery-main">';
			$gallery_output .= '<div class="acf-gallery-attachments ui-sortable trilisting-gallery-attachments">' . $gallery_edit . '</div>';
			$gallery_output .= '<div class="acf-gallery-toolbar"><ul class="acf-hl">';
			$gallery_output .= '<li><a href="#" class="trilisting-form-gallery-js trilisting-frontend-button">' . esc_html__( 'Add to gallery', 'trilisting' ) . '</a></li>';
			$gallery_output .= '</ul></div>';
			$gallery_output .= '</div>'; // end .acf-gallery-main

			$gallery_output .= '</div>'; // end #trilisitng-form-add-gallery
			$gallery_output .= '</div>';
			$gallery_output .= '</div>';
		}

		$output = '<div class="trilisting-form-main-block acf-field field acf_postbox">';
		if ( class_exists( 'acf' ) ) {
			$editor_id = 'trilisting_frontend_editor';
			$settings  = apply_filters(
				'trilisting/frontend/form/wp_editor/settings',
				[
					'wpautop'       => true,
					'media_buttons' => false,
					'textarea_name' => $editor_id,
					'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
					'tabindex'      => '',
					'editor_css'    => '',
					'editor_class'  => '',
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => true,
					'quicktags'     => true,
				]
			);

			$output .= $btn_go_back;

			// Title
			if ( isset( $atts['title'] ) ) {
				$output .= '<h3 class="trilisting-add-listing-title">' . $atts['title'] . '</h3>';
			}

			$output .= '<p class="label acf-input-wrap trilisting-add-listing-field-title acf-label"><label for="trilisting-listing-title">' . esc_html__( 'Title*', 'trilisting' ) . '</label>';
			$output .= '<input id="trilisting-listing-title" type="text" class="input-text" name="listing_title" value="' . esc_attr( $post_title ) . '" required />';
			$output .= '<input type="hidden" name="trilisting_post_type_submit" value="' . $post_type . '"/>';
			$output .= '</p>';

			// Featured image
			$output .= '<div class="acf-field acf-field-image"><div class="acf-input">';
			$output .= '<p class="label acf-label acf-input-wrap "><label for="trilisting-frontend-button">' . esc_html__( 'Featured Image', 'trilisting' ) . '</label></p>';
			$output .= '<div class="acf-image-uploader trilisting-uppload-image-ft' . $active_class . '">';
			$output .= '<div class="show-if-value image-wrap">';
			$output .= '<input type="hidden" value="' . absint( $thumbnail_id ) . '" name="trilisting-upload-featured-image">';
			$output .= '<img id="trilisting-frontend-image" data-name="image" src="' . esc_url( $thumbnail_url ) . '" alt="">';
			$output .= '<div class="acf-actions -hover">';
			$output .= '<a class="acf-icon -cancel dark trilisting-button-delete" data-name="remove" href="#" title="Remove"></a>';
			$output .= '</div></div>';
			$output .= '<p class="trilisting-featured-btn-wrap">' . esc_html__( 'No image selected ', 'trilisting' ) . ' <input id="trilisting-frontend-button" type="button" value="' . esc_html__( 'Add image', 'trilisting' ) . '" class="button"></p>';
			$output .= '</div></div></div>';

			// Content
			$output .= '<p class="label acf-input-wrap trilisting-add-listing-field-content acf-label"><label for="trilisting_frontend_editor">' . esc_html__( 'Description', 'trilisting' ) . '</label></p>';
			ob_start();
			wp_editor( $content_editor, $editor_id, $settings );
			$output .= ob_get_clean();

			$output .= $gallery_output;
		} // End if

		$output .= '</div>';

		return $output;
	}

	/**
	 * Returns the form content.
	 *
	 * @since 1.0.0
	 * @param string $form_name
	 * @param array  $atts Optional passed attributes
	 * @return string|null
	 */
	public function get_form( $form_name, $atts = [] ) {
		switch ( $form_name ) {
			case 'submit-listing' :
				if ( class_exists( 'acf' ) ) {
					$post_status = 'pending';
					if ( current_user_can( 'edit_others_posts' ) || true == get_trilisting_option( 'allow_publish_posts' ) ) {
						$post_status = 'publish';
					}

					$arg_acf = apply_filters( 'trilisting/acf/form/arg', [
						'post_id'      => 'new_post',
						'field_groups' => ! is_array( $atts['field_groups'] ) ? explode( ',', $atts['field_groups'] ) : $atts['field_groups'],
						'post_title'   => false,
						'post_content' => false,
						'html_before_fields' => $this->get_html_additional( $atts ),
						'return'       => '%post_url%',
						'new_post'     => [
							'post_type'   => $atts['post_type'],
							'post_status' => $post_status,
						],
						'submit_value' => esc_html__( 'Create', 'trilisting' ),
					] );

					acf_form( $arg_acf );
				}
				break;
			case 'edit-listing' :
				echo '<div class="trilisting-edit-listing-form">';
				if ( class_exists( 'acf' ) ) {
					$arg_acf_edit = [
						'post_title'         => false,
						'post_content'       => false,
						'return'             => '%post_url%',
						'post_id'            => isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : '',
						'submit_value'       => esc_html__( 'Update', 'trilisting' ),
						'html_before_fields' => $this->get_html_additional( $atts ),
					];

					acf_form( $arg_acf_edit );
				}
				echo '</div>';
			break;
		} // End switch
	}

	/**
	 * Displays edit form.
	 * 
	 * @since 1.0.0
	 */
	public function edit_form() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$atts = [
			'go_back_btn' => true,
		];

		if ( ! isset( $atts['title'] ) ) {
			$atts['title'] = esc_html__( 'Update listing', 'trilisting' );
		}

		return $this->get_form( 'edit-listing', $atts );
	}

	/**
	 * Shows submission form.
	 *
	 * @since 1.0.0
	 * @param array $atts
	 * @return string|null
	 */
	public function submit_form( $atts = [] ) {
		if ( ! is_user_logged_in() ) {
			ob_start();
			Trilisting_Helpers::get_manager_template( TRILISTING_PATH_TEMPLATES . 'dashboard-login.tpl.php' );
			return ob_get_clean();
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$atts = shortcode_atts( [
			'field_groups' => '',
			'post_type'    => '',
		], $atts );

		$output = '';
		$output .= '<div id="trilisting-frontend-submitform" class="trilisting-submit-form-wrap">';

		$key_groups = \TRILISTING\DB\Trilisting_DB_Query::get_group_fields();
		$key_groups = $key_groups['user'];

		if ( isset( $_GET['post_form'] ) ) {
			$current_post_type = $_GET['post_form'];
		} elseif ( isset( $atts['post_type'] ) && ! empty( $atts['post_type'] ) ) {
			$current_post_type = $atts['post_type'];
		} else {
			$post_types        = trilisting_enable_post_types();
			$current_post_type = isset( $post_types[0] ) ? $post_types[0] : 'post';
		}

		if ( ! isset( $atts['post_type'] ) && empty( $atts['post_type'] ) ) {
		} else {
			$atts['post_type'] = $current_post_type;
		}

		$field_groups = [];
		if ( isset( $key_groups[ $current_post_type ] ) && ! empty( $key_groups[ $current_post_type ] ) ) {
			foreach ( $key_groups[ $current_post_type ] as $value ) {
				$field_groups[] = $value;
			}
		}

		if ( is_array( $field_groups ) ) {
			$field_groups = implode( ',', $field_groups );
		}

		$atts['field_groups'] = $field_groups;

		if ( ! isset( $atts['title'] ) ) {
			$atts['title'] = esc_html__( 'Add listing', 'trilisting' );
		}

		ob_start();

		$this->get_form( 'submit-listing', $atts );

		$output .= ob_get_clean();
		$output .= '</div>'; // #trilisting-frontend-submitform

		return $output;
	}

	/**
	 * Handles shortcode which lists the logged in user's.
	 *
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public function dashboard( $atts ) {
		if ( ! is_user_logged_in() ) {
			ob_start();
			Trilisting_Helpers::get_manager_template( TRILISTING_PATH_TEMPLATES . 'dashboard-login.tpl.php' );
			return ob_get_clean();
		}

		extract( shortcode_atts( [
			'posts_per_page' => '20',
		], $atts ) );

		ob_start();

		$tpl_args = [];
		if ( current_user_can( 'edit_posts' ) ) {
			$en_post_type = trilisting_enable_post_types();

			$args = apply_filters( 'trilisting/frontend/dashboard/args', [
				'post_type'           => $en_post_type,
				'post_status'         => [ 'publish', 'pending' ],
				'ignore_sticky_posts' => 1,
				'orderby'             => 'date',
				'order'               => 'desc',
				'posts_per_page'      => $posts_per_page,
				'author'              => get_current_user_id(),
				'offset'              => ( max( 1, get_query_var('paged') ) - 1 ) * $posts_per_page,
			] );

			$trilisting_query  = new WP_Query;
			$dashboard_columns = apply_filters( 'trilisting/frontend/dashboard/columns', [
					'featured_image'    => esc_html__( 'Featured image', 'trilisting' ),
					'post_title'        => esc_html__( 'Title', 'trilisting' ),
					'dashboard_actions' => esc_html__( 'Dashboard actions', 'trilisting' ),
				]
			);

			$post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : '';
			if ( ! empty( $_REQUEST['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'trilisting_actions_delete_listing' . $post_id ) ) {
				$action  = sanitize_title( $_REQUEST['action'] );

				try {
					$post_obj = get_post( $post_id );

					// Check ownership
					if ( ! trilisting_user_can_edit_listing( $post_id ) ) {
						throw new Exception( esc_html__( 'Invalid ID', 'trilisting' ) );
					}

					switch ( $action ) {
						case 'edit' :
							if ( trilisting_user_can_edit_published_submissions() || trilisting_user_can_edit_pending_submissions() ) {
								Trilisting_Helpers::get_manager_template( TRILISTING_PATH_TEMPLATES . 'edit-listing-form.tpl.php' );
							}
							return;
							break;
						case 'delete' :
							wp_trash_post( $post_id );
							$this->listing_dashboard_message = '<div class="trilisting-deleta-message">' . sprintf( esc_html__( '%s has been deleted', 'trilisting' ), $post_obj->post_title ) . '</div>';
							break;
						default :
							do_action( 'trilisting/frontend/dashboard/do_action_' . $action, $post_id );
							break;
					}

					do_action( 'trilisting/frontend/dashboard/do_action', $action, $post_id );

				} catch ( Exception $e ) {
					$this->listing_dashboard_message = '<div class="trilisting-error-message">' . $e->getMessage() . '</div>';
				}
			} // End if

			$tpl_args = [
				'query_args'        => $args,
				'trilisting_query'  => $trilisting_query,
				'dashboard_columns' => $dashboard_columns,
				'enable_post_type'  => $en_post_type,
			];
		} // End if

		Trilisting_Helpers::get_manager_template( TRILISTING_PATH_TEMPLATES . 'dashboard.tpl.php', $tpl_args );

		return ob_get_clean();
	}
}
