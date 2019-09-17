<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// Set permissions!!!


// Adds a page to the Users menu.
function scorecard_admin_reporting_menu() {
	add_submenu_page( 'users.php', 'Small Firm Scorecard Reports', 'Scorecard Reports', 'manage_options', 'scorecard', 'scorecard_admin_reporting' );
}

add_action( 'admin_menu', 'scorecard_admin_reporting_menu' );


// Outputs the reports on the page.
function scorecard_admin_reporting() {

	$this_page_url	= menu_page_url( 'scorecard', false );

	echo '<div class="wrap">';

	// Checks to see if an email address was provided in the URL.
	if ( isset( $_GET[ 'email' ] ) ) :

		$user_email	= $_GET[ 'email' ];
		$user_info	= get_user_by( 'email', $user_email );

		echo '<h1 class="wp-heading-inline">Small Firm Scorecard Report: ' . $user_info->first_name . ' ' . $user_info->last_name . '</h1>';

		echo '<table class="widefat">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Grade</th>';
					echo '<th>Date</th>';
					echo '<th>Version</th>';
					echo '<th>More</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

				$scorecard_results = get_scorecard_results( $user_email );

				foreach ( $scorecard_results as $scorecard_result ) {

					$scorecard_id				= $scorecard_result[ 'entry_id' ];
					$form_id						= $scorecard_result[ 'form_id' ];
					$scorecard_grade		= $scorecard_result[ 'grade' ];
					$scorecard_score		= $scorecard_result[ 'percentage' ];
					$scorecard_date			= date_format( date_create( $scorecard_result[ 'date' ] ), 'M. j, Y' );
					$scorecard_version	= $scorecard_result[ 'version' ];

					$gf_entries_url			= menu_page_url( 'gf_entries', false );

					echo '<tr>';
						echo '<td><strong>' . $scorecard_grade . '</strong> (' . round( $scorecard_score ) . '%)</td>';
						echo '<td>' . $scorecard_date . '</td>';
						echo '<td>' . $scorecard_version . '</td>';
						echo '<td><a href="' . $gf_entries_url. '&view=entry&id=' . $form_id . '&lid=' . $scorecard_id . '">See Scorecard</a></td>';
					echo '</tr>';

				}

			echo '<tbody>';
		echo '</table>';

	else :

		echo '<h1 class="wp-heading-inline">Small Firm Scorecard Report: Labsters</h1>';

		echo '<form action="' . $this_page_url . '" method="GET">';
			echo '<p class="search-box">';
				echo '<input type="hidden" value="scorecard" name="page">';
				echo '<label for="email" class="screen-reader-text">Search users:</label>';
				echo '<input id="email" type="email" placeholder="Enter email address" name="email">';
				echo '<input class="button" type="submit" value="Search Users">';
			echo '</p>';
		echo '</form>';

		echo '<table class="widefat">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Name</th>';
					echo '<th>Initial Grade</th>';
					echo '<th>Current Grade</th>';
					echo '<th>Last Updated</th>';
					echo '<th>More</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

				$labsters						= get_active_labsters();
				$scorecard_results	= array();

				foreach ( $labsters as $labster ) {

					$scorecard_results = get_scorecard_results( $labster[ 'email' ] );

					if ( !empty( $scorecard_results ) ) {

						// This can be removed after we upgrade to PHP 7.3.
						if ( !function_exists( 'array_key_last' ) ) {

							function array_key_last($array) {

								if ( !is_array( $array ) || empty( $array ) ) {
									return NULL;
								}

								return array_keys( $array )[ count( $array ) - 1 ];

							}

						}

						$first_scorecard_key		= array_key_last( $scorecard_results );
						$first_scorecard_grade	= $scorecard_results[ $first_scorecard_key ][ 'grade' ];
						$first_scorecard_score	= $scorecard_results[ $first_scorecard_key ][ 'percentage' ];

						$last_scorecard_grade		= $scorecard_results[ 0 ][ 'grade' ];
						$last_scorecard_score		= $scorecard_results[ 0 ][ 'percentage' ];
						$last_scorecard_version	= $scorecard_results[ 0 ][ 'version' ];
						$last_scorecard_date		= date_format( date_create( $scorecard_results[ 0 ][ 'date' ] ), 'M. j, Y' );
						$total_scorecards				= count( $scorecard_results );

						echo '<tr>';
							echo '<td>' . $labster[ 'last_name' ] . ', ' . $labster[ 'first_name' ] . '</td>';
							echo '<td>';
								if ( !$first_scorecard_key == 0 ) {
									echo $first_scorecard_grade . ' (' . $first_scorecard_score . '%)';
								} else {
									echo '';
								}
							echo '</td>';
							echo '<td><strong>' . $last_scorecard_grade . '</strong> (' . round( $last_scorecard_score ) . '%)</td>';
							echo '<td>' . $last_scorecard_date . '</td>';
							echo '<td><a href="' . $this_page_url . '&email=' . $labster[ 'email' ] . '">See history</a> (' . $total_scorecards . ')</td>';
						echo '</tr>';

					}

				}

			echo '<tbody>';
		echo '</table>';

	endif;

	echo '</div>'; // Close .wrap

}
