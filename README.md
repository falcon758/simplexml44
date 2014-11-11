simplexml44
===========

Work with xml in PHP4 (Extended version of simplexml44 by Ingo Schramm)

For those stuck to PHP4 for various reasons, like me, here is the extended 
version of this amazing class, now with the option of add child to nodes and
apply cdata, attributs changes to all parents.

Hope it helps someone.

Here are some examples how to make it work:

//Initialize xml instance
$xml = new IsterXmlSimpleXMLImpl();

//Load xml from a string 
$this->_xml = $xml->load_string('<?xml version="1.0" encoding="UTF-8"?>
        <Anything></Anything>');

//Load xml from a file 
$this->_xml = $xml->load_file('file/to/load');

//Add new node
1- First field is the new name of the nome
2- Second field is cdata if you want to add, null otherwise
3- The xml instance created above
4- The root to get to the new node, in this case just 'Anything'
$this->_xml->Anything->addChild('Anything2', null, $this->_xml, array('Anything'));

//Note that all nodes are acessible by object. You can now access $this->_xml->Anything->Anything2

//Add new attribute
1- First field is the new name of the attribute
2- Second field is the attribute value
3- The xml instance created above
4- The root to get to the node, in this case just ('Anything', 'Anything2')
$this->_xml->Anything->Anything2->setAttribute2All('teste', 'works', $this->_xml, array('Anything', 'Anything2'));

//Add CDATA
1- First field is the cdata text
3- The xml instance created above
4- The root to get to the node, in this case just ('Anything', 'Anything2')
$this->_xml->Anything->Anything2->setCDATA2All('teste', $this->_xml, array('Anything', 'Anything2'));
