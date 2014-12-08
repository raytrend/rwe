# Flash Message Plugin for Lithium

The Flash Message (li3_flash_message) plugin provides a straightforward interface for displaying status messages to the user.


## Setup

Add the plugin to `app/config/bootstrap/libraries.php`.

	Libraries::add('li3_flash_message');

Make sure you have set up a `default` session configuration in `app/config/bootstrap/session.php`.

## Basic Usage

	use li3_flash_message\extensions\storage\FlashMessage;
	
	// ... some form validation logic ...
	FlashMessage::write('Error, please check your inputs!');

Use the helper to output messages inside your views.

	<?=$this->flashMessage->output(); ?>

In order to customize the output, copy `app/libraries/li3_flash_message/views/elements/flash_message.html.php` to `app/views/elements/flash_message.html.php` and adjust it to your needs.

## Advanced Usage

For now please refer to the documented source or the test cases.