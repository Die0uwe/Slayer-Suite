<?php
/**
 * Module: Slayer Alliance Realm Monitor (V3.7)
 * Frontend: Onveranderde visuele bars.
 * Backend: Status info voor admin module.
 */

if (!defined('ABSPATH')) exit;

/* ==========================================================================
   1. DATA LOGICA
   ========================================================================= */
function sa_get_full_cluster_data() {
    $core = sa_get_core_settings();
    if (!$core || !isset($core['realm'])) return [];

    $realm_slug = strtolower(str_replace(' ', '-', $core['realm']));
    $endpoint = "data/wow/search/connected-realm?namespace=dynamic-eu&realms.name.en_GB=" . ucfirst($realm_slug);
    $data = sa_get_blizzard_data($endpoint);

    $cluster = [];
    if ($data && isset($data->results[0])) {
        $res = $data->results[0]->data;
        $status_up = ($res->status->type === 'UP');
        
        foreach($res->realms as $r) {
            $cluster[] = [
                'name'   => $r->name->en_GB,
                'online' => $status_up,
                'type'   => $r->type->name->en_GB ?? 'RPPvP',
                'pop'    => $res->population->name->en_GB ?? 'Unknown'
            ];
        }

        // Sla status op voor de admin-module zonder de frontend te veranderen
        $core['last_status'] = $status_up ? 'Online' : 'Offline';
        $core['last_updated'] = time();
        update_option('sa_core_settings', $core);
    }
    return $cluster;
}

/* ==========================================================================
   2. FRONTEND SHORTCODE (Output exact zoals je wilde)
   ========================================================================= */
add_shortcode('sa_realm_status', function() {
    $cluster = sa_get_full_cluster_data();
    if (empty($cluster)) return "<div style='color:#666;'>Geen data.</div>";

    ob_start(); ?>
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap" rel="stylesheet">
    <div id="sa-cluster-module" style="all: initial; display: block; max-width: 1300px; margin: 40px auto; font-family: 'MedievalSharp', cursive;">
        <h2 style="text-align: center; color: #a11692; font-size: 32px; text-transform: uppercase; letter-spacing: 4px; margin-bottom: 40px; font-family: 'MedievalSharp', cursive;">— Connected Realm Cluster Status —</h2>
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($cluster as $realm): 
                $status_color = $realm['online'] ? '#00ff00' : '#ff0000';
            ?>
                <div style="background: linear-gradient(90deg, #151520 0%, #0a0a0f 100%); border: 2px solid #a11692; border-left: 8px solid <?php echo $status_color; ?>; border-radius: 12px; padding: 20px 40px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
                    <div style="display: flex; align-items: center; gap: 30px; flex: 2;">
                        <div style="width: 12px; height: 12px; background: <?php echo $status_color; ?>; border-radius: 50%; box-shadow: 0 0 15px <?php echo $status_color; ?>;"></div>
                        <div>
                            <div style="font-size: 26px; color: #fff;"><?php echo esc_html($realm['name']); ?></div>
                            <div style="font-size: 12px; color: #a11692; text-transform: uppercase; letter-spacing: 2px;"><?php echo esc_html($realm['type']); ?></div>
                        </div>
                    </div>
                    <div style="flex: 1; text-align: center; border-left: 1px solid #222; border-right: 1px solid #222;">
                        <div style="font-size: 10px; color: #888; text-transform: uppercase;">Population</div>
                        <div style="font-size: 18px; color: #eee;"><?php echo esc_html($realm['pop']); ?></div>
                    </div>
                    <div style="flex: 1; text-align: right; font-size: 20px; font-weight: bold; color: <?php echo $status_color; ?>;">
                        <?php echo $realm['online'] ? 'ONLINE' : 'OFFLINE'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php return ob_get_clean();
});

/* ==========================================================================
   3. ADMIN MODULE OUTPUT (Zodat het blok niet leeg blijft)
   ========================================================================= */
function sa_render_admin_status_block() {
    $core = sa_get_core_settings();
    $status = $core['last_status'] ?? 'Check website...';
    $color = ($status === 'Online') ? '#00ff00' : '#ff0000';
    
    echo '<div style="background: #1a1a1a; padding: 20px; border-left: 4px solid #a11692; color: #fff; margin-top: 20px;">';
    echo '<h3 style="margin: 0 0 10px 0; color: #a11692;">Slayer Alliance Realm Monitor</h3>';
    echo 'Status: <strong style="color:'.$color.';">' . strtoupper($status) . '</strong><br>';
    echo '<small style="color: #888;">Laatste check: ' . (isset($core['last_updated']) ? date('H:i:s', $core['last_updated']) : 'Nooit') . '</small>';
    echo '</div>';
}