<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

/* Trae Rol y Cédula; correo puede ser NULL */
$sql = "SELECT u.IdUsuario, u.Cedula, u.Nombre_de_Usuario, u.Correo, r.Descripcion AS Rol
        FROM Usuario u
        JOIN Rol r ON r.IdRol = u.IdRol
        ORDER BY u.IdUsuario DESC";

$res = $conexion->query($sql);
$data = [];
while ($r = $res->fetch_assoc()) $data[] = $r;

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
