<?php
/**
 * Easier control Taxonomy for WordPress
 * 
 * @package  Wp_Control_Taxonomy
 * @version  12/12/2012  1.0.0
 * @author   Frank Bültge <frank@bueltge.de>
 */

class Wp_Control_Taxonomy {
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
	public function init() {
		// Register the taxonomy.
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		
		// add to admin column, only important before WP 3.5
		if ( $this -> taxonomy_column && version_compare( $GLOBALS['wp_version'], '3.5', '<' ) ) {
			// Add the taxonomy to the table columns.
			add_action( 'manage_posts_custom_column', array( $this, 'manage_columns' ) );
			
			// Display the taxonomy in the table column.
			add_filter( "manage_edit-{$this->post_type}_columns", array( $this, 'manage_column_titles' ) );
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
		
		return parent::get_textdomain();
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
				'labels'                => apply_filters(
					"ct_{$this->taxonomy}_labels", $this->taxonomy_labels
				),
				'show_tagcloud'         => FALSE,
				'show_ui'               => TRUE,
				'show_admin_column'     => $this->taxonomy_column, // new since WP 3.5
				'rewrite'               => array(
					'slug' => $this->taxonomy_slug
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
		
		$taxonomy = get_taxonomy( $this->taxonomy );
		
		$columns[ $this->taxonomy ] = $taxonomy->labels->singular_name;
		
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
				
				$tax = get_the_term_list( $GLOBALS['post']->ID, $this->taxonomy, '', ', ', '' );
				
				if ( ! empty( $tax ) )
					echo $tax;
				else 
					_e( 'empty', $this->get_text_domain() );
				
			break;
		}
	}
	
} // end class

$wp_control_taxonomy = new Wp_Control_Taxonomy;
