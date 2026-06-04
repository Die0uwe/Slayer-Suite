README: Slayer Alliance Master Suite - Modulaire Transformatie (v25)
PROJECT OVERZICHT De Slayer Alliance Master Suite is getransformeerd van een enkel, groot script naar een modulair framework. De plugin bestaat nu uit een centrale "Core" die de algemene instellingen en API-communicatie beheert, terwijl specifieke onderdelen (zoals Armory, Roster en Delves) als zelfstandige modules in een aparte map opereren.

BELANGRIJKSTE WIJZIGINGEN

Architectuur & Core:

Centralisatie van API-instellingen: Blizzard Client ID, Secret, Realm en Guild worden centraal opgeslagen in de Core en gedeeld met alle modules.

Automatische Module Scanner: De Core scant automatisch de /modules/ submap op .php bestanden en laadt deze in als ze zijn geactiveerd.

Dynamisch Tab-systeem: Geactiveerde modules verschijnen direct als eigen functionele tabbladen in het dashboard.

Visueel Dashboard (v25 Stijl):

Systeemoverzicht: Behoud van de visuele statusblokken (gekleurde kaders) voor een direct overzicht van de Blizzard API-verbinding en de database-status.

Live Status Monitoring: De Core toont nu per actieve module of deze operationeel is (bijv. "Active", "Ready" of "No Data").

Documentatie & Beheer:

Centrale Handleiding: Elke module voegt automatisch zijn eigen instructies en shortcodes toe aan de algemene handleiding via een filter-systeem.

Module Beheer: In de Core Config kunnen modules per stuk aan- of uitgezet worden zonder de website te verstoren.

BESTANDSSTRUCTUUR dieouwe-master-suite/ |-- dieouwe-master-suite.php (De Core: Beheert instellingen en lader) |-- modules/ |-- armory.php (Module: 3D Character View & R.io) |-- roster.php (Module: Ledenlijst & Sync) |-- delves.php (Module: Delve voortgang tracking)

INSTALLATIE & GEBRUIK

Plaats de hoofdplugin in de /plugins/ map.

Zorg dat alle modules in de /modules/ submap staan.

Vul de Blizzard API keys in bij de Core Config tab.

Activeer de gewenste modules onder Module Beheer.

Raadpleeg de Handleiding tab voor de specifieke shortcodes per module.

Gegenereerd voor Slayer Alliance Suite v25.2.0