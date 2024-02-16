<?php
// Sicherstellen, dass das Skript nur im Kontext von Moodle ausgeführt wird
defined('MOODLE_INTERNAL') || die();

// Definition der Fähigkeiten (Capabilities), die das Plugin benötigt
$capabilities = array(
    // Fähigkeit, eine Instanz des Blocks zu "Meine Startseite" hinzuzufügen
    'block/googlesearch:myaddinstance' => array(
        'captype' => 'write', // Typ der Fähigkeit: Schreibzugriff
        'contextlevel' => CONTEXT_SYSTEM, // Kontextebene, auf der die Fähigkeit gilt: Systemweit
        'archetypes' => array(
            'user' => CAP_ALLOW // Standardarchetyp, dem diese Fähigkeit erlaubt ist: Benutzer
        ),

        // Kopieren der Berechtigungen von einer bestehenden Fähigkeit
        'clonepermissionsfrom' => 'moodle/my:addinstance',
    ),

    // Fähigkeit, eine Instanz des Blocks zu einem Kurs hinzuzufügen
    'block/googlesearch:addinstance' => array(
        'riskbitmask' => RISK_XSS, // Risikokennzeichnung: Potenzielles XSS-Risiko

        'captype' => 'write', // Typ der Fähigkeit: Schreibzugriff
        'contextlevel' => CONTEXT_BLOCK, // Kontextebene, auf der die Fähigkeit gilt: Blockebene
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW, // Standardarchetyp, dem diese Fähigkeit erlaubt ist: Bearbeitende Lehrkräfte
            'manager' => CAP_ALLOW // Standardarchetyp, dem diese Fähigkeit erlaubt ist: Manager
        ),

        // Kopieren der Berechtigungen von einer bestehenden Fähigkeit
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
);

