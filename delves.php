<?php
/**
 * Module: Delves Status (v25.3.16)
 * Layout: Admin grid 3-wide (optimized space), enhanced visuals.
 */

if (!defined('ABSPATH')) exit;

/* ==========================================================================
   1. FRONT-END: [sa_delve_status]
   ========================================================================== */
add_shortcode('sa_delve_status', function() {
    $act = get_option('sa_act_delves', []); 
    $imgs = get_option('sa_delve_imgs', []);
    if(empty($act)) return "";

    ob_start(); ?>
    <div class="sa-delve-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; font-family: sans-serif;">
        <?php foreach ($act as $d): 
            $slug = sanitize_title($d);
            $u = !empty($imgs[$slug]) ? $imgs[$slug] : ''; 
        ?>
            <div style="background: #0a0a0f; border: 1px solid #a11692; border-radius: 10px; overflow: hidden; position: relative; text-align: center; color: #fff;">
                <div style="height: 120px; background: #111;">
                    <?php if($u): ?><img src="<?php echo esc_url($u); ?>" style="width:100%; height:100%; object-fit:cover;"><?php endif; ?>
                </div>
                <div style="padding: 10px; font-size: 13px; font-weight: bold; background: #1e1e2c; border-top: 1px solid #a11692;">
                    <?php echo stripslashes($d); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <style>@media (max-width: 800px) { .sa-delve-grid { grid-template-columns: repeat(2, 1fr) !important; } }</style>
    <?php
    return ob_get_clean();
});

/* ==========================================================================
   2. ADMIN: DELVES CONFIG (Full Width 3-Column Layout)
   ========================================================================== */
function sa_status_delves() {
    $delve_list = [
        "Earthcrawl Mines", "Kriegval's Rest", "Fungal Folly", "The Whirring Field",
        "The Waterworks", "The Dread Pit", "Excavation Site 9", "The Bleeding Den",
        "Skittering Breach", "Nightfall Sanctum", "The Sinkhole", "Mycomancer Cavern",
        "The Spiral Weave", "Tak-Rethan Abyss", "The Underkeep", "The Lost Nerubian Lair"
    ];
    
    $active = get_option('sa_act_delves', []);
    $imgs = get_option('sa_delve_imgs', []);
    wp_enqueue_media();
    ?>
    <div class="sa-card" style="background:#fff; padding:30px; border-left:5px solid #a11692;">
        <h2 style="color:#a11692; margin-top:0; font-size:24px;">🕯️ Delve Management</h2>
        <p style="margin-bottom:25px; color:#555;">Selecteer de actieve Bountiful Delves en beheer de visuals voor de front-end.</p>

        <form id="sa-delve-form" method="post">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <?php foreach($delve_list as $d): 
                    $slug = sanitize_title($d); 
                    $u = $imgs[$slug] ?? ''; 
                ?>
                    <div style="display:flex; align-items:center; gap:15px; padding:15px; border:2px solid #eee; border-radius:12px; background:#fff; transition: 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        
                        <div id="p-<?php echo $slug; ?>" 
                             style="width:80px; height:80px; background:<?php echo $u ? "url('$u') center/cover" : "#f0f0f0"; ?>; border:1px solid #ddd; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                             <?php if(!$u): ?><span style="font-size:10px; color:#aaa;">Geen foto</span><?php endif; ?>
                        </div>
                        
                        <div style="flex-grow:1;">
                            <label style="font-weight:800; font-size:14px; display:block; margin-bottom:8px; cursor:pointer; color:#333;">
                                <input type="checkbox" name="delves[]" value="<?php echo $d; ?>" <?php checked(in_array($d, $active)); ?> style="margin-right:8px;"> 
                                <?php echo $d; ?>
                            </label>
                            
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input type="hidden" name="delve_imgs[<?php echo $slug; ?>]" id="i-<?php echo $slug; ?>" value="<?php echo esc_attr($u); ?>">
                                <button type="button" class="sa-m-btn button button-secondary" data-id="<?php echo $slug; ?>" style="background:#f4f4f4; border-color:#ccc; font-weight:bold;">KIES FOTO</button>
                                <?php if($u): ?>
                                    <span style="color:#46b450; font-size:16px;">✔</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top:40px; padding-top:20px; border-top:2px solid #f0f0f0; display:flex; align-items:center; gap:20px;">
                <button type="submit" id="sa-save-delves-btn" class="button button-primary" 
                        style="background:#a11692; border:none; padding:0 40px; height:45px; font-weight:bold; font-size:16px; border-radius:6px; box-shadow: 0 4px 10px rgba(161, 22, 146, 0.3);">
                    💾 ALLES OPSLAAN
                </button>
                <div id="d-msg" style="color:#46b450; font-weight:bold; display:none; padding:10px 20px; border-radius:6px; background:#e7f7ed; border:1px solid #46b450;">
                    ✅ Wijzigingen succesvol doorgevoerd!
                </div>
            </div>
        </form>
    </div>

    <script>
    jQuery(function($){
        var sa_ajax_url = window.ajaxurl || '/wp-admin/admin-ajax.php';

        $('.sa-m-btn').on('click', function(e){
            e.preventDefault();
            var id = $(this).data('id');
            var frame = wp.media({ title: 'Selecteer Delve Visual', multiple: false }).on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                $('#i-'+id).val(attachment.url);
                $('#p-'+id).css({'background-image': 'url('+attachment.url+')', 'background-size': 'cover'}).html('');
            }).open();
        });

        $('#sa-delve-form').on('submit', function(e){
            e.preventDefault();
            var btn = $('#sa-save-delves-btn');
            btn.prop('disabled', true).text('BEZIG MET OPSLAAN...');
            
            $.ajax({
                url: sa_ajax_url,
                type: 'POST',
                data: {
                    action: 'sa_save_delves',
                    data: $(this).serialize()
                },
                success: function() {
                    btn.prop('disabled', false).text('💾 ALLES OPSLAAN');
                    $('#d-msg').fadeIn().delay(3000).fadeOut();
                }
            });
        });
    });
    </script>
    <?php
}

/* ==========================================================================
   3. AJAX HANDLER
   ========================================================================== */
add_action('wp_ajax_sa_save_delves', function() {
    parse_str($_POST['data'], $f);
    update_option('sa_act_delves', isset($f['delves']) ? $f['delves'] : []);
    update_option('sa_delve_imgs', isset($f['delve_imgs']) ? $f['delve_imgs'] : []);
    wp_send_json_success();
});