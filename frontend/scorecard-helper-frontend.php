<?php

if ( !defined( 'ABSPATH' ) ) exit;

/**
* Assembles a bar to be used in a bar graph.
*/
function render_graph_col( $args = array() ) {

	$defaults = array(
		'year'							=> null,
		'result_top_label'	=> null,
		'value'							=> null,
		'title'							=> null,
		'bottom_label'			=> null,
		'bottom_sub_label'	=> null,
	);

	$args									= array_merge( $defaults, $args );
	$bar_wrapper_classes	= array( 'bar-wrapper' );

	ob_start();

		?>

		<div class="result-wrapper">
			<?php if ( $args[ 'year' ] ) { ?>
				<div class="result-year"<?php if ( $args[ 'year' ] !== '&nbsp;' ) { ?> style="border-left: 1px solid #ddd;"<?php } ?>><?php echo $args[ 'year' ]; ?></div>
			<? } ?>
			<?php if ( $args[ 'result_top_label' ] ) { ?>
				<div class="result-top-label"><?php echo $args[ 'result_top_label' ]; ?></div>
			<? } ?>
			<?php if ( !is_null( $args[ 'value' ] ) && $args[ 'value' ] !== 0 ) { ?>
				<div class="<?php echo implode( ' ', $bar_wrapper_classes ); ?>" title="<?php echo $args[ 'title' ]; ?>">
					<div class="bar" style="height: <?php echo $args[ 'value' ]; ?>%"></div>
				</div>
			<? } ?>
			<?php if ( $args[ 'bottom_label' ] ) { ?>
				<div class="result-bottom-label"><strong><?php echo $args[ 'bottom_label' ]; ?></strong></div>
			<? } ?>
			<?php if ( $args[ 'bottom_sub_label' ] ) { ?>
				<div class="result-bottom-sub-label"><?php echo $args[ 'bottom_sub_label' ]; ?></div>
			<? } ?>
		</div>

		<?php

	return ob_get_clean();

}


function format_years( $this_col_year, $prev_col_year = null ) {

	if ( empty( $prev_col_year ) || $this_col_year != $prev_col_year ) {

		$year = $this_col_year;

	} else {

		$year = '&nbsp;';

	}

	return $year;

}


/**
* Small Firm Dashboard: Small Firm Scorecard
*/
function scorecard_results_graph() {

	$results = get_scorecard_results();

	ob_start();

		?>

		<div id="dashboard-scorecard-widget" class="dashboard-card card">
			<div class="card-label">Small Firm Scorecard</div>

			<?php

			if ( empty( $results ) ) {

				?>

				<p class="dashboard-widget-note">We don't have a score for you yet.</p>
				<p class="dashboard-widget-note">The Small Firm Scorecard will help you discover what your firm is doing well and identify areas for improvement to help grow your law firm. It should take 10–15 minutes to complete.</p>
				<p align="center" class="remove_bottom"><a class="button remove_bottom" href="https://lawyerist.com/scorecard/">Get My Score</a></p>

				<?php

			} else {

				$last_version = $results[ 0 ][ 'form_id' ];

				// Reverses the order of the array so that the results display oldest to
				// newest from left to right.
				$results = array_reverse( $results );

				if ( count( $results ) > 8 ) {
					$trim								= count( $results ) - 8;
					$results	= array_slice( $results, $trim );
				}

				$num_results = count( $results );

				?>

					<div class="graph-wrapper" style="display: grid; grid-template-columns: repeat( <?php echo $num_results; ?>, 1fr );">

						<?php

						$prev_col_year = null;

						foreach ( $results as $result ) {

							$this_col_year	= date_format( date_create( $result[ 'date' ] ), 'Y' );
							$year						= format_years( $this_col_year, $prev_col_year );
							$prev_col_year	= $this_col_year;

							$bar_args = array(
								'year'							=> $year,
								'result_top_label'	=> date_format( date_create( $result[ 'date' ] ), 'n/d' ),
								'value'							=> $result[ 'percentage' ],
								'title'							=> 'On ' . date_format( date_create( $result[ 'date' ] ), 'F j, Y' ) . ', you gave yourself ' . $result[ 'percentage' ] . '% on ' . $result[ 'version' ] . '.',
								'bottom_label'			=> $result[ 'grade' ],
								'bottom_sub_label'	=> $result[ 'percentage' ] . '%',
								'full_width'				=> true,
							);

							echo render_graph_col( $bar_args );

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

				<?php

			}

			?>

		</div>

		<?php

	return ob_get_clean();

}


/**
* Small Firm Dashboard: Financial Scorecard
*/
function financial_scorecard_graph() {

	$results = get_financial_scorecard_results();

	ob_start();

		?>

		<div id="dashboard-financial-scorecard-widget" class="dashboard-card card">
			<div class="card-label">Financial Scorecard</div>

			<?php

			if ( empty( $results ) ) {

				?>

				<p class="dashboard-widget-note">We don't have any financial metrics for your firm yet.</p>
				<p class="dashboard-widget-note">The Small Firm Financial Scorecard will help you discover what your firm is doing well and identify areas for improvement to help grow your law firm. It should take 10–15 minutes to complete.</p>
				<p align="center" class="remove_bottom"><a class="button remove_bottom" href="https://lawyerist.com/scorecard/">Get My Score</a></p>

				<?php

			} else {

				if ( count( $results ) > 8 ) {
					$trim			= count( $results ) - 8;
					$results	= array_slice( $results, $trim );
				}

				$num_results = count( $results );

				// Calculate max values for non-percentage values.
				$max_cash_on_hand		= 0;
				$max_unsecured_debt	= 0;
				$max_ar_over_30			= 0;
				$max_real_rate			= 0;

				foreach ( $results as $result ) {

					if ( $max_cash_on_hand < intval( $result[ 'cash_credit' ][ 'cash_on_hand' ] ) ) {
						$max_cash_on_hand = intval( $result[ 'cash_credit' ][ 'cash_on_hand' ] );
					}

					if ( $max_unsecured_debt < intval( $result[ 'cash_credit' ][ 'unsecured_debt' ] ) ) {
						$max_unsecured_debt = intval( $result[ 'cash_credit' ][ 'unsecured_debt' ] );
					}

					if ( $max_ar_over_30 < intval( $result[ 'receivables' ][ 'ar_over_30' ] ) ) {
						$max_ar_over_30 = intval( $result[ 'receivables' ][ 'ar_over_30' ] );
					}

					if ( $max_real_rate < intval( $result[ 'receivables' ][ 'real_rate' ] ) ) {
						$max_real_rate = intval( $result[ 'receivables' ][ 'real_rate' ] );
					}

				}

				$max = array(
					'cash_on_hand'		=> intval( $max_cash_on_hand ),
					'unsecured_debt'	=> intval( $max_unsecured_debt ),
					'ar_over_30'			=> intval( $max_ar_over_30 ),
					'real_rate'				=> intval( $max_real_rate ),
				);

				?>

				<div class="cards">

					<div class="card">
						<div class="graph-label">Profit %</div>
						<div class="graph-wrapper full-width-bars" style="display: grid; grid-template-columns: repeat( <?php echo $num_results; ?>, 1fr );">

							<?php

							$prev_col_year = null;

							foreach ( $results as $result ) {

								$revenue		= $result[ 'revenue' ][ 'fee_income' ] + $result[ 'revenue' ][ 'other_income' ];
								$expenses		= $result[ 'expenses' ][ 'owner_comp' ] + $result[ 'expenses' ][ 'salaries' ] + $result[ 'expenses' ][ 'operating' ];
								$profit			= $revenue - $expenses;

								$profit_percentage	= number_format( round( $profit / $revenue * 100 ) );

								$this_col_year	= date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'Y' );
								$year						= format_years( $this_col_year, $prev_col_year );
								$prev_col_year	= $this_col_year;

								$bar_args = array(
									'year'							=> $year,
									'result_top_label'	=> date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'n/d' ),
									'value'							=> $profit_percentage,
									'title'							=> 'On ' . date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'F j, Y' ) . ', your total revenue was $' . number_format( $revenue ) . ' and your expenses were $' . number_format( $expenses ) . ', for a profit of $' . number_format( $profit ) . '.',
									'bottom_sub_label'	=> $profit_percentage . '%',
								);

								echo render_graph_col( $bar_args );

							}

							?>

						</div>
					</div>

					<div class="card">
						<div class="graph-label">A/R Over 30</div>
						<div class="graph-wrapper full-width-bars value-type-total" style="display: grid; grid-template-columns: repeat( <?php echo $num_results; ?>, 1fr );">

							<?php

							$prev_col_year = null;

							foreach ( $results as $result ) {

								$this_col_year	= date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'Y' );
								$year						= format_years( $this_col_year, $prev_col_year );
								$prev_col_year	= $this_col_year;

								$bar_args = array(
									'year'							=> $year,
									'result_top_label'	=> date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'n/d' ),
									'value'							=> round( $result[ 'receivables' ][ 'ar_over_30' ] / $max[ 'ar_over_30' ] * 100 ),
									'bottom_sub_label'	=> '$' . number_format( $result[ 'receivables' ][ 'ar_over_30' ] ),
								);

								echo render_graph_col( $bar_args );

							}

							?>

						</div>
					</div>

					<div class="card">
						<div class="graph-label">Cash on Hand</div>
						<div class="graph-wrapper full-width-bars value-type-total" style="display: grid; grid-template-columns: repeat( <?php echo $num_results; ?>, 1fr );">

							<?php

							$prev_col_year = null;

							foreach ( $results as $result ) {

								$this_col_year	= date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'Y' );
								$year						= format_years( $this_col_year, $prev_col_year );
								$prev_col_year	= $this_col_year;

								$bar_args = array(
									'year'							=> $year,
									'result_top_label'	=> date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'n/d' ),
									'value'							=> round( $result[ 'cash_credit' ][ 'cash_on_hand' ] / $max[ 'cash_on_hand' ] * 100 ),
									'bottom_sub_label'	=> '$' . number_format( $result[ 'cash_credit' ][ 'cash_on_hand' ] ),
								);

								echo render_graph_col( $bar_args );

							}

							?>

						</div>
					</div>

					<div class="card">
						<div class="graph-label">Unsecured Debt</div>
						<div class="graph-wrapper full-width-bars value-type-total" style="display: grid; grid-template-columns: repeat( <?php echo $num_results; ?>, 1fr );">

							<?php

							$prev_col_year = null;

							foreach ( $results as $result ) {

								$this_col_year	= date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'Y' );
								$year						= format_years( $this_col_year, $prev_col_year );
								$prev_col_year	= $this_col_year;

								$bar_args = array(
									'year'							=> $year,
									'result_top_label'	=> date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'n/d' ),
									'value'							=> round( $result[ 'cash_credit' ][ 'unsecured_debt' ] / $max[ 'unsecured_debt' ] * 100 ),
									'bottom_sub_label'	=> '$' . number_format( $result[ 'cash_credit' ][ 'unsecured_debt' ] ),
								);

								echo render_graph_col( $bar_args );

							}

							?>

						</div>
					</div>

					<div class="card">
						<div class="graph-label">Labor %</div>
						<div class="graph-wrapper full-width-bars" style="display: grid; grid-template-columns: repeat( <?php echo $num_results; ?>, 1fr );">

							<?php

							$prev_col_year = null;

							foreach ( $results as $result ) {

								$revenue					= $result[ 'revenue' ][ 'fee_income' ] + $result[ 'revenue' ][ 'other_income' ];
								$labor_expenses 	= $result[ 'expenses' ][ 'owner_comp' ] + $result[ 'expenses' ][ 'salaries' ];
								$labor_percentage	= round( $labor_expenses / $revenue * 100 );

								$this_col_year	= date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'Y' );
								$year						= format_years( $this_col_year, $prev_col_year );
								$prev_col_year	= $this_col_year;

								$bar_args = array(
									'year'							=> $year,
									'result_top_label'	=> date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'n/d' ),
									'value'							=> $labor_percentage,
									'bottom_sub_label'	=> $labor_percentage . '%',
								);

								echo render_graph_col( $bar_args );

							}

							?>

						</div>
					</div>

					<div class="card">
						<div class="graph-label">Realization Rate</div>
						<div class="graph-wrapper full-width-bars" style="display: grid; grid-template-columns: repeat( <?php echo $num_results; ?>, 1fr );">

							<?php

							$prev_col_year = null;

							foreach ( $results as $result ) {

								$real_rate = round( $result[ 'receivables' ][ 'real_rate' ] );

								$this_col_year	= date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'Y' );
								$year						= format_years( $this_col_year, $prev_col_year );
								$prev_col_year	= $this_col_year;

								$bar_args = array(
									'year'							=> $year,
									'result_top_label'	=> date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'n/d' ),
									'value'							=> $real_rate,
									'bottom_sub_label'	=> $real_rate . '%',
								);

								echo render_graph_col( $bar_args );

							}

							?>

						</div>
					</div>

				</div>

				<?php

			}

			?>

		</div>

		<?php

	return ob_get_clean();

}
