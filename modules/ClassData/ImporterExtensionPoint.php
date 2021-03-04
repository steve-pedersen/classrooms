<?php

/**
 * Extensions provide data importers.
 *
 * @package at:classroom:classdata
 * @author  Charles O'Sullivan (chsoney@sfsu.edu)
 **/
class Dsp_Profiles_ImporterExtensionPoint extends Bss_Core_ExtensionPoint
{
	public function getUnqualifiedName () { return 'importer'; }
	public function getDescription () { return 'Extensions provide profile importers.'; }
	public function getRequiredInterfaces () { return array('Classrooms_ClassData_Importer'); }
}