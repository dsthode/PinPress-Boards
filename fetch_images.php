<?php

/*
Copyright (C) 2012  Damian Serrano Thode

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
