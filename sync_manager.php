<?php
/**
 * Module: Slayer Alliance Sync Manager V5.3
 * STATUS: Originele V4.7 Interface + Unieke Data Koppeling + Item Library Auto-Fill
 */

if (!defined('ABSPATH')) exit;

// --- 1. API TOKEN HELPER ---
function sa_get_quick_token() {
    $auth = base64_encode("3a3eb7ea215641989c08bd24503c8ed0:yYD5Ka6TwKmLRn8qLBHuv9Xief1kr0Gc");
    $res = wp_remote_post('https://eu.battle.net/oauth/token', [
        'headers' => ['Authorization' => 'Basic ' . $auth],
        'body'    => ['grant_type' => 'client_credentials']
    ]);
    if (is_wp_error($res)) return false;
    $body = json_decode(wp_remote_retrieve_body($res), true);
    return $body['access_token'] ?? false;
}

// --- 2. AJAX HANDLERS ---

// Roster Pull: Haalt alle gildeleden op en zet ze in de tabel
add_action('wp_ajax_sa_force_roster_update', function() {
    global $wpdb;
    $table = 'gvxx_sa_housing_chars';
    $token = sa_get_quick_token();
    $url = "https://eu.api.blizzard.com/data/wow/guild/sporeggar/slayer-alliance/roster?namespace=profile-eu";
    $res = wp_remote_get($url, ['headers' => ['Authorization' => 'Bearer ' . $token], 'timeout' => 25]);
    if (is_wp_error($res)) wp_send_json_error("Fout bij ophalen roster");
    
    $data = json_decode(wp_remote_retrieve_body($res), true);
    if (isset($data['members'])) {
        foreach ($data['members'] as $m) {
            $wpdb->query($wpdb->prepare(
                "INSERT IGNORE INTO $table (char_name, realm, last_scan) VALUES (%s, %s, '2000-01-01 00:00:00')", 
                $m['character']['name'], 
                $m['character']['realm']['slug']
            ));
        }
    }
    wp_send_json_success("Klaar");
});

// Perform Sync: De motor die per karakter alles ophaalt
add_action('wp_ajax_sa_perform_sync', function() {
    global $wpdb;
    $table_chars = 'gvxx_sa_housing_chars';
    $table_links = 'gvxx_sa_character_items'; // De koppeltabel
    $table_library = 'gvxx_sa_items';          // De tabel met namen voor de frontend
    
    $offset = intval($_POST['offset']);
    $mode = $_POST['mode'] ?? 'full';
    
    $member = $wpdb->get_row($wpdb->prepare("SELECT id, char_name, realm FROM $table_chars LIMIT 1 OFFSET %d", $offset));
    if (!$member) wp_send_json_error("Einde");

    $token = sa_get_quick_token();
    $n = strtolower(rawurlencode($member->char_name));
    $r = $member->realm; 
    $headers = ['Authorization' => 'Bearer ' . $token, 'Battlenet-Namespace' => 'profile-eu'];
    $updates = ['last_scan' => current_time('mysql')];
    $log = [];

    // Achievements Scan
    if ($mode == 'full' || $mode == 'achievements') {
        $res = wp_remote_get("https://eu.api.blizzard.com/profile/wow/character/$r/$n?namespace=profile-eu", ['headers' => $headers]);
        $d = json_decode(wp_remote_retrieve_body($res), true);
        if (isset($d['achievement_points'])) {
            $updates['rio_score'] = $d['achievement_points'];
            $log[] = "A: " . $d['achievement_points'];
        }
    }

    // Collections Scan (Mounts, Pets, Toys)
    $endpoints = ['mounts' => 'count_mounts', 'pets' => 'count_pets', 'toys' => 'count_toys'];
    if ($mode == 'full' || $mode == 'collections') {
        foreach ($endpoints as $key => $col_db_field) {
            $res = wp_remote_get("https://eu.api.blizzard.com/profile/wow/character/$r/$n/collections/$key?namespace=profile-eu", ['headers' => $headers]);
            $d = json_decode(wp_remote_retrieve_body($res), true);
            $items = $d[$key] ?? $d['collected_' . $key] ?? [];
            
            $updates[$col_db_field] = count($items);
            
            foreach ($items as $it) {
                $item_id = $it['mount']['id'] ?? $it['species']['id'] ?? $it['toy']['id'] ?? 0;
                $item_name = $it['mount']['name'] ?? $it['species']['name'] ?? $it['toy']['name'] ?? 'Onbekend';
                
                if ($item_id > 0) {
                    // 1. Sla de koppeling op (Wie heeft wat)
                    $wpdb->replace($table_links, [
                        'char_id' => $member->id, 
                        'item_id' => $item_id, 
                        'item_type' => strtoupper($key)
                    ]);
                    
                    // 2. Vul de bibliotheek (Namen voor de frontend)
                    $wpdb->query($wpdb->prepare(
                        "INSERT IGNORE INTO $table_library (id, name, item_type) VALUES (%d, %s, %s)",
                        $item_id, $item_name, strtoupper($key)
                    ));
                }
            }
            $log[] = strtoupper($key[0]) . ":" . count($items);
        }
    }

    $wpdb->update($table_chars, $updates, ['id' => $member->id]);
    wp_send_json_success(['name' => $member->char_name, 'details' => implode(' | ', $log)]);
});

// --- 3. INTERFACE (MET GROEN LOG) ---
function sa_status_sync_manager() {
    global $wpdb;
    $table_chars = 'gvxx_sa_housing_chars';
    $table_links = 'gvxx_sa_character_items';

    // Bereken unieke totalen voor de blokken bovenin
    $m_count = $wpdb->get_var("SELECT COUNT(DISTINCT item_id) FROM $table_links WHERE item_type = 'MOUNTS'");
    $p_count = $wpdb->get_var("SELECT COUNT(DISTINCT item_id) FROM $table_links WHERE item_type = 'PETS'");
    $t_count = $wpdb->get_var("SELECT COUNT(DISTINCT item_id) FROM $table_links WHERE item_type = 'TOYS'");
    
    // Achievement berekening (Hoogste per persoon opgeteld)
    $ach_total = $wpdb->get_var("SELECT SUM(max_points) FROM (SELECT MAX(rio_score) as max_points FROM $table_chars GROUP BY char_name) as t");
    
    $totals = $wpdb->get_row("SELECT COUNT(*) as c, SUM(count_decor) as d FROM $table_chars");

    ?>
    <div class="wrap">
        <h2 style="color:#a11692;">🔄 Slayer Alliance Sync Manager</h2>
        
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap:15px; margin-bottom:20px;">
            <div style="background:#fff; padding:15px; border:1px solid #ccd0d4; border-bottom:4px solid #00eeee; text-align:center;"><small>ACHIEVES</small><br><strong><?php echo number_format($ach_total ?: 0, 0, ',', '.'); ?></strong></div>
            <div style="background:#fff; padding:15px; border:1px solid #ccd0d4; border-bottom:4px solid #f1c40f; text-align:center;"><small>MOUNTS (U)</small><br><strong><?php echo number_format($m_count ?: 0, 0, ',', '.'); ?></strong></div>
            <div style="background:#fff; padding:15px; border:1px solid #ccd0d4; border-bottom:4px solid #2ecc71; text-align:center;"><small>PETS (U)</small><br><strong><?php echo number_format($p_count ?: 0, 0, ',', '.'); ?></strong></div>
            <div style="background:#fff; padding:15px; border:1px solid #ccd0d4; border-bottom:4px solid #e67e22; text-align:center;"><small>TOYS (U)</small><br><strong><?php echo number_format($t_count ?: 0, 0, ',', '.'); ?></strong></div>
            <div style="background:#fff; padding:15px; border:1px solid #ccd0d4; border-bottom:4px solid #a11692; text-align:center;"><small>DECOR</small><br><strong><?php echo number_format($totals->d ?: 0, 0, ',', '.'); ?></strong></div>
            <div style="background:#fff; padding:15px; border:1px solid #ccd0d4; border-bottom:4px solid #5865F2; text-align:center;"><small>MEMBERS</small><br><strong><?php echo $totals->c ?: 0; ?></strong></div>
        </div>

        <div style="background:#fff; padding:20px; border:1px solid #ccd0d4;">
            <div style="margin-bottom:20px;">
                <button id="force-update-btn" class="button">🔄 Roster Pull</button>
                <button class="sync-trigger button button-primary" data-mode="full" style="background:#a11692; border:none;">▶ Full Scan</button>
                <button class="sync-trigger button" data-mode="collections">🐾 Collections</button>
                <button class="sync-trigger button" data-mode="achievements">🏆 Achievs</button>
            </div>

            <div id="progress-container" style="width:100%; background:#eee; height:20px; display:none; border:1px solid #ccc; border-radius:10px; overflow:hidden;">
                <div id="progress-bar" style="width:0%; background:linear-gradient(90deg, #a11692, #5865F2); height:100%;"></div>
            </div>

            <div id="sync-log" style="margin-top:20px; background:#111; color:#0f0; padding:15px; font-family:monospace; height:400px; overflow-y:auto; display:none; border-radius:5px; font-size:12px; border:1px solid #333; line-height: 1.4;"></div>
        </div>
    </div>

    <script>
    jQuery(function($){
        // Roster Update
        $('#force-update-btn').on('click', function(){ 
            $(this).addClass('disabled').text('Bezig...');
            $.post(ajaxurl, { action: 'sa_force_roster_update' }, function() { location.reload(); }); 
        });

        // Sync Trigger
        $('.sync-trigger').on('click', function(){
            const mode = $(this).data('mode'); 
            let offset = 0; 
            const total = <?php echo (int)$totals->c; ?>;
            
            $('.sync-trigger, #force-update-btn').addClass('disabled');
            $('#sync-log').show().html('<span style="color:#fff;">[INITIALIZING MASTER SYNC V5.3...]</span><br>'); 
            $('#progress-container').show();

            function run() {
                if (offset >= total) { 
                    $('#sync-log').append('<br><span style="color:#fff;">[SYNC COMPLETE. REFRESH PAGE FOR TOTALS]</span>'); 
                    $('.sync-trigger, #force-update-btn').removeClass('disabled');
                    return; 
                }
                
                $.post(ajaxurl, { action: 'sa_perform_sync', offset: offset, mode: mode }, function(res) {
                    if (res.success) { 
                        $('#sync-log').append('<div><span style="color:#a11692;">></span> ' + res.data.name + ': ' + res.data.details + '</div>'); 
                    } else {
                        $('#sync-log').append('<div style="color:red;">> Error op offset ' + offset + '</div>');
                    }
                    
                    offset++; 
                    $('#progress-bar').css('width', (offset/total*100) + '%');
                    $('#sync-log').scrollTop($('#sync-log')[0].scrollHeight); 
                    run();
                });
            } 
            run();
        });
    });
    </script>
    <?php
}
add_action('admin_menu', function () { 
    add_menu_page('Alliance Sync', 'Alliance Sync', 'manage_options', 'sa-sync-manager', 'sa_status_sync_manager', 'dashicons-update', 25); 
});