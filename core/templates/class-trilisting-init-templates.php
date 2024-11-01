<?php

namespace TRILISTING;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Init templates.
 */
class Trilisting_Init_Templates {
	private $options;

	public function __construct() {
		$this->options = trilisting_enable_post_types();

		add_filter( 'archive_template', [ &$this, '_archive_template' ] );
		$this->content_filter( true );
	}

	/**
	 * Toggles content filter on and off.
	 *
	 * @since 1.0.0
	 * @param bool $enable
	 */
	private function content_filter( $enable ) {
		if ( ! $enable ) {
			remove_filter( 'the_content', [ $this, '_the_content' ] );
		} else {
			add_filter( 'the_content', [ $this, '_the_content' ] );
		}
	}

	/**
	 * Adds extra content before/after the post for single listings.
	 *
	 * @since 1.0.0
	 * @param string $content
	 * @return string
	 */
	public function _the_content( $content ) {
		global $post;

		$support_post_type = $this->options;
		trilisting_set_post_views( get_the_ID() );

		if ( ! in_the_loop() || empty( $support_post_type ) ) {
			return $content;
		}

		foreach ( $support_post_type as $post_type ) {
			if ( is_singular( $post_type ) || $post_type === $post->post_type  ) {
				ob_start();

				$this->content_filter( false );

				do_action( 'trilisting/single/content_start' );

				Trilisting_Helpers::get_template_part( 'single', 'directory-listing' );

				do_action( 'trilisting/single/content_end' );

				$this->content_filter( true );
		
				return apply_filters( 'trilisting/single/content', ob_get_clean(), $post );
			}
		}

		return $content;
	}

	/**
	 * @since 1.0.0
	 * @param $template
	 * @return string
	 */
	public function _archive_template( $template ) {
		$support_post_type = $this->options;

		if ( is_archive() && ! empty( $support_post_type ) ) {
			foreach ( $support_post_type as $post_type ) {
				if ( $post_type === get_post_type() ) {
					$template_file = locate_template( "trilisting-templates/archive-{$post_type}.tpl.php" );

					if ( file_exists( $template_file ) ) {
						return $template_file;
					} else {
						if ( file_exists(  dirname( __FILE__ ) . '/archive.tpl.php' ) ) {
							return dirname( __FILE__ ) . '/archive.tpl.php';
						}
					}
				}
			}
		}

		return $template;
	}

}

(new Trilisting_Init_Templates());
