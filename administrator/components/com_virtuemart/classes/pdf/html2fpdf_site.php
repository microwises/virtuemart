<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* @version $Id$
* @package VirtueMart
* @subpackage HMTL2PDF
* @author Renato Coelho
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
/*/////////////////////////////////////////////////////////////////////////////
//////////////DO NOT MODIFY THE CONTENTS OF THIS BOX//////////////////////////
//////////////////////////////////////////////////////////////////////////////
//                                                                          //
// HTML2FPDF is a php script to read a HTML text and generate a PDF file.   //
// Copyright (C) 2004  Renato Coelho                                        //
// This script may be distributed as long as the following files are kept   //
// together: 	                             							    //
//	                                                               		    //
// fpdf.php, html2fpdf.php, gif.php, license.txt,credits.txt,htmltoolkit.php//
//                                                                          //
//////////////////////////////////////////////////////////////////////////////
*/

class PDF extends HTML2FPDF {

  function PDF()   {
  //! @return A class instance
  //! @desc Constructor
      //Call parent constructor
      $this->HTML2FPDF();
      //Disable some tags
      $this->DisableTags("<big>,<small>");
    //Disable <title>/CSS/<pre> in order to increase script performance
    $this->usetitle=false;
    $this->usecss=false;
    $this->usepre=false;
  }
  
  //Common Logo for all HTML files (Montfort)
  function InitLogo($src) {
    global $mosConfig_live_site;
  //! @desc Insert Image Logo on 1st page
  //! @return void
    if ($src == '') return;
    
    $this->x = $this->lMargin;
    $halfwidth = $this->pgwidth/2;
    $sizesarray = $this->Image($src, $this->GetX(), $this->GetY(), 0, 0,'','',false);
    
    //Alinhar imagem ao centro
    $this->y = $this->tMargin - $sizesarray['HEIGHT']/8;
    $this->x = $this->pgwidth- $sizesarray['WIDTH'];
    $sizesarray = $this->Image($src, $this->GetX(), $this->GetY(), 0, 0,'', $mosConfig_live_site );
    $this->Ln(1);
    //Contruir <HR> particular
      $this->SetLineWidth(0.3);
      $this->Line($this->x,$this->y,$this->x+$this->pgwidth,$this->y);
      $this->SetLineWidth(0.3);
      $this->Ln(2);
  }
  
  //Put title in page
  function PutTitle($titulo) {
  //! @desc Insert Title on 1st page
  //! @return void
    $this->SetTitle($titulo); 
    $this->Ln(10);
      $this->SetFont('Arial','B',22);
      $this->divalign="L";
    $this->divwidth = $this->pgwidth;
    $this->divheight = 8.5;
  
    //Custom Word Wrap (para melhorar organiza��o das palvras no titulo)
    $maxwidth = $this->divwidth;
    $titulo = trim($titulo);
    $words = preg_split('/ +/', $titulo);
    $space = $this->GetStringWidth(' ');
    $titulo = '';
    $width = 0;
    $numwords = count($words);
    for($i = 0 ; $i < $numwords ; $i++)
    {
      $word = $words[$i];
      if ($i + 1 < $numwords) $nextword = $words[$i+1];
      else $nextword = '';
      $wordwidth = $this->GetStringWidth($word);
      $nextwordwidth = $this->GetStringWidth($nextword);
      if((strlen($word) <= 3) and ($nextword != '') and ($width + $wordwidth + $nextwordwidth > $maxwidth))
      {
         //Para n�o ficar um artigo/preposi��o esquecido(a) no final de uma linha
         $width = $wordwidth + $space;
         $titulo = rtrim($titulo)."\n".$word.' ';
      }
      elseif ($width + $wordwidth <= $maxwidth) //Palavra cabe, inserir
      {
         $width += $wordwidth + $space;
         $titulo .= $word.' ';
      }
      else //Palavra n�o cabe, pular linha e inserir na outra linha
      {
         $width = $wordwidth + $space;
         $titulo = rtrim($titulo)."\n".$word.' ';
      }
    }
    $titulo = rtrim($titulo);
    //End of Custom WordWrap
    $this->textbuffer[] = array($titulo,'','',array());
  
    //Print content
    $this->printbuffer($this->textbuffer);
    //Reset values
    $this->textbuffer=array();
    $this->divwidth=0;
      $this->divheight=0;
      $this->divalign="L";
      $this->SetFont('Arial','',11);
  
    $this->Ln(4);
    //Contruir <HR> particular
    /* $this->SetLineWidth(0.3);
      $this->Line($this->x,$this->y,$this->x+$this->pgwidth,$this->y);
      $this->SetLineWidth(0.3);
      $this->Ln(2);*/
  }
  
  //Put author in page
  function PutAuthor($autor) {
  //! @desc Insert Author on 1st page
  //! @return void
    $this->SetAuthor($autor);
      $this->SetFont('Arial','',14);
      $this->SetStyle('B',true);
    $this->SetStyle('I',true);
    $texto = $autor;//'by author'
    $this->MultiCell(0,5,$texto,0,'R');
      $this->SetFont('Arial','',11);
      $this->SetStyle('B',false);
    $this->SetStyle('I',false);
  }
  
  //Page footer
  function Footer() {
      global $mosConfig_live_site, $vendor_name;
  //! @desc Insert footer on every page
  //! @return void
      //Position at 1.0 cm from bottom
      $this->SetY(-10);
      //Copyright //especial para esta vers�o
      $this->SetFont('Arial','B',9);
      $this->SetTextColor(0);
      $texto = "Copyright ".chr(169).date('Y')."  -  $vendor_name  -  ";
      $this->Cell($this->GetStringWidth($texto),10,$texto,0,0,'L');
      $this->SetTextColor(0,0,255);
      $this->SetStyle('U',true);
      $this->SetStyle('B',false);
      $this->Cell(0,10,$mosConfig_live_site,0,0,'L',0,$mosConfig_live_site);
      $this->SetStyle('U',false);
      $this->SetTextColor(0);
      //Arial italic 9
      $this->SetFont('Arial','I',9);
      //Page number
      $this->Cell(0,10, _PN_PAGE." ".$this->PageNo()." "._PN_OF." {nb}",0,0,'R');
      //Return Font to normal
      $this->SetFont('Arial','',11);
  }

}//end of class
?>
