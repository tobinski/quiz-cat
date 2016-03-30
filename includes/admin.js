jQuery( document ).ready(function($) {
	
	
	const url = location.href
	const urlSplit = url.split("&")[0]
			
	const fca_qc_select_form = document.getElementById("fca_qc_select_form")
	const fca_qc_delete_form_btn = document.getElementById("fca_qc_delete_form_btn")	
	const fca_qc_current_form = document.getElementById("fca_qc_current_form")
	const fca_qc_form_name_input = document.getElementById("fca_qc_name_input")	
	const currentForm = fca_qc_current_form.value
	const formName = fca_qc_form_name_input.value
	
	const new_question_div = "<div class='fca_qc_admin_panel'><span class='dashicons dashicons-trash fca_qc_delete_icon'></span><h3 class='fca_qc_toggle_h3'>Question {{QUESTION_NUMBER}} <span class='fca_qc_admin_question_label'></span></h3><div class='fca_qc_question_info'><label class='fca_qc_label' for='fca_qc_question_input'>Question: </label><textarea class='fca_qc_align_top fca_qc_admin_question_input' name='fca_qc_question_input[]'></textarea><br><label class='fca_qc_label' for='fca_qc_answer_input'>Correct Answer: </label><textarea class='fca_qc_align_top' name='fca_qc_answer_input[]'></textarea><br><label class='fca_qc_label' for='fca_qc_hint_input'>Hint: </label><textarea class='fca_qc_align_top' name='fca_qc_hint_input[]'></textarea><br><label class='fca_qc_label' for='fca_qc_wrong_1_input'>Wrong Answer 1: </label> <textarea class='fca_qc_align_top' name='fca_qc_wrong_1_input[]'></textarea><br><label class='fca_qc_label' for='fca_qc_wrong_2_input'>Wrong Answer 2: </label><textarea class='fca_qc_align_top' name='fca_qc_wrong_2_input[]'></textarea><br><label class='fca_qc_label' for='fca_qc_wrong_3_input'>Wrong Answer 3: </label><textarea class='fca_qc_align_top' name='fca_qc_wrong_3_input[]'></textarea><br></div></div>" 

	fca_qc_delete_form_btn.onclick = function(event){
	
		event.preventDefault()
		if (confirm('Delete form ' + formName + '?')) {
			document.location.href = urlSplit + "&fca_qc_delete_form=" + currentForm
			setConfirmUnload( true )
		} else {
			// Do nothing!
		}	
	}
	
	function fca_qc_add_delete_button_handlers() {
			
		$('.fca_qc_delete_icon').unbind( "click" )
		
		$('.fca_qc_delete_icon').click(function(){

			if (confirm('Delete question?')) {
				$( this ).closest('.fca_qc_admin_panel').remove()
				let question_number = $( '#fca_qc_question_count' ).val()
				
				if (question_number == '' || isNaN(question_number)) {
					question_number = 1
				} else {
					question_number = Number(question_number)
				}
				setConfirmUnload( true )
				question_number = question_number - 1
		
				$( '#fca_qc_question_count' ).val(question_number)
			} else {
				// Do nothing!
			}
			
		})
	}
	
	function fca_qc_h3_toggle_handlers() {
			
		$('.fca_qc_toggle_h3').unbind( "click" )
		$('.fca_qc_admin_question_input').unbind( "keyup" )
		
		
		$( '.fca_qc_admin_question_input' ).keyup(function() {
			const value = $( this ).val()
			$( this ).parents('.fca_qc_admin_panel').children('.fca_qc_toggle_h3').children('.fca_qc_admin_question_label').html(value)
				
		})

		$( '.fca_qc_toggle_h3' ).click(function() {
			$( this ).next().toggle('fast')
				
		})	
	
	}
	
	fca_qc_select_form.onchange = function(){
		/* FORWARD TO NEW URL ON CHANGE */
		document.location.href = urlSplit + "&fca_qc_select_form=" + fca_qc_select_form.options[fca_qc_select_form.selectedIndex].value
	}
	
	
		
	$( '#fca_qc_add_question_btn' ).click(function() {
		addQuestion()
		setConfirmUnload( true )
	})
	
	
	function addQuestion() {
	
		let div_to_append = new_question_div
		
		let question_number = $( '#fca_qc_question_count' ).val()
				
		if (question_number == '' || isNaN(question_number)) {
			question_number = 1
		} else {
			question_number = Number(question_number)
		}
		
		question_number = question_number + 1
		
		$( '#fca_qc_question_count' ).val(question_number)
		
		div_to_append = div_to_append.replace("{{QUESTION_NUMBER}}", question_number)
		
		$( '#fca_qc_question_inputs' ).append(div_to_append)
		
		fca_qc_add_delete_button_handlers()
		fca_qc_h3_toggle_handlers()
	
	}
	
	fca_qc_h3_toggle_handlers()
	fca_qc_add_delete_button_handlers()
	
	window.setTimeout(hideNotice, 800)
	
	function hideNotice() {
		$( '.fca_qc_notice' ).hide('slow')
	}
	
		/***** ADD CONFIRM DIALOG WHEN NAVIGATING AWAY *****/
	
	function setConfirmUnload( on ) {
    
		 window.onbeforeunload = ( on ) ? unloadMessage : null

	}

	function unloadMessage() {
		
		 return 'You have entered new data on this page.' +
			' If you navigate away from this page without' +           
			' first saving your data, the changes will be' +
			' lost.'

	}
	
	/***** PREVENT ACCIDENTAL NAVIGATION AWAY *****/
	
	$( 'input, textarea' ).bind( 'change', function() { 
		setConfirmUnload( true )
	}) 
	
	$( '#submit, #fca_qc_delete_form_btn' ).click(function(){
		setConfirmUnload( false )
	})
	
	//UNHIDE MAIN PAGE WHEN LOADED -> PREVENT FLASHING PARTIALLY RENDERED HTML
	$( '#fca_qc_admin_page' ).show()
	
	
})