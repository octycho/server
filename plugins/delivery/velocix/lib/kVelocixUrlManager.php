<?php
/**
 * @package plugins.velocix
 * @subpackage storage
 */
class kVelocixUrlManager extends kUrlManager
{

	/**
	 * @return kUrlTokenizer
	 */
	public function getTokenizer()
	{
		$liveEntry = entryPeer::retrieveByPK($this->entryId);
		//if stream name doesn't start with 'auth' than the url stream is not tokenized
		if (substr($liveEntry->getStreamName(), 0, 4) == 'auth'){
			$secret = $this->params['shared_secret'];
			$window = $this->params['access_window_seconds'];
			$hdsPaths = array();
			$hdsPaths[] = $this->params['hdsBitratesManifestPath'];
			$hdsPaths[] = $this->params['hdsSegmentsPath'];
			$tokenParamName = $this->params['tokenParamName'];
			$protocol = $this->protocol;
			return new kVelocixUrlTokenizer($window, $secret, $protocol, $liveEntry->getStreamName(), $hdsPaths, $tokenParamName);
		}
		
		return null;
	}
}
