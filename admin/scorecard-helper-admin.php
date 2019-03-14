<?php

// Set permissions!!!

class Scorecard_Report_Labsters extends WP_List_Table {

	function __construct() {

		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'labster',
			'plural'    => 'labsters',
			'ajax'      => TRUE,
		) );

	}

	function prepare_items() {

			$per_page = 20;

			/**
			 * REQUIRED. Now we need to define our column headers. This includes a complete
			 * array of columns to be displayed (slugs & titles), a list of columns
			 * to keep hidden, and a list of columns that are sortable. Each of these
			 * can be defined in another method (as we've done here) before being
			 * used to build the value for our _column_headers property.
			 */
			$columns	= array(
				'name'     			=> 'Name',
				'current_grade'	=> 'Current Grade',
				'last_updated'	=> 'Last Updated',
			);

			$hidden		= array();

			$sortable	= array(
				'name'					=> array( 'name', true ), // Already sorted.
				'current_grade'	=> array( 'current_grade', false ),
				'last_updated'	=> array( 'last_updated', false ),
			);

			$this->_column_headers = array( $columns, $hidden, $sortable );

			/**
			 * Instead of querying a database, we're going to fetch the example data
			 * property we created for use in this plugin. This makes this example
			 * package slightly different than one you might build on your own. In
			 * this example, we'll be using array manipulation to sort and paginate
			 * our data. In a real-world implementation, you will probably want to
			 * use sort and pagination data to build a custom query instead, as you'll
			 * be able to use your precisely-queried data immediately.
			 */
			$data = $this->example_data;


			/***********************************************************************
			 * ---------------------------------------------------------------------
			 * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
			 *
			 * In a real-world situation, this is where you would place your query.
			 *
			 * For information on making queries in WordPress, see this Codex entry:
			 * http://codex.wordpress.org/Class_Reference/wpdb
			 *
			 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
			 * ---------------------------------------------------------------------
			 **********************************************************************/


			/**
			 * REQUIRED for pagination. Let's figure out what page the user is currently
			 * looking at. We'll need this later, so you should always include it in
			 * your own package classes.
			 */
			$current_page = $this->get_pagenum();

			/**
			 * REQUIRED for pagination. Let's check how many items are in our data array.
			 * In real-world use, this would be the total number of items in your database,
			 * without filtering. We'll need this later, so you should always include it
			 * in your own package classes.
			 */
			$total_items = count($data);


			/**
			 * The WP_List_Table class does not handle pagination for us, so we need
			 * to ensure that the data is trimmed to only the current page. We can use
			 * array_slice() to
			 */
			$data = array_slice($data,(($current_page-1)*$per_page),$per_page);



			/**
			 * REQUIRED. Now we can add our *sorted* data to the items property, where
			 * it can be used by the rest of the class.
			 */
			$this->items = $data;


			/**
			 * REQUIRED. We also have to register our pagination options & calculations.
			 */
			$this->set_pagination_args( array(
					'total_items' => $total_items,                  //WE have to calculate the total number of items
					'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
					'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
			) );
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
						$last_scorecard_score		= $scorecard_results[ 0 ][ 'score' ];
						$last_scorecard_version	= $scorecard_results[ 0 ][ 'version' ];
						$last_scorecard_date		= date_format( date_create( $scorecard_results[ 0 ][ 'date' ] ), 'M. j, Y' );
						$total_scorecards				= count( $scorecard_results );

						echo '<tr>';
							echo '<td>' . $labster[ 'last_name' ] . ', ' . $labster[ 'first_name' ] . '</td>';
							echo '<td>' . $last_scorecard_grade . '</td>';
							echo '<td>' . $last_scorecard_date . '</td>';
							echo '<td>See history (' . $total_scorecards . ' ' . _n( 'scorecard', 'scorecards', $total_scorecards ) . ')</td>';
						echo '</tr>';

					}

				}

			echo '<tbody>';
		echo '</table>';

	echo '</div>'; // Close .wrap

}
