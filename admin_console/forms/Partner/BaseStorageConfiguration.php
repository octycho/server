<?php
/**
 * @package Admin
 * @subpackage Partners
 */
class Form_Partner_BaseStorageConfiguration extends Infra_Form
{
	public function init()
	{
		// Set the method for the display form to POST
		$this->setMethod('post');
		$this->setAttrib('id', 'frmStorageConfig');

		$this->addElement('text', 'partnerId', array(
			'label' 		=> 'Related Publisher ID*:',
			'required'		=> true,
			'filters' 		=> array('StringTrim'),
			'validators' 	=> array(),
		));
		
		$this->addElement('text', 'name', array(
			'label' 		=> 'Remote Storage Name*:',
			'required'		=> true,
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('text', 'systemName', array(
			'label' 		=> 'System Name:',
			'filters'		=> array('StringTrim'),
		));
		
		 
		$deliveryStatus = new Kaltura_Form_Element_EnumSelect('deliveryStatus', array('enum' => 'Kaltura_Client_Enum_StorageProfileDeliveryStatus'));
		$deliveryStatus->setLabel('Delivery Status:');
		$this->addElements(array($deliveryStatus));	
		
		$this->addElement('text', 'deliveryPriority', array(
			'label' 		=> 'Delivery Priority:',
			'required'		=> false,
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('textarea', 'description', array(
			'label'			=> 'Description:',
			'cols'			=> 60,
			'rows'			=> 3,
			'filters'		=> array('StringTrim'),
		));
		 
		 
		$this->addElement('text', 'storageUrl', array(
			'label'			=> 'Storage URL*:',
			'required'		=> true,
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('checkbox', 'allowAutoDelete', array(
			'label'			=> 'Allow auto-deletion of files:',
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('text', 'minFileSize', array(
			'label'			=> 'Export only files bigger than:',
			'filters'		=> array('Digits'),
			
		));
		 
		$this->addElement('text', 'maxFileSize', array(
			'label'			=> 'Export only files smaller than:',
			'filters'		=> array('Digits'),
		));
		
		$this->addElement('select', 'urlManagerClass', array(
			'label'			=> 'Delivery URL format :',
			'filters'		=> array('StringTrim'),
			'multiOptions'  => array('' => 'Kaltura Delivery URL Format',
									'kLocalPathUrlManager' => 'Local FMS Server',
									'kLimeLightUrlManager' => 'Lime Light CDN',
									'kAkamaiUrlManager' => 'Akamai CDN',
									'kLevel3UrlManager' => 'Level 3 CDN',
									'kMirrorImageUrlManager' => 'Mirror Image CDN',
									'kFmsUrlManager' => 'FMS Server',
									),		
			
		));
		$this->getElement('urlManagerClass')->setRegisterInArrayValidator(false);
		 
		
		$this->addElement('select', 'trigger', array(
			'label'			=> 'Trigger:',
			'filters'		=> array('StringTrim'),
			'multiOptions'  => array(3 => 'Flavor Ready',
									2 => 'Moderation Approved',
							  ),	
		));
		
		$readyBehavior = new Kaltura_Form_Element_EnumSelect('readyBehavior', array('enum' => 'Kaltura_Client_Enum_StorageProfileReadyBehavior'));
		$readyBehavior->setLabel('Ready Behavior:');
		$this->addElements(array($readyBehavior));
		
		$this->addElement('textarea', 'urlManagerParamsJson', array(
			'label'			=> 'URL Manager Params (JSON):',
			'cols'			=> 48,
			'rows'			=> 2,
			'filters'		=> array('StringTrim'),
		));
				
		$this->addDisplayGroup(array('partnerId', 'name', 'systemName', 'deliveryStatus', 'deliveryPriority', 'description'), 'general_info', array(
			'legend' => 'General',
		));
		
		$this->addElement('hidden', 'crossLine1', array(
			'lable'			=> 'line',
			'decorators' => array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'hr', 'class' => 'crossLine')))
		));
		
		
		$this->addDisplayGroup(array('storageUrl', 'allowAutoDelete'), 'storage_info', array(
			'legend' => 'Export Details',

		));
		
		$this->addElement('hidden', 'crossLine2', array(
			'lable'			=> 'line',
			'decorators' => array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'hr', 'class' => 'crossLine')))
		));
		
		$this->addDisplayGroup(array('minFileSize', 'maxFileSize', 'trigger', 'readyBehavior'), 'export_policy', array(
			'legend' => 'Export Policy',

		));
				
		$this->addElement('hidden', 'crossLine3', array(
			'lable'			=> 'line',
			'decorators' => array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'hr', 'class' => 'crossLine')))
		));
		
		$this->addDisplayGroup(array('urlManagerClass' ), 'playback_info', array(
			'legend' => 'Delivery Details',

		));
		
		$this->addElement('hidden', 'crossLine4', array(
			'lable'			=> 'line',
			'decorators' => array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'hr', 'class' => 'crossLine')))
		));
		
		$this->addDisplayGroup(array('urlManagerParamsJson'), 'advanced', array(
			'legend' => 'Advanced',

		));
		
		$displayGroups = $this->getDisplayGroups();
		foreach ($displayGroups as $displayGroup)
		{
			$displayGroup->removeDecorator ('label');
	  		$displayGroup->removeDecorator('DtDdWrapper');
		}
		
		$openLeftDisplayGroup = $this->getDisplayGroup('general_info');
    	$openLeftDisplayGroup->setDecorators(array(
             'FormElements',
             'Fieldset',
             array('HtmlTag',array('tag'=>'div','openOnly'=>true,'class'=> 'storageConfigureFormPanel'))
    	 ));
	
    	$closeLeftDisplayGroup = $this->getDisplayGroup('advanced');
    	$closeLeftDisplayGroup->setDecorators(array(
             'FormElements',
             'Fieldset',
              array('HtmlTag',array('tag'=>'div','closeOnly'=>true))
     	));
	}

	public function addFlavorParamsFields(Kaltura_Client_Type_FlavorParamsListResponse $flavorParams, array $selectedFlavorParams = array())
	{
		$flavorParamsElementNames = array();
		foreach($flavorParams->objects as $index => $flavorParamsItem)
		{
			$checked = in_array($flavorParamsItem->id, $selectedFlavorParams);
			$this->addElement('checkbox', 'flavorParamsId_' . $flavorParamsItem->id, array(
				'label'			=> "Flavor Params {$flavorParamsItem->name} ({$flavorParamsItem->id})",
				'checked'		=> $checked,
			    'indicator'		=> 'dynamic',
				'decorators' => array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'dt')))
			));
			
			$this->addElementToDisplayGroup('advanced', 'flavorParamsId_' . $flavorParamsItem->id);
		}

	}
}
