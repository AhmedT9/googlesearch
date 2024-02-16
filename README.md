# Einrichtung des Moodle-Servers

## Schritt 1: VM-Erstellung und Initialsetup

1. DigitalOcean VM-Erstellung:

Zuerst wurde ein Konto bei DigitalOcean erstellt, und danach wurden $200 Hosting-Guthaben geholt.

Im DigitalOcean-Dashboard wurde eine Droplet (VM) mit Ubuntu 23.10 x64 erstellt.

Während der Erstellung wurden die SSH-Keys eingefügt und für den Zugang konfiguriert. Eine manuelle Installation ist dann nicht mehr notwendig.

Die installierten SSH-Schlüssel können mit dem Befehl "cat /root/.ssh/authorized_keys" eingesehen werden.

## Schritt 2: Server-Initialkonfiguration

Der Server wurde mit diesen Befehlen geupdatet und geupgradet (ist immer wichtig, um Fehlerkorrekturen, welche die Stabilität des Systems verbessern und insbesondere potenzielle oder erwiesene Sicherheitslücken schließen):

sudo apt update
sudo apt upgrade

## Schritt 3: DNS-Konfiguration

1. DNS-Name Registrierung und Setup:

Mit dem GitHub Student Developer Pack wurde ein kostenloser DNS-Name bei Name.com registriert.
Im Name.com-Dashboard wurden die Nameserver auf DigitalOcean gesetzt, also:
ns1.digitalocean.com
ns2.digitalocean.com
ns3.digitalocean.com

Im Name.com wurden auch 2 A-Records erstellt (für die IPv4 der Droplet), genau wie 2 AAAA-Records (für die IPv6 der Droplet), also:
A ssystemstoumi.engineer 139.59.141.84 mit TTL 3600
A www.ssystemstoumi.engineer 139.59.141.84 mit TTL 3600
AAAA ssystemstoumi.engineer 2a03:b0c0:3:d0::1943:d001 mit TTL 3600
AAAA www.ssystemstoumi.engineer 2a03:b0c0:3:d0::1943:d001 mit TTL 3600

Im DigitalOcean-Dashboard wurden die gleichen Records erstellt, die auf die IP-Adresse der Droplet zeigen.

## Schritt 4: Fail2Ban Einrichtung

1. Fail2Ban wurde mit diesem Befehl installiert:
   
sudo apt install fail2ban

2. Fail2ban-Konfiguration:

Da jail.conf nicht direkt geändert werden soll, wurde mit diesem Befehl eine Kopie jail.local von jail.conf erstellt, in der alle lokalen Einstellungen erstellt wurden:

sudo cp jail.conf jail.local

Danach wurde jail mit dem Texteditor nano konfiguriert:

sudo nano jail.local

Dann wurden die Dienste [SSHD] und [nginx-http-auth] angepasst und aktiviert:

[nginx-http-auth]
enabled = true
port = http,https
logpath = %(nginx_error_log)s
maxretry = 5
bantime = 600
findtime = 600
backend = %(sshd_backend)s

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 5
bantime = 600
findtime = 600
backend = %(sshd_backend)s

maxretry: Die Anzahl der fehlgeschlagenen Versuche, bevor eine IP gebannt wird.
bantime: Die Dauer (in Sekunden), wie lange eine IP gebannt bleibt.
findtime: Der Zeitraum (in Sekunden), in dem die maxretry-Anzahl erreicht werden muss, um ein Bann auszulösen.
backend = %(sshd_backend)s bedeutet, dass das System für die Überwachung von Logdateien (Backend) den Wert verwendet, der für sshd_backend irgendwo in den Konfigurationsdateien festgelegt ist. Es ermöglicht eine flexible und zentrale Einstellung des Überwachungsmechanismus für Logdateien.

3. Fail2ban aktivieren und starten:
   
Fail2ban wurde mit diesen Befehlen aktiviert und gestartet:

sudo systemctl enable fail2ban
sudo systemctl start fail2ban

Mit diesem Befehl kann überprüft werden, ob es ausgeführt wird:

sudo systemctl status fail2ban

Es ist enabled!

## Schritt 5: Installation und Konfiguration von Moodle über git mit Postgres, Nginx und PHP als WWW-Server

1. Nginx wurde mit diesem Befehl installiert:

sudo apt-get install nginx

2. PHP8.2 zusammen mit einigen Modulen, die Moodle gerne zur Verfügung hat, wurden mit diesem Befehl installiert:
   
sudo apt-get install php8.2-fpm php-apc php8.2-curl php8.2-gd php8.2-xmlrpc php8.2-intl

3.PostgreSQL (sowie seine PHP-Abhängigkeiten) wurden mit diesem Befehl installiert:

sudo apt-get install postgresql postgresql-contrib php8.2-pgsql

4. Git wurde mit diesem Befehl installiert:
   
sudo apt-get install git

5. Konfiguration:
   
Mit diesem Befehl zum Verzeichnis /usr/share/nginx navigieren:

cd /usr/share/nginx

Das Verzeichnis ist nicht für den Zugriff durch irgendjemanden bereit, daher wurde mit diesem Befehl Ahmed der alleinige Zugriff darauf gewährt:

chown -R $Ahmed:$Ahmed www

Dann zum www-Verzeichnis wechseln:

cd www

Danach wurde Moodle geklont:

git clone https://github.com/moodle/moodle.git

Dann zum Moodle-Verzeichnis wechseln:

cd moodle

Dann die aktuellste Version aus den Git-Tags auswählen:

git tag
git checkout v4.3.3

Dann zum Moodle-Verzeichnis wechseln:

cd moodle

Dann wurde die Vorlage config-dist.php zu config.php kopiert, und anschließend wurde config.php bearbeitet, um die Datenbankeinstellungen einzugeben:

sudo cp config-dist.php config.php
nano config.php

So sehen die wichtigsten Einstellungen aus:

$CFG->dbtype = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost = 'localhost';
$CFG->dbname = 'moodle';
$CFG->dbuser = 'moodle';
$CFG->dbpass = 'password';
$CFG->prefix = 'mdl2_';
$CFG->wwwroot = 'https://ssystemstoumi.engineer'
//Sehr wichtig, die s in https nicht zu vergessen, nach der Zugriffssicherung mittels HTTPS und Let's Encrypt SSL-Zertifikat, falls $CFG->wwwroot zuerst als http://ssystemstoumi.engineer gespeichert wurde!!
$CFG->dataroot = '/usr/local/moodledata';

Dann wurde ein Moodle-Datenverzeichnis und ein Cache-Verzeichnis eingerichtet:

sudo mkdir /usr/local/moodledata
sudo mkdir /var/cache/moodle

Dann wurde dafür gesorgt, dass sie dem www-data-Benutzer gehören, kurz Nginx:

sudo chown www-data:www-data /usr/local/moodledata
sudo chown www-data:www-data /var/cache/moodle

Die erste besteht darin, Benutzer-Uploads, Sitzungsdaten und andere Dinge zu speichern, auf die nur Moodle Zugriff benötigt und die nicht über das Internet zugänglich sein sollten. Der Cache-Speicher hilft dabei, Dateien für eine schnellere Zwischenspeicherung aufzubewahren.

Weitere Befehle zum Setzen der Berechtigungen wurden auch ausgeführt:

sudo find /usr/share/nginx/www/moodle -type d -exec chmod 755 {} ;
sudo find /usr/share/nginx/www/moodle -type f -exec chmod 644 {} ;

Dann wurde der Postgres-Benutzer verwendet, um eine neue Rolle namens moodle zu erstellen, die dann die moodle-Datenbank verwalten kann (\q wurde benutzt, um zur Shell zurückzukehren):

CREATE USER moodle WITH PASSWORD 'password';
CREATE DATABASE moodle;
GRANT ALL PRIVILEGES ON DATABASE moodle to moodle;
\q
exit

Um Nginx mitzuteilen, wie die Dateien bereitgestellt werden sollen, wurde dazu eine Nginx-Hostdatei erstellt:

sudo nano /etc/nginx/sites-available/moodle

So sah es erstmal aus, vor der Zugriffssicherung (PHP-Version muss stimmen!!):

server {
    listen 80;
    root /usr/share/nginx/www/moodle;
     server_name ssystemstoumi.engineer;
     index index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; 
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}


Dann wurde die Moodle-Seite aktiviert, und der Standard-Symlink entfernt:

sudo rm /etc/nginx/sites-enabled/default
sudo ln -s /etc/nginx/sites-available/moodle /etc/nginx/sites-enabled/moodle

Anschließend wurden PHP, PostgreSQL und Nginx aktiviert und gestartet mit systemctl, sodass sie beim Booten automatisch starten:

sudo systemctl enable nginx
sudo systemctl start nginx
sudo systemctl enable php8.2-fpm
sudo systemctl start php8.2-fpm
sudo systemctl enable postgresql
sudo systemctl start postgresql

6. Konfiguration im Browser
   
Es wurde im Browser http://ssystemstoumi.engineer/ aufgerufen.

Wenn die Moodle-Site zum ersten Mal geöffnet wird, muss man einige Formulare ausfüllen. Diese Informationen wurden unter anderem eingegeben:

Datenverzeichnis: /usr/local/moodledata
Datenbanktreiber: Postgres

Die Geschäftsbedingungen wurden am Ende akzeptiert.

Der Installationsassistent von Moodle prüft alle Voraussetzungen auf Ubuntu, ob diese für die Ausführung von LMS erfüllt sind oder nicht. Alle wurden erfüllt, außer die HTTPS-Voraussetzung, das wurde aber erstmal übersprungen.

Dann installierte das System automatisch die erforderlichen Moodle-Module.

Um sich später anzumelden, muss man ein Admin-Konto erstellen. Das wurde gemacht, indem admin als Benutzername eingegeben wurde und Password1* als Passwort (es gibt Voraussetzungen für die Setzung des Passwortes).

Am Ende wurde der Seitennamen beschrieben, sowohl den vollständigen Namen als auch den Kurznamen (ssystemstoumi.engineer finde ich persönlich besser als http://ssystemstoumi.engineer, weil man den vollständigen Namen sieht, sobald man die Seite aufruft).

## Schritt 6: Zugriffssicherung mittels HTTPS und Let's Encrypt SSL-Zertifikat mit Certbot

1. Certbot wurde mit diesem Befehl installiert:
   
sudo apt install certbot python3-certbot-nginx

2. Erhalten eines SSL-Zertifikats, mit diesem Befehl:
   
sudo certbot --nginx -d ssystemstoumi.engineer -d www.ssystemstoumi.engineer

Dies wird certbot mit dem --nginx Plugin ausgeführt und verwendet, -d um die Domänennamen anzugeben, für die das Zertifikat gültig sein soll.

So sieht die Datei /etc/nginx/sites-available/moodle jetzt aus:

server {
     root /usr/share/nginx/www/moodle;
     server_name ssystemstoumi.engineer www.
ssystemstoumi.engineer;
     index index.php;
     rewrite ^/(.*\.php)(/)(.*)$ /$1?file=/$3 last;

     location ^~ / {
         try_files $uri $uri/ /index.php?q=$request_uri;
         index index.php index.html index.htm;

         location ~ \.php$ {
             include snippets/fastcgi-php.conf;
             fastcgi_split_path_info ^(.+\.php)(/.+)$;
             fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
             fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
             include fastcgi_params;
         }
     }

    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/ssystemstoumi.engineer/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/ssystemstoumi.engineer/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot
} 
server {
    listen 80;
    if ($host = www.ssystemstoumi.engineer) {
        return 301 https://$host$request_uri;} # managed by Certbot


    if ($host = ssystemstoumi.engineer) {
        return 301 https://$host$request_uri;
    } # managed by Certbot

     server_name ssystemstoumi.engineer www.
ssystemstoumi.engineer;
    return 404; # managed by Certbot
    
Außerdem wurde $CFG->wwwroot der Datei /usr/share/nginx/www/moodle/config.php von http://ssystemstoumi.engineer/ zu https://ssystemstoumi.engineer/ geändert.

3. Dann wurde Nginx neugestartet mit:
   
sudo service nginx restart

PHP8.2 wurde auch neugestartet, und die Caches geflusht mit:

sudo resolvectl flush-caches
sudo service php8.2-fpm restart

Diese 3 Befehle am besten oft ausführen.

4. Auf https://www.ssllabs.com/ wurde getestet, ob das Ganze geklappt hat:

Das hat geklappt, und ssystemstoumi.engineer ist jetzt mittels HTTPS und Let's Encrypt SSL-Zertifikat gesichert!

# Google Search Block für Moodle

## Schritt 1: Git Repo namens googlesearch erstellen

1. Es wurde im Git-Konto angemeldet, und ein Public Git Repo namens googlesearch erstellt, die README.md wurde dabei hinzugefügt.

2. Im Verzeichnis /usr/share/nginx/www/moodle/blocks wurde dieses Repo geklont mit:

sudo git clone https://github.com/AhmedT9/googlesearch.git

Im Schritt 2 wird das Push-Prozess beschrieben.

## Schritt 2: Grunddateien erstellen:

1. Alle Grunddateien, die in https://moodledev.io/docs/apis/plugintypes/blocks beschrieben sind, wurden mit dem Befehl touch im /usr/share/nginx/www/moodle/blocks/googlesearch erstellt, so sieht am Ende das Verzeichnislayout für das Plugin aus:
   
blocks/googlesearch/
 |-- db
 |   `-- access.php
 |-- lang
 |   `-- en
 |       `-- block_googlesearch.php
 |-- block_googlesearch.php
 |-- styles.css
 |-- version.php

2. Dann wurde gepusht mit:
   
git add .
git commit -m "initial commit"
git push

Benutzername von GitHub wurde dann eingegeben, sowie ein persönliches Zugangstoken.

## Schritt 3: Programmablesearchengine auf https://programmablesearchengine.google.com/ holen, die ID davon speichern.

## Schritt 4: API-Key der Custom Search JSON API holen und speichern -> https://developers.google.com/custom-search/v1/overview.

## Schritt 5: Design und Entwicklung:

Design und Entwicklung: Zuerst wurde die Funktionalität des Blocks entworfen und der benötigte Code entwickelt, einschließlich der Verbindung zur Google Custom Search API.

Beachten Sie, dass alle Dateien Kommentare enthalten, die die Funktionalität im Detail erläutern.

## Schritt 6: Stilisierung:

Anschließend wurden CSS-Stile definiert, um das Aussehen der Suchergebnisse anzupassen. Es gibt auch die Möglichkeit, das Ganze im raw JSON-Format zu zeigen, es wurde aber auskommentiert.
## Schritt 7: Plugin-Registrierung:

Die version.php Datei wurde erstellt, um das Plugin bei Moodle zu registrieren.

## Schritt 8: Berechtigungen definieren:

In access.php wurden die benötigten Berechtigungen für den Block festgelegt.

## Schritt 9: Lokalisierung vorbereiten:

Die Sprachdatei lang/en/block_googlesearch.php wurde hinzugefügt, um die Texte des Plugins zu definieren und eine spätere Übersetzung zu ermöglichen.

## Schritt 10: Installation

Das Plugin wurde automatisch installiert, indem ssystemstoumi.engineer aufgerufen wurde.

## Schritt 11: Benutzung und Tests

## Um den Google Search Block zu benutzen und testen:

1. Schalten Sie die Bearbeitung auf der gewünschten Seite ein.

2. Wählen Sie "Block hinzufügen" und fügen Sie den „Google Search Block“ hinzu.

3. Der Block erscheint nun auf Ihrer Seite, und das Suchergebnis für ("Moodle Blocks") wird angezeigt.

# Dateibeschreibungen:

block_googlesearch.php -> Diese Datei definiert die Hauptklasse des Blocks, block_googlesearch, die von block_base erbt. Sie ist verantwortlich für die Initialisierung des Blocks, das Setzen des Blocktitels und das Generieren des Inhalts, der in dem Block angezeigt wird. Die Methode get_content() verwendet die Google Custom Search API, um Suchergebnisse zu einem vordefinierten Suchbegriff ("Moodle Blocks") zu erhalten und diese Ergebnisse in einer formatierten Liste anzuzeigen.

styles.css -> Die styles.css Datei enthält CSS-Regeln, die das Aussehen der Suchergebnisse im Block gestalten. Sie definiert Stile für die Liste der Suchergebnisse, wie z.B. das Entfernen von Listenaufzählungszeichen, das Hinzufügen von Abständen zwischen den Ergebnissen und das Anpassen der Farbe und Dekoration der Hyperlinks.

version.php -> In version.php werden die Plugin-Version, die erforderliche Moodle-Version für das Plugin, die Reife und die Release-Bezeichnung definiert. Diese Datei ist notwendig für die Moodle-Plugin-Verwaltung und hilft Moodle zu erkennen, ob das Plugin mit der installierten Moodle-Version kompatibel ist.

access.php -> access.php definiert die Fähigkeiten (Capabilities) des Plugins. Es legt fest, welche Benutzerrollen den Block hinzufügen dürfen und welche Berechtigungen für die Verwaltung des Blocks erforderlich sind. Dies beinhaltet Berechtigungen sowohl für das Hinzufügen des Blocks zu einem Kurs als auch zum eigenen Moodle-Dashboard.

lang/en/block_googlesearch.php -> Diese Sprachdatei enthält englische Zeichenketten (Strings) für das Plugin, darunter den Namen des Plugins und die Beschreibungen der Fähigkeiten. Die Verwendung von Sprachdateien ermöglicht die einfache Lokalisierung des Plugins für verschiedene Sprachen.
