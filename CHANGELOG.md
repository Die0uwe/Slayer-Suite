# Changelog — Slayer Alliance Master Suite

All notable changes to this project will be documented in this file.

---

## [v25.12.30] — 2026-06-04 · Initial GitHub Release

### Added
- Initial commit van de volledige Slayer Alliance Master Suite codebase
- Core plugin: `dieouwe-master-suite.php` (362 regels) — module loader, Blizzard API engine, admin dashboard
- Module: `armory.php` — 3D character view + Raider.io integratie
- Module: `roster.php` — guild ledenlijst + database sync
- Module: `collections.php` — Mounts & Pets ranglijst
- Module: `delves.php` — Bountiful Delves voortgang tracking
- Module: `recruitment.php` — class/rol recruitment blok
- Module: `realm_status.php` + `REALM_STATUS_II.php` — realm monitor
- Module: `sync_manager.php` — Blizzard API synchronisatie engine
- Module: `CurseForge.php` — addon release tracker
- Module: `discord_manager.php` — Discord webhook integratie
- Module: `backup-manager.php` — database backup + SQL console
- Module: `sa-debug-tool.php` — debug console
- Module: `shortcode_guide.php` — shortcode bibliotheek met click-to-copy
- Module: `buttons.php` — navigatieknoppen
- Module: `Twitch.php` — Twitch integratie (basis)
- Module: `modules.php` — module beheer panel
- Placeholder: `Google.php`, `Theme.php`, `decor_browser.php` (Sprint 2)
- Licentie: GPL v3

### Known Issues (Sprint Backlog)
- `Google.php` leeg — credentials structuur nog niet gebouwd
- `Theme.php` leeg — centrale font loader nog bouwen
- `decor_browser.php` leeg — housing decor module nog bouwen
- `realm_status.php` + `REALM_STATUS_II.php` — samenvoeging gepland Sprint 2
- Hardcoded `gvxx_` DB prefix in sommige modules — fix gepland Sprint 2
- Credentials nog niet volledig via `wp-config.php` constants — fix Sprint 1

---

*Slayer Alliance Master Suite · DieOuwe · slayeralliance.com*
