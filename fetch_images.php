<?php

/*
Copyright 2021 Damian Serrano Thode

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

$source_url = $_GET['source_url'];

$results = array();

if (strlen($source_url) > 0) {
	$content = file_get_contents($source_url);
	if (strlen($content) > 0) {
		$dom = new DOMDocument;
		$dom->loadHTML($content);
		$base = '';
		if ($dom->getElementsByTagName('base')->length > 0) {
			$base = $dom->getElementsByTagName('base')->item(0)->getAttribute('href');
		}
		if ($base == FALSE || strlen($base) == 0) {
			$base = dirname($source_url);
			if ((strcasecmp($base, 'http:') == 0) || (strcasecmp($base, 'https:') == 0)) {
				$base = $source_url .'/';
			} else {
				$base = $base . '/';
			}
		}
		$imgs = $dom->getElementsByTagName('img');
		for ($i=0;$i<$imgs->length;$i++) {
			$src = $imgs->item($i)->getAttribute('src');
			if ((strncasecmp($src, 'http://', 7) == 0) || (strncasecmp($src, 'https://', 8) == 0)) {
				array_push($results, $src);
			} else {
				array_push($results, $base . $src);
			}
		}
	}
}

header('Content-Type: application/json');
echo json_encode($results);

?>
