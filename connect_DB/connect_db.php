<?php
if (!function_exists('connectData')) {
    function connectData()
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "fshop";

        $conn = new mysqli($servername, $username, $password, $database,3306);

        if ($conn->connect_error) {
            die("❌ Kết nối thất bại: " . $conn->connect_error);
        }

        return $conn;
    }
}
