<?php
/**
 * Module: CurseForge Slayer Edition - Pro Layout
 * Filename: curseforge.php
 */
if (!defined('ABSPATH')) exit;

// 1. ADMIN UI: Manual Input (6 Rows)
function sa_status_curseforge() {
    if (isset($_POST['sa_save_addons'])) {
        $addons = [];
        if (isset($_POST['addon_data']) && is_array($_POST['addon_data'])) {
            foreach ($_POST['addon_data'] as $addon) {
                if (!empty($addon['url'])) {
                    $addons[] = [
                        'url'   => esc_url_raw($addon['url']),
                        'title' => sanitize_text_field($addon['title']),
                        'desc'  => sanitize_textarea_field($addon['desc'])
                    ];
                }
            }
        }
        update_option('sa_manual_addons', $addons);
        echo '<div class="updated"><p>Addons updated successfully! ✅</p></div>';
    }

    $addons = get_option('sa_manual_addons', []);
    $display_count = max(6, count($addons) + 1);

    echo '<div style="background:#111; padding:20px; border:2px solid #a335ee; border-radius:10px; color:#fff; margin-top:20px; font-family:sans-serif;">';
    echo '<h3 style="color:#a335ee;">🛠️ ADDON MANAGER</h3>';
    
    echo '<form method="post">';
    echo '<table style="width:100%; border-spacing: 0 15px;">';
    echo '<tr style="text-align:left; color:#00ccff; font-size:11px; text-transform:uppercase;">
            <th style="width:30%;">Settings</th>
            <th style="width:70%;">Description</th>
          </tr>';

    for ($i = 0; $i < $display_count; $i++) {
        $v = isset($addons[$i]) ? $addons[$i] : ['url'=>'','title'=>'','desc'=>''];
        echo '<tr>';
        echo '<td style="vertical-align:top;">
                <input type="text" name="addon_data['.$i.'][title]" value="'.esc_attr($v['title']).'" placeholder="Addon Title" style="width:95%; background:#000; color:#fff; border:1px solid #444; font-weight:bold; margin-bottom:5px;">
                <input type="text" name="addon_data['.$i.'][url]" value="'.esc_attr($v['url']).'" placeholder="CurseForge URL (Hidden)" style="width:95%; background:#000; color:#444; border:1px solid #222; font-size:10px;">
              </td>';
        echo '<td>
                <textarea name="addon_data['.$i.'][desc]" rows="3" placeholder="Enter description..." style="width:100%; background:#000; color:#ccc; border:1px solid #444; resize:vertical;">'.esc_textarea($v['desc']).'</textarea>
              </td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '<br><input type="submit" name="sa_save_addons" style="background:#a335ee; color:#fff; border:none; padding:10px 25px; border-radius:5px; cursor:pointer; font-weight:bold;" value="SAVE CHANGES">';
    echo '</form>';
    echo '</div>';
}

// 2. SHORTCODE: [sa_addon_list]
add_shortcode('sa_addon_list', function() {
    $addons = get_option('sa_manual_addons', []);
    if (empty($addons)) return '';

    ob_start(); ?>
    <style>
        .sa-addon-container { display: flex; flex-direction: column; gap: 20px; margin: 20px 0; }
        
        @keyframes sa-neon-glow {
            0% { border-color: #a335ee; box-shadow: 0 0 5px #a335ee; }
            50% { border-color: #00ccff; box-shadow: 0 0 12px #00ccff; }
            100% { border-color: #a335ee; box-shadow: 0 0 5px #a335ee; }
        }

        .sa-addon-row {
            background: #0a0a0a;
            border: 2px solid #a335ee;
            border-radius: 12px;
            display: flex;
            padding: 20px;
            text-decoration: none !important;
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: sa-neon-glow 6s infinite ease-in-out;
            align-items: center;
        }

        .sa-addon-row:hover {
            transform: scale(1.02);
            border-color: #ff007f;
            box-shadow: 0 0 25px #ff007f;
            animation: none;
        }

        .sa-left-col {
            width: 30%;
            border-right: 1px solid rgba(163, 53, 238, 0.3);
            padding-right: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sa-addon-title {
            color: #fff;
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .sa-btn-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sa-get-btn {
            background: linear-gradient(45deg, #a335ee, #ff007f);
            color: #fff !important;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .sa-cf-logo {
            width: 24px;
            height: 24px;
            filter: drop-shadow(0 0 2px #00ccff);
        }

        .sa-right-col {
            width: 70%;
            padding-left: 25px;
        }

        .sa-addon-description {
            color: #ccc;
            font-size: 14px;
            line-height: 1.6;
            min-height: 4.8em;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <div class="sa-addon-container">
        <?php foreach ($addons as $addon): ?>
            <a href="<?php echo esc_url($addon['url']); ?>" target="_blank" class="sa-addon-row">
                <div class="sa-left-col">
                    <span class="sa-addon-title"><?php echo esc_html($addon['title']); ?></span>
                    <div class="sa-btn-wrapper">
                        <span class="sa-get-btn">Get Addon</span>
                        <img src="https://www.curseforge.com/favicon.ico" class="sa-cf-logo" alt="CF">
                    </div>
                </div>
                <div class="sa-right-col">
                    <div class="sa-addon-description">
                        <?php echo nl2br(esc_html($addon['desc'])); ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
});