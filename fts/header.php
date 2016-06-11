<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="Accept-Charset" content="UTF-8" />
  <title><?php echo $lang['title']; ?></title>
  <link rel="stylesheet" type="text/css" href="style.css" />
  <script type="text/javascript" src="jquery.js"></script>
  <script type="text/javascript" src="animatedcollapse.js">
   /* Animated Collapsible DIV v2.4- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
    *  This notice MUST stay intact for legal use
    *  Visit Dynamic Drive at http://www.dynamicdrive.com/ for this script and 100s more */
  </script>
  <script type="text/javascript">
   animatedcollapse.ontoggle=function($, divobj, state){	//fires each time a DIV is expanded/contracted
    //$: Access to jQuery
    //divobj: DOM reference to DIV being expanded/ collapsed. Use "divobj.id" to get its ID
    //state: "block" or "none", depending on state
   }

   animatedcollapse.init()
  </script>
  <script type="text/javascript">
   function vindov(helpid){
	newwindow = window.open('help.php?'+helpid, 'help','width=400,height=400,toolbar=no,location=no,scrollbars=yes');
	if(window.focus){ newwindow.focus() }
	return false;
   }
  </script>
  <style type="text/css">
   <!--
    div.title { background-position: <?php echo rand(-100, 0).'px '.rand(-250, 0); ?>px; }
   -->
  </style>
 </head>
 <body>
  <div id="wrapper">
  <div id="title">
   <h1><?php echo $lang['title_h1']; ?></h1>
  </div>
  <div id="content">

