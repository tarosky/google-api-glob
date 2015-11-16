<?php

namespace Tarosky\GoogleApiGlob\Commands;


use cli\Table;
use Tarosky\GoogleApiGlob\AbstractCommand;

/**
 * Glob all data of analytics and package them to single csv
 *
 * @package Tarosky\GoogleApiGlob\Commands
 */
class Glob extends AbstractCommand {

	protected static $command = 'glob-ga';


	/**
	 * Show installed JSON
	 *
	 * This commands list up all JSONs and if they are valid.
	 *
	 * @subcommand list
	 */
	public function _list() {
		$jsons = $this->list_json();
		if ( empty( $jsons ) ) {
			\WP_CLI::error( sprintf( 'No JSON is installed at %s', GAG_SRC_DIR ) );
		} else {
			$header = [ 'File name', 'from', 'to', 'valid' ];
			$rows   = [];
			foreach ( $jsons as $json ) {
				$obj = json_decode( file_get_contents( GAG_SRC_DIR . DIRECTORY_SEPARATOR . $json ), true );
				$row = [ $json ];
				if ( is_wp_error( $this->test( $obj ) ) ) {
					$row = array_merge( $row, [ '-', '-', '×' ] );
				} else {
					$row = array_merge( $row, [ $obj['from'], $obj['to'], '✔︎' ] );
				}
				$rows[] = $row;
			}
			$table = new Table();
			$table->setHeaders( $header );
			$table->setRows( $rows );
			$table->display();
		}
	}

	/**
	 * Show detail of installed JSON
	 *
	 * Display detailed JSON
	 *
	 * # EXAMPLE
	 * wp glob-ga detail
	 */
	public function detail() {
		$result   = $this->select();
		if ( is_wp_error( $result ) ) {
			foreach ( $result->get_error_messages() as $message ) {
				\WP_CLI::warning( $message );
			}
			\WP_CLI::error( 'Specified JSON is broken.' );
		} else {
			print_r( $result );
			\WP_CLI::success( 'Specified is valid.' );
		}
	}

	/**
	 * Execute JSON setting and dump it to csv
	 *
	 * @param array $args
	 * @param array $assoc_args
	 * @synopsis <destination_file> [--header] [--from=<from>] [--to=<to>] [--max-results=<max-results>]
	 */
	public function dump( $args, $assoc_args ) {
		$json = $this->select();
		if ( is_wp_error( $json ) ) {
			\WP_CLI::error( 'JSON is broken. Please check it with detail command.' );
		}
		// Override default.
		foreach ( [ 'from' => true, 'to' => true, 'max-results' => false ] as $key => $parent ) {
			if ( isset( $assoc_args[ $key ] ) ) {
				if ( $parent ) {
					$json[ $key ] = $assoc_args[ $key ];
				} else {
					$json['params'][ $key ] = $assoc_args[ $key ];
				}
			}
		}
		// Build CSV.
		list( $path ) = $args;
		$handle = fopen( $path, 'w' );
		if ( isset( $assoc_args['header'] ) && $assoc_args ) {
			fputcsv( $handle, $this->grab_header( $json ) );
		}
		if ( ! isset( $json['params']['max-results'] ) ) {
			$json['params']['max-results'] = 1000;
		}
		echo 'Start fetching data...';
		$per_page  = $json['params']['max-results'];
		$retrieved = 0;
		while ( true ) {
			$json['params']['start-index'] = $retrieved + 1;
			$result = $this->ga->fetch( $json['from'], $json['to'], $json['metrics'], $json['params'] );
			if ( is_wp_error( $result ) ) {
				\WP_CLI::error( sprintf( 'ABBORTED! Failed to fetch data: %s', $result->get_error_message() ) );
			}
			$rows = $result['rows'];
			if ( empty( $rows ) ) {
				break;
			}
			// Put to CSV.
			foreach ( $rows as $row ) {
				fputcsv( $handle, $row );
			}
			// Check this is finished.
			$total = count( $rows );
			$retrieved += $total;
			echo '.';
			if ( $total < $per_page ) {
				break;
			}
		}
		fclose( $handle );
		\WP_CLI::success( sprintf( 'Finish %d records.', $retrieved ) );
	}

	/**
	 * Try to fetch data
	 *
	 * You can override setting value with options.
	 *
	 * # EXAMPLE
	 *
	 * wp glob-ga fetch --from=20151201 --to=20151231 --max-results=20
	 *
	 * @synopsis [--from=<from>] [--to=<to>] [--max-results=<max-results>]
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function fetch( $args, $assoc_args ) {
		$json = $this->select();
		if ( is_wp_error( $json ) ) {
			\WP_CLI::error( 'This JSON is invalid.' );
		}
		foreach ( [ 'from' => true, 'to' => true, 'max-results' => false ] as $key => $parent ) {
			if ( isset( $assoc_args[ $key ] ) ) {
				if ( $parent ) {
					$json[ $key ] = $assoc_args[ $key ];
				} else {
					$json['params'][ $key ] = $assoc_args[ $key ];
				}
			}
		}
		\WP_CLI::line( 'Setting: ' );
		print_r( $json );
		\WP_CLI::line( 'Fetching...' );
		$result = $this->ga->fetch( $json['from'], $json['to'], $json['metrics'], $json['params'] );
		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( sprintf( 'Invalid response: %s', $result->get_error_message() ) );
		}
		print_r( $result['rows'] );
		\WP_CLI::success( 'Done!' );
	}

	/**
	 * Build header from JSON parameters
	 *
	 * @param array $json JSON array
	 *
	 * @return array
	 */
	protected function grab_header( $json ) {
		$headers = [];
		foreach ( preg_split( '/[;,]/', $json['params']['dimensions'] ) as $dimension ) {
			$headers[] = $dimension;
		}
		foreach ( explode( ',', $json['metrics'] ) as $metric ) {
			$headers[] = $metric;
		}

		return $headers;
	}
}
