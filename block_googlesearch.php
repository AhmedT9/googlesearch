
<?php
class block_googlesearch extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_googlesearch');
    }

    public function get_content() {
        global $PAGE;
        $PAGE->requires->css(new moodle_url('/blocks/googlesearch/styles.css'));
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->footer = '';

        $url = "https://www.googleapis.com/customsearch/v1?key=AIzaSyAGMxTcZt71NgJlUNNVauOuQqXJNObGL8o&cx=954542981e8a640fb&q=Moodle+Blocks";

        $contextOptions = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\n"
            ]
        ];
        $context = stream_context_create($contextOptions);
        $result = file_get_contents($url, false, $context);

        //$this->content->text = '<pre>' . htmlspecialchars($result) . '</pre>';

        $searchResults = json_decode($result, true);

        if (!empty($searchResults['items'])) {
            $html = '<ul class="google-search-results">';
            foreach ($searchResults['items'] as $item) {
                if (strpos($item['link'], 'continue=') !== false) {
                    $parsedUrl = parse_url($item['link']);
                    $queryParams = [];
                    parse_str($parsedUrl['query'], $queryParams);
                    if (isset($queryParams['continue'])) {
                        $item['link'] = urldecode($queryParams['continue']);
                    }
                }

                $html .= '<li><a href="' . htmlspecialchars($item['link']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
            }
            $html .= '</ul>';
            $this->content->text = $html;
        } else {
            $this->content->text = 'Keine Ergebnisse gefunden.';
        }

        return $this->content;
    }
}
