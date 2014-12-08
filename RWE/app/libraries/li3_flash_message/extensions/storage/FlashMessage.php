<?php
/**
 * li3_flash_message plugin for Lithium: the most rad php framework.
 *
 * @copyright     Copyright 2010, Michael Hüneburg
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
 
namespace li3_flash_message\extensions\storage;

/**
 * Class for setting, getting and clearing flash messages. Use this class inside your
 * controllers to set messages for your views.
 *
 * {{{
 * // Controller
 * if (empty($data)) {
 *     FlashMessage::write('Invalid data.');
 * }
 *
 * // View
 * <?=$this->flashMessage->output(); ?>
 * }}}
 */
class FlashMessage extends \lithium\core\StaticObject {

	/**
	 * Class dependencies.
	 *
	 * @var array
	 */
	protected static $_classes = array(
		'session' => 'lithium\storage\Session'
	);
	
	/**
	 * Writes a flash message.
	 *
	 * @param string $message Message that will be stored.
	 * @param array [$atts] Optional attributes that will be available in the view.
	 * @param string [$key] Optional key to store multiple flash messages.
	 * @return boolean True on successful write, false otherwise.
	 */
	public static function write($message, array $atts = array(), $key = 'default') {
		$session = static::$_classes['session'];
		return $session::write("FlashMessage.{$key}", compact('message', 'atts'), array('name' => 'default'));
	}
	
	/**
	 * Reads a flash message.
	 *
	 * @param string [$key] Optional key.
	 * @return array The stored flash message.
	 */
	public static function read($key = 'default') {
		$session = static::$_classes['session'];
		return $session::read("FlashMessage.{$key}", array('name' => 'default'));
	}
	
	/**
	 * Clears one or all flash messages from the storage.
	 *
	 * @param string [$key] Optional key. Set this to `null` to delete all flash messages.
	 * @return void
	 */
	public static function clear($key = 'default') {
		$session = static::$_classes['session'];
		$sessionKey = 'FlashMessage';
		if (!empty($key)) {
			$sessionKey .= ".{$key}"; 
		}
		return $session::delete($sessionKey, array('name' => 'default'));
	}
	
}

?>