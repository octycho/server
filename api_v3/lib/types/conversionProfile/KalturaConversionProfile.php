<?php
/**
 * @package api
 * @subpackage objects
 */
class KalturaConversionProfile extends KalturaObject implements IFilterable 
{
	/**
	 * The id of the Conversion Profile
	 * 
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $id;
	
	/**
	 * @var int
	 * @readonly
	 */
	public $partnerId;
	
	/**
	 * @var KalturaConversionProfileStatus
	 * @filter eq,in
	 */
	public $status;
	
	/**
	 * The name of the Conversion Profile
	 * 
	 * @var string
	 * @filter eq
	 */
	public $name;
	
	/**
	 * System name of the Conversion Profile
	 * 
	 * @var string
	 * @filter eq,in
	 */
	public $systemName;
	
	/**
	 * Comma separated tags
	 * 
	 * @var string
	 * @filter mlikeor,mlikeand
	 */
	public $tags;
	
	/**
	 * The description of the Conversion Profile
	 * 
	 * @var string
	 */
	public $description;
	
	/**
	 * ID of the default entry to be used for template data
	 * 
	 * @var string
	 * @filter eq,in
	 */
	public $defaultEntryId;
	
	/**
	 * Creation date as Unix timestamp (In seconds) 
	 * 
	 * @var int
	 * @readonly
	 * @filter order
	 */
	public $createdAt;
	
	/**
	 * List of included flavor ids (comma separated)
	 * 
	 * @var string
	 */
	public $flavorParamsIds;
	
	/**
	 * Indicates that this conversion profile is system default
	 *  
	 * @var KalturaNullableBoolean
	 */
	public $isDefault;
	
	/**
	 * Indicates that this conversion profile is partner default
	 * 
	 * @var bool
	 * @readonly
	 */
	public $isPartnerDefault;
	
	/**
	 * Cropping dimensions
	 * 
	 * @var KalturaCropDimensions
	 * @deprecated
	 */
	public $cropDimensions;
	
	/**
	 * Clipping start position (in miliseconds)
	 * 
	 * @var int
	 * @deprecated
	 */
	public $clipStart;
	
	/**
	 * Clipping duration (in miliseconds)
	 * 
	 * @var int
	 * @deprecated
	 */
	public $clipDuration;
	
	/**
	 * XSL to transform ingestion MRSS XML
	 * 
	 * @var string
	 */
	public $xslTransformation;
	
	/**
	 * ID of default storage profile to be used for linked net-storage file syncs
	 * 
	 * @var int
	 */
	public $storageProfileId;

	/**
	 * Media parser type to be used for extract media
	 *  
	 * @var KalturaMediaParserType
	 */
	public $mediaParserType;
	
	private static $map_between_objects = array
	(
		"id",
		"partnerId",
		"status",
		"name",
		"systemName",
		"tags",
		"description",
		"defaultEntryId",
		"createdAt",
		"isDefault",
		"clipStart",
		"clipDuration",
		"storageProfileId",
		"mediaParserType",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function fromObject($sourceObject)
	{
		parent::fromObject($sourceObject);
		
		$this->xslTransformation = $sourceObject->getXsl();
		
		$this->cropDimensions = new KalturaCropDimensions();
		$this->cropDimensions->fromObject($sourceObject);
		
		$this->isPartnerDefault = false;
		if($this->partnerId)
		{
			$partner = PartnerPeer::retrieveByPK($this->partnerId);
			if($partner && $this->id == $partner->getDefaultConversionProfileId())
				$this->isPartnerDefault = true;
		}
	}
	
	public function toObject($objectToFill = null , $propsToSkip = array())
	{
		$ret = parent::toObject($objectToFill, $propsToSkip);
		
		if ($this->cropDimensions !== null)
		{
			$this->cropDimensions->toObject($ret);
		}
			
		return $ret;
	}
	
	public function toUpdatableObject($objectToFill , $propsToSkip = array())
	{
		$ret = parent::toUpdatableObject($objectToFill, $propsToSkip);
		
		if ($this->cropDimensions !== null)
		{
			$this->cropDimensions->toUpdatableObject($ret);
		}
		
		return $ret;
	}
	
	/* (non-PHPdoc)
	 * @see KalturaObject::validateForUpdate()
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		$this->validatePropertyMinLength("name", 1, true);
	
		if($this->systemName)
		{
			$c = KalturaCriteria::create(conversionProfile2Peer::OM_CLASS);
			$c->add(conversionProfile2Peer::ID, $sourceObject->getId(), Criteria::NOT_EQUAL);
			$c->add(conversionProfile2Peer::SYSTEM_NAME, $this->systemName);
			if(conversionProfile2Peer::doCount($c))
				throw new KalturaAPIException(KalturaErrors::SYSTEM_NAME_ALREADY_EXISTS, $this->systemName);
		}
		
		if (!is_null($this->flavorParamsIds) && !($this->flavorParamsIds instanceof KalturaNullField)) 
			$this->validateFlavorParamsIds();
		
		$this->validateDefaultEntry();
		
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see KalturaObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyMinLength("name", 1);
		$this->validateFlavorParamsIds();
		$this->validateDefaultEntry();
		
		if($this->systemName)
		{
			$c = KalturaCriteria::create(conversionProfile2Peer::OM_CLASS);
			$c->add(conversionProfile2Peer::SYSTEM_NAME, $this->systemName);
			if(conversionProfile2Peer::doCount($c))
				throw new KalturaAPIException(KalturaErrors::SYSTEM_NAME_ALREADY_EXISTS, $this->systemName);
		}
		
		return parent::validateForInsert($propertiesToSkip);
	}
	
	public function validateDefaultEntry()
	{
		if(is_null($this->defaultEntryId) || $this->defaultEntryId instanceof KalturaNullField)
			return;
			
		$entry = entryPeer::retrieveByPK($this->defaultEntryId);
		if(!$entry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $this->defaultEntryId);
	}
	
	public function validateFlavorParamsIds()
	{
		$flavorParamsIds = $this->getFlavorParamsAsArray();
		$flavorParams = assetParamsPeer::retrieveByPKs($flavorParamsIds);
		
		$sourceFound = false;
		$indexedFlavorParams = array();
		foreach($flavorParams as $flavorParamsItem)
		{
			if($flavorParamsItem->hasTag(flavorParams::TAG_SOURCE))
			{
				if($sourceFound)
					throw new KalturaAPIException(KalturaErrors::FLAVOR_PARAMS_SOURCE_DUPLICATE);
					
				$sourceFound = true;
			}
			$indexedFlavorParams[$flavorParamsItem->getId()] = $flavorParamsItem;
		}
			
		$foundFlavorParams = array();
		foreach($flavorParamsIds as $id)
		{
			if(!isset($indexedFlavorParams[$id]))
				throw new KalturaAPIException(KalturaErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
				
			if(in_array($id, $foundFlavorParams))
				throw new KalturaAPIException(KalturaErrors::FLAVOR_PARAMS_DUPLICATE, $id);
				
			$foundFlavorParams[] = $id;
		}
	}
	
	/**
	 * @return array
	 */
	public function getFlavorParamsAsArray()
	{
		return explode(",", $this->flavorParamsIds);
	}
	
	/**
	 * @param conversionProfile2 $conversionProfile
	 * @param PropelPDO $con
	 * Sets flavorParamsIds attribute based on flavorParamsConversionProfiles table
	 */
	public function loadFlavorParamsIds(conversionProfile2 $conversionProfile, $con = null)
	{
		$flavorParams = $conversionProfile->getflavorParamsConversionProfilesJoinflavorParams(null, $con);
		$flavorParamIds = array();
		foreach($flavorParams as $flavorParam)
		{
			$flavorParamIds[] = $flavorParam->getFlavorParamsId();
		}
		$this->flavorParamsIds = implode(",", $flavorParamIds);
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getExtraFilters()
	 */
	public function getExtraFilters()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getFilterDocs()
	 */
	public function getFilterDocs()
	{
		return array();
	}
}