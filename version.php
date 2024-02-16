<?php
// Sicherstellen, dass das Skript nur im Kontext von Moodle ausgeführt wird
defined('MOODLE_INTERNAL') || die();

// Zuweisung der Plugin-Identifikation, inklusive des Namens des Plugins
$plugin->component = 'block_googlesearch'; // Der Name des Plugins

// Festlegung der aktuellen Version des Plugins
// Dies wird verwendet, um Updates zu verwalten und sicherzustellen, dass das Plugin mit der Moodle-Version kompatibel ist
$plugin->version   = 2024021001; // Beispiel: Veröffentlicht am 10. Februar 2024, Version 01

// Definition der Moodle-Hauptversion, die mindestens erforderlich ist, um dieses Plugin zu verwenden
// Dies stellt sicher, dass das Plugin nur auf unterstützten Moodle-Versionen installiert wird
$plugin->requires  = 2019111800; // Beispiel: Erfordert Moodle-Version 3.8 (veröffentlicht im November 2019)

// Angabe der Reife des Plugins
// MATURITY_STABLE zeigt an, dass das Plugin als stabil betrachtet wird und für den Einsatz in Produktivumgebungen geeignet ist
$plugin->maturity  = MATURITY_STABLE;

// Definition der Veröffentlichungsnummer oder des Namens dieser spezifischen Plugin-Version
// Dies wird oft verwendet, um Versionen benutzerfreundlich zu identifizieren
$plugin->release   = 'v1.0'; // Version 1.0 des Plugins

