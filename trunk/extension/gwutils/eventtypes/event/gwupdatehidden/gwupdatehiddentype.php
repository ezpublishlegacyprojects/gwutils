<?php
//
// Definition of GWUpdateHiddenType class
//
// Created on: <03-Jun-2008 13:04:31 br>
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

/*! \file gwupdatehiddentype.php
*/

/*!
  \class GWUpdateHiddenType gwupdatehiddentype.php
  \brief The class GWUpdateHiddenType does

*/

class GWUpdateHiddenType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = 'gwupdatehidden';

    const MODIFYDATE = 'data_int1';

    const PUBLISH_CLASS = 'data_text1';
    const PUBLISH_ATTRIBUTE = 'data_text2';

    const UNPUBLISH_CLASS = 'data_text3';
    const UNPUBLISH_ATTRIBUTE = 'data_text4';

    /*!
     Constructor
    */
    function GWUpdateHiddenType()
    {
        $this->eZWorkflowEventType( self::WORKFLOW_TYPE_STRING,  ezi18n( 'gwupdatehidden/event', "Update hidden field" ) );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'after' ) ) ) );
    }

    /*!
      Executes the workflow.
    */
    function execute( $process, $event )
    {
        $returnStatus = eZWorkflowType::STATUS_ACCEPTED;

        $parameters = $process->attribute( 'parameter_list' );
        $object = eZContentObject::fetch( $parameters['object_id'] );

        if ( !$object )
        {
            eZDebugSetting::writeError( 'extension-workflow-updatehidden','The object with ID ' . $parameters['object_id'] . ' does not exist.', 'GWUpdateHiddenType::execute() object is unavailable' );
            return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
        }

        // if a newer object is the current version, abort this workflow.
        $currentVersion = $object->attribute( 'current_version' );
        $version = $object->version( $parameters['version'] );
        if ( $currentVersion != $version->attribute( 'version' ) )
        {
            return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
        }


        $objectAttributes = $version->attribute( 'contentobject_attributes' );

        $modifyPublishDate = $event->attribute( self::MODIFYDATE );

        $updateHiddenObject = $this->workflowEventContent( $event );

        $publishAttributeArray = $updateHiddenObject->attribute( 'publish_attribute_array' );
        $unpublishAttributeArray = $updateHiddenObject->attribute( 'unpublish_attribute_array' );

        $publishAttribute = false;
        $unpublishAttribute = false;

        foreach ( $objectAttributes as $objectAttribute )
        {
            $contentClassAttributeID = $objectAttribute->attribute( 'contentclassattribute_id' );
            if ( in_array( $contentClassAttributeID, $publishAttributeArray ) )
            {
                $publishAttribute = $objectAttribute;
            }

            if ( in_array( $contentClassAttributeID, $unpublishAttributeArray ) )
            {
                $unpublishAttribute = $objectAttribute;
            }
        }

        $currentDateTime = new eZDateTime();
        $currentTime = $currentDateTime->timestamp();
        if ( $publishAttribute instanceof eZContentObjectAttribute )
        {
            $date = $publishAttribute->attribute( 'content' );
            if ( $date instanceof eZDateTime )
            {
                $publishTimestamp = (int)$date->attribute( 'timestamp' );
                if ( $publishTimestamp != 0 )
                {
                    if ( $publishTimestamp != 0 )
                    {
                        $nodes = $object->attribute( 'assigned_nodes' );
                        $doUnhide = false;

                        eZContentCacheManager::clearContentCache( $object->attribute('id') );
                        eZContentCacheManager::generateObjectViewCache( $object->attribute('id') );
                        eZStaticCache::executeActions();

                        foreach ( $nodes as $node )
                        {
                            if ( $currentTime < $publishTimestamp )
                            {
                                eZContentObjectTreeNode::hideSubTree( $node, true );
                            }
                            else
                            {
                                eZContentObjectTreeNode::unhideSubTree( $node, true );
                                $doUnhide = true;
                            }
                        }

                        // check if the modification date should be modified.
                        $modifyPublishDate = $updateHiddenObject->attribute( 'modify_date' );
                        if ( $modifyPublishDate == 1  and
                             $date->isValid() and
                             $doUnhide === true )
                        {
                            $object->setAttribute( 'published', $date->timeStamp() );
                            $object->store();
                        }

                        // if the publish date is set in the future, it should be published later by a workfow.
                        if ( $currentTime < $publishTimestamp )
                        {
                            $returnStatus = eZWorkflowType::STATUS_DEFERRED_TO_CRON_REPEAT;
                        }
                    }
                }
            }
        }

        if ( $unpublishAttribute instanceof eZContentObjectAttribute )
        {
            $date = $unpublishAttribute->attribute( 'content' );
            if ( $date instanceof eZDateTime )
            {
                $unpublishTimestamp = (int)$date->attribute( 'timestamp' );

                if ( $unpublishTimestamp != 0 )
                {
                    if ( $currentTime > $unpublishTimestamp and
                         ( $currentTime > $publishTimestamp or
                           $publishTimestamp = 0 ) )
                    {
                        $nodes = $object->attribute( 'assigned_nodes' );
                        foreach ( $nodes as $node )
                        {
                            eZContentObjectTreeNode::hideSubTree( $node, true );
                        }
                    }

                    // if the unpublish date is set to the future, the object need to be unpublished by a cron job.
                    if ( $currentTime < $unpublishTimestamp )
                    {
                        $returnStatus = eZWorkflowType::STATUS_DEFERRED_TO_CRON_REPEAT;
                    }
                }
            }
        }
        return $returnStatus;
    }

    function attributes()
    {
        return array_merge( array( 'class_attributes' ),
                            eZWorkflowEventType::attributes() );
    }

    function hasAttribute( $attr )
    {
        return in_array( $attr, $this->attributes() );
    }

    function attribute( $attr )
    {
        $value = false;
        switch ( $attr )
        {
            case "class_attributes":
            {
                $updateHidden = new GWUpdateHidden();
                $value = $updateHidden->attribute( 'class_attributes' );
            } break;

            default:
            {
                $value = parent::attribute( $attr );
            }
        }
        return $value;
    }

    /*!
      Fetch the different post variables.
    */
    function fetchHTTPInput( $http, $base, $event )
    {
        $doUpdate = $base . "_data_updatehidden_do_update_" . $event->attribute( "id" );
        if ( $http->hasPostVariable( $doUpdate ) )
        {
            $updateHidden = new GWUpdateHidden();

            $modifyDateVariable = $base . "_data_updatehidden_modifydate_" . $event->attribute( "id" );
            if ( $http->hasPostVariable( $modifyDateVariable ) )
            {
                $event->setAttribute( 'data_int1', 1 );
            }
            else
            {
                $event->setAttribute( 'data_int1', 0 );
            }

            $publishDateVariable = $base . "_data_updatehidden_publish_attribute_" . $event->attribute( "id" );
            $publishDateClassString = '';
            $publishDateAttributeString = '';
            if ( $http->hasPostVariable( $publishDateVariable ) )
            {
                $publishDateValue = $http->postVariable( $publishDateVariable );
                $updateHidden->extractID( $publishDateValue, $publishDateClassString, $publishDateAttributeString );
            }
            $event->setAttribute( 'data_text1', $publishDateClassString );
            $event->setAttribute( 'data_text2', $publishDateAttributeString );

            $unpublishDateVariable = $base . "_data_updatehidden_unpublish_attribute_" . $event->attribute( "id" );
            $unpublishDateClassString = '';
            $unpublishDateAttributeString = '';
            if ( $http->hasPostVariable( $unpublishDateVariable ) )
            {
                $unpublishDateValue = $http->postVariable( $unpublishDateVariable );
                $updateHidden->extractID( $unpublishDateValue, $unpublishDateClassString, $unpublishDateAttributeString );
            }
            $event->setAttribute( 'data_text3', $unpublishDateClassString );
            $event->setAttribute( 'data_text4', $unpublishDateAttributeString );
        }
    }

    /*!
      Return the content of the event.
    */
    function workflowEventContent( $event )
    {
        $id = $event->attribute( "id" );
        $version = $event->attribute( "version" );

        $publishClass = $event->attribute( self::PUBLISH_CLASS );
        $publishAttribute = $event->attribute( self::PUBLISH_ATTRIBUTE );

        $unpublishClass = $event->attribute( self::UNPUBLISH_CLASS );
        $unpublishAttribute = $event->attribute( self::UNPUBLISH_ATTRIBUTE );

        $modifyDate = $event->attribute( self::MODIFYDATE );

        $updateHidden = GWUpdateHidden::create( $id, $version,
                                                $publishClass, $publishAttribute,
                                                $unpublishClass, $unpublishAttribute,
                                                $modifyDate );
        return $updateHidden;
    }
}

eZWorkflowEventType::registerEventType( GWUpdateHiddenType::WORKFLOW_TYPE_STRING, "GWUpdateHiddenType" );

?>
