
## How to use

		<?php
		/**
		 * Control Taxonomy Example
		 * 
		 * @package  Wp_Control_Taxonomy
		 * @author   Frank Bültge <frank@bueltge.de>
		 */

		// avoid direct calls to this file, because now WP core and framework has been used.
		if ( ! function_exists( 'add_filter' ) ) {
			echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
			exit;
		}

		if ( ! class_exists( 'Wp_Control_Taxonomy' ) )
			require_once( dirname( __FILE__ ) . '/class-taxonomy.php' );

		class Issue_Tracking_Taxonomy_Example {
			
			public static $post_type = 'example';
			
			public function __construct() {
				
				$this->register_categories();
			}
			
			public function register_categories() {
				
				$args = array(
					'textdomain'      => 'my_example_textdomain',
					'taxonomy'        => self::$post_type . '_category',
					'taxonomy_slug'   => 'category',
					'taxonomy_labels' => array(
						'name'          => __( 'Examples', 'my_example_textdomain'() ),
						'singular_name' => __( 'Example', 'my_example_textdomain'() ),
						'search_items'  => __( 'Search Examples', 'my_example_textdomain'() ),
						'popular_items' => __( 'Popular Examples', 'my_example_textdomain'() ),
						'all_items'     => __( 'All Examples', 'my_example_textdomain'() ),
						'update_item'   => __( 'Update Example', 'my_example_textdomain'() ),
						'add_new_item'  => __( 'Add New Example', 'my_example_textdomain'() ),
						'new_item_name' => __( 'New Example Name', 'my_example_textdomain'() ),
						'edit_item'     => __( 'Edit Example', 'my_example_textdomain'() )
					),
					'taxonomy_column'       => TRUE,
					'taxonomy_hierarchical' => TRUE
				);
				new Wp_Control_Taxonomy( self::$post_type, $args );
			}
			
		} // end class
		$issue_category = new Issue_Tracking_Taxonomy_Example;
		
