jQuery( document ).ready(function($) {
	
	console.log(quizData)
	console.log(userData)
	
	const questions = quizData.quiz_questions
	let questionsShuffled = shuffleArray( quizData.quiz_questions )
	let responses = []
	
	const results = quizData.quiz_results
	const settings = quizData.quiz_settings
	const hideAnswers = settings.hide_answers == 'on' ? true : false
	
	const correctString = quizData.correct_string
	const wrongString = quizData.wrong_string
	const questionCount = questions.length
	
	const yourAnswerString = quizData.your_answer_string
	const correctAnswerString = quizData.correct_answer_string
	
	const scoreString = $( '#fca_qc_score_text').html()
	
	let currentQuestion = 0
	let score = 0
	let currentAnswer, currentHint = ''
	
	function preloadImages() {
		let preloaded_images = []
		
		for (i = 0; i < results.length; i++) {
			preloaded_images[i] = new Image()
			preloaded_images[i].src = results[i].img
		}

	}
	preloadImages()
	
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
	
	function showQuestion() {
		
		if (  currentQuestion < questionCount  ) {
			
			$( '#fca_qc_question_count' ).html( ( currentQuestion + 1) + "/" + questionCount)
			
			$( '.fca_qc_answer_div' ).removeClass('quizprep-wrong-answer')
			
			let question = questionsShuffled[currentQuestion].question
			let answer = questionsShuffled[currentQuestion].answer
			currentHint = questionsShuffled[currentQuestion].hint  //'GLOBAL' HINT
			let wrong1 = questionsShuffled[currentQuestion].wrong1
			let wrong2 = questionsShuffled[currentQuestion].wrong2
			let wrong3 = questionsShuffled[currentQuestion].wrong3
			
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
	
	function show_responses() {
		
		let i = 0;
		
		for ( i = 0; i<questionsShuffled.length; i++ ) {
			do_answer_response_div( questionsShuffled[i].question, questionsShuffled[i].answer, responses[i], i + 1 )
		}
		$( '#fca_qc_result_container' ).show()
	}
	
	function do_answer_response_div( question, answer, response, questionNumber ) {
		
		let html = '';
		
		if ( answer == response ) {
			html += "<div class='fca_eoi_question_response_item correct-answer'>";
		} else {
			html += "<div class='fca_eoi_question_response_item wrong-answer'>";
		}
				
		html += "<h3 class='fca_eoi_question_response_question'>" + questionNumber + ". " + question + "</h3>"
		
		html += "<p class='fca_eoi_question_response_response'><span class='fca_qc_bold'>" + yourAnswerString + " </span>" + response + "</p>";
		html += "<p class='fca_eoi_question_response_correct_answer'><span class='fca_qc_bold'>" + correctAnswerString + " </span>" + answer + "</p>";
					
		html += "</div>";
		
		$( '#fca_qc_insert_response_above' ).before(html)
		
		
	}
	
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