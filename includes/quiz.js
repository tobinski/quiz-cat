jQuery( document ).ready(function($) {
	
	let currentQuestion = 0
	let shuffled_data = shuffleArray( quiz_data )
	let questionCount = shuffled_data.length
	const user_id = user_data.user_id
	let currentAnswer, hint

	let correctAnswers = 0
	
	$( '#fca_qc_start_button' ).click(function() {
		
		$( '#fca_qc_quiz_name' ).hide()
		$( '#fca_qc_quiz_description' ).hide()
		$( '#fca_qc_question_count' ).hide()
		$( '#fca_qc_quiz_div' ).show()
		
		$( this ).hide()
		currentAnswer = showQuestion()
	})
	
	$( '#fca_qc_restart_button' ).click(function() {
		correctAnswers = 0
		currentQuestion = 0
		shuffled_data = shuffleArray( quiz_data )
		$( '#fca_qc_quiz_div' ).show()
		$( this ).hide()
		resetScore()
		currentAnswer = showQuestion()
		
	})
	
	$( '#fca_qc_next_question').click(function() {
		currentAnswer = showQuestion()
		$( '#fca_qc_quiz_div' ).removeClass('flip')
		
	})

	$( '#fca_qc_answer_1,#fca_qc_answer_2,#fca_qc_answer_3,#fca_qc_answer_4' ).click(function() {
		$( this ).blur()
		$( '#fca_qc_quiz_div' ).addClass('flip')
		$( '#fca_qc_hint' ).html(hint)
		
		$( '#fca_qc_result_back' ).removeClass('correct-answer')

		if ( $( this ).html() == currentAnswer ) {
			correctAnswers = correctAnswers + 1
			$( '#fca_qc_result_back' ).addClass('correct-answer')
			$( '#fca_qc_result_back' ).html("Correct!")
			
		} else {
		
			$( '#fca_qc_result_back' ).html("Incorrect")
			
		}
		
		updateScore()
		
	})
	
	function showQuestion() {
		
		if (  currentQuestion < shuffled_data.length  ) {
			
			$( '#fca_qc_answer_1,#fca_qc_answer_2,#fca_qc_answer_3,#fca_qc_answer_4' ).removeClass('quizprep-wrong-answer')
			let question = shuffled_data[currentQuestion].question
			let answer = shuffled_data[currentQuestion].answer
			hint = shuffled_data[currentQuestion].hint
			let wrong1 = shuffled_data[currentQuestion].wrong1
			let wrong2 = shuffled_data[currentQuestion].wrong2
			let wrong3 = shuffled_data[currentQuestion].wrong3
			
			let answers = [answer, wrong1, wrong2, wrong3]
			let shuffled_answers = shuffleArray( answers )
			
			$( '#fca_qc_question' ).html(question)
			
			$( '#fca_qc_question_back' ).html(question)
			
			
			$( '#fca_qc_answer_1' ).html(shuffled_answers[0])
			$( '#fca_qc_answer_2' ).html(shuffled_answers[1])
			$( '#fca_qc_answer_3' ).html(shuffled_answers[2])
			$( '#fca_qc_answer_4' ).html(shuffled_answers[3])
			
			currentQuestion = currentQuestion + 1
						
			return answer
		
		} else {
			alert ("the test has ended!")
			endTest()
		}
		
	}
	
	function updateScore() {
		$( '#fca_qc_score_div' ).show()
		let score = correctAnswers / currentQuestion
		score = score * 100
		$( '#fca_qc_score' ).html( correctAnswers + "/" + currentQuestion + " = " + score.toFixed(0) + "%" )
	}
	
	function resetScore() {
		$( '#fca_qc_score_div' ).show()
		$( '#fca_qc_score' ).html( '' )
	}
	
	function endTest() {
	
		$( '#fca_qc_quiz_div' ).hide()
		$( '#fca_qc_restart_button' ).show()
		
	}
	
	/**
	* Randomize array element order in-place.
	* Using Durstenfeld shuffle algorithm.
	*/
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

