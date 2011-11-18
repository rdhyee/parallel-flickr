<?php

	#################################################################

	loadlib("flickr_photos");
	
	#################################################################

	function flickr_photos_exif_has_exif(&$photo){

		# THIS IS NOT IDEAL. Maybe store a hasexif flag against
		# the database on import? (20111118/straup) Anyway, for
		# now it's just for photo owners. Or at least caching or
		# something...
 
		$rsp = flickr_photos_exif_read($photo);
		return $rsp['ok'];
	}

	#################################################################

	function flickr_photos_exif_read(&$photo){

		$map = flickr_photos_media_map();

		if ($map[$photo['media']] == 'video'){
			return not_ok("video does not contain EXIF data");
		}

		$fname = "{$photo['id']}_{$photo['originalsecret']}_o.{$photo['originalformat']}";
		$froot = $GLOBALS['cfg']['flickr_static_path'] . flickr_photos_id_to_path($photo['id']);

		$path = "{$froot}/{$fname}";

		if (! preg_match("/\.jpe?g$/i", $path)){
			return not_ok("not a JPEG photo");
		}

		if (! file_exists($path)){
			return not_ok("original photo not found");
		}

		# TO DO: cache me?

		$exif = exif_read_data($path);

		if (! $exif){
			return not_ok("failed to read EXIF data");
		}

		# TO DO: expand EXIF tag values

		$to_simplejoin = array(
			'SubjectLocation',
			'GPSLatitude',
			'GPSLongitude',
			'GPSTimeStamp',
		);

		foreach ($to_simplejoin as $tag){

			if (isset($exif[$tag])){
				$exif[$tag] = implode(",", $exif[$tag]);
			}
		}

		return ok(array("rows" => $exif));
	}

	#################################################################

?>