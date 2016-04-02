jQuery(document).ready(function($){
	
	////////////////
	//	VARIABLES FROM PHP
	////////////////	
	
	console.log ( adminData.questionDiv )
	console.log ( adminData.navigationWarning )
	console.log ( adminData.sureWarning )
	console.log ( adminData.resultDiv )
	
	////////////////
	//	ON CLICK EVENT HANDLERS
	////////////////
	
	//THE ADD QUESTION BUTTON
	$( '#fca_qc_add_question_btn' ).click(function() {

		let question_number = $( '.fca_qc_question_item' ).length
		
		question_number = question_number + 1

		let div_to_append = adminData.questionDiv.replace('{{QUESTION_NUMBER}}', question_number)
		div_to_append = div_to_append.replace('{{QUESTION_NUMBER}}:', question_number + ':')
		
		$( '#fca_qc_add_question_btn' ).before(div_to_append)
		
		add_question_heading_text()
		add_input_click_toggles()
		attach_delete_button_handlers()
		
		setConfirmUnload( true )
	})
	
	//THE ADD RESULT BUTTON
	$( '#fca_qc_add_result_btn' ).click(function() {

		let result_number = $( '.fca_qc_result_item' ).length
		
		result_number = result_number + 1
	
		let div_to_append = adminData.resultDiv.replace('{{RESULT_NUMBER}}', result_number )
		div_to_append = div_to_append.replace('{{RESULT_NUMBER}}<', result_number + '<' )
		
		$( '#fca_qc_add_result_btn' ).before(div_to_append)
		
		add_input_click_toggles()
		attach_delete_button_handlers()
		
		setConfirmUnload( true )
	})
	
	//MAKES SHORTCODE INPUT AUTO-SELECT THE TEXT WHEN YOU CLICK IT
	$('#fca_qc_shortcode_input').click(function(e) {
		this.select()
	})
	
	//MAKES QUESTION AND RESULT LABELS TOGGLE THE INPUT VISIBILITY ON CLICK
	function add_input_click_toggles() {
			
		$( '.fca_qc_question_label, .fca_qc_result_label' ).unbind( 'click' )


		$( '.fca_qc_question_label, .fca_qc_result_label' ).click( function() {
			$( this ).next().toggle( 'fast' )
				
		})	
	}
	add_input_click_toggles()
	
	
	//MAKES QUESTION HEADINGS AUTOMATICALLY SHOW THE QUESTION FROM THE INPUT BELOW IT
	
	function add_question_heading_text() {
			
		$('.fca_qc_question_text').unbind( 'keyup' )


		$( '.fca_qc_question_text' ).keyup( function() {
	
			$( this ).closest( '.fca_qc_question_input_div').prev().children( '.fca_qc_quiz_heading_text' ).html( $( this ).val() )
		})	
	}
	add_question_heading_text()
	
	//THE DELETE QUESTION BUTTON
	function attach_delete_button_handlers() {
			
		$('.fca_qc_delete_icon').unbind( 'click' )
		
		$('.fca_qc_delete_icon').click( function(){

			if (confirm( adminData.sureWarning )) {
				$( this ).closest('.fca_qc_deletable_item').remove()

				setConfirmUnload( true )

			} else {
				// Do nothing!
			}
			
		})
	}	
	attach_delete_button_handlers()
	

	
	////////////////
	//	MEDIA UPLOAD
	////////////////
	
	
	//ACTION WHEN CLICKING IMAGE UPLOAD
    $('.fca_qc_quiz_image_upload_btn').click(function(e) {
		$this = $( this )
        e.preventDefault()
		
        var image = wp.media({ 
            title: adminData.selectImage,
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first()

            var image_url = uploaded_image.toJSON().url
            // Assign the url value to the input field
			$this.siblings('.fca_qc_image_input').val(image_url)
			$this.siblings('.fca_qc_image').attr('src',image_url)
			
        })
    })
	
	//ACTION WHEN CLICKING REMOVE IMAGE
	$('.fca_qc_quiz_image_revert_btn').click( function(e) {
		$( this ).siblings('.fca_qc_image_input').val('')
		$( this ).siblings('.fca_qc_image').attr('src','')
		
	})

	
	////////////////
	//	PREVENT ACCIDENTIAL NAV
	////////////////
	
	function setConfirmUnload( on ) {
    
		 window.onbeforeunload = ( on ) ? unloadMessage : null

	}

	function unloadMessage() {
		
		 return adminData.navigationWarning 

	}
	
	/***** PREVENT ACCIDENTAL NAVIGATION AWAY *****/
	
	$( 'input, textarea' ).bind( 'change', function() { 
		setConfirmUnload( true )
	}) 
	
	$( '#fca_qc_submit_btn' ).click(function(){
		setConfirmUnload( false )
	})
	
	
	////////////////
	//	SET DEFAULTS
	////////////////
	
	
	//MAKE THE QUESTION INPUTS AND RESULT TOGGLEABLE INPUTS HIDDEN
	$( '.fca_qc_question_input_div, .fca_qc_result_input_div').hide()
	
	
})