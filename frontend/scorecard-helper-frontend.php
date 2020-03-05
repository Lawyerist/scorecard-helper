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


function render_gauge() {

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

				$num_results	= count( $results );
				$current			= $results[ $num_results - 1 ];

				$green	= '#b1ffb1';
				$yellow	= '#ffffb1';
				$red		= '#ffb1b1';

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

				$ranges = array();

				if ( have_rows( 'profit_percentage_target', 'option' ) ) : while( have_rows( 'profit_percentage_target', 'option' ) ) : the_row();

						$ranges[ 'profit_percentage' ][ 'min' ] = get_sub_field( 'profit_percentage_min' );
						$ranges[ 'profit_percentage' ][ 'max' ] = get_sub_field( 'profit_percentage_max' );

				endwhile; endif;

				if ( have_rows( 'ar_target', 'option' ) ) : while( have_rows( 'ar_target', 'option' ) ) : the_row();

						$ranges[ 'ar_over_30' ][ 'min' ] = get_sub_field( 'ar_min' );
						$ranges[ 'ar_over_30' ][ 'max' ] = get_sub_field( 'ar_max' );

				endwhile; endif;

				if ( have_rows( 'cash_on_hand_target', 'option' ) ) : while( have_rows( 'cash_on_hand_target', 'option' ) ) : the_row();

						$ranges[ 'cash_on_hand' ][ 'min' ] = get_sub_field( 'cash_on_hand_min' );
						$ranges[ 'cash_on_hand' ][ 'max' ] = get_sub_field( 'cash_on_hand_max' );

				endwhile; endif;

				if ( have_rows( 'labor_percentage_target', 'option' ) ) : while( have_rows( 'labor_percentage_target', 'option' ) ) : the_row();

						$ranges[ 'labor_percentage' ][ 'min' ] = get_sub_field( 'labor_percentage_min' );
						$ranges[ 'labor_percentage' ][ 'max' ] = get_sub_field( 'labor_percentage_max' );

				endwhile; endif;

				if ( have_rows( 'real_rate_target', 'option' ) ) : while( have_rows( 'real_rate_target', 'option' ) ) : the_row();

						$ranges[ 'real_rate' ][ 'min' ] = get_sub_field( 'real_rate_min' );
						$ranges[ 'real_rate' ][ 'max' ] = get_sub_field( 'real_rate_max' );

				endwhile; endif;

				?>

				<div class="cards">

					<?php

					$revenue		= $current[ 'revenue' ][ 'fee_income' ] + $current[ 'revenue' ][ 'other_income' ];
					$expenses		= $current[ 'expenses' ][ 'owner_comp' ] + $current[ 'expenses' ][ 'salaries' ] + $current[ 'expenses' ][ 'operating' ];
					$profit			= $revenue - $expenses;

					$start		= new DateTime( $current[ 'reporting_period' ][ 'start_date' ] );
					$end			= new DateTime( $current[ 'reporting_period' ][ 'end_date' ] );
					$interval	= $start->diff( $end );

					$days						= $interval->days;
					$daily_expenses = $expenses / $days;


					$profit_percentage = round( $profit / $revenue * 100 );

					switch ( $profit_percentage ) {

						case ( $profit_percentage >= $ranges[ 'profit_percentage' ][ 'max' ] ) :
							$color = $green;
							break;

						case ( $profit_percentage < $ranges[ 'profit_percentage' ][ 'min' ] ) :
							$color = $red;
							break;

						default :
							$color = $yellow;

					}

					?>

					<div class="card" style="background-color: <?php echo $color; ?>">
						<div class="graph-wrapper full-width-bars" style="display: grid; grid-template-columns: 30% repeat( <?php echo $num_results; ?>, 1fr );">

							<div class="label-wrapper">
								<div class="graph-label">Profit %</div>
								<div class="graph-sub-label">Target: <?php echo $ranges[ 'profit_percentage' ][ 'min' ] . '–' . $ranges[ 'profit_percentage' ][ 'max' ]; ?>%</div>
							</div>

							<?php

							foreach ( $results as $result ) {

								$this_col_year	= date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'Y' );
								$year						= format_years( $this_col_year, $prev_col_year );
								$prev_col_year	= $this_col_year;

								$revenue		= $result[ 'revenue' ][ 'fee_income' ] + $result[ 'revenue' ][ 'other_income' ];
								$expenses		= $result[ 'expenses' ][ 'owner_comp' ] + $result[ 'expenses' ][ 'salaries' ] + $result[ 'expenses' ][ 'operating' ];
								$profit			= $revenue - $expenses;

								$profit_percentage = number_format( round( $profit / $revenue * 100 ) );

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

					<?php

					$ar_over_30 = round( $current[ 'receivables' ][ 'ar_over_30' ] );

					switch ( $ar_over_30 ) {

						case ( $ar_over_30 <= $ranges[ 'ar_over_30' ][ 'min' ] ) :
							$color = $green;
							break;

						case ( $ar_over_30 > $ranges[ 'ar_over_30' ][ 'max' ] ) :
							$color = $red;
							break;

						default :
							$color = $yellow;

					}

					?>

					<div class="card" style="background-color: <?php echo $color; ?>">
						<div class="graph-wrapper full-width-bars value-type-total" style="display: grid; grid-template-columns: 30% repeat( <?php echo $num_results; ?>, 1fr );">

							<div class="label-wrapper">
								<div class="graph-label">A/R Over 30</div>
								<div class="graph-sub-label">Target: $<?php echo number_format( $ranges[ 'ar_over_30' ][ 'min' ] ) . '&ndash;' . number_format( $ranges[ 'ar_over_30' ][ 'max' ] ); ?></div>
							</div>

							<?php

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

					<?php

					$cash_needed_min	= $daily_expenses * $ranges[ 'cash_on_hand' ][ 'min' ];
					$cash_needed_max	= $daily_expenses * $ranges[ 'cash_on_hand' ][ 'max' ];
					$cash_on_hand 		= round( $current[ 'cash_credit' ][ 'cash_on_hand' ] );

					switch ( $cash_on_hand ) {

						case ( $cash_on_hand >= $cash_needed_max ) :
							$color = $green;
							break;

						case ( $cash_on_hand < $cash_needed_min ) :
							$color = $red;
							break;

						default :
							$color = $yellow;

					}

					?>

					<div class="card" style="background-color: <?php echo $color; ?>">
						<div class="graph-wrapper full-width-bars value-type-total" style="display: grid; grid-template-columns: 30% repeat( <?php echo $num_results; ?>, 1fr );">

							<div class="label-wrapper">
								<div class="graph-label">Cash on Hand</div>
								<div class="graph-sub-label">Target: <?php echo number_format( $ranges[ 'cash_on_hand' ][ 'min' ] ) . '&ndash;' . number_format( $ranges[ 'cash_on_hand' ][ 'max' ] ); ?> days</div>
							</div>

							<?php

							foreach ( $results as $result ) {

								$this_col_year	= date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'Y' );
								$year						= format_years( $this_col_year, $prev_col_year );
								$prev_col_year	= $this_col_year;

								$revenue		= $result[ 'revenue' ][ 'fee_income' ] + $result[ 'revenue' ][ 'other_income' ];
								$expenses		= $result[ 'expenses' ][ 'owner_comp' ] + $result[ 'expenses' ][ 'salaries' ] + $result[ 'expenses' ][ 'operating' ];
								$profit			= $revenue - $expenses;

								$result_start		= new DateTime( $result[ 'reporting_period' ][ 'start_date' ] );
								$result_end			= new DateTime( $result[ 'reporting_period' ][ 'end_date' ] );
								$result_interval	= $start->diff( $end );

								$result_days						= $interval->days;
								$result_daily_expenses	= $expenses / $days;

								$result_cash_on_hand		= round( $result[ 'cash_credit' ][ 'cash_on_hand' ] );
								$result_days_on_hand		=	round( $result_cash_on_hand / $result_daily_expenses );

								$bar_args = array(
									'year'							=> $year,
									'result_top_label'	=> date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'n/d' ),
									'value'							=> round( $result[ 'cash_credit' ][ 'cash_on_hand' ] / $max[ 'cash_on_hand' ] * 100 ),
									'bottom_sub_label'	=> '$' . number_format( $result[ 'cash_credit' ][ 'cash_on_hand' ] ) . '<br />' . number_format( $result_days_on_hand ) . ' days',
								);

								echo render_graph_col( $bar_args );

							}

							?>

						</div>
					</div>

					<div class="card" style="background-color: <?php echo $color; ?>">
						<div class="graph-wrapper full-width-bars value-type-total" style="display: grid; grid-template-columns: 30% repeat( <?php echo $num_results; ?>, 1fr );">

							<div class="label-wrapper">
								<div class="graph-label">Unsecured Debt</div>
							</div>

							<?php

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

					<?php

					$revenue					= $current[ 'revenue' ][ 'fee_income' ] + $current[ 'revenue' ][ 'other_income' ];
					$labor_expenses 	= $current[ 'expenses' ][ 'owner_comp' ] + $current[ 'expenses' ][ 'salaries' ];
					$labor_percentage	= round( $labor_expenses / $revenue * 100 );

					switch ( $labor_percentage ) {

						case ( $labor_percentage < $ranges[ 'labor_percentage' ][ 'min' ] ) :
							$color = $green;
							break;

						case ( $labor_percentage >= $ranges[ 'labor_percentage' ][ 'max' ] ) :
							$color = $red;
							break;

						default :
							$color = $yellow;

					}

					?>

					<div class="card" style="background-color: <?php echo $color; ?>">
						<div class="graph-wrapper full-width-bars" style="display: grid; grid-template-columns: 30% repeat( <?php echo $num_results; ?>, 1fr );">

							<div class="label-wrapper">
								<div class="graph-label">Labor %</div>
								<div class="graph-sub-label">Target: <?php echo $ranges[ 'labor_percentage' ][ 'min' ] . '&ndash;' . $ranges[ 'labor_percentage' ][ 'max' ]; ?>%</div>
							</div>

							<?php

							$prev_col_year = null;

							foreach ( $results as $result ) {

								$this_col_year	= date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'Y' );
								$year						= format_years( $this_col_year, $prev_col_year );
								$prev_col_year	= $this_col_year;

								$revenue					= $result[ 'revenue' ][ 'fee_income' ] + $result[ 'revenue' ][ 'other_income' ];
								$labor_expenses 	= $result[ 'expenses' ][ 'owner_comp' ] + $result[ 'expenses' ][ 'salaries' ];
								$labor_percentage	= round( $labor_expenses / $revenue * 100 );

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

					<?php

					$real_rates_exist = false;

					foreach ( $results as $result ) {

						if ( !empty( $result[ 'receivables' ][ 'real_rate' ] ) ) {
							$real_rates_exist = true;
							continue;
						}

					}

					if ( $real_rates_exist ) {

						?>

						<div class="card" style="background-color: <?php echo $color; ?>">
							<div class="graph-wrapper full-width-bars" style="display: grid; grid-template-columns: 30% repeat( <?php echo $num_results; ?>, 1fr );">

								<div class="label-wrapper">
									<div class="graph-label">Realization Rate</div>
									<div class="graph-sub-label">Target: <?php echo $ranges[ 'real_rate' ][ 'min' ] . '&ndash;' . $ranges[ 'real_rate' ][ 'max' ]; ?>%</div>
								</div>

								<?php

								$prev_col_year = null;

								foreach ( $results as $result ) {

									$this_col_year	= date_format( date_create( $result[ 'reporting_period' ][ 'end_date' ] ), 'Y' );
									$year						= format_years( $this_col_year, $prev_col_year );
									$prev_col_year	= $this_col_year;

									$real_rate = round( $result[ 'receivables' ][ 'real_rate' ] );

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

						<?php

					}

					?>

				</div>

				<?php

			}

			?>

		</div>

		<?php

	return ob_get_clean();

}
