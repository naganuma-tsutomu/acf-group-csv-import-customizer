<?php
/*
Plugin Name: ACF Group CSV import customizer
Description: ACFのグループフィールドを登録するためのプラグインです。
Version: 1.2.0
Author: naganuma
*/

/**
 * @param $meta
 * @param $post
 * @param $is_update
 *
 * @return $meta_array
 */

require_once(__DIR__ . '/src/hook.php');
require_once(__DIR__ . '/src/class.php');