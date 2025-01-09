<?php
session_start();

function checkAdmin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header('Location: ../dangnhap.php');
        exit();
    }
} 