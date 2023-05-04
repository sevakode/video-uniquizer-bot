<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$flipStatus = (bool) env('FLIP_STATUS');
