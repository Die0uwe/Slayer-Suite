<?php
/**
 * Module: Backup Manager
 * Stijl: WordPress Admin (Grijs/Blauw/Rood)
 * Inclusief: SQL Console met foutafhandeling binnen de module.
 */

if (!defined('ABSPATH')) exit;

// --- 1. LOGICA: ACTIES AFHANDELING ---
add_action('admin_init', 'sa_handle_backup_manager_actions');

function sa_handle_backup_manager_actions() {
    global $wpdb;

    if (!isset($_POST['sa_backup_action'])) return;
    if (!current_user_can('manage_options')) wp_die('Onvoldoende rechten.');

    $action = $_POST['sa_backup_action'];

    switch ($action) {
        case 'download_sql':
            sa_generate_db_sql_limited();
            break;

        case 'download_zip':
            sa_generate_files_zip_limited();
            break;

        case 'import_sql':
            sa_handle_sql_import();
            break;

        case 'execute_sql':
            if (!empty($_POST['sa_custom_sql'])) {
                $query = stripslashes($_POST['sa_custom_sql']);
                $query = trim($query);

                // Verberg fouten bovenaan het scherm
                $wpdb->hide_errors(); 

                $result = $wpdb->query($query);

                if ($result === false) {
                    set_transient('sa_backup_msg', [
                        'type' => 'error', 
                        'text' => "SQL Fout: " . $wpdb->last_error
                    ], 30);
                } else {
                    set_transient('sa_backup_msg', [
                        'type' => 'success', 
                        'text' => "Query succesvol uitgevoerd! ($result rijen geraakt)"
                    ], 30);
                }
            }
            break;

        case 'reset_db':
            $tables = $wpdb->get_col($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->prefix . 'sa_%'));
            foreach ($tables as $table) {
                $wpdb->query("TRUNCATE TABLE $table");
            }
            set_transient('sa_backup_msg', ['type' => 'warning', 'text' => "Database Reset voltooid. Alle tabellen zijn leeg."], 30);
            break;
    }
}

// --- 2. HELPERS (DOWNLOADS & IMPORTS) ---

function sa_handle_sql_import() {
    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) return;
    global $wpdb;
    $sql_content = file_get_contents($_FILES['sql_file']['tmp_name']);
    $queries = preg_split("/;+(?=(?:[^'\"]*['\"][^'\"]*['\"])*[^'\"]*$)/", $sql_content);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) $wpdb->query($query);
    }
    set_transient('sa_backup_msg', ['type' => 'success', 'text' => "Backup succesvol geïmporteerd!"], 30);
}

function sa_generate_files_zip_limited() {
    $plugin_path = plugin_dir_path(dirname(__FILE__)); 
    $zip_name = 'sa-logic-backup-' . date('Y-m-d') . '.zip';
    $zip_file = sys_get_temp_dir() . '/' . $zip_name;
    $zip = new ZipArchive();
    if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $main_file = $plugin_path . 'dieouwe-master-suite.php';
        if (file_exists($main_file)) $zip->addFile($main_file, 'dieouwe-master-suite.php');
        $mods = $plugin_path . 'modules/';
        if (is_dir($mods)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($mods), RecursiveIteratorIterator::LEAVES_ONLY);
            foreach ($files as $file) {
                if (!$file->isDir()) $zip->addFile($file->getRealPath(), 'modules/' . substr($file->getRealPath(), strlen($mods)));
            }
        }
        $zip->close();
        sa_download_file($zip_file, $zip_name, 'application/zip');
    }
}

function sa_generate_db_sql_limited() {
    global $wpdb;
    $sql = "-- Alliance Suite SQL Backup\n\n";
    $tables = $wpdb->get_col($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->prefix . 'sa_%'));
    foreach ($tables as $table) {
        $create = $wpdb->get_row("SHOW CREATE TABLE $table", ARRAY_N);
        $sql .= "DROP TABLE IF EXISTS `$table`;\n" . $create[1] . ";\n\n";
        $rows = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);
        foreach ($rows as $row) {
            $vals = array_map(function($v) { return is_null($v) ? "NULL" : "'" . esc_sql($v) . "'"; }, array_values($row));
            $sql .= "INSERT INTO `$table` VALUES (" . implode(", ", $vals) . ");\n";
        }
        $sql .= "\n";
    }
    sa_download_string($sql, 'sa-db-backup-' . date('Y-m-d') . '.sql', 'application/sql');
}

function sa_download_file($f, $n, $t) { if (ob_get_length()) ob_clean(); header("Content-Type: $t"); header("Content-Disposition: attachment; filename=\"$n\""); readfile($f); unlink($f); exit; }
function sa_download_string($s, $n, $t) { if (ob_get_length()) ob_clean(); header("Content-Type: $t"); header("Content-Disposition: attachment; filename=\"$n\""); echo $s; exit; }

// --- 3. INTERFACE (WP ADMIN STYLE) ---

function sa_status_backup_manager() {
    global $wpdb;
    $tables = $wpdb->get_col($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->prefix . 'sa_%'));
    $msg = get_transient('sa_backup_msg');
    delete_transient('sa_backup_msg');
    ?>
    <div style="background:#f0f0f1; padding:20px; border-radius:5px; font-family: sans-serif;">
        <h2 style="color:#a11692; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
            <span style="background:#a11692; color:#fff; padding:5px; border-radius:4px;">💾</span> Backup Manager
        </h2>

        <?php if ($msg): ?>
            <div style="padding:12px; border-left:4px solid <?php echo ($msg['type']=='error' ? '#d63638' : ($msg['type']=='warning' ? '#ffb900' : '#46b450')); ?>; background:#fff; margin-bottom:20px; box-shadow:0 1px 1px rgba(0,0,0,.04);">
                <strong style="color:<?php echo ($msg['type']=='error' ? '#d63638' : '#333'); ?>;">
                    <?php echo ($msg['type']=='error' ? '🔴 Fout: ' : '🟢 Status: '); ?>
                </strong>
                <?php echo esc_html($msg['text']); ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            
            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:3px; box-shadow:0 1px 1px rgba(0,0,0,.04);">
                <h3 style="margin-top:0; font-size:14px; border-bottom:1px solid #eee; padding-bottom:10px;">📦 Bestanden & Data</h3>
                <form method="post"><input type="hidden" name="sa_backup_action" value="download_sql"><button type="submit" class="button button-primary" style="width:100%; margin-bottom:10px;">Download Data (.sql)</button></form>
                <form method="post"><input type="hidden" name="sa_backup_action" value="download_zip"><button type="submit" class="button" style="width:100%; margin-bottom:20px;">Download Code (.zip)</button></form>
                
                <h4 style="font-size:13px; margin-bottom:8px;">Restore via SQL</h4>
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="sql_file" accept=".sql" required style="font-size:11px; margin-bottom:10px; width:100%;">
                    <input type="hidden" name="sa_backup_action" value="import_sql">
                    <button type="submit" class="button button-secondary" style="width:100%;" onclick="return confirm('Dit overschrijft huidige data. Doorgaan?');">Importeer SQL</button>
                </form>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-top:3px solid #2271b1; border-radius:3px; box-shadow:0 1px 1px rgba(0,0,0,.04);">
                <h3 style="margin-top:0; font-size:14px; color:#2271b1;">💻 SQL Console</h3>
                <p style="font-size:11px; color:#646970; margin-bottom:10px;">Voer SQL strings uit op de <code><?php echo $wpdb->prefix; ?>sa_</code> tabellen.</p>
                <form method="post" onsubmit="return confirm('Query uitvoeren?');">
                    <textarea name="sa_custom_sql" spellcheck="false" style="width:100%; height:110px; background:#f6f7f7; color:#1d2327; border:1px solid #8c8f94; font-family:monospace; font-size:12px; padding:8px; margin-bottom:10px; border-radius:3px;" placeholder="SELECT * FROM ..."></textarea>
                    <input type="hidden" name="sa_backup_action" value="execute_sql">
                    <button type="submit" class="button button-primary" style="width:100%;">Execute String</button>
                </form>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:3px; box-shadow:0 1px 1px rgba(0,0,0,.04);">
                <h3 style="margin-top:0; font-size:14px;">📊 Systeem Status</h3>
                <table style="width:100%; font-size:12px; margin-bottom:15px;">
                    <tr><td>Tabellen:</td><td style="text-align:right;"><strong><?php echo count($tables); ?></strong></td></tr>
                    <tr><td>Prefix:</td><td style="text-align:right;"><code><?php echo $wpdb->prefix; ?>sa_</code></td></tr>
                </table>
                <div style="background:#fcf0f1; padding:15px; border:1px solid #f5c2c7; border-radius:3px;">
                    <h4 style="color:#d63638; margin:0 0 10px 0; font-size:13px;">⚠️ Danger Zone</h4>
                    <form method="post">
                        <input type="hidden" name="sa_backup_action" value="reset_db">
                        <button type="submit" class="button button-link-delete" style="width:100%; text-align:center; background:#d63638; color:#fff; padding:5px; text-decoration:none; border-radius:3px;" onclick="return confirm('LET OP: Dit wist alle plugin data!');">RESET TABELLEN</button>
                    </form>
                </div>
            </div>

        </div>

        <div style="margin-top:25px; background:#fff; border:1px solid #ccd0d4; border-radius:3px; overflow:hidden;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="font-weight:bold; padding:12px;">Tabelnaam</th>
                        <th style="font-weight:bold; padding:12px; width:100px;">Records</th>
                        <th style="font-weight:bold; padding:12px; width:100px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables as $t): $c = $wpdb->get_var("SELECT COUNT(*) FROM $t"); ?>
                    <tr>
                        <td style="padding:10px;"><code><?php echo $t; ?></code></td>
                        <td style="padding:10px;"><?php echo $c; ?></td>
                        <td style="padding:10px;"><?php echo ($c > 0) ? '<span style="color:#46b450;">● Actief</span>' : '<span style="color:#c3c4c7;">○ Leeg</span>'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
add_action('sa_render_tab_backup_manager', 'sa_status_backup_manager');