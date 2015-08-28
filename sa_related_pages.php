<?php

/*
 Plugin Name: St Andrews Related Pages
 Description: A custom widget for St Andrews website to show related page from the page tree in a sidebar widget.
 Author: David Bick, follwing a plugin by Sean Barton
 */

function sa_related_pages_render_entry($title, $permalink, $post_class) {
	$return = '<li><a href="' . $permalink . '" class="' . $post_class . '">' . $title . '</a></li>';
	return $return;
}

function sa_related_pages_render_list($related_posts) {
	$return = false;

	if ($related_posts->have_posts()) {
		$return .= $template_start;
		
		while ($related_posts->have_posts()) {
			$related_posts->the_post();
			global $post;
			$p = $post;

			$post_class = '';			
			if ($p->ID == $this_page_id) {
				$post_class = 'sa_related_pages_current_page';
			}
			
			$return .= sa_related_pages_render_entry($p->post_title, get_permalink($p->ID), $post_class);
		}
	}
	return $return;
}

function sa_related_pages_find_and_render_list() {
	global $wpdb;
	global $wp_query;
	global $post;

	$p = $post;
	
	$template_start = '<ul>';
	$template_end = '</ul>';

	$this_page_id = $wp_query->get_queried_object_id();

	$return = false;
	$nest_level++;

	if (!$id) {
		$id = get_the_ID();
	}
	
	if (!$id) {
		return; //in the event the $id variable is still empty.
	}
	
	// Parent


	// Siblings
	$args = array(
		'post_type'=>'page'
		, 'post_status'=>'publish'
		, 'post_parent'=>$p->post_parent
		, 'orderby'=>'menu_order'
		, 'order'=>'ASC'	);
	$related_posts = new WP_Query($args);
	$return .= sa_related_pages_render_list($related_posts);

	// Children
	$args = array(
		'post_type'=>'page'
		, 'post_status'=>'publish'
		, 'post_parent'=>$id
		, 'orderby'=>'menu_order'
		, 'order'=>'ASC'	);
	$related_posts = new WP_Query($args);
	$return .= sa_related_pages_render_list($related_posts);

	wp_reset_postdata();
	wp_reset_query();	

	if($return) {
		$return = $template_start . $return . $template_end;
	}

	return $return;
}

function sa_related_pages_loaded() {
	add_action('widgets_init', create_function('', 'return register_widget("sa_related_pages_pages_widget");'));
}

class sa_related_pages_pages_widget extends WP_Widget {
    function sa_related_pages_pages_widget() {
        parent::WP_Widget(false, 'St Andrews Related Pages');	
    }

    function widget($args, $instance) {
		global $sbu;
		
	    extract($args);
	    $title = apply_filters('widget_title', $instance['title']);
	    $text = apply_filters('widget_text', $instance['text']);
		$list = sa_related_pages_find_and_render_list();
		
		if ($list) {
			echo $before_widget;
			
			if ($title) {
			    echo $before_title . $title . $after_title;
			}		    
		
			if ($text) {
				echo $text;
			}
						
			echo $list;
			
			echo $after_widget;
		}
    }

    function update($new_instance, $old_instance) {
        return $new_instance;
    }

    function form($instance) {
		global $sbu;
	
        $title = esc_attr($instance['title']);
		$text = trim(esc_attr($instance['text']));
	
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
	    <p><label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Intro Text (optional):'); ?> <textarea class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea></label></p>
	    <?php
	}
	
}

add_action('plugins_loaded', 'sa_related_pages_loaded');

?>