<?php
/*
Plugin Name: ACF Group CSV import customizer
Description: ACFのグループフィールドを登録するためのプラグインです。
Version: 1.1
Author: naganuma
*/

/**
 * @param $meta
 * @param $post
 * @param $is_update
 *
 * @return $meta_array
 */

require_once(__DIR__ . '/hook.php');
require_once(__DIR__ . '/class.php');