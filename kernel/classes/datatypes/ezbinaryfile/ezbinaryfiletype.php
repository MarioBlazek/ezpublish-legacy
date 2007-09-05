<?php
//
// Definition of eZBinaryFileType class
//
// Created on: <30-Apr-2002 13:06:21 bf>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.9.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*!
  \class eZBinaryFileType ezbinaryfiletype.php
  \ingroup eZDatatype
  \brief The class eZBinaryFileType handles files and association with content objects

*/

include_once( "kernel/classes/ezdatatype.php" );
include_once( "kernel/classes/datatypes/ezbinaryfile/ezbinaryfile.php" );
include_once( "kernel/classes/ezbinaryfilehandler.php" );
include_once( "lib/ezfile/classes/ezfile.php" );
include_once( "lib/ezutils/classes/ezsys.php" );
include_once( "lib/ezutils/classes/ezmimetype.php" );
include_once( "lib/ezutils/classes/ezhttpfile.php" );

define( 'EZ_DATATYPESTRING_MAX_BINARY_FILESIZE_FIELD', 'data_int1' );
define( 'EZ_DATATYPESTRING_MAX_BINARY_FILESIZE_VARIABLE', '_ezbinaryfile_max_filesize_' );
define( "EZ_DATATYPESTRING_BINARYFILE", "ezbinaryfile" );

class eZBinaryFileType extends eZDataType
{
    function eZBinaryFileType()
    {
        $this->eZDataType( EZ_DATATYPESTRING_BINARYFILE, ezi18n( 'kernel/classes/datatypes', "File", 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
    }

    /*!
     \return the binary file handler.
    */
    function &fileHandler()
    {
        return eZBinaryFileHandler::instance();
    }

    /*!
     \reimp
     \return the template name which the handler decides upon.
    */
    function &viewTemplate( &$contentobjectAttribute )
    {
        $handler =& $this->fileHandler();
        $handlerTemplate = $handler->viewTemplate( $contentobjectAttribute );
        $template = $this->DataTypeString;
        if ( $handlerTemplate !== false )
            $template .= '_' . $handlerTemplate;
        return $template;
    }

    /*!
     \return the template name to use for editing the attribute.
     \note Default is to return the datatype string which is OK
           for most datatypes, if you want dynamic templates
           reimplement this function and return a template name.
     \note The returned template name does not include the .tpl extension.
     \sa viewTemplate, informationTemplate
    */
    function &editTemplate( &$contentobjectAttribute )
    {
        $handler =& $this->fileHandler();
        $handlerTemplate = $handler->editTemplate( $contentobjectAttribute );
        $template = $this->DataTypeString;
        if ( $handlerTemplate !== false )
            $template .= '_' . $handlerTemplate;
        return $template;
    }

    /*!
     \return the template name to use for information collection for the attribute.
     \note Default is to return the datatype string which is OK
           for most datatypes, if you want dynamic templates
           reimplement this function and return a template name.
     \note The returned template name does not include the .tpl extension.
     \sa viewTemplate, editTemplate
    */
    function &informationTemplate( &$contentobjectAttribute )
    {
        $handler =& $this->fileHandler();
        $handlerTemplate = $handler->informationTemplate( $contentobjectAttribute );
        $template = $this->DataTypeString;
        if ( $handlerTemplate !== false )
            $template .= '_' . $handlerTemplate;
        return $template;
    }

    /*!
     Sets value according to current version
    */
    function initializeObjectAttribute( &$contentObjectAttribute, $currentVersion, &$originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $contentObjectAttributeID = $originalContentObjectAttribute->attribute( "id" );
            $version = $contentObjectAttribute->attribute( "version" );
            $oldfile = eZBinaryFile::fetch( $contentObjectAttributeID, $currentVersion );
            if ( $oldfile != null )
            {
                $oldfile->setAttribute( 'contentobject_attribute_id', $contentObjectAttribute->attribute( 'id' ) );
                $oldfile->setAttribute( "version",  $version );
                $oldfile->store();
            }
        }
    }

    /*!
     Delete stored attribute
    */
    function deleteStoredObjectAttribute( &$contentObjectAttribute, $version = null )
    {
        $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );
        $sys =& eZSys::instance();
        $storage_dir = $sys->storageDirectory();

        require_once( 'kernel/classes/ezclusterfilehandler.php' );
        if ( $version == null )
        {
            $binaryFiles = eZBinaryFile::fetch( $contentObjectAttributeID );
            eZBinaryFile::remove( $contentObjectAttributeID, null );

            foreach ( array_keys( $binaryFiles ) as $key )
            {
                $binaryFile =& $binaryFiles[$key];

                $mimeType =  $binaryFile->attribute( "mime_type" );
                list( $prefix, $suffix ) = split ('[/]', $mimeType );
                $orig_dir = $storage_dir . '/original/' . $prefix;
                $fileName = $binaryFile->attribute( "filename" );

                // Check if there are any other records in ezbinaryfile that point to that fileName.
                $binaryObjectsWithSameFileName = eZBinaryFile::fetchByFileName( $fileName );

                $filePath = $orig_dir . "/" . $fileName;
                $file = eZClusterFileHandler::instance( $filePath );

                if ( $file->exists() and count( $binaryObjectsWithSameFileName ) < 1 )
                    $file->delete();
            }
        }
        else
        {
            $count = 0;
            $binaryFile = eZBinaryFile::fetch( $contentObjectAttributeID, $version );
            if ( $binaryFile != null )
            {
                $mimeType =  $binaryFile->attribute( "mime_type" );
                list( $prefix, $suffix ) = split ('[/]', $mimeType );
                $orig_dir = $storage_dir . "/original/" . $prefix;
                $fileName = $binaryFile->attribute( "filename" );

                eZBinaryFile::remove( $contentObjectAttributeID, $version );

                // Check if there are any other records in ezbinaryfile that point to that fileName.
                $binaryObjectsWithSameFileName = eZBinaryFile::fetchByFileName( $fileName );

                $filePath = $orig_dir . "/" . $fileName;
                $file = eZClusterFileHandler::instance( $filePath );

                if ( $file->exists() and count( $binaryObjectsWithSameFileName ) < 1 )
                    $file->delete();
            }
        }
    }

    /*!
     Checks if file uploads are enabled, if not it gives a warning.
    */
    function checkFileUploads()
    {
        $isFileUploadsEnabled = ini_get( 'file_uploads' ) != 0;
        if ( !$isFileUploadsEnabled )
        {
            $isFileWarningAdded =& $GLOBALS['eZBinaryFileTypeWarningAdded'];
            if ( !isset( $isFileWarningAdded ) or
                 !$isFileWarningAdded )
            {
                eZAppendWarningItem( array( 'error' => array( 'type' => 'kernel',
                                                              'number' => EZ_ERROR_KERNEL_NOT_AVAILABLE ),
                                            'text' => ezi18n( 'kernel/classes/datatypes',
                                                              'File uploading is not enabled. Please contact the site administrator to enable it.' ) ) );
                $isFileWarningAdded = true;
            }
        }
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        eZBinaryFileType::checkFileUploads();
        $classAttribute =& $contentObjectAttribute->contentClassAttribute();
        $mustUpload = false;
        $httpFileName = $base . "_data_binaryfilename_" . $contentObjectAttribute->attribute( "id" );
        $maxSize = 1024 * 1024 * $classAttribute->attribute( EZ_DATATYPESTRING_MAX_BINARY_FILESIZE_FIELD );

        if ( $contentObjectAttribute->validateIsRequired() )
        {
            $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );
            $version = $contentObjectAttribute->attribute( "version" );
            $binary = eZBinaryFile::fetch( $contentObjectAttributeID, $version );
            if ( $binary === null )
            {
                $mustUpload = true;
            }
        }

        $canFetchResult = eZHTTPFile::canFetch( $httpFileName, $maxSize );
        if ( $mustUpload && $canFetchResult == EZ_UPLOADEDFILE_DOES_NOT_EXIST )
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                 'A valid file is required.' ) );
            return EZ_INPUT_VALIDATOR_STATE_INVALID;
        }
        if ( $canFetchResult == EZ_UPLOADEDFILE_EXCEEDS_PHP_LIMIT )
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                'The size of the uploaded file exceeds the limit set by the upload_max_filesize directive in php.ini.' ) );
            return EZ_INPUT_VALIDATOR_STATE_INVALID;
        }
        if ( $canFetchResult == EZ_UPLOADEDFILE_EXCEEDS_MAX_SIZE )
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                 'The size of the uploaded file exceeds the maximum upload size: %1 bytes.' ), $maxSize );
            return EZ_INPUT_VALIDATOR_STATE_INVALID;
        }
        return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
    }

    /*!
     Fetches the http post var integer input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        eZBinaryFileType::checkFileUploads();
        if ( !eZHTTPFile::canFetch( $base . "_data_binaryfilename_" . $contentObjectAttribute->attribute( "id" ) ) )
            return false;

        $binaryFile =& eZHTTPFile::fetch( $base . "_data_binaryfilename_" . $contentObjectAttribute->attribute( "id" ) );

        $contentObjectAttribute->setContent( $binaryFile );

        //$binaryFile =& $contentObjectAttribute->content();

        if ( get_class( $binaryFile ) == "ezhttpfile" )
        {
            $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );
            $version = $contentObjectAttribute->attribute( "version" );

            /*
            $mimeObj = new  eZMimeType();
            $mimeData = $mimeObj->findByURL( $binaryFile->attribute( "original_filename" ), true );
            $mime = $mimeData['name'];
            */

            $mimeData = eZMimeType::findByFileContents( $binaryFile->attribute( "original_filename" ) );
            $mime = $mimeData['name'];

            if ( $mime == '' )
            {
                $mime = $binaryFile->attribute( "mime_type" );
            }
            $extension = preg_replace('/.*\.(.+?)$/', '\\1', $binaryFile->attribute( "original_filename" ) );
            $binaryFile->setMimeType( $mime );
            if ( !$binaryFile->store( "original", $extension ) )
            {
                eZDebug::writeError( "Failed to store http-file: " . $binaryFile->attribute( "original_filename" ),
                                     "eZBinaryFileType" );
                return false;
            }

            $binary = eZBinaryFile::fetch( $contentObjectAttributeID, $version );
            if ( $binary === null )
                $binary = eZBinaryFile::create( $contentObjectAttributeID, $version );

            $orig_dir = $binaryFile->storageDir( "original" );

            $binary->setAttribute( "contentobject_attribute_id", $contentObjectAttributeID );
            $binary->setAttribute( "version", $version );
            $binary->setAttribute( "filename", basename( $binaryFile->attribute( "filename" ) ) );
            $binary->setAttribute( "original_filename", $binaryFile->attribute( "original_filename" ) );
            $binary->setAttribute( "mime_type", $mime );

            $binary->store();

            // VS-DBFILE

            require_once( 'kernel/classes/ezclusterfilehandler.php' );
            $filePath = $binaryFile->attribute( 'filename' );
            $fileHandler = eZClusterFileHandler::instance();
            $fileHandler->fileStore( $filePath, 'binaryfile', true, $mime );

            $contentObjectAttribute->setContent( $binary );
        }
        return true;
    }

    /*!
     Does nothing, since the file has been stored. See fetchObjectAttributeHTTPInput for the actual storing.
    */
    function storeObjectAttribute( &$contentObjectAttribute )
    {
    }

    function customObjectAttributeHTTPAction( $http, $action, &$contentObjectAttribute )
    {
        eZBinaryFileType::checkFileUploads();
        if( $action == "delete_binary" )
        {
            $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );
            $version = $contentObjectAttribute->attribute( "version" );
            $this->deleteStoredObjectAttribute( $contentObjectAttribute, $version );
        }
    }

    /*!
     \reimp
     HTTP file insertion is supported.
    */
    function isHTTPFileInsertionSupported()
    {
        return true;
    }

    /*!
     \reimp
     HTTP file insertion is supported.
    */
    function isRegularFileInsertionSupported()
    {
        return true;
    }

    /*!
     \reimp
     Inserts the file using the eZBinaryFile class.
    */
    function insertHTTPFile( &$object, $objectVersion, $objectLanguage,
                             &$objectAttribute, &$httpFile, $mimeData,
                             &$result )
    {
        $result = array( 'errors' => array(),
                         'require_storage' => false );
        $errors =& $result['errors'];
        $attributeID = $objectAttribute->attribute( 'id' );

        $binary = eZBinaryFile::fetch( $attributeID, $objectVersion );
        if ( $binary === null )
            $binary = eZBinaryFile::create( $attributeID, $objectVersion );

        $httpFile->setMimeType( $mimeData['name'] );

        $db =& eZDB::instance();
        $db->begin();

        if ( !$httpFile->store( "original", false, false ) )
        {
            $errors[] = array( 'description' => ezi18n( 'kernel/classes/datatypes/ezbinaryfile',
                                                        'Failed to store file %filename. Please contact the site administrator.', null,
                                                        array( '%filename' => $httpFile->attribute( "original_filename" ) ) ) );
            return false;
        }


        $filePath = $binary->attribute( 'filename' );

        $binary->setAttribute( "contentobject_attribute_id", $attributeID );
        $binary->setAttribute( "version", $objectVersion );
        $binary->setAttribute( "filename", basename( $httpFile->attribute( "filename" ) ) );
        $binary->setAttribute( "original_filename", $httpFile->attribute( "original_filename" ) );
        $binary->setAttribute( "mime_type", $mimeData['name'] );

        $binary->store();

        // SP-DBFILE

        require_once( 'kernel/classes/ezclusterfilehandler.php' );
        $filePath = $httpFile->attribute( 'filename' );
        $fileHandler = eZClusterFileHandler::instance();
        $fileHandler->fileStore( $filePath, 'binaryfile', true, $mimeData['name'] );
        $objectAttribute->setContent( $binary );

        $db->commit();

        $objectAttribute->setContent( $binary );

        return true;
    }

    /*!
     \reimp
     Inserts the file using the eZBinaryFile class.
    */
    function insertRegularFile( &$object, $objectVersion, $objectLanguage,
                                &$objectAttribute, $filePath,
                                &$result )
    {
        $result = array( 'errors' => array(),
                         'require_storage' => false );
        $errors =& $result['errors'];
        $attributeID = $objectAttribute->attribute( 'id' );

        $binary = eZBinaryFile::fetch( $attributeID, $objectVersion );
        if ( $binary === null )
            $binary = eZBinaryFile::create( $attributeID, $objectVersion );

        $fileName = basename( $filePath );
        $mimeData = eZMimeType::findByFileContents( $filePath );
        $storageDir = eZSys::storageDirectory();
        list( $group, $type ) = explode( '/', $mimeData['name'] );
        $destination = $storageDir . '/original/' . $group;
        $oldumask = umask( 0 );
        if ( !eZDir::mkdir( $destination, false, true ) )
        {
            umask( $oldumask );
            return false;
        }
        umask( $oldumask );
        $destFileName = md5( basename( $fileName ) . microtime() . mt_rand() );
        $destination = $destination . '/' . $destFileName;
        copy( $filePath, $destination );

        // SP-DBFILE
        require_once( 'kernel/classes/ezclusterfilehandler.php' );
        $fileHandler = eZClusterFileHandler::instance();
        $fileHandler->fileStore( $destination, 'binaryfile', true, $mimeData['name'] );


        $binary->setAttribute( "contentobject_attribute_id", $attributeID );
        $binary->setAttribute( "version", $objectVersion );
        $binary->setAttribute( "filename", $destFileName );
        $binary->setAttribute( "original_filename", $fileName );
        $binary->setAttribute( "mime_type", $mimeData['name'] );

        $binary->store();

        $objectAttribute->setContent( $binary );
        return true;
    }

    /*!
      \reimp
      We support file information
    */
    function hasStoredFileInformation( &$object, $objectVersion, $objectLanguage,
                                       &$objectAttribute )
    {
        return true;
    }

    /*!
      \reimp
      Extracts file information for the binaryfile entry.
    */
    function storedFileInformation( &$object, $objectVersion, $objectLanguage,
                                    &$objectAttribute )
    {
        $binaryFile = eZBinaryFile::fetch( $objectAttribute->attribute( "id" ),
                                            $objectAttribute->attribute( "version" ) );
        if ( $binaryFile )
        {
            return $binaryFile->storedFileInfo();
        }
        return false;
    }
    /*!
      \reimp
      Updates download count for binary file.
    */
    function handleDownload( &$object, $objectVersion, $objectLanguage,
                             &$objectAttribute )
    {
        $binaryFile = eZBinaryFile::fetch( $objectAttribute->attribute( "id" ),
                                            $objectAttribute->attribute( "version" ) );

        $contentObjectAttributeID = $objectAttribute->attribute( 'id' );
        $version =  $objectAttribute->attribute( "version" );

        if ( $binaryFile )
        {
            $db =& eZDB::instance();
            $db->query( "UPDATE ezbinaryfile SET download_count=(download_count+1)
                         WHERE
                         contentobject_attribute_id=$contentObjectAttributeID AND version=$version" );
            return true;
        }
        return false;
    }

    function fetchClassAttributeHTTPInput( &$http, $base, &$classAttribute )
    {
        $filesizeName = $base . EZ_DATATYPESTRING_MAX_BINARY_FILESIZE_VARIABLE . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $filesizeName ) )
        {
            $filesizeValue = $http->postVariable( $filesizeName );
            $classAttribute->setAttribute( EZ_DATATYPESTRING_MAX_BINARY_FILESIZE_FIELD, $filesizeValue );
        }
    }
    /*!
     Returns the object title.
    */
    function title( &$contentObjectAttribute,  $name = "original_filename" )
    {
        $value = false;
        $binaryFile = eZBinaryFile::fetch( $contentObjectAttribute->attribute( 'id' ),
                                           $contentObjectAttribute->attribute( 'version' ) );
        if ( is_object( $binaryFile ) )
            $value = $binaryFile->attribute( $name );

        return $value;
    }

    function hasObjectAttributeContent( &$contentObjectAttribute )
    {
        $binaryFile = eZBinaryFile::fetch( $contentObjectAttribute->attribute( "id" ),
                                            $contentObjectAttribute->attribute( "version" ) );
        if ( !$binaryFile )
            return false;
        return true;
    }

    function &objectAttributeContent( $contentObjectAttribute )
    {
        $binaryFile = eZBinaryFile::fetch( $contentObjectAttribute->attribute( "id" ),
                                            $contentObjectAttribute->attribute( "version" ) );
        if ( !$binaryFile )
        {
            $attrValue = false;
            return $attrValue;
        }
        return $binaryFile;
    }

    /*!
     \reimp
    */
    function isIndexable()
    {
        return true;
    }

    function &metaData( &$contentObjectAttribute )
    {
        $binaryFile =& $contentObjectAttribute->content();

        $metaData = "";
        if ( get_class( $binaryFile ) == "ezbinaryfile" )
        {
            $metaData = $binaryFile->metaData();
        }
        return $metaData;
    }

    /*!
     \reimp
    */
    function serializeContentClassAttribute( &$classAttribute, &$attributeNode, &$attributeParametersNode )
    {
        $maxSize = $classAttribute->attribute( EZ_DATATYPESTRING_MAX_BINARY_FILESIZE_FIELD );
        $attributeParametersNode->appendChild( eZDOMDocument::createElementTextNode( 'max-size', $maxSize,
                                                                                     array( 'unit-size' => 'mega' ) ) );
    }

    /*!
     \reimp
    */
    function unserializeContentClassAttribute( &$classAttribute, &$attributeNode, &$attributeParametersNode )
    {
        $maxSize = $attributeParametersNode->elementTextContentByName( 'max-size' );
        $sizeNode = $attributeParametersNode->elementByName( 'max-size' );
        $unitSize = $sizeNode->attributeValue( 'unit-size' );
        $classAttribute->setAttribute( EZ_DATATYPESTRING_MAX_BINARY_FILESIZE_FIELD, $maxSize );
    }

    /*!
     \return string representation of an contentobjectattribute data for simplified export

    */
    function toString( $objectAttribute )
    {
        $binaryFile = $objectAttribute->content();

        if ( is_object( $binaryFile ) )
        {
            return implode( '|', array( $binaryFile->attribute( 'filepath' ), $binaryFile->attribute( 'original_filename' ) ) );
        }
        else
            return '';
    }



    function fromString( &$objectAttribute, $string )
    {
        if( !$string )
            return true;

        $result = array();
        return $this->insertRegularFile( $objectAttribute->attribute( 'object' ),
                                         $objectAttribute->attribute( 'version' ),
                                         $objectAttribute->attribute( 'language_code' ),
                                         $objectAttribute,
                                         $string,
                                         $result );
    }

    /*!
     \param package
     \param content attribute

     \return a DOM representation of the content object attribute
    */
    function serializeContentObjectAttribute( &$package, &$objectAttribute )
    {
        $node = $this->createContentObjectAttributeDOMNode( $objectAttribute );

        $binaryFile = $objectAttribute->attribute( 'content' );
        if ( is_object( $binaryFile ) )
        {
            $fileKey = md5( mt_rand() );
            $package->appendSimpleFile( $fileKey, $binaryFile->attribute( 'filepath' ) );

            $fileNode = eZDOMDocument::createElementNode( 'binary-file' );
            $fileNode->appendAttribute( eZDOMDocument::createAttributeNode( 'filesize', $binaryFile->attribute( 'filesize' ) ) );
            $fileNode->appendAttribute( eZDOMDocument::createAttributeNode( 'filename', $binaryFile->attribute( 'filename' ) ) );
            $fileNode->appendAttribute( eZDOMDocument::createAttributeNode( 'original-filename', $binaryFile->attribute( 'original_filename' ) ) );
            $fileNode->appendAttribute( eZDOMDocument::createAttributeNode( 'mime-type', $binaryFile->attribute( 'mime_type' ) ) );
            $fileNode->appendAttribute( eZDOMDocument::createAttributeNode( 'filekey', $fileKey ) );
            $node->appendChild( $fileNode );
        }

        return $node;
    }

    /*!
     \reimp
     \param package
     \param contentobject attribute object
     \param ezdomnode object
    */
    function unserializeContentObjectAttribute( &$package, &$objectAttribute, $attributeNode )
    {
        $fileNode = $attributeNode->elementByName( 'binary-file' );
        if ( !is_object( $fileNode ) or !$fileNode->hasAttributes() )
        {
            return;
        }

        $binaryFile = eZBinaryFile::create( $objectAttribute->attribute( 'id' ), $objectAttribute->attribute( 'version' ) );

        $sourcePath = $package->simpleFilePath( $fileNode->attributeValue( 'filekey' ) );

        if ( !file_exists( $sourcePath ) )
        {
            eZDebug::writeError( "The file '$sourcePath' does not exist, cannot initialize file attribute with it",
                                 'eZBinaryFileType::unserializeContentObjectAttribute' );
            return false;
        }

        include_once( 'lib/ezfile/classes/ezdir.php' );
        $ini =& eZINI::instance();
        $mimeType = $fileNode->attributeValue( 'mime-type' );
        list( $mimeTypeCategory, $mimeTypeName ) = explode( '/', $mimeType );
        $destinationPath = eZSys::storageDirectory() . '/original/' . $mimeTypeCategory . '/';
        if ( !file_exists( $destinationPath ) )
        {
            $oldumask = umask( 0 );
            if ( !eZDir::mkdir( $destinationPath, eZDir::directoryPermission(), true ) )
            {
                umask( $oldumask );
                return false;
            }
            umask( $oldumask );
        }

        $basename = basename( $fileNode->attributeValue( 'filename' ) );
        while ( file_exists( $destinationPath . $basename ) )
        {
            $basename = substr( md5( mt_rand() ), 0, 8 ) . '.' . eZFile::suffix( $fileNode->attributeValue( 'filename' ) );
        }

        include_once( 'lib/ezfile/classes/ezfilehandler.php' );
        eZFileHandler::copy( $sourcePath, $destinationPath . $basename );
        eZDebug::writeNotice( 'Copied: ' . $sourcePath . ' to: ' . $destinationPath . $basename,
                              'eZBinaryFileType::unserializeContentObjectAttribute()' );

        $binaryFile->setAttribute( 'contentobject_attribute_id', $objectAttribute->attribute( 'id' ) );
        $binaryFile->setAttribute( 'filename', $basename );
        $binaryFile->setAttribute( 'original_filename', $fileNode->attributeValue( 'original-filename' ) );
        $binaryFile->setAttribute( 'mime_type', $fileNode->attributeValue( 'mime-type' ) );

        $binaryFile->store();

        // VS-DBFILE + SP DBFile fix

        require_once( 'kernel/classes/ezclusterfilehandler.php' );
        $fileHandler = eZClusterFileHandler::instance();
        $fileHandler->fileStore( $destinationPath . $basename, 'binaryfile', true );
    }

}

eZDataType::register( EZ_DATATYPESTRING_BINARYFILE, "ezbinaryfiletype" );

?>
