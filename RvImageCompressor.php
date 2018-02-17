<?php
/**
 * RV is a service dealing with the image resources of ReserveDine
 * Author : Nightraiser
 * Date : 20 Jun 2017
 */
class RV
{
   
   // Threshold size is the max size , compression is done only if the file size exceeds the threshold
   protected $_thresholdSize = 250; // Threshold size is given in KBs;
   protected $_thresholdCompressionRatio = 0.25; // Threshold compression ratio;

   /**
    * [rvCompressor description]
    * @param  [type] $source      [source link of the file to be compressed]
    * @param  [type] $destination [destination of the file to be written]
    * @param  [type] $quality     [quality of the image output, default to 100]
    * @return [string]              [detination of the new file]
    */
   public function rvCompressor($source,$destination,$quality = 100,$customThresholdSize = null)
   {
   	try {
   		$fileSizeBytes = filesize($source);
		$fileSizeKb = $fileSizeBytes * 0.001; // CALCULATING THE FILE SIZE IN KB;
		if($customThresholdSize != null)
		{
			$this->_thresholdSize = $customThresholdSize;
		}	
		if($fileSizeKb > $this->_thresholdSize)
		{
			// COMPRESSION STARTS
			$imageStats = getimagesize($source);
			$orginalWidth = $imageStats[0];
			$originalHeight = $imageStats[1];

			// compression ratio is the ratio of the given threshold file size to the  file size given both in KBs
			$compressionRatio = $this->_thresholdSize / $fileSizeKb; 
			$compressionRatio = max($compressionRatio,$this->_thresholdCompressionRatio);
			$newWidth = ceil($orginalWidth * $compressionRatio);
			$newHeight = ceil($originalHeight * $compressionRatio);

			$mime = $imageStats['mime'];
			$finalCreationFunction = null;
			$newSource = null;
			switch ($mime) {
				case  'image/jpeg':
					$newSource = imagecreatefromjpeg($source);
					$finalCreationFunction = 'imagejpeg';
				break;
				
				// case 'image/gif':
				// 	$newSource = imagecreatefromgif($source);
				// 	$finalCreationFunction = 'imagegif';
				// break;
				case  'image/png':
					$newSource = imagecreatefrompng($source);
					$finalCreationFunction = 'imagecreatefrompng';
				break;
			}

			if($newSource != null)
			{
				$newDestinationImage = imagecreatetruecolor($newWidth,$newHeight); // CREATES A NEW IMAGE WITH REQUIRED SPECS
				// new image file generated and resampled with original image content
				imagecopyresampled($newDestinationImage,$newSource,0,0,0,0,$newWidth,$newHeight,$orginalWidth,$originalHeight);
				// SAVE THE FILE TO THE GIVEN DESTINATION
				$finalCreationFunction($newDestinationImage,$destination,$quality);
			}

		}
		else {
			move_uploaded_file($source, $destination);
		}
   	} catch (Exception $ex) {
   		Rdine_Logger_FileLogger::info($ex->getMessage());
			throw new Exception($ex->getMessage());
   	}
   }

}
