<?php
/**
 * Slayer Alliance - Master Suite Discord Module (Gallerij Versie)
 */

class DiscordManager {

    // Gebruik de URL die je eerder aanmaakte
    private $webhookUrl   = 'https://discord.com/api/webhooks/1457882425387516048/F341J6ktPyVvQn9AO5Qv7kttkFVVT9hMRwd5adCaFN7i-zr8j4U_0msjm7_b2uV9x5Zn'; 
    private $redirectUri  = 'http://www.slayeralliance.com/callback.php';

    /**
     * Luister-functie: Deze vangt de Discord data op
     */
    public function handleIncomingDiscordData() {
        if (isset($_GET['discord_sync'])) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (!empty($data['attachments'][0]['url'])) {
                $this->processImageToGallery($data['attachments'][0]['url'], $data['author']['username']);
            }
            exit; // Stop verdere WordPress loding
        }
    }

    /**
     * Verwerk de afbeelding naar Categorie 60
     */
    private function processImageToGallery($url, $user) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $image_id = media_sideload_image($url, 0, "Discord Upload van $user", 'id');

        if (!is_wp_error($image_id)) {
            wp_insert_post(array(
                'post_title'    => "Slayer Alliance Gallery - $user",
                'post_status'   => 'publish',
                'post_category' => array( 60 ),
                'meta_input'    => array('_thumbnail_id' => $image_id)
            ));
        }
    }

    public function getModuleContent() {
        ob_start();
        ?>
        <div class="suite-content-box" style="margin-top: 10px; padding: 20px; background: #fff; border: 1px solid #ddd; clear: both;">
            <h2 style="color: #7289da; margin-top: 0;">🛡️ Slayer Alliance Discord Sync</h2>
            <p>Status: <strong>Actief (Luistert op /?discord_sync=1)</strong></p>
            <hr>
            <div style="padding: 15px; background: #f0f0f0; border-radius: 5px;">
                <strong>Webhook Doel:</strong><br>
                <code><?php echo $this->webhookUrl; ?></code>
            </div>
            <p style="font-size: 0.9em; color: #666;">Alle afbeeldingen gepost in het gekoppelde kanaal komen automatisch in Categorie 60.</p>
        </div>
        <?php
        return ob_get_clean();
    }
}