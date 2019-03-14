<?php

/**
* Returns an array of active Labsters.
*/
function get_active_labsters() {

	$labster_query_args = array(
		'post_type'				=> 'wc_user_membership',
		'post_status'			=> 'wcm-active',
		'post_parent__in'	=> array(
			223686, // Lab Pro
			223685, // Lab
		),
		'posts_per_page'	=> -1,
	);

	$labster_query = new WP_Query( $labster_query_args );

	if ( $labster_query->have_posts() ) :

		$labsters	= array();

		while ( $labster_query->have_posts() ) : $labster_query->the_post();

			array_push( $labsters, array(
				'labster_id'	=> get_the_ID(),
				'email'				=> get_the_author_meta( 'user_email' ),
				'first_name'	=> get_the_author_meta( 'user_firstname' ),
				'last_name'		=> get_the_author_meta( 'user_lastname' ),
			) );

		endwhile; wp_reset_postdata();

		// Sorts $labsters[] by last name.
		usort( $labsters, function( $a, $b ) {
			return $a[ 'last_name' ] <=> $b[ 'last_name' ];
		});

		return $labsters;

	else :

		return;

	endif;

}


/**
* Returns an array of scorecard data for a given email address.
*
* @param string $user_email Optional. Accepts a valid email address.
* Defaults to logged-in user.
*/
function get_scorecard_results( $user_email = '' ) {

	if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) :

		if ( empty( $user_email ) ) {

			// Gets the current user's email address.
			$user_info	= get_userdata( get_current_user_id() );
			$user_email	= $user_info->user_email;

		}

		// Defines the Gravity Forms form ids.
		// 45 = Small Firm Scorecard; 47 = Solo Practice Scorecard
		$form_ids		= array( 45, 47 );

		// Searches for all scorecards that have the current user's email address.
		$search_criteria['field_filters'][] = array(
			'key'		=> '18', // Luckily, 18 is the email field ID in both forms.
			'value' => $user_email,
		);

		// Sorts scorecard results by the date created, with the most recent first.
		$sorting = array(
			'key'					=> 'date_created',
			'direction'		=> 'DESC',
		);

		// Gets all the scorecards for the given $user_email.
		$entries = GFAPI::get_entries( $form_ids, $search_criteria, $sorting );

		if ( !empty( $entries ) ) {

			$scorecard_results = array();

			foreach ( $entries as $entry ) {

				$entry_id		= $entry['id'];
				$form_id		= $entry['form_id'];
				$raw_score	= $entry['gsurvey_score'];

				// Checks to see which form was submitted.
			  switch ( $form_id ) {

			    case $form_id == '45': // Small Firm Scorecard

			      $total = 500;
						$scorecard_result[ 'version' ] = 'Small Firm Scorecard';

			      break;

			    case $form_id == 47: // Solo Practice Scorecard
			      $total = 400;
						$scorecard_result[ 'version' ] = 'Solo Practice Scorecard';

			      break;

			  }

			  // Calculates the % score.
			  $score = ( $raw_score / $total ) * 100;

			  switch ( $score ) {

			    case ( $score < 60 ) :
			      $grade = 'F';
			      break;

			    case ( $score >= 60 && $score < 70 ) :
			      $grade = 'D';
			      break;

			    case ( $score >= 70 && $score < 80 ) :
			      $grade = 'C';
			      break;

			    case ( $score >= 80 && $score < 90 ) :
			      $grade = 'B';
			      break;

			    case ( $score >= 90 ) :
			      $grade = 'A';
			      break;

			  }

				$scorecard_result[ 'percentage' ]	= $score;
				$scorecard_result[ 'grade' ]			= $grade;

				// Creates a new sub-array for the scorecard.
				array_push( $scorecard_results, array(
					'entry_id'		=> $entry_id,
					'grade'				=> $scorecard_result[ 'grade' ],
					'percentage'	=> $scorecard_result[ 'percentage' ],
					'version'			=> $scorecard_result[ 'version' ],
					'date'				=> $entry[ 'date_created' ],
				) );

			}

		}

		return $scorecard_results;

	else :

		return;

	endif;

}
