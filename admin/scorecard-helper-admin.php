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

	function column_cb( $item ){

		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
		);

	}

	function get_columns() {

		$columns = array(
			'cb'        		=> '<input type="checkbox" />', //Render a checkbox instead of text
			'name'     			=> 'Name',
			'current_grade'	=> 'Current Grade',
			'last_updated'	=> 'Last Updated',
		);

		return $columns;

	}

	function get_sortable_columns() {

		$sortable_columns = array(
			'name'					=> array( 'name', true ), // Already sorted.
			'current_grade'	=> array( 'current_grade', false ),
			'last_updated'	=> array( 'last_updated', false ),
		);

		return $sortable_columns;

	}

	function prepare_items() {

			global $wpdb; //This is used only if making any database queries

			/**
			 * First, lets decide how many records per page to show
			 */
			$per_page = 5;


			/**
			 * REQUIRED. Now we need to define our column headers. This includes a complete
			 * array of columns to be displayed (slugs & titles), a list of columns
			 * to keep hidden, and a list of columns that are sortable. Each of these
			 * can be defined in another method (as we've done here) before being
			 * used to build the value for our _column_headers property.
			 */
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();


			/**
			 * REQUIRED. Finally, we build an array to be used by the class for column
			 * headers. The $this->_column_headers property takes an array which contains
			 * 3 other arrays. One for all columns, one for hidden columns, and one
			 * for sortable columns.
			 */
			$this->_column_headers = array($columns, $hidden, $sortable);


			/**
			 * Optional. You can handle your bulk actions however you see fit. In this
			 * case, we'll handle them within our package just to keep things clean.
			 */
			$this->process_bulk_action();


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


			/**
			 * This checks for sorting input and sorts the data in our array accordingly.
			 *
			 * In a real-world situation involving a database, you would probably want
			 * to handle sorting by passing the 'orderby' and 'order' values directly
			 * to a custom query. The returned data will be pre-sorted, and this array
			 * sorting technique would be unnecessary.
			 */
			function usort_reorder($a,$b){
					$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
					$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
					$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
					return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
			}
			usort($data, 'usort_reorder');


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
