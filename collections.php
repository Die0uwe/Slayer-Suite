<?php
/**
 * Module: Collections Hub Pro (V5.2.7)
 * Fix: Column Agnostic Mode - Omzeilt 'Unknown column' fouten
 */

if (!defined('ABSPATH')) exit;

add_shortcode('sa_collections', function() {
    global $wpdb;

    $table_items = $wpdb->prefix . 'sa_items'; 

    // We halen alles op met SELECT * zodat we niet afhankelijk zijn van namen
    $raw_items = $wpdb->get_results("SELECT * FROM $table_items", ARRAY_N);

    if (empty($raw_items)) {
        return "<div style='color:#fff; padding:30px; background:#16161e; border-radius:10px; border:1px dashed #a11692; text-align:center;'>
                Tabel <strong>$table_items</strong> is leeg. Voer de Sync uit in de Manager.
                </div>";
    }

    ob_start(); ?>
    <div class="sa-hub-container" style="background:#0d0d12; padding:30px; border-radius:20px; border:1px solid #333; font-family: sans-serif; color: #fff;">
        <h3 style="color:#a11692; margin-bottom:20px;">Gilde Collectie (Database Scan)</h3>
        
        <div id="saColGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(130px, 1fr)); gap:20px;">
            <?php foreach ($raw_items as $row): 
                // Op basis van jouw SQL bestand is de volgorde:
                // [0] = ID, [1] = Naam, [2] = Icon, [3] = Type, [4] = URL
                $id    = $row[0];
                $name  = !empty($row[1]) ? $row[1] : "Item #$id";
                $type  = $row[3];
                $icon  = !empty($row[4]) ? $row[4] : "https://wow.zamimg.com/images/wow/icons/large/inv_misc_questionmark.jpg";

                // Filter alleen de types die we willen zien
                if (!in_array(strtoupper($type), ['TOYS', 'MOUNTS', 'PETS'])) continue;
            ?>
                <div class="sa-item-card" style="background:#1c1c26; padding:15px; border-radius:12px; border:1px solid #333; text-align:center;">
                    <div style="width:56px; height:56px; margin:0 auto 10px auto;">
                        <img src="<?php echo $icon; ?>" style="width:100%; height:100%; border-radius:8px; border:2px solid #a11692; object-fit:cover;">
                    </div>
                    <div style="color:#fff; font-size:11px; font-weight:600; height:28px; overflow:hidden;"><?php echo esc_html($name); ?></div>
                    <div style="font-size:9px; color:#a11692; font-weight:bold; margin-top:5px;"><?php echo esc_html($type); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php return ob_get_clean();
});