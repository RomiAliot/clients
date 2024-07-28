<?php

function cl_clients_render_callback( $block_attributes, $block_content ) {
	$block_classes = isset( $block_attributes['className'] )
		? $block_attributes['className'] . 'wp-block-cl-clients'
		: 'wp-block-cl-clients';

	$args = array(
		'post_type'      => 'clients',
		'posts_per_page' => -1, 
		'orderby'        => 'date', 
		'order'          => 'DESC'
	);

  if ( isset( $block_attributes['numberOfClients'] ) && !empty( $block_attributes['numberOfClients'] ) ) {
    $args['posts_per_page'] = intval( $block_attributes['numberOfClients'] );
  }

  if ( isset( $block_attributes['order'] ) && in_array( $block_attributes['order'], array( 'asc', 'desc' ) ) ) {
      $args['order'] = strtoupper( $block_attributes['order'] );
  }

  $query = new WP_Query( $args );

   // Output the content
   $output = '<div class="' . esc_attr( $block_classes ) . '">';
   if ( $query->have_posts() ) {
       $output .= '<table>';
       $output .= '<thead><tr>';
       $output .= '<th>' . esc_html( $block_attributes['nameColumnTitle'] ) . '</th>';
       $output .= '<th>' . esc_html( $block_attributes['lastnameColumnTitle'] ) . '</th>';
       $output .= '<th>' . esc_html( $block_attributes['provinceColumnTitle'] ) . '</th>';
       $output .= '</tr></thead>';
       $output .= '<tbody>';

       while ( $query->have_posts() ) {
           $query->the_post();
           $client_name = get_post_meta( get_the_ID(), 'client_name', true );
           $client_lastname = get_post_meta( get_the_ID(), 'client_lastname', true );
           $client_province = get_post_meta( get_the_ID(), 'client_province', true );
           
           $output .= '<tr>';
           $output .= '<td>' . esc_html( $client_name ) . '</td>';
           $output .= '<td>' . esc_html( get_post_meta( get_the_ID(), 'client_lastname', true ) ) . '</td>';
           $output .= '<td>' . esc_html( $client_province ) . '</td>';
           $output .= '</tr>';
       }

       $output .= '</tbody>';
       $output .= '</table>';
   } else {
       $output .= '<p>No clients found</p>';
   }
   $output .= '</div>';

   wp_reset_postdata();

   return $output;
}

add_action( 'init', 'cl_clients_block_init' );
function cl_clients_block_init() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cl_clients_render_callback',
		)
	);
}