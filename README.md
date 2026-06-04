# Slayer Alliance Master Suite

**WordPress plugin voor [slayeralliance.com](https://slayeralliance.com)**  
Guild: Slayer Alliance · Realm: Sporeggar (EU) · Versie: v25.12.30

---

## Overzicht

De Slayer Alliance Master Suite is een modulaire WordPress plugin die de guild-website aandrijft. Een centrale core beheert de Blizzard API-verbinding en laadt modules dynamisch in.

## Modules

| Module | Shortcode | Functie |
|---|---|---|
| armory.php | `[sa_armory]` | 3D Character View + Raider.io |
| roster.php | `[guild_roster]` | Ledenlijst vanuit database |
| collections.php | `[sa_collections]` | Mounts & Pets ranglijst |
| delves.php | `[sa_delve_status]` | Bountiful Delves voortgang |
| recruitment.php | `[guild_recruitment]` | Recruitment blok per class/rol |
| realm_status.php | `[sa_realm_status]` | Live realm online/offline |
| CurseForge.php | `[sa_addon_list]` | Addon releases |
| shortcode_guide.php | *(admin only)* | Shortcode bibliotheek |
| buttons.php | `[sa_buttons]` | Navigatieknoppen |
| sync_manager.php | *(admin only)* | Blizzard API sync engine |
| discord_manager.php | *(intern)* | Discord webhook integratie |
| backup-manager.php | *(admin only)* | Database backup console |
| sa-debug-tool.php | *(admin only)* | Debug console |

## Installatie

1. Upload de plugin map naar `/wp-content/plugins/`
2. Zet credentials in `wp-config.php` (zie hieronder)
3. Activeer de plugin via WordPress admin
4. Activeer modules onder **SA Master → Module Beheer**

## Vereiste wp-config.php constants

```php
define('SA_BLIZZARD_CLIENT_ID',     'jouw_client_id');
define('SA_BLIZZARD_CLIENT_SECRET', 'jouw_secret');
define('SA_GOOGLE_CLIENT_ID',       'jouw_google_id');
define('SA_GOOGLE_CLIENT_SECRET',   'jouw_google_secret');
```

## Bestandsstructuur

```
dieouwe-master-suite/
├── dieouwe-master-suite.php   ← Core
├── modules.php
├── Theme.php
├── armory.php
├── roster.php
├── collections.php
├── delves.php
├── recruitment.php
├── realm_status.php
├── REALM_STATUS_II.php
├── sync_manager.php
├── discord_manager.php
├── backup-manager.php
├── CurseForge.php
├── Google.php
├── Twitch.php
├── buttons.php
├── shortcode_guide.php
├── decor_browser.php
├── sa-debug-tool.php
└── licence_DIEOUWE.txt
```

## Bekende issues (Sprint backlog)

- [ ] `Google.php` — nog leeg, credentials structuur toevoegen
- [ ] `Theme.php` — centrale font loader nog bouwen
- [ ] `decor_browser.php` — module nog leeg
- [ ] `realm_status.php` + `REALM_STATUS_II.php` — samenvoegen tot één module
- [ ] `gvxx_` hardcoded DB prefix fixen → `$wpdb->prefix` in alle modules

## Licentie

GPL v3 — zie `licence_DIEOUWE.txt`

---

*Created by DieOuwe · [dieouwe.nl](https://www.dieouwe.nl) · [discord.gg/y8Pu5qsEbQ](https://discord.gg/y8Pu5qsEbQ)*
