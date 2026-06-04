<?php
/**
 * Module: Suite Master Monitor (Clean Edition)
 * Versie: 25.3.12
 * Focus: Systeemstatus, API-verbindingen en Module beheer.
 */

if (!defined('ABSPATH')) exit;

function sa_status_sa_debug_tool() {
    global $wpdb, $wp_version;
    
    // Core & Module Check
    $core_exists = function_exists('sa_get_core_settings');
    $core = $core_exists ? sa_get_core_settings() : array();
    $active_modules = get_option('sa_active_modules', []);
    
    // Database Berekening (Grootte van sa_ tabellen)
    $db_size = $wpdb->get_row("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "' AND table_name LIKE '{$wpdb->prefix}sa_%'");

    echo '<div style="padding:20px; background:#f0f0f1; border:1px solid #ccd0d4; border-radius:5px; font-family: sans-serif; line-height: 1.4;">';
    echo '<h2 style="color:#d63638; margin-top:0; font-size: 20px;">🛡️ Alliance Suite Master Monitor</h2>';

    // --- 1. COMPACTE SYSTEEM & MODULE SECTIE ---
    echo '<div style="display: flex; gap: 15px; margin-bottom: 15px;">';
        
        // Systeem Info
        echo '<div style="flex: 2; background:#fff; padding:12px; border:1px solid #ccd0d4; border-radius:4px; border-top: 3px solid #d63638;">';
            echo '<h4 style="margin: 0 0 10px 0; font-size: 13px; color: #555;">📊 Server & DB Info</h4>';
            
            $mem_usage = round(memory_get_usage() / 1024 / 1024, 2);
            $mem_limit = ini_get('memory_limit');
            
            echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">';
                echo "<span><strong>PHP:</strong> " . PHP_VERSION . " <span style='color:#888;'>($mem_limit)</span></span>";
                echo "<span><strong>Memory:</strong> {$mem_usage}MB in gebruik</span>";
                echo "<span><strong>WP / Core:</strong> $wp_version / v25.3.9</span>";
                echo "<span><strong>WP Cron:</strong> " . (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? '❌ Uit' : '✅ Actief') . "</span>";
                echo "<span><strong>DB Size:</strong> " . round($db_size->size, 2) . "MB (sa_)</span>";
                echo "<span><strong>Realm:</strong> " . esc_html($core['realm'] ?? '---') . "</span>";
            echo '</div>';
        echo '</div>';

        // Actieve Modules (Chips)
        echo '<div style="flex: 1; background:#fff; padding:12px; border:1px solid #ccd0d4; border-radius:4px; border-top: 3px solid #46b450;">';
            echo '<h4 style="margin: 0 0 10px 0; font-size: 13px; color: #555;">🔌 Actieve Modules</h4>';
            echo '<div style="display: flex; flex-wrap: wrap; gap: 4px;">';
            $has_mods = false;
            if (!empty($active_modules)) {
                foreach ($active_modules as $mod_id => $status) {
                    if ($status === 'yes') {
                        echo '<span style="background:#46b450; color:#fff; padding:2px 6px; border-radius:3px; font-size:10px; font-weight:bold; text-transform:uppercase;">' . esc_html($mod_id) . '</span>';
                        $has_mods = true;
                    }
                }
            }
            if (!$has_mods) echo '<span style="font-size:11px; color:#999;">Geen mods actief</span>';
            echo '</div>';
        echo '</div>';

    echo '</div>';

    // --- 2. API STATUS ROW ---
    echo '<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px;">';
        $bnet_token = get_transient('sa_blizz_token');
        $discord_active = (!empty($core['webhook']) && strpos($core['webhook'], 'discord.com') !== false);

        $apis = [
            ['Blizzard', $bnet_token ? '✅' : '⌛', '#00c0ff'],
            ['Discord', $discord_active ? '✅' : '❌', '#7289da'],
            ['Twitch', !empty($core['tw_id']) ? '✅' : '⚪', '#6441a5'],
            ['Google', isset($core['google_id']) ? '✅' : '⚪', '#ea4335']
        ];

        foreach ($apis as $api) {
            echo '<div style="background:#fff; padding:10px; border:1px solid #ccd0d4; border-bottom: 3px solid '.$api[2].'; font-size:12px; text-align:center; border-radius:4px;">';
            echo '<strong style="display:block; margin-bottom:3px;">'.$api[0].'</strong>'.$api[1];
            echo '</div>';
        }
    echo '</div>';

    // --- 3. DATABASE TABEL INTEGRITEIT ---
    $sa_tables = $wpdb->get_col($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->prefix . 'sa_%'));
    echo '<div style="background:#fff; padding:12px; border:1px solid #ccd0d4; border-radius:4px;">';
    echo '<h4 style="margin:0 0 10px 0; font-size:13px; color: #555;">📊 Tabel Integriteit (sa_prefix)</h4>';
    echo '<table style="width:100%; font-size: 11px; border-collapse: collapse;">';
    echo '<tr style="background:#f9f9f9; text-align:left;"><th style="padding:5px;">Tabelnaam</th><th style="padding:5px; text-align:center;">Kolom: last_scan</th><th style="padding:5px; text-align:right;">Records</th></tr>';
    
    if ($sa_tables) {
        foreach ($sa_tables as $table) {
            $col_check = $wpdb->get_results("SHOW COLUMNS FROM `$table` LIKE 'last_scan'");
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            echo '<tr>';
            echo '<td style="padding:4px 5px; border-bottom:1px solid #eee;"><code>'.$table.'</code></td>';
            echo '<td style="padding:4px 5px; border-bottom:1px solid #eee; text-align:center;">'.(!empty($col_check) ? '✅' : '<span style="color:red;">❌ Mist</span>').'</td>';
            echo '<td style="padding:4px 5px; border-bottom:1px solid #eee; text-align:right;">'.$count.'</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3" style="padding:10px; text-align:center; color:#999;">Geen Suite tabellen gevonden.</td></tr>';
    }
    echo '</table></div>';

    echo '</div>'; 
}