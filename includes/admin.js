jQuery(document).ready(function($){
	
	////////////////
	//	VARIABLES FROM PHP
	////////////////	
	
	//console.log ( adminData )

	
	////////////////
	//	SET DEFAULTS
	////////////////
	
	//SET TRANSLATIONS FOR ON/OFF SWITCHES
	$( '.onoffswitch-inner' ).each( function(){
		$(this).attr('data-content-on', adminData.on_string );
		$(this).attr('data-content-off', adminData.off_string );
	});
	
	
	//HIDE THE INDIVIDUAL QUESTION AND RESULT INPUTS, AS WELL AS THE DEFAULT SUBMIT/PUBLISH METABOX
	if ( $( '.fca_qc_question_input_div' ).length > 1 ) {
		$( '.fca_qc_question_input_div' ).hide()
	}
	if ( $( '.fca_qc_result_input_div' ).length > 1 ) {
		$( '.fca_qc_result_input_div' ).hide()
	}

	
	//SET UP SAVE AND PREVIEW BUTTONS, THEN HIDE THE PUBLISHING METABOX
	const saveButton = '<button type="button" class="button-primary" id="fca_qc_submit_button">' + adminData.save_string + '</buttton>'
	const previewButton = '<button type="button" class="button-secondary" id="fca_qc_preview_button">' + adminData.preview_string + '</buttton>'

	$( '#normal-sortables' ).append( saveButton )
	$('#fca_qc_submit_button').click(function(event) {
		setConfirmUnload( false )
		//$('#publish').click()
		event.preventDefault()
		
        // Add target
        var form = $(this).closest('form')
        form.removeAttr('target')

        // Remove preview url
        $('#fca_qc_quiz_preview_url').val('')
		
        // Submit form
        form.submit()
		  
        return false
	})
	 
	$( '#normal-sortables' ).append( previewButton )
	$('#fca_qc_preview_button').click(function(event) {
		setConfirmUnload( false )
		var url = $('#fca_qc_quiz_preview_url').attr('data')
		$('#fca_qc_quiz_preview_url').val(url)
		event.preventDefault()
		// Add target
        var form = $(this).closest('form')
        form.prop('target', '_blank')
					
        // Submit form
        form.submit()
		  
        return false
	})
	
	$( '#submitdiv' ).hide()
	
	
	//SHOW OUR MAIN DIV AFTER WE'RE DONE WITH DOM CHANGES
	$( '#wpbody-content').show()
	
	
	//DRAG AND DROP SUPPORT
	var dragCheck = false;
	function add_drag_and_drop_sort() {
		
		$( '.fca_qc_sortable_results, .fca_qc_sortable_questions' ).sortable({
			revert: true,
			start: function(){
				// On drag set that flag to true
				dragCheck = true;
			},
			stop: function(){
				// On stop of dragging reset the flag back to false
				dragCheck = false;
			}
	
		})
		
		$( '.fca_qc_sortable_results' ).unbind( 'sortupdate' )
		$( '.fca_qc_sortable_results' ).on( 'sortupdate', function( event, ui ) {
			setScoreRanges()
			setConfirmUnload( true )
		})
	
		$( '.fca_qc_sortable_questions' ).unbind( 'sortupdate' )
		$( '.fca_qc_sortable_questions' ).on( 'sortupdate', function( event, ui ) {
			setQuestionNumbers()
			setConfirmUnload( true )
		})
	
	}
	add_drag_and_drop_sort()

	
	////////////////
	//	ON CLICK EVENT HANDLERS
	////////////////
	
	//THE ADD QUESTION BUTTON
	$( '#fca_qc_add_question_btn' ).click(function() {

		var question_number = $( '.fca_qc_question_item' ).length
		
		question_number = question_number + 1

		var div_to_append = adminData.questionDiv.replace('{{QUESTION_NUMBER}}', question_number)
		div_to_append = div_to_append.replace('{{QUESTION_NUMBER}}:', question_number + ':')
		
		$( '.fca_qc_sortable_questions' ).append(div_to_append)
		
		add_drag_and_drop_sort()
		add_question_heading_text_handlers()
		add_question_and_result_click_toggles()
		add_question_3_and_4_toggles()
		attach_delete_button_handlers()
		attach_image_upload_handlers()
		setScoreRanges()
		setQuestionNumbers()
		setConfirmUnload( true )
		
	})
	
	//THE ADD RESULT BUTTON
	$( '#fca_qc_add_result_btn' ).click(function() {

		var result_number = $( '.fca_qc_result_item' ).length
		
		result_number = result_number + 1
	
		var div_to_append = adminData.resultDiv.replace('{{RESULT_NUMBER}}', result_number )
		div_to_append = div_to_append.replace('{{RESULT_NUMBER}}<', result_number + '<' )
		
		$( '.fca_qc_sortable_results' ).append(div_to_append)
		
		add_drag_and_drop_sort()
		add_question_and_result_click_toggles()
		attach_delete_button_handlers()
		attach_image_upload_handlers()
		add_result_heading_text_handlers()
		setScoreRanges()
		setConfirmUnload( true )
		
	})
	
	//QUESTION 3 AND 4 TOGGLES
	function add_question_3_and_4_toggles() {
		
		$( '.fca_qc_answer_toggle' ).unbind( 'click' )
		
		$( '.fca_qc_answer_toggle' ).click( function() {
			
			$(this).parent().next().val('')
			
			$(this).parent().next().toggle()
			
			const str = "(" + adminData.remove_string + ")"
			
			if ( $(this).html() === str ) {
				$(this).html( "(" + adminData.show_string + ")") 
			} else {
				$(this).html( "(" + adminData.remove_string + ")") 
			}
		})
	}
	add_question_3_and_4_toggles()
	
	//MAKES SHORTCODE INPUT AUTO-SELECT THE TEXT WHEN YOU CLICK IT
	$('#fca_qc_shortcode_input').click(function(e) {
		this.select()
	})
	
	//MAKES CLICKING LABELS AUTO-SELECT THE NEXT ITEM
	$('.fca_qc_admin_label').click(function(e) {
		$( this ).next().focus()
	})
	
	//MAKES QUESTION AND RESULT LABELS TOGGLE THE INPUT VISIBILITY ON CLICK
	function add_question_and_result_click_toggles() {
			
		$( '.fca_qc_question_item, .fca_qc_result_item' ).unbind( 'click' )

		$( '.fca_qc_question_item' ).click( function() {
			if ( dragCheck == false ) {
				$( this ).find( '.fca_qc_question_input_div' ).toggle( 'fast' )
			}
				
		})	
		$( '.fca_qc_result_item' ).click( function() {
			if ( dragCheck == false ) {
				$( this ).find( '.fca_qc_result_input_div' ).toggle( 'fast' )	
			}
					
		})	
		
		$( '.fca_qc_question_input_div, .fca_qc_result_input_div' ).bind( 'click', function(e) {
			e.stopPropagation();
		})
		
	}
	add_question_and_result_click_toggles()
	
	
	//MAKES QUESTION HEADINGS AUTOMATICALLY SHOW THE QUESTION FROM THE INPUT BELOW IT
	function add_question_heading_text_handlers() {
			
		$( '.fca_qc_question_text' ).unbind( 'keyup' )


		$( '.fca_qc_question_text' ).keyup( function() {
	
			$( this ).closest( '.fca_qc_question_input_div').prev().children( '.fca_qc_quiz_heading_text' ).html( $( this ).val() )
		})	
	}
	add_question_heading_text_handlers()
	
	//MAKES RESULT HEADINGS AUTOMATICALLY SHOW THE RESULT TITLE FROM THE INPUT BELOW IT
	function add_result_heading_text_handlers() {
			
		$('.fca_qc_quiz_result').unbind( 'keyup' )

		$( '.fca_qc_quiz_result' ).keyup( function() {
			$( this ).closest( '.fca_qc_result_input_div').siblings( '.fca_qc_result_label').children( '.fca_qc_result_score_title' ).html( $( this ).val() )
		})	
	}
	add_result_heading_text_handlers()
	
	//THE DELETE QUESTION BUTTON
	function attach_delete_button_handlers() {
			
		$('.fca_qc_delete_icon').unbind( 'click' )
		
		$('.fca_qc_delete_icon').click( function(){

			if (confirm( adminData.sureWarning_string )) {
				$( this ).closest('.fca_qc_deletable_item').remove()
				setScoreRanges ()
				setConfirmUnload( true )

			} else {
				// Do nothing!
			}
			
		})
	}	
	attach_delete_button_handlers()
	
		
	////////////////
	//	HELPER FUNCTIONS
	////////////////
	
	//FINDS RANGE OF RESULTS FOR EACH RESULT AUTOMATICALLY.
	//results -> based on question count, divided by result count, with rounding to cover all
	//e.g. 5 ANSWERS, 3 RESULTS = [0-1],[2-3],[4-5]
	//at max ( equal to questions ) -> remove ability to add more
	//when question or result count changes, have to re-calculate
	function setScoreRanges() {
		const questionCount = $( '.fca_qc_question_item' ).length
		const resultCount = $( '.fca_qc_result_item' ).length
		//plus one because zero is a possible result, e.g. you can get 0/10
		const divisor = parseInt ( (questionCount + 1) / resultCount )
		var remainder = ( (questionCount + 1) % resultCount )
		//n is the result 'counter' to be iterated, and passed to the next result to start at
		var n = 0
		
		$( '.fca_qc_result_item' ).each(function() {
			
			if ( n <= questionCount ) {
				var start = n
				var end = 0
						
				if ( start == questionCount ) {
					
					end = start
					
				} else {
					
					end = start + (divisor - 1)
					if ( remainder != 0 ) {
						end = end + 1
						remainder = remainder - 1
					}
					if ( end > questionCount ) {
						end = questionCount
					}
					
				}
				
				n = end + 1
				
				
				$(this).children( '.fca_qc_result_min' ).attr('value', start)
				$(this).children( '.fca_qc_result_max' ).attr('value', end)
				
				if (end == start ) {
					$(this).children('.fca_qc_result_label').children('.fca_qc_result_score_value').html( start + ' ' + adminData.points_string + ': ' )
				} else {
					$(this).children('.fca_qc_result_label').children('.fca_qc_result_score_value').html( start + '-' + end + ' ' + adminData.points_string + ': ')
				}		
			} else {
				$(this).children('.fca_qc_result_label').children('.fca_qc_result_score_value').html( adminData.unused_string )
			}

		})
	}	
	setScoreRanges()
	
	function setQuestionNumbers(){
		var n = 1;
		$( '.fca_qc_question_item' ).each(function() {
			$(this).children( '.fca_qc_question_label' ).children( '.fca_qc_quiz_heading_question_number' ).html( adminData.question_string + ' ' + n + ': ')
			n = n + 1
		})
	}
	
	////////////////
	//	MEDIA UPLOAD
	////////////////
		
	function attach_image_upload_handlers() {
		//ACTION WHEN CLICKING IMAGE UPLOAD
		$('.fca_qc_quiz_image_upload_btn, .fca_qc_image').unbind( 'click' )
		//HANDLER FOR RESULTS AND META IMAGES
		$('.fca_qc_quiz_image_upload_btn, .fca_qc_image').click(function(e) {
			
			e.preventDefault()
			$this = $( this )
			//IF WE CLICK ON THE IMAGE VS THE BUTTON IT HAS TO WORK A LITTLE DIFFERENTLY
			if ( $(this).hasClass( 'fca_qc_quiz_image_upload_btn' ) ) {
				$this = $( this.parentNode ).siblings('.fca_qc_image')
			}			
			
			var image = wp.media({ 
				title: adminData.selectImage_string,
				// mutiple: true if you want to upload multiple files at once
				multiple: false
			}).open()
			.on('select', function(e){
				// This will return the selected image from the Media Uploader, the result is an object
				var uploaded_image = image.state().get('selection').first()

				var image_url = uploaded_image.toJSON().url
				// Assign the url value to the input field
				$this.siblings('.fca_qc_image_input').attr('value', image_url)
				$this.attr('src',image_url)
				//UNHIDE THE REMOVE AND CHANGE IMAGE BUTTONS
				$this.siblings('.fca_qc_image_hover_controls').children('.fca_qc_quiz_image_revert_btn').show()
				$this.siblings('.fca_qc_image_hover_controls').children('.fca_qc_quiz_image_upload_btn').show()
				
			})
		})
		
		//ACTION WHEN CLICKING REMOVE IMAGE
		$('.fca_qc_quiz_image_revert_btn').unbind( 'click' )
		$('.fca_qc_quiz_image_revert_btn').click( function(e) {
			$( this.parentNode ).siblings('.fca_qc_image_input').attr('value', '')
			$( this.parentNode ).siblings('.fca_qc_image').attr('src', adminData.image_placeholder_url )
			$( this ).hide()
			$( this ).siblings( '.fca_qc_quiz_image_upload_btn' ).hide()
			
		})
	}
	attach_image_upload_handlers()

	
	////////////////
	//	PREVENT ACCIDENTIAL NAV
	////////////////
	
	function setConfirmUnload( on ) {
    
		 window.onbeforeunload = ( on ) ? unloadMessage : null

	}

	function unloadMessage() {
		
		 return adminData.navigationWarning_string 

	}
	
	/***** PREVENT ACCIDENTAL NAVIGATION AWAY *****/
	
	$( 'input, textarea' ).bind( 'change', function() { 
		setConfirmUnload( true )
	}) 

	
})