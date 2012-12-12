<?php
/**
 * Easier control Taxonomy for WordPress
 * 
 * @package  Wp_Control_Taxonomy
<<<<<<< HEAD
 * @version  12/12/2012  1.0.0
 * @author   Frank Bültge <frank@bueltge.de>
 */

// avoid direct calls to this file, because now WP core and framework has been used.
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

class Wp_Control_Taxonomy {
	
	/**
	 * The Class Object
	 */
	protected static $classobj = NULL;
	
	/**
	 * Initialize Class Object
	 *
	 * @since   0.0.2
	 * @return  object
	 */
	public static function get_object() {
		
		if ( NULL === self::$classobj )
			self::$classobj = new self;
		
		return self::$classobj;
	}
	
	/**
	 * Create Taxonomy; define different variables and settings
	 * Use Filter `wp_control_taxonomy_register_taxonomy` for change params via Plugin
	 * 
	 * @since   0.0.1
	 * @uses    wp_parse_args, apply_filters
	 * @param   string $post_type, Name of Post_ID
	 * @param   array  $args, different vars for taxonomy: ID, slug, array for labels, add columns
	 * @return  void
	 */
	public function __construct( $post_type = FALSE, $args = FALSE ) {
		
		if ( ! $post_type )
			return NULL;
		
		$defaults = array(
			'textdomain'                        => 'wp_control_taxonomy',
			'taxonomy'                          => '',
			'taxonomy_slug'                     => '',
			'taxonomy_labels'                   => array(),
			'taxonomy_column'                   => FALSE,
			'taxonomy_hierarchical'             => FALSE,
			'terms'                             => FALSE,
			'taxonomy_custom_hierarchical'      => FALSE,
			'taxonomy_custom_hierarchical_type' => FALSE
		);
		
		// Filter Hook for params
		$args = wp_parse_args(
			$args,
			apply_filters( 'wp_control_taxonomy_register_taxonomy', $defaults )
		);
		
		$this->post_type                         = $post_type;
		$this->textdomain                        = $args['textdomain'];
		$this->taxonomy                          = $args['taxonomy'];
		$this->taxonomy_slug                     = $args['taxonomy_slug'];
		$this->taxonomy_labels                   = $args['taxonomy_labels'];
		$this->taxonomy_column                   = $args['taxonomy_column'];
		$this->taxonomy_hierarchical             = $args['taxonomy_hierarchical'];
		$this->taxonomy_terms                    = $args['terms'];
		$this->taxonomy_custom_hierarchical      = $args['taxonomy_custom_hierarchical'];
		$this->taxonomy_custom_hierarchical_type = $args['taxonomy_custom_hierarchical_type'];
		
		$this->init();
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
		
		// @ToDo: ändern, nur beim Aktivieren des Plugins register_activation_hook()
		// after register tax is important
		if ( $this->taxonomy_terms && is_array( $this->taxonomy_terms ) )
			add_action( 'init', array( $this, 'insert_terms' ), 11 );
		
		// add to admin column, only important before WP 3.5
		if ( $this->taxonomy_column && version_compare( $GLOBALS['wp_version'], '3.5', '<' ) ) {
			// Add the taxonomy to the table columns.
			add_action( 'manage_posts_custom_column', array( $this, 'manage_columns' ) );
			
			// Display the taxonomy in the table column.
			add_filter( "manage_edit-{$this->post_type}_columns", array( $this, 'manage_column_titles' ), 9 );
		}
		
		// Possibility to list hierarchical
		if ( $this->taxonomy_custom_hierarchical )
			add_action( 'add_meta_boxes', array( $this, 'change_meta_box' ) );
	}
	
	/**
	 * Return string for textdomain from parent class
	 * 
	 * @since   0.0.1
	 * @uses    
	 * @param   
	 * @return  void
	 */
	public function get_textdomain() {
		
		return apply_filters( 'wp_control_taxonomy_textdomain', $this->textdomain );
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
			$this->taxonomy, 
			array( $this->post_type ), 
			array(
				'hierarchical'          => $this->taxonomy_hierarchical,
				'labels'                => apply_filters(
					"wp_control_taxonomy_{$this->taxonomy}_labels",
					$this->taxonomy_labels
				),
				'show_tagcloud'         => FALSE,
				'show_ui'               => TRUE,
				'show_admin_column'     => $this->taxonomy_column,
				'rewrite'               => array(
					'slug' => $this->taxonomy_slug
				),
				'update_count_callback' => '_update_post_term_count'
			) 
		);
	}
	
	public function insert_terms() {
		
		foreach ( (array) $this->taxonomy_terms as $term ) {
			
			if ( ! term_exists( $term, $this->taxonomy ) )
				wp_insert_term( $term , $this->taxonomy );
		}
		
	}
	
	/**
	 * Add this taxonomy to the array of column titles
	 * 
	 * @since   0.0.1
	 * @param   array columns titles
	 * @return  array columns new titles
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
	 * @param   array columns
	 * @return  void
	 */
	function manage_columns( $column ) {
		
		switch( $column ) {
			case $this->taxonomy :
				
				$tax = get_the_term_list( $GLOBALS['post']->ID, $this->taxonomy, '', ', ', '' );
				
				if ( ! empty( $tax ) )
					echo $tax;
				else
					_e( '—', $this->textdomain );
				
			break;
		}
	}
	
	/**
	 * Change default Meta Box for hierarchical list
	 * 
	 * @since  12/12/2012  1.0.0
	 * @param  String $post_type
	 * @return void
	 */
	public function change_meta_box( $post_type ) {
		
		remove_meta_box( 'tagsdiv-' . $this->taxonomy, $this->post_type, 'side' );
		$taxonomy = get_taxonomy( $this->taxonomy );
		add_meta_box( 
			'tagsdiv-' . $this->taxonomy,
			$taxonomy->labels->name, 
			array( $this, 'get_custom_meta_boxes' ),
			$this->post_type,
			'side'
		);
	}
	
	/**
	 * Get the markup and date to use inside meta box
	 * 
	 * @since  12/12/2012  1.0.0
	 * @param  Array  $post
	 * @param  Array  $meta_box
	 * @param  String $type      checkbox, radio
	 * @return void
	 */
	public function get_custom_meta_boxes( $post, $meta_box, $type = 'checkbox' ) {
		var_dump($meta_box);
		$taxonomy = $this->taxonomy;
		$tax      = get_taxonomy( $taxonomy );
		$selected = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
		$name     = 'tax_input[' . $taxonomy . ']';
		
		// set type of input field
		if ( $this->taxonomy_custom_hierarchical_type )
			$type = $this->taxonomy_custom_hierarchical_type;
		
		if ( 'radio' === $type ) {
			// Set up the taxonomy object and get terms  
			$terms = get_terms( $taxonomy, array( 'hide_empty' => 0 ) );
			
			// Get popular terms
			$popular  = get_terms( 
				$taxonomy,
				array( 
					'orderby'      => 'count',
					'order'        => 'DESC',
					'number'       => 10,
					'hierarchical' => FALSE
				)
			);
			// Get current terms
			$postterms = get_the_terms( $post->ID, $taxonomy );  
			$current   = ( $postterms ? array_pop( $postterms ) : FALSE );  
			$current   = ( $current ? $current->term_id : 0 );
		}
		?>
		
		<div id="taxonomy-<?php echo $taxonomy; ?>-all" class="categorydiv">
			
			<ul id="<?php echo $taxonomy; ?>checklist" class="list:<?php echo $taxonomy; ?> categorychecklist form-no-clear">
				<?php
				if ( 'checkbox' === $type ) {
					
					// get list as checkbox
					wp_terms_checklist( 
						$post->ID, array(
							'taxonomy'      => $taxonomy,
							'selected_cats' => $selected
						)
					);
					
				} else if ( 'radio' === $type ) {
					
					// get list as radio
					foreach( $terms as $term ) {
						echo '<li id="' . $taxonomy . '-' . $term->term_id . '">';
						echo '<label class="selectit">';
						echo '<input value="' . $term->name . '" type="radio" name="' 
							. $name . '" id="in-' . $taxonomy . '-' . $term->term_id 
							. '" ' . checked( $current, $term->term_id, FALSE ) . ' /> ' 
							. $term->name;
						echo '</label>';
						echo '</li>';
					}
				} else {
					
					_e( 'No type - ToDo' );
					
				}
				?>
			</ul>
		
		</div>
		<?php
	}
	
} // end class
=======
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
>>>>>>> e323bbe336c5e4dd1ae7c02aae6aff72e8a4bc4b
