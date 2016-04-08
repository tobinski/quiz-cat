jQuery( document ).ready(function($) {
	
	
	////////////////
	//	VARIABLES FROM PHP
	////////////////	
	
	const questions = quizData.quiz_questions
	const results = quizData.quiz_results
	const settings = quizData.quiz_settings
	const hideAnswers = settings.hide_answers == 'on' ? true : false
	const yourAnswerString = quizData.your_answer_string
	const correctAnswerString = quizData.correct_answer_string
	const correctString = quizData.correct_string
	const wrongString = quizData.wrong_string
	
	const questionCount = questions.length
	const scoreString = $( '#fca_qc_score_text').html()
	
	let responses = []  //PUSH EACH USERS RESPONSE INTO THIS ARRAY FOR SHOWING AT THE END
	let currentQuestion = 0  //CURRENT ACTIVE QUESTION (INTEGER)
	let score = 0  //SCORE INTEGER (INTEGER)
	let currentAnswer = '' // CORRECT ANSWER FOR THIS QUESTION,  (STRING) USED TO SEE IF INPUT MATCHES IT TO COUNT AS CORRECT
	let currentHint = ''  //(UNUSED) HINT
	
	////////////////
	//	PRE LOAD RESULT IMAGES 
	////////////////	
		
	function preloadImages() {
		let preloaded_images = []
		
		for (i = 0; i < results.length; i++) {
			preloaded_images[i] = new Image()
			preloaded_images[i].src = results[i].img
		}

	}
	preloadImages()
	
	
	////////////////
	//	EVENT HANDLERS 
	////////////////	
	
	$( '#fca_qc_start_button' ).click(function() {
		
		$( '#fca_qc_quiz_title' ).hide()
		$( '#fca_qc_quiz_description' ).hide()
		$( '#fca_qc_quiz_description_img' ).hide()
		$( this ).hide()
		
		$( '#fca_qc_quiz_div' ).show()
		$( '#fca_qc_quiz_footer' ).show()
		$( '#fca_qc_question_count' ).html( ( currentQuestion + 1) + "/" + questionCount)
		
		showQuestion()
		
	})
	
	//UNUSED 
	$( '#fca_qc_restart_button' ).click(function() {
		score = 0
		currentQuestion = 0
		//shuffled_data = shuffleArray( quiz_data )
		$( '#fca_qc_quiz_div' ).show()
		$( '#fca_qc_score_container' ).hide()
		$( this ).hide()
		resetScore()
		showQuestion()
		
	})
	
	$( '#fca_qc_next_question').click(function() {
		showQuestion()
		$( '#fca_qc_quiz_div' ).removeClass('flip')
		
	})

	$( '.fca_qc_answer_div' ).click(function() {
		
		$( this ).blur()
		
		responses.push ( $( this ).children('.fca_qc_answer_span').html() )
		
		if ( hideAnswers ) {
			if ( $( this ).children('.fca_qc_answer_span').html() == currentAnswer ) {
				
				score = score + 1
				showQuestion()
				
			} else {

				showQuestion()
			}
			
		} else {
			$( '#fca_qc_quiz_div' ).addClass( 'flip' )
			$( '#fca_qc_back_container' ).removeClass( 'correct-answer' )
			$( '#fca_qc_back_container' ).removeClass( 'wrong-answer' )
			$( '#fca_qc_your_answer' ).html( $( this ).children('.fca_qc_answer_span').html() )
			$( '#fca_qc_correct_answer' ).html( currentAnswer )
			$( '#fca_qc_quiz_div' ).addClass( 'flip' )
			
			if ( $( this ).children('.fca_qc_answer_span').html() == currentAnswer ) {
				
				score = score + 1
				
				$( '#fca_qc_back_container' ).addClass( 'correct-answer' )
				$( '#fca_qc_question_right_or_wrong' ).html( correctString )
				$( '#fca_qc_correct_answer_p' ).hide()
				
				
			} else {

				$( '#fca_qc_back_container' ).addClass( 'wrong-answer' )
				$( '#fca_qc_question_right_or_wrong' ).html( wrongString )
				$( '#fca_qc_correct_answer_p' ).show()
				
			}
		}
	
	})
	
	
	////////////////
	//	HELPER FUNCTIONS 
	////////////////	
	
	
	function showQuestion() {
		
		if (  currentQuestion < questionCount  ) {
			
			$( '#fca_qc_question_count' ).html( ( currentQuestion + 1) + "/" + questionCount)
			
			$( '.fca_qc_answer_div' ).removeClass('quizprep-wrong-answer')
			
			let question = questions[currentQuestion].question
			let answer = questions[currentQuestion].answer
			//currentHint = questions[currentQuestion].hint  //'GLOBAL' HINT - unused
			let wrong1 = questions[currentQuestion].wrong1
			let wrong2 = questions[currentQuestion].wrong2
			let wrong3 = questions[currentQuestion].wrong3
			
			let answers = [answer, wrong1, wrong2, wrong3]
			let shuffled_answers = shuffleArray( answers )
			
			$( '#fca_qc_question' ).html(question)
			$( '#fca_qc_question_back' ).html(question)
						
			$( '.fca_qc_answer_span' ).eq(0).html(shuffled_answers[0])
			$( '.fca_qc_answer_span' ).eq(1).html(shuffled_answers[1])
			$( '.fca_qc_answer_span' ).eq(2).html(shuffled_answers[2])
			$( '.fca_qc_answer_span' ).eq(3).html(shuffled_answers[3])
			
			currentQuestion = currentQuestion + 1
						
			currentAnswer = answer
		
		} else {
			endTest()
		}
		
	}
	
	function set_result() {

		let yourResult = "undefined"
		let i = 0
		
		while ( yourResult == "undefined" ) {
			if ( results[i].min <= score && results[i].max >= score) {
				yourResult = results[i]
			} else if( i == results.length ) {
				yourResult = 'error'
			}else {
				i++
			}
		}

		let scoreParagraph = scoreString.replace('{{SCORE_CORRECT}}', score)
		scoreParagraph = scoreParagraph.replace('{{SCORE_TOTAL}}', questionCount)
		  
		$( '#fca_qc_score_text').html( scoreParagraph )
		$( '#fca_qc_score_title').html( yourResult.title )
		$( '#fca_qc_score_img').attr( 'src', yourResult.img )
		$( '#fca_qc_score_desc').html( yourResult.desc )
			
	}
	
	//DRAW THE 'YOUR RESPOSNES' BOXES AT THE END OF THE QUIZ
	function show_responses() {
		
		let i = 0
		
		for ( i = 0; i<questions.length; i++ ) {
			do_answer_response_div( questions[i].question, questions[i].answer, responses[i], i + 1 )
		}
		$( '#fca_qc_result_container' ).show()
	}
	
	function do_answer_response_div( question, answer, response, questionNumber ) {
		
		let html = ''
		
		if ( answer == response ) {
			html += "<div class='fca_eoi_question_response_item correct-answer'>"
		} else {
			html += "<div class='fca_eoi_question_response_item wrong-answer'>"
		}
				
		html += "<h3 class='fca_eoi_question_response_question'>" + questionNumber + ". " + question + "</h3>"
		
		html += "<p class='fca_eoi_question_response_response'><span class='fca_qc_bold'>" + yourAnswerString + " </span>" + response + "</p>"
		html += "<p class='fca_eoi_question_response_correct_answer'><span class='fca_qc_bold'>" + correctAnswerString + " </span>" + answer + "</p>"
					
		html += "</div>"
		
		$( '#fca_qc_insert_response_above' ).before(html)
		
		
	}
	
	//UNUSED
	function resetScore() {
		//$( '#fca_qc_score_div' ).hide()
		//$( '#fca_qc_score' ).html( '' )
	}
	
	function endTest() {
	
		$( '#fca_qc_quiz_footer' ).hide()
		$( '#fca_qc_quiz_div' ).hide()
		//$( '#fca_qc_restart_button' ).show()
		$( '#fca_qc_score_container' ).show()
		
		set_result()
		
		if ( hideAnswers ) {
			show_responses()
		}
		
	}
	
	////////////////
	//	UTILITY FUNCTIONS 
	////////////////	

	function shuffleArray(array) {
		for (let i = array.length - 1; i > 0; i--) {
			let j = Math.floor(Math.random() * (i + 1))
			let temp = array[i]
			array[i] = array[j]
			array[j] = temp
		}
		return array
	}
	
})