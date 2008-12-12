<?php
//
// Definition of GWUpdateHidden class
//
// Created on: <04-Jun-2008 12:37:40 br>
//
// Copyright (C) 2008-2008 Grenland Web as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE included in
// the packaging of this file.
//
// Licencees holding a valid "eZ publish professional licence" version 2
// may use this file in accordance with the "eZ publish professional licence"
// version 2 Agreement provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" version 2 is available at
// http://ez.no/ez_publish/licences/professional/ and in the file
// PROFESSIONAL_LICENCE included in the packaging of this file.
// For pricing of this licence please contact us via e-mail to licence@ez.no.
// Further contact information is available at http://ez.no/company/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file gwupdatehidden.php
*/

/*!
  \class GWUpdateHidden gwupdatehidden.php
  \brief The class GWUpdateHidden does

*/

class GWUpdateHidden
{
    /*!
     Constructor
    */
    function GWUpdateHidden()
    {
        $this->Keys = array( 'id',
                             'version',
                             'publish_class',
                             'publish_attribute',
                             'unpublish_class',
                             'unpublish_attribute',
                             'modify_date' );

        $this->FunctionKeys = array( 'classAttributes' => 'class_attributes',
                                     'publishClassArray' => 'publish_class_array',
                                     'publishAttributeArray' => 'publish_attribute_array',
                                     'unpublishClassArray' => 'unpublish_class_array',
                                     'unpublishAttributeArray' => 'unpublish_attribute_array',
                                     'publishIDArray' => 'publish_id_array',
                                     'unpublishIDArray' => 'unpublish_id_array');

        $this->AllKeys = array_merge( $this->Keys, $this->FunctionKeys );
        sort( $this->AllKeys );
    }

    /*!
      Create a new object
    */
    static function create( $id, $version,
                            $publishClass, $publishAttribute,
                            $unpublishClass, $unpublishAttribute,
                            $modifyDate )
    {
        $updateHidden = new GWUpdateHidden();
        $updateHidden->setAttribute( 'id', $id );
        $updateHidden->setAttribute( 'version', $version );

        $updateHidden->setAttribute( 'publish_class', $publishClass );
        $updateHidden->setAttribute( 'publish_attribute', $publishAttribute );

        $updateHidden->setAttribute( 'unpublish_class', $unpublishClass );
        $updateHidden->setAttribute( 'unpublish_attribute', $unpublishAttribute );

        $updateHidden->setAttribute( 'modify_date', $modifyDate );

        return $updateHidden;
    }

    function setAttribute( $key, $value )
    {
        if ( in_array( $key, $this->Keys ) )
        {
            $this->$key = $value;
        }
    }

    function attribute( $key )
    {
        $value = false;
        if ( in_array( $key, $this->Keys ) )
        {
            $value = $this->$key;
        }
        else if ( in_array( $key, $this->FunctionKeys ) )
        {
            $functionName = array_search( $key, $this->FunctionKeys );
            if ( $functionName !== false )
            {
                $value = $this->$functionName( $key );
            }
        }
        return $value;
    }

    function publishIDArray( $key )
    {
        $classArray = explode( ",", $this->publish_class );
        $attrArray = explode( ",", $this->publish_attribute );
        $contentArray = array();
        $classCount = count( $classArray );
        for ( $i=0; $i < $classCount; $i++ )
        {
            $contentArray[] = $classArray[$i] . "-" . $attrArray[$i];
        }
        return $contentArray;
    }

    function unpublishIDArray( $key )
    {
        $classArray = explode( ",", $this->unpublish_class );
        $attrArray = explode( ",", $this->unpublish_attribute );
        $contentArray = array();
        $classCount = count( $classArray );
        for ( $i=0; $i < $classCount; $i++ )
        {
            $contentArray[] = $classArray[$i] . "-" . $attrArray[$i];
        }
        return $contentArray;
    }

    function publishClassArray( $key )
    {
        $value = explode( ",", $this->publish_class );
        return $value;
    }

    function publishAttributeArray( $key )
    {
        $value = explode( ",", $this->publish_attribute );
        return $value;
    }

    function unpublishClassArray( $key )
    {
        $value = explode( ",", $this->unpublish_class );
        return $value;
    }

    function unpublishAttributeArray( $key )
    {
        $value = explode( ",", $this->unpublish_attribute );
        return $value;
    }


    function attributes()
    {
        return $this->AllKeys;
    }

    function hasAttribute( $attr )
    {
        return in_array( $attr, $this->attributes() );
    }

    function extractID( $idVariable, &$idClassString, &$idAttributeString )
    {
        if ( is_array( $idVariable ) )
        {
            foreach ( $idVariable as $id )
            {
                list( $classID, $attributeID ) = split( "-", $id );
                if ( $idClassString != '' )
                {
                    $idClassString .= ',';
                }
                $idClassString .= $classID;

                if ( $idAttributeString != '' )
                {
                    $idAttributeString .= ',';
                }
                $idAttributeString .= $attributeID;
            }
        }
    }

    function classAttributes()
    {
        $db = eZDB::instance();
        $query = "SELECT ezcontentclass.id as contentclass_id,
                         ezcontentclass.identifier as contentclass_identifier,
                         ezcontentclass_attribute.id as contentclass_attribute_id,
                         ezcontentclass_attribute.identifier as contentclass_attribute_identifier
                  FROM ezcontentclass, ezcontentclass_attribute
                  WHERE ezcontentclass.id=ezcontentclass_attribute.contentclass_id AND
                        ezcontentclass_attribute.data_type_string='ezdatetime'
                  ORDER BY ezcontentclass.identifier";
        $resultArray = $db->arrayQuery( $query );

        $contentArray = array();
        foreach ( $resultArray as $result )
        {
            $contentArray[] = array( 'class' => eZContentClass::fetch( $result['contentclass_id'] ),
                                'class_attribute' => eZContentClassAttribute::fetch( $result['contentclass_attribute_id'] ),
                                'id' => $result['contentclass_id'] . '-' . $result['contentclass_attribute_id'] );
        }
        return $contentArray;
    }

    var $Keys;
}

?>
