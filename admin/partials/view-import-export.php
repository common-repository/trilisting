<?php
/**
 * Import/export page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// export
$demo_export = new TRILISTING\Export\Trilisting_Demo_Export();
$demo_export->export();

// import
$demo_import = new TRILISTING\Import\Trilisting_Demo_Import();
$demo_import->_import();
