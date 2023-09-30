### 0.5.1
(2023-09-30)

* Fix: Bei ungültiger Zeitzonen ID wurde E_NOTICE getriggert. Das wird jetzt abgefangen und eine kontrollierte Fehlermeldung ausgegeben.

### 0.5.0
(2023-09-20)

* Dateien von HashGen umbenannt, damit diese zum Namensmuster von FileCheck passen.
* Konfig-Variablen und CLI-Parameter haben jetzt dieselben Namen.
* Für maximale Flexibilität sind nun alle Konfig-Variablen auch als CLI-Parameter verfügbar. Ist ein CLI-Parameter nicht angegeben, wird die Konfig-Variable verwendet.
* Die beiden Funktionen zum Ermitteln der Prüfsummen aller Dateien eines ZIPs und eines Ordners zu einer einzigen Funktion zusammengefasst, bei der nur noch die Quelle angegeben werden muss.
* Die beiden zusätzlichen Dateien `filecheck_ignore.txt` und `filecheck_exceptions.txt` die zum ZIP hinzugefügt werden, bekommen jetzt den aktuellen Zeitstempel im ZIP. Setzt PHP 8.0 voraus.
* In `filecheck_hashgen_config.php` kann jetzt für `filecheck_ignore.txt` und `filecheck_exceptions.txt` jeweils ein abweichender Quell-Dateiname angegeben werden. Im ZIP bekommen diese beiden Dateien dann automatisch den korrekten Dateinamen, der von FileCheck erwartet wird.
* Nachdem das Hash Paket ZIP erstellt wurde, wird dieses zur Kontrolle geöffnet und der Inhalt angezeigt.
* Fehlerbehandlung erweitert: 
  * Ist eine angegebene Quelle nicht vorhanden (ZIP oder Ordner), wird das explizit gemeldet. Bislang gab es nur eine indirekte Fehlermeldung bezüglich fehlender `constants.php`.
  * Enthält ein Dateiname vom phpBB Paket unerlaubte Zeichen, wird das gemeldet und die Ausführung abgebrochen. In diesem Fall muss die Konstante `VALID_CHARS` sowohl bei "phpBB File Check Hash Gen" als auch bei "phpBB File Check" angepasst werden.
  * Es werden jetzt alle relevanten Parameter und Einstellungen geprüft.
  * An etlichen Stellen gibt es zusätzliche Prüfungen um im Fehlerfall kontrollierte Fehlermeldungen ausgeben zu können.
* Code Verbesserungen.
* PHP Mindestversion geändert: 7.1 -> 8.0

### 0.4.2
(2023-07-31)

* Im RegEx unnötige non-greedy-Operatoren entfernt, da die Reichweite bereits per Zeichen-Klasse fest vorgegeben ist und ohnehin keine Abweichung erlaubt. Das betrifft die phpBB Version in `constants.php`.
* HashGen war bisher nur für den Einsatz bei nationalen Support-Foren geeignet, da immer ein sekundäres Paket vorhanden sein musste. Diese Voraussetzung wurde nun bei 0.4.2 entfernt. Dafür musste der Code an etlichen Stellen geändert werden.
* Fehlerbehandlung erweitert: Fehlt der Parameter für eine notwendige Quelle (ZIP oder Ordner), wird das gemeldet.
* Code Verbesserungen.

### 0.4.1
(2023-07-25)

* Erste interne Test Version.

### 0.4.0
(2023-07-23)

* Unterstützung für ZIP Archive als Quellen. Somit kann man HashGen entweder einen Ordner übergeben, in dem das entpackte phpBB Paket vorhanden ist, oder direkt ein ZIP.
