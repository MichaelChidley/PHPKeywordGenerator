<?php

/*
-----------------------------------------------------------------------------------------------------------
Class: Keywords
Version: 1.0
Release Date: 
-----------------------------------------------------------------------------------------------------------
Overview:	Class to create keywords based upon how often words appear on a page
-----------------------------------------------------------------------------------------------------------
History:
25/07/2013      1.0	MJC	Created
-----------------------------------------------------------------------------------------------------------
Uses:

*/

class Keywords
{
        private $pageUrl;
        private $minOccurances;
        private $pageData;
        
        private $replace = '. ,';
        
        private $keywords;
        
        
        /*----------------------------------------------------------------------------------
      	Function:	getPage
      	Overview:	Function to return the page data using curl
      			
      	In:	
      	Out:	
	----------------------------------------------------------------------------------*/ 
        private function getPage()
        {
                $ch = curl_init();
          	curl_setopt($ch, CURLOPT_URL, $this->pageUrl);
          	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          	$data = curl_exec($ch);
          	curl_close($ch);
                
                $this->pageData = $data;
        }
        
        /*----------------------------------------------------------------------------------
      	Function:	refineOutput
      	Overview:	Function refine the output of the page data by removing common
                        words and miscellaneous characters
      			
      	In:	
      	Out:	
	----------------------------------------------------------------------------------*/         
        private function refineOutput()
        {
                $arrMiscwords = split("\n", file_get_contents('ignoredMiscWords.txt'));
                $arrCommonWords = split("\n", file_get_contents('commonWords.txt'));
                
                
                $dom = new DOMDocument;
                $dom->loadHTML($this->pageData);
                $bodies = $dom->getElementsByTagName('body');
                assert($bodies->length === 1);
                $body = $bodies->item(0);
                for ($i = 0; $i < $body->children->length; $i++) 
                {
                    $body->remove($body->children->item($i));
                }
                $string = $dom->saveHTML();
                
                
                $string = strip_tags($string);
                
                $indLines = explode(PHP_EOL, $string);
                
                
                $arrKeywords = array();     
                foreach($indLines as $indLine => $indData)
                {
                        if(trim($indData) == '')
                        {
                                unset($indLines[$indLine]);
                        }
                        $indLines[$indLine] = trim($indLines[$indLine]);
                }
                   
                
                foreach($indLines as $strIndLines => $data)
                {     
                       $arrExplodeEach = explode(' ',$data);
                       
                       foreach($arrExplodeEach as $strFLine => $strFData)
                       {
                                foreach($arrCommonWords as $arrComLines => $arrComData)
                                {
                                        if(strtolower(trim($strFData)) == strtolower(trim($arrComData)))
                                        {
                                                unset($arrExplodeEach[$strFLine]);
                                        }
                                }
                                
                                foreach($arrMiscwords as $arrMiscLines => $arrMiscData)
                                {
                                        if(strpos(trim($strFData), trim($arrMiscData)) !== false)
                                        {
                                                unset($arrExplodeEach[$strFLine]);
                                        }   
                                }
                                
                                
                                //If the word is a number or contains numbers, remove the element from the array
                                if((is_numeric($strFData)) || (preg_match('#[0-9]#',$strFData)))
                                {
                                        unset($arrExplodeEach[$strFLine]);
                                }
                                
                                //Word containing other characters such as ,. on the end of the word, replace them with nothing
                                $arrReplace = explode(' ', $this->replace);
                                foreach($arrReplace as $strReplace)
                                {
                                        if(strpos($strFData,$strReplace) !== false)
                                        {
                                                $arrExplodeEach[$strFLine] = str_replace($strReplace,'',$strFData);   
                                        }
                                }     
                       }
                                          
                           
                        foreach($arrExplodeEach as $strIndWord)
                        {
                                $strIndWord = strtolower($strIndWord);
                                if(array_key_exists($strIndWord, array_change_key_case($arrKeywords)))
                                {       
                                        $arrKeywords[$strIndWord] = $arrKeywords[$strIndWord] + 1;    
                                }
                                else
                                {
                                        $arrKeywords[$strIndWord] = 1; 
                                }
                        }
                }
                
                arsort($arrKeywords);
                foreach($arrKeywords as $keywordlines => $data)
                {
                        if(key($arrKeywords) == '')
                        {
                                unset($arrKeywords[$keywordlines]);
                        }
                        
                        if($data < $this->minOccurances)
                        {
                                unset($arrKeywords[$keywordlines]);
                        }                                
                }
                
                                
                $counter = 0;
                $limit = 13;
                $keywords = '';
                while($element = current($arrKeywords)) 
                {
                        if($counter < $limit)
                        {
                                $keywords .= key($arrKeywords).", ";
                                next($arrKeywords);
                        }
                        else
                        {
                                break;
                        }
                        $counter++;
                }        
                $this->keywords = rtrim($keywords, ", ");               
                
        }
        
        /*----------------------------------------------------------------------------------
      	Function:	keywords
      	Overview:	Constructor function that sets the page url and initiates the
                        program to filter the content for keywords
      			
      	In:      $strUrl         String          String containg the page url
                 $minOccurance   Int             Integer to specify a minimum amount of 
                                                 occurances for each word, to improve 
                                                 keyword suitability.	
      	Out:	
	----------------------------------------------------------------------------------*/    
        public function keywords($strUrl,$minOccurance=0)
        {
                $this->pageUrl = $strUrl;
                $this->minOccurances = $minOccurance; 
                $this->getPage();  
                $this->refineOutput(); 
        }
        
        /*----------------------------------------------------------------------------------
      	Function:	generateKeywords
      	Overview:	Function to return the value of the keyword class member containing 
                        the keywords
      			
      	In:      	
      	Out:     $this->keywords         String          String containing the class member
                                                         with the keywords in correct
                                                         formatting.	
	----------------------------------------------------------------------------------*/    
        public function generateKeywords()
        {
                return $this->keywords;  
        }

}


$gen = new Keywords("http://michaelchidley.co.uk",2);
echo $gen->generateKeywords();
?>