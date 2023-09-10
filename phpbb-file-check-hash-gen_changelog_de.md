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
