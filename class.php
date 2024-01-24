<?php
class ShopImport
{
    // metaArray という配列を作って最後にreturnする。
    public $metaArray = array();
    public $keyArray = array();

    public function __construct()
    {
        //CSVインポートでGoogleマップを表示する設定
        define("GOOGLE_MAP_KEY", "AIzaSyAS7u5tq4-95Pgrjt9lfxedL3kx_K5t_Tc");
        // カスタムフィールドキーの配列の作成
        $this->keyArray = $this->makeKey();
    }

    public function makeKey()
    {
        $key_array = array(
            'mon_check' => 'field_627df47e464f3', // 月曜定休日
            'tue_check' => 'field_627df47e464fe', // 火曜定休日
            'wed_check' => 'field_627df47e46509', // 水曜定休日
            'thu_check' => 'field_627e09d0ecc63', // 木曜定休日
            'fri_check' => 'field_627e09e01a63e', // 金曜定休日
            'sat_check' => 'field_627e0996659d9', // 土曜定休日
            'sun_check' => 'field_627df47e464e8', // 日曜定休日
            'credit_card_check' => 'field_62820710066ec', // クレジットカード 有無
            'credit_card_select' => 'field_627dcdfad6a5d', // クレジットカード チェックボックス
            'electronic_money_check' => 'field_628209dfb1195', // 電子マネー 有無
            'electronic_money_select' => 'field_627dcdfad6ac9', // 電子マネー チェックボックス
            'capacity_check' => 'field_627e155101a3b', // 人数・座席数 選択
            'capacity_people' => 'field_627e160a01a3c', // 人数
            'capacity_seat' => 'field_627e164301a3d', // 座席数
            'capacity_remarks' => 'field_627e166a01a3e', // 人数・座席数 備考
            'parking_check' => 'field_627e175d51c6a', // 駐車場 選択
            'parking_units' => 'field_627e181051c6c', // 駐車場 台数
            'smoking_check' => 'field_627e190c3c1e2', // 喫煙 選択
            'sns_instagram_check' => 'field_627e1b052fee5', // SNS instagram 有無
            'sns_instagram_account' => 'field_627e1b242fee6', // SNS instagram アカウント
            'sns_facebook_check' => 'field_627e1becbb8e6', // SNS facebook 有無
            'sns_facebook_account' => 'field_627e1becbb8e7', // SNS facebook アカウント
            'sns_twitter_check' => 'field_627e1c28bb8e9', // SNS twitter 有無
            'sns_twitter_account' => 'field_627e1c28bb8ea', // SNS twitter アカウント
            'sns_line_check' => 'field_627e1c43bb8ec', // SNS line 有無
            'sns_line_account' => 'field_627e1c43bb8ed', // SNS line アカウント
            'sns_youtube_check' => 'field_627e1c57bb8ef', // SNS youtube 有無
            'sns_youtube_account' => 'field_627e1c57bb8f0', // SNS youtube アカウント
            // google map用
            'location' => 'field_627dcdfad6e86',
            'pref' => 'field_627dcdfad6661',
            'city' => 'field_627dcdfad6697',
            'addr' => 'field_627dcdfad66cc',
            // // 画像用
            'img_import' => 'field_627dcdfad651c',
        );

        return $key_array;
    }

    public function foreach($meta, $key, $value, $post_id = null, $post_author = null)
    {

        $value = trim($value); // 値の前後の空白は削除する
        if (!empty($value)) { // 値があれば

            switch ($key) {

                case $this->keyArray['mon_check']:
                case $this->keyArray['tue_check']:
                case $this->keyArray['wed_check']:
                case $this->keyArray['thu_check']:
                case $this->keyArray['fri_check']:
                case $this->keyArray['sat_check']:
                case $this->keyArray['sun_check']:
                    if ($value == '休') {
                        $this->inport($key, 'holiday');
                    }
                    // クレジットカード
                case $this->keyArray['credit_card_select']:
                    $this->parentImport($key, preg_split("/,+/", $value));
                    $this->parentImport($this->keyArray['credit_card_check'], 'true');
                    break;
                    // 電子マネー
                case $this->keyArray['electronic_money_select']:
                    $this->parentImport($key, preg_split("/,+/", $value));
                    $this->parentImport($this->keyArray['electronic_money_check'], 'true');
                    break;
                    // 人数・座席数
                case $this->keyArray['capacity_people']: // 人数
                    $this->parentImport($key, $value);
                    $this->parentImport($this->keyArray['capacity_check'], 'people');
                    break;
                case $this->keyArray['capacity_seat']: // 座席数
                    $this->parentImport($key, $value);
                    $this->parentImport($this->keyArray['capacity_check'], 'seat');
                    break;
                    // 駐車場
                case $this->keyArray['parking_units']:
                    $this->parentImport($key, $value);
                    $this->parentImport($this->keyArray['parking_check'], 'true');
                    break;
                    // 喫煙
                case $this->keyArray['smoking_check']:
                    if ($value == '可') {
                        $this->parentImport($key, 'true');
                    } elseif ($value == '不可') {
                        $this->parentImport($key, 'false');
                    }
                    break;
                    // SNS
                case $this->keyArray['sns_instagram_account']: // SNS instagram
                    $this->parentImport($key, $value);
                    $this->parentImport($this->keyArray['sns_instagram_check'], 'true');
                    break;
                case $this->keyArray['sns_facebook_account']: // SNS facebook
                    $this->parentImport($key, $value);
                    $this->parentImport($this->keyArray['sns_facebook_check'], 'true');
                    break;
                case $this->keyArray['sns_twitter_account']: // SNS twitter
                    $this->parentImport($key, $value);
                    $this->parentImport($this->keyArray['sns_twitter_check'], 'true');
                    break;
                case $this->keyArray['sns_line_account']: // SNS line
                    $this->parentImport($key, $value);
                    $this->parentImport($this->keyArray['sns_line_check'], 'true');
                    break;
                case $this->keyArray['sns_youtube_account']: // SNS youtube
                    $this->parentImport($key, $value);
                    $this->parentImport($this->keyArray['sns_youtube_check'], 'true');
                    break;

                    // google map
                case $this->keyArray['city']:
                    $this->parentImport($this->keyArray['pref'], '沖縄県');
                    $this->parentImport($key, $value);
                    $shop_address = '沖縄県' . $value .  $meta[$this->keyArray['addr']]; // google map検索用の住所を作成(建物名は不要)

                    $url = sprintf( // Geocoding API を使い緯度経度データを取得
                        "https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s",
                        urlencode($shop_address),
                        GOOGLE_MAP_KEY
                    );

                    $context = stream_context_create([
                        'http' => ['ignore_errors' => true] //エラーで止まらないようにする
                    ]);
                    $response = file_get_contents($url, false, $context); // JSONデータを取得

                    $pos = strpos($http_response_header[0], '200');
                    if ($pos === false) {
                        //緯度経度が取得できなかった場合は登録しない;
                        break;
                    }

                    $json_data = json_decode($response, true); // JSONデータをphpデータにデコード

                    $lat = $json_data["results"][0]["geometry"]["location"]["lat"]; // 緯度を変数に
                    $lng = $json_data["results"][0]["geometry"]["location"]["lng"]; // 経度を変数に

                    $this->parentImport($this->keyArray['location'], [
                        "address" => $shop_address, // 住所を入れる
                        "lat" => $lat, // 緯度を入れる
                        "lng" => $lng // 経度を入れる
                    ]);
                    // echo '<pre>';
                    // var_dump($json_data);
                    // echo '</pre>';
                    break;

                    // 画像フィールドに対応

                case $this->keyArray['img_import']:
                    // 画像名から登録されているメディアのIDを取得
                    $img_ids = $this->get_attachment_id($value);

                    if (!empty($img_ids)) {
                        $this->parentImport($key, $img_ids[0]);
                        //画像を投稿に関連付けする
                        $this->update_attachment_post_parent($img_ids[0], $post_id, $post_author);
                    }
                    break;

                    // 基本のカスタムフィールド
                default:
                    $this->parentImport($key, $value);
            }
        }
    }

    public function parentImport($key, $value)
    {
        global $wpdb;
        $fieldArray = array();
        /*フィールドキーを取得して挿入する設定*/
        $prepared = $wpdb->prepare($this->sqlNormal(), esc_sql($key));
        $parentFieldId = $wpdb->get_col($prepared); // 親のカスタムフィールドのIDの取得
        if (!empty($parentFieldId)) {
            while (!empty($parentFieldId)) { // 親のカスタムフィールドのIDが存在すれば
                $pr = $wpdb->prepare($this->relatedSQL(), esc_sql($parentFieldId[0]));
                $parentField = $wpdb->get_col($pr); // カスタムフィールドのキーの取得
                if (!empty($parentField)) { // カスタムフィールドのキーが取得できれば
                    (empty($fieldArray)) ? $fieldArray = array($parentField[0] => array($key => $value)) : $fieldArray = array($parentField[0] => $fieldArray);
                    $prepared = $wpdb->prepare($this->sqlNormal(), esc_sql($parentField[0]));
                    $parentFieldId = $wpdb->get_col($prepared);
                } else {
                    if (empty($fieldArray)) $fieldArray = array($key => $value);
                    $parentFieldId = null;
                }
            }
            $this->metaArray = array_merge_recursive($this->metaArray, $fieldArray);
        } else {
            $this->metaArray[$key] = $value;
        }
    }

    /**
     * 親のカスタムフィールドのIDの取得
     */
    protected function sqlNormal()
    {
        global $wpdb;
        return "SELECT post_parent
        FROM $wpdb->posts
        WHERE post_type = 'acf-field' AND post_name = '%s' LIMIT 1";
    }

    /**
     * カスタムフィールドのキーの取得
     */
    protected function relatedSQL()
    {
        global $wpdb;
        return "SELECT post_name
        FROM $wpdb->posts
        WHERE ID = '%d' AND post_type = 'acf-field'  LIMIT 1";
    }

    /**
     * 画像を投稿に関連させ、投稿者を変更する関数
     */
    protected function update_attachment_post_parent($img_id, $post_id, $user_id)
    {
        global $wpdb;
        $post_related = $wpdb->prepare("UPDATE $wpdb->posts SET post_parent=%d, post_author=%d WHERE post_type='attachment' AND ID=%d", esc_sql($post_id), esc_sql($user_id), esc_sql($img_id));
        return $wpdb->query($post_related);
    }

    /**
     * アタッチする画像の画像名からIDを取得する関数
     */
    protected function get_attachment_id($v)
    {
        global $wpdb;
        $img_ids = array();

        //$replace_texts = array('.jpg','.png','.gif','.svg','.JPG','.PNG','.GIF','.SVG');
        //$img_title = str_replace($replace_texts,'',$v);
        $img_pattern = '/(.*)\.(jpg|png|gif|svg|JPG|PNG|GIF|SVG|webp|WEBP)/';
        $num_pattern = '(\d+)';

        if (preg_match($img_pattern, $v, $m)) {

            $img_title = $m[1];
            $imgGetSQL = "SELECT ID
            FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image%' AND post_title = '%s' LIMIT 1";

            $img_prepared = $wpdb->prepare($imgGetSQL, esc_sql($img_title));
            $img_ids = $wpdb->get_col($img_prepared);
        } elseif (preg_match($num_pattern, $v, $num_m)) {
            $img_ids[0] = $v;
        }

        return $img_ids;
    }
}

class TouristspotImport extends ShopImport
{
    public function __construct()
    {
        parent::__construct();
    }
    public function foreach($meta, $key, $value, $post_id = null, $post_author = null)
    {

        $value = trim($value); // 値の前後の空白は削除する
        if (!empty($value)) { // 値があれば
            if (false !== strpos($key, '_img')) {
                $key = str_replace('_img', '', $key);
                // 画像名から登録されているメディアのIDを取得
                $img_ids = $this->get_attachment_id($value);
                echo '<pre>';
                var_dump($img_ids);
                echo '</pre>';

                if (!empty($img_ids)) {
                    $this->parentImport($key, $img_ids[0]);
                    //画像を投稿に関連付けする
                    $this->update_attachment_post_parent($img_ids[0], $post_id, $post_author);
                }
            } else if ($key === $this->keyArray['address']) {
                // google map
                $this->parentImport($key, $value);

                $url = sprintf( // Geocoding API を使い緯度経度データを取得
                    "https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s",
                    urlencode($value),
                    GOOGLE_MAP_KEY
                );

                $context = stream_context_create([
                    'http' => ['ignore_errors' => true] //エラーで止まらないようにする
                ]);
                $response = file_get_contents($url, false, $context); // JSONデータを取得

                $pos = strpos($http_response_header[0], '200');
                if ($pos !== false) {
                    //緯度経度が取得できれば登録する;
                    $json_data = json_decode($response, true); // JSONデータをphpデータにデコード

                    $lat = $json_data["results"][0]["geometry"]["location"]["lat"]; // 緯度を変数に
                    $lng = $json_data["results"][0]["geometry"]["location"]["lng"]; // 経度を変数に

                    $this->parentImport($this->keyArray['location'], [
                        "address" => $value, // 住所を入れる
                        "lat" => $lat, // 緯度を入れる
                        "lng" => $lng // 経度を入れる
                    ]);
                }
                echo '<pre>';
                var_dump($json_data);
                echo '</pre>';
            } else {
                $this->parentImport($key, $value);
            }
        }
    }
    public function makeKey()
    {
        $key_array = array(
            // google map用
            'address' => 'field_621c7eb2c0355',
            'location' => 'field_621d84b51f938',
        );

        return $key_array;
    }
}
