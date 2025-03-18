<?php
use Cbx\Petition\Helpers\PetitionHelper;
use Cbx\Petition\Hooks;

/**
 * Petition plugin main class file
 *
 * Class CBXPetition
 * @package Cbx\Petition
 */
final class CBXPetition {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  2.0.0
	 */
	private static $instance = null;

	/**
	 * @var Hooks
	 * @since 1.0.0
	 */
	public $hooks;

	/**
	 * Singleton Instance.
	 *
	 * Ensures only one instance of CBXPetition is loaded or can be loaded.
	 *
	 * @return self Main instance.
	 * @since  2.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}//end method instance

	public function __construct() {
		$this->include_files();

		$this->hooks = new Hooks();
	}//end method constructor

	/**
	 * Autoload inaccessible or non-existing properties on demand.
	 *
	 * @param $key
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function __get( $key ) {
		if ( in_array( $key, [ 'mailer' ], true ) ) {
			return $this->$key();
		}
	}//end magic method get

	/**
	 * Set the value of an inaccessible or non-existing property.
	 *
	 * @param string $key Property name.
	 * @param mixed $value Property value.
	 * @since 2.0.0
	 */
	public function __set( string $key, $value ) {
		if ( property_exists( $this, $key ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( 'Cannot access private property CBXPetition::$' . esc_html( $key ), E_USER_ERROR );
		} else {
			$this->$key = $value;
		}
	}//end magic mathod set

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __clone() {
		cbxpetition_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'cbxpetition' ), '2.0.0' );
	}//end method clone

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __wakeup() {
		cbxpetition_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'cbxpetition' ), '2.0.0' );
	}//end method wakeup


	/**
	 * Include necessary files
	 *
	 * @return void
	 */
	private function include_files() {
		require_once __DIR__ . '/../lib/autoload.php';
		include_once __DIR__ . '/CBXPetitionEmails.php';
	}//end method include_files

	/**
	 * Email Class.
	 *
	 * @return CBXPetitionEmails
	 * @since 2.0.0
	 */
	public function mailer() {
		return CBXPetitionEmails::instance();
	}//end method mailer
}//end class CBXPetition