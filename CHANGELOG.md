syncCto Changelog
=================

Version 2.3.x (2013-03-02)
--------------------------

### Defect
Fehlende Version und Build Nummer (see #141).

### Defect
Sync wird nicht durchgeführt nur für Contao-Installation > Gelöschte Dateien (see #139).

### Defect
Leere Steps beim syncFrom (see #136).

### Defect
Suhosin Überprüfung 2.3.0. rc3 Build18 wirft Fehlermeldung (see #135).

### Defect
Entfernter Systemcheck funktioniert nicht mehr (see #134).

### Feature
Blacklist von extern befüllen (see #133).

### Defect
Daten anderer Clients im DB Popup (see #130).

### Defect
Verzeichnisse werden nicht entfernt (see #127).

### Feature
Systemcheck > Warnung das GMP nicht aktiviert ist...  (see #126).

### Feature
Letzter Step  (see #125).

### Defect
Datenbank Strukturvergleich  (see #124).

### Defect
ModSecurity Regelverletzung in syncCto (see #119).

### Defect
Suhosin wird nicht korrekt geprüft im Systemcheck (see #118).

### Feature
Neuer Hook bevor die alten Tabellen gelöscht werden (see #117).

### Feature
Nur eine Synchronisation gleichzeitig pro Installation (see #52).


Version 2.2.x (2012-08-17)
--------------------------

### Defect
Fehlerhafte Texte während der Synchronisation (see #115).

### Defect
Falsche Platzierung in den Konfigurationen (see #114).

### Defect
Optimierung der Beschreibungen (see #113).

### Defect
Abbruch bei unbekannten RPC's (see #112).

### Defect
Filterregeln der DB-Tabellen optimieren (see #110).

### Defect/Feature
Zusammenfassung der normalen und zu großen Dateilisten (see #109).

### Feature
UI der Steps optimieren (see #108).

### Defect
runonce.php beim AutoUpdate nicht berücksichtigen (see #106).

### Defect
AutoUpdater legt keine neuen DB-Tabellen an (see #105).

### Defect
Fehlermeldung beim Tabellenvergleich (see #104).

### Defect
Große Dateien syncen nicht möglich (see #101).

### Feature
Autoloader mit ER koppeln (see #95).

### Feature
DB-Tabellen erst in den Steps auswählen (see #94).

### Feature
Auslagerung der Vergleichsliste in eine Lightbox (see #93).


Version 2.1.x (2012-05-09)
--------------------------

### Defect
Anpassung der Rechte (see #92).

### Feature
Tabellen mit großen Datenmengen rot hinterlegen (see #89).

### Feature
Kompatibilität für Contao 2.11 (see #88).

### Feature
Optionaler Warnhinweis für BE-User im Client (see #87).

### Feature
DCA für SyncTo und SyncFrom anpassen (see #86).

### Feature
Konfiguration der Handshake Funktion (see #84).

### Defect
Korrektur der Blacklist Funktion (see #83).

### Feature
Deaktivierung der kritischen Einstellungen nach dem Sync (see #53).

### Feature
HOOK: Nach dem Synchronisieren die DB manipulieren (see #50).

### Feature
Filecache aktivieren (see #49).

### Defect
Zu große Datenbanken sprengen das Limit (see #3).

### Defect
Die Synchronisation des Clients wurde erfolgreich abgeschlossen (see #2).


Version 2.0.0 (2012-02-01)
--------------------------

### Feature
Optimierung und Zusammenfassung der Backup-Templates (see #79).

### Feature
Ermittlung der maximalen Bandbreite (see #77).

### Feature
Besseres Exception Handling (see #76).

### Feature
Optimierung der Klassen (see #75).

### Feature
Referrer-Prüfung erweitern (see #74).

### Feature
SMH berücksichtigen um Debugfiles schreiben zu können (see #71).

### Feature
Vorbereitung für Contao 2.10.x (see #61).

### Feature
Backup versteckter Tabellen (see #60).

### Feature
Auth Daten verarbeiten (htacces) (see #59).

### Feature
Abbrechen Button (see #58).

### Feature
Übersetzung auf Französisch (see #57).

### Feature
Verlinkung der Client-URL nach erfolgreicher Synchronisation (see #56).

### Defect
Syncronisation schlägt fehl (see #43).

### Defect
syncFrom wieder aktivieren (see #42).

### Defect
Error by sending XML to Server. Server answer with 500, Internal (see #35).

### Defect
Filelist Template entschlacken (see #29).

### Defect
Optimierung der Sync Templates (see #28).

### Defect
MaxRequestLen in den Systemcheck integrieren (see #27).

### Defect
Suhosin in den Systemcheck integrieren (see #26).

### Defect
Backupscript prüfen (see #24).

### Defect
Filelist Template für große Dateien fertigstellen (see #23).

### Defect
Datumsformat aus dem Core (see #22).

### Defect
Säuberung der Klassen (see #21).

### Defect
Request Token verhindert den Client Ping (see #20).

### Defect
Dateidownload bei den Backups geht nicht (see #19).

### Defect
API-Key Verwaltung vereinfachen (see #18).

### Defect
Filecache nach der Synchronisation anschubsen (see #17).

### Defect
Im Debugmodus leere Informationen nicht anzeigen (see #16).

### Defect
Löschen von Ordnern (see #15).

### Defect
Filelisten für große Dateien angleichen (see #14).

### Defect
Synchronisation wird nicht korrekt abgeschlossen (see #13).

### Defect
Systemcheck fehlerhafter Wertevergleich (see #12).

### Defect
Fehlermeldung bei Verschlüsselung (see #11).

### Defect
Umlaute im Debugmodus zerschossen (see #10).

### Defect
Mcrypt Verschlüsselung prüfen (see #9).

### Defect
Datenbank Export/Import - Memory Limit (see #8).

### Defect
MySQL Session richtig konfigurieren (see #7).

### Defect
DB importer (see #6).

### Defect	
DB-Tabellen werden nicht korrekt importiert (see #5).

### Defect
Umlaute der gelöschten Dateien (see #4).


Version 1.1.5 (2012-01-01)
--------------------------

### Fixed
Doppelklick auf "Dateien transferieren" abfangen (see #82).

### Fixed
Gelöschte Dateien erkennen und auch auf dem Client löschen (see #72).

### Fixed
Operatoren für die Blacklist einfügen (see #70).

### Fixed
Optimierung der Kommunikationsklassen (see #65).

### Fixed
Debugmodus in den Einstellungen definieren (see #64).

### Fixed
Backup-Files verlinken (see #63).

### Fixed
Sicherheitsmechanismus beim DB-Import (see #62).

### Fixed
Fatal error: Exception thrown without a stack frame in Unknown on line 0 (see #34).

### Fixed
Website Ping reparieren (see #25).