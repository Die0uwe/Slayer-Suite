<?php
/**
 * Module: Armory (V4.8 Shield Visuals - Modular Version)
 * Versie: 25.3.9
 * Beschrijving: Behoudt V4.8 front-end, gekoppeld aan Core API en luistert naar de Search Module.
 */

if (!defined('ABSPATH')) exit;

/* ==========================================================================
   1. STATUS FEEDBACK VOOR HET DASHBOARD
   ========================================================================== */
function sa_status_armory() {
    $core = sa_get_core_settings();
    $token = get_transient('sa_blizz_token');

    echo '<div class="sa-card" style="border-left:5px solid #2271b1;">';
    echo '<h3 style="color:#2271b1;">🛡️ Armory Systeemstatus</h3>';
    echo 'Interface: <span style="background:#2271b1; color:#fff; padding:2px 6px; border-radius:3px; font-size:10px;">V4.8 SHIELD</span><br>';
    echo 'Standaard Realm: <strong>' . esc_html($core['realm']) . '</strong><br>';
    echo 'API Bridge: ' . ($token ? '🟢 Connected' : '🔴 Waiting for Sync');
    echo '<p style="font-size:12px; margin-top:10px; color:#666;">Gebruik <code>[sa_armory]</code> op de pagina <b>/armory/</b></p>';
    echo '</div>';
}

/* ==========================================================================
   2. FRONT-END SHORTCODE [sa_armory]
   ========================================================================== */
add_shortcode('sa_armory', function () {
    wp_enqueue_script('jquery');
    $core = sa_get_core_settings();
    
    // Check of er gezocht is via de Search Module
    $url_char = isset($_GET['char']) ? sanitize_text_field($_GET['char']) : '';
    $url_realm = isset($_GET['realm']) ? sanitize_text_field($_GET['realm']) : $core['realm'];

    ob_start();
    ?>

    <script>var whTooltips = { colorLinks: false, iconizeLinks: false, renameLinks: false };</script>
    <script src="https://wow.zamimg.com/js/tooltips.js"></script>

    <div id="sa-armory-container" style="background:#000; width:100%; max-width:780px; margin:auto; color:#fff; border:1px solid #333; font-family:'Roboto Condensed', sans-serif; position:relative; overflow:hidden; box-shadow: 0 0 60px #000;">

        <div style="padding:15px; background:#0a0a0a; border-bottom:1px solid #444; display:flex; gap:10px; position:relative; z-index:1000;">
            <input id="sa-char" value="<?php echo esc_attr($url_char); ?>" placeholder="Naam speler..." style="background:#000; color:#fff; border:1px solid #a11692; padding:8px; width:220px; outline:none; font-weight:bold; font-size:16px;">
            <input id="sa-realm" value="<?php echo esc_attr($url_realm); ?>" style="background:#000; color:#fff; border:1px solid #a11692; padding:8px; width:180px; outline:none; font-size:16px;">
            <button id="sa-btn" style="background:#a11692; color:#fff; border:none; padding:8px 25px; font-weight:900; cursor:pointer; text-transform:uppercase; letter-spacing:1px;">SCAN</button>
        </div>

        <div id="sa-viewport" style="position:relative; height:850px; background:#000; overflow:hidden;">
            <div style="position:absolute; inset:0; display:flex; justify-content:center; z-index:1;">
                <img src="https://slayeralliance.com/wp-content/uploads/2025/12/schield.png" style="width:95%; height:auto; object-fit: contain; opacity:0.4;">
            </div>

            <div style="position:absolute; inset:0; display:flex; justify-content:center; align-items:flex-end; z-index:5; padding-bottom:220px;">
                <img id="sa-render" style="height:92%; width:auto; display:none; filter:drop-shadow(0 0 35px #000); object-fit:contain;">
            </div>

            <div id="sa-ui" style="position:relative; z-index:10; padding:20px; display:none; height:100%;">
                <h1 id="sa-name" style="text-align:center; font-size:4.5rem; margin:0; text-shadow:3px 3px 15px #000; font-weight:900; text-transform:uppercase; letter-spacing:-2px;"></h1>
                <p id="sa-title" style="text-align:center; color:#ddd; margin-top:-15px; text-transform:uppercase; font-size:0.85rem; letter-spacing:3px; font-weight:300;"></p>

                <div style="display:flex; justify-content:space-between; margin-top:15px; padding:0 5px;">
                    <div id="sa-gear-l" style="display:flex; flex-direction:column; gap:10px;"></div>
                    <div id="sa-gear-r" style="display:flex; flex-direction:column; gap:10px;"></div>
                </div>

                <div id="sa-gw" style="position:absolute; bottom:130px; left:0; right:0; display:flex; justify-content:center; gap:25px; z-index:20;"></div>

                <div id="sa-stats-bar" style="position:absolute; bottom:50px; left:20px; right:20px; background:rgba(0,0,0,0.85); border:1px solid #444; display:flex; justify-content:space-around; padding:18px; text-align:center; border-radius:4px;">
                    <div style="flex:1;"><span class="sa-lbl">LEVEL</span><b id="sa-lvl">-</b></div>
                    <div style="flex:1; border-left:1px solid #333;"><span class="sa-lbl">ILVL</span><b id="sa-ilvl">-</b></div>
                    <div style="flex:1; border-left:1px solid #333;"><span class="sa-lbl">M+ SCORE</span><b id="sa-rio">-</b></div>
                </div>
            </div>

            <div id="sa-loader" style="display:none; text-align:center; position:absolute; inset:0; background:rgba(0,0,0,0.7); z-index:999; padding-top:380px;">
                <div class="sa-spin"></div>
                <p style="margin-top:20px; font-weight:bold; letter-spacing:2px; color:#a11692;">LOADING SLAYER...</p>
            </div>
        </div>
    </div>

    <style>
        .sa-lbl { display:block; color:#a11692; font-size:0.75rem; font-weight:bold; margin-bottom:2px; }
        .sa-spin { width:60px; height:60px; border:6px solid #111; border-top-color:#a11692; border-radius:50%; animation:spin 1s linear infinite; margin:auto; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .sa-item { width:56px; height:56px; border:1px solid #444; background:#000; }
        .sa-item img { width:100%; height:100%; display:block; }
        .sa-wep { width:68px; height:68px; border:2px solid #a11692; }
        #sa-stats-bar b { font-size: 22px; }
    </style>

    <script>
    jQuery(function($){
        function performScan() {
            let c = $('#sa-char').val(), r = $('#sa-realm').val();
            if(!c) return;
            $('#sa-ui, #sa-render').css('display','none'); 
            $('#sa-loader').show();
            
            $.post('<?php echo admin_url("admin-ajax.php"); ?>', { 
                action: 'sa_fetch_armory_v25', 
                char: c, 
                realm: r 
            }, function(res){
                $('#sa-loader').hide();
                if(!res.success) { alert('Slayer niet gevonden.'); return; }
                let d = res.data;
                $('#sa-name').text(d.name).css('color', d.color);
                $('#sa-title').text(d.title.replace(/{name}/gi, '').trim());
                $('#sa-lvl').text(d.level); 
                $('#sa-ilvl').text(d.ilvl);
                $('#sa-rio').text(d.rio).css('color', d.rio_color);
                if(d.render) { $('#sa-render').attr('src', d.render).fadeIn(800); }
                const box = (i, w=false) => `<div class="sa-item ${w?'sa-wep':''}" style="${w?'border-color:'+d.color:''}"><a href="https://www.wowhead.com/item=${i.id}" data-wowhead="item=${i.id}"><img src="https://wow.zamimg.com/images/wow/icons/large/${i.icon_name}.jpg"></a></div>`;
                $('#sa-gear-l').html(d.gl.map(i => box(i)).join(''));
                $('#sa-gear-r').html(d.gr.map(i => box(i)).join(''));
                $('#sa-gw').html(d.gw.map(i => box(i, true)).join(''));
                $('#sa-ui').fadeIn(300);
                if(typeof $WowheadPower !== 'undefined') $WowheadPower.refreshLinks();
            });
        }

        $('#sa-btn').on('click', performScan);

        // AUTO-SCAN als er parameters zijn vanuit de Search Module
        <?php if (!empty($url_char)): ?>
            performScan();
        <?php endif; ?>
    });
    </script>
    <?php return ob_get_clean();
});

/* ==========================================================================
   3. AJAX HANDLER
   ========================================================================== */
add_action('wp_ajax_sa_fetch_armory_v25', 'sa_fetch_armory_handler');
add_action('wp_ajax_nopriv_sa_fetch_armory_v25', 'sa_fetch_armory_handler');

function sa_fetch_armory_handler() {
    $c = strtolower(trim($_POST['char']));
    $r = str_replace(' ', '-', strtolower(trim($_POST['realm'])));
    $core = sa_get_core_settings();
    
    // Gebruik Core token
    $token = sa_get_valid_token();
    if (!$token) wp_send_json_error('API Error');

    $h = ['headers' => ['Authorization' => "Bearer $token", 'Battlenet-Namespace' => 'profile-eu']];

    $p = json_decode(wp_remote_retrieve_body(wp_remote_get("https://eu.api.blizzard.com/profile/wow/character/$r/$c?locale=en_GB", $h)));
    if (!isset($p->name)) wp_send_json_error();

    $m = json_decode(wp_remote_retrieve_body(wp_remote_get("https://eu.api.blizzard.com/profile/wow/character/$r/$c/character-media?locale=en_GB", $h)));
    $e = json_decode(wp_remote_retrieve_body(wp_remote_get("https://eu.api.blizzard.com/profile/wow/character/$r/$c/equipment?locale=en_GB", $h)));

    $render = "";
    if(isset($m->assets)) {
        foreach($m->assets as $a) { if(in_array($a->key, ['main-raw', 'full'])) { $render = $a->value; break; } }
    }

    $rio_res = wp_remote_get("https://raider.io/api/v1/characters/profile?region=eu&realm=$r&name=$c&fields=mythic_plus_scores_by_season:current");
    $rio_data = json_decode(wp_remote_retrieve_body($rio_res));
    $rio_score = $rio_data->mythic_plus_scores_by_season[0]->scores->all ?? 0;

    $gl=[]; $gr=[]; $gw=[];
    if(!empty($e->equipped_items)) {
        foreach($e->equipped_items as $i) {
            $media_url = $i->media->key->href;
            $media_data = json_decode(wp_remote_retrieve_body(wp_remote_get($media_url, $h)));
            $icon = (isset($media_data->assets)) ? basename(parse_url($media_data->assets[0]->value, PHP_URL_PATH), ".jpg") : "inventoryslot_empty";
            $item = ['id' => $i->item->id, 'icon_name' => $icon];
            $slot = $i->slot->type;
            if(in_array($slot,['HEAD','NECK','SHOULDER','BACK','CHEST','WRIST','SHIRT','TABARD'])) $gl[]=$item;
            elseif(in_array($slot,['MAIN_HAND','OFF_HAND'])) $gw[]=$item;
            else $gr[]=$item;
        }
    }

    $colors = [1=>'#C79C6E',2=>'#F58CBA',3=>'#ABD473',4=>'#FFF569',5=>'#FFFFFF',6=>'#C41F3B',7=>'#0070DE',8=>'#69CCF0',9=>'#9482C9',10=>'#00FF96',11=>'#FF7D0A',12=>'#A330C9',13=>'#33937F'];
    
    wp_send_json_success([
        'name' => $p->name, 
        'title' => $p->active_title->display_string ?? '', 
        'level' => $p->level, 
        'ilvl' => $p->equipped_item_level,
        'rio' => $rio_score, 
        'rio_color' => ($rio_score > 2400) ? '#ff8000' : '#a335ee',
        'color' => $colors[$p->character_class->id] ?? '#fff', 
        'render' => $render, 
        'gl' => $gl, 'gr' => $gr, 'gw' => $gw
    ]);
}