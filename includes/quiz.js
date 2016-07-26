/* jshint asi: true */
jQuery( document ).ready(function($) {
	
	////////////////
	//   BROWSER DETECTION
	////////////////
	
	function detectIE() {
		var ua = window.navigator.userAgent;

		var msie = ua.indexOf('MSIE ')
		if (msie > 0) {
			// IE 10 or older
			return true
		}

		var trident = ua.indexOf('Trident/')
		if (trident > 0) {
			// IE 11
			return true
		}

		// other browser
		return false
	}
	var usingIE = detectIE()
	
	function ieFix( selector ) {
		$( selector ).find( '#fca_qc_back_container' ).css( "backface-visibility", "visible" )
		$( selector ).find( '#fca_qc_back_container' ).css( "transform", "none" )
		$( selector ).find( '#fca_qc_back_container' ).hide()
	}
	
	////////////////
	//	LOAD VARIABLES FROM PHP
	////////////////
		
	var scoreString = $( '.fca_qc_score_text').first().html()
		
	//LOAD ALL QUIZZES INTO AN ARRAY AS KEY->VALUE PAIR WHERE KEY IS THE POST-ID OF THE QUIZ AND VALUE IS THE QUIZ OBJECT
	var quizzes = {}
	
	function loadQuizzes() {
		$('.fca_qc_quiz').each(function( index ) {
			var thisId = get_quiz_id(this)
			quizzes[thisId] = eval( 'quizData_' + thisId )
		})		

		//TRIM ANY QUESTIONS THAT HAVE NO ANSWERS
		$.each(quizzes, function (key, value) {
			for (var i = 0; i < quizzes[key].questions.length; i++) {
				if ( quizzes[key].questions[i].answer == '' ) {
					quizzes[key].questions.splice(i)
					
				}
			}
		})
	}
	loadQuizzes()
	
	////////////////
	//	PRE LOAD RESULT IMAGES 
	////////////////	
		
	function preloadImages() {
		var preloaded_images = []
		var k = 0
		$.each(quizzes, function (key, value) {
			for (var j = 0; j < quizzes[key].quiz_results.length; j++) {
				preloaded_images[j] = new Image()
				preloaded_images[j].src = quizzes[key].quiz_results[j].img
			}
			
			//LOAD FIRST IMAGE
			preloaded_images[k] = new Image()
			preloaded_images[k].src = quizzes[key].questions[0].img
			k++
		})

	}
	preloadImages()
	
	function lazyLoadImage( src ) {
		if ( src != '' && src != undefined && typeof src == 'string' ) {
			var img = new Image()
			img.src = src
		}
	}
	
	////////////////
	//	EVENT HANDLERS 
	////////////////	
	
	$( '.fca_qc_start_button' ).click(function() {
		
		var thisQuiz =  quizzes[ get_quiz_id( this.parentNode ) ]
		
		thisQuiz.selector = this.parentNode
		usingIE ? ieFix( thisQuiz.selector ) : ''
		
		thisQuiz.currentQuestion = 0
		thisQuiz.score = 0
		thisQuiz.responses = []
		thisQuiz.questionCount = thisQuiz.questions.length
		thisQuiz.hideAnswers = thisQuiz.quiz_settings.hide_answers == 'on' ? true : false
		
		$( this ).siblings( '.fca_qc_quiz_title' ).hide()
		$( this ).siblings( '.fca_qc_quiz_description' ).hide()
		$( this ).siblings( '.fca_qc_quiz_description_img' ).hide()
		$( this ).hide()
		
		$( this ).siblings( '.fca_qc_quiz_div' ).show()
		$( this ).siblings( '.fca_qc_quiz_footer' ).show()
		$( this ).siblings( '.flip-container' ).show()
		$( this ).siblings( '.fca_qc_question_count' ).html( 1 + "/" + thisQuiz.questionCount )
		
		showQuestion( thisQuiz )
		
		var screenPosition = $(thisQuiz.selector).offset().top + -120
		screenPosition < 0 ? screenPosition = 0 : ''
		
		$('html, body').animate( { scrollTop: screenPosition }, 300);
			
		
	})
	
	$( '.fca_qc_next_question').click(function() {
		var thisQuiz =  quizzes[ get_quiz_id( $(this).closest('.fca_qc_quiz') ) ]
		if ( usingIE ) {
			$( thisQuiz.selector ).find( '#fca_qc_answer_container' ).show()
			$( thisQuiz.selector ).find( '#fca_qc_back_container' ).hide()
			
		} else {
			$( thisQuiz.selector ).find( '.fca_qc_quiz_div' ).removeClass('flip')
		}

		showQuestion( thisQuiz )
	})

	$( '.fca_qc_answer_div' ).click(function() {
		var thisQuiz =  quizzes[ get_quiz_id( $(this).closest('.fca_qc_quiz') ) ]
		$( this ).blur()
		
		thisQuiz.responses.push ( $( this ).children('.fca_qc_answer_span').html() )
		
		if ( thisQuiz.hideAnswers ) {
			if ( $( this ).attr('data-question') == thisQuiz.currentAnswer ) {
				
				thisQuiz.score = thisQuiz.score + 1
				showQuestion( thisQuiz )
				
			} else {

				showQuestion( thisQuiz )
			}
			
		} else {
			
			$( thisQuiz.selector ).find( '#fca_qc_your_answer' ).html( $( this ).children('.fca_qc_answer_span').html() )
			
			$( thisQuiz.selector ).find( '#fca_qc_correct_answer' ).html( get_correct_answer_html( thisQuiz ) )
			if ( usingIE ) {
				$( thisQuiz.selector ).find( '#fca_qc_answer_container' ).hide()
				$( thisQuiz.selector ).find( '#fca_qc_back_container' ).show()
			} else {
				$( thisQuiz.selector ).find( '.fca_qc_quiz_div' ).addClass( 'flip' )
			}
			
			$( thisQuiz.selector ).find( '#fca_qc_back_container' ).removeClass( 'correct-answer' )
			$( thisQuiz.selector ).find( '#fca_qc_back_container' ).removeClass( 'wrong-answer' )

			if ( $( this ).attr('data-question') == thisQuiz.currentAnswer ) {
				
				thisQuiz.score = thisQuiz.score + 1
				
				$( thisQuiz.selector ).find( '#fca_qc_back_container' ).addClass( 'correct-answer' )
				$( thisQuiz.selector ).find( '#fca_qc_question_right_or_wrong' ).html( thisQuiz.correct_string )
				$( thisQuiz.selector ).find( '#fca_qc_correct_answer_p' ).hide()
				
				
			} else {

				$( thisQuiz.selector ).find( '#fca_qc_back_container' ).addClass( 'wrong-answer' )
				$( thisQuiz.selector ).find( '#fca_qc_question_right_or_wrong' ).html( thisQuiz.wrong_string )
				$( thisQuiz.selector ).find( '#fca_qc_correct_answer_p' ).show()
				
			}
			
			scale_flip_box_back( thisQuiz.selector )
		}
	
	})
	
	////////////////
	//	HELPER FUNCTIONS 
	////////////////	
	
	function get_quiz_id ( obj ){
		return $( obj ).attr('id').replace(/\D+/g, "");
	}
	
	function showQuestion( quiz ) {

		if (  quiz.currentQuestion < quiz.questionCount  ) {
			
			$( quiz.selector ).find( '.fca_qc_question_count' ).html( ( quiz.currentQuestion + 1) + "/" + quiz.questionCount)
			
			$( quiz.selector ).find( '.fca_qc_answer_div' ).removeClass('quizprep-wrong-answer')
			
			var question = quiz.questions[quiz.currentQuestion].question
			var answer = quiz.questions[quiz.currentQuestion].answer
			var img = quiz.questions[quiz.currentQuestion].img
			var wrong1 = quiz.questions[quiz.currentQuestion].wrong1
			var wrong2 = quiz.questions[quiz.currentQuestion].wrong2
			var wrong3 = quiz.questions[quiz.currentQuestion].wrong3
			
			var answers = [answer, wrong1, wrong2, wrong3]
			var shuffled_answers = shuffleArray( answers )
			
			$( quiz.selector ).find( '#fca_qc_answer_container' ).find( '.fca_qc_quiz_question_img' ).attr('src', img)
			$( quiz.selector ).find( '#fca_qc_back_container' ).find( '.fca_qc_quiz_question_img' ).attr('src', img)
			
			//LAZY LOAD NEXT IMAGE 
			if ( (quiz.currentQuestion + 1) < quiz.questionCount ) {
				lazyLoadImage ( quiz.questions[quiz.currentQuestion+1].img )
			}
			
			$( quiz.selector ).find( '#fca_qc_question' ).html(question)
			$( quiz.selector ).find( '#fca_qc_question_back' ).html(question)
			
			$( quiz.selector ).find( '.fca_qc_answer_div' ).show()
			
			//PUT OUR ANSWER DATA INTO THE DIVS, BUT IF ITS EMPTY HIDE THE PARENT ELEMENT
			for (var i = 0; i<shuffled_answers.length; i++) {
				if ( shuffled_answers[i] == '') {
					$( quiz.selector ).find( '.fca_qc_answer_span' ).eq(i).parent().hide()
				} else {
					if ( shuffled_answers[i] == quiz.questions[quiz.currentQuestion].answer ) {
						quiz.currentAnswer = $( quiz.selector ).find( '.fca_qc_answer_div' ).eq(i).attr('data-question')
					}
					$( quiz.selector ).find( '.fca_qc_answer_span' ).eq(i).html(shuffled_answers[i])
				}
		
			}
				
			quiz.currentQuestion = quiz.currentQuestion + 1
						
			
			$( quiz.selector ).find( '#fca_qc_answer_container' ).waitForImages( function() {
				scale_flip_box_question( quiz.selector )
			})

		} else {
			endTest( quiz )
		}
		
	}
	
	function scale_flip_box_question( selector ) {
		var newHeight = $(selector).find('#fca_qc_question').outerHeight(true)
		
		newHeight += $(selector).find('.fca_qc_quiz_question_img').outerHeight(true)
		
		$(selector).find( '.fca_qc_answer_div' ).each(function(){
			if ( $( this ).is( ':visible' ) ) {
				
				newHeight += $(this).outerHeight(true)
			}
			
		})
		
		if ( newHeight < 200 ) {
			newHeight = 200
		}

		$(selector).find( '.fca_qc_quiz_div, #fca_qc_answer_container, #fca_qc_back_container' ).height( newHeight )
		
	}
	
	function scale_flip_box_back( selector ) {
		var newHeight = 0
		$(selector).find('#fca_qc_back_container').children().each( function() {
			if ( $( this ).is( ':visible' ) ) {	
				newHeight += $(this).outerHeight(true)
			}
		})
		
		// A MARGIN FOR THE BOTTOM
		newHeight += 35
	
		if ( newHeight < 400 ) {
			newHeight = 400
		}

		$(selector).find( '.fca_qc_quiz_div, #fca_qc_answer_container, #fca_qc_back_container' ).height( newHeight )
		
	}
	
	function set_result( quiz ) {

		var yourResult = "undefined"
		var i = 0
		
		while ( yourResult == "undefined" ) {
			if ( quiz.quiz_results[i].min <= quiz.score && quiz.quiz_results[i].max >= quiz.score) {
				yourResult = quiz.quiz_results[i]
			} else if( i == quiz.quiz_results.length ) {
				yourResult = 'error'
			}else {
				i++
			}
		}

		var scoreParagraph = scoreString.replace('{{SCORE_CORRECT}}', quiz.score)
		scoreParagraph = scoreParagraph.replace('{{SCORE_TOTAL}}', quiz.questionCount)
		  
		$( quiz.selector ).find( '.fca_qc_score_text').html( scoreParagraph )
		$( quiz.selector ).find( '.fca_qc_score_title').html( yourResult.title )
		$( quiz.selector ).find( '.fca_qc_score_img').attr( 'src', yourResult.img )
		$( quiz.selector ).find( '.fca_qc_score_desc').html( yourResult.desc )
			
	}
	
	//DRAW THE 'YOUR RESPOSNES' BOXES AT THE END OF THE QUIZ
	function show_responses( quiz ) {
				
		for (var i = 0; i<quiz.questions.length; i++ ) {
			do_answer_response_div( quiz.questions[i], quiz.responses[i], i + 1, quiz.selector, quiz.your_answer_string, quiz.correct_answer_string  )
		}
		$( quiz.selector ).find( '.fca_qc_result_container' ).show()
	}
	
	function do_answer_response_div( questionArray, response, questionNumber, selector, yourAnswerString, correctAnswerString ) {
	
		var html = ''
		
		if ( questionArray.answer == response ) {
			html += "<div class='fca_eoi_question_response_item correct-answer'>"
		} else {
			html += "<div class='fca_eoi_question_response_item wrong-answer'>"
		}
				
		html += "<h3 class='fca_eoi_question_response_question'>" + questionNumber + ". " + questionArray.question + "</h3>"
		html += "<img class='fca_qc_quiz_question_img' src='" + questionArray.img + "'>"
		html += "<p class='fca_eoi_question_response_response'><span class='fca_qc_bold'>" + yourAnswerString + " </span>" + response + "</p>"
		html += "<p class='fca_eoi_question_response_correct_answer'><span class='fca_qc_bold'>" + correctAnswerString + " </span>" + questionArray.answer + "</p>"
					
		html += "</div>"
		
		$( selector ).find( '.fca_qc_insert_response_above' ).before(html)
		
	}
	
	function endTest( quiz ) {
	
		$( quiz.selector ).find( '.fca_qc_quiz_footer' ).hide()
		$( quiz.selector ).find( '.fca_qc_quiz_div' ).hide()
		$( quiz.selector ).find( '.fca_qc_score_container' ).show()
		
		set_result( quiz )
		
		if ( quiz.hideAnswers ) {
			show_responses( quiz )
		}
		
	}
	
	function get_correct_answer_html ( quiz ) {
		var returnItem = ''
		$( quiz.selector ).find('.fca_qc_answer_div').each(function() {
			if ( $(this).attr('data-question') == quiz.currentAnswer ) {
				returnItem = $(this).find('.fca_qc_answer_span').html()
			}
		})
		return returnItem
		
	}
	
	////////////////
	//	UTILITY FUNCTIONS 
	////////////////	

	function shuffleArray(array) {
		for (var i = array.length - 1; i > 0; i--) {
			var j = Math.floor(Math.random() * (i + 1))
			var temp = array[i]
			array[i] = array[j]
			array[j] = temp
		}
		return array
	}
	
})