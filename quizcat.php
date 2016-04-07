<?php
/*
Plugin Name: Quiz Cat
Plugin URI: https://fatcatapps.com/optincat
Description: Provides an easy way to create and administer quizes
Version: 1.0
Author: Fatcat Apps
Author URI: https://fatcatapps.com/
License: GPLv2
*/

// BASIC SECURITY
defined( 'ABSPATH' ) or die( 'Unauthorized Access!' );

// DEFINE SOME USEFUL CONSTANTS
define( 'FCA_QC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FCA_QC_PLUGINS_URL', plugins_url( '', __FILE__ ) );

//ADD AJAX HANDLERS
add_action( 'wp_ajax_fca_qc_submit_form', 'fca_qc_submit_form' );
add_action( 'wp_ajax_nopriv_fca_qc_submit_form', 'fca_qc_submit_form' );


////////////////////////////
//		SET UP POST TYPE
////////////////////////////

//REGISTER CPT
function fca_qc_register_post_type() {
	
	$labels = array(
		'name' => _x('Quizzes','fca_quiz_cat'),
		'singular_name' => _x('Quiz','fca_quiz_cat'),
		'add_new' => _x('Add New','fca_quiz_cat'),
		'all_items' => _x('All Quzzes','fca_quiz_cat'),
		'add_new_item' => _x('Add New Quiz','fca_quiz_cat'),
		'edit_item' => _x('Edit Quiz','fca_quiz_cat'),
		'new_item' => _x('New Quiz','fca_quiz_cat'),
		'view_item' => _x('View Quiz','fca_quiz_cat'),
		'search_items' => _x('Search Quizzes','fca_quiz_cat'),
		'not_found' =>  _x('Quiz not found','fca_quiz_cat'),
		'not_found_in_trash' => _x('No Quizzes found in trash','fca_quiz_cat'),
		'parent_item_colon' => _x('Parent Quiz:','fca_quiz_cat'),
		'menu_name' => _x('Quiz Cat','fca_quiz_cat')
	);
		
	$args = array(
		'labels' => $labels,
		'description' => "",
		'public' => false,
		'exclude_from_search' => true,
		'publicly_queryable' => false,
		'show_ui' => true,
		'show_in_nav_menus' => false,
		'show_in_menu' => true,
		'show_in_admin_bar' => true,
		'menu_position' => 10,
		'menu_icon' => null,
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
		1  => __( 'Quiz updated.','fca_quiz_cat'),
		2  => __( 'Quiz updated.','fca_quiz_cat'),
		3  => __( 'Quiz deleted.','fca_quiz_cat'),
		4  => __( 'Quiz updated.','fca_quiz_cat'),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Quiz restored to revision from %s','fca_quiz_cat'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Quiz published.' ,'fca_quiz_cat'),
		7  => __( 'Quiz saved.' ,'fca_quiz_cat'),
		8  => __( 'Quiz submitted.' ,'fca_quiz_cat'),
		9  => sprintf(
			__( 'Quiz scheduled for: <strong>%1$s</strong>.','fca_quiz_cat'),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
		),
		10 => __( 'Quiz draft updated.' ,'fca_quiz_cat'),
	);

	return $messages;
}
add_filter('post_updated_messages', 'fca_qc_post_updated_messages' );



//Customize CPT table columns

function fca_qc_add_new_post_table_columns($columns) {
	
    $new_columns['cb'] = '<input type="checkbox" />';
    $new_columns['title'] = _x('Quiz Name', 'column name', 'fca_quiz_cat');    
    $new_columns['shortcode'] = __('Shortcode', 'fca_quiz_cat');
    $new_columns['date'] = _x('Date', 'column name', 'fca_quiz_cat');
 
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


////////////////////////////
//		EDITOR PAGE 
////////////////////////////


//ENQUEUE ANY SCRIPTS OR CSS FOR OUR ADMIN PAGE EDITOR
function fca_qc_admin_cpt_script( $hook ) {
	global $post;  
	if ( $hook == 'post-new.php' || $hook == 'post.php'  &&  $post->post_type === 'fca_qc_quiz' ) {  
		wp_enqueue_media();		
		wp_enqueue_script('fca_qc_admin_js', FCA_QC_PLUGINS_URL . '/includes/admin.js' );		
		wp_enqueue_style( 'fca_qc_admin_stylesheet', FCA_QC_PLUGINS_URL . '/includes/admin.css' );
		
		$admin_data = array (
			//A TEMPLATE DIV OF THE QUESTION AND RESULT DIVS, SO WE CAN ADD MORE OF THEM VIA JAVASCRIPT
			'questionDiv' => 	fca_qc_render_question_meta_box( array(), '{{QUESTION_NUMBER}}', 'return' ),
			'resultDiv' 	=> 	fca_qc_render_result_meta_box( array(), '{{RESULT_NUMBER}}', 'return' ),
			
			//SOME LOCALIZATION STRINGS FOR JAVASCRIPT STUFF
			'navigationWarning' => __( "You have entered new data on this page.  If you navigate away from this page without first saving your data, the changes will be lost.", 'fca_quiz_cat'),
			'sureWarning' => 	 __( 'Are you sure?', 'fca_quiz_cat'),
			'selectImage' => __('Select Image', 'fca_quiz_cat' ),			
		);
		
		wp_localize_script( 'fca_qc_admin_js', 'adminData', $admin_data ); 
    }
}
add_action( 'admin_enqueue_scripts', 'fca_qc_admin_cpt_script', 10, 1 );  

//ADD META BOXES TO EDIT CPT PAGE
function add_custom_meta_boxes() {

	add_meta_box( 
        'fca_qc_description_meta_box',
        __( 'This Quiz', 'fca_quiz_cat' ),
        'fca_qc_render_description_meta_box',
        null,
        'normal',
        'high'
    );	
	
	add_meta_box( 
        'fca_qc_questions_meta_box',
        __( 'Questions', 'fca_quiz_cat' ),
        'fca_qc_render_questions_meta_box',
        null,
        'normal',
        'default'
    );
	

	add_meta_box( 
        'fca_qc_add_result_meta_box',
        __( 'Scoring', 'fca_quiz_cat' ),
        'fca_qc_render_add_result_meta_box',
        null,
        'normal',
        'default'
    );

	add_meta_box( 
        'fca_qc_quiz_settings_meta_box',
        __( 'Settings', 'fca_quiz_cat' ),
        'fca_qc_render_quiz_settings_meta_box',
        null,
        'normal',
        'default'
    );
	
	add_meta_box( 
        'fca_qc_sidebar_meta_box',
        __( 'Extensions', 'fca_quiz_cat' ),
        'fca_qc_render_side_meta_box',
        null,
        'side',
        'high'
    );	
}
add_action( 'add_meta_boxes_fca_qc_quiz', 'add_custom_meta_boxes' );

//RENDER THE DESCRIPTION META BOX
function fca_qc_render_description_meta_box( $post ) {
	
	$quiz_meta = get_post_meta ( $post->ID, 'quiz-cat-meta', true );

	echo '<div class="fca_qc_two_third_div">';
		echo "<label class='fca_qc_admin_label'>" . __('Description', 'fca_quiz_cat') . "</label>";
		echo "<textarea class='fca_qc_texta' id='fca_qc_quiz_description' name='fca_qc_quiz_description'>" . $quiz_meta['desc'] . "</textarea>";	
	echo '</div>';
	
	echo '<div class="fca_qc_one_third_div">';
		echo "<label class='fca_qc_admin_label'>" . __('Image', 'fca_quiz_cat') . "</label>";
		echo "<input type='text' class='fca_qc_image_input' name='fca_qc_quiz_description_image_src' id='fca_qc_quiz_description_image_src' style='display: none;' value='" . $quiz_meta['desc_img_src'] . "'>";
		echo "<span class='pointer dashicons dashicons-welcome-add-page fca_qc_quiz_image_upload_btn'></span>";	
		echo "<span class='pointer dashicons dashicons-no-alt fca_qc_quiz_image_revert_btn'></span>";	
		//ASSIGN PLACEHOLDER IMAGE
		empty ( $quiz_meta['desc_img_src'] ) ? $quiz_meta['desc_img_src'] = FCA_QC_PLUGINS_URL . '/assets/image-placeholder.png' : '';
		echo "<img class='fca_qc_image' id='fca_qc_quiz_description_image' src='" . $quiz_meta['desc_img_src'] . "'>";

	echo '</div>';
	
}

//RENDER THE ADD QUESTION META BOX
function fca_qc_render_questions_meta_box( $post ) {
		
	$questions = get_post_meta ( $post->ID, 'quiz-cat-questions', true );

	if ( count ( $questions ) == 0 ) {
		
		fca_qc_render_question_meta_box( array(), 1, 'echo' );
		
	} else {
		
		$counter = 1;
		
		forEach ( $questions as $question ) {
			
			fca_qc_render_question_meta_box( $question, $counter, 'echo' );
			$counter = $counter + 1;
			
		}		
	}	
	echo "<span id='fca_qc_add_question_btn'  class='dashicons dashicons-plus-alt fca_qc_add_btn pointer'></span>";
	
}

// RENDER A QUESTION META BOXE
// INPUT: ARRAY->$question [QUESTION, ANSWER, HINT, WRONG1, WRONG2, WRONG3], STRING->$operation ('echo' OR 'return')
// OUTPUT: ECHO OR RETURNED HTML 
function fca_qc_render_question_meta_box( $question, $question_number, $operation = 'echo' ) {
	
	if ( empty ( $question ) ) {
		$question = array(
			'question' => '',
			'answer' => '',
			'hint' => '',
			'wrong1' => '',
			'wrong2' => '',
			'wrong3' => '',
		
		);
		
	}
	
	$html = "<div class='fca_qc_question_item fca_qc_deletable_item' id='fca_qc_question_$question_number'>";
		$html .= "<span class='dashicons dashicons-trash fca_qc_delete_icon'></span>";
		$html .= "<h3 class='fca_qc_question_label'>" . __('Question', 'fca_quiz_cat') . ' ' . $question_number . ": <span class='fca_qc_quiz_heading_text'>". $question['question'] . "</span></h3>";
			
			$html .= "<div class='fca_qc_question_input_div'>";
			
				$html .= "<label class='fca_qc_admin_label'>" . __('Question', 'fca_quiz_cat') . "</label><br>";
				$html .= "<textarea class='fca_qc_question_texta fca_qc_question_text' name='fca_qc_quiz_question[]'>" . $question['question']  ."</textarea><br>";

				$html .= "<label class='fca_qc_admin_label'>" . __('Correct Answer', 'fca_quiz_cat') . "</label><br>";
				$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_answer[]'>" . $question['answer']  ."</textarea><br>";

				$html .= "<label class='fca_qc_admin_label'>" . __('Hint', 'fca_quiz_cat') . "</label><br>";
				$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_hint[]'>" . $question['hint']  ."</textarea><br>";

				$html .= "<label class='fca_qc_admin_label'>" . __('Wrong Answer 1', 'fca_quiz_cat') . "</label><br>";
				$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_wrong_1[]'>" . $question['wrong1']  ."</textarea><br>";
				
				$html .= "<label class='fca_qc_admin_label'>" . __('Wrong Answer 2', 'fca_quiz_cat') . "</label><br>";
				$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_wrong_2[]'>" . $question['wrong2']  ."</textarea><br>";
				
				$html .= "<label class='fca_qc_admin_label'>" . __('Wrong Answer 3', 'fca_quiz_cat') . "</label><br>";
				$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_wrong_3[]'>" . $question['wrong3']  ."</textarea><br>";
				
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
			
	$results = get_post_meta ( $post->ID, 'quiz-cat-results', true );
	
	if ( count ( $results ) == 0 ) {
		
		fca_qc_render_result_meta_box( array(), 1, 'echo' );
		
	} else {
		
		$counter = 1;
		
		forEach ( $results as $result ) {
			
			fca_qc_render_result_meta_box( $result, $counter, 'echo' );
			
			$counter = $counter + 1;
			
		}		
	}	

	echo "<span id='fca_qc_add_result_btn' class='dashicons dashicons-plus-alt fca_qc_add_btn pointer'></span>";
}

// RENDER A RESULT META BOX
// INPUT: ARRAY->$result (TITLE, DESC, IMG), INT|STRING->$result_number, STRING->$operation ('echo' OR 'return')
// OUTPUT: ECHO OR RETURNED HTML
function fca_qc_render_result_meta_box( $result, $result_number, $operation = 'echo' ) {
	
	if ( empty ( $result ) ) {
		$result = array(
			'title' => '',
			'desc' => '',
			'img' => FCA_QC_PLUGINS_URL . '/assets/image-placeholder.png',
		
		);
		
	}
	
	$html = "<div class='fca_qc_result_item fca_qc_deletable_item' id='fca_qc_result_$result_number'>";
		$html .= "<span class='dashicons dashicons-trash fca_qc_delete_icon'></span>";
		$html .= "<h3 class='fca_qc_result_label'><span class='fca_qc_result_score_value'></span><span class='fca_qc_result_score_title'>" . $result['title'] . "</span></h3>";
		
		$html .= "<div class='fca_qc_result_input_div'>";
			
			$html .= '<div class="fca_qc_two_third_div">';
				$html .= "<label class='fca_qc_admin_label'>" . __('Result Title', 'fca_quiz_cat') . "</label><br>";
				$html .= "<input type='text' class='fca_qc_text_input fca_qc_quiz_result' name='fca_qc_quiz_result_title[]' value='" . $result['title'] . "'></input><br>";
				$html .= "<label class='fca_qc_admin_label'>" . __('Description (Optional)', 'fca_quiz_cat') . "</label><br>";
				$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_result_description[]'>" . $result['desc'] . "</textarea><br>";
			$html .= '</div>';
			
			$html .= '<div class="fca_qc_one_third_div">';
				$html .= "<label class='fca_qc_admin_label'>" . __('Image', 'fca_quiz_cat') . "</label>";
				$html .= '<input type="text" class="fca_qc_image_input" name="fca_qc_quiz_result_image_src[]" style="display: none;" value="' . $result['img'] . '">';
				$html .= "<span class='pointer dashicons dashicons-welcome-add-page fca_qc_quiz_image_upload_btn'></span>";		
				$html .= "<span class='pointer dashicons dashicons-no-alt fca_qc_quiz_image_revert_btn'></span>";
				$html .= '<img class="fca_qc_image" id="fca_qc_quiz_result_image[]" src="' . $result['img'] . '">';

			$html .= '</div>';
		
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

//RENDER A SIDE META BOX
function fca_qc_render_side_meta_box() {
	
	echo '</p>COMING SOON</p>';
	
}

//RENDER THE QUIZ SETTINGS META BOX 
function fca_qc_render_quiz_settings_meta_box( $post ) {
	
	$settings = get_post_meta ( $post->ID, 'quiz-cat-settings', true );
	
	$hide_answers = empty ( $settings['hide_answers'] ) ? '' : "checked='checked'";
	
	
	$shortcode = '[quiz-cat id="' . $post->ID . '"]';
		
	echo "<div class='fca_qc_onoffswitch'>";
		echo "<input type='checkbox' class='fca_qc_onoffswitch-checkbox' id='fca_qc_hide_answers_until_end' style='display:none;' name='fca_qc_hide_answers_until_end' $hide_answers></input>";		
	echo "<label class='fca_qc_onoffswitch-label' for='fca_qc_hide_answers_until_end'></label>";
	echo "</div>";
	echo "<label class='fca_qc_admin_label fca_qc_admin_label_switch' for='fca_qc_hide_answers_until_end'>" . __('Hide answers until the end of the quiz', 'fca_quiz_cat') . "</label><br>";
	
	echo "<label class='fca_qc_admin_label' for='fca_qc_shortcode_input'>" . __('Shortcode (copy & paste in to the post or page where you want the quiz to appear)', 'fca_quiz_cat') . "</label><br>";
	echo "<input type='text' class='fca_qc_text_input' id='fca_qc_shortcode_input' name='fca_qc_shortcode_input' value='$shortcode' readonly>";		
				
}



//CUSTOM SAVE HOOK
function fca_qc_save_post( $post_id ) {
	
	//SAVING META DATA ( DESCRIPTION, IMAGE )
	$meta_fields = array (
		'fca_qc_quiz_description'	=> 'desc',
		'fca_qc_quiz_description_image_src'	=> 'desc_img_src',
	);
	
	$meta = array();
	
	forEach ( $meta_fields as $key => $value ) {
		empty ( $_POST[$key] ) ?  $_POST[$key] = '' : '';
		$meta[$value] = $_POST[$key];
	}
	
	update_post_meta ( $post_id, 'quiz-cat-meta', $meta );
	
	//SAVING QUESTIONS
	$questions = array();
	
	$n = empty ( $_POST['fca_qc_quiz_question'] ) ? 0 : count ( $_POST['fca_qc_quiz_question'] );
	
	for ($i = 0; $i < $n ; $i++) {
		$questions[$i]['question'] = fca_qc_escape_input( $_POST['fca_qc_quiz_question'][$i] );
		$questions[$i]['answer'] = fca_qc_escape_input( $_POST['fca_qc_quiz_answer'][$i] );
		$questions[$i]['hint'] = fca_qc_escape_input( $_POST['fca_qc_quiz_hint'][$i] );
		$questions[$i]['wrong1'] = fca_qc_escape_input( $_POST['fca_qc_quiz_wrong_1'][$i] );
		$questions[$i]['wrong2'] = fca_qc_escape_input( $_POST['fca_qc_quiz_wrong_2'][$i] );
		$questions[$i]['wrong3'] = fca_qc_escape_input( $_POST['fca_qc_quiz_wrong_3'][$i] );
	} 
	
	update_post_meta ( $post_id, 'quiz-cat-questions', $questions );
	
	$results = array();
	
	
	//SAVING RESULTS
	$n = empty ( $_POST['fca_qc_quiz_result_title'] ) ? 0 : count ( $_POST['fca_qc_quiz_result_title'] );
	
	for ($i = 0; $i < $n ; $i++) {
		$results[$i]['title'] = fca_qc_escape_input( $_POST['fca_qc_quiz_result_title'][$i] );
		$results[$i]['desc'] = fca_qc_escape_input( $_POST['fca_qc_quiz_result_description'][$i] );
		$results[$i]['img'] = fca_qc_escape_input( $_POST['fca_qc_quiz_result_image_src'][$i] );
		$results[$i]['min'] = intval ( fca_qc_escape_input( $_POST['fca_qc_result_min'][$i] ) );
		$results[$i]['max'] = intval ( fca_qc_escape_input( $_POST['fca_qc_result_max'][$i] ) );
		
	} 	
				
	update_post_meta ( $post_id, 'quiz-cat-results', $results );
	
	//SAVING SETTINGS
	$fields = array (
		'fca_qc_hide_answers_until_end'	=> 'hide_answers',
	);
	
	$save = array();
	
	forEach ( $fields as $key => $value ) {
		empty ( $_POST[$key] ) ?  $_POST[$key] = '' : '';
		$save[$value] = fca_qc_escape_input( $_POST[$key] );
	}
		
	update_post_meta ( $post_id, 'quiz-cat-settings', $save );
	
}
add_action( 'save_post_fca_qc_quiz', 'fca_qc_save_post' );

function fca_qc_escape_input($data) {
 
	if (!empty($data)) {

		$data = htmlentities ( $data, ENT_QUOTES );
		
		return $data;
		
	} else {
	
		return false;
	}

}

////////////////////////////
//		DISPLAY QUIZ
////////////////////////////


function fca_qc_do_quiz( $atts ) {
	
	if ( !empty ( $atts[ 'id' ] ) ) {
		$quiz_meta = get_post_meta ( $atts[ 'id' ], 'quiz-cat-meta', true );
		$quiz_meta['title'] = get_the_title ( $atts[ 'id' ] );
		$quiz_questions = get_post_meta ( $atts[ 'id' ], 'quiz-cat-questions', true );
		$quiz_results = get_post_meta ( $atts[ 'id' ], 'quiz-cat-results', true );
		$quiz_settings = get_post_meta ( $atts[ 'id' ], 'quiz-cat-settings', true );
		
		if ( !$quiz_meta || !$quiz_questions ) {
			echo '<p>Quiz Cat: ' . __('No Quiz found', 'fca_quiz_cat') . '</p>';
			return false;
		}
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'fca_qc_quiz_stylesheet', plugins_url( 'includes/quiz.css', __FILE__ ) );
		wp_enqueue_script( 'fca_qc_quiz_js', plugins_url( 'includes/quiz.js', __FILE__ ) );
		
		//SEND JS THE DATA BUT CONVERT ANY ESCAPED THINGS BACK TO NORMAL CHARACTERS
		$quiz_data = array(
			'quiz_meta' => fca_qc_convert_entities($quiz_meta),
			'quiz_questions' => fca_qc_convert_entities($quiz_questions),
			'quiz_results' => fca_qc_convert_entities($quiz_results),
			'quiz_settings' => $quiz_settings,
			'wrong_string' =>  __('Wrong!', 'fca_quiz_cat'),
			'correct_string' => __('Correct!', 'fca_quiz_cat'),
			'your_answer_string' => __('Your answer:', 'fca_quiz_cat'),
			'correct_answer_string' => __('Correct answer:', 'fca_quiz_cat'),
		);	
		
		$user_data = array(
			'user_id' => get_current_user_id(),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);
		
		wp_localize_script( 'fca_qc_quiz_js', 'quizData', $quiz_data );
		wp_localize_script( 'fca_qc_quiz_js', 'userData', $user_data );
		
		ob_start(); ?>
		<div class='fca_qc_quiz' id='<?php echo 'fca_qc_quiz_' . $atts[ 'id' ] ?>'>
			<h2 id='fca_qc_quiz_title'><?php echo $quiz_meta['title'] ?></h2>
			<p id='fca_qc_quiz_description'><?php echo $quiz_meta['desc'] ?></p>
			<img id='fca_qc_quiz_description_img' src='<?php echo $quiz_meta['desc_img_src'] ?>'>
			
			<button type='button' class='fca_qc_button' id='fca_qc_start_button'><?php _e('Start Quiz', 'fca_quiz_cat') ?></button>
			
			<div class='flip-container' id='fca_qc_quiz_div' style='display: none;'>
				<div class='flipper'>
					<?php fca_qc_do_question_panel() ?> 
					<?php fca_qc_do_answer_panel() ?> 
					
				</div>
			</div>
			<?php fca_qc_do_score_panel() ?> 
			<button type='button' class='fca_qc_button' id='fca_qc_restart_button' style='display: none;'><?php _e('Retake Quiz', 'fca_quiz_cat') ?></button>
			<div id='fca_qc_quiz_footer' style='display: none;'>
				<span id='fca_qc_question_count'></span>		
			</div>
			<?php fca_qc_do_result_panel() ?> 
			
		</div>
		<?php
		
		echo ob_get_clean();
	} else {
		echo '<p>Quiz Cat: ' . __('No Quiz found', 'fca_quiz_cat') . '</p>';
	}
}
add_shortcode( 'quiz-cat', 'fca_qc_do_quiz' );

function fca_qc_do_question_panel( $operation = 'echo' ) {
	
	$svg_rectangle = '<svg class="fca_qc_rectancle" width="26" height="26"><rect width="26" height="26" style="fill:#fff;stroke-width:1;stroke:#000"></svg>';
			
	$html = "<div class='front' id='fca_qc_answer_container'>";
		$html .= "<p id='fca_qc_question'>" . __('Question', 'fca_quiz_cat') . "</p>";
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
	$html = "<div class='back' id='fca_qc_back_container'>";
		$html .= "<p id='fca_qc_question_right_or_wrong'></p>";
		$html .= "<span id='fca_qc_question_back'></span></p>";
		$html .= "<p class='fca_qc_back_response'>" . __('Your answer: ', 'fca_quiz_cat') . "<span id='fca_qc_your_answer'></span></p>";
		$html .= "<p id='fca_qc_correct_answer_p' class='fca_qc_back_response'>" . __('Correct answer: ', 'fca_quiz_cat') . "<span id='fca_qc_correct_answer'></span></p>";
		$html .= "<button type='button' id='fca_qc_next_question'>" . __('Next', 'fca_quiz_cat') . "</button>";
	$html .= "</div>";
	
	if ( $operation == 'echo' ) {
		echo $html;
	} else {
		return $html;
	}
}

function fca_qc_do_score_panel( $operation = 'echo') {
	$html = "<div id='fca_qc_score_container' style='display:none;'>";
		$html .= "<p id='fca_qc_score_text'>" . __('You got', 'fca_quiz_cat') . " {{SCORE_CORRECT}} " . __('out of', 'fca_quiz_cat') . " {{SCORE_TOTAL}} " . __('right', 'fca_quiz_cat') . "</p>";
		$html .= "<h3 id='fca_qc_score_title'></h3>";
		$html .= "<img id='fca_qc_score_img' src=''>";
		$html .= "<p id='fca_qc_score_desc'></p>";
	$html .= "</div>";
	
	if ( $operation == 'echo' ) {
		echo $html;
	} else {
		return $html;
	}
}

function fca_qc_do_result_panel( $operation = 'echo') {
	$html = "<div id='fca_qc_result_container' style='display:none;'>";
		$html .= "<p id='fca_qc_result_text'>" . __('Your Answers', 'fca_quiz_cat') . "</p>";
		//THIS IS WHERE EACH RESPONSE WILL BE INSERTED
		$html .= "<div id='fca_qc_insert_response_above'></div>";
	$html .= "</div>";
	
	if ( $operation == 'echo' ) {
		echo $html;
	} else {
		return $html;
	}
}

function fca_qc_convert_entities ( $array ) {
	$array = is_array($array) ? array_map('fca_qc_convert_entities', $array) : wp_kses_decode_entities( $array );

    return $array;
}