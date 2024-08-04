<?php
/*
  Plugin Name: Multisite disk usage
  Plugin URI:
  Description: indexページに表示可能なバナーの設定
  Version: 0.0.1
  Author: Y
  Author URI: https://github.com/
  License: MIT
 */

add_action('init', 'MultisiteDiskUsage::init');

/**
 * 
 */
class MultisiteDiskUsage
{
    static function init()
    {
        new MultisiteDiskUsage();
    }

    function __construct()
    {
        if (is_multisite() && current_user_can('manage_network')) {
            add_action('network_admin_menu', [$this, 'setup_menu']);
        }
    }

    function setup_menu()
    {
        add_submenu_page(
            'settings.php', // 親メニュー
            'Multisite disk usage',           // ページタイトル
            'Multisite disk usage',           // メニュータイトル
            'manage_network',         // 権限
            'multisite-disk-usage',    // ページを開いたときのURL
            [$this, 'show_page'],       // メニューに紐づく画面を描画するcallback関数
            'dashicons-analytics', // アイコン see: https://developer.wordpress.org/resource/dashicons/#awards
            99                      // 表示位置のオフセット
        );
    }

    function show_page()
    {
        readfile(__DIR__ . '/table-head.element.php');

        foreach ($this->get_sites() as $site) {
            $site_id = $site['site_id'];

            echo '<tr>';
            echo $this->td($site_id);
            echo $this->td($this->get_blog_name($site_id));
            echo $this->td($this->get_disk_usage($site_id));
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    function get_sites()
    {
        global $wpdb;

        $sites = $wpdb->get_results("SELECT * FROM {$wpdb->blogs}", ARRAY_A);

        return $sites;
    }

    function get_blog_name($site_id)
    {
        $blog_details = get_blog_details($site_id);
        return $blog_details->blogname;
    }

    function get_disk_usage($site_id)
    {
        $upload_dir = WP_CONTENT_DIR . '/uploads/sites/' . $site_id;
        return directory_size($upload_dir);
    }

    function td($string)
    {
        return '<td>', $string, '</td>';
    }
}
