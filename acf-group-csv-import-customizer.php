<?php
/*
Plugin Name: ACF Group CSV import customizer
Description: ACFのグループフィールドを登録するためのプラグインです。
Version: 1.3.1
Author: naganuma
*/

/**
 * @param $meta
 * @param $post
 * @param $is_update
 *
 * @return $meta_array
 */

require_once(__DIR__ . '/class.php');

// ---------------------------------------------------------------------------- 基本情報インポート
function really_simple_csv_importer_save_meta_filter($meta, $post, $is_update)
{
    global $wpdb;
    /**
     * $postにはCSVファイルでpost_idで指定した値として$post['import_id']に格納される
     * そのpost_idがすでに登録されている場合は、$post['ID']に格納される
     */
    if (isset($post['ID'])) {
        $post_id = $post['ID'];
    } elseif (isset($post['import_id'])) {
        $post_id = $post['import_id'];
    } else {
        $sqlMaxID = "SELECT ID
        FROM $wpdb->posts
        WHERE ID = (SELECT MAX(ID) FROM $wpdb->posts)";
        $max_id = $wpdb->get_col($sqlMaxID);
        $next_id = $max_id[0] + 1;
        $post_id = $next_id;
    }
    if (isset($post["post_author"])) {
        $post_author = $post["post_author"];
    } else {
        $post_author = 1;
    }

    if ($post["post_type"] === 'tourist-spot') {
        $import = new TouristspotImport();
    } else {
        $import = new ShopImport();
    }

    foreach ($meta as $key => $value) { // $keyはcsvのカラム名、$valueはcsvの値
        $import->foreach($meta, $key, $value, $post_id, $post_author);
    }

    $meta_array = $import->metaArray;

    // echo '<pre>';
    // var_dump($meta_array);
    // echo '</pre>';

    return $meta_array;
}
add_filter('really_simple_csv_importer_save_meta', 'really_simple_csv_importer_save_meta_filter', 10, 3);

// ---------------------------------------------------------------------------- エリアカテゴリ用
function really_simple_csv_importer_save_tax_filter($tax, $post, $is_update)
{

    if (!empty($tax['area_cat'][0])) {
        // JSONファイルのURLを指定
        $area_url = wp_upload_dir()['baseurl'] . '/okinawa47.json';
        $area_context = stream_context_create([
            'http' => ['ignore_errors' => true] //エラーで止まらないようにする
        ]);
        $area_response = file_get_contents($area_url, false, $area_context); // JSONデータを取得
        $area_pos = strpos($http_response_header[0], '200');
        if ($area_pos !== false) {
            $area_json_data = json_decode($area_response, true); // JSONデータをphpデータにデコード
            $key_index = array_search($tax['area_cat'][0], array_column($area_json_data, 'zip'));
            $result = $area_json_data[$key_index]['slug'];

            // Fix misspelled taxonomy
            if (isset($result)) {
                $tax['area_cat'][0] = $result;
            }
        }
    }
    // メインカテゴリが存在しないカテゴリの場合
    if (!empty($genre = $tax['main_genre_cat'][0])) {
        if (!empty($slug = $tax[$genre . '_style_cat'][0])) {
            $term = get_term_by('slug', $slug, $genre . '_style_cat');
            if (empty($term)) {
                echo '<span style="color: red;">' . $slug . ' は、インポートした投稿のカテゴリには存在しませんでした。</span><br>';
                $tax[$genre . '_style_cat'][0] = "";
            }
        }
    }
    return $tax;
}
add_filter('really_simple_csv_importer_save_tax', 'really_simple_csv_importer_save_tax_filter', 10, 3);

// ---------------------------------------------------------------------------- インポート時に親カテゴリもチェックする

function really_simple_csv_importer_cat_update($post)
{
    $post_id = $post->ID;
    $main_tax_slug = 'main_genre_cat'; // 対象とするタクソノミーのスラッグ
    $main_term = wp_get_post_terms($post_id, $main_tax_slug); // 登録されたタームの取得
    if (!empty($main_term)) {
        $taxonomy_slug = "{$main_term[0]->slug}_style_cat"; // 対象とするタクソノミーのスラッグ
        $terms = wp_get_post_terms($post_id, $taxonomy_slug); // 登録されたタームの取得
        if (!empty($terms)) {
            foreach ($terms as $term) { // 繰り返し
                while ($term->parent != 0 && !has_term($term->parent, $taxonomy_slug, $post)) { // 親があり、親のタームがセットされていないとき
                    wp_set_post_terms($post_id, array($term->parent), $taxonomy_slug, true); // 親のタームをセット
                    $term = get_term($term->parent, $taxonomy_slug); // 親のタームを変数へセット
                }
            }
        }
    }

    $area_tax_slug = 'area_cat'; // 対象とするタクソノミーのスラッグ
    $area_terms = wp_get_post_terms($post_id, $area_tax_slug); // 登録されたタームの取得
    if (!empty($area_terms)) {
        foreach ($area_terms as $term) { // 繰り返し
            while ($term->parent != 0 && !has_term($term->parent, $area_tax_slug, $post)) { // 親があり、親のタームがセットされていないとき
                wp_set_post_terms($post_id, array($term->parent), $area_tax_slug, true); // 親のタームをセット
                $term = get_term($term->parent, $area_tax_slug); // 親のタームを変数へセット
            }
        }
    }
}
add_action('really_simple_csv_importer_post_saved', 'really_simple_csv_importer_cat_update', 10, 2);
