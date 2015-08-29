<?php

/*
 Plugin Name: St Andrews Related Pages
 Description: A custom widget for St Andrews website to show related page from the page tree in a sidebar widget.
 Author: David Bick, follwing a plugin by Sean Barton
 */

function sa_related_pages_render_entry($title, $permalink, $post_class) {
	$return = '<li class="' . $post_class . '"><a href="' . $permalink . '">' . $title . '</a></li>';
	return $return;
}

function sa_related_pages_render_list($related_posts, $post_class = '', $this_page_id=false) {
	$return = false;

	if ($related_posts->have_posts()) {
		$return .= $template_start;
		
		while ($related_posts->have_posts()) {
			$related_posts->the_post();
			global $post;
			$p = $post;

			if($p->ID != $this_page_id) {
				$return .= sa_related_pages_render_entry(
					$p->post_title, 
					get_permalink($p->ID), 
					$post_class
				);
			}
		}
	}
	return $return;
}

function sa_related_pages_find_and_render_list() {
	global $wpdb;
	global $wp_query;
	global $post;

	$post_id = get_the_ID();
	$parent_id = $post->post_parent;
	
	$template_start = '<ul>';
	$template_end = '</ul>';

	$parent_list = $sibling_list = $child_list = false;
	
	if($parent_id) {
		// Parent
		$args = array(
			'post_type'=>'page'
			, 'post_status'=>'publish'
			, 'page_id'=>$parent_id
		);
		$related_posts = new WP_Query($args);
		$parent_list .= sa_related_pages_render_list($related_posts, 'sa_related_pages_parent');

		// Siblings
		$args = array(
			'post_type'=>'page'
			, 'post_status'=>'publish'
			, 'post_parent'=>$parent_id
			, 'orderby'=>'menu_order'
			, 'order'=>'ASC'	);
		$related_posts = new WP_Query($args);
		$sibling_list .= sa_related_pages_render_list($related_posts, 'sa_related_pages_sibling', $post_id);
	}

	// Children
	$args = array(
		'post_type'=>'page'
		, 'post_status'=>'publish'
		, 'post_parent'=>$post_id
		, 'orderby'=>'menu_order'
		, 'order'=>'ASC'	);
	$related_posts = new WP_Query($args);
	$child_list .= sa_related_pages_render_list($related_posts, 'sa_related_pages_child');

	wp_reset_postdata();
	wp_reset_query();	

	// Choose something suitable... children only, if we have them. Otherwise siblings and parent.
	$return = $child_list ? $child_list : $parent_list . $sibling_list;

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