<?php

$explain = array(

'ngram' => 'Aus Texten wird für jede eingebaute Sprache die am häufigsten auftretenden 3-Buchstabengruppen (Trigramme) 
ermittelt und gespeichert.
In Deutsch kommen zum Beispiel die Endungen <code>en_</code> und <code>er_</code> (wobei <code>_</code> einen Leerschlag symbolisiert)
am häufigsten vor, gefolgt vom Wortanfang <code>_de</code> und den Gruppen <code>der</code> und <code>sch</code>.
Wird ein Text dem Programm abgeschickt, berechnet er die häufigsten Trigramme des Textes und vergleicht diese mit den 
gespeicherten Durchschnittswerten jeder eingebauten Sprache.
<br />
<br />
Ab 5 bis 7 Wörtern ist diese Methode sehr zuverlässig. <b>Eine kleine Prozentzahl bedeutet nicht, dass der Text nicht gut mit den 
typischen Buchstabenfolgen einer Sprache übereinstimmt.</b> Je länger der Text ist, desto grösser sind die Abweichungen vom berechneten 
Durchschnitt. Die Prozentzahl liegt meistens zwischen 30% und 60%. Beim Vergleich mit den Sprachen ist die relative Differenz 
aussagekräftiger (z.B.  43% vs. 29%) als die absolute Prozentzahl.',

'words' => 'Aus  ausgewerteten Texten werden die häufigsten Wörter für jeder eingebauten Sprache ermittelt. Um Leistung und Zeit zu sparen, 
werden dabei nur die ersten 100 am häufigsten auftretende Wörter gespeichert. Das Programm sucht im zu bestimmenden Text nach diesen 
Wörtern.
<br />
<br />Es ist möglich, dass Sprachen auftauchen, die überhaupt nicht miteinander verwandt sind. Manche Wörter werden in verschiedenen 
Sprachen gleich geschrieben, obschon sie eine andere, unabhängige Bedeutung haben.',

'chars' => 'Sonderzeichen (spezielle Buchstaben wie á, ç, ë, õ, š, ű oder ż) geben Hinweise auf eine oder mehreren Sprachen. Die 
Ergebnisse dieses Tests werden im Endresultat jedoch nicht fest gewichtet, weil Lehnwörter - Wörter, die aus einer anderen Sprache 
genommen wurden - im Text vorkommen könnten (z.B. <code>clich<b>é</b></code> oder <code>na<b>ï</b>ve</code> in einem englischen Text).',

'cyrillic' => 'Die Sprachen, die das kyrillische Alphabet verwenden, benutzen nicht alle die gleichen Buchstaben. 
So können einige Buchstaben auf gewisse Sprachen deuten, wie der Buchstabe <code>Є</code> auf Ukrainisch, <code>Ы</code> auf Russisch und 
Weissrussisch. Der Buchstabe <code>Ћ</code> wird nur in Serbisch und Montenegrinisch gebraucht.',

'scripts' => 'Da Chinesisch, Japanisch, Koreanisch, Griechisch, Tamilisch und weitere Sprachen mit Schriften geschrieben werden, in denen sonst keine 
Sprachen geschrieben werden (ausser kleinen Minderheitssprachen), wird für diese Sprachen nur die Schrift determiniert und dann angenommen, 
es handelt sich um die primäre Sprache, die die betreffende Schrift benutzt.
<br />
<br />Für das Schreiben von Japanisch werden auch chinesische Zeichen benutzt; <b>normalerweise</b>
kommen dabei mehr japanische als chinesische Zeichen vor. Chinesische Dialekte (z.B. Mandarin, Kantonesisch) werden nicht näher bestimmt.',

'bks' => 'Dieses Programm hält Bosnisch, Kroatisch und Serbisch nicht auseinander, da die Unterschiede minim sind<sup>[1]</sup> und weil die 
Definition, dass es bei sich bei Bosnisch, Kroatisch und Serbisch um eigene Sprachen handelt, eher aus politischen Gründen stammt. 
(Die Unterschiede zwischen Schweizerdeutsch und Hochdeutsch sind wesentlich grösser.)
<br />
<br />Heutzutage ist Serbisch die einzige der drei Sprachen, die auch im kyrillischen Alphabet geschrieben werden kann.
<br />
<br />[1] Siehe <a href="http://de.wikipedia.org/wiki/Unterschiede_zwischen_den_serbokroatischen_Standardvariet%C3%A4ten#Textbeispiele">
Wikipedia: Unterschiede zwischen den serbokroatischen Standardvarietäten</a>',

'similar' => 'Gewisse Sprachen sind sich so ähnlich, dass sich die Sprecher untereinander, zumindest zu einem gewissen Mass, 
verstehen können. Diese sind auch in schriftlicher Form (für Nicht-Sprecher) schwierig auseinanderzuhalten, weshalb weitere Tests, 
wie die Suche nach Wörtern oder Buchstabenkombinationen, die nur in einer Sprache vorkommen, ausgeführt werden.
<br />
<br />Bei Folgenden Sprachgruppen werden weitere Tests ausgeführt:<ul>
<li>Dänisch, Norwegisch und Schwedisch</li>
<li>Slowakisch und Tschechisch</li>
</ul>',


);




/// 
if(!isset($in_info)){
	if(isset($_SERVER['QUERY_STRING']) && strlen(trim($_SERVER['QUERY_STRING'])) > 0){
		$id = $_SERVER['QUERY_STRING'];
		if(isset($explain[$id])){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Hilfe - Sprachenidentifizierung</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
 </head>
 <body style="font-size: 10pt; margin: 18px; text-align: justify">
  <h2>Hilfe</h2>
<p><?php echo $explain[$id]; ?><br /><br /></p>
<p style="background-image: url('./img/cross.png'); background-repeat: no-repeat; background-position: 0 1px; padding-left: 18px"><a href="javascript:window.close()">Fenster schliessen</a></p>
 </body>
</html>



<?php
		}
	}
}
?>