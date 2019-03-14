<?php

/*------------------------------
Insider Dashboard: Small Firm Scorecard
------------------------------*/

function scorecard_results_graph() {

	$scorecard_results = get_scorecard_results();

	$user_info	= get_userdata( get_current_user_id() );
	$user_email	= $user_info->user_email;

	if ( empty( $scorecard_results ) ) {

		ob_start();

			echo '<div id="dashboard-scorecard-widget" class="card">';

			echo '<p class="dashboard-widget-label">Small Firm Scorecard</p>';

				echo '<p class="dashboard-widget-note">We don\'t have a score for you yet.</p>';
				echo '<p class="dashboard-widget-note">The Small Firm Scorecard will help you discover what your firm is doing well and identify areas for improvement to help grow your law firm. It should take 10–15 minutes to complete.</p>';

				echo '<p align="center" class="remove_bottom"><a class="button remove_bottom" href="https://lawyerist.com/scorecard/">Get Your Score</a></p>';

			echo '</div>'; // Close #dashboard-scorecard-widget

		$scorecard_graph = ob_get_clean();

	} else {

		ob_start();

			$last_version = $scorecard_results[ 0 ][ 'version' ];

			// Reverses the order of the array so that the results display oldest to
			// newest from left to right.
			$scorecard_results = array_reverse( $scorecard_results );

			$col_width = 100 / count( $scorecard_results );

			echo '<div id="dashboard-scorecard-widget" class="card">';

			echo '<p class="dashboard-widget-label">Small Firm Scorecard</p>';

			echo '<div class="scorecard-results-wrapper">';

				foreach ( $scorecard_results as $scorecard_result ) {

					$this_col_year = date_format( date_create( $scorecard_result[ 'date' ] ), 'Y' );
					$col_height	= $scorecard_result[ 'percentage' ];

					if ( empty( $prev_col_year ) || $this_col_year != $prev_col_year ) {

						$year						= $this_col_year;
						$prev_col_year	= date_format( date_create( $scorecard_result[ 'date' ] ), 'Y' );

						$add_border			= ' style="border-left: 1px solid #ddd;"';

					} else {

						$year				= '&nbsp;';
						$add_border	= '';

					}

					echo '<div class="scorecard-result-wrapper" style="width: ' . $col_width . '%;">';

						echo '<div class="scorecard-year"' . $add_border . '>' . $year . '</div>';
						echo '<div class="scorecard-month-day">' . date_format( date_create( $scorecard_result[ 'date' ] ), 'n/d' ) . '</div>';

						echo '<div class="scorecard-bar-wrapper">';
							echo '<div class="scorecard-bar" style="height: ' . $col_height/10 . 'rem;" title="On ' . date_format( date_create( $scorecard_result[ 'date' ] ), 'F j, Y' ) . ', you gave yourself ' . $scorecard_result[ 'percentage' ] . '% on the ' . $scorecard_result[ 'version' ] . '."></div>';
						echo '</div>';

						echo '<div class="scorecard-grade"><strong>' . $scorecard_result[ 'grade' ] . '</strong></div>';
						echo '<div class="scorecard-percentage">' . round( $scorecard_result[ 'percentage' ] ) . '%</div>';

					echo '</div>';

				}

			echo '</div>'; // Close #dashboard-scorecard-widget-frame

			echo '<p class="dashboard-widget-note">We recommend updating your score every three months, and no less than once a year.</p>';

			switch ( $last_version ) {

				case ( $last_version == 'Small Firm Scorecard' ) :

					$scorecard_url = 'https://lawyerist.com/scorecard/small-firm-scorecard/';
					break;

				case ( $last_version == 'Solo Practice Scorecard' ) :

					$scorecard_url = 'https://lawyerist.com/scorecard/solo-practice-scorecard/';
					break;

			}

			echo '<p align="center" class="remove_bottom"><a class="button remove_bottom" href="' . $scorecard_url . '">Update your score</a></p>';

			echo '</div>'; // Close #dashboard-scorecard-widget

		$scorecard_graph = ob_get_clean();

	}

	return $scorecard_graph;

}
