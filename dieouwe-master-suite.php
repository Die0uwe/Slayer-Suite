<?php
/**
 * Plugin Name: Dieouwe Master Suite - Modular Core
 * Version: 25.12.30
 * Description: Volledige Suite met Blizzard, Twitch, Discord Test en Modulaire Editor. Alles strikt gescheiden.
 * Author: Slayer Alliance
 * Website: http://www.slayeralliance.com
 */

if (!defined('ABSPATH')) exit;

##### 1. DATABASE ENGINE #####
function sa_init_database() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset = $wpdb->get_charset_collate();

    $table_items = $wpdb->prefix . 'sa_housing_items';
    $sql_items = "CREATE TABLE $table_items (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        item_id varchar(50) NOT NULL,
        item_name varchar(255) NOT NULL,
        item_icon varchar(255) DEFAULT 'toy',
        item_icon_url varchar(255) DEFAULT NULL,
        found_by_count int(11) DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY item_id (item_id)
    ) $charset;";

    $table_chars = $wpdb->prefix . 'sa_housing_chars';
    $sql_chars = "CREATE TABLE $table_chars (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        char_name varchar(100) NOT NULL,
        realm varchar(100) NOT NULL,
        last_scan datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY char_realm (char_name, realm)
    ) $charset;";

    dbDelta($sql_items);
    dbDelta($sql_chars);

    $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_items' AND COLUMN_NAME = 'item_icon_url'");
    if(empty($row)) {
        $wpdb->query("ALTER TABLE $table_items ADD COLUMN item_icon_url varchar(255) DEFAULT NULL AFTER item_icon");
    }
}
add_action('admin_init', 'sa_init_database');

##### 2. API CORE & SETTINGS #####
function sa_get_core_settings() {
    return [
        'id'      => get_option('sa_blizz_id', ''),   // Stel in via Core Config dashboard
        'sec'     => get_option('sa_blizz_sec', ''),   // Stel in via Core Config dashboard
        'realm'   => get_option('sa_realm_name', 'sporeggar'),
        'guild'   => get_option('sa_guild_name', 'slayer-alliance'),
        'webhook' => get_option('sa_discord_webhook', ''),
        'tw_id'   => get_option('sa_twitch_id', ''),
        'tw_sec'  => get_option('sa_twitch_sec', '')
    ];
}

function sa_get_valid_token() {
    $token = get_transient('sa_blizz_token');
    if ($token) return $token;
    $core = sa_get_core_settings();
    $response = wp_remote_post("https://eu.battle.net/oauth/token", [
        'body'    => ['grant_type' => 'client_credentials'],
        'headers' => ['Authorization' => 'Basic ' . base64_encode($core['id'] . ":" . $core['sec'])]
    ]);
    if (is_wp_error($response)) return false;
    $data = json_decode(wp_remote_retrieve_body($response));
    if (isset($data->access_token)) {
        set_transient('sa_blizz_token', $data->access_token, HOUR_IN_SECONDS);
        return $data->access_token;
    }
    return false;
}

function sa_get_blizzard_data($endpoint) {
    $token = sa_get_valid_token();
    if (!$token) return false;
    $url = "https://eu.api.blizzard.com/" . ltrim($endpoint, '/');
    $response = wp_remote_get($url, [
        'headers' => ['Authorization' => 'Bearer ' . $token, 'Battlenet-Namespace' => 'profile-eu'],
        'timeout' => 20
    ]);
    if (is_wp_error($response)) return false;
    return json_decode(wp_remote_retrieve_body($response));
}

##### 3. MODULE LOADER ENGINE #####
$module_path = plugin_dir_path(__FILE__) . 'modules/';
if (!file_exists($module_path)) mkdir($module_path, 0755, true);

$active_modules = get_option('sa_active_modules', []);
foreach (glob($module_path . '*.php') as $file) { 
    $id = basename($file, '.php');
    if (isset($active_modules[$id]) && $active_modules[$id] === 'yes') {
        include_once $file; 
    }
}

##### 4. STYLING & EXPORT ENGINE #####
add_action('admin_init', function() {
    if (isset($_POST['sa_export_module']) && !empty($_POST['mod_code'])) {
        $filename = !empty($_POST['mod_filename']) ? $_POST['mod_filename'] : 'module-export.php';
        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'php') $filename .= '.php';
        header('Content-Type: application/x-httpd-php');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo stripslashes($_POST['mod_code']);
        exit;
    }
});

add_action('admin_head', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'sa-master') {
        echo '<style>
            .sa-master-container { width: 100%; margin-top: 20px; margin-left: -20px; padding-right: 20px; }
            .sa-main-wrap { padding: 30px; border-radius: 4px; border: 1px solid #ccd0d4; background: #fff; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
            .sa-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #a11692; padding-bottom: 20px; margin-bottom: 25px; }
            .sa-header h1 { margin:0; font-size: 26px; color: #2271b1; font-weight: 800; }
            .sa-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
            .sa-card { padding: 15px; border-radius: 4px; border: 1px solid #ccd0d4; background: #f0f6fb; border-left: 5px solid #ccd0d4; }
            .sa-card.green { border-left-color: #46b450; background: #f0fff0; }
            .sa-card.purple { border-left-color: #5865F2; background: #f5f6ff; }
            .sa-card.blue { border-left-color: #2271b1; background: #f0f6fb; }
            .sa-card.gray { border-left-color: #aaa; background: #f9f9f9; }
            .status-val { font-size: 16px; font-weight: bold; display: block; color: #111; margin-top: 5px; }
            .sa-btn { background: #a11692; color:#fff; border:none; padding:10px 18px; border-radius:4px; font-weight:bold; cursor:pointer; text-transform:uppercase; font-size:11px; }
            .sa-btn.green { background: #46b450; } .sa-btn.blue { background: #2271b1; } .sa-btn.red { background: #d63638; }
            .code-box { width: 100%; height: 600px; background: #1e1e1e; color: #d4d4d4; font-family: monospace; padding: 15px; border-radius: 4px; line-height:1.5; }
        </style>';
    }
});

add_action('admin_enqueue_scripts', function() {
    wp_enqueue_script('wowhead-power', 'https://wow.zamimg.com/widgets/power.js', [], null, true);
});

##### 5. MAIN ADMIN UI & HANDLERS #####
add_action('admin_menu', function() {
    add_menu_page('Alliance Suite', 'Alliance Suite', 'manage_options', 'sa-master', 'sa_admin_ui', 'dashicons-shield-alt', 2);
});

function sa_admin_ui() {
    $active_tab = $_GET['tab'] ?? 'core_config';
    $core = sa_get_core_settings();
    $active_modules = get_option('sa_active_modules', []);
    $module_path = plugin_dir_path(__FILE__) . 'modules/';
    $imported_code = '';

    // HANDLERS
    if (isset($_POST['sa_test_discord'])) {
        $webhook_url = sanitize_text_field($_POST['sa_webhook']);
        $test_response = wp_remote_post($webhook_url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode(['content' => "🛡️ **SA Master Suite**\nWebhook test succesvol! ✅", 'username' => 'SA Core'])
        ]);
        echo is_wp_error($test_response) ? '<div class="notice notice-error"><p>Fout: '.$test_response->get_error_message().'</p></div>' : '<div class="updated"><p>Testbericht verstuurd!</p></div>';
    }

    if (isset($_POST['sa_import_module']) && !empty($_FILES['import_file']['tmp_name'])) {
        $imported_code = file_get_contents($_FILES['import_file']['tmp_name']);
        echo '<div class="updated"><p>Bestand geladen in editor.</p></div>';
    }

    if (isset($_POST['sa_save_module'])) {
        check_admin_referer('sa_editor_action', 'sa_editor_nonce');
        $old_fn = sanitize_text_field($_POST['old_filename']);
        $new_fn = sanitize_text_field($_POST['mod_filename']);
        if (!empty($new_fn)) {
            if (pathinfo($new_fn, PATHINFO_EXTENSION) !== 'php') $new_fn .= '.php';
            if (!empty($old_fn) && $old_fn !== $new_fn && file_exists($module_path . $old_fn)) unlink($module_path . $old_fn);
            file_put_contents($module_path . $new_fn, stripslashes($_POST['mod_code']));
            echo '<div class="updated"><p>Module opgeslagen.</p></div>';
            $_POST['edit_file'] = $new_fn;
        }
    }

    if (isset($_POST['sa_delete_module'])) {
        check_admin_referer('sa_editor_action', 'sa_editor_nonce');
        $fn = sanitize_text_field($_POST['mod_filename']);
        if (!empty($fn) && file_exists($module_path . $fn)) {
            unlink($module_path . $fn);
            echo '<div class="notice notice-warning"><p>Module verwijderd.</p></div>';
            $_POST['edit_file'] = '';
        }
    }

    if (isset($_POST['sa_save_core'])) {
        update_option('sa_blizz_id', sanitize_text_field($_POST['blizz_id']));
        update_option('sa_blizz_sec', sanitize_text_field($_POST['blizz_sec']));
        update_option('sa_realm_name', sanitize_text_field($_POST['sa_realm_name']));
        update_option('sa_guild_name', sanitize_text_field($_POST['sa_guild_name']));
        update_option('sa_discord_webhook', sanitize_text_field($_POST['sa_webhook']));
        update_option('sa_twitch_id', sanitize_text_field($_POST['tw_id']));
        update_option('sa_twitch_sec', sanitize_text_field($_POST['tw_sec']));
        update_option('sa_active_modules', $_POST['mod'] ?? []);
        delete_transient('sa_blizz_token');
        echo '<div class="updated"><p>Core instellingen bijgewerkt.</p></div>';
        $active_modules = get_option('sa_active_modules', []);
    }

    echo '<div class="sa-master-container"><div class="sa-main-wrap">';
    echo '<div class="sa-header"><h1>SLAYER ALLIANCE <span style="color:#a11692;">MASTER SUITE</span></h1></div>';

    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=sa-master&tab=core_config" class="nav-tab '.($active_tab == 'core_config' ? 'nav-tab-active' : '').'">⚙️ CORE CONFIG</a>';
    foreach (glob($module_path . '*.php') as $file) {
        $id = basename($file, '.php');
        if (isset($active_modules[$id]) && $active_modules[$id] === 'yes') {
            echo '<a href="?page=sa-master&tab='.$id.'" class="nav-tab '.($active_tab == $id ? 'nav-tab-active' : '').'">'.strtoupper(str_replace('-', ' ', $id)).'</a>';
        }
    }
    echo '<a href="?page=sa-master&tab=builder" class="nav-tab '.($active_tab == 'builder' ? 'nav-tab-active' : '').'">🛠️ MODULE EDITOR</a>';
    echo '</h2>';

    if ($active_tab == 'core_config') sa_render_core_config($active_modules, $core);
    elseif ($active_tab == 'builder') sa_render_editor($module_path, $imported_code);
    else {
        $status_func = "sa_status_" . str_replace('-', '_', $active_tab);
        if (function_exists($status_func)) $status_func();
    }
    echo '</div></div>';
}

function sa_render_core_config($active_modules, $core) {
    $token = sa_get_valid_token();
    $discord_active = (strpos($core['webhook'], 'discord.com') !== false);
    $twitch_active = (!empty($core['tw_id']) && !empty($core['tw_sec']));

    echo '<div class="sa-grid">';
    echo '<div class="sa-card '.($token ? 'green' : 'blue').'"><h3>Blizzard API</h3><span class="status-val">'.($token ? '🟢 CONNECTED' : '⚪ STANDBY').'</span></div>';
    echo '<div class="sa-card purple"><h3>Discord Webhook</h3><span class="status-val">'.($discord_active ? '🟢 ACTIVE' : '⚪ NO URL').'</span></div>';
    echo '<div class="sa-card '.($twitch_active ? 'purple' : 'gray').'"><h3>Twitch Engine</h3><span class="status-val">'.($twitch_active ? '🟢 READY' : '⚪ OFFLINE').'</span></div>';
    echo '<div class="sa-card gray"><h3>System Load</h3><span class="status-val">'.count($active_modules).' MODS</span></div>';
    echo '</div>';
    ?>
    <form method="post">
        <div style="display: flex; gap: 40px;">
            <div style="flex: 2;">
                <h3 style="color:#a11692; border-bottom: 1px solid #eee; padding-bottom: 5px;">🛡️ API & SERVER SETTINGS</h3>
                <table class="form-table">
                    <tr><th>Blizzard Client ID</th><td><input type="text" name="blizz_id" value="<?php echo esc_attr($core['id']); ?>" class="regular-text"></td></tr>
                    <tr><th>Blizzard Secret</th><td><input type="text" name="blizz_sec" value="<?php echo esc_attr($core['sec']); ?>" class="regular-text"></td></tr>
                    <tr><th>Realm / Guild</th><td><input type="text" name="sa_realm_name" value="<?php echo esc_attr($core['realm']); ?>" style="width:120px;"> <input type="text" name="sa_guild_name" value="<?php echo esc_attr($core['guild']); ?>" style="width:120px;"></td></tr>
                    <tr>
                        <th>Discord Webhook</th>
                        <td>
                            <input type="text" name="sa_webhook" value="<?php echo esc_attr($core['webhook']); ?>" class="regular-text">
                            <input type="submit" name="sa_test_discord" class="button" value="🔔 TEST">
                        </td>
                    </tr>
                    <tr><th>Twitch ID / Sec</th><td><input type="text" name="tw_id" value="<?php echo esc_attr($core['tw_id']); ?>" style="width:48%;"> <input type="text" name="tw_sec" value="<?php echo esc_attr($core['tw_sec']); ?>" style="width:48%;"></td></tr>
                </table>
                <p><input type="submit" name="sa_save_core" class="sa-btn" value="INSTELLINGEN OPSLAAN"></p>
            </div>
            <div style="flex: 1;">
                <div style="background:#f9f9f9; padding:20px; border:1px solid #ccc; border-radius:4px;">
                    <h3 style="margin-top:0; color:#a11692;">🔌 MODULE BEHEER</h3>
                    <?php foreach (glob(plugin_dir_path(__FILE__) . 'modules/*.php') as $file): $id = basename($file, '.php'); ?>
                        <div style="display:flex; justify-content:space-between; margin-bottom:12px; border-bottom:1px solid #eee; padding-bottom:8px;">
                            <strong><?php echo strtoupper(str_replace('-', ' ', $id)); ?></strong>
                            <select name="mod[<?php echo $id; ?>]">
                                <option value="no">OFF</option>
                                <option value="yes" <?php selected($active_modules[$id] ?? '', 'yes'); ?>>ON</option>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </form>
    <?php
}

##### 6. GEÏSOLEERDE MODULE EDITOR #####
function sa_render_editor($path, $imported_code = '') {
    $selected_file = $_POST['edit_file'] ?? '';
    $current_code = $imported_code ?: ($selected_file && file_exists($path . $selected_file) ? file_get_contents($path . $selected_file) : '');
    ?>
    <div class="sa-card" style="background:#fff; border-left-color:#a11692;">
        <h3 style="color:#a11692;">🛠️ MODULE EDITOR</h3>
        <div style="background:#f0f0f0; border:1px solid #ccc; padding:15px; display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap;">
            <form method="post" style="display:flex; align-items:center; gap:8px;">
                <strong>Bestand:</strong>
                <select name="edit_file" onchange="this.form.submit()" style="min-width:140px;">
                    <option value="">-- Nieuw --</option>
                    <?php foreach (glob($path . '*.php') as $f): $fn = basename($f); ?><option value="<?php echo $fn; ?>" <?php selected($selected_file, $fn); ?>><?php echo strtoupper($fn); ?></option><?php endforeach; ?>
                </select>
            </form>
            <form method="post" enctype="multipart/form-data" style="display:flex; align-items:center; gap:8px; border-left:2px solid #ddd; padding-left:15px;">
                <strong>Laden:</strong>
                <input type="file" name="import_file" style="max-width:140px; font-size:10px;">
                <input type="submit" name="sa_import_module" class="button" value="📥 LADEN">
            </form>
            <form method="post" style="display:flex; align-items:center; gap:10px; border-left:2px solid #ddd; padding-left:15px;">
                <?php wp_nonce_field('sa_editor_action', 'sa_editor_nonce'); ?>
                <input type="hidden" name="old_filename" value="<?php echo esc_attr($selected_file); ?>">
                <input type="submit" name="sa_save_module" class="sa-btn green" value="💾 OPSLAAN">
                <input type="submit" name="sa_export_module" class="sa-btn blue" value="📤 EXPORT">
                <?php if ($selected_file): ?>
                    <input type="submit" name="sa_delete_module" class="sa-btn red" value="🗑️ WISSEN" onclick="return confirm('Definitief verwijderen?');">
                <?php endif; ?>
                <div style="margin-left:10px;">
                    <strong>Naam:</strong> 
                    <input type="text" name="mod_filename" value="<?php echo esc_attr($selected_file); ?>" style="width:140px;" placeholder="module.php">
                </div>
                <textarea name="mod_code" id="hidden_code" style="display:none;"><?php echo esc_textarea($current_code); ?></textarea>
            </form>
        </div>
        <textarea id="main_editor" class="code-box" oninput="document.querySelectorAll('#hidden_code').forEach(el => el.value = this.value)"><?php echo esc_textarea($current_code); ?></textarea>
    </div>
    <script>
        window.onload = function() {
            var editorValue = document.getElementById('main_editor').value;
            document.querySelectorAll('#hidden_code').forEach(el => el.value = editorValue);
        };
    </script>
    <?php
}

##### 7. GLOBAL SYNC ENGINE #####
add_action('wp_ajax_sa_sync_member_data', function() {
    global $wpdb;
    $name = sanitize_text_field($_POST['char']);
    $realm = sanitize_text_field($_POST['realm']);
    $table_items = $wpdb->prefix . 'sa_housing_items';
    $wpdb->replace($wpdb->prefix . 'sa_housing_chars', ['char_name' => $name, 'realm' => $realm, 'last_scan' => current_time('mysql')]);
    $endpoints = [
        'toy'   => "profile/wow/character/{$realm}/" . strtolower($name) . "/collections/toys",
        'mount' => "profile/wow/character/{$realm}/" . strtolower($name) . "/collections/mounts",
        'pet'   => "profile/wow/character/{$realm}/" . strtolower($name) . "/collections/pets",
        'decor' => "profile/wow/character/{$realm}/" . strtolower($name) . "/collections/decor"
    ];
    foreach ($endpoints as $type => $url) {
        $data = sa_get_blizzard_data($url);
        if (!$data) continue;
        $list = [];
        if ($type === 'toy') $list = $data->toys ?? [];
        elseif ($type === 'mount') $list = $data->mounts ?? [];
        elseif ($type === 'pet') $list = $data->pets ?? [];
        elseif ($type === 'decor') $list = $data->collected_decor ?? [];
        foreach ($list as $entry) {
            $item_id = 0; $item_name = ''; $media_path = '';
            if ($type === 'toy') { $item_id = $entry->toy->id; $item_name = $entry->toy->name; $media_path = "data/wow/media/item/{$item_id}"; }
            elseif ($type === 'mount') { $item_id = $entry->mount->id; $item_name = $entry->mount->name; $media_path = "data/wow/media/mount/{$item_id}"; }
            elseif ($type === 'pet') { $item_id = $entry->species->id; $item_name = $entry->species->name; $media_path = "data/wow/media/creature-display/" . ($entry->creature_display->id ?? 0); }
            elseif ($type === 'decor') { $item_id = $entry->decor->id; $item_name = $entry->decor->name; $media_path = "data/wow/media/item/{$item_id}"; }
            if ($item_id > 0) {
                $icon_url = $wpdb->get_var($wpdb->prepare("SELECT item_icon_url FROM $table_items WHERE item_id = %s", $item_id));
                if (empty($icon_url) && !empty($media_path)) {
                    $media_data = sa_get_blizzard_data($media_path . "?namespace=static-eu");
                    if ($media_data && isset($media_data->assets)) {
                        foreach($media_data->assets as $asset) {
                            if ($asset->key === 'icon' || $asset->key === 'zoom') { $icon_url = $asset->value; break; }
                        }
                    }
                }
                $wpdb->query($wpdb->prepare("INSERT INTO $table_items (item_id, item_name, item_icon, item_icon_url, found_by_count) VALUES (%s, %s, %s, %s, 1) ON DUPLICATE KEY UPDATE item_icon_url = VALUES(item_icon_url)", $item_id, $item_name, $type, $icon_url));
            }
        }
    }
    wp_send_json_success();
});