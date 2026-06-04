<?php
/**
 * Module: Shortcode Guide (v25.3.18)
 * Layout: Click-to-Copy, Status Check & Updated List
 */

if (!defined('ABSPATH')) exit;

function sa_get_all_shortcodes_info() {
    return [
        '[sa_armory]'         => 'Toont de interactieve Armory met gildeleden, 3D renders en gear.',
        '[sa_search]'         => 'De zoekbalk gekoppeld aan de Armory (Search Module).',
        '[sa_delve_status]'   => 'Toont de 16 Bountiful Delves in een 4-wide grid (v25.3 Update).',
        '[sa_collections]'    => 'Toont de verzamelaars ranglijst (Mounts & Pets) van de gilde.',
        '[sa_realm_status]'   => 'Toont de live online/offline status van de WoW Realm.',
        '[sa_buttons]'        => 'Toont de navigatieknoppen (Armory, Roster, Recruitment).',
        '[guild_roster]'      => 'Toont de volledige lijst met alle gildeleden vanuit de database.',
        '[guild_recruitment]' => 'Toont het recruitment-blok met status per class/rol.'
    ];
}

function sa_status_shortcode_guide() {
    $codes = sa_get_all_shortcodes_info();
    ?>
    <style>
        .sa-copy-badge { 
            cursor: pointer; 
            font-size: 13px; 
            color: #fff; 
            background: #a11692; 
            padding: 8px 12px; 
            border-radius: 6px; 
            display: inline-block;
            transition: all 0.2s;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .sa-copy-badge:hover { background: #821275; transform: translateY(-1px); }
        .sa-copy-badge:active { transform: scale(0.95); }
        .copy-msg { font-size: 11px; color: #46b450; font-weight: bold; margin-left: 10px; opacity: 0; transition: 0.3s; }
        .show-msg { opacity: 1; }
    </style>

    <script>
    function saCopyCode(text, el) {
        navigator.clipboard.writeText(text).then(function() {
            var msg = el.nextElementSibling;
            msg.classList.add('show-msg');
            setTimeout(function() { msg.classList.remove('show-msg'); }, 1500);
        });
    }
    </script>

    <div class="sa-card" style="background:#fff; padding:30px; border-left:5px solid #a11692;">
        <h2 style="color:#a11692; margin-top:0;">📖 Shortcode Bibliotheek</h2>
        <p style="margin-bottom:25px; color:#555;">Gebruik de onderstaande codes op je pagina's. Klik op een code om deze direct naar je klembord te kopiëren.</p>
        
        <table class="wp-list-table widefat fixed striped" style="border:1px solid #eee;">
            <thead>
                <tr>
                    <th style="width:280px; padding:15px; font-weight:bold;">Shortcode</th>
                    <th style="padding:15px; font-weight:bold;">Beschrijving / Gebruik</th>
                    <th style="width:120px; padding:15px; font-weight:bold; text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($codes as $code => $desc): 
                    $tag = str_replace(['[', ']'], '', $code);
                    $is_active = shortcode_exists($tag);
                ?>
                <tr>
                    <td style="padding:15px; vertical-align:middle;">
                        <span class="sa-copy-badge" onclick="saCopyCode('<?php echo $code; ?>', this)">
                            <?php echo $code; ?>
                        </span>
                        <span class="copy-msg">GEKOPIEERD!</span>
                    </td>
                    <td style="padding:15px; vertical-align:middle; font-size:14px; color:#333;">
                        <?php echo $desc; ?>
                    </td>
                    <td style="padding:15px; vertical-align:middle; text-align:center;">
                        <?php if($is_active): ?>
                            <span style="background:#e7f7ed; color:#46b450; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:bold; border:1px solid #46b450;">🟢 ACTIEF</span>
                        <?php else: ?>
                            <span style="background:#f9f9f9; color:#ccc; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:bold; border:1px solid #eee;">⚪ UIT</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top:30px; padding:20px; background:#f0f6fb; border-radius:8px; border:1px solid #ccd0d4; display:flex; align-items:center; gap:15px;">
            <span style="font-size:24px;">💡</span>
            <p style="margin:0; font-size:13px; color:#2271b1; line-height:1.5;">
                <strong>Pro Tip:</strong> Staat een shortcode op ⚪ <b>UIT</b>? Controleer dan of de bijbehorende module is geüpload in de <code>/modules/</code> map en of deze correct wordt aangeroepen in je Master Suite.
            </p>
        </div>
    </div>
    <?php
}