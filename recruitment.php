<?php
/**
 * Module: Recruitment Manager PRO (v25.12.31)
 * Layout: 5x4 Grid, Medieval Header, 3x2 Central Text, 1x1 Bottom Shield.
 * Icons: 50px XXL-Icons.
 * Text: Verbeterde leesbaarheid (Bold & High Contrast).
 * Website: http://www.slayeralliance.com
 */

if (!defined('ABSPATH')) exit;

/* ==========================================================================
   1. FRONT-END: [guild_recruitment]
   ========================================================================== */
add_shortcode('guild_recruitment', function() {
    $status = get_option('sa_recruitment_status', []);
    $custom_html = get_option('sa_recruitment_html', '<h4 style="color:#a11692;">JOIN US</h4><p>Apply via Discord</p>');
    $bottom_logo = get_option('sa_recruitment_logo', 'https://slayeralliance.com/wp-content/uploads/2025/12/schield.png');
    
    if (empty($status)) return "";

    ob_start(); ?>
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap" rel="stylesheet">

    <style>
        .sa-rec-wrapper { 
            background: #0a0a0f; border: 2px solid #a11692; border-radius: 12px; 
            padding: 35px; color: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        /* Grote Medieval Header */
        .sa-main-header {
            display: flex; align-items: center; gap: 25px;
            margin-bottom: 25px; border-bottom: 2px solid #a11692; padding-bottom: 20px;
        }
        .sa-main-header img {
            width: 100px; height: auto;
            filter: drop-shadow(0 0 10px rgba(161, 22, 146, 0.5));
        }
        .sa-main-header h3 {
            margin: 0; color: #a11692; font-family: 'MedievalSharp', cursive;
            font-size: 42px; text-transform: uppercase; letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }

        .sa-grid-layout { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; }

        /* Centraal HTML Vlak */
        .sa-central-wide-bar { 
            grid-column: 2 / span 3; grid-row: 2 / span 2; 
            background: linear-gradient(180deg, rgba(161, 22, 146, 0.1), rgba(0,0,0,0.9)); 
            border: 2px solid #a11692; border-radius: 12px; padding: 25px; 
            display: flex; justify-content: center; align-items: center; text-align: center; 
        }

        /* Onderste Logo Vlak */
        .sa-single-logo-box { 
            grid-column: 3; grid-row: 4; 
            display: flex; justify-content: center; align-items: center; 
            border: 1px solid rgba(161, 22, 146, 0.4); border-radius: 10px; 
            background: rgba(161, 22, 146, 0.05); overflow: hidden; padding: 8px;
        }
        .sa-single-logo-box img { width: 100%; height: 100%; object-fit: contain; }

        /* Class kaarten & Tekst Verbeteringen */
        .sa-rec-card { 
            background: rgba(255,255,255,0.04); 
            padding: 12px; border-radius: 10px; border: 1px solid #444; 
        }
        .sa-class-header { 
            display: flex; align-items: center; gap: 12px; 
            margin-bottom: 12px; border-bottom: 1px solid rgba(161, 22, 146, 0.4); 
            padding-bottom: 8px; 
        }
        .sa-class-header img { 
            width: 50px; height: 50px; border-radius: 6px; border: 2px solid #a11692; 
            box-shadow: 0 0 10px rgba(161, 22, 146, 0.5); 
        }
        .sa-class-header span { 
            font-weight: 900; /* Extra Bold */
            font-size: 14px;  /* Iets groter */
            color: #ffffff;   /* Fel wit */
            letter-spacing: 0.5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,1);
        }

        /* Spec regels */
        .sa-spec-row { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 5px; font-size: 12px; 
        }
        .sa-spec-name { 
            color: #eeeeee;   /* Lichter grijs voor betere leesbaarheid */
            font-weight: 600; /* Bold */
            text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
        }
        .sa-spec-dot { font-size: 14px; }

        /* Grid posities */
        .sa-priest  { grid-column: 1; grid-row: 2; }
        .sa-rogue   { grid-column: 1; grid-row: 3; }
        .sa-shaman  { grid-column: 5; grid-row: 2; }
        .sa-warlock { grid-column: 5; grid-row: 3; }
        .sa-mage    { grid-column: 1; grid-row: 4; }
        .sa-monk    { grid-column: 2; grid-row: 4; }
        .sa-paladin { grid-column: 4; grid-row: 4; }
        .sa-warrior { grid-column: 5; grid-row: 4; }

        @media (max-width: 1100px) { 
            .sa-grid-layout { display: flex; flex-direction: column; } 
            .sa-main-header { flex-direction: column; text-align: center; }
            .sa-main-header h3 { font-size: 30px; }
        }
    </style>

    <div class="sa-rec-wrapper">
        <div class="sa-main-header">
            <img src="https://slayeralliance.com/wp-content/uploads/2025/12/schield.png" alt="Slayer Alliance">
            <h3>GUILD RECRUITMENT</h3>
        </div>

        <div class="sa-grid-layout">
            <div class="sa-central-wide-bar"><div><?php echo do_shortcode(stripslashes($custom_html)); ?></div></div>
            <div class="sa-single-logo-box">
                <img src="<?php echo esc_url($bottom_logo); ?>" alt="Shield Footer">
            </div>

            <?php foreach ($status as $class => $specs): 
                $slug = str_replace(' ', '', strtolower($class)); ?>
                <div class="sa-rec-card sa-<?php echo sanitize_title($class); ?>">
                    <div class="sa-class-header">
                        <img src="https://wow.zamimg.com/images/wow/icons/large/class_<?php echo $slug; ?>.jpg">
                        <span><?php echo strtoupper($class); ?></span>
                    </div>
                    <?php foreach ($specs as $spec => $pri): if ($pri === 'closed') continue; ?>
                        <div class="sa-spec-row">
                            <span class="sa-spec-name"><?php echo $spec; ?></span>
                            <span class="sa-spec-dot" style="color:<?php echo ($pri === 'high' ? '#ff4d4d' : '#46b450'); ?>;">●</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php return ob_get_clean();
});

/* ==========================================================================
   2. ADMIN: WP-BLAUW INTERFACE
   ========================================================================== */
function sa_status_recruitment() {
    $classes = [
        'Death Knight' => ['Blood', 'Frost', 'Unholy'], 'Demon Hunter' => ['Havoc', 'Vengeance'],
        'Druid' => ['Balance', 'Feral', 'Guardian', 'Restoration'], 'Evoker' => ['Augmentation', 'Devastation', 'Preservation'],
        'Hunter' => ['Beast Mastery', 'Marksmanship', 'Survival'], 'Mage' => ['Arcane', 'Fire', 'Frost'],
        'Monk' => ['Brewmaster', 'Mistweaver', 'Windwalker'], 'Paladin' => ['Holy', 'Protection', 'Retribution'],
        'Priest' => ['Discipline', 'Holy', 'Shadow'], 'Rogue' => ['Assassination', 'Outlaw', 'Subtlety'],
        'Shaman' => ['Elemental', 'Enhancement', 'Restoration'], 'Warlock' => ['Affliction', 'Demonology', 'Destruction'],
        'Warrior' => ['Arms', 'Fury', 'Protection']
    ];
    $current = get_option('sa_recruitment_status', []);
    $current_html = get_option('sa_recruitment_html', '');
    $current_logo = get_option('sa_recruitment_logo', '');
    ?>
    <style>
        .sa-adm-container { margin: 20px 20px 0 0; background: #f0f0f1; border: 1px solid #ccd0d4; border-radius: 4px; overflow: hidden; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; }
        .sa-adm-header { background: #2271b1; color: #fff; padding: 15px 20px; display: flex; align-items: center; gap: 12px; }
        .sa-adm-header h2 { color: #fff; margin: 0; font-size: 18px; }
        .sa-adm-body { padding: 20px; }
        .sa-adm-editors { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px; }
        .sa-adm-box { background: #fff; border: 1px solid #ccd0d4; padding: 15px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
        .sa-adm-label { font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block; color: #1d2327; }
        .sa-adm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 15px; }
        .sa-adm-card { background: #fff; border: 1px solid #ccd0d4; padding: 12px; border-radius: 4px; }
        .sa-adm-card-head { display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 10px; }
        .sa-adm-card-head img { width: 34px; height: 34px; border-radius: 3px; border: 1px solid #2271b1; }
        .sa-adm-footer { background: #fff; border-top: 1px solid #ccd0d4; padding: 15px 20px; text-align: right; }
        .sa-adm-save-btn { background: #2271b1 !important; color: #fff !important; padding: 12px 30px !important; font-weight: 600 !important; cursor: pointer; border-radius: 3px !important; border: none !important; }
        .sa-adm-save-btn:hover { background: #135e96 !important; }
    </style>

    <div class="sa-adm-container">
        <div class="sa-adm-header">
            <img src="https://slayeralliance.com/wp-content/uploads/2025/12/schield.png" style="height:40px; background:#fff; border-radius:50%; padding:2px;">
            <h2>Recruitment Module Management</h2>
        </div>
        <form id="sa-final-form">
            <div class="sa-adm-body">
                <div class="sa-adm-editors">
                    <div class="sa-adm-box">
                        <label class="sa-adm-label">Inhoud Centrale Blok (HTML)</label>
                        <textarea name="sa_html" style="width:100%; height:100px; border:1px solid #ccd0d4; font-family:monospace;"><?php echo esc_textarea(stripslashes($current_html)); ?></textarea>
                    </div>
                    <div class="sa-adm-box">
                        <label class="sa-adm-label">Logo URL (Onderste vak)</label>
                        <input type="text" name="sa_logo" value="<?php echo esc_attr($current_logo); ?>" style="width:100%; border:1px solid #ccd0d4; padding:5px;">
                        <div style="margin-top:10px; text-align:center;">
                            <img src="<?php echo esc_url($current_logo); ?>" style="max-height:45px;">
                        </div>
                    </div>
                </div>
                <div class="sa-adm-grid">
                    <?php foreach ($classes as $class => $specs): $slug = str_replace(' ', '', strtolower($class)); ?>
                        <div class="sa-adm-card">
                            <div class="sa-adm-card-head">
                                <img src="https://wow.zamimg.com/images/wow/icons/large/class_<?php echo $slug; ?>.jpg">
                                <strong><?php echo strtoupper($class); ?></strong>
                            </div>
                            <?php foreach ($specs as $spec): $v = $current[$class][$spec] ?? 'low'; ?>
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                    <span style="font-size:12px; color:#555;"><?php echo $spec; ?></span>
                                    <select name="status[<?php echo $class; ?>][<?php echo $spec; ?>]" style="font-size:11px; height:24px;">
                                        <option value="closed" <?php selected($v, 'closed'); ?>>Closed</option>
                                        <option value="low" <?php selected($v, 'low'); ?>>Low</option>
                                        <option value="high" <?php selected($v, 'high'); ?>>High</option>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="sa-adm-footer"><button type="submit" class="sa-adm-save-btn">Instellingen Opslaan</button></div>
        </form>
    </div>
    <script>
    jQuery(function($){
        $('#sa-final-form').on('submit', function(e){
            e.preventDefault();
            $.post(ajaxurl, { action: 'sa_save_mega_rec', data: $(this).serialize() }, function() { 
                alert('Website succesvol bijgewerkt!'); 
                location.reload(); 
            });
        });
    });
    </script>
    <?php
}

add_action('wp_ajax_sa_save_mega_rec', function() {
    parse_str($_POST['data'], $out);
    update_option('sa_recruitment_status', $out['status'] ?? []);
    update_option('sa_recruitment_html', $out['sa_html'] ?? '');
    update_option('sa_recruitment_logo', $out['sa_logo'] ?? '');
    wp_send_json_success();
});