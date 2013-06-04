<?php

/*
 * The main program library for the php-browser project.  php-browser is designed to be a a lightweight and simple
 * to implement PHP based file browser, a repacement for the default Apache/Nginx/Etc file browsing pages.
 *
 * Copyright 2013 - Andrew Kester.  Distributed under GNU-GPL
 *
 * This file is part of thunar-web.
 *
 * This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * A simple class to browse files via PHP.
 */
class phpBrowser {

	/**
	 * The location of the configuration file.
	 * @var string
	 */
	private $cfgFile = 'include/tw-config.ini';
	private $version = 0.1;
	private $ignored = array();
	private $mimeTypes = array();
	private $config = array();
	private $static = array();
	private $start = -1;

	/**
	 * A simple class to browse files via PHP.
	 */
	function __construct($time = False) {
		/* Start the clock */
		if ($time)
			$this -> start = microtime();

		/* Read the config file */
		if (!file_exists($this -> cfgFile))
			die('Could not locate cfg file.');
		$this -> config = parse_ini_file($this -> cfgFile);

		/* Reads the ignore file */
		$this -> ignored = $this -> parseIgnoreFile();

		/* Read the mime-types file */
		$this -> mimeTypes = $this -> parseMimeFile();

		/* Read the static links file */
		$this -> static = $this -> parseStaticFile();
	}

	/**
	 * Parses the .ignore file
	 * @return array The ignored files
	 */
	private function parseIgnoreFile() {
		if (file_exists('.ignore'))
			$data = parse_ini_file('.ignore');
		if (!empty($data))
			return $data['ignore'];
		else
			return array();
	}

	/**
	 * Parses the configured mime-types file
	 * @return array The index of mime-types and thier icons
	 */
	private function parseMimeFile() {
		if (file_exists($this -> config['mimeFile']))
			$data = parse_ini_file($this -> config['mimeFile']);
		if (!empty($data))
			return $data;
		else
			return array();
	}

	/**
	 * Parses the configured static-links file
	 * @return array The array of static links
	 */
	private function parseStaticFile() {
		if (file_exists($this -> config['staticFile']))
			$data = parse_ini_file($this -> config['staticFile'], True);
		if (!empty($data))
			return $data;
		else
			return array();
	}

	/**
	 * Tests if a file is to be ignored.
	 * @param string $file The file to test
	 * @return boolean Is the file to be ignored
	 */
	private function isIgnored($file) {
		if (!isset($this -> ignored))
			/* Weird... */
			$this -> ignored = $this -> parseIgnoreFile();
		return in_array($file, $this -> ignored);
	}

	/**
	 * Converts a file size in bytes to a more human readable format
	 * @param int $size The file size (in Bytes)
	 * @return string The human readable equivalant
	 */
	private function humanSize($size) {
		$kb = 1024;
		$mb = $kb * 1024;
		$gb = $mb * 1024;
		$tb = $gb * 1024;

		if ($size >= 0 && $size < $kb)
			return $size . ' B';
		elseif ($size >= $kb && $size < $mb)
			return round($size / $kb, 2) . ' KB';
		elseif ($size >= $mb && $size < $gb)
			return round($size / $mb, 2) . ' MB';
		elseif ($size >= $mb && $size < $gb)
			return round($size / $gb, 2) . ' GB';
		elseif ($size >= $gb && $size < $tb)
			return round($size / $gb, 2) . ' GB';
		elseif ($size >= $tb)
			return round($size / $tb, 2) . ' TB';
		else
			return $size;
	}

	/**
	 * Sorts an array of files based on a specific key.
	 * @param array $array The array to sort
	 * @param string $skey The key to sort by
	 * @param boolean $asc Ascending or Decending
	 * @return array The sorted array.
	 */
	private function applySort($array, $skey, $asc) {
		$sort = array();
		$out = array();

		/* Break apart the array */
		foreach ($array as $k => $v) {
			if (!array_key_exists($skey, $v))
				continue;
			/* Seperate directories so they can be placed at the top */
			if ($v['type'] == 'dir')
				$dsort[$k] = strtolower($v[$skey]);
			else
				$fsort[$k] = strtolower($v[$skey]);
		}

		/* Sort the array */
		if ($asc == 1) {
			asort($dsort);
			asort($fsort);
		} else {
			arsort($dsort);
			arsort($fsort);
		}

		/* Rebuild the array */
		foreach ($dsort as $k => $v) {
			if (!array_key_exists($k, $array))
				continue;
			$out[$k] = $array[$k];
		}
		foreach ($fsort as $k => $v) {
			if (!array_key_exists($k, $array))
				continue;
			$out[$k] = $array[$k];
		}

		return $out;
	}

	/**
	 * Gets the contents of a directory along with some metadata
	 * @param string $path The path to read
	 * @return array The contents of the directory
	 */
	public function getDirectoryContents($path, $skey, $asc) {
		/* We don't use scandir to keep PHP-4 compatability */
		$path_dir = opendir($path);
		while ($path_ent = readdir($path_dir)) {
			/* Check if the file is ignored */
			if ($this -> isIgnored($path_ent))
				continue;
			$files[] = $path_ent;
		}
		closedir($path_dir);

		/* Loop through the files, get the type and size */
		$x = 0;
		if ($this -> config['displayIcons'])
			$fin = finfo_open();
		foreach ($files as $f) {
			if (strpos($f, '.') === 0)
				continue;
			if ($f == 'index.php')
				continue;
			$fn = $path . '/' . $f;
			#$data[$f] = [];

			if ($this -> config['displaySize']) {
				$data[$f]['size'] = (filesize($fn));
				$data[$f]['size-human'] = $this -> humanSize($data[$f]['size']);
			}
			$data[$f]['type'] = filetype($fn);
			$data[$f]['name'] = $f;

			if ($this -> config['displayIcons']) {
				/* Determine the file-type and assign an icon */
				$mime = finfo_file($fin, $fn, FILEINFO_MIME);
				$mime_parts = explode(';', $mime);
				$data[$f]['mime'] = $mime_parts[0];

				if (array_key_exists($data[$f]['mime'], $this -> mimeTypes))
					/* We have a specific mapping */
					$data[$f]['ico'] = $this -> config['icoPrefix'] . $this -> mimeTypes[$data[$f]['mime']];
				elseif (array_key_exists('default', $this -> mimeTypes))
					/* We aren't specific but have a default */
					$data[$f]['ico'] = $this -> config['icoPrefix'] . $this -> mimeTypes['default'];
				else
					/* Found nothing */
					$data[$f]['ico'] = '';
			}

			$x++;
		}
		if ($this -> config['displayIcons'])
			finfo_close($fin);

		/* Apply the sort */
		$data = $this -> applySort($data, $skey, $asc);

		/* Prepend and static elements */
		foreach ($this->static as $s) {
			$static[$s['location']] = array('name' => $s['display'], 'ico' => $this -> config['icoPrefix'] . $s['ico']);
		}
		$data = array_merge($static, $data);

		$data['count'] = $x;

		return $data;
	}

	/**
	 * Extracts the valid page name for the path.
	 * @param string $path The URI to parse
	 * @return string The page title.
	 */
	public function getPageTitle($path) {
		$url = parse_url($path);
		$bn = basename($url['path']);
		return 'Index of ' . $bn . ' at ' . $_SERVER['SERVER_NAME'];
	}

	/**
	 * Returns a page footer with version and loadtime.
	 * @return string The page footer
	 */
	public function getPageFooter() {
		if ($this -> start >= 0) {
			$loadtime = round((microtime() - $this -> start), 4);
			return 'PHP Browser version ' . $this -> version . '; Page loaded in ' . $loadtime . ' seconds.';
		} else {
			return 'PHP Browser version ' . $this -> version;
		}
	}

	/**
	 * Returns the formatted Readme for the section.
	 * @return string The HTML Readme code to print.
	 */
	public function printReadMe() {
		if (file_exists($this -> config['readmeFile']))
			$readme = file_get_contents($this -> config['readmeFile']);
		else
			return '';

		if (!$this -> config['readmeHTML']) {
			return $this -> config['readmeHeader'] . '<code>' . nl2br($readme) . '</code>';
		} else {
			return $this -> config['readmeHeader'] . readme;
		}
	}

	/**
	 * Prints a table of data for the directory
	 * @param string $path The path to print
	 */
	public function printTable($path, $skey = 'name', $asc = 1) {
		if (empty($skey))
			$skey = 'name';

		$dir = $this -> getDirectoryContents($path, $skey, $asc);

		if ($asc)
			$nasc = 0;
		else
			$nasc = 1;

        /* Print the readme at the top, if configured. */
		if ($this -> config['printReadme'] && $this -> config['readmePos'] == 'top')
			echo $this -> printReadMe();

		/* Echo the table header */
		echo '<table id="pbr-table">';
		echo '<tr>';
		if ($this -> config['displayIcons'])
			echo '<th id="pbr-ico"></th>';
		echo '<th id="pbr-name"><a href=".?sort=name&asc=' . $nasc . '">Name</a></th>';
		if ($this -> config['displaySize'])
			echo '<th id="pbr-size"><a href=".?sort=size&asc=' . $nasc . '">Size</a></th>';
		if ($this -> config['displayType'])
			echo '<th id="pbr-type"><a href=".?sort=mime&asc=' . $nasc . '">Type</a></th>';

		echo '</tr>';

		$class = 'even';

		/* Echo each file in the table */
		foreach ($dir as $n => $d) {
			if ($n == 'count')
				continue;

			if ($class == 'pbr-odd')
				$class = 'pbr-even';
			else
				$class = 'pbr-odd';

			echo '<tr class=' . $class . '>';
			if ($this -> config['displayIcons'])
				echo '<td id="pbr-icon"><img src="', $d['ico'], '" alt="' . $d['mime'] . '" /></td>';
			echo '<td id="pbr-name"><a href="', $n, '">', $d['name'], '</a></td>';
			if ($this -> config['displaySize'])
				echo '<td id="pbr-size">', $d['size-human'], '</td>';
			if ($this -> config['displayType'])
				echo '<td id="pbr-type">', $d['mime'], '</td>';

			echo '</tr>';
		}
		echo '</table>';
		echo '<p class="pbr-count">', $dir['count'], ' Files/Directories</p>';

		/* Print the readme at the bottom, if configured*/
		if ($this -> config['printReadme'] && $this -> config['readmePos'] == 'bottom')
			echo $this -> printReadMe();
	}

}
?>
