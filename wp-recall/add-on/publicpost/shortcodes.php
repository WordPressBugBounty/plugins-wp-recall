<?php

add_shortcode( 'public-form', 'rcl_publicform' );
function rcl_publicform( $atts ) {

	if ( rcl_is_gutenberg() ) {
		return false;
	}

	$form = new Rcl_Public_Form( array_map('esc_attr', $atts) );

	return $form->get_form();
}
