<?php

//
/*
simplexml44 Version 0.4.4

Copyright (c) 2006 - Ingo Schramm, Ister.ORG
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
    this list of conditions and the following disclaimer.

    * Redistributions in binary form must reproduce the above copyright notice,
    this list of conditions and the following disclaimer in the documentation
    and/or other materials provided with the distribution.

    * Neither the name of Ingo Schramm and Ister.ORG nor the names of its
    contributors may be used to endorse or promote products derived from this
    software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/



require_once('IsterXmlNode.php');
require_once('IsterObjectChecker.php');

/**
 * This class represents a SimpleXML element.
 *
 * This is part of the pure PHP4 implementation of PHP5's interface
 * SimpleXML. Due to the nature of PHP4 there are some differences to
 * the PHP5 class. Since PHP5's SimpleXML implements the new
 * ArrayIterator interface, this cannot be done with PHP4. So, you
 * cannot access a node's attributes with array syntax - you should
 * always use the attributes() method. Also, if you dump a PHP4
 * SimpleXMLElement using print_r() or something, you will notice much
 * more output compared to PHP5. This is because PHP4 does not know
 * about private or protected object properties.
 *
 * @package xml
 * @subpackage simplexml
 * @author Ingo Schramm
 * @copyright Copyright (c) 2005 Ister.ORG Ingo Schramm
 */
class IsterSimpleXMLElement extends IsterXmlNode
{

  /**
   * Constructor
   *
   */
  function IsterSimpleXMLElement()
  {
    parent::IsterXMLNode();
  }


  /**
   * Clone this from a given IsterXMLNode.
   * @param object IsterXMLNode
   * @return boolean
   */
  function init(&$template)
  {
    $checker = new IsterObjectChecker($this);
    if(! $checker->compareClass($template, 'IsterXMLNode') )
      return false;

    // this may not work with PHP5
    foreach( $template as $attr => $val ) {
      $this->$attr = $val;
    }

    // enable SimpleXML easy access to nodes
    foreach($this->___c as $idx => $child) {
      if( $child->___t == ISTER_XML_PENTITY ) {
        foreach( $child->___ns as $idx2 => $child ) {
			if( $child->___t == ISTER_XML_TAG) {
				$this->addSimpleNode($child->___n, $this->___c[$idx]->___ns[$idx2]);
			}
        }
        continue;
      }
      if( $child->___t == ISTER_XML_TAG) {
		$this->addSimpleNode($child->___n, $this->___c[$idx]);
      }//if type
    }//foreach
    return true;
  }


  /**
   * Get the children of this SimpleXMLElement.
   * @return array
   */
  function children()
  {
      $ch = array();
      $count = 0;
      foreach( $this->___c as $child ) {
          if( $child->___t == ISTER_XML_TAG )
              $ch[] = $child;
      }
      return $ch;
  }


  /**
   * Return an array with all the nodes attributes.
   *
   * @return array
   */
  function attributes()
  {
    return $this->___a;
  }
  
  
  /**
   * Return an array with all the nodes attributes.
   *
   * @return array
   */
  function setAttribute($name, $value)
  {
      $this->___a[$name]      = $value;
      $this->ref->___a[$name] = $value;
      return true;
  }

  /**
   * Return an array with all the nodes attributes.
   *
   * @return array
   */
  function setAttribute2All($name, $value, &$root, $parents = array())
  {
      $this->setAttribute($name, $value);
      
      if(count($parents) > 0) {
        $this->reflectChildAttribute($name, $value, $root, $parents);
      }
      
      return true;
  }
  

  /**
   * Return a nodes CDATA children as a single string.
   * @return mixed   string or null if no CDATA exist
   */
  function CDATA()
  {
    $txt = '';
    foreach( $this->___c as $child ) {
      switch( $child->___t ) {
      case ISTER_XML_CDATA:
        if( preg_match('/^(<!\[CDATA\[|]]>)$/', $child->___n) )
          continue;
        $txt .= $child->___n;
        break;
      case ISTER_XML_ENTITY:
        $txt .= $child->___ns;
        break;
      default:
        continue;
      }
    }
    return $txt;
  }


  /**
   * Add a CDATA node to this element.
   * @param string
   * @return boolean
   */
  function setCDATA($text)
  {
    if(! ($this->___t == ISTER_XML_TAG) || ($this->___t == ISTER_XML_DOCUMENT) )
      return $this->log('cannot add CDATA here', E_USER_WARNING, 'setCDATA');
    $cdata = new IsterXmlNode(ISTER_XML_CDATA, $this->___l + 1, $text);
    $this->___c      =  array($cdata->toSimpleXML());
    $this->ref->___c =& $this->___c;
    return true;
  }
  
  /**
   * Add a CDATA node to this element.
   * @param string
   * @return boolean
   */
  function setCDATA2All($text, &$root, $parents = array())
  {
    $this->setCDATA($text);
    
    if(count($parents) > 0) {
      $this->reflectChildCDATA($text, $root, $parents);
    }
    
    return true;
  }
  
   /**
   * Add a child node to this element.
   * @param string
   * @param string
   * @param object
   * @param array
   * @param boolean
   * @return boolean
   */
  function addChild($name, $cdata, &$root, $parents = array(), $multiple = false)
  {
    $child = new IsterSimpleXMLElement();
    $child->___a = array();
    $child->___ns = 0;
    $child->___t = ISTER_XML_TAG;
    $child->___l = $this->___l + 1;
    $child->___n = $name;
    $child->___c = array();
    
    if(!is_null($cdata)) {
        $child->setCDATA($cdata);
    }

    $this->append($child);
    $this->addSimpleNode($child->___n, $child, $multiple);

    if(count($parents) > 0) {
        $this->reflectChildAdd($child, $root, $parents);
    }

    return true;
  }
  
  /**
   * @access private
   */
  function &findChild(&$reference, $breadcrumb) {
      if(!is_object($reference) || count($breadcrumb) === 0) { return null; }
      
      $bc = $breadcrumb[0];
      $element = null;
      if(!is_numeric($bc)) {
        foreach(array_keys($reference->___c) as $pos) {
          $el = &$reference->___c[$pos];
          if($el->___n === $bc) {
              $element = &$el;
          }
        }
      } else {
          $element = &$reference;
      }
      
      if(!is_null($element)) {
        if(count($breadcrumb) === 1) { return $element; } 
        unset($breadcrumb[0]);

        return $this->findChild($element, array_values($breadcrumb), true);
      }
      
      return null;
  }
  
  /**
   * @access private
   */
  function reflectChildAdd($toAdd, &$startElem, $parents)
  {
    if(count($parents) === 0) { return; }

    for($i = 0; $i <= count($parents); $i++) {
        $find = $parents[$i];
        if(!isset($startElem->$find) && !isset($startElem[$find])) { return false; }

        if(isset($startElem->$find)) {
            $element = &$startElem->$find;
        } elseif(isset($startElem[$find])) {
            $element = &$startElem[$find];
        } else { return; }
        
        unset($parents[$i]);
        $found =& $this->findChild($element, array_values($parents));

        if(!is_object($found)) { return; }

        $found->append($toAdd);
    }
  }
  
  /**
   * @access private
   */
  function reflectChildCDATA($cdata, &$startElem, $parents)
  {
    if(count($parents) === 0) { return; }

    for($i = 0; $i <= count($parents); $i++) {
        $find = $parents[$i];
        if(!isset($startElem->$find) && !isset($startElem[$find])) { return false; }

        if(isset($startElem->$find)) {
            $element = &$startElem->$find;
        } elseif(isset($startElem[$find])) {
            $element = &$startElem[$find];
        } else { return; }
        
        unset($parents[$i]);
        $found =& $this->findChild($element, array_values($parents));

        if(!is_object($found)) { return; }

        $found->setCDATA($cdata);
    }
  }
  
  /**
   * @access private
   */
  function reflectChildAttribute($name, $attribute, &$startElem, $parents)
  {
    if(count($parents) === 0) { return; }

    for($i = 0; $i <= count($parents); $i++) {
        $find = $parents[$i];
        if(!isset($startElem->$find) && !isset($startElem[$find])) { return false; }

        if(isset($startElem->$find)) {
            $element = &$startElem->$find;
        } elseif(isset($startElem[$find])) {
            $element = &$startElem[$find];
        } else { return; }
        
        unset($parents[$i]);
        $found =& $this->findChild($element, array_values($parents));

        if(!is_object($found)) { return; }

        $found->setAttribute($name, $attribute);
    }
  }
  
  
  /**
   * @access private
   */
  function addSimpleNode($name, &$child, $multiple = false)
  {
    $new      =  $child;
    $new->ref =& $child;
    if(isset($this->$name) || $multiple) {
      if(isset($this->$name) && !is_array($this->$name)) {
        $first       = $this->$name;
        $this->$name = array();
        array_push($this->$name, $first);
      } 
      
      if(!is_array($this->$name)) { $this->$name = array(); }
      array_push($this->$name, $new);
    }
    else {
      $this->$name = $new;
    }
    return true;
  }

}
?>
