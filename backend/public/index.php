<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api#', '', $path);

if ($path === '' || $path === '/') {
  http_response_code(404);
  echo json_encode(["error" => "No endpoint"]);
  exit;
}

$target = __DIR__ . $path;

if (is_file($target) && pathinfo($target, PATHINFO_EXTENSION) === 'php') {
  require $target;
  exit;
}

http_response_code(404);
echo json_encode(["error"=>"Endpoint no encontrado", "path"=>$path]);
