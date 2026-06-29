<?php
// If the request is for the root, serve index.html
if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '') {
    readfile('index.html');
    exit;
}

// For API requests, route to backend
if (strpos($_SERVER['REQUEST_URI'], '/backend/api/') === 0) {
    // Let the API handle it
    require_once '.' . $_SERVER['REQUEST_URI'];
    exit;
}

// For all other files, try to serve them
$file = '.' . $_SERVER['REQUEST_URI'];
if (file_exists($file) && !is_dir($file)) {
    // Serve the file with correct content type
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $content_types = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'json' => 'application/json'
    ];
    
    if (isset($content_types[$ext])) {
        header('Content-Type: ' . $content_types[$ext]);
    }
    readfile($file);
    exit;
}

// If nothing matches, show 404
http_response_code(404);
echo "404 - File not found";
?>