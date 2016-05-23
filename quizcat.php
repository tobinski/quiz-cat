<?php
/*
Plugin Name: Quiz Cat
Plugin URI: https://fatcatapps.com/quiz-cat
Description: Provides an easy way to create and administer quizes
Text Domain: quiz-cat
Domain Path: /languages
Author: Fatcat Apps
Author URI: https://fatcatapps.com/
License: GPLv2
Version: 1.0.5
*/

// Create a helper function for easy SDK access.
function fca_qc_fs() {
    global $fca_qc_fs;

    if ( ! isset( $fca_qc_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $fca_qc_fs = fs_dynamic_init( array(
            'id'                => '284',
            'slug'              => 'quiz-cat',
            'public_key'        => 'pk_4ca03d6e4a1d5e18948fa9839ccb2',
            'is_premium'        => false,
            'has_addons'        => false,
            'has_paid_plans'    => false,
            'menu'              => array(
                'slug'       => 'edit.php?post_type=fca_qc_quiz',
                'account'    => false,
                'support'    => false,
            ),
        ) );
    }

    return $fca_qc_fs;
}

// Init Freemius.
fca_qc_fs();

//Freemius filter
function fca_qc_fs_custom_connect_message_on_update(
	$message,
	$user_first_name,
	$plugin_title,
	$user_login,
	$site_link,
	$freemius_link
) {
	return __('Hey ', 'quiz-cat') . $user_first_name .  ', ' . '<br>'
			. __('In order to enjoy all our features and functionality', 'quiz-cat' ) . ', Quiz Cat '
			. __('needs to connect your user', 'quiz-cat' ) . ", $user_login" . ' ' 
			. __("at $site_link, to freemius.com.", 'quiz-cat' );
}

fca_qc_fs()->add_filter('connect_message_on_update', 'fca_qc_fs_custom_connect_message_on_update', 10, 6);

// BASIC SECURITY
defined( 'ABSPATH' ) or die( 'Unauthorized Access!' );

// DEFINE SOME USEFUL CONSTANTS
define( 'FCA_QC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FCA_QC_PLUGINS_URL', plugins_url( '', __FILE__ ) );

//ADD AJAX HANDLERS
add_action( 'wp_ajax_fca_qc_submit_form', 'fca_qc_submit_form' );
add_action( 'wp_ajax_nopriv_fca_qc_submit_form', 'fca_qc_submit_form' );

//RUN FILTER ON FRONT-END STRINGS
$strings_array = array (
	'no_quiz_found' => __('No Quiz found', 'quiz-cat'),
	'correct' => __('Correct!', 'quiz-cat'),
	'wrong' => __('Wrong!', 'quiz-cat'),
	'your_answer' => __('Your answer:', 'quiz-cat'),
	'correct_answer' => __('Correct answer:', 'quiz-cat'),
    'question' => __('Question', 'quiz-cat'),
	'next' =>  __('Next', 'quiz-cat'),
	'you_got' =>  __('You got', 'quiz-cat'),
	'out_of' => __('out of', 'quiz-cat'),
	'your_answers' =>  __('Your Answers', 'quiz-cat'),
	'start_quiz' => __('Start Quiz', 'quiz-cat'),
	'retake_quiz' => __('Retake Quiz', 'quiz-cat'),
);
$quiz_text_strings = apply_filters( 'fca_qc_quiz_text', $strings_array );



////////////////////////////
//		SET UP POST TYPE
////////////////////////////

//REGISTER CPT
function fca_qc_register_post_type() {
	
	$labels = array(
		'name' => _x('Quizzes','quiz-cat'),
		'singular_name' => _x('Quiz','quiz-cat'),
		'add_new' => _x('Add New','quiz-cat'),
		'all_items' => _x('All Quizzes','quiz-cat'),
		'add_new_item' => _x('Add New Quiz','quiz-cat'),
		'edit_item' => _x('Edit Quiz','quiz-cat'),
		'new_item' => _x('New Quiz','quiz-cat'),
		'view_item' => _x('View Quiz','quiz-cat'),
		'search_items' => _x('Search Quizzes','quiz-cat'),
		'not_found' =>  _x('Quiz not found','quiz-cat'),
		'not_found_in_trash' => _x('No Quizzes found in trash','quiz-cat'),
		'parent_item_colon' => _x('Parent Quiz:','quiz-cat'),
		'menu_name' => _x('Quiz Cat','quiz-cat')
	);
		
	$args = array(
		'labels' => $labels,
		'description' => "",
		'public' => false,
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_nav_menus' => false,
		'show_in_menu' => true,
		'show_in_admin_bar' => true,
		'menu_position' => 103,
		'menu_icon' => FCA_QC_PLUGINS_URL . '/assets/icon.png',
		'capability_type' => 'post',
		'hierarchical' => false,
		'supports' => array('title'),
		'has_archive' => false,
		'rewrite' => false,
		'query_var' => true,
		'can_export' => true
	);
	
	register_post_type( 'fca_qc_quiz', $args );
}
add_action ( 'init', 'fca_qc_register_post_type' );


//CHANGE CUSTOM 'UPDATED' MESSAGES FOR OUR CPT
function fca_qc_post_updated_messages( $messages ){
	
	$post = get_post();
	
	$messages['fca_qc_quiz'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Quiz updated.','quiz-cat'),
		2  => __( 'Quiz updated.','quiz-cat'),
		3  => __( 'Quiz deleted.','quiz-cat'),
		4  => __( 'Quiz updated.','quiz-cat'),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Quiz restored to revision from %s','quiz-cat'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Quiz published.' ,'quiz-cat'),
		7  => __( 'Quiz saved.' ,'quiz-cat'),
		8  => __( 'Quiz submitted.' ,'quiz-cat'),
		9  => sprintf(
			__( 'Quiz scheduled for: <strong>%1$s</strong>.','quiz-cat'),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
		),
		10 => __( 'Quiz draft updated.' ,'quiz-cat'),
	);

	return $messages;
}
add_filter('post_updated_messages', 'fca_qc_post_updated_messages' );



//Customize CPT table columns

function fca_qc_add_new_post_table_columns($columns) {
	
    $new_columns['cb'] = '<input type="checkbox" />';
    $new_columns['title'] = _x('Quiz Name', 'column name', 'quiz-cat');    
    $new_columns['shortcode'] = __('Shortcode', 'quiz-cat');
    $new_columns['date'] = _x('Date', 'column name', 'quiz-cat');
 
    return $new_columns;
}
add_filter('manage_edit-fca_qc_quiz_columns', 'fca_qc_add_new_post_table_columns');

function fca_qc_manage_post_table_columns($column_name, $id) {
    switch ($column_name) {
	    case 'shortcode':
	        echo '<input type="text" readonly="readonly" onclick="this.select()" value="[quiz-cat id=&quot;'. $id . '&quot;]"/>';
	            break;
	 
	    default:
	        break;
    } // end switch
}   
// Add to admin_init function
add_action('manage_fca_qc_quiz_posts_custom_column', 'fca_qc_manage_post_table_columns', 10, 2);


//PREVIEW
function fca_qc_live_preview( $content ){
	global $post;
	if ( is_user_logged_in() && $post->post_type === 'fca_qc_quiz' && is_main_query() && !doing_action( 'wp_head' ) )  {
		return $content . do_shortcode("[quiz-cat id='" . $post->ID . "']");
	} else {
		return $content;
	}
}
add_filter( 'the_content', 'fca_qc_live_preview');


////////////////////////////
//		EDITOR PAGE 
////////////////////////////


//ENQUEUE ANY SCRIPTS OR CSS FOR OUR ADMIN PAGE EDITOR
function fca_qc_admin_cpt_script( $hook ) {
	global $post;  
	if ( ($hook == 'post-new.php' || $hook == 'post.php')  &&  $post->post_type === 'fca_qc_quiz' ) {  
		wp_enqueue_media();	
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');		
		wp_enqueue_script('fca_qc_admin_js', FCA_QC_PLUGINS_URL . '/includes/admin.min.js' );		
		wp_enqueue_style( 'fca_qc_admin_stylesheet', FCA_QC_PLUGINS_URL . '/includes/admin.min.css' );
		
		$admin_data = array (
			//A TEMPLATE DIV OF THE QUESTION AND RESULT DIVS, SO WE CAN ADD MORE OF THEM VIA JAVASCRIPT
			'questionDiv' => 	fca_qc_render_question_meta_box( array(), '{{QUESTION_NUMBER}}', 'return' ),
			'resultDiv' 	=> 	fca_qc_render_result_meta_box( array(), '{{RESULT_NUMBER}}', 'return' ),
			
			//SOME LOCALIZATION STRINGS FOR JAVASCRIPT STUFF
			'navigationWarning_string' => __( "You have entered new data on this page.  If you navigate away from this page without first saving your data, the changes will be lost.", 'quiz-cat'),
			'sureWarning_string' => 	 __( 'Are you sure?', 'quiz-cat'),
			'selectImage_string' => __('Select Image', 'quiz-cat' ),			
			'remove_string' =>  __('remove', 'quiz-cat'),
			'show_string' =>  __('show', 'quiz-cat'),
			'unused_string' =>  __('Unused', 'quiz-cat'),
			'points_string' =>  __('Points', 'quiz-cat'),
			'image_placeholder_url' => FCA_QC_PLUGINS_URL . '/assets/fca-qc-image-placeholder.png',
			'question_string' =>  __('Question', 'quiz-cat'),
			'save_string' =>  __('Save', 'quiz-cat'),
			'preview_string' =>  __('Save & Preview', 'quiz-cat'),
			'on_string' =>  __('YES', 'quiz-cat'),
			'off_string' =>  __('NO', 'quiz-cat'),
		);
		
		wp_localize_script( 'fca_qc_admin_js', 'adminData', $admin_data ); 
    }
}
add_action( 'admin_enqueue_scripts', 'fca_qc_admin_cpt_script', 10, 1 );  

//ADD META BOXES TO EDIT CPT PAGE
function add_custom_meta_boxes() {

	add_meta_box( 
        'fca_qc_description_meta_box',
        __( 'This Quiz', 'quiz-cat' ),
        'fca_qc_render_description_meta_box',
        null,
        'normal',
        'high'
    );	
	
	add_meta_box( 
        'fca_qc_questions_meta_box',
        __( 'Questions', 'quiz-cat' ),
        'fca_qc_render_questions_meta_box',
        null,
        'normal',
        'default'
    );
	

	add_meta_box( 
        'fca_qc_add_result_meta_box',
        __( 'Scoring (Optional)', 'quiz-cat' ),
        'fca_qc_render_add_result_meta_box',
        null,
        'normal',
        'default'
    );

	add_meta_box( 
        'fca_qc_quiz_settings_meta_box',
        __( 'Settings', 'quiz-cat' ),
        'fca_qc_render_quiz_settings_meta_box',
        null,
        'normal',
        'default'
    );
	
	//SIDE META BOX, UNUSED 
/*	
	add_meta_box( 
        'fca_qc_opt_in_meta_box',
        __( 'Need Quiz Ideas?', 'quiz-cat' ),
        'fca_qc_render_opt_in',
        null,
        'side',
        'high'
    );
*/		
}
add_action( 'add_meta_boxes_fca_qc_quiz', 'add_custom_meta_boxes' );

//RENDER THE DESCRIPTION META BOX
function fca_qc_render_description_meta_box( $post ) {
	
	$quiz_meta = get_post_meta ( $post->ID, 'quiz_cat_meta', true );
	
	$img_placeholder = FCA_QC_PLUGINS_URL . '/assets/fca-qc-image-placeholder.png';
	empty ( $quiz_meta['desc'] ) ? $quiz_meta['desc'] = '' : '';
	empty ( $quiz_meta['desc_img_src'] ) ? $quiz_meta['desc_img_src'] = $img_placeholder : '';

	//ADD A HIDDEN PREVIEW URL INPUT
	echo "<input type='text' class='fca_qc_image_input' name='fca_qc_quiz_preview_url' id='fca_qc_quiz_preview_url'  hidden readonly data='" . get_permalink( $post->ID ) . "'>";
	
	echo "<label class='fca_qc_admin_label'>" . __('Description (Optional)', 'quiz-cat') . "</label>";
	echo "<textarea class='fca_qc_texta' id='fca_qc_quiz_description' name='fca_qc_quiz_description'>" . $quiz_meta['desc'] . "</textarea>";	
	
	echo "<label class='fca_qc_admin_label'>" . __('Image (Optional)', 'quiz-cat') . "</label>";
	echo "<input type='text' class='fca_qc_image_input' name='fca_qc_quiz_description_image_src' id='fca_qc_quiz_description_image_src' style='display: none;' value='" . $quiz_meta['desc_img_src'] . "'>";
	//ASSIGN PLACEHOLDER IMAGE
	
	echo "<img class='fca_qc_image' id='fca_qc_quiz_description_image' style='max-width: 252px' src='" . $quiz_meta['desc_img_src'] . "'>";
	echo "<div class='fca_qc_image_hover_controls'>";
		
		//IF PLACEHOLDER IS THERE DON'T SHOW THE "REMOVE OR CHANGE" BUTTON
		if ( $quiz_meta['desc_img_src'] == $img_placeholder ) {
			echo "<button type='button' class='button-secondary fca_qc_quiz_image_upload_btn' style='display:none;'>" . __('Change', 'quiz-cat') . "</button>";
			echo "<button type='button' class='button-secondary fca_qc_quiz_image_revert_btn' style='display:none;'>" . __('Remove', 'quiz-cat') . "</button>";
		}else {
			echo "<button type='button' class='button-secondary fca_qc_quiz_image_upload_btn'>" . __('Change', 'quiz-cat') . "</button>";
			echo "<button type='button' class='button-secondary fca_qc_quiz_image_revert_btn'>" . __('Remove', 'quiz-cat') . "</button>";
		}
		
	echo '</div>';	
}

//RENDER THE ADD QUESTION META BOX
function fca_qc_render_questions_meta_box( $post ) {
		
	$questions = get_post_meta ( $post->ID, 'quiz_cat_questions', true );
	echo "<div class='fca_qc_sortable_questions'>";
	if ( empty ( $questions ) ) {
		
		fca_qc_render_question_meta_box( array(), 1, 'echo' );
		
	} else {
		
		$counter = 1;
		
		forEach ( $questions as $question ) {
			
			fca_qc_render_question_meta_box( $question, $counter, 'echo' );
			$counter = $counter + 1;
			
		}		
	}	
	echo "</div>";
	echo "<button type='button' id='fca_qc_add_question_btn' class='button-secondary fca_qc_add_btn' ><span class='dashicons dashicons-plus' style='vertical-align: text-top;'></span>" .__('Add', 'quiz-cat') . "</button>";
	
}

// RENDER A QUESTION META BOX
// INPUT: ARRAY->$question [QUESTION, ANSWER, IMG, HINT, WRONG1, WRONG2, WRONG3], STRING->$operation ('echo' OR 'return')
// OUTPUT: ECHO OR RETURNED HTML 
function fca_qc_render_question_meta_box( $question, $question_number, $operation = 'echo' ) {
	$default_image = FCA_QC_PLUGINS_URL . '/assets/fca-qc-image-placeholder.png';
	if ( empty ( $question ) ) {
		$question = array(
			'question' => '',
			'answer' => '',
			'img' => $default_image,
			'hint' => '',
			'wrong1' => '',
			'wrong2' => '',
			'wrong3' => '',
		
		);
		
	}
	
	empty ( $question['img'] ) ? $question['img'] = $default_image : '';
	
	$html = "<div class='fca_qc_question_item fca_qc_deletable_item' id='fca_qc_question_$question_number'>";
		$html .= "<span class='dashicons dashicons-trash fca_qc_delete_icon'></span>";
		$html .= "<h3 class='fca_qc_question_label'><span class='fca_qc_quiz_heading_question_number'>" . __('Question', 'quiz-cat') . ' ' . $question_number . ": </span><span class='fca_qc_quiz_heading_text'>". fca_qc_convert_entities($question['question']) . "</span></h3>";
			
			$html .= "<div class='fca_qc_question_input_div'>";
			
				$html .= "<label class='fca_qc_admin_label'>" . __('Question', 'quiz-cat') . "</label>";
				$html .= "<textarea class='fca_qc_question_texta fca_qc_question_text' name='fca_qc_quiz_question[]'>" . $question['question']  ."</textarea><br>";

				$html .= "<label class='fca_qc_admin_label'>" . __('Image (Optional)', 'quiz-cat') . "</label>";
				$html .= '<input type="text" class="fca_qc_image_input" name="fca_qc_quiz_question_image_src[]" style="display: none;" value="' . $question['img'] . '">';
				$html .= '<img class="fca_qc_image" style="max-width: 252px" src="' . $question['img'] . '">';
				$html .= "<div class='fca_qc_image_hover_controls'>";
									
					if (  $question['img'] == $default_image  ) {
						$html .= "<button type='button' class='button-secondary fca_qc_quiz_image_upload_btn' style='display:none;'>" . __('Change', 'quiz-cat') . "</button>";
						$html .= "<button type='button' class='button-secondary fca_qc_quiz_image_revert_btn' style='display:none;'>" . __('Remove', 'quiz-cat') . "</button>";
					} else {
						$html .= "<button type='button' class='button-secondary fca_qc_quiz_image_upload_btn'>" . __('Change', 'quiz-cat') . "</button>";
						$html .= "<button type='button' class='button-secondary fca_qc_quiz_image_revert_btn'>" . __('Remove', 'quiz-cat') . "</button>";
					}
				$html .= '</div>';	
				
				
				$html .= "<label class='fca_qc_admin_label'>" . __('Correct Answer', 'quiz-cat') . "</label>";
				$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_answer[]'>" . $question['answer']  ."</textarea><br>";

				$html .= "<label class='fca_qc_admin_label'>" . __('Wrong Answer 1', 'quiz-cat') . "</label>";
				$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_wrong_1[]'>" . $question['wrong1']  ."</textarea><br>";
				
				if ( empty ($question['wrong2']) ) {
					$html .= "<label class='fca_qc_admin_label'>" . __('Wrong Answer 2', 'quiz-cat') . " <span class='fca_qc_answer_toggle'>(" . __('show', 'quiz-cat') . ")</span></label>";
					$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_wrong_2[]' style='display:none;'>" . $question['wrong2']  ."</textarea><br>";
				} else {
					$html .= "<label class='fca_qc_admin_label'>" . __('Wrong Answer 2', 'quiz-cat') . " <span class='fca_qc_answer_toggle'>(" . __('remove', 'quiz-cat') . ")</span></label>";
					$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_wrong_2[]'>" . $question['wrong2']  ."</textarea><br>";
				}
				
				if ( empty ($question['wrong3']) ) {
					$html .= "<label class='fca_qc_admin_label'>" . __('Wrong Answer 3', 'quiz-cat') . " <span class='fca_qc_answer_toggle'>(" . __('show', 'quiz-cat') . ")</span></label>";
					$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_wrong_3[]' style='display:none;'>" . $question['wrong3']  ."</textarea><br>";
				} else {
					$html .= "<label class='fca_qc_admin_label'>" . __('Wrong Answer 3', 'quiz-cat') . " <span class='fca_qc_answer_toggle'>(" . __('remove', 'quiz-cat') . ")</span></label>";
					$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_wrong_3[]' >" . $question['wrong3']  ."</textarea><br>";
				}

				
			$html .= "</div >";
			
	$html .= "</div >";
	
	if ( $operation == 'return' ) {
		return $html;
	} else {
		 echo $html;
	}
}

//RENDER THE ADD RESULT META BOX
function fca_qc_render_add_result_meta_box( $post ) {
			
	$results = get_post_meta ( $post->ID, 'quiz_cat_results', true );
	echo "<div class='fca_qc_sortable_results'>";
	if ( empty ( $results ) ) {
		
		fca_qc_render_result_meta_box( array(), 1, 'echo' );
		
	} else {
		
		$counter = 1;
		
		forEach ( $results as $result ) {
			
			fca_qc_render_result_meta_box( $result, $counter, 'echo' );
			
			$counter = $counter + 1;
			
		}		
	}
	echo "</div>";	
	echo "<button type='button' id='fca_qc_add_result_btn' class='button-secondary fca_qc_add_btn' ><span class='dashicons dashicons-plus' style='vertical-align: text-top;'></span>" . __('Add', 'quiz-cat') . "</button>";

}

// RENDER A RESULT META BOX
// INPUT: ARRAY->$result (TITLE, DESC, IMG), INT|STRING->$result_number, STRING->$operation ('echo' OR 'return')
// OUTPUT: ECHO OR RETURNED HTML
function fca_qc_render_result_meta_box( $result, $result_number, $operation = 'echo' ) {
	
	$default_image = FCA_QC_PLUGINS_URL . '/assets/fca-qc-image-placeholder.png';
	
	if ( empty ( $result ) ) {
		$result = array(
			'title' => '',
			'desc' => '',
			'img' => $default_image,
		
		);
		
	}
	
	empty ( $result['img'] ) ? $result['img'] = $default_image : '';
	
	$html = "<div class='fca_qc_result_item fca_qc_deletable_item' id='fca_qc_result_$result_number'>";
		$html .= "<span class='dashicons dashicons-trash fca_qc_delete_icon'></span>";
		$html .= "<h3 class='fca_qc_result_label'><span class='fca_qc_result_score_value'></span><span class='fca_qc_result_score_title'>" . $result['title'] . "</span></h3>";
		
		$html .= "<div class='fca_qc_result_input_div'>";
			
				$html .= "<label class='fca_qc_admin_label'>" . __('Result Title', 'quiz-cat') . "</label>";
				$html .= "<input type='text' class='fca_qc_text_input fca_qc_quiz_result' name='fca_qc_quiz_result_title[]' value='" . $result['title'] . "'></input><br>";
				$html .= "<label class='fca_qc_admin_label'>" . __('Description (Optional)', 'quiz-cat') . "</label>";
				$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_result_description[]'>" . $result['desc'] . "</textarea><br>";
			
				$html .= "<label class='fca_qc_admin_label'>" . __('Image (Optional)', 'quiz-cat') . "</label>";
				$html .= '<input type="text" class="fca_qc_image_input" name="fca_qc_quiz_result_image_src[]" style="display: none;" value="' . $result['img'] . '">';
				$html .= '<img class="fca_qc_image" style="max-width: 252px" src="' . $result['img'] . '">';
				$html .= "<div class='fca_qc_image_hover_controls'>";
									
					if (  $result['img'] == $default_image  ) {
						$html .= "<button type='button' class='button-secondary fca_qc_quiz_image_upload_btn' style='display:none;'>" . __('Change', 'quiz-cat') . "</button>";
						$html .= "<button type='button' class='button-secondary fca_qc_quiz_image_revert_btn' style='display:none;'>" . __('Remove', 'quiz-cat') . "</button>";
					} else {
						$html .= "<button type='button' class='button-secondary fca_qc_quiz_image_upload_btn'>" . __('Change', 'quiz-cat') . "</button>";
						$html .= "<button type='button' class='button-secondary fca_qc_quiz_image_revert_btn'>" . __('Remove', 'quiz-cat') . "</button>";
						
					}
					
				$html .= "</div>";
		
		$html .= '</div>';
		
		//SOME HIDDEN INPUTS FOR THE RANGE OF SCORES FOR THIS RESULT
		$html .= "<input type='number' class='fca_qc_result_min' name='fca_qc_result_min[]' value='-1' hidden >";
		$html .= "<input type='number' class='fca_qc_result_max' name='fca_qc_result_max[]' value='-1' hidden >";
		
	$html .= "</div>";
	
	if ( $operation == 'return' ) {
		return $html;
	} else {
		echo $html;
	}
}

//RENDER A SIDE META BOX (UNUSED)
function fca_qc_render_side_meta_box() {
}

function fca_qc_render_opt_in() {
	global $post;
	
	if ( $post->post_type === 'fca_qc_quiz' ) {
		include_once 'includes/sidebar.php';
	}	

}
add_action( 'admin_footer-post.php', 'fca_qc_render_opt_in' );
add_action( 'admin_footer-post-new.php', 'fca_qc_render_opt_in' );

//RENDER THE QUIZ SETTINGS META BOX 
function fca_qc_render_quiz_settings_meta_box( $post ) {
	
	$settings = get_post_meta ( $post->ID, 'quiz_cat_settings', true );
	
	$hide_answers = empty ( $settings['hide_answers'] ) ? '' : "checked='checked'";
	
	
	$shortcode = '[quiz-cat id="' . $post->ID . '"]';
	echo "<table id='fca_qc_setting_table'>";
		echo "<tr>";
			echo "<th>";
				echo "<label class='fca_qc_admin_label fca_qc_admin_settings_label' for='fca_qc_hide_answers_until_end'>" . __('Hide answers until the end of the quiz', 'quiz-cat') . "</label>";
			echo "</th>";
			echo "<td>";
				echo "<div class='onoffswitch'>";
					echo "<input type='checkbox' class='onoffswitch-checkbox' id='fca_qc_hide_answers_until_end' style='display:none;' name='fca_qc_hide_answers_until_end' $hide_answers></input>";		
				echo "<label class='onoffswitch-label' for='fca_qc_hide_answers_until_end'><span class='onoffswitch-inner'><span class='onoffswitch-switch'></span></span></label>";
				echo "</div>";
			echo "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<th>";
				echo "<label class='fca_qc_admin_label fca_qc_admin_settings_label' for='fca_qc_shortcode_input'>" . __('Shortcode', 'quiz-cat') . "</label>";
			echo "</th>";
			echo "<td>";
				echo "<input type='text' class='fca_qc_text_input' id='fca_qc_shortcode_input' name='fca_qc_shortcode_input' value='$shortcode' readonly>";		
			echo "</td>";
		echo "<tr>";
	echo "</table>";
}



//CUSTOM SAVE HOOK
function fca_qc_save_post( $post_id ) {
	
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
		return $post_id;
	}
	
	//ONLY DO OUR STUFF IF ITS A REAL SAVE, NOT A NEW IMPORTED ONE
	if ( !empty ( $_POST['fca_qc_quiz_description_image_src'] ) ) {
			
		
		//DON'T SAVE THE DEFAULT IMAGE:
		$default_image = FCA_QC_PLUGINS_URL . '/assets/fca-qc-image-placeholder.png';
		
		
		//SAVING META DATA ( DESCRIPTION, IMAGE )
		$meta_fields = array (
			'fca_qc_quiz_description'	=> 'desc',
			'fca_qc_quiz_description_image_src'	=> 'desc_img_src',
		);
		
		$meta = array();
		
		forEach ( $meta_fields as $key => $value ) {
			empty ( $_POST[$key] ) ?  $_POST[$key] = '' : '';
			$meta[$value] = $_POST[$key];
			
			$meta[$value] == $default_image ? $meta[$value] = '' : '';
		}
		
		update_post_meta ( $post_id, 'quiz_cat_meta', $meta );
		
		//SAVING QUESTIONS
		$questions = array();
		
		$n = empty ( $_POST['fca_qc_quiz_question'] ) ? 0 : count ( $_POST['fca_qc_quiz_question'] );
		
		for ($i = 0; $i < $n ; $i++) {
			$questions[$i]['question'] = fca_qc_escape_input( $_POST['fca_qc_quiz_question'][$i] );
			$questions[$i]['answer'] = fca_qc_escape_input( $_POST['fca_qc_quiz_answer'][$i] );
			
			$questions[$i]['img'] = fca_qc_escape_input( $_POST['fca_qc_quiz_question_image_src'][$i] );
			$questions[$i]['img'] == $default_image ? $questions[$i]['img'] = '' : '';
			
			$questions[$i]['wrong1'] = fca_qc_escape_input( $_POST['fca_qc_quiz_wrong_1'][$i] );
			$questions[$i]['wrong2'] = fca_qc_escape_input( $_POST['fca_qc_quiz_wrong_2'][$i] );
			$questions[$i]['wrong3'] = fca_qc_escape_input( $_POST['fca_qc_quiz_wrong_3'][$i] );
		} 
		
		update_post_meta ( $post_id, 'quiz_cat_questions', $questions );
		
		$results = array();
		
		
		//SAVING RESULTS
		$n = empty ( $_POST['fca_qc_quiz_result_title'] ) ? 0 : count ( $_POST['fca_qc_quiz_result_title'] );
		
		for ($i = 0; $i < $n ; $i++) {
			$results[$i]['title'] = fca_qc_escape_input( $_POST['fca_qc_quiz_result_title'][$i] );
			$results[$i]['desc'] = fca_qc_escape_input( $_POST['fca_qc_quiz_result_description'][$i] );
			$results[$i]['img'] = fca_qc_escape_input( $_POST['fca_qc_quiz_result_image_src'][$i] );
			$results[$i]['min'] = intval ( fca_qc_escape_input( $_POST['fca_qc_result_min'][$i] ) );
			$results[$i]['max'] = intval ( fca_qc_escape_input( $_POST['fca_qc_result_max'][$i] ) );
			
			$results[$i]['img'] == $default_image ? $results[$i]['img'] = '' : '';
			
		} 	
					
		update_post_meta ( $post_id, 'quiz_cat_results', $results );
		
		//SAVING SETTINGS
		$fields = array (
			'fca_qc_hide_answers_until_end'	=> 'hide_answers',
		);
		
		$settings = array();
		
		forEach ( $fields as $key => $value ) {
			empty ( $_POST[$key] ) ?  $_POST[$key] = '' : '';
			$settings[$value] = fca_qc_escape_input( $_POST[$key] );
		}
			
		update_post_meta ( $post_id, 'quiz_cat_settings', $settings );
	}
	
}
add_action( 'save_post_fca_qc_quiz', 'fca_qc_save_post' );

function fca_qc_escape_input($data) {
 
	$data = htmlentities ( $data, ENT_QUOTES, "UTF-8");
		
	return $data;

}

/* Redirect when Save & Preview button is clicked */
add_filter('redirect_post_location', 'fca_qc_save_preview_redirect');
function fca_qc_save_preview_redirect ( $location ) {
    global $post;
 
    if ( !empty($_POST['fca_qc_quiz_preview_url'] ) ) {
		// Flush rewrite rules
		global $wp_rewrite;
		$wp_rewrite->flush_rules(true);
		
        // Always redirect to the post
        $location = $_POST['fca_qc_quiz_preview_url'];
    }
 
    return $location;
}

////////////////////////////
//		DISPLAY QUIZ
////////////////////////////

//SUPPRESS POST TITLES ON OUR CUSTOM POST TYPE
function fca_qc_suppress_post_title() {
	global $post;
	if ( $post->post_type == 'fca_qc_quiz' &&  is_main_query() ) {
		wp_enqueue_style( 'fca_qc_quiz_post_stylesheet', plugins_url( 'includes/hide-title.css', __FILE__ ) );
	}
}	
add_action( 'wp_enqueue_scripts', 'fca_qc_suppress_post_title' );

function fca_qc_do_quiz( $atts ) {
	
	global $quiz_text_strings;
	
	if ( !empty ( $atts[ 'id' ] ) ) {
		$post_id = intVal ( $atts[ 'id' ] );
		$quiz_meta = get_post_meta ( $post_id, 'quiz_cat_meta', true );
		$quiz_meta['title'] = get_the_title ( $post_id );
		$questions = get_post_meta ( $post_id, 'quiz_cat_questions', true );
		$quiz_results = get_post_meta ( $post_id, 'quiz_cat_results', true );
		$quiz_settings = get_post_meta ( $post_id, 'quiz_cat_settings', true );
		
		if ( !$quiz_meta || !$questions ) {
			echo '<p>Quiz Cat: ' . $quiz_text_strings[ 'no_quiz_found' ] . '</p>';
			return false;
		}
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'fca_qc_quiz_stylesheet', plugins_url( 'includes/quiz.min.css', __FILE__ ) );
		wp_enqueue_script( 'fca_qc_img_loaded', plugins_url( 'includes/jquery.waitforimages.min.js', __FILE__ ) );
		wp_enqueue_script( 'fca_qc_quiz_js', plugins_url( 'includes/quiz.min.js', __FILE__ ) );
		
		//SEND JS THE DATA BUT CONVERT ANY ESCAPED THINGS BACK TO NORMAL CHARACTERS
		$quiz_data = array(
			'quiz_meta' => fca_qc_convert_entities($quiz_meta),
			'questions' => fca_qc_convert_entities($questions),
			'quiz_results' => fca_qc_convert_entities($quiz_results),
			'quiz_settings' => $quiz_settings,
			'wrong_string' => $quiz_text_strings[ 'wrong' ],
			'correct_string' => $quiz_text_strings[ 'correct' ],
			'your_answer_string' => $quiz_text_strings[ 'your_answer' ],
			'correct_answer_string' => $quiz_text_strings[ 'correct_answer' ],
		);	
		
		$user_data = array(
			'user_id' => get_current_user_id(),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);
		
		wp_localize_script( 'fca_qc_quiz_js', "quizData_$post_id", $quiz_data );
		wp_localize_script( 'fca_qc_quiz_js', "userData_$post_id", $user_data );
		
		ob_start(); ?>
		<div class='fca_qc_quiz' id='<?php echo "fca_qc_quiz_$post_id" ?>'>
			<h2 class='fca_qc_quiz_title'><?php echo $quiz_meta['title'] ?></h2>
			<p class='fca_qc_quiz_description'><?php echo $quiz_meta['desc'] ?></p>
			<img class='fca_qc_quiz_description_img' src='<?php echo $quiz_meta['desc_img_src'] ?>'>
			
			<button type='button' class='fca_qc_button fca_qc_start_button'><?php echo $quiz_text_strings[ 'start_quiz' ] ?></button>
			
			<div class='flip-container fca_qc_quiz_div' style='display: none;'>
				<div class='fca-qc-flipper'>
					<?php fca_qc_do_question_panel() ?> 
					<?php fca_qc_do_answer_panel() ?> 
					
				</div>
			</div>
			<?php fca_qc_do_score_panel() ?> 
			<button type='button' class='fca_qc_button' id='fca_qc_restart_button' style='display: none;'><?php echo $quiz_text_strings[ 'retake_quiz' ]  ?></button>
			<div class='fca_qc_quiz_footer' style='display: none;'>
				<span class='fca_qc_question_count'></span>		
			</div>
			<?php fca_qc_do_result_panel() ?> 
			
		</div>
		<?php
		
		return ob_get_clean();
	} else {
		return '<p>Quiz Cat: ' . $quiz_text_strings[ 'no_quiz_found' ] . '</p>';
	}
}
add_shortcode( 'quiz-cat', 'fca_qc_do_quiz' );

function fca_qc_do_question_panel( $operation = 'echo' ) {
	global $quiz_text_strings;
	$svg_rectangle = '<svg class="fca_qc_rectancle" width="26" height="26"><rect width="26" height="26" style="fill:#fff;stroke-width:1;stroke:#000"></svg>';
			
	$html = "<div class='fca-qc-front' id='fca_qc_answer_container'>";
		$html .= "<p id='fca_qc_question'>" . $quiz_text_strings['question'] . "</p>";
		$html .= "<img class='fca_qc_quiz_question_img' src=''>";
		$html .= "<div class='fca_qc_answer_div'>$svg_rectangle<span class='fca_qc_answer_span'></span></div>";
		$html .= "<div class='fca_qc_answer_div'>$svg_rectangle<span class='fca_qc_answer_span'></span></div>";
		$html .= "<div class='fca_qc_answer_div'>$svg_rectangle<span class='fca_qc_answer_span'></span></div>";
		$html .= "<div class='fca_qc_answer_div'> $svg_rectangle<span class='fca_qc_answer_span'></span></div>";
	$html .= "</div>";
	
	if ( $operation == 'echo' ) {
		echo $html;
	} else {
		return $html;
	}
}

function fca_qc_do_answer_panel( $operation = 'echo') {
	global $quiz_text_strings;
	$html = "<div class='fca-qc-back' id='fca_qc_back_container'>";
		$html .= "<p id='fca_qc_question_right_or_wrong'></p>";
		$html .= "<img class='fca_qc_quiz_question_img' src=''>";
		$html .= "<span id='fca_qc_question_back'></span>";
		$html .= "<p id='fca_qc_back_response_p' class='fca_qc_back_response'>" . $quiz_text_strings['your_answer'] . " <span id='fca_qc_your_answer'></span></p>";
		$html .= "<p id='fca_qc_correct_answer_p' class='fca_qc_back_response'>" . $quiz_text_strings['correct_answer'] . " <span id='fca_qc_correct_answer'></span></p>";
		$html .= "<button type='button' class='fca_qc_next_question'>" . $quiz_text_strings['next'] . "</button>";
	$html .= "</div>";
	
	if ( $operation == 'echo' ) {
		echo $html;
	} else {
		return $html;
	}
}

function fca_qc_do_score_panel( $operation = 'echo') {
	global $quiz_text_strings;
	$html = "<div class='fca_qc_score_container' style='display:none;'>";
		$html .= "<p class='fca_qc_score_text'>" . $quiz_text_strings['you_got'] . " {{SCORE_CORRECT}} " . $quiz_text_strings['out_of'] . " {{SCORE_TOTAL}} </p>";
		$html .= "<h3 class='fca_qc_score_title'></h3>";
		$html .= "<img class='fca_qc_score_img' src=''>";
		$html .= "<p class='fca_qc_score_desc'></p>";
	$html .= "</div>";
	
	if ( $operation == 'echo' ) {
		echo $html;
	} else {
		return $html;
	}
}

function fca_qc_do_result_panel( $operation = 'echo') {
	global $quiz_text_strings;
	$html = "<div class='fca_qc_result_container' style='display:none;'>";
		$html .= "<p class='fca_qc_result_text'>" . $quiz_text_strings['your_answers'] . "</p>";
		//THIS IS WHERE EACH RESPONSE WILL BE INSERTED
		$html .= "<div class='fca_qc_insert_response_above'></div>";
	$html .= "</div>";
	
	if ( $operation == 'echo' ) {
		echo $html;
	} else {
		return $html;
	}
}

function fca_qc_convert_entities ( $array ) {
	$array = is_array($array) ? array_map('fca_qc_convert_entities', $array) : html_entity_decode( $array, ENT_QUOTES );

    return $array;
}

/* Localization */
function fca_qc_load_localization() {
	load_plugin_textdomain( 'quiz-cat', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'fca_qc_load_localization' );