# Einrichtung des Moodle-Servers

Es wurden folgende Schritte unternommen um den Server einzurichten:

1. $200 Hosting Guthaben bei digitalocean geholt, eine Linux VM wurde da erstellt.

2. Bei der Erstellung wurden direkt die angehängten ssh public keys auf der VM installiert

3. Selber einen kostenlosen DNS-Namen registriert, mithilfe von Name.com und github student developer pack

4. Nameservers und DNS Records konfiguriert

5. PostgreSQL, nginx, PHP 8.2 und Moodle installiert, Datenbank erstellt

6. Moodle System konfiguriert, kompatibilität Probleme wurden behoben

7. Den Zugriff mittels HTTPs und let's Encrypt SSL Zertifikat wurde mithilfe von Certbot abgesichert, bzw. HTTPs wurde innerhalb von Moodle erzwungen 

8. Fail2ban wurde eingerichtet und SSHD wurde entsprechend angepasst

Moodle-Admin Anmeldedaten:


      Username : admin 

   
      Password : Password1*


# Google Search Block für Moodle

Erstellung des Blocks:

Um den Google Search Block zu erstellen und in Moodle zu integrieren, wurden folgende Schritte unternommen:

-> Design und Entwicklung: Zuerst wurde die Funktionalität des Blocks entworfen und der benötigte Code entwickelt, einschließlich der Verbindung zur Google Custom Search API.

-> Stilisierung: Anschließend wurden CSS-Stile definiert, um das Aussehen der Suchergebnisse anzupassen, es gibt auch die Möglichkeit das ganze in raw JSON Format zu zeigen, es wurde aber auskommentiert .

-> Plugin-Registrierung: Die version.php Datei wurde erstellt, um das Plugin bei Moodle zu registrieren.

-> Berechtigungen definieren: In access.php wurden die benötigten Berechtigungen für den Block festgelegt.

-> Lokalisierung vorbereiten: Die Sprachdatei lang/en/block_googlesearch.php wurde hinzugefügt, um die Texte des Plugins zu definieren und eine spätere Übersetzung zu ermöglichen.

# Dateibeschreibungen:

block_googlesearch.php -> 
Diese Datei definiert die Hauptklasse des Blocks, block_googlesearch, die von block_base erbt. Sie ist verantwortlich für die Initialisierung des Blocks, das Setzen des Blocktitels und das Generieren des Inhalts, der in dem Block angezeigt wird. Die Methode get_content() verwendet die Google Custom Search API, um Suchergebnisse zu einem vordefinierten Suchbegriff ("Tunesien") zu erhalten und diese Ergebnisse in einer formatierten Liste anzuzeigen.

styles.css -> 
Die styles.css Datei enthält CSS-Regeln, die das Aussehen der Suchergebnisse im Block gestalten. Sie definiert Stile für die Liste der Suchergebnisse, wie z.B. das Entfernen von Listenaufzählungszeichen, das Hinzufügen von Abständen zwischen den Ergebnissen und das Anpassen der Farbe und Dekoration der Hyperlinks.

version.php -> 
In version.php werden die Plugin-Version, die erforderliche Moodle-Version für das Plugin, die Reife und die Release-Bezeichnung definiert. Diese Datei ist notwendig für die Moodle-Plugin-Verwaltung und hilft Moodle zu erkennen, ob das Plugin mit der installierten Moodle-Version kompatibel ist.

access.php -> 
access.php definiert die Fähigkeiten (Capabilities) des Plugins. Es legt fest, welche Benutzerrollen den Block hinzufügen dürfen und welche Berechtigungen für die Verwaltung des Blocks erforderlich sind. Dies beinhaltet Berechtigungen sowohl für das Hinzufügen des Blocks zu einem Kurs als auch zum eigenen Moodle-Dashboard.

lang/en/block_googlesearch.php -> 
Diese Sprachdatei enthält englische Zeichenketten (Strings) für das Plugin, darunter den Namen des Plugins und die Beschreibungen der Fähigkeiten. Die Verwendung von Sprachdateien ermöglicht die einfache Lokalisierung des Plugins für verschiedene Sprachen.
