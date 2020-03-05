<?php

if ( !defined( 'ABSPATH' ) ) exit;


// Adds a page to the Users menu.
function scorecard_admin_reporting_menu() {
	add_submenu_page( 'users.php', 'Small Firm Scorecard Reports', 'Scorecard Reports', 'manage_options', 'scorecard', 'scorecard_admin_reporting' );
}

add_action( 'admin_menu', 'scorecard_admin_reporting_menu' );


/**
* Adds an options page.
*/
function scorecard_acf_op_init() {

  // Check function exists.
  if( function_exists( 'acf_add_options_sub_page' ) ) {

    acf_add_options_sub_page( array(
      'page_title'  => __( 'Small Firm Dashboard Settings' ),
      'menu_title'  => __( 'Small Firm Dashboard' ),
      'parent_slug' => __( 'options-general.php' ),
    ) );

  }

}

add_action( 'acf/init', 'scorecard_acf_op_init' );



// Get Active Labsters
function scorecard_get_active_labsters() {

	$args = array(
		'post_type'				=> 'wc_user_membership',
		'post_status'			=> 'wcm-active',
		'post_parent'			=> 223685,
		'posts_per_page'	=> -1,
	);

	$labster_query = new WP_Query( $args );

	if ( $labster_query->have_posts() ) :

		$labsters	= array();

		while ( $labster_query->have_posts() ) : $labster_query->the_post();

			array_push( $labsters, array(
				'id'						=> get_the_author_meta( 'ID' ),
				'email'					=> get_the_author_meta( 'user_email' ),
				'first_name'		=> get_the_author_meta( 'user_firstname' ),
				'last_name'			=> get_the_author_meta( 'user_lastname' ),
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


// Outputs the reports on the page.
function scorecard_admin_reporting() {

	?>

	<div class="wrap">

		<?php

		// Checks to see if an email address was provided in the URL.
		if ( isset( $_GET[ 'user_email' ] ) ) :

			$user_email	= $_GET[ 'user_email' ];
			$user_info	= get_user_by( 'email', $user_email );

			?>

			<h1 class="wp-heading-inline">Small Firm Scorecard Report: <?php echo $user_info->first_name . ' ' . $user_info->last_name; ?></h1>

			<table class="widefat">
				<thead>
					<tr>
						<th>Grade</th>
						<th>Date</th>
						<th>Version</th>
						<th>More</th>
					</tr>
				</thead>
				<tbody>

					<?php

					$results = get_scorecard_results( $user_info->ID );

					foreach ( $results as $result ) {

						$scorecard_id				= $result[ 'entry_id' ];
						$form_id						= $result[ 'form_id' ];
						$scorecard_grade		= $result[ 'grade' ];
						$scorecard_score		= $result[ 'percentage' ];
						$scorecard_date			= date_format( date_create( $result[ 'date' ] ), 'M. j, Y' );
						$scorecard_version	= $result[ 'version' ];

						?>

						<tr>
							<td><strong><?php echo $scorecard_grade; ?></strong> (<?php echo $scorecard_score; ?>%)</td>
							<td><?php echo $scorecard_date; ?></td>
							<td><?php echo $scorecard_version; ?></td>
							<td><a href="<?php menu_page_url( 'gf_entries' ); ?>&view=entry&id=<?php echo $form_id; ?>&lid=<?php echo $scorecard_id; ?>">See Scorecard</a></td>
						</tr>

						<?php

					}

					?>

				<tbody>
			</table>

			<?php

		else :

			?>

			<h1 class="wp-heading-inline">Small Firm Scorecard Report: Labsters</h1>

			<form action="<?php menu_page_url( 'scorecard' ); ?>" method="GET">
				<p class="search-box">
					<input type="hidden" value="scorecard" name="page">
					<label for="email" class="screen-reader-text">Search users:</label>
					<input id="email" type="email" placeholder="Enter email address" name="email">
					<input class="button" type="submit" value="Search Users">
				</p>
			</form>

			<table class="widefat">
				<thead>
					<tr>
						<th>Name</th>
						<th>Initial Grade</th>
						<th>Current Grade</th>
						<th>Last Updated</th>
						<th>More</th>
					</tr>
				</thead>
				<tbody>

					<?php

					$labsters						= scorecard_get_active_labsters();
					$results	= array();

					foreach ( $labsters as $labster ) {

						$results = get_scorecard_results( $labster[ 'id' ] );

						if ( !empty( $results ) ) {

							$first_scorecard_key		= array_key_last( $results );
							$first_scorecard_grade	= $results[ $first_scorecard_key ][ 'grade' ];
							$first_scorecard_score	= $results[ $first_scorecard_key ][ 'percentage' ];

							$last_scorecard_grade		= $results[ 0 ][ 'grade' ];
							$last_scorecard_score		= $results[ 0 ][ 'percentage' ];
							$last_scorecard_version	= $results[ 0 ][ 'version' ];
							$last_scorecard_date		= date_format( date_create( $results[ 0 ][ 'date' ] ), 'M. j, Y' );
							$total_scorecards				= count( $results );

							?>

							<tr>
								<td><?php echo $labster[ 'last_name' ] . ', ' . $labster[ 'first_name' ]; ?></td>
								<td>

									<?php

									if ( !$first_scorecard_key == 0 ) {
										echo $first_scorecard_grade . ' (' . $first_scorecard_score . '%)';
									} else {
										echo '';
									}

									?>

								</td>
								<td><strong><?php echo $last_scorecard_grade; ?></strong> (<?php echo $last_scorecard_score; ?>%)</td>
								<td><?php echo $last_scorecard_date; ?></td>
								<td><a href="<?php menu_page_url( 'scorecard' ); ?>&user_email=<?php echo $labster[ 'email' ]; ?>">See history</a> (<?php echo $total_scorecards; ?>)</td>
							</tr>

							<?php

						}

					}

					?>

				<tbody>
			</table>

			<?php

		endif;

		?>

	</div><!-- Close .wrap ?>

	<?php

}
