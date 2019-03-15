<?php

// Set permissions!!!


/*if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}*/

class Labster_Scorecards_Report extends WP_List_Table {

	function get_columns() {

	  $columns = array(
	    'last_name'	=> 'Name',
			'email'			=> 'Email',
	  );

	  return $columns;
	}

	function sortable_columns() {

		$sortable_columns = array(
			'last_name'			=> array( 'last_name', true ),
			'email'					=> array( 'email', false ),
		)

		return $sortable_columns;

	}

	$labsters	= get_active_labsters();


	function prepare_items() {

	  $columns = $this->get_columns();
	  $hidden = array();
	  $sortable = $this->get_sortable_columns();
	  $this->_column_headers = array( $columns, $hidden, $sortable );
	  $this->items = $labsters;

	}


}





// Adds a page to the Users menu.
function scorecard_admin_reporting_menu() {
	add_submenu_page( 'users.php', 'Small Firm Scorecard Reports', 'Scorecard Reports', 'manage_options', 'scorecard', 'scorecard_admin_reporting' );
}

add_action( 'admin_menu', 'scorecard_admin_reporting_menu' );


// Outputs the reports on the page.
function scorecard_admin_reporting() {

	echo '<div class="wrap">';

		echo '<h1 class="wp-heading-inline">Small Firm Scorecard Report: Labsters</h1>';

		$myListTable = new Labster_Scorecards_Report();
		$myListTable->prepare_items();
		$myListTable->display();

		/********************************************************************************/
		/* REGULAR TABLE ****************************************************************/
		echo '<hr />';
		/********************************************************************************/

		echo '<table class="widefat">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Name</th>';
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

						$last_scorecard_grade		= $scorecard_results[ 0 ][ 'grade' ];
						$last_scorecard_score		= $scorecard_results[ 0 ][ 'percentage' ];
						$last_scorecard_version	= $scorecard_results[ 0 ][ 'version' ];
						$last_scorecard_date		= date_format( date_create( $scorecard_results[ 0 ][ 'date' ] ), 'M. j, Y' );
						$total_scorecards				= count( $scorecard_results );

						echo '<tr>';
							echo '<td>' . $labster[ 'last_name' ] . ', ' . $labster[ 'first_name' ] . '</td>';
							echo '<td><strong>' . $last_scorecard_grade . '</strong> (' . round( $last_scorecard_score ) . '%)</td>';
							echo '<td>' . $last_scorecard_date . '</td>';
							echo '<td>' . $total_scorecards . ' ' . _n( 'scorecard', 'scorecards', $total_scorecards ) . ' (see history)</td>';
						echo '</tr>';

					}

				}

			echo '<tbody>';
		echo '</table>';

	echo '</div>'; // Close .wrap

}
