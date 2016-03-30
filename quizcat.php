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

/* BASIC SECURITY */
defined( 'ABSPATH' ) or die( 'Unauthorized Access!' );

add_shortcode( 'quizprep-quiz', 'fca_qc_do_quiz' );

//ADD MENU TO ADMIN PAGE
add_action( 'admin_menu', 'fca_qc_add_admin_menu_page' );
add_action ( 'admin_init', 'fca_qc_register_admin_scripts' );

//ADD AJAX HANDLERS
add_action( 'wp_ajax_fca_qc_submit_form', 'fca_qc_submit_form' );
add_action( 'wp_ajax_nopriv_fca_qc_submit_form', 'fca_qc_submit_form' );

//DEFAULTS WHEN MAKING NEW 
$fca_qc_defaults = array (
	'name' => 'New Test',
	'desc' => 'New Test',
	'questions' => array(),
);



function fca_qc_register_admin_scripts() {
	 wp_register_style( 'fca_qc_admin_stylesheet', plugins_url( 'includes/admin.css', __FILE__ ) );
	 wp_register_script( 'fca_qc_admin_js', plugins_url( 'includes/admin.js', __FILE__ ) );
	 
}

function fca_qc_add_admin_menu_page() {
	$handle = add_menu_page( 'Quiz Cat', 'Quiz Cat', 'manage_options', 'fca_qc_editor_page', 'fca_qc_editor_page' );

}
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
						
							echo '<label class="fca_qc_label" for="fca_qc_question_input">Question: </label>';
							echo "<textarea class='fca_qc_align_top fca_qc_admin_question_input' name='fca_qc_question_input[]'>$question</textarea><br>";
							
							$answer = $questionData['answer'];
							
							echo '<label class="fca_qc_label" for="fca_qc_answer_input">Correct Answer: </label>'; 
							echo "<textarea class='fca_qc_align_top' name='fca_qc_answer_input[]'>$answer</textarea><br>";
							
							$hint = $questionData['hint'];
							
							echo '<label class="fca_qc_label" for="fca_qc_hint_input">Hint: </label>'; 
							echo "<textarea class='fca_qc_align_top' name='fca_qc_hint_input[]'>$hint</textarea><br>";
							
							$wrong = $questionData['wrong1'];
							
							echo '<label class="fca_qc_label" for="fca_qc_wrong_1_input">Wrong Answer 1: </label>';
							echo "<textarea class='fca_qc_align_top' name='fca_qc_wrong_1_input[]'>$wrong</textarea><br>";
							
							$wrong = $questionData['wrong2'];
							
							echo '<label class="fca_qc_label" for="fca_qc_wrong_2_input">Wrong Answer 2: </label>';
							echo "<textarea class='fca_qc_align_top' name='fca_qc_wrong_2_input[]'>$wrong</textarea><br>";
							
							$wrong = $questionData['wrong3'];
							
							echo '<label class="fca_qc_label" for="fca_qc_wrong_3_input">Wrong Answer 3: </label>';
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
	if ($key !== false) { /* FOUND IN EXISITNG DB - REMOVE ITEM */
		unset($forms[$key]);
	
	}

	delete_option ( 'fca_qc_quiz_' . $formID );
	update_option ('fca_qc_quizzes', $forms);

}

function fca_qc_escape_input($data) {
 
	if (!empty($data)) {

		$data = htmlentities ( $data, ENT_QUOTES );
		
		return $data;
		
	} else {
	
		return false;
	}

}

