<?php
	// Load configuration FIRST (correct path)
	require_once __DIR__ . '/config/config.php'; // No "../" needed

	// Then load core classes
	require_once __DIR__ . '/core/App.php';
	require_once __DIR__ . '/core/Controller.php';
	require_once __DIR__ . '/core/Database.php';
?>
