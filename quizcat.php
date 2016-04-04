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
		echo "<label class='fca_qc_admin_label'>" . __('Image', 'fca_quiz_cat') . "</label><br>";
		echo "<input type='text' class='fca_qc_image_input' name='fca_qc_quiz_description_image_src' id='fca_qc_quiz_description_image_src' style='display: none;' value='" . $quiz_meta['desc_img_src'] . "'>";
		
		//ASSIGN PLACEHOLDER IMAGE
		empty ( $quiz_meta['desc_img_src'] ) ? $quiz_meta['desc_img_src'] = FCA_QC_PLUGINS_URL . '/assets/image-placeholder.png' : '';
		echo "<img class='fca_qc_image' id='fca_qc_quiz_description_image' src='" . $quiz_meta['desc_img_src'] . "'>";
		echo "<span class='pointer dashicons dashicons-welcome-add-page fca_qc_quiz_image_upload_btn'></span>";	
		echo "<span class='pointer dashicons dashicons-no-alt fca_qc_quiz_image_revert_btn'></span>";	
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

//RENDER A RESULT META BOXE
// INPUT: ARRAY->$results (TITLE, DESC, IMG), INT|STRING->$result_number, STRING->$operation ('echo' OR 'return')
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
		$html .= "<h3 class='fca_qc_result_label'>" . __('Result', 'fca_quiz_cat') . ' ' . $result_number . "</h3>";
		
		$html .= "<div class='fca_qc_result_input_div'>";
			
			$html .= '<div class="fca_qc_two_third_div">';
				$html .= "<label class='fca_qc_admin_label'>" . __('Result Title', 'fca_quiz_cat') . "</label><br>";
				$html .= "<input type='text' class='fca_qc_text_input' name='fca_qc_quiz_result[]' value='" . $result['title'] . "'></input><br>";
				$html .= "<label class='fca_qc_admin_label'>" . __('Description (Optional)', 'fca_quiz_cat') . "</label><br>";
				$html .= "<textarea class='fca_qc_question_texta' name='fca_qc_quiz_result_description[]'>" . $result['desc'] . "</textarea><br>";
			$html .= '</div>';
			
			$html .= '<div class="fca_qc_one_third_div">';
				$html .= "<label class='fca_qc_admin_label'>" . __('Image', 'fca_quiz_cat') . "</label><br>";
				$html .= '<input type="text" class="fca_qc_image_input" name="fca_qc_quiz_result_image_src[]" style="display: none;" value="' . $result['img'] . '">';
				$html .= '<img class="fca_qc_image" id="fca_qc_quiz_result_image[]" src="' . $result['img'] . '">';
				$html .= "<span class='pointer dashicons dashicons-welcome-add-page fca_qc_quiz_image_upload_btn'></span>";		
				$html .= "<span class='pointer dashicons dashicons-no-alt fca_qc_quiz_image_revert_btn'></span>";
			$html .= '</div>';
		
		$html .= '</div>';
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
	
	$shortcode = '[quiz-cat id="' . $post->ID . '"]';
	
	echo "<input type='checkbox' class='fca_qc_checkbox' id='fca_qc_hide_answers_until_end' name='fca_qc_hide_answers_until_end'></input>";	
	echo "<label class='fca_qc_admin_label' for='fca_qc_hide_answers_until_end'>" . __('Hide answers until the end of the quiz', 'fca_quiz_cat') . "</label><br><br>";
		
	echo "<label class='fca_qc_admin_label' for='fca_qc_shortcode_input'>" . __('Shortcode (copy & paste in to the post or page where you want the quiz to appear)', 'fca_quiz_cat') . "</label><br>";
	echo "<input type='text' class='fca_qc_text_input' id='fca_qc_shortcode_input' name='fca_qc_shortcode_input' value='$shortcode' readonly><br><br>";		
				
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
		$questions[$i]['question'] = fca_qc_escape_input($_POST['fca_qc_quiz_question'][$i]);
		$questions[$i]['answer'] = fca_qc_escape_input($_POST['fca_qc_quiz_answer'][$i]);
		$questions[$i]['hint'] = fca_qc_escape_input($_POST['fca_qc_quiz_hint'][$i]);
		$questions[$i]['wrong1'] = fca_qc_escape_input($_POST['fca_qc_quiz_wrong_1'][$i]);
		$questions[$i]['wrong2'] = fca_qc_escape_input($_POST['fca_qc_quiz_wrong_2'][$i]);
		$questions[$i]['wrong3'] = fca_qc_escape_input($_POST['fca_qc_quiz_wrong_3'][$i]);
	} 
	
	update_post_meta ( $post_id, 'quiz-cat-questions', $questions );
	
	$results = array();
	
	$n = empty ( $_POST['fca_qc_quiz_result'] ) ? 0 : count ( $_POST['fca_qc_quiz_result'] );
	
	for ($i = 0; $i < $n ; $i++) {
		$results[$i]['title'] = fca_qc_escape_input($_POST['fca_qc_quiz_result'][$i]);
		$results[$i]['desc'] = fca_qc_escape_input($_POST['fca_qc_quiz_result_description'][$i]);
		$results[$i]['img'] = fca_qc_escape_input($_POST['fca_qc_quiz_result_image_src'][$i]);
	} 
	
	update_post_meta ( $post_id, 'quiz-cat-results', $results );
	
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
	
	$quizID = $atts[ 'id' ];
	$quizData = get_option ('fca_qc_quiz_' . $quizID);
	
	wp_enqueue_script( 'jquery' );
	wp_enqueue_style( 'fca_qc_quiz_stylesheet', plugins_url( 'includes/quiz.css', __FILE__ ) );
	wp_enqueue_script( 'fca_qc_quiz_js', plugins_url( 'includes/quiz.js', __FILE__ ) );
	
	
	$quizName = $quizData[ 'name' ];
	$description = $quizData[ 'desc' ];
	$questions = $quizData[ 'questions' ];
	$questionCount = count ( $questions );
	
	$user_data = array(
		'user_id' => get_current_user_id(),
	);
	
	wp_localize_script( 'fca_qc_quiz_js', 'quiz_data', $questions );
	wp_localize_script( 'fca_qc_quiz_js', 'user_data', $user_data );
	
	ob_start(); ?>
	
	<h2 id='fca_qc_quiz_name'><?php echo $quizName ?></h2>
	<p id='fca_qc_quiz_description'><?php echo $description ?></p>
	<p id='fca_qc_question_count'>This quiz is <?php echo $questionCount ?> questions.</p>
	
	<button type='button' id='fca_qc_start_button'>Start Quiz!</button>
	<button type='button' id='fca_qc_restart_button' style='display: none;'>Retake Quiz!</button>
	<div class='flip-container' id='fca_qc_quiz_div' style='display: none;'>
		<div class='flipper'>
			<div class='front' id='fca_qc_answer_container'>
				<p id='fca_qc_question'>Question</p>
				<button type='button' class='fca_qc_answer' id='fca_qc_answer_1'></button>
				<button type='button' class='fca_qc_answer' id='fca_qc_answer_2'></button>
				<button type='button' class='fca_qc_answer' id='fca_qc_answer_3'></button>
				<button type='button' class='fca_qc_answer' id='fca_qc_answer_4'></button>
			</div>
			<div class='back' id='fca_qc_back_container'>
				<p id='fca_qc_question_back'>Question</p>
				<p id='fca_qc_result_back'></p>
				<p id='fca_qc_hint'>Hint</p>
				<button type='button' id='fca_qc_next_question'>Next Question</button><br>
			</div>
		</div>
	</div>
	
	<div id='fca_qc_score_div' style='display: none;'>
		<p id='fca_qc_score_info'>Your Score:</p>
		<p id='fca_qc_score'></p>
		
	</div>
	
	<?php
	
	echo ob_get_clean();
	
}
add_shortcode( 'quiz-cat', 'fca_qc_do_quiz' );


//////////////////////
//	DEPRECATED STUFF
//////////////////////

/*
//ADMIN PAGE
function fca_qc_editor_page() {


	wp_enqueue_script('jquery');
	wp_enqueue_style( 'fca_qc_admin_stylesheet' );
	wp_enqueue_script( 'fca_qc_admin_js' );
	
	$uri = $_SERVER[ 'REQUEST_URI' ];
	
	$quizzes = get_option( 'fca_qc_quizzes' );
	$activeQuiz = get_option( 'fca_qc_active_quiz' );
	
	//CHECK FOR DELETE / SWITCH COMMAND
	if ( isSet( $_GET[ 'fca_qc_delete_form' ] ) ) {
		//DELETE SELECTED FORM 
		$uri = str_replace( '&fca_qc_delete_form=' . $_GET[ 'fca_qc_delete_form' ], '', $uri );		
		fca_qc_delete_form( $_GET[ 'fca_qc_delete_form' ] );
		$forms = get_option( 'fca_qc_quizzes' );
	
		if ( count( $forms ) > 0 && !empty( $forms ) ) {
			//GET THE NEXT REMAINING QUIZ IN THE LIST
			$keys = array_keys($forms);
			$activeQuiz = $forms[ $keys[0] ];
			update_option( 'fca_qc_active_quiz', $activeQuiz );
		} else {
			$activeQuiz = '';
		}
	}
			
	if (isSet($_GET[ 'fca_qc_add_form' ])){
		//ADD NEW FORM
		$uri = str_replace('&fca_qc_add_form=TRUE', '', $uri);
		$forms = get_option( 'fca_qc_quizzes' );
		$lastForm = end($forms);
		$lastForm = get_option ('fca_qc_quiz_' . $lastForm);
		$nextFormID = $lastForm['id'] + 1;
		reset($forms);
		$activeQuiz = $nextFormID;
				
		global $fca_qc_defaults;
		$quizData = $fca_qc_defaults;
		$quizData['id'] = $activeQuiz;
		
		$forms[] = $activeQuiz;
		
		update_option( 'fca_qc_active_quiz', $activeQuiz );
		update_option( 'fca_qc_quizzes', $forms );
		update_option( 'fca_qc_quiz_' . $activeQuiz, $quizData );

	} else if (isSet($_GET[ 'fca_qc_select_form' ])){
		//SWITCH TO SELECTED FORM
		$activeQuiz = $_GET[ 'fca_qc_select_form' ];
		update_option( 'fca_qc_active_quiz', $activeQuiz );
		$uri = str_replace('&fca_qc_select_form=' . $_GET[ 'fca_qc_select_form' ], '', $uri);	
		$quizData = get_option ('fca_qc_quiz_' . $activeQuiz);
		
	} else if ( !empty( $activeQuiz ) ) {
		//HAS A CURRENTLY ACTIVE QUIZ
		$quizData = get_option ( 'fca_qc_quiz_' . $activeQuiz );
		
	} else if ( count( $quizzes ) > 0 && !empty( $quizzes ) ) {
		//HAS NO ACTIVE QUIZ, BUT SAVED ONES (JUST DELETED ONE)
		$keys = array_keys($quizzes);
		$activeQuiz = $quizzes[ $keys[0] ];
		$quizData = get_option ( 'fca_qc_quiz_' . $activeQuiz );
		
	} else {
		//HAS NO SAVED OR ACTIVE QUIZES, MAKE A DEFAULT NEW ONE
		$activeQuiz = 1;
		global $fca_qc_defaults;
		$quizData = $fca_qc_defaults;
		$quizData[ 'id' ] = 1;
		update_option( 'fca_qc_active_quiz', $activeQuiz );
		
	}
	
	//TRYING TO SAVE
	if ( !empty( $_POST ) ) {
			
		$verifyNonce = wp_verify_nonce( $_POST[ 'fca_qc_nonce' ], 'fca_qc_do_admin_save' );
		
		if ($verifyNonce != 1) {
			echo "Sorry, there was an error authenticating your login.  Please try again.";
			exit;
		} 
		
		//DO SAVING HERE
		$success = fca_qc_save_admin();
		if ( $success ) {
			echo "<div class='fca_qc_notice'>Test Updated</div>";
			$quizData = get_option ( 'fca_qc_quiz_' . $activeQuiz );
		}else {
			echo "<div class='fca_qc_notice'>Test unchanged</div>";
		}		

	}
		
	if ( !empty ( $quizData[ 'name' ] ) ) {
		$name = $quizData[ 'name' ];
		echo "<div class='fca_qc_notice'>$name selected</div>";
	};
	
	ob_start(); ?>
	
	<div class="wrap" id="fca_qc_admin_page" style="display: none;">
	<form action="<?php echo $uri ?>" method="post">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"><br><br>
		<fieldset>
			
			<?php
			
				$forms = get_option ('fca_qc_quizzes'); 
			
				if (!is_array($forms)) {
					$forms = array();
				}

			?>
					
			<div id="fca_qc_controls" class="fca_qc_admin_panel">
				
				<label class="fca_qc_label" for="fca_qc_name_input">Test Name: </label>
				<input type="text" class="fca_qc_align_top" name="fca_qc_name_input" id="fca_qc_name_input" value="<?php echo $quizData[ 'name' ] ?>"><br>
				
				<label class="fca_qc_label" for="fca_qc_description_input">Description: </label> 
				<textarea class="fca_qc_align_top" id="fca_qc_form_description_input" name="fca_qc_description_input"><?php echo $quizData[ 'desc' ]?></textarea><br>
				
				<label class="fca_qc_label" for="fca_qc_name_input">Question Count: </label>
				<input type="text" name="fca_qc_question_count" id="fca_qc_question_count" value="<?php $count = count ($quizData[ 'questions' ]); $count == 0 ? $count = 1 : ''; echo "$count" ?>" readonly>
				
				<label class="fca_qc_label" for="fca_qc_shortcode">Shortcode: </label>
				<input type="text" name="fca_qc_shortcode" id="fca_qc_shortcode" value="[quizprep-quiz id='<?php $ID = $quizData[ 'id' ]; echo "$ID" ?>']" readonly><br>
				
				<input type="hidden" class="fca_qc_align_top" name="fca_qc_current_form" id="fca_qc_current_form" value="<?php echo $activeQuiz ?>">
				
				<label class="fca_qc_label" for="fca_qc_select_form">Saved Tests: <?php echo count ($forms) ?></label>
				<select name="fca_qc_select_form" id="fca_qc_select_form">
					<option value="" disabled selected style="display:none;"></option>
					<?php
					
					foreach ($forms as $form) {
						$data = get_option ('fca_qc_quiz_' . $form);
						$name = $data['name'];
						echo "<option value='$form'>$name</option>";
					} 
					?>
				</select>
				
				<a href="<?php echo $uri .  "&fca_qc_add_form=TRUE" ?>" title="ADD" id="fca_qc_add_form">
					<input type="button" name="fca_qc_add_form_btn" id="fca_qc_add_form_btn" class="button button-primary" value="NEW">
				</a>				
			</div>

			<div id="fca_qc_question_inputs">
				
					<?php 
					
					$blank_question_defaults = 
					array (
						array (
							'question' => 'What color is the sky?',
							'answer' => 'Blue',
							'hint' => 'Not green',
							'wrong1' => 'Red',
							'wrong2' => 'Purple',
							'wrong3' => 'Yellow',
						)
					);
						
					if ( empty ( $quizData[ 'questions' ] ) ) {
						$quizData[ 'questions' ] = $blank_question_defaults;
					}
					
					$questions = $quizData['questions'];
					$n = 1;
					forEach ( $questions as $questionData ) {
					
						echo '<div class="fca_qc_admin_panel">';
						
						$question = $questionData['question'];
						
						echo "<span class='dashicons dashicons-trash fca_qc_delete_icon'></span>";
						echo "<h3 class='fca_qc_toggle_h3'>Question $n: <span class='fca_qc_admin_question_label'>$question</span></h3>";
												
						echo "<div class='fca_qc_question_info' style='display: none;'>";
						
							echo '<label class="fca_qc_label" for="fca_qc_question_input">Question</label>';
							echo "<textarea class='fca_qc_align_top fca_qc_admin_question_input' name='fca_qc_question_input[]'>$question</textarea><br>";
							
							$answer = $questionData['answer'];
							
							echo '<label class="fca_qc_label" for="fca_qc_answer_input">Correct Answer</label>'; 
							echo "<textarea class='fca_qc_align_top' name='fca_qc_answer_input[]'>$answer</textarea><br>";
							
							$hint = $questionData['hint'];
							
							echo '<label class="fca_qc_label" for="fca_qc_hint_input">Hint</label>'; 
							echo "<textarea class='fca_qc_align_top' name='fca_qc_hint_input[]'>$hint</textarea><br>";
							
							$wrong = $questionData['wrong1'];
							
							echo '<label class="fca_qc_label" for="fca_qc_wrong_1_input">Wrong Answer 1</label>';
							echo "<textarea class='fca_qc_align_top' name='fca_qc_wrong_1_input[]'>$wrong</textarea><br>";
							
							$wrong = $questionData['wrong2'];
							
							echo '<label class="fca_qc_label" for="fca_qc_wrong_2_input">Wrong Answer 2</label>';
							echo "<textarea class='fca_qc_align_top' name='fca_qc_wrong_2_input[]'>$wrong</textarea><br>";
							
							$wrong = $questionData['wrong3'];
							
							echo '<label class="fca_qc_label" for="fca_qc_wrong_3_input">Wrong Answer 3</label>';
							echo "<textarea class='fca_qc_align_top' name='fca_qc_wrong_3_input[]'>$wrong</textarea><br>";
							
							echo "</div>";
						echo "</div>";
						$n = $n + 1;
					};
					?>
				
			</div>	
			
			<div id="fca_qc_add_question_div" class="fca_qc_admin_panel">			
				<input type="button" name="fca_qc_add_question_btn" id="fca_qc_add_question_btn" class="button button-primary" value="ADD QUESTION"><br>
			</div>	
			

			<?php wp_nonce_field( 'fca_qc_do_admin_save', 'fca_qc_nonce' ); ?>
			
			<hr>
						
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
			<a href="<?php echo $uri .  "&fca_qc_delete_form=$activeQuiz" ?>" title="DELETE" id="fca_qc_delete_form">
				<input type="button" name="fca_qc_delete_form_btn" id="fca_qc_delete_form_btn" class="button button-secondary" value="Delete">
			</a>

	
		</fieldset>
		
	</form>
	</div>
	<?php 
	
	echo ob_get_clean();

}


function fca_qc_save_admin() {

	//STORE TO DB - forms list and individual saved one
	$forms = get_option( 'fca_qc_quizzes' );
	
	if (empty($forms)){
		$forms = array();
	}
	$formID = $_POST['fca_qc_current_form'];
	
	if (!in_array($formID, $forms)) {
		$forms[] = $formID;
		$success = update_option( 'fca_qc_quizzes', $forms );
	} else {
		//DO NOTHING, ALREADY SAVED
	}
	
	$quizData = array(
		'id' => $_POST['fca_qc_current_form'],
		'name' => empty($_POST['fca_qc_name_input']) ? '' : fca_qc_escape_input($_POST['fca_qc_name_input']),
		'desc' => empty($_POST['fca_qc_description_input']) ? '' : fca_qc_escape_input($_POST['fca_qc_description_input']),
		'questions' => array(),
	);
	
	$n = count ( $_POST['fca_qc_question_input'] );
	
	for ($i = 0; $i < $n ; $i++) {
		$quizData['questions'][$i]['question'] = fca_qc_escape_input($_POST['fca_qc_question_input'][$i]);
		$quizData['questions'][$i]['answer'] = fca_qc_escape_input($_POST['fca_qc_answer_input'][$i]);
		$quizData['questions'][$i]['hint'] = fca_qc_escape_input($_POST['fca_qc_hint_input'][$i]);
		$quizData['questions'][$i]['wrong1'] = fca_qc_escape_input($_POST['fca_qc_wrong_1_input'][$i]);
		$quizData['questions'][$i]['wrong2'] = fca_qc_escape_input($_POST['fca_qc_wrong_2_input'][$i]);
		$quizData['questions'][$i]['wrong3'] = fca_qc_escape_input($_POST['fca_qc_wrong_3_input'][$i]);
	} 
	

	$success = update_option( 'fca_qc_quiz_' . $formID, $quizData );
		
	if ($success) {
		return true;
	}else {
		return false;
	}
	
}

function fca_qc_delete_form($formID) {

	$forms = get_option( 'fca_qc_quizzes' );
	
	if (!is_array ($forms)) {
		$forms = array();
	}
	
	$key = array_search($formID, $forms);
	if ($key !== false) { // FOUND IN EXISITNG DB - REMOVE ITEM 
		unset($forms[$key]);
	
	}

	delete_option ( 'fca_qc_quiz_' . $formID );
	update_option ('fca_qc_quizzes', $forms);

}



*/
