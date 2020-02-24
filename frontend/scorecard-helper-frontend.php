<?php

if ( !defined( 'ABSPATH' ) ) exit;

/**
* Small Firm Dashboard: Small Firm Scorecard
*/
function scorecard_results_graph() {

	$scorecard_results = get_scorecard_results();

	if ( empty( $scorecard_results ) ) {

		ob_start();

			?>

			<div id="dashboard-scorecard-widget" class="card">
				<p class="card-label">Small Firm Scorecard</p>
				<p class="dashboard-widget-note">We don't have a score for you yet.</p>
				<p class="dashboard-widget-note">The Small Firm Scorecard will help you discover what your firm is doing well and identify areas for improvement to help grow your law firm. It should take 10–15 minutes to complete.</p>
				<p align="center" class="remove_bottom"><a class="button remove_bottom" href="https://lawyerist.com/scorecard/">Get My Score</a></p>
			</div>

			<?php

		$scorecard_graph = ob_get_clean();

	} else {

		ob_start();

			$last_version = $scorecard_results[ 0 ][ 'form_id' ];

			// Reverses the order of the array so that the results display oldest to
			// newest from left to right.
			$scorecard_results = array_reverse( $scorecard_results );

			if ( count( $scorecard_results ) > 8 ) {
				$trim								= count( $scorecard_results ) - 8;
				$scorecard_results	= array_slice( $scorecard_results, $trim );
			}

			$num_results				= count( $scorecard_results );

			?>

			<div id="dashboard-scorecard-widget" class="card">
				<p class="card-label">Small Firm Scorecard</p>
				<div class="scorecard-results-wrapper" style="display: grid; grid-template-columns: repeat( <?php echo $num_results; ?>, 1fr );">

					<?php

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

						?>

						<div class="scorecard-result-wrapper">
							<div class="scorecard-year"<?php echo $add_border; ?>><?php echo $year; ?></div>
							<div class="scorecard-month-day"><?php echo date_format( date_create( $scorecard_result[ 'date' ] ), 'n/d' ); ?></div>
							<div class="scorecard-bar-wrapper">
								<?php echo '<div class="scorecard-bar" style="height: ' . $col_height/10 . 'rem;" title="On ' . date_format( date_create( $scorecard_result[ 'date' ] ), 'F j, Y' ) . ', you gave yourself ' . $scorecard_result[ 'percentage' ] . '% on ' . $scorecard_result[ 'version' ] . '."></div>'; ?>
							</div>
							<div class="scorecard-grade"><strong><?php echo $scorecard_result[ 'grade' ]; ?></strong></div>
							<div class="scorecard-percentage"><?php echo round( $scorecard_result[ 'percentage' ] ); ?>%</div>
						</div>

						<?php

					}

					?>

				</div>

				<p class="dashboard-widget-note">We recommend updating your score every three months, and no less than once a year.</p>

				<?php

				switch ( $last_version ) {

					case $last_version == 45:
					case $last_version == 60:

						$scorecard_url = 'https://lawyerist.com/scorecard/small-firm-scorecard/';
						break;

						case $last_version == 47:
						case $last_version == 61:

						$scorecard_url = 'https://lawyerist.com/scorecard/solo-practice-scorecard/';
						break;

				}

				?>

				<p align="center" class="remove_bottom"><a class="button remove_bottom" href="<?php echo $scorecard_url; ?>">Update My Score</a></p>

			</div>

			<?php

		$scorecard_graph = ob_get_clean();

	}

	return $scorecard_graph;

}


/**
* Small Firm Dashboard: Financial Scorecard
*/
function financial_scorecard_graph() {
	
}
