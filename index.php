<?php
//session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$db = new SQLite3('app.db');
$db->enableExceptions(true);

$path = array_values(
    array_filter(
        explode("/", $_SERVER['REQUEST_URI']),
        fn($e) => !empty($e)
    )
);

if (count($path) > 0 && $path[0] === "api") {
    $routes = [
        "notes" => fn() => require("notes.php"),
    ];

    if (count($path) > 1 && array_key_exists($path[1], $routes)) {
        $routes[$path[1]]();
        run_route($path, $db);
    }
    else {
        http_response_code(404);
        echo json_encode(["error" => "not found"]);
    }

    exit;
}

require("one_page.php");
?>
