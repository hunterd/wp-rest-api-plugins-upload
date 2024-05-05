// BEGIN: Test Cases for upload_install_plugin function

// Test case when no file is submitted
$request = new WP_REST_Request();
$result = upload_install_plugin($request);
assert($result instanceof WP_Error);
assert($result->get_error_code() === 'no_plugin_file');

// Test case when file is not a zip file
$request = new WP_REST_Request();
$request->set_file_params([
    'pluginfile' => [
        'name' => 'plugin.php',
        'tmp_name' => '/tmp/plugin.php',
    ]
]);
$result = upload_install_plugin($request);
assert($result instanceof WP_Error);
assert($result->get_error_code() === 'invalid_file_type');

// Test case when file upload fails
$request = new WP_REST_Request();
$request->set_file_params([
    'pluginfile' => [
        'name' => 'plugin.zip',
        'tmp_name' => '/tmp/plugin.zip',
    ]
]);
$mocked_wp_upload_bits = function ($name, $deprecated, $bits) {
    return [
        'error' => 'Upload failed',
    ];
};
add_filter('wp_upload_bits', $mocked_wp_upload_bits, 10, 3);
$result = upload_install_plugin($request);
remove_filter('wp_upload_bits', $mocked_wp_upload_bits, 10, 3);
assert($result instanceof WP_Error);
assert($result->get_error_code() === 'upload_error');

// Test case when file is successfully uploaded and unzipped
$request = new WP_REST_Request();
$request->set_file_params([
    'pluginfile' => [
        'name' => 'plugin.zip',
        'tmp_name' => '/tmp/plugin.zip',
    ]
]);
$mocked_unzip_file = function ($file, $to) {
    return true;
};
add_filter('unzip_file_ziparchive', $mocked_unzip_file, 10, 2);
$result = upload_install_plugin($request);
remove_filter('unzip_file_ziparchive', $mocked_unzip_file, 10, 2);
assert($result instanceof WP_REST_Response);
assert($result->get_status() === 200);
assert($result->get_data() === [
    'message' => 'Plugin uploaded successfully',
    'slug' => 'plugin',
]);

// END: Test Cases for upload_install_plugin function