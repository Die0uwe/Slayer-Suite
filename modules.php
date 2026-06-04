<?php
/**
 * Module: Realm Monitor Cluster 2 (v25.3.16)
 * Bestandsnaam: realm-monitor-2.php
 */

if (!defined('ABSPATH')) exit;

/* ==========================================================================
   1. DATA LOGICA (API SYNC)
   ========================================================================== */
function sa_get_cluster_2_data_final() {
    // We gebruiken 'Eonar' om het hele connected cluster op te halen
    $endpoint = "data/wow/search/connected-realm?namespace=dynamic-eu&realms.name.en_GB=Eonar";
    
    // Gebruikt de sa_get_blizzard_data functie uit jouw Master Suite core
    $data = sa_get_blizzard_data($endpoint);

    if ($data && isset($data->results[0])) {
        $res = $data->results[0]->data;
        $status_up = ($res->status->type === 'UP');
        
        $cluster = [];
        foreach($res->realms as $r) {
            $cluster[] = [
                'name'   => $r->name->en_GB,
                'online' => $status_up,
                'type'   => $r->type->name->en_GB ?? 'PvE',
                'pop'    => $res->population->name->en_GB ?? 'Unknown'
            ];
        }

        // Cache de resultaten in de database zodat admin en front-end ze direct hebben
        update_option('sa_cluster2_cache', $cluster);
        update_option('sa_cluster2_last_sync', time());
        return $cluster;
    }
    return get_option('sa_cluster2_cache', []);
}

/* ==========================================================================
   2. ADMIN PANEL: SA_STATUS_REALM_MONITOR_2
   ========================================================================== */
/**
 * Deze functie vult de tab in je Master Suite.
 * De naam MOET sa_status_ + bestandsnaam (zonder .php) zijn.
 */
function sa_status_realm_monitor_2() {
    $cluster = sa_get_cluster_2_data_final(); 
    $last_sync = get_option('sa_cluster2_last_sync', 0);
    ?>
    <div class="sa-card" style="background:#fff; padding:30px; border-left:5px solid #a11692;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="color:#a11692; margin:0; font-size:24px;">🌐 Realm Cluster 2 Status</h2>
            <div style="text-align:right;">
                <span style="font-size:12px; color:#888;">Laatste API Update:</span><br>
                <strong style="color:#333;"><?php echo $last_sync ? date('H:i:s', $last_sync) : 'Nooit'; ?></strong>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
            <?php if(!empty($cluster)): foreach($cluster as $r): 
                $color = $r['online'] ? '#46b450' : '#dc3232';
            ?>
                <div style="padding:15px; border:1px solid #eee; border-radius:8px; background:#f9f9f9;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span style="font-weight:bold; color:#111;"><?php echo $r['name']; ?></span>
                        <div style="width:10px; height:10px; background:<?php echo $color; ?>; border-radius:50%;"></div>
                    </div>
                    <div style="font-size:11px; color:#666; text-transform:uppercase; letter-spacing:1px;">
                        Pop: <span style="color:#111; font-weight:bold;"><?php echo $r['pop']; ?></span>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <p>Geen data gevonden. Controleer je Blizzard API instellingen.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top:30px; padding:15px; background:#f0f6fb; border-radius:4px; font-size:13px; color:#2271b1;">
            ℹ️ <strong>Webmaster Tip:</strong> Gebruik shortcode <code>[sa_realm_status_2]</code> om de visuele bars op je website te tonen.
        </div>
    </div>
    <?php
}

/* ==========================================================================
   3. FRONT-END: [sa_realm_status_2]
   ========================================================================== */
add_shortcode('sa_realm_status_2', function() {
    $cluster = get_option('sa_cluster2_cache', []);
    if(empty($cluster)) $cluster = sa_get_cluster_2_data_final();

    ob_start(); ?>
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap" rel="stylesheet">
    <div style="max-width: 1300px; margin: 40px auto; font-family: 'MedievalSharp', cursive;">
        <h2 style="text-align: center; color: #a11692; font-size: 32px; text-transform: uppercase; letter-spacing: 4px; margin-bottom: 40px;">— Realm Cluster Status —</h2>
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
    <?php
    return ob_get_clean();
});