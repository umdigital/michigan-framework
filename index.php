<?php

global $post;

if( !is_front_page() ) {
    get_template_part( 'templates/homepage' );
}
else {
    get_template_part( 'templates/posts', ($post->post_type != 'posts' ? $post->post_type : null) );
}
