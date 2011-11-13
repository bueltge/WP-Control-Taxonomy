<?php
/**
 * Control Taxonomy for WordPress
 * @package  Wp_Control_Taxonomy
 * @version  0.0.2
 * @author   Frank Bültge <frank@bueltge.de>
 */

if ( ! class_exists( 'Wp_Control_Taxonomy' ) ) {
	
	if ( function_exists( 'add_action' ) ) {
		add_action( 'init', array( 'Wp_Control_Taxonomy', 'get_object' ) );
	}
	
	class Wp_Control_Taxonomy {
		
		/**
		 * The Class Object
		 */
		static private $classobj = NULL;

		/**
		 * Initialize Class Object
		 *
		 * @since   0.0.2
		 * @return object
		 */
		public function get_object () {
			if ( NULL === self::$classobj ) {
				self::$classobj = new self;
			}
			return self::$classobj;
		}
		
		/**
		 * Create Taxonomy; define different variables and settings
		 * Use Filter wpit_register_taxonomy for change params via Plugin
		 * 
		 * @since   0.0.1
		 * @uses    wp_parse_args, apply_filters
		 * @param   string $post_type, Name of Post_ID
		 * @param   array  $args, different vars for taxonomy: ID, slug, array for labels, add columns
		 * @return  void
		 */
		public function create_taxonomy( $post_type = FALSE, $args = FALSE ) {
			
			if ( ! $post_type )
				return NULL;
			
			$defaults = array(
				'taxonomy'        => '',
				'taxonomy_slug'   => '',
				'taxonomy_labels' => array(),
				'taxonomy_column' => FALSE
			);
			
			$args = wp_parse_args( $args, apply_filters( 'wpit_register_taxonomy', $defaults ) );
			
			$this -> post_type       = $post_type;
			$this -> taxonomy        = $args['taxonomy'];
			$this -> taxonomy_slug   = $args['taxonomy_slug'];
			$this -> taxonomy_labels = $args['taxonomy_labels'];
			$this -> taxonomy_column = $args['taxonomy_column'];
		}
		
		/**
		 * Add all actions to WordPress for the taxonomies
		 * 
		 * @since   0.0.1
		 * @uses    add_action, add_filter
		 * @param   
		 * @return  void
		 */
		public function __construct() {
			// Register the taxonomy.
			add_action( 'init', array( $this, 'register_taxonomy' ) );
			
			if ( $this -> taxonomy_column ) {
				// Add the taxonomy to the table columns.
				add_action( 'manage_posts_custom_column', array( $this, 'manage_columns' ) );
				
				// Display the taxonomy in the table column.
				add_filter( "manage_edit-{$this -> post_type}_columns", array( $this, 'manage_column_titles' ) );
			}
		}
		
		/**
		 * Return string for textdomain from parent class
		 * 
		 * @since   0.0.1
		 * @uses    
		 * @param   
		 * @return  void
		 */
		public function get_text_domain() {
			
			return parent :: get_textdomain();
		}
		
		/**
		 * Register the taxonomy initially
		 * 
		 * @since   0.0.1
		 * @uses    register_taxonomy, apply_filters
		 * @param   
		 * @return  void
		 */
		public function register_taxonomy() {
			
			register_taxonomy( 
				$this -> taxonomy, 
				array( $this -> post_type ), 
				array(
					'labels'                => apply_filters( "wpit_{$this -> taxonomy}_labels", $this -> taxonomy_labels ),
					'show_tagcloud'         => FALSE,
					'show_ui'               => TRUE,
					'rewrite'               => array(
						'slug' => $this -> taxonomy_slug
					),
					'update_count_callback' => '_update_post_term_count'
				) 
			);
		}
		
		/**
		 * Add this taxonomy to the array of column titles
		 * 
		 * @since   0.0.1
		 * @uses    get_taxonomy
		 * @param   array $columns, titles
		 * @return  array $columns, new titles
		 */
		function manage_column_titles( $columns ) {
			
			$taxonomy = get_taxonomy( $this -> taxonomy );
			
			$columns[ $this -> taxonomy ] = $taxonomy -> labels -> singular_name;
			
			return $columns;
		}
		
		/**
		 * Create the callback for the column headers
		 * 
		 * @since   0.0.1
		 * @uses    get_the_term_list
		 * @param   array columns
		 * @return  void
		 */
		function manage_columns( $column ) {
			
			switch( $column ) {
				case $this -> taxonomy :
					
					$tax = get_the_term_list( $GLOBALS['post'] -> ID, $this -> taxonomy, '', ', ', '' );
					
					if ( ! empty( $tax ) )
						echo $tax;
					else 
						_e( 'empty', $this -> get_text_domain() );
					
				break;
			}
		}
		
	} // end class
}
