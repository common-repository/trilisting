<?php

namespace TRILISTING;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Trilisting_Sidebars {

	/**
	 * Filter the `$before_widget` widget opening HTML tag.
	 *
	 * @since 1.0.0
	 * @param string $var The HTML string to filter. Default = '<div id="%1$s" class="widget %2$s"><div class="trilisting-block">'.
	 * @see 'trilisting/sidebar/before_widget'
	 */
	protected $before_widget;

	/**
	 * Filter the `$after_widget` widget closing HTML tag.
	 *
	 * @since 1.0.0
	 * @param string $var The HTML string to filter. Default = '</div></div>'.
	 * @see 'trilisting/sidebar/after_widget'
	 */
	protected $after_widget;

	/**
	 * Filter the `$before_title` widget title opening HTML tag.
	 *
	 * @since 1.0.0
	 * @param string $var The HTML string to filter. Default = '<div class="trilisting-block-title"><h4 class="trilisting-title widget-title">'.
	 * @see 'trilisting/sidebar/before_title'
	 */
	protected $before_title;

	/**
	 * Filter the `$after_title` widget title closing HTML tag.
	 *
	 * @since 1.0.0
	 * @param string $var The HTML string to filter. Default = '</h4></div>'.
	 * @see 'trilisting/sidebar/after_title'
	 */
	protected $after_title;

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	public static $instance;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 * 
	 * @static
	 * 
	 * @since 1.0.0
	 * @return self Main instance.
	 */
	public static function init() {
		if ( self::$instance == NULL ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->before_widget = apply_filters( 'trilisting/sidebar/before_widget', '<div id="%1$s" class="widget %2$s"><div class="trilisting-block">' );
		$this->after_widget  = apply_filters( 'trilisting/sidebar/after_widget', '</div></div>' );
		$this->before_title  = apply_filters( 'trilisting/sidebar/before_title', '<div class="trilisting-block-title"><h4 class="trilisting-title widget-title">' );
		$this->after_title   = apply_filters( 'trilisting/sidebar/after_title', '</h4></div>' );
	}

	/**
	 * Register sidebars.
	 * 
	 * @static
	 * 
	 * @since 1.0.0
	 */
	public function register_sidebars() {
		if ( function_exists( 'register_sidebar' ) ) {
			register_sidebar( [
				'name'          => esc_html__( 'triListing Place Top', 'trilisting' ),
				'id'            => 'trilisting-single-top',
				'description'   => esc_html__( 'Only single.', 'trilisting' ),
				'before_widget' => $this->before_widget,
				'after_widget'  => $this->after_widget,
				'before_title'  => $this->before_title,
				'after_title'   => $this->after_title,
			] );

			register_sidebar( [
				'name'          => esc_html__( 'triListing Place Bottom', 'trilisting' ),
				'id'            => 'trilisting-single-bottom',
				'description'   => esc_html__( 'Only single.', 'trilisting' ),
				'before_widget' => $this->before_widget,
				'after_widget'  => $this->after_widget,
				'before_title'  => $this->before_title,
				'after_title'   => $this->after_title,
			] );
		} // End if
	}
}
