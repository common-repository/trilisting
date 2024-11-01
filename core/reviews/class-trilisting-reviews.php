<?php

namespace TRILISTING;

class Trilisting_Reviews {
	/**
	 * Options enable reting post types.
	 */
	private $_opt_post_types = '';
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
	 * @since 1.0.0
	 */
	public function add_rating_field() {
		global $post;
		$uid = get_current_user_id();

		if ( ! current_user_can( 'administrator' ) && (int) $uid !== (int) $post->post_author && trilisting_check_insert_rating() ) {
		?>
			<div class="trilisting-rating-wrap">
				<label class="trilisting-rating-label" for="rating-0"><?php esc_html_e( 'Rating', 'trilisting' ); ?><span class="required">*</span></label>
				<div class="trilisting-comments-rating">
					<span class="trilisting-rating-container">
						<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
							<input type="radio" id="rating-<?php echo esc_attr( $i ); ?>" name="tril_rating" value="<?php echo esc_attr( $i ); ?>" /><label for="rating-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></label>
						<?php endfor; ?>
						<input type="radio" id="rating-0" class="tril-star-clear" name="rating" value="0" /><label for="rating-0">0</label>
					</span>
				</div>
			</div>
		<?php
		}
	}

	/**
	 * @since 1.0.0
	 */
	public function save_comment_rating( $comment_id ) {
		if ( ( isset( $_POST['tril_rating'] ) ) && ( '' !== $_POST['tril_rating'] ) ) {
			$rating = intval( $_POST['tril_rating'] );	
			add_comment_meta( $comment_id, 'tril_rating', $rating );
		}
	}

	/**
	 * @since 1.0.0
	 */
	public function require_rating( $commentdata ) {
		$post = get_post( $commentdata['comment_post_ID'] );
		$uid  = get_current_user_id();

		if (
			! current_user_can( 'administrator' )
			&& (int) $uid !== (int) $post->post_author
			&& trilisting_check_insert_rating( $post->post_type )
			&& ! is_admin()
			&& ( ! isset( $_POST['tril_rating'] ) || 0 === intval( $_POST['tril_rating'] ) )
		) {
			wp_die( esc_html__( 'Error: You did not add a rating. Hit the Back button on your Web browser and resubmit your comment with a rating.', 'trilisting' ) );
		}

		return $commentdata;
	}

	/**
	 * @since 1.0.0
	 */
	public function display_rating( $comment_text ) {
		$is_comment_text = apply_filters( 'trilisting/action/rating/comment_text', true );

		if ( $is_comment_text ) {
			return trilisting_display_rating( $comment_text );
		}

		return $comment_text;
	}
}
