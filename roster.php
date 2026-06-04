<?php
/**
 * Module: Guild Roster - Medieval King Size (V9.6)
 * Features: MedievalSharp Font, 140px Shield, Pagination, iLvl, Hover Effects.
 */

if (!defined('ABSPATH')) exit;

add_shortcode('guild_roster', function() {
    global $wpdb;

    $table_chars = $wpdb->prefix . 'sa_housing_chars';
    $core = sa_get_core_settings();
    $realm = strtolower($core['realm'] ?? 'scarshield-legion');
    $guild = strtolower($core['guild'] ?? 'slayer alliance');

    $endpoint = "data/wow/guild/{$realm}/{$guild}/roster?namespace=profile-eu";
    $data = sa_get_blizzard_data($endpoint);

    if (!$data || !isset($data->members)) {
        return "<div style='color:#fff; background:#1e1e2c; padding:40px; border-radius:15px; border:3px solid #a11692; text-align:center;'>⚠️ Blizzard API Connectie mislukt.</div>";
    }

    $total_members = count($data->members);
    $guild_points = $wpdb->get_var("SELECT MAX(count_achievements) FROM $table_chars") ?: '12.450';
    $db_chars = $wpdb->get_results("SELECT char_name, ilevel FROM $table_chars", OBJECT_K);

    $class_map = [
        1 => ['c' => '#C79C6E', 'i' => 'warrior'], 2 => ['c' => '#F58CBA', 'i' => 'paladin'],
        3 => ['c' => '#ABD473', 'i' => 'hunter'], 4 => ['c' => '#FFF569', 'i' => 'rogue'],
        5 => ['c' => '#FFFFFF', 'i' => 'priest'], 6 => ['c' => '#C41F3B', 'i' => 'deathknight'],
        7 => ['c' => '#0070DE', 'i' => 'shaman'], 8 => ['c' => '#69CCF0', 'i' => 'mage'],
        9 => ['c' => '#9482C9', 'i' => 'warlock'], 10 => ['c' => '#00FF96', 'i' => 'monk'],
        11 => ['c' => '#FF7D0A', 'i' => 'druid'], 12 => ['c' => '#A330C9', 'i' => 'demonhunter'],
        13 => ['c' => '#33937F', 'i' => 'evoker']
    ];

    ob_start(); ?>
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&family=Roboto:wght@400;900&display=swap" rel="stylesheet">
    
    <div class="sa-roster-final" style="all: initial; font-family: 'Roboto', sans-serif; display: block; max-width: 1300px; margin: 40px auto; color: #fff;">
        
        <div style="background: linear-gradient(135deg, #151520 0%, #0a0a0f 100%); border: 3px solid #a11692; border-radius: 20px; padding: 40px; margin-bottom: 40px; display: flex; align-items: center; justify-content: space-between; gap: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.8);">
            <div style="display: flex; align-items: center; gap: 50px;">
                <img src="https://slayeralliance.com/wp-content/uploads/2025/12/schield.png" alt="" style="height: 140px; width: auto; filter: drop-shadow(0 0 20px #a11692);">
                <div style="display: flex; gap: 60px; border-left: 4px solid #a11692; padding-left: 50px;">
                    <div>
                        <span style="font-family: 'MedievalSharp', cursive; color: #a11692; font-size: 16px; text-transform: uppercase; letter-spacing: 2px; display: block; margin-bottom: 5px;">Leden</span>
                        <span style="color: #fff; font-size: 55px; font-weight: 900; line-height: 0.8;"><?php echo $total_members; ?></span>
                    </div>
                    <div>
                        <span style="font-family: 'MedievalSharp', cursive; color: #ffd700; font-size: 16px; text-transform: uppercase; letter-spacing: 2px; display: block; margin-bottom: 5px;">Achievements</span>
                        <span style="color: #ffd700; font-size: 55px; font-weight: 900; line-height: 0.8;">🏆 <?php echo $guild_points; ?></span>
                    </div>
                </div>
            </div>
            <div style="flex-grow: 1; max-width: 450px; position: relative;">
                <input type="text" id="sa-search-mega" placeholder="ZOEK EEN SLAYER..." style="font-family: 'MedievalSharp', cursive; width: 100%; background: rgba(0,0,0,0.6); border: 3px solid #444; color: #fff; padding: 20px 25px 20px 65px; border-radius: 60px; outline: none; font-size: 18px; letter-spacing: 1px;">
                <span style="position: absolute; left: 25px; top: 50%; transform: translateY(-50%); font-size: 28px; color: #a11692;">🔍</span>
            </div>
        </div>

        <div style="background: #0a0a0f; border: 3px solid #a11692; border-radius: 25px; overflow: hidden; box-shadow: 0 30px 80px rgba(0,0,0,0.9);">
            <table style="width: 100%; border-collapse: collapse; color: #fff;" id="sa-table-mega">
                <thead>
                    <tr style="background: #1e1e2c; border-bottom: 4px solid #a11692;">
                        <th style="font-family: 'MedievalSharp', cursive; padding: 30px; text-align: left; font-size: 18px; text-transform: uppercase; letter-spacing: 2px; color: #aaa;">Slayer</th>
                        <th style="font-family: 'MedievalSharp', cursive; padding: 30px; text-align: center; font-size: 18px; text-transform: uppercase; letter-spacing: 2px; color: #aaa;">Level</th>
                        <th style="font-family: 'MedievalSharp', cursive; padding: 30px; text-align: center; font-size: 18px; text-transform: uppercase; letter-spacing: 2px; color: #aaa;">iLvl</th>
                        <th style="font-family: 'MedievalSharp', cursive; padding: 30px; text-align: right; font-size: 18px; text-transform: uppercase; letter-spacing: 2px; color: #aaa;">Profiel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    usort($data->members, function($a, $b) { return strcmp($a->character->name, $b->character->name); });
                    foreach ($data->members as $m): 
                        $char = $m->character;
                        $class_id = $char->playable_class->id;
                        $c_data = isset($class_map[$class_id]) ? $class_map[$class_id] : ['c' => '#fff', 'i' => 'inv_misc_questionmark'];
                        $icon_url = "https://wow.zamimg.com/images/wow/icons/large/class_{$c_data['i']}.jpg";
                        $ilvl = (isset($db_chars[$char->name]) && !empty($db_chars[$char->name]->ilevel)) ? $db_chars[$char->name]->ilevel : '??';
                    ?>
                    <tr class="sa-row-mega" style="border-bottom: 1px solid #222; transition: 0.3s;">
                        <td style="padding: 25px 30px; display: flex; align-items: center; gap: 25px;">
                            <img src="<?php echo $icon_url; ?>" style="width: 45px; height: 45px; border-radius: 8px; border: 3px solid <?php echo $c_data['c']; ?>; box-shadow: 0 0 15px <?php echo $c_data['c']; ?>66;">
                            <span style="font-family: 'MedievalSharp', cursive; font-weight: bold; color: <?php echo $c_data['c']; ?>; font-size: 30px; letter-spacing: 1px;"><?php echo esc_html($char->name); ?></span>
                        </td>
                        <td style="padding: 25px 30px; text-align: center; font-weight: 900; font-size: 24px; color: #eee;"><?php echo $char->level; ?></td>
                        <td style="padding: 25px 30px; text-align: center;">
                            <span style="background: #1e1e2c; padding: 10px 20px; border-radius: 12px; border: 2px solid <?php echo ($ilvl != '??' && $ilvl > 600) ? '#ff8000' : '#a11692'; ?>; font-weight: 900; font-size: 22px; color: #fff; display: inline-block; min-width: 60px;">
                                <?php echo $ilvl; ?>
                            </span>
                        </td>
                        <td style="padding: 25px 30px; text-align: right;">
                            <a href="https://worldofwarcraft.blizzard.com/en-gb/character/eu/<?php echo $char->realm->slug; ?>/<?php echo strtolower($char->name); ?>" 
                               target="_blank" class="sa-armory-btn">ARMORY</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="padding: 30px 45px; background: #1e1e2c; display: flex; justify-content: space-between; align-items: center; border-top: 4px solid #a11692;">
                <span id="sa-page-info-mega" style="font-family: 'MedievalSharp', cursive; font-size: 18px; color: #aaa; text-transform: uppercase;"></span>
                <div style="display: flex; gap: 20px;">
                    <button id="sa-prev-mega" class="sa-btn-mega">VORIGE</button>
                    <button id="sa-next-mega" class="sa-btn-mega">VOLGENDE</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var itemsPerPage = 25; var currentPage = 1; var $rows = $("#sa-table-mega tbody tr"); var filteredRows = $rows;
        function updateDisplay() {
            var totalPages = Math.ceil(filteredRows.length / itemsPerPage);
            $rows.hide(); 
            filteredRows.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage).show();
            $("#sa-page-info-mega").text("Pagina " + currentPage + " / " + (totalPages || 1));
            $("#sa-prev-mega").prop("disabled", currentPage === 1);
            $("#sa-next-mega").prop("disabled", currentPage === totalPages || totalPages === 0);
        }
        $("#sa-search-mega").on("keyup", function() {
            var v = $(this).val().toLowerCase();
            filteredRows = $rows.filter(function() { return $(this).text().toLowerCase().indexOf(v) > -1; });
            currentPage = 1; updateDisplay();
        });
        $("#sa-prev-mega").click(function() { if(currentPage > 1) { currentPage--; updateDisplay(); } });
        $("#sa-next-mega").click(function() { if(currentPage < Math.ceil(filteredRows.length / itemsPerPage)) { currentPage++; updateDisplay(); } });
        updateDisplay();
    });
    </script>
    <style>
        .sa-row-mega:hover { background: rgba(161, 22, 146, 0.2) !important; }
        .sa-armory-btn { 
            font-family: 'MedievalSharp', cursive; color: #fff !important; text-decoration: none !important; 
            font-size: 16px; background: #a11692; padding: 15px 30px; border-radius: 12px; transition: 0.3s; 
            display: inline-block; box-shadow: 0 5px 15px rgba(161, 22, 146, 0.4);
        }
        .sa-armory-btn:hover { transform: scale(1.1); box-shadow: 0 0 25px #a11692; background: #c21da8; }
        .sa-btn-mega { 
            font-family: 'MedievalSharp', cursive; background: #0a0a0f; border: 3px solid #a11692; 
            color: #fff; padding: 15px 35px; border-radius: 12px; cursor: pointer; font-size: 16px; transition: 0.3s;
        }
        .sa-btn-mega:hover:not(:disabled) { background: #a11692; transform: translateY(-3px); }
        .sa-btn-mega:disabled { opacity: 0.1; cursor: not-allowed; }
        #sa-search-mega:focus { border-color: #a11692; background: rgba(0,0,0,0.8); }
    </style>
    <?php return ob_get_clean();
});