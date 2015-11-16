<?php

namespace Tarosky\GoogleApiGlob;
use Hametuha\GapiWP\Loader;

/**
 * Command skeleton
 *
 * @package Tarosky\GoogleApiGlob
 * @property-read \Hametuha\GapiWP\Service\Analytics $ga
 */
abstract class AbstractCommand extends \WP_CLI_Command
{

	/**
	 * Command name required to be overridden
	 *
	 * @var string
	 */
	protected static $command = '';

	/**
	 * Register command.
	 *
	 * @throws \Exception Occurs when setting is wrong.
	 */
	public static function register() {
		// Is command set?
		if ( ! static::$command ) {
			throw new \Exception( sprintf( 'Command not set. Define %s::$command', get_called_class() ), 500 );
		}
		// Is source directory set?
		if ( ! defined( 'GAG_SRC_DIR' ) || ! is_dir( GAG_SRC_DIR ) ) {
			throw new \Exception( sprintf( 'You must define GAG_SRC_DIR as source directory.', get_called_class() ), 500 );
		}
		\WP_CLI::add_command( static::$command, get_called_class() );
	}

	/**
	 * Get list of installed json
	 *
	 * @return array
	 */
	protected function list_json() {
		$jsons = [];
		foreach ( scandir( GAG_SRC_DIR ) as $file ) {
			if ( preg_match( '#^[^\.].*\.json$#', $file ) ) {
				$jsons[] = $file;
			}
		}
		return $jsons;
	}

	/**
	 * Select JSON
	 *
	 * @param string $question Question
	 *
	 * @return array|\WP_Error JSON name.
	 */
	protected function select( $question = 'Which JSON?' ) {
		$jsons = $this->list_json();
		foreach ( $jsons as $index => $json ) {
			\WP_CLI::line( sprintf( "%d\t:%s", $index + 1, $json ) );
		}
		\WP_CLI::out( sprintf( '%s(1-%d): ', $question, count( $jsons ) ) );
		$handle     = fopen( 'php://stdin', 'r' );
		$json_index = false;
		while ( true ) {
			$index = trim( fgets( $handle ) );
			if ( ! is_numeric( $index ) || $index < 1 || $index > count( $jsons ) ) {
				\WP_CLI::warning('Oops, invalid input!');
				\WP_CLI::out( sprintf( 'Try again. %s(1-%d): ', $question, count( $jsons ) ) );
				continue;
			}
			fclose( $handle );
			$json_index = $index - 1;
			break;
		}
		$json   = json_decode( file_get_contents( GAG_SRC_DIR . DIRECTORY_SEPARATOR . $jsons[ $json_index ] ), true );
		$result = $this->test( $json );

		return is_wp_error( $result ) ? $result : $json;
	}

	/**
	 * Check if JSON is O.K.
	 *
	 * @param array $json JSON object.
	 *
	 * @return true|\WP_Error
	 */
	protected function test(  $json ) {
		$errors = new \WP_Error();
		if ( ! $json ) {
			$errors->add( 500, 'Failed to parse as JSON.' );
		}
		if ( ! isset( $json['from'] ) ) {
			$errors->add( 500, '"from" is not set.' );
		}
		if ( ! isset( $json['to'] ) ) {
			$errors->add( 500, '"to" is not set.' );
		}
		if ( ! isset( $json['metrics'] ) ) {
			$errors->add( 500, '"metrics" is not set.' );
		}
		if ( ! isset( $json['params']['dimensions'] ) ) {
			$errors->add( 500, '"dimensions" is not set.' );
		}

		return $errors->get_error_messages() ? $errors : true;
	}

	/**
	 * Getter
	 *
	 * @param string $name Key name.
	 *
	 * @return null
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'ga':
				return Loader::analytics();
				break;
			default:
				return null;
				break;
		}
	}
}
