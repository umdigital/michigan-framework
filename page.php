<?php

global $post;

if( is_front_page() ) {
    get_template_part( 'templates/homepage' );
}
else {
    get_template_part( 'templates/page', ($post->post_type != 'page' ? $post->post_type : null) );
}
