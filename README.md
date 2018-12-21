# IPSymconCsv2Archive

[![IPS-Version](https://img.shields.io/badge/Symcon_Version-5.0+-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Module-Version](https://img.shields.io/badge/Modul_Version-1.2-blue.svg)
![Code](https://img.shields.io/badge/Code-PHP-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/126683101/shield?branch=master)](https://github.styleci.io/repos/145117879)

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Funktionsreferenz](#4-funktionsreferenz)
5. [Konfiguration](#5-konfiguration)
6. [Anhang](#6-anhang)
7. [Versions-Historie](#7-versions-historie)

## 1. Funktionsumfang

Das Modul erlaubt es, Daten aus einer CSV-Datein in das Archiv einer Variablen zu importieren.

Dabei werden die Daten soweit möglich überprüft, die erforderliche Sortierung sichergestellt und die Archiv-Dateien angelegt/erweitert.

Durchgeführte Tests:
- angegebene Spalten in Ordnung (Spalten ungleich, Spalten passen zur Spaltenanzahl)
- Variable ist vorhanden und hat eine Standard-Aggregation (also nicht nur 'Zähler').
- Werte passen zu dem Datentyp der Variable<br>
  - bool: können die Werte _1_/_0_, _true_/_false_, _ja_/_nein_ oder _yes_/_no_ (Gross/Kleinschreibung ist irrelevant) haben<br>
  - int: es wird überprüft, das der Wert nur aus Zahlen und dem optionale Vorzeichen besteht
  - float: es wird geprüft, das der Wert nur aus Zahlen, dem optionalen Dezimaltrenner, Vorzeichen und einem Expotenten (als _e_/_E_) besteht. Bei der Ausgabe in die Archivdatei wird - wie erforderlich - der Punkt als Dezimalrenner verwendet.
  - string: können n der CSV-Datein base64-kodiert sein (siehe oben), ansonsten wird der Wert base64-kodiert ausgegeben.
- Zeitstempel 
  - ist dekodierbar
  - liegt nach dem 1.1.2000 (vorher liegende Werte werden von IPS nicht beachtet) 
  - nicht in der Zukunft
- Zeitstempel müssen eindeutig sein, sowohl in der CSV-Datei als auch in eventuell bereits vorhandenen Daten.<br>
Daher ist es möglich, einen Import erneut durchzuführen, da bereits vorhandenen Daten übersprungen oder überschrieben werden…

Sonstiges:
- die Einträge in der Achricdatei müssen chronologisch aufsteigend sortiert sein. Dazu werden
  - die Daten der CSV-Datei sortiert
  - Daten an der richtigen Stellen in einer vorhandenen Datei eingefügt.
- es werden alle Daten eines Monats auf einmal (wenn vorhanen) eingelesen, ergänzt und geschrieben

Ausgaben:
- Liste der Fehler
- Anzahl der hinzugefügten oder geänderten Einträge pro Datei und in Summe.

**Wichtig**

* Vor einem Import immer ein Backup machen!
* Vor einem Import immer ein Testlauf durchführen!
* Während des Imports sollte so wenig wie möglich im System passieren, wenn während des Imports Daten dieser Variable empfangen werden, kann es passieren, das diese Werte verloren gehen!
* Nach dem Import die Daten kontrollieren!

Und ganz wichtig: trotz einiger Tests ist natürlich ein unerwartetes oder fehlerhaftes Verhalten nicht ausgeschlossen. Zudem ist der Import von Daten auf diese Art und Weise nicht offiziell unterstützt.<br>
**Die Nutzung dieses Modules erfolgt unbedingt und in jedem Fall auf eigenen Gefahr.**

## 2. Voraussetzungen

 - IP-Symcon ab Version 5.0

## 3. Installation

### Laden des Moduls

Die Konsole von IP-Symcon öffnen. Im Objektbaum unter Kerninstanzen die Instanz __*Modules*__ durch einen doppelten Mausklick öffnen.

In der _Modules_ Instanz rechts oben auf den Button __*Hinzufügen*__ drücken.

In dem sich öffnenden Fenster folgende URL hinzufügen:

`https://github.com/demel42/IPSymconCsv2Archive.git`

und mit _OK_ bestätigen.

Anschließend erscheint ein Eintrag für das Modul in der Liste der Instanz _Modules_

### c. Einrichtung in IPS

In IP-Symcon nun _Instanz hinzufügen_ (_CTRL+1_) auswählen unter der Kategorie, unter der man die Instanz hinzufügen will, und Hersteller _(sonstiges)_ und als Gerät _Csv2Archive_ auswählen.

Die Werte der Felder im Konfigurationsdialog werden nicht gespeichert sondern nur innerhalb des Dialoges benutzt!

### d. Verwendung

#### Felder

| Eigenschaft                          | Typ      | Beschreibung |
| :----------------------------------: | :-----:  | :----------------------------------------------------------------------------------------------------------: |
| Zeitstempel-Format                   | int      | zulässige Formate des Zeitstempels. Hinweis: Zeitstempel nur ab 1.1.2000 und bis zum aktuellen Zeitpunkt     |
| Trenner                              | string   | Trennerzeichen der Spalten.                                                                                  |
| Spaltenüberschrift in der 1. Zeile   | bool     | in der 1. Zeile ist ein Header und soll nicht ausgewertet werden                                             |
| Spalte des Zeitstempels              | int      | Spalte des Zeitstempels (1-relativ)                                                                          |
| Spalte des Wertes                    | int      | Spalte des Wertes (1-relativ)                                                                                |
| vorhandene Daten überschreiben       | bool     | Zeilen werden per Zeitstempel identifiziert, Daten bereits vorhandener Zeitstempel werden ersetzt            |
| Wert in CSV-Datei ist base64-kodiert | bool     | Strings sind bereits bas64-kodiert (wie in den IPS-CSV-Dateien)                                              |
| Variable automatisch re-aggregieren  | bool     | Variable nach Abschluss des Imports automatisch re-aggregieren                                               |
| CSV-Datei                            | string   | Datei mit den CSV-Daten                                                                                      |
| Variable                             | int      | ID der zu ändernden Variablen                                                                                |

Das Trennzeichen kann negeb der Angabe eines normeln Zeichens auch kodierte Zeichen gemäß [php - Escape sequences](
http://php.net/manual/de/regexp.reference.escape.php) enthalten; so entspricht **\t** einem TAB.

#### Aktionen

| Bezeichnung        |  Beschreibung |
| :----------------: | :-------------------------------------------------------------------------------: |
| Import testen      | Test der CSV-Daten, der Einstellungen und des Imports, keine Übernahme von Daten  |
| Import durchführen | Importieren der Daten                                                             |

## 4. Funktionsreferenz

### zentrale Funktion

`boolean Csv2Archive_Import(int $InstanzID, int $tstamp_type, string $delimiter, bool $with_header, int $tstamp_col, int $value_col, bool $overwrite_old, bool $string_is_base64, bool $do_reaggregate, string $data, int $VariableID, bool $test_mode)`<br>

| Variable         | Eigenschaft                          |
| :--------------: | :----------------------------------: |
| tstamp_type      | Zeitstempel-Format                   |
| delimiter        | Trenner                              |
| with_header      | Spaltenüberschrift in der 1. Zeile   |
| tstamp_col       | Spalte des Zeitstempels              |
| value_col        | Spalte des Wertes                    |
| overwrite_old    | vorhandene Daten überschreiben       |
| string_is_base64 | Wert in CSV-Datei ist base64-kodiert |
| do_reaggregate   | Variable automatisch re-aggregieren  |
| data             | CSV-Daten                            |
| VariableID       | Variable                             |
| test_mode        | Test-Modus                           |

## 5. Konfiguration:

## 6. Anhang

GUIDs

- Modul: `{0A762BB3-7A3F-4ED8-A275-59A58938F00B}`
- Instanzen:
  - Csv2Archive: `{C3129C82-96B3-44AA-A98A-0AC5E5A12917}`

## 7. Versions-Historie

- 1.2 @ 21.12.2018 13:10<br>
  - Standard-Konstanten verwenden

- 1.1 @ 21.08.2018 19:04<br>
  aufgrund von Fehlern in der Legacy-Konsole und daruas folgender Inkompatibilität funktioniert das Modul nur noch für IPS 5 und Web-Konsole

- 1.0 @ 19.08.2018 11:53<br>
  Initiale Version
