=== KraehennestPodcaster ===
Contributors: Marc Schieferdecker (@motorradblogger)
Donate link: https://www.piratenpartei.de/mitmachen/spenden/
Tags: podcast, piraten, piratenpartei, krähennest, pirateparty
Requires at least: 3.4.0
Tested up to: 3.4.2
Stable tag: 1.2.1
License: GPLv2 or later

== Description ==
Der KraehennestPodcaster ermöglicht dir die super einfache Einbindung von Podcasts des Krähennests oder anderen Seiten in dein WordPress Blog.

Außerdem kannst du die letzte Folge des Krähennest Podcasts als Widget in deine Sidebar integrieren.

Wer es nicht weiß: Das Krähennest ist der offizielle Podcast / das offizielle News-Portal der Piratenpartei Nordrhein-Westfalen.

Letzte Änderung: Jetzt auch für WordPress MultiSite einsetzbar - kann global aktiviert und lokal konfiguriert werden!

== Changelog ==
Version 1.0.0
- Eine erste Version des Plugins ist nun veröffentlicht

Version 1.0.1
- Dateipfadfoo

Version 1.0.2
- Sprache hinzugefügt: en_EN

Version 1.0.3
- Screenshots hinzugefügt, Typo

Version 1.0.4
- SVN Update weil ich zu doof war das SVN zu updaten :/

Version 1.0.5
- Kleinen Bug im XML Reader gefixt

Version 1.0.6
- Cache-Verzeichnis konfigurierbar gemacht, Systemvorraussetzungs-Check eingefügt, Sprachdateien aktualisiert, anderen Default-Pfad für das Cache-Verzeichnis gesetzt (/wp-content/uploads)

Version 1.0.7
- Fatal Error "...::__toString()" behoben und PHP 4 konform umgestaltet - wer nutzt bitte noch PHP 4 WTF?, Warning bzgl. plugin_dir_url() gefixt

Version 1.0.8
- SimplePie_File statt file_get_contents (nicht mehr auf allow_url_fopen angewiesen, wenn curl installiert ist)
- Bug im Cache-System behoben
- Perfomance Kram, läuft nun schneller
- Prüft nun nicht nur beim Veröffentlichen auf Beiträge im Krähennest, sondern auch beim Lesen von Beiträgen ohne MP3 Zuordnung
- Plugin läuft nun ohne Probleme auch auf WAMP (wer's braucht... *scnr*)
- Ich mag p0wnys

Version 1.0.9
- HOTFIX weil die XML vom Kraehennest buggy ist!

Version 1.1.0
- Neue Option: XML Einträge rückwärts sortieren (wurde vom Kraehennest plötzlich geändert...)
- Neue Option: Entfernt immer wieder kehrende und unnötige Sätze aus dem Titel des Kraehennest Beitrags
- Neue Option: Entfernt die Zahlen am Anfang des Krähennest Beitrags
- Dadurch wird das Plugin wesentlich flexibeler

Version 1.1.0-1
- Kleinen Bug beim Neuerstellen des Cache-Files behoben, die cacheTime wird nun korrekt beachtet

Version 1.1.1
- Neue Option: Es ist nun wählbar, ob der Player vor oder nach dem Inhalt ausgegeben werden soll.
- Kleine allgemeine Verbesserungen am Code

Version 1.1.2
- Neue Option: Es kann eine Regular Expression eingegeben werden, nach der die Podcasts anhand des Titels gefiltert werden
- Widget kann nun mit CSS formatiert werden, entsprechende Div-Wrapper wurden hinzugefügt
- Englische Sprach-Strings aktualisiert (plugin now completly available in english)
- Kleine Verbesserungen am Code
- Wer ist eigentlich dieser Steinmeier?

Version 1.1.3
- Regular Expressions UTF-8 kompatibel gemacht

Version 1.1.3-1
- Automatisches Einbinden bei Artikeln Fehlertoleranter gemacht (Sonderzeichen foo)
- Kleine Verbesserung im Widget
- Die Krähennest Feeds für NWR und für den BUND auf der Konfigurationsseite werden mit ausgegeben (einfacher zu konfigurieren)

Version 1.2.0
- Das Plugin wurde WordPress MU (Multisite) fähig gemacht und kann nun als globales, oder als lokales Plugin installiert werden!

Version 1.2.1
- Die Integration in WordPress Multisite wurde verbessert, da nun jedes Blog im Netzwerk das Plugin für sich selbst konfigurieren und anpassen kann

== Frequently Asked Questions ==

= Wie kann ich eine Folge automatisch in einen Post einbinden? =

Nenne den neuen Post einfach genau so, wie den Post im Krähennest. Bei Speichern deines Posts werden dann zwei Benutzerdefinierte Felder mit den korrekten Werten befüllt. Völlig automatisch.

= Wie kann ich eine Folge von Hand in einen Post einbinden? =

Lege zwei Benutzerdefinierte Felder an: "KP_MP3_TITLE" mit dem Titel der angezeigt werden soll und "KP_MP3_URL" mit der vollen URL zur MP3 Datei. Es müssen übrigens keine MP3 Dateien vom Krähennest sein, du kannst das Plugin auch für andere Inhalte nutzen. ;-)

= Was ist der Unterschied zwischen der Ausgabe über the_meta() und dem internen Template? =

WordPress bringt die Ausgabe von Benutzerdefinierte Feldern als Funktion bereits mit, allerdings muss das Template dies unterstützen. Daher wird in den meisten Fällen die Ausgabe über das interne Template besser sein. Falls nicht kannst du das aber abschalten und dein eigenes CSS in der Konfiguration hinzufügen.

= Der Funktionstest schlägt fehl, was kann das sein? =

PHP muss Dateien über das Netzwerk öffnen können. Falls es nicht klappt, ist entweder der Pfad zur XML Datei falsch, das Krähennest down, oder dein PHP falsch konfiguriert. In der Regel verwende ich aber WordPress interne Funktionen, so dass es keine Probleme geben sollte, wenn WordPress ohne Angabe von FTP Zugangsdaten Updates installieren kann.

= Was macht das Widget? =

Das Widget läd die letzte Krähennest Folge in einem kleinen SWF-MP3-Player (der Player ist public domain!) und zeigt diese in der Sidebar an. Der Titel des Widgets ist konfigurierbar.

== Installation ==
Installiere das Plugin über das WordPress Plugin Repository, oder kopiere das Verzeichnis nach "/wp-content/plugins/".

== Upgrade Notice ==
Mit dem Update 1.1.0 kommen drei neue Optionsfelder hinzu. Diese werden mit Default-Werten belegt. Für bereits existierende Artikel mit einer Verküpfung zum Kraehennest ändert sich dadurch jedoch nicht. Schaut aber bitte beim Wechsel auf diese Version trotzdem mal in die Konfigurationseite des Plugins.

Danke!

Marc

== Screenshots ==

1. Hier die Widget Ausgabe.
2. Automatische integration der MP3, wenn der Titel mit dem Titel im Krähennest übereinstimmt.
3. So sieht die Ausgabeseite aus.
4. Die Administrationsseite.
