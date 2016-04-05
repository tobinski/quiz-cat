jQuery( document ).ready(function($) {
	
	console.log(quizData)
	console.log(userData)

	
	let questions = quizData.quiz_questions
	let results = quizData.quiz_results
	let settings = quizData.quiz_settings

	const questionCount = questions.length
	let currentQuestion = 0
	let score = 0
	let currentAnswer, currentHint = ''
	
	$( '#fca_qc_start_button' ).click(function() {
		$( '#fca_qc_quiz_title' ).hide()
		$( '#fca_qc_quiz_description' ).hide()
		$( '#fca_qc_quiz_description_img' ).hide()
		$( '#fca_qc_quiz_div' ).show()
		$( '#fca_qc_question_count' ).show()
		$( '#fca_qc_question_count' ).html( ( currentQuestion + 1) + "/" + questionCount)
		$( this ).hide()
		showQuestion()
	})
	
	$( '#fca_qc_restart_button' ).click(function() {
		score = 0
		currentQuestion = 0
		//shuffled_data = shuffleArray( quiz_data )
		$( '#fca_qc_quiz_div' ).show()
		$( this ).hide()
		resetScore()
		showQuestion()
		
	})
	
	$( '#fca_qc_next_question').click(function() {
		showQuestion()
		$( '#fca_qc_quiz_div' ).removeClass('flip')
		
	})

	$( '#fca_qc_answer_1,#fca_qc_answer_2,#fca_qc_answer_3,#fca_qc_answer_4' ).click(function() {
		$( this ).blur()
		$( '#fca_qc_quiz_div' ).addClass( 'flip' )
		$( '#fca_qc_hint' ).html( currentHint )
		
		$( '#fca_qc_result_back' ).removeClass( 'correct-answer' )

		if ( $( this ).html() == currentAnswer ) {
			score = score + 1
			$( '#fca_qc_result_back' ).addClass( 'correct-answer' )
			$( '#fca_qc_result_back' ).html( 'Correct!' )
			
		} else {
		
			$( '#fca_qc_result_back' ).html( 'Incorrect' )
			
		}
	
	})
	
	function showQuestion() {
		
		if (  currentQuestion < questionCount  ) {
			
			$( '#fca_qc_question_count' ).html( ( currentQuestion + 1) + "/" + questionCount)
			
			$( '#fca_qc_answer_1,#fca_qc_answer_2,#fca_qc_answer_3,#fca_qc_answer_4' ).removeClass('quizprep-wrong-answer')
			
			let question = questions[currentQuestion].question
			let answer = questions[currentQuestion].answer
			currentHint = questions[currentQuestion].hint  //'GLOBAL' HINT
			let wrong1 = questions[currentQuestion].wrong1
			let wrong2 = questions[currentQuestion].wrong2
			let wrong3 = questions[currentQuestion].wrong3
			
			let answers = [answer, wrong1, wrong2, wrong3]
			let shuffled_answers = shuffleArray( answers )
			
			$( '#fca_qc_question' ).html(question)
			
			$( '#fca_qc_question_back' ).html(question)
			
			
			$( '#fca_qc_answer_1' ).html(shuffled_answers[0])
			$( '#fca_qc_answer_2' ).html(shuffled_answers[1])
			$( '#fca_qc_answer_3' ).html(shuffled_answers[2])
			$( '#fca_qc_answer_4' ).html(shuffled_answers[3])
			
			currentQuestion = currentQuestion + 1
						
			currentAnswer = answer
		
		} else {
			alert ("the test has ended!")
			endTest()
		}
		
	}
	
	function drawScore() {
		$( '#fca_qc_score_div' ).show()
		let newScore = score / currentQuestion
		newScore = newScore * 100
		$( '#fca_qc_score' ).html( score + "/" + currentQuestion + " = " + newScore.toFixed(0) + "%" )
	}
	
	function resetScore() {
		$( '#fca_qc_score_div' ).hide()
		$( '#fca_qc_score' ).html( '' )
	}
	
	function endTest() {
	
		$( '#fca_qc_quiz_div' ).hide()
		$( '#fca_qc_restart_button' ).show()
		drawScore()
		
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

