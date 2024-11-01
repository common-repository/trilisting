<?php

namespace TRILISTING;
use TRILISTING\Walker\Trilisting_Search_Walker;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 * Search shortcode - [trilisting-search]
 */
class Trilisting_Search {
	//confirm the form was posted
	private $has_search_query = false;
	private $has_form_posted  = false;
	private $hasq_mark        = false;

	private $url_params   = '/';
	private $search_term  = '';
	private $redirect_url = '';

	private $catid    = 0;
	private $tagid    = 0;
	private $defaults = [];
	//reserved fields
	private $taxonomy_list   = [];
	private $reserved_fields = [];

	/**
	 * Trilisting_Search constructor.
	 */
	public function __construct() {
		$this->reserved_fields = [
			TRILISTING_SEARCH_PREFIX . 'category',
			TRILISTING_SEARCH_PREFIX . 'search',
			TRILISTING_SEARCH_PREFIX . 'post_tag',
			TRILISTING_SEARCH_PREFIX . 'submitted',
			TRILISTING_SEARCH_PREFIX . 'post_types',
		];
		$this->reserved_fields = apply_filters( 'trilisting/search/filter_reseved_fields', $this->reserved_fields );

		$this->frmqreserved = [
			TRILISTING_SEARCH_PREFIX . 'category_name',
			TRILISTING_SEARCH_PREFIX . 's',
			TRILISTING_SEARCH_PREFIX . 'tag',
			TRILISTING_SEARCH_PREFIX . 'submitted',
			TRILISTING_SEARCH_PREFIX . 'post_types',
		];
		$this->frmqreserved = apply_filters( 'trilisting/search/filter_frmqreserved', $this->frmqreserved );

		//add query vars
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );

		//filter post type & date if it is set
		add_filter( 'pre_get_posts', [ $this, 'filter_query_post_types' ] );

		// Add shortcode support for widgets
		add_shortcode( 'trilisting_search', [ $this, 'search_shortcode' ] );

		// Check the header to see if the form has been submitted
		add_action( 'get_header', [ $this, 'posts_check' ] );
	}

	/**
	 * @since 1.0.0
	 * @param $atts
	 * @param null $content
	 * @return string
	 */
	public function search_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( [
			'fields'                => 'search,location',
			'submit_label'          => null,
			'submitlabel'           => null,
			'taxonomies'            => null,
			'types'                 => '',
			'type'                  => '',
			'all_items_labels'      => '',
			'search_placeholder'    => esc_html__( "What are you looking for?", 'trilisting' ),
			'headings'              => '',
			'hierarchical'          => '',
			'class'                 => '',
			'post_types'            => 'trilisting_places',
			'order_by'              => '',
			'hide_empty'            => '1',
			'order_dir'             => '',
			'redirect_url'          => '',
			'operators'             => '',
			'show_count'            => '',
			'add_search_param'      => '0',
			'search_type'           => 'box',
		], $atts) );

		if ( null != $fields ) {
			$fields = explode( ',', $fields );
		} else {
			$fields = explode( ',', $taxonomies );
		}

		$this->taxonomy_list = $fields;
		$nofields = count( $fields );

		$add_search_param = (int) $add_search_param;

		if ( ! empty( $redirect_url ) ) {
			$this->redirect_url = $redirect_url;
		} else {
			$search_page_theme = get_trilisting_option( 'trilisting_placessearch_page_theme' );

			if ( ! empty( $search_page_theme ) && is_numeric( $search_page_theme ) ) {
				$this->redirect_url = get_page_link( absint( $search_page_theme ) );
			}
		}

		if ( null != $submitlabel ) {
			if ( null == $submit_label ) {
				$submit_label = $submitlabel;
			}
		} else if ( null == $submitlabel ) {
			if ( null == $submit_label ) {
				$submit_label = esc_html__( 'Submit', 'trilisting' ); 
			}
		}

		if ( '' != $post_types ) {
			$post_types = explode( ',', $post_types );
		} else {
			if ( in_array( 'post_types', $fields ) ) {
				$post_types = [ 'all' ];
			}
		}

		if ( '' != $hierarchical ) {
			$hierarchical = explode( ',', $hierarchical );
		} else {
			$hierarchical = [ '' ];
		}

		if ( '' != $hide_empty ) {
			$hide_empty = explode( ',', $hide_empty );
		} else {
			$hide_empty = [ '' ];
		}

		if ( '' != $show_count ) {
			$show_count = explode( ',', $show_count );
		} else {
			$show_count = [];
		}

		if ( '' != $order_by ) {
			$order_by = explode( ',', $order_by );
		} else {
			$order_by = [ '' ];
		}

		if ( '' != $order_dir ) {
			$order_dir = explode( ',', $order_dir );
		} else {
			$order_dir = [ '' ];
		}

		if ( '' != $operators ) {
			$operators = explode( ',', $operators );
		} else {
			$operators = [ '' ];
		}

		$labels = explode( ',', $headings );
		if ( ! is_array( $labels ) ) {
			$labels = [];
		}

		$all_items_labels = explode( ',', $all_items_labels );
		
		if ( ! is_array( $all_items_labels ) ) {
			$all_items_labels = [];
		}

		if ( null != $types ) {
			$types = explode( ',', $types );
		} else {
			$types = explode( ',', $type );
		}

		if ( ! is_array( $types ) ) {
			$types = [];
		}
		
		for ( $i = 0; $i < $nofields; $i++ ) {
			if ( isset( $types[ $i ] ) ) {
					if (
						( 'select' != $types[ $i ] ) &&
						( 'checkbox' != $types[ $i ] ) &&
						( 'radio' != $types[ $i ] ) &&
						( 'list' != $types[ $i ] ) &&
						( 'multiselect' != $types[ $i ] )
					) {
						$types[ $i ] =  'select';
					}
			} else {
				$types[ $i ] =  'select';
			}

			if ( ! isset( $labels[ $i ] ) ) {
				$labels[ $i ] = '';
			}

			if ( ! isset( $all_items_labels[ $i ] ) ) {
				$all_items_labels[$i] = '';
			}

			if ( isset( $order_by[ $i ] ) ) {
				if (
					( 'id' != $order_by[ $i ] ) &&
					( 'name' != $order_by[ $i ]) &&
					( 'slug' != $order_by[ $i ] ) &&
					( 'count' != $order_by[ $i ] ) &&
					( 'term_group' != $order_by[ $i ] )
				) {
					$order_by[ $i ] =  'name';
				}
			} else {
				$order_by[ $i ] =  'name';
			}

			if ( isset( $order_dir[ $i ] ) ) {
				if ( ( 'asc' != $order_dir[ $i ] ) && ( 'desc' != $order_dir[ $i ] ) ) {
					$order_dir[ $i ] =  'asc';
				}
			} else {
				$order_dir[ $i ] =  'asc';
			}

			if ( isset( $operators[ $i ] ) ) {
				$operators[ $i ] = strtolower( $operators[ $i ] );
				if( ( 'and' != $operators[ $i ] ) && ( 'or' != $operators[ $i ] ) ) {
					$operators[ $i ] = 'and';
				}
			} else {
				$operators[ $i ] =  "and";
			}
		} // End for
		$this->set_defaults();

		return $this->get_search_filter_form(
			$submit_label,
			$search_placeholder,
			$fields,
			$types,
			$labels,
			$hierarchical,
			$hide_empty,
			$show_count,
			$post_types,
			$order_by,
			$order_dir,
			$operators,
			$all_items_labels,
			$add_search_param,
			$class,
			$search_type
		);
	}

	/**
	 * @since 1.0.0
	 * @param $qvars
	 * @return array
	 */
	public function add_query_vars( $qvars ) {
		$qvars[] = 'post_types';
		return $qvars;
	}

	/**
	 * @since 1.0.0
	 * @param $query
	 * @return mixed
	 */
	public function filter_query_post_types( $query ) {
		global $wp_query;

		if ( ( $query->is_main_query() ) && ( ! is_admin() ) ) {
			if ( isset( $wp_query->query['post_types'] ) ) {
				$search_all = false;
				$post_types = explode( ',', esc_attr( $wp_query->query['post_types'] ) );

				if ( isset( $post_types[0] ) ) {
					if ( 1 == count( $post_types ) ) {
						if ( 'all' == $post_types[0] ) {
							$search_all = true;
						}
					}
				}
				if ( $search_all ) {
					$post_types = get_post_types( [ '_builtin' => false, ], 'names' );
					$query->set( 'post_type', $post_types );
				} else {
					$query->set( 'post_type', $post_types );
				}
			}
		} // End if

		return $query;
	}

	/**
	 * @since 1.0.0
	 */
	public function set_defaults() {
		global $wp_query;
		$categories = [];

		if ( isset( $wp_query->query['category_name'] ) ) {
			$category_params = ( preg_split("/[,\+ ]/", esc_attr( $wp_query->query['category_name'] ) ) );

			foreach ( $category_params as $category_param ) {
				$category = get_category_by_slug( $category_param );
				if ( isset( $category->cat_ID ) ) {
					$categories[] = $category->cat_ID;
				}
			}
		}

		$this->defaults[ TRILISTING_SEARCH_PREFIX . 'category' ] = $categories;

		if ( isset( $wp_query->query['s'] ) ) {
			$this->search_term = trim( get_search_query() );
		}

		$tags = [];
		if ( isset( $wp_query->query['tag'] ) ) {
			$tag_params = ( preg_split("/[,\+ ]/", esc_attr( $wp_query->query['tag'] ) ) );

			foreach ( $tag_params as $tag_param ) {
				$tag = get_term_by( 'slug', $tag_param, 'post_tag' );
				if ( isset( $tag->term_id ) ) {
					$tags[] = $tag->term_id;
				}
			}
		}

		$this->defaults[ TRILISTING_SEARCH_PREFIX . 'post_tag' ] = $tags;
		foreach ( $wp_query->query as $key => $val ) {
			if ( ! in_array( TRILISTING_SEARCH_PREFIX . $key, $this->frmqreserved ) ) {
				if ( in_array($key, $this->taxonomy_list ) ) {
					$taxslug = ( $val );
					$tax_params = ( preg_split( "/[,\+ ]/", esc_attr( $taxslug ) ) );
					$taxs = [];

					foreach ( $tax_params as $tax_param ) {
						$tax = get_term_by( 'slug', $tax_param, $key );

						if ( isset( $tax->term_id ) ) {
							$taxs[] = $tax->term_id;
						}
					}

					$this->defaults[ TRILISTING_SEARCH_PREFIX . $key ] = $taxs;
				}
			}
		} // End foreach

		$post_types = [];
		if ( isset( $wp_query->query['post_types'] ) ) {
			$post_types = explode( ',', esc_attr( $wp_query->query['post_types'] ) );
		}
		$this->defaults[ TRILISTING_SEARCH_PREFIX . 'post_types' ] = $post_types;
	}

	/**
	 * @since 1.0.0 
	 */
	public function posts_check() {
		if ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'submitted' ] ) ) {
			if ( '1' === $_GET[ TRILISTING_SEARCH_PREFIX . 'submitted' ] ) {
				$this->has_form_posted = true;
			}
		}

		$taxcount = 0;
		if ( ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'category'] ) ) && ( $this->has_form_posted ) ) {
			$the_post_cat = ( $_GET[ TRILISTING_SEARCH_PREFIX . 'category' ] );

			if ( ! is_array( $_GET[ TRILISTING_SEARCH_PREFIX . 'category' ] ) ) {
				$post_cat[] = $the_post_cat;
			} else {
				$post_cat = $the_post_cat;
			}
			$catarr = [];

			foreach ( $post_cat as $cat ) {
				$cat = esc_attr( $cat );
				$catobj = get_category( $cat );

				if ( isset( $catobj->slug ) ) {
					$catarr[] = $catobj->slug;
				}
			}

			if ( 0 < count( $catarr ) ) {
				$operator = '+';

				if ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'category_operator'] ) ) {
					if ( 'and' == strtolower( $_GET[ TRILISTING_SEARCH_PREFIX . 'category_operator'] ) ) {
						$operator = '+';
					} elseif ( 'or' == strtolower( $_GET[ TRILISTING_SEARCH_PREFIX . 'category_operator'] ) ) {
						$operator = ',';
					} else {
						$operator = '+';
					}
				}

				$categories = implode( $operator, $catarr );
				if ( get_option( 'permalink_structure' ) && ( 0 == $taxcount ) ) {
					$category_base = ( get_option( '' == 'category_base' ) ) ? 'category' : get_option( 'category_base' );
					$category_path = $category_base . '/' . $categories . '/';
					$this->url_params .= $category_path;
				} else {
					if ( !$this->hasq_mark ) {
						$this->url_params .= '?';
						$this->hasq_mark = true;
					} else {
						$this->url_params .= '&';
					}

					$this->url_params .= 'category_name=' . $categories;
				}

				$taxcount++;
			} // End if

		} // End if

		if ( ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_tag' ] ) ) && ( $this->has_form_posted ) ) {
			$the_post_tag = ( $_GET[TRILISTING_SEARCH_PREFIX . 'post_tag'] );

			if ( ! is_array( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_tag' ] ) ) {
				$post_tag[] = $the_post_tag;
			} else {
				$post_tag = $the_post_tag;
			}

			$tagarr = [];
			foreach ( $post_tag as $tag ) {
				$tag = esc_attr( $tag );
				$tagobj = get_tag( $tag );

				if ( isset( $tagobj->slug ) ) {
					$tagarr[] = $tagobj->slug;
				}
			}

			if ( 0 < count( $tagarr ) ) {
				$operator = '+';

				if ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_tag_operator' ] ) ) {
					if ( strtolower( 'and' == $_GET[ TRILISTING_SEARCH_PREFIX . 'post_tag_operator' ] ) ) {
						$operator = '+';
					} elseif ( strtolower( 'or' == $_GET[ TRILISTING_SEARCH_PREFIX . 'post_tag_operator' ] ) ) {
						$operator = ',';
					} else {
						$operator = '+';
					}
				}

				$tags = implode( $operator, $tagarr );

				if ( get_option( 'permalink_structure' ) && ( 0 == $taxcount ) ) {
					$tag_path = 'tag/' . $tags . '/';
					$this->url_params .= $tag_path;
				} else {
					if ( ! $this->hasq_mark ) {
						$this->url_params .= '?';
						$this->hasq_mark = true;
					} else {
						$this->url_params .= '&';
					}
					$this->url_params .= 'tag=' . $tags;
				}

				$taxcount++;
			} // End if
		} // End if

		if ( $this->has_form_posted ) {
			foreach ( $_GET as $key => $val ) {

				if ( ! in_array( $key, $this->reserved_fields ) ) {
					if ( 0 === strpos( $key, TRILISTING_SEARCH_PREFIX ) ) {
						$key = substr( $key, strlen( TRILISTING_SEARCH_PREFIX ) );
					}

					$the_post_tax = $val;
					$post_tax = [];

					if ( ! is_array( $the_post_tax ) ) {
						$post_tax[] = $the_post_tax;
					} else {
						$post_tax = $the_post_tax;
					}

					$taxarr = [];

					foreach ( $post_tax as $tax ) {
						$tax = esc_attr( $tax );
						$taxobj = get_term_by( 'id', $tax, $key );

						if ( isset( $taxobj->slug ) ) {
							$taxarr[] = $taxobj->slug;
						}
					}

					if ( 0 < count( $taxarr ) ) {
						$operator = '+';

						if ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . $key . '_operator' ] ) ) {
							if ( 'and' == strtolower( $_GET[TRILISTING_SEARCH_PREFIX . $key . '_operator' ] ) ) {
								$operator = '+';
							} else if ( 'or' == strtolower( $_GET[ TRILISTING_SEARCH_PREFIX . $key . '_operator' ] ) ) {
								$operator = ',';
							} else {
								$operator = '+';
							}
						}

						$taxs = implode( $operator, $taxarr );

						if ( get_option( 'permalink_structure' ) && ( 0 == $taxcount ) ) {
							$key_taxonomy = get_taxonomy( $key );

							$tax_path = $key . '/' . $taxs . '/';
							if ( ( isset( $key_taxonomy->rewrite ) ) && ( isset( $key_taxonomy->rewrite['slug'] ) ) ) {
								$tax_path = $key_taxonomy->rewrite['slug'] . '/' . $taxs . '/';
							}

							$this->url_params .= $tax_path;
						} else {
							if ( ! $this->hasq_mark ) {
								$this->url_params .= '?';
								$this->hasq_mark = true;
							} else {
								$this->url_params .= '&';
							}
							$this->url_params .= $key . '=' . $taxs;
						}

						$taxcount++;

					} // End if
				}
			} // End foreach
		} // End if

		if ( ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'search' ] ) ) && ( $this->has_form_posted ) ) {
			$this->search_term = trim( stripslashes( $_GET[ TRILISTING_SEARCH_PREFIX . 'search' ] ) );

			if ( '' != $this->search_term ) {
				if ( ! $this->hasq_mark ) {
					$this->url_params .= '?';
					$this->hasq_mark = true;
				} else {
					$this->url_params .= '&';
				}
				$this->url_params .= 's=' . urlencode( $this->search_term );
				$this->has_search_query = true;
			}
		}

		if ( ! $this->has_search_query ) {
			if ( ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'add_search_param' ] ) ) && ( $this->has_form_posted ) ) {
				if ( ! $this->hasq_mark ) {
					$this->url_params .= '?';
					$this->hasq_mark = true;
				} else {
					$this->url_params .= '&';
				}
				$this->url_params .= 's=';
			}
		}

		if ( ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] ) ) && ( $this->has_form_posted ) ) {
			$the_post_types = ( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] );
			if ( ! is_array( $the_post_types ) ) {
				$post_types_arr[] = $the_post_types;
			} else {
				$post_types_arr = $the_post_types;
			}

			$num_post_types = count( $post_types_arr );

			for ( $i = 0; $i < $num_post_types; $i++ ) {
				if ( '0' == $post_types_arr[ $i ] ) {
					$post_types_arr[ $i ] = 'all';
				}
			}

			if ( 0 < count( $post_types_arr ) ) {
				$operator = ',';

				$post_types = implode( $operator, $post_types_arr );

				if ( ! $this->hasq_mark ) {
					$this->url_params .= '?';
					$this->hasq_mark = true;
				} else {
					$this->url_params .= '&';
				}
				$this->url_params .= 'post_types=' . $post_types;

			}
		} // End if

	}

	protected function parse_redirect_page() {
		$output = '';
		$query_page = parse_url( $this->redirect_url, PHP_URL_QUERY );
		parse_str( $query_page, $output );

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $submitlabel
	 * @param $search_placeholder
	 * @param $fields
	 * @param $types
	 * @param $labels
	 * @param $hierarchical
	 * @param $hide_empty
	 * @param $show_count
	 * @param $post_types
	 * @param $order_by
	 * @param $order_dir
	 * @param $operators
	 * @param $all_items_labels
	 * @param $add_search_param
	 * @param $class
	 * @param $search_type
	 * @return string
	 */
	public function get_search_filter_form(
		$submitlabel,
		$search_placeholder,
		$fields,
		$types,
		$labels,
		$hierarchical,
		$hide_empty,
		$show_count,
		$post_types,
		$order_by,
		$order_dir,
		$operators,
		$all_items_labels,
		$add_search_param,
		$class,
		$search_type
	) {
		$output    = '';
		$add_class = '';
		if ( '' != $class ) {
			$add_class = ' ' . $class;
		}

		$action_url	= '';
		if ( ! empty( $this->redirect_url ) ) {
			$action_url = $this->redirect_url;
		} else {
			global $wp;
  			$action_url = home_url( add_query_arg( [], $wp->request ) );
		}

		if ( 'page' == $search_type ) {
			$output .= Trilisting_Helpers::do_action( 'trilisting/search/before_form' );
		}

		$output .= '<form action="' . $action_url . '" method="get" class="trilisting-search-form' . esc_attr( $add_class ) . '"><div class="trilisting-form-wrap">';

		if ( ! in_array( "post_types", $fields ) ) {
			if ( ( '' != $post_types ) && ( is_array( $post_types ) ) ) {
				foreach ( $post_types as $post_type ) {
					$output .= "<input class='trilisting-input trilisting-post-types' type=\"hidden\" name=\"" . TRILISTING_SEARCH_PREFIX . "post_types[]\" value=\"" . $post_type . "\" />";
				}
			}
		}
		$output .= '<ul class="trilisting-form-filters">';

		$i = 0;
		foreach ( $fields as $field ) {
			if ( 'search' == $field ) {
				$output .= '<li class="trilisting-cat-name trilisting-form-search">';
				if ( '' != $labels[ $i ] ) {
					$output .= '<h4 class="trilisting-cat-title">' . $labels[ $i ] . '</h4>';
				}
				$clean_search_term = isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'search' ] ) ? $_GET[ TRILISTING_SEARCH_PREFIX . 'search' ] : esc_attr( $this->search_term );
				$output .= '<input class="trilisting-input trilisting-search-input" type="text" name="' . TRILISTING_SEARCH_PREFIX . 'search" placeholder="' . esc_attr( $search_placeholder ) . '" value="' . $clean_search_term . '">';
				$output .= '</li>';
			} elseif ( 'location' == $field ) {
				$output .= '<li class="trilisting-cat-name trilisting-form-location">';
				if ( '' != $labels[ $i ] ) {
					$output .= '<h4 class="trilisting-cat-title">' . $labels[ $i ] . '</h4>';
				}
				$clean_location_term = isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'location' ] ) ? $_GET[ TRILISTING_SEARCH_PREFIX . 'location' ] : '';
				$output .= '<input id="trilisting-geocode-search" class="controls" type="text" name="' . TRILISTING_SEARCH_PREFIX . 'location" placeholder="' . esc_html__( 'Location' ) . '" value="' . $clean_location_term . '">';
				$output .= '<a id="trilisting-curent_position" href="javascript:;" class="geolocate"><svg enable-background="new 0 0 24.947 24.947" height="24.947px" id="Capa_1" version="1.1" viewBox="0 0 24.947 24.947" width="24.947px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M12.474,0C5.596,0,0,5.596,0,12.474s5.597,12.474,12.475,12.474s12.474-5.596,12.474-12.474S19.352,0,12.474,0z   M16.009,7.858c0.07,0.239,0.223,0.503,0.462,0.798l0.096,0.131c0.04,0.055,0.092,0.114,0.142,0.173  c-0.144-0.04-0.326-0.086-0.554-0.136l-0.312-0.951C15.906,7.871,15.961,7.865,16.009,7.858z M14.835,8.085l0.332,0.531  l-0.027,0.003L15,8.729c-0.106,0.086-0.9,0.055-1.181-0.06c-0.008-0.042-0.021-0.102-0.037-0.17l0.155,0.115l0.211-0.035  C14.502,8.52,14.727,8.291,14.835,8.085z M12.474,23.588c-2.592,0-5.062-0.891-7.046-2.524c0.099-0.062,0.178-0.121,0.227-0.162  l0.261-0.219l-0.232-0.697l0.337-1.062l0.028-0.169c0-0.405-0.161-0.873-0.589-0.957c-0.049-0.02-0.175-0.219-0.25-0.272  c-0.213-0.156-0.382-0.385-0.589-0.385c0,0-0.001,0-0.002,0c-0.038,0-0.146,0.061-0.211,0.018c-0.058-0.037-0.134-0.025-0.216-0.066  c-0.049-0.023-0.131-0.036-0.12-0.02c-0.079-0.116-0.186-0.285-0.282-0.531c-0.077-0.215-0.146-0.322-0.508-0.729  c-0.238-0.27-0.295-0.329-0.515-0.432l-0.092-0.042c-0.093-0.046-0.154-0.093-0.214-0.136c-0.095-0.068-0.213-0.154-0.384-0.224  c-0.144-0.059-0.19-0.094-0.329-0.212c-0.05-0.045-0.109-0.095-0.18-0.141C1.43,13.922,1.36,13.199,1.36,12.474l0.002-0.138  c0.11-0.286,0.212-0.771,0.163-1.017c-0.003-0.062,0.036-0.256,0.06-0.371c0.041-0.202,0.076-0.402,0.072-0.5  c0.021-0.084,0.17-0.312,0.249-0.434c0.105-0.162,0.195-0.312,0.236-0.412C2.184,9.5,2.415,9.145,2.554,8.932l0.232-0.364  C2.847,8.473,2.988,8.248,2.934,8C3.197,7.814,3.536,7.573,3.58,7.536C3.634,7.482,4.086,6.988,3.623,6.13  C3.608,6.102,3.782,6.08,3.775,6.068c0.039-0.072,0.275-0.19,0.275-0.348V5.683c0-0.305,0.119-0.544,0.387-0.544h0.166  c0.06,0,0.119-0.175,0.174-0.185l0.408,0.099L6.03,4.697L6.12,4.51c0.001-0.001,0.09-0.173,0.278-0.307  c0.28-0.201,0.365-0.328,0.496-0.545L6.493,3.357L6.966,3.57C7.021,3.516,7.132,3.147,7.42,2.838  c0.335-0.36,0.527-0.698,0.505-0.698h0.209l0.148,0.094C8.327,2.184,8.515,2.104,8.66,1.908c1.925-0.706,4.048-0.793,6.048-0.385  c-0.264,0.006-0.512,0.119-0.89,0.3c-0.092,0.044-0.174,0.102-0.223,0.118c-0.086,0.021-0.216,0.082-0.353,0.144  c-0.083,0.036-0.237,0.108-0.255,0.116c-0.341,0-0.635,0.281-0.869,0.588c-0.293,0.533-0.222,0.854-0.128,1.012l0.192,0.339h0.139  c-0.182,0-0.351,0.266-0.456,0.358L11.36,3.875l-0.477-1.006l-0.834,0.72v0.373L9.538,3.787L9.02,4.867l0.738,1.018l0.293-0.258  v0.07l0.468-0.119c-0.011,0.118-0.035,0.234,0.004,0.342l-1.37,0.318l0.118,0.51c0.021,0.082,0.026,0.243,0.033,0.34  C9.116,7.834,9.286,8.061,9.353,8.148l0.123,0.136C9.303,8.459,9.189,8.654,9.132,8.75c-0.078,0.091-0.22,0.252-0.25,0.49  C8.837,9.297,8.737,9.388,8.666,9.436L8.575,9.492c-0.328,0.201-0.635,0.402-0.79,0.66C7.592,10.477,7.47,10.762,7.424,11  c-0.053,0.265-0.053,0.472-0.053,0.785c0,0.096-0.005,0.178-0.01,0.254c-0.01,0.166-0.021,0.338,0.017,0.562  c0.08,0.479,0.739,1.339,0.748,1.35c0.206,0.258,0.45,0.547,0.733,0.715c0.146,0.088,0.335,0.244,0.413,0.31  c0.188,0.221,0.625,0.411,1.053,0.248c0.12-0.046,0.241-0.068,0.358-0.096c0.216-0.048,0.462-0.101,0.704-0.238  c0.056-0.032,0.106-0.061,0.152-0.082c0.187,0.311,0.571,0.465,0.96,0.337l0.008-0.003c0.013,0.08,0.025,0.154,0.038,0.223  c0.014,0.068,0.026,0.127,0.025,0.245c-0.008,0.17-0.023,0.487,0.235,0.747c0.051,0.051,0.127,0.112,0.209,0.18  c0.035,0.027,0.087,0.068,0.127,0.104c0.017,0.214,0.062,0.572,0.174,0.795c0.054,0.105,0.068,0.186,0.087,0.15  c-0.095,0.188-0.282,0.699-0.301,0.763c-0.116,0.522-0.049,0.721-0.029,0.778c0.081,0.25,0.233,0.484,0.465,0.715  c0.028,0.028,0.041,0.069,0.068,0.17c0.032,0.116,0.075,0.274,0.185,0.421c0.044,0.062,0.145,0.318,0.195,0.473l0.063,0.348  l-0.001,0.965l0.572-0.082c0,0,0.938-0.137,1.121-0.183c0.213-0.055,0.934-0.388,1.193-0.646c0.132-0.132,0.195-0.255,0.237-0.337  c0.034-0.063,0.038-0.071,0.071-0.1c0.214-0.17,0.603-0.469,0.603-0.469l0.211-0.162l-0.046-0.729l0.359-0.484l0.306-0.312  c-0.072,0.172-0.163,0.396-0.196,0.527c-0.06,0.238-0.02,0.525,0.17,0.766l0.222,0.138h0.243l0.179,0.067  c0.186-0.061,0.333-0.153,0.478-0.291c0.044-0.042,0.086-0.061,0.113-0.081c0.273-0.18,0.537-0.64,0.6-0.886l0.173-0.64  c0.05-0.199,0.101-0.583,0.101-0.765c0-0.181-0.048-0.381-0.058-0.42l-0.313-1.263l-0.875,1.644c-0.128,0.062-0.22,0.12-0.303,0.18  c0.001-0.076-0.006-0.138-0.015-0.17c-0.021-0.084-0.065-0.471-0.104-0.826c0.062-0.13,0.143-0.281,0.189-0.346  c0.082-0.109,0.129-0.218,0.166-0.304c0.045-0.104,0.067-0.153,0.14-0.215c0.269-0.223,0.449-0.471,0.582-0.652l0.104-0.139  c0.143-0.188,0.366-0.488,0.44-0.604c0.123-0.183,0.208-0.511,0.247-0.688l0.159-0.722l-0.599,0.1c0.072-0.036,0.137-0.07,0.181-0.1  c0.035-0.023,0.096-0.058,0.165-0.097c0.273-0.153,0.477-0.271,0.604-0.398l0.268-0.195c0.235-0.156,0.528-0.351,0.565-0.688  c0.052,0.041,0.107,0.08,0.171,0.11c0.043,0.027,0.146,0.14,0.229,0.244c0.062,0.146,0.218,0.514,0.25,0.643  c0.022,0.087,0.087,0.249,0.158,0.418c0.055,0.128,0.115,0.263,0.128,0.275c0,0.113,0.051,0.451,0.239,0.666  C22.735,19.408,18.083,23.588,12.474,23.588z M12.634,7.641c-0.004,0-0.013,0.002-0.016,0.002c-0.047-0.009-0.195-0.057-0.312-0.103  l-0.112-0.044l-0.119,0.011c-0.042,0.005-0.104,0.013-0.172,0.021c0.004-0.006,0.014-0.021,0.017-0.024  c0.035-0.053,0.068-0.103,0.203-0.146c0.247-0.083,0.38-0.198,0.426-0.258c0.071-0.022,0.24-0.056,0.376-0.078l0.21,0.139  c0.062,0.074,0.15,0.156,0.277,0.236l-0.799,0.228L12.634,7.641z"/></svg></a>';
				$output .= '</li>';
			} elseif ( 'post_types' == $field ) {
				$output .= $this->render_post_type_el( $types, $labels, $post_types, $field, $all_items_labels, $i );
			} else {
				$output .= $this->render_taxonomy_el(
					$types,
					$labels,
					$field,
					$hierarchical,
					$hide_empty,
					$show_count,
					$order_by,
					$order_dir,
					$operators,
					$all_items_labels,
					$i
				);
			}
			$i++;
		} // End foreach

		$output .= '<li class="trilisting-form-submit">';

		if ( 1 == $add_search_param ) {
			$output .= "<input type=\"hidden\" name=\"" . TRILISTING_SEARCH_PREFIX . "add_search_param\" value=\"1\" />";
		}

		if ( ! get_option( 'permalink_structure' ) && ! empty( $this->redirect_url ) ) {
			$query_obj = $this->parse_redirect_page();
			$page_id   = isset( $query_obj['page_id'] ) ? $query_obj['page_id'] : '';
			if ( ! empty( $page_id ) ) {
				$output .= "<input type=\"hidden\" name=\"page_id\" value=\"" . esc_attr( $page_id ) . "\" />";
			}
		}

		$output .= '<input type="hidden" name="' . TRILISTING_SEARCH_PREFIX . 'submitted" value="1"><input class="trilisting-search-form-submit" type="submit" value="' . esc_attr( $submitlabel ) . '"></li>';
		$output .= "</ul>";
		$output .= '</div></form>';

		if ( 'page' == $search_type ) {
			$output .= Trilisting_Helpers::do_action( 'trilisting/search/after_form' );
		}

		// listings
		if ( 'page' == $search_type ) {
			if ( ( '' != $post_types ) && ( is_array( $post_types ) ) ) {
				$post_types = implode( ',', $post_types );
			} else {
				$opt_post_types = trilisting_enable_post_types();
				if ( ! empty( $opt_post_types ) ) {
					$post_types = implode( ',', $opt_post_types );
				}
			}

			$post_limit_opt  = \Trilisting_Widgets_Platform::get_trilisting_option( 'layouts_search_result_count_posts' );
			$pagination_type = \Trilisting_Widgets_Platform::get_trilisting_option( 'pagination_type' );

			if ( 'all' == $post_types ) {
				$post_types = isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] ) ? $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] : '';
			}

			if ( is_array( $post_types ) ) {
				$post_types = implode( ',', $post_types );
			}

			$ats_widget = [
				's'                     => isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'search' ] ) ? $_GET[ TRILISTING_SEARCH_PREFIX . 'search' ] : '',
				'wp_custom_post_types'  => $post_types,
				'post_limit'            => ! empty( $post_limit_opt ) ? $post_limit_opt : 12,
				'paged'                 => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
				'ajax_pagination'       => ! empty( $pagination_type ) ? $pagination_type : 'numeric',
			];

			$tax_query = [];
			if ( ! empty( $fields ) && is_array( $fields ) ) {
				foreach ( $fields as $tax_name ) {
					if (
						'search' != $tax_name &&
						'location' != $tax_name &&
						isset( $_GET[ TRILISTING_SEARCH_PREFIX . $tax_name ] ) &&
						( 0 != $_GET[ TRILISTING_SEARCH_PREFIX . $tax_name ] ) &&
						( 0 != $_GET[ TRILISTING_SEARCH_PREFIX . $tax_name ][0] )
						) {
						$tax_query[] =  [
							'taxonomy'  => $tax_name,
							'field'     => 'id',
							'terms'     => $_GET[ TRILISTING_SEARCH_PREFIX . $tax_name ],
							'operator'  => 'AND',
						];
					}
				}
			}

			$ats_widget['tax_query'] = $tax_query;
			$ats_widget = apply_filters( 'trilisting/filter/search/widget_atts', $ats_widget );

			if ( isset( $_GET['trilisting_location'] ) && ! empty( $_GET['trilisting_location'] ) ) {
				$locations  = explode( ',', $_GET['trilisting_location'] );
				$meta_query = [
					'relation' => 'AND',
				];

				$google_maps_key = apply_filters( 'trilisting/search/google_maps_key', TRILISTING_GOOGLE_MAPS_KEY );

				if ( ! empty( $locations ) ) {
					foreach ( $locations as $location ) {
						$meta_query[] = [
							'key'     => $google_maps_key,
							'value'   => $location,
							'compare' => 'RLIKE',
						];
					}
				}

				$ats_widget['meta_query'] = $meta_query;
			}

			$widget_tmpl_option = \Trilisting_Widgets_Platform::get_trilisting_option( 'layouts_search_result_tmpl' );
			if ( empty( $widget_tmpl_option ) ) {
				$widget_tmpl_option = 'widget_blog_1';
			}

			$widget_mod_class = 'trilisting_' . $widget_tmpl_option;
			$widget_mod_class = apply_filters( 'trilisting/search/widget_view', $widget_mod_class );
			if ( class_exists( $widget_mod_class ) ) {
				$widget_tmpl = new $widget_mod_class();
			} else {
				/* translators: %s: main widget */
				echo printf( esc_html__( 'Error: widget class "%s" doesnt exists', 'trilisting' ), $widget_mod_class );
			}

			// Search columns
			$search_columns = \Trilisting_Widgets_Platform::get_trilisting_option( 'layouts_search_result_columns' );
			$ats_widget['column_number'] = ! empty( $search_columns ) ? $search_columns : 1;

			$class_enable_map = ' trilisting-disable-maps';
			$enable_maps      = \Trilisting_Widgets_Platform::get_trilisting_option( 'enable_archive_search_maps' );
			if ( true == $enable_maps ) {
				$output .= '<div class="trilisting-map"></div>';
				$class_enable_map = '';
			}

			$output .= Trilisting_Helpers::do_action( 'trilisting/search/before_widget' );

			$output .= '<div class="trilisting-listings trilisting-listing-maps' . $class_enable_map . '">';
			$output .= Trilisting_Helpers::do_action( 'trilisting/search/before_render_widget' );
			$output .= $widget_tmpl->render( $ats_widget );
			$output .= Trilisting_Helpers::do_action( 'trilisting/search/after_render_widget' );
			$output .= '</div>';

			$output .= Trilisting_Helpers::do_action( 'trilisting/search/after_widget' );
		} // End if

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $types
	 * @param $labels
	 * @param $post_types
	 * @param $field
	 * @param $all_items_labels
	 * @param $i
	 * @return string
	 */
	public function render_post_type_el( $types, $labels, $post_types, $field, $all_items_labels, $i ) {
		$output	= '';
		$taxonomy_children = [];
		$post_type_count   = count( $post_types );

		if ( is_array( $post_types ) ) {
			if ( ( 1 == $post_type_count ) && ( 'all' == $post_types[0] ) ) {
				$args = array(
					'public'   => true,
					'_builtin' => false,
				);

				$output_type = 'object';
				$operator    = 'and';

				$post_types_objs = get_post_types( $args, $output_type, $operator );
				$post_types      = [];

				foreach ( $post_types_objs as $post_type ) {
					if ( 'attachment' != $post_type->name ) {
						$tempobject = [];
						$tempobject['term_id']	= $post_type->name;
						$tempobject['cat_name']	= $post_type->labels->name;

						$taxonomy_children[] = ( object ) $tempobject;

						$post_types[] = $post_type->name;

					}
				}
				$post_type_count = count( $post_types_objs );

			} else {
				foreach ( $post_types as $post_type ) {
					$post_type_data = get_post_type_object( $post_type );

					if ( $post_type_data ) {
						$tempobject = [];
						$tempobject['term_id']	= $post_type;
						$tempobject['cat_name']	= $post_type_data->labels->name;

						$taxonomy_children[] = ( object ) $tempobject;
					}
				}
			}
		} // End if
		$taxonomy_children = ( object ) $taxonomy_children;

		$output .= '<li class="trilisting-cat-name trilisting-post-types">';

		$post_type_labels = [];
		$post_type_labels['name']           = esc_html__( 'Post Types', 'trilisting' ) ;
		$post_type_labels['singular_name']  = esc_html__( 'Post Type', 'trilisting' );
		$post_type_labels['search_items']   = esc_html__( 'Search Post Types', 'trilisting' );

		if ( '' != $all_items_labels[ $i ] ) {
			$post_type_labels['all_items'] = $all_items_labels[ $i ];
		} else {
			$post_type_labels['all_items'] = esc_html__( 'All Post Types', 'trilisting' );
		}

		$post_type_labels = ( object ) $post_type_labels;

		if ( '' != $labels[ $i ] ) {
			$output .= '<h4 class="trilisting-cat-title">' . esc_attr( $labels[ $i ] ) . '</h4>';
		}

		if ( 0 < $post_type_count ) {
			$defaultval = implode( ',', $post_types );
		} else {
			$defaultval = 'all';
		}
		
		if ( 'select' == $types[ $i ] ) {
			$output .= $this->render_select_type( $taxonomy_children, $field, $this->tagid, $post_type_labels, $defaultval );
		} elseif ( 'checkbox' == $types[ $i ] ) {
			$output .= $this->render_checkbox_type( $taxonomy_children, $field, $this->tagid );
		} elseif ( 'radio' == $types[ $i ] ) {
			$output .= $this->render_radio_type( $taxonomy_children, $field, $this->tagid, $post_type_labels, $defaultval );
		}
		$output .= '</li>';

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $types
	 * @param $labels
	 * @param $taxonomy
	 * @param $hierarchical
	 * @param $hide_empty
	 * @param $show_count
	 * @param $order_by
	 * @param $order_dir
	 * @param $operators
	 * @param $all_items_labels
	 * @param $i
	 * @return string
	 */
	public function render_taxonomy_el(
		$types,
		$labels,
		$taxonomy,
		$hierarchical,
		$hide_empty,
		$show_count,
		$order_by,
		$order_dir,
		$operators,
		$all_items_labels,
		$i
	) {
		$output        = '';
		$taxonomy_data = get_taxonomy( $taxonomy );

		if ( $taxonomy_data ) {
			$output .= '<li class="trilisting-cat-name ' . esc_attr( TRILISTING_SEARCH_PREFIX . $taxonomy ) . '">';

			if ( '' != $labels[ $i ] ) {
				$output .= '<h4 class="trilisting-cat-title">' . esc_attr( $labels[ $i ] ) . '</h4>';
			}

			$args = [
				'prefix_name'           => TRILISTING_SEARCH_PREFIX . $taxonomy,
				'taxonomy'              => $taxonomy,
				'hierarchical'          => false,
				'child_of'              => 0,
				'echo'                  => false,
				'hide_if_empty'         => false,
				'hide_empty'            => true,
				'order'                 => $order_dir[ $i ],
				'orderby'               => $order_by[ $i ],
				'show_option_none'      => '',
				'show_count'            => '0',
				'show_option_all'       => '',
				'show_option_all_sf'    => '',
			];
			 $args = apply_filters( 'trilisting/search/taxonomy_args', $args );

			if ( isset( $hierarchical[ $i ] ) ) {
				if ( 1 == $hierarchical[ $i ] ) {
					$args['hierarchical'] = true;
				}
			}

			if ( isset( $hide_empty[ $i ] ) ) {
				if ( 0 == $hide_empty[$i] ) {
					$args['hide_empty'] = false;
				}
			}

			if ( isset( $show_count[ $i ] ) ) {
				if ( 1 == $show_count[$i] ) {
					$args['show_count'] = true;
				}
			}

			if ( '' != $all_items_labels[ $i ] ) {
				$args['show_option_all_sf'] = $all_items_labels[ $i ];
			}

			$taxonomy_children = get_categories( $args );

			if ( 'select' == $types[ $i ] ) {
				$args['class'] = 'trilisting-form-select2';
				$output .= $this->render_wp_dropdown_type( $args, $taxonomy, $this->tagid, $taxonomy_data->labels );
			} elseif ( 'checkbox' == $types[ $i ] ) {
				$args['title_li'] = '';
				$args['defaults'] = '';
				if ( isset( $this->defaults[ $args['prefix_name'] ] ) ) {
					$args['defaults'] = $this->defaults[ $args['prefix_name'] ];
				}

				$output .= $this->render_wp_checkbox_type( $args, $taxonomy, $this->tagid, $taxonomy_data->labels );
			} elseif ('radio' ==  $types[ $i ] ) {
				$args['title_li'] = '';
				$args['defaults'] = '';

				if ( isset( $this->defaults[ $args['prefix_name'] ] ) ) {
					$args['defaults'] = $this->defaults[ $args['prefix_name'] ];
				}

				$output .= $this->render_wp_radio_type( $args, $taxonomy, $this->tagid, $taxonomy_data->labels );
			} elseif ( 'multiselect' == $types[ $i ] ) {
				$args['title_li'] = '';
				$args['defaults'] = '';

				if ( isset( $this->defaults[ $args['prefix_name'] ] ) ) {
					$args['defaults'] = $this->defaults[ $args['prefix_name'] ];
				}

				$output .= $this->render_wp_multiselect_type( $args, $taxonomy, $this->tagid, $taxonomy_data->labels );
			}

			if ( isset( $operators[ $i ] ) ) {
				$operators[ $i ] = strtolower( $operators[ $i ] );

				if ( ( 'and' == $operators[ $i ] ) || ( 'or' == $operators[ $i ] ) ) {
					$output .= '<input type="hidden" name="' . TRILISTING_SEARCH_PREFIX . $taxonomy . '_operator" value="' . $operators[ $i ] . '" />';
				}
			}

			$output .= '</li>';
		} // End if

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $args
	 * @param $name
	 * @param int $currentid
	 * @param null $labels
	 * @param string $defaultval
	 * @return string
	 */
	public function render_wp_dropdown_type( $args, $name, $currentid = 0, $labels = null, $defaultval = '0' ) {
		$args['name'] = $args['prefix_name'];

		$output = '';
		if ( '' == $args['show_option_all_sf'] ) {
			$args['show_option_all'] = $labels->all_items != '' ? $labels->all_items : esc_html__( 'All ', 'trilisting' ) . esc_attr( $labels->name );
		} else {
			$args['show_option_all'] = $args['show_option_all_sf'];
		}

		if ( isset( $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ] ) ) {
			$defaults = $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ];
			if ( is_array( $defaults ) ) {
				if ( 1 == count( $defaults ) ) {
					$args['selected'] = $defaults[0];
				}
			} else {
				$args['selected'] = $defaultval;
			}
		}

		if ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . $args['taxonomy'] ] ) && ! is_array( $_GET[ TRILISTING_SEARCH_PREFIX . $args['taxonomy'] ] ) && ! empty( $_GET[ TRILISTING_SEARCH_PREFIX . $args['taxonomy'] ] ) ) {
			$args['selected'] = $_GET[ TRILISTING_SEARCH_PREFIX . $args['taxonomy'] ];
		}
		$output .= wp_dropdown_categories( $args );

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $args
	 * @param $name
	 * @param int $currentid
	 * @param null $labels
	 * @param string $defaultval
	 * @return string
	 */
	public function render_wp_multiselect_type( $args, $name, $currentid = 0, $labels = null, $defaultval = '0' ) {
		$output = '<select multiple="multiple" name="' . $args['prefix_name'] . '[]" class="trilisting-form-select2 trilisting-form-filter">';
		$output .= $this->walker_search( 'multiselect', $args );
		$output .= "</select>";

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $args
	 * @param $name
	 * @param int $currentid
	 * @param null $labels
	 * @param string $defaultval
	 * @return string
	 */
	public function render_wp_checkbox_type( $args, $name, $currentid = 0, $labels = null, $defaultval = '0' ) {
		$output = '<ul class="trilisting-filter trilisting-filter-checkbox-type">';

		$args['selected'] = isset( $_GET[ TRILISTING_SEARCH_PREFIX . $args['taxonomy'] ] ) ? $_GET[ TRILISTING_SEARCH_PREFIX . $args['taxonomy'] ] : '';
		$output .= $this->walker_search( 'checkbox', $args );

		$output .= "</ul>";

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $args
	 * @param $name
	 * @param int $currentid
	 * @param null $labels
	 * @param string $defaultval
	 * @return string
	 */
	public function render_wp_radio_type( $args, $name, $currentid = 0, $labels = null, $defaultval = '0' ) {

		if ( '' == $args['show_option_all_sf'] ) {
			$show_option_all = $labels->all_items != '' ? $labels->all_items : esc_html__( 'All ', 'trilisting' ) . esc_attr( $labels->name );
		} else {
			$show_option_all = $args['show_option_all_sf'];
		}
		
		$checked = ( $defaultval == '0' ) ? " checked='checked'" : '';
	
		if ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . $args['taxonomy'] ] ) && ! empty( $_GET[ TRILISTING_SEARCH_PREFIX . $args['taxonomy'] ] ) ) {
			$args['selected'] = $_GET[ TRILISTING_SEARCH_PREFIX . $args['taxonomy'] ];
		}

		$output = '<ul class="trilisting-search-radio-wrap">';
		$output .= '<li class="trilisting-search-radio-field cat-item">' . "<label class='trilisting-label'><input type='radio' name='" . $args['prefix_name'] . "[]' value='0'$checked /> " . esc_attr( $show_option_all ) . "</label>" . '</li>';
		$output .= $this->walker_search( 'radio', $args );
		$output .= "</ul>";

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $dropdata
	 * @param $name
	 * @param int $currentid
	 * @param null $labels
	 * @param string $defaultval
	 * @return string
	 */
	public function render_select_type( $dropdata, $name, $currentid = 0, $labels = null, $defaultval = '0' ) {
		$output = '';

		$output .= '<select class="trilisting-form-select2 trilisting-form-filter" name="' . TRILISTING_SEARCH_PREFIX . $name . '">';
		if ( isset( $labels ) ) {
			if ( '' != $labels->all_items ) {
				$output .= '<option class="level-0" value="' . $defaultval . '">' . esc_attr( $labels->all_items ) . '</option>';
			} else {
				$output .= '<option class="level-0" value="' . $defaultval . '">' . esc_html__( 'All ', 'trilisting' ) . esc_attr( $labels->name ) . '</option>';
			}
		}

		foreach ( $dropdata as $dropdown ) {
			$selected = '';

			if ( isset( $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ] ) ) {
				$defaults = $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ];

				$count_selected = count( $defaults );

				if ( ( 1 == $count_selected ) && ( is_array( $defaults ) ) ) {
					foreach ( $defaults as $defaultid ) {
						if ( $defaultid == $dropdown->term_id ) {
							$selected = ' selected="selected"';
						}
					}
				}

				if ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] ) && ( $dropdown->term_id == $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] ) ) {
					$selected = ' selected="selected"';
				}
			}
			$output .= '<option class="level-0" value="' . $dropdown->term_id . '"' . $selected . '>' . esc_attr( $dropdown->cat_name ) . '</option>';

		} // End foreach
		$output .= "</select>";

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $dropdata
	 * @param $name
	 * @param int $currentid
	 * @param null $labels
	 * @param string $defaultval
	 * @return string
	 */
	public function render_checkbox_type( $dropdata, $name, $currentid = 0, $labels = null, $defaultval = '' ) {
		$output = '<ul class="trilisting-search-checkbox-wrap">';

		foreach ( $dropdata as $dropdown ) {
			$checked = '';

			if ( isset( $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ] ) ) {
				$defaults = $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ];

				$count_selected = count( $defaults );

				if ( ( 0 < $count_selected ) && ( is_array( $defaults ) ) ) {
					foreach ( $defaults as $defaultid ) {
						if ( $defaultid == $dropdown->term_id ) {
							$checked = ' checked="checked"';
						}
					}
				}

				if ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] ) && is_array( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] ) ) {
					foreach ( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] as $key ) {
						if ( $dropdown->term_id == $key ) {
							$checked = ' checked="checked"';
						}	
					}

					
				}
			} // End if

			$output .= '<li class="cat-item"><label class="trilisting-label"><input class="trilisting-form-filter cat-item" type="checkbox" name="' . TRILISTING_SEARCH_PREFIX . $name . '[]" value="' . $dropdown->term_id . '"' . $checked . '> ' . esc_attr( $dropdown->cat_name ) . '</label></li>';

		} // End foreach

		$output .= '</ul>';

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $dropdata
	 * @param $name
	 * @param int $currentid
	 * @param null $labels
	 * @param string $defaultval
	 * @return string
	 */
	public function render_radio_type( $dropdata, $name, $currentid = 0, $labels = null, $defaultval = '0' ) {
		$output = '<ul class="trilisting-search-radio-wrap">';

		if ( isset( $labels ) ) {
			$checked = "";
			if ( isset( $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ] ) ) {
				$defaults = $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ];
				$count_selected = count( $defaults );

				if ( 0 == $count_selected ) {
					$checked = ' checked="checked"';
				} elseif (1 == $count_selected ) {
					if ( $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ][0] == $defaultval ) {
						$checked = ' checked="checked"';
					}
				}
			} else {
				$checked = ' checked="checked"';
			}

			if ( isset( $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ] ) ) {
				$defaults = $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ];
				if ( 1 < count( $defaults ) ) {
					$checked = ' checked="checked"';
				}
			}

			$all_items_name = '';
			if ( '' != $labels->all_items ) {
				$all_items_name = $labels->all_items;
			} else {
				$all_items_name = 'All ' . $labels->name;
			}

			$output .= '<li class="cat-item"><labe class="trilisting-label"l><input class="trilisting-form-filter" type="radio" name="' . TRILISTING_SEARCH_PREFIX . $name . '[]" value="' . $defaultval . '"' . $checked . '> ' . esc_attr( $all_items_name ) . '</label></li>';
		} // End if

		foreach ( $dropdata as $dropdown ) {
			$checked = '';

			if ( isset( $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ] ) ) {
				$defaults = $this->defaults[ TRILISTING_SEARCH_PREFIX . $name ];

				$count_selected = count( $defaults );

				if ( ( 1 == $count_selected ) && ( is_array( $defaults ) ) ) {
					foreach ( $defaults as $defaultid ) {
						if ( $defaultid == $dropdown->term_id ) {
							$checked = ' checked="checked"';
						}
					}
				}

				if ( isset( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] ) ) {
					$checked_popst_types = is_array( $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] ) ? implode( ',', $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ] ) : $_GET[ TRILISTING_SEARCH_PREFIX . 'post_types' ];
					if ( $dropdown->term_id == $checked_popst_types ) {
						$checked = ' checked="checked"';
					}
				}
			}
			$output .= '<li class="cat-item"><label class="trilisting-label"><input class="trilisting-form-filter" type="radio" name="' . TRILISTING_SEARCH_PREFIX . $name . '[]" value="' . $dropdown->term_id . '"' . $checked . '> ' . esc_attr( $dropdown->cat_name ) . '</label></li>';
		} // End foreach

		$output .= '</ul>';

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param string $type
	 * @param array $args
	 * @return mixed
	 */
	protected function walker_search( $type = 'checkbox', $args = [] ) {
		$args['walker'] = new Trilisting_Search_Walker( $type, $args['prefix_name'] );

		$args   = apply_filters( 'trilisting/search/filter_walker_args', $args, $type );
		$output = wp_list_categories( $args );
		if ( $output ) {
			return $output;
		}
	}
}

(new Trilisting_Search());
