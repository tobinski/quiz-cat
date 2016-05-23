<style>
	.fca_qc_ad_bar a {
		font-size: 14px;
		font-weight: bold;
	}

	.fca_qc_ad_sidebar {
		position: absolute;
		top: 62px;
		right: 20px;
		width: 280px;
	}
	
	.fca_qc_quick_links {
		position: absolute;
		top: 330px;
		right: 20px;
		width: 280px;				
	}

	.fca_qc_ad_sidebar .fca_qc_centered {
		text-align: center;
	}

	.fca_qc_sidebar .button-large {
		font-size: 17px;
		line-height: 30px;
		height: 32px;
	}

	.fca_qc_ad_input {
		width: 100%;
	}

	.fca_qc_ad_form {
		border-top: 1px solid #fcfcfc;
		margin: 0 -11px;
		padding: 0 11px;
	}
	
	#side-sortables {
		border: none;
	}
	
	@media screen and (max-width: 850px) {
		.fca_qc_ad_sidebar, .fca_qc_quick_links  {
			display: none;
		}
	}	

</style>

<div class="sidebar-container metabox-holder fca_qc_sidebar fca_qc_ad_sidebar" id="fca_qc_ad_sidebar">
	<div class="postbox">
		<h3 class="wp-ui-primary"><span><?php _e('Need Quiz Ideas?', 'quiz-cat') ?></span></h3>

		<div class="inside">
			<div class="main">
				<p class="fca_qc_centered">
					<?php _e("So you wanna build engaging, viral quizzes? We'll send you our favorite example quizzes for inspiration?", 'quiz-cat') ?>
					
				</p>

				<form class="fca_qc_ad_form" action="https://www.getdrip.com/forms/77666172/submissions" method="post" target="_blank">
					<p>
						<label for="fca_qc_ad_input_email">Email</label>
						<input type="email" name="fields[email]" id="fca_qc_ad_input_email" class="fca_qc_ad_input" value="<?php

						echo htmlspecialchars( wp_get_current_user()->user_email )

						?>">
					</p>

					<div class="fca_qc_centered">
						<input type="submit" name="submit" class="button-primary button-large" value="<?php _e('Sign Up', 'quiz-cat') ?>">
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder fca_qc_sidebar fca_qc_quick_links" id="fca_qc_quick_links">
	<div class="postbox">
		<h3 class="wp-ui-primary"><span><?php _e('Quick Links', 'quiz-cat') ?></span></h3>
			<div class="inside">
				<div class="main">
					<ul>
						<li><div class="dashicons dashicons-arrow-right"></div><a href="http://fatcatapps.com/quizcat/" target="_blank"><?php _e( 'Need help getting started? Watch a video tutorial.', 'quiz-cat' ); ?></a> </li>
						<li><div class="dashicons dashicons-arrow-right"></div><a href="http://wordpress.org/support/plugin/quiz-cat" target="_blank"><?php _e( 'Problems or Suggestions? Get help here.', 'quiz-cat' ); ?></a> </li>
						<li><div class="dashicons dashicons-arrow-right"></div><strong><a href="https://wordpress.org/support/view/plugin-reviews/quiz-cat?rate=5#postform" target="_blank"><?php _e( 'Like this plugin?  Please leave a review.', 'quiz-cat' ); ?></strong></a> </li>
					</ul>
				</div>	
			</div>
	</div>
</div>