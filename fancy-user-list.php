<?php 
/*
Plugin Name: Fancy User List
Description: This amazing plugin gives you the possibility to list your website registered users on custom pages with one simple shortcode. :)
Version: 2.0
Author: GeroNikolov
Author URI: http://blogy.co?GeroNikolov
License: GPLv2
*/

class FANCY_USER_LIST {
	public function __construct() {
		//Add scripts and styles for the Back-end part
		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_JS' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_CSS' ) );

		//Add scripts and styles for the Front-end part
		add_action( 'wp_enqueue_scripts', array( $this, 'add_front_JS' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_front_CSS' ) );

		//Register custom post type
		add_action( 'init', array( $this, 'ful_post_type' ) );

		//Add custom meta boxes
		add_action( 'add_meta_boxes', array( $this, 'meta_boxes_setup' ) );

		//Save the updates
		add_action( 'save_post', array( $this, 'save_fields' ) );

		//Add the shortcode which will call the plugin in the pages
		add_action( 'init', array( $this, 'register_shortcode' ) );

		//Register AJAX call for Open User box
		add_action( 'wp_ajax_ful_pull_user', array( $this, 'ful_pull_user' ) );
		add_action( 'wp_ajax_nopriv_ful_pull_user', array( $this, 'ful_pull_user' ) );

		//Register AJAX call for User Picker box
		add_action( 'wp_ajax_ful_load_user_picker', array( $this, 'ful_load_user_picker' ) );
		add_action( 'wp_ajax_nopriv_ful_load_user_picker', array( $this, 'ful_load_user_picker' ) );
	}

	//Add Admin JS
	function add_admin_JS( $hook ) {
		wp_enqueue_script( 'ful-admin-js', plugins_url( '/assets/scripts/admin.js' , __FILE__ ), array('jquery'), '1.0', true );
	}

	//Add Admin CSS
	function add_admin_CSS( $hook ) {
		wp_enqueue_style( 'ful-admin-css', plugins_url( '/assets/css/admin.css', __FILE__ ), array(), '1.0', 'screen' );
	}

	//Register frontend JS
	function add_front_JS() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'ful-front-js', plugins_url( '/assets/scripts/front.js' , __FILE__ ), array(), '1.0', true );
	}

	//Register frontend CSS
	function add_front_CSS() {
		wp_enqueue_style( 'ful-front-css', plugins_url( '/assets/css/front.css', __FILE__ ), array(), '1.0', 'screen' );
	}

	//Fancy User Lists custom post type
	function ful_post_type() {
 		register_post_type( 
 			'fancy_user_list',
		    array(
	      		'labels' => array(
	        		'name' => __( 'Fancy Lists' ),
		        	'singular_name' => __( 'List' ),
		        	'all_items' => __( 'Lists' ),
		        	'add_new' => __( 'Add new list' ),
		        	'menu_icon' => 'dashicons-list-view'
		      	),
		      	'public' => true,
		      	'supports' => array( 'title' )
		    )
	  	);
	}

	//Meta Boxes Setup
	function meta_boxes_setup() {
		//Shortcode Metabox
		add_meta_box(
			'ful_shortcode',
			__( 'Fancy List Shortcode', 'short_code' ),
			array($this, 'ful_shortcode_'),
			'fancy_user_list',
			'side',
			'low'
		);

		//List Setup Metabox
		add_meta_box(
			'ful_setup_fields',
			__( 'Fancy List Setup', 'fls' ),
			array($this, 'ful_setup_fields_'),
			'fancy_user_list',
			'normal',
			'default'
		);
	}

	//Build FUL Shortcode
	function ful_shortcode_() {
		$list_id = get_the_ID();
	?>
		<p>Shortcode: <strong>[fancy_user_list ID="<?php echo $list_id; ?>"]</strong></p>
		<div class="line-separator"></div>
		<p><strong>Note:</strong> Grab the shortcode and put it on everypage on which you want to see the listing.</p>
	<?php
	}

	//Save FUL fields
	function save_fields( $post_id ) {
		foreach ( $_POST as $key => $value ) {
			if ( $key == "users_quantity" ) { update_post_meta( $post_id, "users_quantity", $value ); }
			elseif ( $key == "users_shape" ) { update_post_meta( $post_id, "users_shape", $value ); }
			elseif ( $key == "users_avatar" ) { update_post_meta( $post_id, "users_avatar", $value ); }
			elseif ( $key == "users_popup" ) { update_post_meta( $post_id, "users_popup", $value ); }
			elseif ( $key == "users_posts_num" ) { update_post_meta( $post_id, "users_posts_num", $value ); }
			elseif ( $key == "listing_order" ) { update_post_meta( $post_id, "listing_order", $value ); }
			elseif ( $key == "listing_order_type" ) { update_post_meta( $post_id, "listing_order_type", $value ); }
			elseif ( $key == "page_title" ) { update_post_meta( $post_id, "page_title", $value ); }
			elseif ( $key == "custom_users_listing" ) {
				if ( $_POST["pic_users"] == "on" ) {
					if ( !empty( $value ) ) { update_post_meta( $post_id, "custom_users_listing", $value ); }
					else { $value = NULL; update_post_meta( $post_id, "custom_users_listing", $value ); }
				} else {
					$value = NULL; 
					update_post_meta( $post_id, "custom_users_listing", $value );
				}
			}
		}
	}

	//Build FUL Setup
	function ful_setup_fields_() {
		$list_id  =get_the_ID();
		$post_meta = get_post_custom( $list_id );

		//Selected vars 
		$select_list_shape = "";
		$select_grid_shape = "";
		$select_users_avatars_true = "";
		$select_users_avatars_false = "";
		$select_popup_trigger_true = "";
		$select_popup_trigger_false = "";
		$select_posts_num_true = "";
		$select_posts_num_false = "";
		$select_order_by_id = "";
		$select_order_by_email = "";
		$select_order_by_nickname = "";
		$select_order_by_users_posts = "";
		$select_order_ASC = "";
		$select_order_DESC = "";
		$select_page_title_true = "";
		$select_page_title_false = "";

		//Checked vars
		$check_custom_listing = "";
		
		//Setup metas
		$users_quantity = $post_meta["users_quantity"][0];
		if ( empty( $users_quantity ) ) { $users_quantity = 0; }

		$users_shape = $post_meta["users_shape"][0];
		if ( $users_shape == "list" ) { $select_list_shape = "selected"; }
		elseif ( $users_shape == "grid" ) { $select_grid_shape = "selected"; }
	
		$users_avatar = $post_meta["users_avatar"][0];
		if ( $users_avatar == "yes" ) { $select_users_avatars_true = "selected"; }
		elseif ( $users_avatar == "no" ) { $select_users_avatars_false = "selected"; }

		$users_popup = $post_meta["users_popup"][0];
		if ( $users_popup == "yes" ) { $select_popup_trigger_true = "selected"; }
		elseif ( $users_popup == "no" ) { $select_popup_trigger_false = "selected"; }

		$users_posts_num = $post_meta["users_posts_num"][0];
		if ( $users_posts_num == "yes" ) { $select_posts_num_true = "selected"; }
		elseif ( $users_posts_num == "no" ) { $select_posts_num_false = "selected"; }

		$users_order_by = $post_meta["listing_order"][0];
		if ( $users_order_by == "ID" ) { $select_order_by_id = "selected"; }
		elseif ( $users_order_by == "email" ) { $select_order_by_email = "selected"; }
		elseif ( $users_order_by == "display_name" ) { $select_order_by_nickname = "selected"; }
		elseif ( $users_order_by == "post_count" ) { $select_order_by_users_posts = "selected"; }

		$users_order_ = $post_meta["listing_order_type"][0];
		if ( $users_order_ == "asc" ) { $select_order_ASC = "selected"; }
		elseif ( $users_order_ == "desc" ) { $select_order_DESC = "selected"; }

		$listing_title = $post_meta["page_title"][0];
		if ( $listing_title == "yes" ) { $select_page_title_true = "selected"; }
		elseif ( $listing_title == "no" ) { $select_page_title_false = "selected"; }

		$custom_users_listing = $post_meta["custom_users_listing"][0];
		if ( !empty( $custom_users_listing ) ) {
			$check_custom_listing = "checked";
		}
	?>
		<div class="setup-fields">
			<label for="pic_users">Choose custom users to list :</label>
			<input type="checkbox" id="pic_users" name="pic_users" <?php echo $check_custom_listing; ?>>
			<div id="quantity_holder">
				<label for="users_quantity">Users quantity :</label>
				<input type="number" id="users_quantity" name="users_quantity" min="0" value="<?php echo $users_quantity; ?>">
			</div>
			<div id="sep_holder">
				<label for="users_shape">Show users in :</label>
				<select id="users_shape" name="users_shape">
					<option value="list" <?php echo $select_list_shape ?>>List</option>
					<option value="grid" <?php echo $select_grid_shape ?>>Grid</option>
				</select>
				<label for="users_avatar">Show users avatar :</label>
				<select id="users_avatar" name="users_avatar">
					<option value="yes" <?php echo $select_users_avatars_true; ?>>Yes</option>
					<option value="no" <?php echo $select_users_avatars_false; ?>>No</option>
				</select>
				<label for="users_popup">Use username as a popup trigger :</label>
				<select id="users_popup" name="users_popup">
					<option value="yes" <?php echo $select_popup_trigger_true; ?>>Yes</option>
					<option value="no" <?php echo $select_popup_trigger_false; ?>>No</option>
				</select>
				<label for="users_posts_num">Show users posts number :</label>
				<select id="users_posts_num" name="users_posts_num">
					<option value="yes" <?php echo $select_posts_num_true; ?>>Yes</option>
					<option value="no" <?php echo $select_posts_num_false; ?>>No</option>
				</select>
				<label for="listing_order">Order by :</label>
				<select id="listing_order" name="listing_order">
					<option value="ID" <?php echo $select_order_by_id; ?>>ID</option>
					<option value="email" <?php echo $select_order_by_email; ?>>Email</option>
					<option value="display_name" <?php echo $select_order_by_nickname; ?>>Display Name</option>
					<option value="post_count" <?php echo $select_order_by_users_posts; ?>>User Posts</option>
				</select>
				<label for="listing_order_type">Order by ASC / DESC :</label>
				<select id="listing_order_type" name="listing_order_type">
					<option value="asc" <?php echo $select_order_ASC; ?>>ASC</option>
					<option value="desc" <?php echo $select_order_DESC; ?>>DESC</option>
				</select>
				<label for="page_title">Use Listing title as Section title :</label>
				<select id="page_title" name="page_title">
					<option value="yes" <?php echo $select_page_title_true; ?>>Yes</option>
					<option value="no" <?php echo $select_page_title_false; ?>>No</option>
				</select>
				<input type="text" style="display: none;" id="custom_users_listing" name="custom_users_listing" value="<?php echo $custom_users_listing; ?>">
			</div>
		</div>
		<!-- INLINE STYLE -->
		<style>
		#edit-slug-box { display: none; }
		.pick-box .loader {
			background-image: url(<?php echo plugin_dir_url( __FILE__ ); ?>/assets/images/loader.gif);
		}
		</style>
		<!-- INLINE SCRIPTS -->
		<script type="text/javascript">
			var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
			<?php if ( !empty( $custom_users_listing ) ) { ?>
				jQuery( document ).ready(function(){
					loadUserPicker();
				});
			<?php } ?>
		</script>
	<?php
	}

	//Register shortcode function
	function register_shortcode() {
		add_shortcode( 'fancy_user_list', array( $this, 'register_listing' ) );
	}

	//Listing function
	function register_listing( $atts ) {
		extract( shortcode_atts( array(
			'id' => '', // ID of the listing
	 	), $atts ));	

		if ( !empty( $id ) ) {
			$listing_meta = get_post_custom( $id );
			
			$users_quantity = $listing_meta["users_quantity"][0];
			$users_shape = $listing_meta["users_shape"][0];
			$users_avatar = $listing_meta["users_avatar"][0];
			$users_popup = $listing_meta["users_popup"][0];
			$users_posts_num = $listing_meta["users_posts_num"][0];
			$listing_order = $listing_meta["listing_order"][0];
			$listing_order_type = $listing_meta["listing_order_type"][0];
			$listing_title_as_section_title = $listing_meta["page_title"][0];
			$custom_users_listing = $listing_meta["custom_users_listing"][0];

			$user_ids = array();
			if ( !empty( $custom_users_listing ) ) {
				$user_ids = explode( ",", $custom_users_listing );
			} else {
				if ( $users_quantity <= 0 ) { $users_quantity = -1; }
			}

			//Set arguments for the listing
			$args = array(
					"include" => $user_ids,
					"orderby" => $listing_order,
					"order" => $listing_order_type,
					"number" => $users_quantity
				);
			$users_list = get_users( $args );

			if ( $listing_title_as_section_title == "yes" ) {
				$listing_title = get_the_title( $id );
				?>
				<h1 class="fancy-user-listing-section-title"><?php echo $listing_title; ?></h1>
				<?php
			}

			if ( $users_shape == "list" ) { //List layout
			    ?>
			    <div id="fancy-user-list">
			    <?php
				foreach ( $users_list as $user ) {
					$user_id = $user->ID;
					$user_display_name = ucfirst( $user->display_name );

					$user_avatar = "";
					if ( $users_avatar == "yes" ) { $user_avatar = get_avatar( $user_id ); }

					$user_posts_end = "post";
					$user_posts_count = "";
					if ( $users_posts_num == "yes" ) { 
						$user_posts_count = count_user_posts( $user_id ); 
						if ( $user_posts_count > 1 ) { $user_posts_end = "posts"; }
					}

					if ( $users_popup == "yes" ) {
						$open_wrapper = "<a href='#!' id='user-". $user_id ."' class='fancy-user-container-link fancy-user-trigger'>";
						$close_wrapper = "</a>";
					} elseif ( $users_popup == "no" ) {
						$author_url = get_author_posts_url( $user_id );
						$open_wrapper = "<a href='". $author_url ."' id='user-'". $user_id ."' class='fancy-user-container-link'>";
						$close_wrapper = "</a>";
					}

					echo $open_wrapper;
					if ( !empty( $user_avatar ) ) { echo $user_avatar; }
					?>
					<h2 class="fancy-author-name"><?php echo $user_display_name; ?></h2>
					
					<?php if ( !empty( $user_posts_count ) ) { ?>
					<span class="fancy-author-posts"><?php echo "- ". $user_posts_count ." ". $user_posts_end; ?></span>
					<?php } ?>
					
					<?php
					echo $close_wrapper;
				}
				?>
				</div>
				<?php
			} elseif ( $users_shape == "grid" ) { //Grid layout
			    ?>
			    <div id="fancy-user-grid">
			    <?php
				foreach ( $users_list as $user ) {
					$user_id = $user->ID;
					$user_display_name = ucfirst( $user->display_name );

					$user_avatar = "";
					if ( $users_avatar == "yes" ) { $user_avatar = get_avatar( $user_id ); }

					$user_posts_end = "post";
					$user_posts_count = "";
					if ( $users_posts_num == "yes" ) { 
						$user_posts_count = count_user_posts( $user_id ); 
						if ( $user_posts_count > 1 ) { $user_posts_end = "posts"; }
					}

					if ( $users_popup == "yes" ) {
						$open_wrapper = "<a href='#!' id='user-". $user_id ."' class='fancy-user-container-link fancy-user-trigger'>";
						$close_wrapper = "</a>";
					} elseif ( $users_popup == "no" ) {
						$author_url = get_author_posts_url( $user_id );
						$open_wrapper = "<a href='". $author_url ."' id='user-'". $user_id ."' class='fancy-user-container-link'>";
						$close_wrapper = "</a>";
					}

					echo $open_wrapper;
					if ( !empty( $user_avatar ) ) { echo $user_avatar; }
					?>
					<h2 class="fancy-author-name"><?php echo $user_display_name; ?></h2>
					
					<?php if ( !empty( $user_posts_count ) ) { ?>
					<span class="fancy-author-posts"><?php echo "- ". $user_posts_count ." ". $user_posts_end; ?></span>
					<?php } ?>
					
					<?php
					echo $close_wrapper;
				}
				?>
				</div>
				<?php
			}
		}

		?>
		<!-- INLINE STYLE -->
		<style type="text/css">
		.fancy-user-list-popup .loader {
			background-image: url(<?php echo plugin_dir_url( __FILE__ ); ?>/assets/images/loader.gif);
		}
		.fancy-user-list-popup .close-button {
			background-image: url(<?php echo plugin_dir_url( __FILE__ ); ?>/assets/images/close-white-ex.png);
		}
		</style>

		<!-- INLINE SCRIPTS -->
		<script type="text/javascript">
			var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
		</script>
		<?php
		return NULL;
	}

	//AJAX Function ---> Pull User
	function ful_pull_user() {
		$user_id = $_POST["data"];
		$user_meta = get_userdata( $user_id );
		$user_avatar = get_avatar( $user_id );
		$user_display_name = ucfirst( $user_meta->display_name );
		$user_posts_count = count_user_posts( $user_id );
		$user_link = get_author_posts_url( $user_id );

		$posts_end = "post";
		if ( $user_posts_count > 1 ) { $posts_end = "posts"; }
	?>
		<div class="ful-meta-container">
			<div id="top" class="fancy-author">
				<?php echo $user_avatar; ?>
				<a href="<?php echo $user_link; ?>" class="author_link">
					<h2 class="fancy-author-name"><?php echo $user_display_name; ?></h2>
				</a>
				<?php if ( $user_posts_count > 0 ) { ?>
					<span class="fancy-author-posts">- <?php echo $user_posts_count ." ". $posts_end; ?></span>
					<h1 class="fancy-latest-post-label">Latest Posts :</h1>
				<?php } ?>
			</div>
			<div id="bottom" class="fancy-posts">
	<?php
		if ( $user_posts_count > 0 ) {
			//Get the last 3 posts
			$args = array(
					'author' => $user_id,
					'orderby' => 'post_date',
					'order' => 'DESC',
					'post_status' => 'publish',
					'post_type' => 'post',
					'posts_per_page' => 3
				);
			$collection_ = get_posts( $args );
		
			if ( !empty( $collection_ ) ) {
				foreach ( $collection_ as $post ) {
					$post_id = $post->ID;
					$post_title = $post->post_title;
					$post_content = wp_trim_words( $post->post_content, 37, "..." );
					$post_permalink = get_the_permalink( $post_id );
					?>
						<a href="<?php echo $post_permalink; ?>" class="fancy-post-link">
							<div id="post-<?php echo $post_id; ?>" class="fancy-post-container">
								<h2 class="fancy-post-title"><?php echo $post_title; ?></h2>
								<div class="fancy-post-excerpt"><?php echo $post_content; ?></div>
							</div>
						</a>
					<?php
				}
			}
		} else {
		?>
			<div class="no-posts-container">
				<img src='<?php echo plugin_dir_url( __FILE__ ); ?>/assets/images/dead.png' />
				<h2>No posts yet :-|</h2>
			</div>
		<?php
		}
	?>
			</div>
		</div>
	<?php
		die();
	}

	//AJAX Function ---> Load User Picker
	function ful_load_user_picker() {
		//Set arguments for the listing
		$args = array(
				"orderby" => "ID",
				"order" => "DESC",
				"number" => -1
			);
		$users_list = get_users( $args );
		?>
	
		<div id="users-list">
		<?php
		foreach ( $users_list as $user ) {
			$user_id = $user->ID;
			$user_display_name = ucfirst( $user->display_name );
			$user_avatar = get_avatar( $user_id );
		?>
		
		<a href="#!" id="user-<?php echo $user_id; ?>" class="user" onclick="addUserToListing('<?php echo $user_id; ?>');">
			<?php echo $user_avatar; ?>
			<h1 class="user-title"><?php echo $user_display_name; ?></h1>
		</a>

		<?php
		}
		?>
		</div>

		<?php
		die();
	}
}

//Call the plugin
$call_ = new FANCY_USER_LIST;
?>