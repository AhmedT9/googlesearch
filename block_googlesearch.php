<?php
// Definition der Klasse block_googlesearch, die von block_base erbt
class block_googlesearch extends block_base {
    // Initialisierungsmethode des Blocks
    public function init() {
        // Setzen des Titels des Blocks, der aus den Sprachdateien geladen wird
        $this->title = get_string('pluginname', 'block_googlesearch');
    }

    // Methode zur Erzeugung des Blockinhalts
    public function get_content() {
        global $PAGE; // Globales PAGE-Objekt für Zugriff auf Systemfunktionen
        // Einbinden der CSS-Datei für das Design des Blocks
        $PAGE->requires->css(new moodle_url('/blocks/googlesearch/styles.css'));
        
        // Überprüfen, ob der Inhalt bereits gesetzt ist
        if ($this->content !== null) {
            return $this->content;
        }

        // Initialisierung des Inhaltsobjekts
        $this->content         =  new stdClass;
        $this->content->footer = '';

        // URL für die Google Custom Search API mit vordefiniertem Suchbegriff "Moodle Blocks"
        $url = "https://www.googleapis.com/customsearch/v1?key=AIzaSyCxb8_Mu7jw70DYI-Xm3lfNNwCpPHZ0lQI&cx=12fb0aa4804a0447e&q=Moodle+Blocks";

        // Konfiguration der Kontextoptionen für die HTTP-Anfrage
        $contextOptions = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\n"
            ]
        ];
        // Erstellung des Kontextes aus den Optionen
        $context = stream_context_create($contextOptions);
        // Durchführung der Anfrage und Speichern des Ergebnisses
        $result = file_get_contents($url, false, $context);

        //$this->content->text = '<pre>' . htmlspecialchars($result) . '</pre>';

        // Dekodieren des Ergebnisses von JSON in ein Array
        $searchResults = json_decode($result, true);

        // Überprüfen auf Fehler bei der Anfrage
        if ($result === FALSE) {
            $responseCode = 0;
            foreach ($http_response_header as $header) {
                 if (preg_match('/^HTTP\/\d+\.\d+ (\d+)/', $header, $matches)) {
                       $responseCode = intval($matches[1]);
                       break;
                 }
            }
            // Behandlung von spezifischen HTTP-Antwortcodes
            if ($responseCode == 429) {
                $this->content->text .= 'Für heute sind keine Queries mehr vorhanden';
                return $this->content;
            } else {
                $this->content->text .= 'Beim Durchführen der Suche ist ein Fehler aufgetreten';
                return $this->content;
            }
        }

        // Überprüfen, ob Suchergebnisse vorhanden sind
        if (!empty($searchResults['items'])) {
            $html = '<ul class="google-search-results">';
            foreach ($searchResults['items'] as $item) {
                // Verarbeitung des continue-Parameters in der URL
                if (strpos($item['link'], 'continue=') !== false) {
                    $parsedUrl = parse_url($item['link']);
                    $queryParams = [];
                    parse_str($parsedUrl['query'], $queryParams);
                    if (isset($queryParams['continue'])) {
                        $item['link'] = urldecode($queryParams['continue']);
                    }
                }

                // Hinzufügen des Suchergebnisses zur Ausgabe
                $html .= '<li><a href="' . htmlspecialchars($item['link']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
            }
            $html .= '</ul>';
            $this->content->text = $html;
        } else {
            $this->content->text = 'Keine Ergebnisse gefunden.';
        }

        // Rückgabe des Blockinhalts
        return $this->content;
    }
}
