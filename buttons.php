<?php
/**
 * Module: Buttons
 */
if (!defined('ABSPATH')) exit;

add_shortcode('sa_buttons', function() {
    ob_start();
    ?>
    <style>
        .sa-btn-group { display: flex; gap: 15px; justify-content: center; margin: 20px 0; flex-wrap: wrap; }
        .sa-btn-link { 
            background: #a11692; color: #fff !important; padding: 12px 25px; 
            text-decoration: none !important; border-radius: 4px; font-weight: bold;
            transition: 0.3s; border: 1px solid #333; font-size: 14px;
        }
        .sa-btn-link:hover { background: #fff; color: #a11692 !important; border-color: #a11692; }
    </style>
    <div class="sa-btn-group">
        <a href="/armory" class="sa-btn-link">🛡️ ARMORY</a>
        <a href="/roster" class="sa-btn-link">👥 ROSTER</a>
        <a href="/recruitment" class="sa-btn-link">⚔️ RECRUITMENT</a>
    </div>
    <?php
    return ob_get_clean();
});

function sa_status_buttons() {
    echo '<div class="sa-card"><h3>Navigatie Knoppen</h3><p>Shortcode: <code>[sa_buttons]</code> is nu actief.</p></div>';
}