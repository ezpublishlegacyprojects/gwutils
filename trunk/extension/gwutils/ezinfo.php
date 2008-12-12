<?php
//
// Definition of eZInfo class
//
// Created on: <05-Jun-2008 14:31:35 br>
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

/*! \file ezinfo.php
*/

/*!
  \class eZInfo ezinfo.php
  \brief The class eZInfo does

*/

class GWUtilsInfo
{
    static function info()
    {
        return array( 'Name' => "Grenland Web utils",
                      'Version' => "1.0",
                      'Copyright' => "Copyright (C) 2008 Grenland Web AS",
                      'License' => "GNU General Public License v2.0"
                     );
    }
}

?>
