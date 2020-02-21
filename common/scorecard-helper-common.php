<?php

if ( !defined( 'ABSPATH' ) ) exit;

/**
* Returns an array of scorecard data for a given email address.
*
* @param string $user_email Optional. Accepts a valid email address.
* Defaults to logged-in user.
*/
function get_scorecard_results( $user_id = '' ) {

	if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) :

		$scorecard_results = array();

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$user_info	= get_userdata( $user_id );
		$user_email	= $user_info->user_email;

		// Defines the Gravity Forms form ids.
		// 45 & 60 = Small Firm Scorecard; 47 & 61 = Solo Practice Scorecard
		$form_ids	= array( 45, 47, 60, 61 );

		// Searches for all scorecards that have the current user's ID.
		$search_criteria[ 'field_filters' ][] = array(
			'key'		=> created_by,
			'value' => $user_id,
		);

		// Sorts scorecard results by the date created, with the most recent first.
		$sorting = array(
			'key'					=> 'date_created',
			'direction'		=> 'DESC',
		);

		// Gets all the scorecards for the given user ID.
		$entries = GFAPI::get_entries( $form_ids, $search_criteria, $sorting );

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
					'percentage'	=> round( $scorecard_result[ 'percentage' ] ),
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
