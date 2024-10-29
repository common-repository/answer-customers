<?php
/*
Plugin Name: Answer Customers 
Plugin URI: https://answercustomers.com
Description: The WordPress plugin to get your content from Zendesk to Wordpress. 
Version: 1.0.0
Author: Retro Mocha 
Author URI: https://retromocha.com
*/

class Answer_Customers_API_Endpoint {

	public function __construct() {
		add_filter('query_vars', array($this, 'add_query_vars'), 0);
		add_action('parse_request', array($this, 'watch_requests'), 0);
		add_action('admin_menu', array($this, 'answer_customers_menu') );
	}	
	
	public function add_query_vars($vars) {
		$vars[] = '__ac_api';
		$vars[] = 'ac_title';
		$vars[] = 'ac_content';
		$vars[] = 'ac_api_key';
		return $vars;
	}
	
	public function watch_requests(){
		global $wp;
		if(isset($wp->query_vars['__ac_api'])){
			$this->handle_request();
			exit;
		}
	}
	
	protected function handle_request(){
		global $wp;
		$title = $wp->query_vars['ac_title'];
		$content = $wp->query_vars['ac_content'];
		$api_key = $wp->query_vars['ac_api_key'];
		if($api_key == get_option('ac_api_key')) {
			$post = array(
				'post_title'=> $title,
				'post_content' => $content,
				'post_status' => 'draft',
				'post_category' => array(get_option('ac_category')),
				'post_author' => get_option('ac_user')
			);
			
			wp_insert_post( $post );
		}
		exit;
	}
	
	function answer_customers_menu() {
		add_options_page( 'Answer Customers Settings', 'Answer Customers', 'manage_options', 'answer_customers_settings', array($this, 'answer_customers_options'));
		add_action( 'admin_init', array($this, 'register_answer_customer_settings') );
	}

	function register_answer_customer_settings() {
		register_setting( 'answer_customers_settings_group', 'ac_api_key' );
		register_setting( 'answer_customers_settings_group', 'ac_user' );
		register_setting( 'answer_customers_settings_group', 'ac_category' );
	}

	function answer_customers_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap">	
			<h2>Answer Customers</h2> 

			<form method="post" action="options.php">
			<?php settings_fields( 'answer_customers_settings_group' ); ?>
			<?php do_settings_sections( 'answer_customers_settings_group' ); ?>
			<table class="form-table">
					<tr valign="top">
					<th scope="row">API Key</th>
					<td><input type="text" name="ac_api_key" style="width: 300px;" value="<?php echo get_option('ac_api_key'); ?>" /></td>
					</tr>
					 
					<tr valign="top">
					<th scope="row">Post As User</th>
					<td><?php wp_dropdown_users(array('name' => 'ac_user', 'selected' => get_option('ac_user') )); ?></td>
					</tr>
					
					<tr valign="top">
					<th scope="row">Post In Category</th>
					<td><?php wp_dropdown_categories(array('name' => 'ac_category', 'selected' => get_option('ac_category'), 'hide_empty' => FALSE )); ?></td>
					</tr>
			</table>
			
			<?php submit_button(); ?>

			</form>
		</div>

	<?php include 'instructions.php'; ?>

<?php 
	}

}

new Answer_Customers_API_Endpoint();
