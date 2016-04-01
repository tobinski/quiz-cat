jQuery(document).ready(function($){
	
	////////////////
	//	ON CLICK EVENT HANDLERS
	////////////////
	
	
	$('#fca_qc_shortcode_input').click(function(e) {
		this.select()
	})
	
	
	////////////////
	//	MEDIA UPLOAD
	////////////////
	
    $('#fca_qc_quiz_image_upload_btn').click(function(e) {
		
        e.preventDefault()
		
        var image = wp.media({ 
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first()

            var image_url = uploaded_image.toJSON().url
            // Assign the url value to the input field
            $('#fca_qc_quiz_description_image_src').val(image_url)
			$('#fca_qc_quiz_description_image').attr('src',image_url)
			
        })
    })
	
	
})