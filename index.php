<?php

	declare(strict_types=1);

	/**
	 * Remove unnecessary server signature header
	 */
	header_remove('X-Powered-By');

	require_once __DIR__ . 'app.php';

	use Core\App;

	/**
	 * Initialize application
	 */

	$app = new App();

	$app->run();
?>