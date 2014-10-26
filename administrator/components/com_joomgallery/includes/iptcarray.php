<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/includes/iptcarray.php $
// $Id: iptcarray.php 4076 2013-02-12 10:35:29Z erftralle $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

    $iptc_config_array = array
    (
      "IPTC" => array
      (
//            204 => array('Attribute'   => "Object Attribute Reference",
//                         'Name'        => JText::_('COM_JOOMGALLERY_IPTC_INTELLECTUALGENRE'),
//                         'Description' => JText::_('COM_JOOMGALLERY_IPTC_INTELLECTUALGENRE_DEFINITION'),
//                         'Group'       => "Object",
//                         'IMM'         => "2:004",
//                         'Format'      => "Characters",
//                         'Length'      => "256"
//                        ),
           205 => array('Attribute'   => "Object Name",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_TITLE'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_TITLE_DEFINITION'),
                        'Group'       => "Object",
                        'IMM'         => "2:005",
                        'Format'      => "Characters",
                        'Length'      => "64"
                       ),
           225 => array('Attribute'   => "Keywords",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_KEYWORDS'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_KEYWORDS_DEFINITION'),
                        'Group'       => "Keywords",
                        'IMM'         => "2:025",
                        'Format'      => "Characters",
                        'Length'      => "each max. 64"
                       ),
           240 => array('Attribute'   => "Special Instructions",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_INSTRUCTIONS'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_INSTRUCTIONS_DEFINITION'),
                        'Group'       => "Caption",
                        'IMM'         => "2:040",
                        'Format'      => "Characters",
                        'Length'      => "256"
                       ),
           255 => array('Attribute'   => "Date Created",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_DATECREATED'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_DATECREATED_DEFINITION'),
                        'Group'       => "Object",
                        'IMM'         => "2:055",
                        'Format'      => "Numeric",
                        'Length'      => "8"
                       ),
//            260 => array('Attribute'   => "Time Created",
//                         'Name'        => JText::_('COM_JOOMGALLERY_IPTC_TIMECREATED'),
//                         'Description' => JText::_('COM_JOOMGALLERY_IPTC_TIMECREATED_DEFINITION'),
//                         'Group'       => "Object",
//                         'IMM'         => "2:060",
//                         'Format'      => "Characters",
//                         'Length'      => "11"
//                        ),
           280 => array('Attribute'   => "By-line",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_CREATOR'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_CREATOR_DEFINITION'),
                        'Group'       => "Contact",
                        'IMM'         => "2:080",
                        'Format'      => "Characters",
                        'Length'      => "32"
                       ),
           285 => array('Attribute'   => "By-line Title",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_CREATORSJOBTITLE'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_CREATORSJOBTITLE_DEFINITION'),
                        'Group'       => "Contact",
                        'IMM'         => "2:085",
                        'Format'      => "Characters",
                        'Length'      => "32"
                       ),
           290 => array('Attribute'   => "City",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_CITYLEGACY'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_CITYLEGACY_DEFINITION'),
                        'Group'       => "Object",
                        'IMM'         => "2:090",
                        'Format'      => "Characters",
                        'Length'      => "32"
                       ),
           292 => array('Attribute'   => "Sublocation",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_SUBLOCATIONLEGACY'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_SUBLOCATIONLEGACY_DEFINITION'),
                        'Group'       => "Object",
                        'IMM'         => "2:092",
                        'Format'      => "Characters",
                        'Length'      => "32"
                       ),
           295 => array('Attribute'   => "Province/State",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_PROVINCEORSTATELEGACY'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_PROVINCEORSTATELEGACY_DEFINITION'),
                        'Group'       => "Object",
                        'IMM'         => "2:095",
                        'Format'      => "Characters",
                        'Length'      => "32"
                       ),
//           2100 => array('Attribute'   => "Country/Primary Location Code",
//                         'Name'        => JText::_('COM_JOOMGALLERY_IPTC_COUNTRYCODELEGACY'),
//                         'Description' => JText::_('COM_JOOMGALLERY_IPTC_COUNTRYCODELEGACY_DEFINITION'),
//                         'Group'       => "Object",
//                         'IMM'         => "2:100",
//                         'Format'      => "Characters",
//                         'Length'      => "2 or 3"
//                        ),
          2101 => array('Attribute'   => "Country/Primary Location Name",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_COUNTRYLEGACY'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_COUNTRYLEGACY_DEFINITION'),
                        'Group'       => "Object",
                        'IMM'         => "2:101",
                        'Format'      => "Characters",
                        'Length'      => "64"
                       ),
          2105 => array('Attribute'   => "Headline",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_HEADLINE'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_HEADLINE_DEFINITION'),
                        'Group'       => "Caption",
                        'IMM'         => "2:105",
                        'Format'      => "Characters",
                        'Length'      => "256"
                       ),
          2110 => array('Attribute'   => "Credit",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_CREDITLINE'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_CREDITLINE_DEFINITION'),
                        'Group'       => "Credit",
                        'IMM'         => "2:110",
                        'Format'      => "Characters",
                        'Length'      => "32"
                       ),
          2115 => array('Attribute'   => "Source",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_SOURCE'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_SOURCE_DEFINITION'),
                        'Group'       => "Credit",
                        'IMM'         => "2:115",
                        'Format'      => "Characters",
                        'Length'      => "32"
                       ),
          2116 => array('Attribute'   => "Copyright Notice",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_COPYRIGHTNOTICE'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_COPYRIGHTNOTICE_DEFINITION'),
                        'Group'       => "Credit",
                        'IMM'         => "2:116",
                        'Format'      => "Characters",
                        'Length'      => "128"
                       ),
          2118 => array('Attribute'   => "Contact",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_CONTACT'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_CONTACT_DEFINITION'),
                        'Group'       => "Credit",
                        'IMM'         => "2:118",
                        'Format'      => "Characters",
                        'Length'      => "128"
                       ),
          2120 => array('Attribute'   => "Caption/Abstract",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_DESCRIPTION'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_DESCRIPTION_DEFINITION'),
                        'Group'       => "Caption",
                        'IMM'         => "2:120",
                        'Format'      => "Characters",
                        'Length'      => "2000"
                       ),
          2122 => array('Attribute'   => "Writer/Editor",
                        'Name'        => JText::_('COM_JOOMGALLERY_IPTC_DESCRIPTIONWRITER'),
                        'Description' => JText::_('COM_JOOMGALLERY_IPTC_DESCRIPTIONWRITER_DEFINITION'),
                        'Group'       => "Caption",
                        'IMM'         => "2:122",
                        'Format'      => "Characters",
                        'Length'      => "128"
                       ),
      ),
    );