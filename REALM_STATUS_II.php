<?php
/**
 * Module: Slayer Alliance Realm Monitor Cluster 2 (V3.8)
 * Frontend: Shortcode [sa_realm_status_2]
 * Cluster: Eonar, Blade's Edge, Vek'nilash, Aerie Peak, Bronzebeard
 */

if (!defined('ABSPATH')) exit;

/* ==========================================================================
   1. DATA LOGICA (Specifiek voor Cluster 2)
   ========================================================================= */
function sa_get_cluster_2_data() {
    $core = get_option('sa_core_settings');
    
    // We gebruiken 'Eonar' om de data van het hele cluster op te halen
    $anchor_realm = "eonar"; 
    $endpoint = "data/wow/search/connected-realm?namespace=dynamic-eu&realms.name.en_GB=" . ucfirst($anchor_realm);
    
    // Gebruik de bestaande API helper functie
    $data = sa_get_blizzard_data($endpoint);

    $cluster = [];
    if ($data && isset($data->results[0])) {
        $res = $data->results[0]->data;
        $status_up = ($res->status->type === 'UP');
        
        foreach($res->realms as $r) {
            $cluster[] = [
                'name'   => $r->name->en_GB,
                'online' => $status_up,
                'type'   => $r->type->name->en_GB ?? 'PvE',
                'pop'    => $res->population->name->en_GB ?? 'Unknown'
            ];
        }

        // Status opslaan voor het aparte admin blok
        $core['cluster2_status'] = $status_up ? 'Online' : 'Offline';
        $core['cluster2_updated'] = time();
        update_option('sa_core_settings', $core);
    }
    return $cluster;
}

/* ==========================================================================
   2. FRONTEND SHORTCODE [sa_realm_status_2]
   ========================================================================= */
add_shortcode('sa_realm_status_2', function() {
    $cluster = sa_get_cluster_2_data();
    if (empty($cluster)) return "<div style='color:#666; font-family: MedievalSharp, cursive; padding: 20px;'>Realm data voor Cluster 2 niet beschikbaar...</div>";

    ob_start(); ?>
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap" rel="stylesheet">
    
    <div id="sa-cluster-2-module" style="all: initial; display: block; max-width: 1300px; margin: 40px auto; font-family: 'MedievalSharp', cursive;">
        <h2 style="text-align: center; color: #a11692; font-size: 32px; text-transform: uppercase; letter-spacing: 4px; margin-bottom: 40px; font-family: 'MedievalSharp', cursive;">
            — Connected Realm Cluster 2 Status —
        </h2>
        
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
   3. ADMIN STATUS BLOK (Voor de back-end weergave)
   ========================================================================= */
function sa_render_admin_status_block_2() {
    $core = get_option('sa_core_settings');
    $status = $core['cluster2_status'] ?? 'Onbekend';
    $color = ($status === 'Online') ? '#00ff00' : '#ff0000';
    $last_check = isset($core['cluster2_updated']) ? date('H:i:s', $core['cluster2_updated']) : 'Nooit';
    
    echo '<div style="background: #1a1a1a; padding: 20px; border-left: 4px solid #a11692; color: #fff; margin-top: 20px; border-radius: 4px; border: 1px solid #333;">';
    echo '<h3 style="margin: 0 0 10px 0; color: #a11692; font-family: sans-serif;">Slayer Alliance Cluster 2 Monitor</h3>';
    echo '<div style="font-size: 16px; font-family: sans-serif;">Cluster Status: <strong style="color:'.$color.';">' . strtoupper($status) . '</strong></div>';
    echo '<div style="margin-top: 8px; font-size: 12px; color: #888; font-family: sans-serif;">Laatste synchronisatie: ' . $last_check . '</div>';
    echo '<div style="margin-top: 10px; font-size: 11px; color: #666;">Bevat: Eonar, Blade\'s Edge, Vek\'nilash, Aerie Peak, Bronzebeard</div>';
    echo '</div>';
}