<?php

function environment_config() {
	if (empty($GLOBALS['_APP_ENV_DATA'])) {
		if (PHP_SAPI == 'cli') {
			foreach ( $GLOBALS['argv'] as $arg ) {
				$arg = explode('=', $arg);

				if ($arg[0] == '--APP_DOMAIN') {
					$_SERVER["SERVER_NAME"] = $arg[1];
					continue;
				}

				if (strlen($arg[0]) > 7 && strtoupper(substr($arg[0], 0, 6)) == '--ARG_') {
					$_SERVER[substr($arg[0], 6)] = $arg[1];
				}
			}

			if (empty($_SERVER["SERVER_NAME"])) {
				$_SERVER["SERVER_NAME"] = 'dev.raytrend.oa.com';
			}

			$_SERVER["HTTP_HOST"] = $_SERVER["SERVER_NAME"];

			echo 'Product domain: ' . $_SERVER["SERVER_NAME"] . PHP_EOL;
		}

		$return_data = array (
				'environment' => 'production',
				'namespace' => 'app'
		);

		switch ($_SERVER["SERVER_NAME"]) {
			case 'www.raytrend.cn':
				$return_data['environment'] = 'production';
				break;

			case 'coffeesdk.sinaapp.com':
				$return_data['environment'] = 'sae';
				break;
					
			default:
				$return_data['environment'] = 'development';
				break;
		}

		$GLOBALS['_APP_ENV_DATA'] = $return_data;
	}

	return $GLOBALS['_APP_ENV_DATA'];
}
