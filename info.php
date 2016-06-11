<?php
$languages = 34;
?>
<h2>&Uuml;ber dieses Programm</h2>
Dieses Programm erkennt die Sprache, in der ein eingegebener Text geschrieben ist. Je l&auml;nger der Text, desto treffsicherer ist das Resultat.

<h2>Funktionsweise</h2>
<p style="text-align: justify">Für jede eingebaute Sprache werden Wikipedia-Artikel gespeichert und dann statistisch ausgewertet. Dabei werden die h&auml;ufigsten 
<b>Trigramme (Gruppen aus 3 Buchstaben)</b> ermittelt, die, zusammen mit den <b>h&auml;ufigsten W&ouml;rtern</b> und den in der Sprache verwendeten 
<b>Sonderzeichen</b>, für jede Sprache separat gespeichert werden.
<br />
<br />Beim Eingeben eines Textes werden die h&auml;ufigsten Trigramme des Textes mit den statistisch ermittelten Trigrammen der Sprachen 
verglichen. Zusammen mit der Anzahl gefundener W&ouml;rter und Sonderzeichen wird anschliessend die Wahrscheinlichkeit für jede Sprache 
berechnet. Bei Eingaben ab 5-7 W&ouml;rtern wird der Trigramm-Vergleich am meisten gewichtet.
<br />
<br />Ein grosses Ziel für mich ist es, die Dateigr&ouml;sse des Programmes und die Zeit, die für die Sprachbestimmung ben&ouml;tigt wird, so klein 
wie m&ouml;glich zu halten. Deshalb werden nicht mehr als 100 W&ouml;rter pro Sprache gespeichert, was eine schnelle Ausführung bedeutet 
(da das Programm nicht nach Unmengen von W&ouml;rtern suchen muss), was aber zum Nachteil hat, dass das Programm bei kürzeren Eingaben falsch 
liegen kann.
<br />
<br />Gewisse Sprachen &auml;hneln sich so sehr, dass sich die Sprecher der Sprachen untereinander verstehen k&ouml;nnen, wie es der Fall für 
D&auml;nisch, Norwegisch und Schwedisch ist. In geschriebener Form sind sie dabei auch schwierig auseinanderzuhalten. Um korrektere Resultate 
zu bekommen, werden für solche Sprachgruppen weitere Tests ausgeführt, wie die Suche nach W&ouml;rtern oder Buchstabenfolgen, die nur in einer 
der Sprachen vorkommen.
<br />
<br />Zum Teil sind die Ansichten, ob es sich um unabh&auml;ngige Sprachen oder nur um Dialekte handelt, politisch gef&auml;rbt: Die Unterschiede 
zwischen Bosnisch, Kroatisch und Serbisch sind l&auml;cherlich klein im Verh&auml;ltnis zu den chinesischen 
<span style="font-style: italic">Dialekten</span>, deren Sprecher sich untereinander nicht verstehen. Dieses Programm unterscheidet 
nicht zwischen Bosnisch, Kroatisch und Serbisch.</p>


<h2>Statistiken &amp; Fakten</h2>
<p>Dieses Programm kann <b><?php echo $languages; ?> Sprachen</b> erkennen.</p>
<div id="details" style="display: none; padding: 10px 0">
<b>Eingebaute Sprachen</b>
<ul>
<?php
include('langbox.php');
$supported = array_slice($lang, (0 - $languages));
asort($supported);
foreach($supported as $abbr => $de_name){
	echo "\r".'<li>'.$de_name.' ('.$demo[$abbr]['name'].')</li>';
}
?>
</ul>
</div>
<p id="langlink" style="background-image: url('./img/ent.png'); background-repeat: no-repeat; padding-left: 18px; background-position: 0px 3px"><a href="javascript:animatedcollapse.toggle('details')" onclick="document.getElementById('langlink').style.display = 'none'">Eingebaute Sprachen anzeigen</a></p>
<p style="margin-top: 15px">
<b>Anzahl Dateien</b>:  72
<br /><b>Dateigr&ouml;sse des Programmes</b>: 441 KB
<br /><b>PHP-Version</b>: <?php echo phpversion(); ?>
<br />Insgesamt wurden <b>364</b> Wikipedia-Artikel (7 MB) für die statistischen Analysen der Sprachenmerkmale verwendet.</p>


<h2>Quellen</h2>
<p>
<ul>
 <li>Die Texte f&uuml;r die Sprachenanalysen stammen aus <a href="http://wikipedia.org/">Wikipedia</a>.</li>
 <li>Der Code, der Sonderzeichen in die korrespondierenden HTML Entities umwandelt, stammt urspr&uuml;nglich aus <a href="http://greywyvern.com/code/php/utf8_html">GreyWyvern.Com</a>.</li>
 <li>Die Symbole (Icons) kommen von <a href="http://famfamfam.com/lab/icons/silk/">FamFamFam.Com</a>.
 <li>Der Code f&uuml;r die JavaScript-Effekte stammt aus <a href="http://www.dynamicdrive.com/dynamicindex17/animatedcollapse.htm">DynamicDrive</a>.</li>
 <li>Die Beispielstexte sind Ausz&uuml;ge aus <a href="http://www.ohchr.org/EN/UDHR/Pages/SearchByLang.aspx">&Uuml;bersetzungen der UN-Menschenrechtscharta</a>.</li>
 <li>Die Anzahl Sprecher der Sprachen stammen aus <a href="http://en.wikipedia.org/wiki/List_of_languages_by_number_of_native_speakers">en.wikipedia.org</a>.</li>
</ul>
</p>