<?php
/*
 * Plugin Name:       Clients list
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Plugin to display clients with a Gutenberg block.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Romina Aliotta
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       clients-list
 * Domain Path:       /languages
 */

define('CL_PATH', plugin_dir_path( __FILE__ ));
//blocks
require_once CL_PATH . '/blocks/clients/index.php';

/**
* Register the "Clients" custom post type
*/
function clients_setup_post_type() {
	register_post_type('clients',
		array(
			'labels'      => array(
      'name'                  => __( 'Clients', ' clients-list' ),
      'singular_name'         => __( 'Client', ' clients-list' ),
      'all_items'             => __( 'All Client', ' clients-list' ),
      'add_new_item'          => __( 'Add new client', ' clients-list' ),
      'edit_item'             => __( 'Edit client', ' clients-list' ),
      'view_item'             => __( 'View client', ' clients-list' ),
			),
			'public'                => true,
			'has_archive'           => true,
			'rewrite'               => array( 'slug' => 'clients' ), 
      'show_in_rest'          => true,
      'menu_icon'             => 'dashicons-businessman',
      'supports'              => array( 'thumbnail', 'title', 'custom-fields' ), 
		)
	);
} 

/*** adding metaboxes for clients information */
function cl_clients_custom_fields() {
    add_meta_box(
      'client_name',
      'Nombre del cliente',
      'cl_clients_name_metabox',
      'clients',
      'normal',
      'default'
    );
    add_meta_box(
      'client_lastname',
      'Apellido del cliente',
      'cl_clients_lastname_metabox',
      'clients',
      'normal',
      'default'
    );
    add_meta_box(
      'client_province',
      'Provincia del Cliente',
      'cl_clients_province_metabox',
      'clients', 
      'normal',
      'default'
    );
}

function cl_clients_name_metabox( $post ) {
   // Add nonce 
   wp_nonce_field( basename(__FILE__), 'client_meta_nonce' );

  $client_name = get_post_meta( $post->ID, 'client_name', true );

  ?>
  <label for="client_name">Nombre del cliente:</label>
  <input type="text" id="client_name" name="client_name" value="<?php echo esc_attr( $client_name ); ?>" size="30" />
  <?php
}

function cl_clients_lastname_metabox( $post ) {
  // Add nonce 
  wp_nonce_field( basename(__FILE__), 'client_meta_nonce' );
  $client_address = get_post_meta( $post->ID, 'client_lastname', true );

  ?>
  <label for="client_lastname">Apellido del cliente:</label>
  <input type="text" id="client_lastname" name="client_lastname" value="<?php echo esc_attr( $client_address ); ?>" size="50" />
  <?php
}

function cl_clients_province_metabox( $post ) {
  // Add nonce 
  wp_nonce_field( basename(__FILE__), 'client_meta_nonce' );
  $selected_province = get_post_meta($post->ID, 'client_province', true);

  $url = 'https://apis.datos.gob.ar/georef/api/provincias';
  $response = wp_remote_get($url);

  if (is_wp_error($response)) {
      echo 'Error conecting API';
      return;
  }

  $body = wp_remote_retrieve_body($response);
  $provincias = json_decode($body);

  if (!$provincias || empty($provincias->provincias)) {
      echo 'No data found';
      return;
  }

  // Render field select province
  echo '<label for="client_province">Selecciona la provincia:</label><br>';
  echo '<select name="client_province" id="client_province">';
  foreach ($provincias->provincias as $provincia) {
      $selected = ($selected_province == $provincia->nombre) ? 'selected' : '';
      echo '<option value="' . esc_attr($provincia->nombre) . '" ' . $selected . '>' . esc_html($provincia->nombre) . '</option>';
  }
  echo '</select>';
}

// Save metaboxes clients data
function cl_save_clients_metabox( $post_id ) {
  // Verify nonce
  if (!isset($_POST['client_meta_nonce']) || !wp_verify_nonce($_POST['client_meta_nonce'], basename(__FILE__))) {
    return $post_id;
  }

  // If user can edit
  if (!current_user_can('edit_post', $post_id)) {
      return $post_id;
  }

  // Save fields
  $fields = array('client_name', 'client_lastname', 'client_province');
  foreach ($fields as $field) {
      $new_value = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : '';
      update_post_meta($post_id, $field, $new_value); 
  }
}

// Register custom meta fields for REST API
function register_custom_meta_fields() {
  register_meta('post', 'client_name', array(
      'type'              => 'string',
      'description'       => 'Client Name',
      'single'            => true,
      'show_in_rest'      => true,
      'object_subtype'    => 'clients',
  ));

  register_meta('post', 'client_lastname', array(
      'type'              => 'string',
      'description'       => 'Client Lastname',
      'single'            => true,
      'show_in_rest'      => true,
      'object_subtype'    => 'clients',
  ));

  register_meta('post', 'client_province', array(
      'type'              => 'string',
      'description'       => 'Client Province',
      'single'            => true,
      'show_in_rest'      => true,
      'object_subtype'    => 'clients',
  ));
}

// Register actions
add_action('init', 'clients_setup_post_type');
add_action('add_meta_boxes', 'cl_clients_custom_fields');
add_action('save_post', 'cl_save_clients_metabox');
add_action('rest_api_init', 'register_custom_meta_fields');


/**
 * Activate the plugin.
 */
function clients_activate() { 
	clients_setup_post_type();
  cl_clients_custom_fields();
  register_custom_meta_fields();
	flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'clients_activate' );

/**
 * Deactivation hook.
 */
function pluginprefix_deactivate() {
	// Uncomment to unregister the CPT Clients when deactivate , I dont think is the best choice
	//unregister_post_type( 'clients' );
	flush_rewrite_rules();
  //.... else to do
}
register_deactivation_hook( __FILE__, 'clients_deactivate' );

