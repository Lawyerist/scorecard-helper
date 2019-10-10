<?php

if ( !defined( 'ABSPATH' ) ) exit;

/**
* Returns an array of scorecard data for a given email address.
*
* @param string $user_email Optional. Accepts a valid email address.
* Defaults to logged-in user.
*/
function get_scorecard_results( $user_email = '' ) {

	if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) :

		$scorecard_results = array();

		if ( empty( $user_email ) ) {

			// Gets the current user's email address.
			$user_ID		= get_current_user_id();
			$user_info	= get_userdata( $user_ID );
			$user_email	= $user_info->user_email;

		}

		// Defines the Gravity Forms form ids.
		// 45 & 60 = Small Firm Scorecard; 47 & 61 = Solo Practice Scorecard
		$form_ids_v1	= array( 45, 47 );
		$form_ids_v2	= array( 60, 61 );

		// Searches for all scorecards that have the current user's email address.
		$search_criteria_v1[ 'field_filters' ][] = array(
			'key'		=> 18, // Luckily, 18 is the email field ID in both v1 forms.
			'value' => $user_email,
		);

		$search_criteria_v2[ 'field_filters' ][] = array(
			'key'		=> array( 1, 2 ), // Deliberately, 1 and 2 are the email and user ID field IDs in both v2 forms.
			'value' => array( $user_ID, $user_email ),
		);

		$search_criteria_v2[ 'field_filters' ][ 'mode' ] = 'any';

		// Sorts scorecard results by the date created, with the most recent first.
		$sorting = array(
			'key'					=> 'date_created',
			'direction'		=> 'DESC',
		);

		// Gets all the scorecards for the given $user_email.
		$entries_v1 = GFAPI::get_entries( $form_ids_v1, $search_criteria_v1, $sorting );
		$entries_v2 = GFAPI::get_entries( $form_ids_v2, $search_criteria_v2, $sorting );

		$entries = array_merge( $entries_v2, $entries_v1 );
		// The order is important so the entries sort properly by date.

		if ( !empty( $entries ) ) {

			foreach ( $entries as $entry ) {

				$entry_id		= $entry[ 'id' ];
				$form_id		= $entry[ 'form_id' ];
				$raw_score	= $entry[ 'gsurvey_score' ];

				// Checks to see which form was submitted.
			  switch ( $form_id ) {

					// Small Firm Scorecard 1.0
			    case $form_id == 45:
						$total = 500;
						$scorecard_result[ 'version' ] = 'Small Firm Scorecard 1.0';

						break;

					// Small Firm Scorecard 2.0
					case $form_id == 60:
			      $total = 500;
						$scorecard_result[ 'version' ] = 'Small Firm Scorecard 2.0';

			      break;

					// Solo Practice Scorecard 1.0
			    case $form_id == 47:
			      $total = 400;
						$scorecard_result[ 'version' ] = 'Solo Practice Scorecard 1.0';

			      break;

					// Solo Practice Scorecard 2.0
			    case $form_id == 61:
			      $total = 420;
						$scorecard_result[ 'version' ] = 'Solo Practice Scorecard 2.0';

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
					'form_id'			=> $form_id,
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
