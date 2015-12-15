<?php

/* php-tar version 0.1 */

/* php-tar is a single PHP class that abstracts a tar archive (gzipped
   tar archives are supported as well, but only as far as PHP's
   Zlib functions). Documentation is scattered throughout the source
   file. Scoot around and have a good time.

   BASIC API:

   $tar = new Tar(); // constructor takes no arguments

   $tar->load($filename[, $format]); // load a file
      $format defaults to ".gz" but can be either ".gz" or "" to
      indicate the type of compression on the file. Future versions will
      auto-detect this.

   $tar->save($filename[, $format]); // save a file
      Same as load, except in the other direction. Note that php-tar
      is not fully standards-compliant and might cause data loss when
      loading and saving a tar archive created by another tar program.

   $tar->to_s(); // export to string
      Returns an uncompressed string representing the contents of a
      tar archive. This can be compressed with any of PHP's compression
      functions. This is useful for generating tar archives from webpages
      and sending them for download without using the filesystem or
      requiring external programs.

   $tar->add_file($name, $mode, $data); // add a file
      Adds a file to the archive. $name should be a full path name with
      slashes and everything. Parent directories will be created if they
      need to be.

   $tar->contents([$dirname]); // iterate subtree
      Returns an array of name => entry pairs for a subtree. If $dirname
      is omitted, it defaults to the entire archive. If $dirname does not
      exist or refers to something other than a directory this function
      will simply return an empty array so code doesn't break.

   */

define('TAR_HDR_UNPACK_FORMAT',
	'a100name/' . /* 'name' => file name */
	'a8mode/' . /* 'mode' => file mode */
	'a8uid/' . /* 'uid' => numeric uid */
	'a8gid/' . /* 'gid' => numeric gid */
	'a12size/' . /* 'size' => size in bytes */
	'a12mtime/' . /* 'mtime' => modification time */
	'a8checksum/' . /* 'checksum' => checksum */
	'a1type/' . /* 'type' => type indicator */
	'a100link/' . /* 'link' => name of linked file */
	'a6ustar/' . /* 'ustar' => UStar indicator */
	'a2uver/' . /* 'uver' => UStar version */
	'a32owner/' . /* 'owner' => owner name */
	'a32group/' . /* 'group' => group name */
	'a8major/' . /* 'major' => device major */
	'a8minor/' . /* 'minor' => device minor */
	'a155nameprefix/' /* 'nameprefix' => file name prefix */
	);
define('TAR_HDR_PACK_FORMAT',
	'a100' . 'a8' . 'a8' . 'a8' . /* name, mode, uid, gid */
	'a12' . 'a12' . 'a8' . 'a1' . /* size, mtime, checksum, type */
	'a100' . 'a6' . 'a2' . 'a32' . /* link, ustar, uver, owner */
	'a32' . 'a8' . 'a8' . 'a155' /* group, major, minor, nameprefix */
	);

class TarIOPlain {

	public function __construct($filename) { $this->filename = $filename; $this->f = NULL; }

	/* low-level */

	function open($f, $m) { return fopen($f, $m); }
	function close($f) { fclose($f); }
	function read($f, $n) { return fread($f, $n); }
	function write($f, $s) { fwrite($f, $s); }

	/* high-level */

	public function start_load() {
		$this->f = $this->open($this->filename, "rb");
		return $this->f !== NULL;
	}

	public function start_save() {
		$this->f = $this->open($this->filename, "wb");
		return $this->f !== NULL;
	}

	public function end() {
		$this->close($this->f);
	}

	public function record_load() {
		return $this->read($this->f, 512);
	}

	public function record_save($r) {
		$len = strlen($r);
		$n = 0x200;

		if ($len > 0)
			$n = (($len - 1) & ~0x1ff) + 0x200;

		$this->write($this->f, str_pad($r, $n, "\0"));
	}
}

class TarIOGzip extends TarIOPlain {

	function open($f, $m) { return gzopen($f, $m); }
	function close($f) { return gzclose($f); }
	function read($f, $n) { return gzread($f, $n); }
	function write($f, $s) { return gzwrite($f, $s); }

}

class TarIOString extends TarIOPlain {

	function open($f, $m) {
		if ($m != 'wb') {
			die('TarIOString can be used for save only');
			return;
		}

		$this->s = '';

		return TRUE;
	}

	function close($f) { }

	function read($f, $n) { return ''; }

	function write($f, $s) {
		$this->s .= $s;
	}

	public function report() {
		return "".$this->s;
	}

}


class TarDirent {

	public function __construct() {
		$this->dirent = array();
	}

	public function get($name) {
		if (isset($this->dirent[$name]))
			return $this->dirent[$name];
		return FALSE;
	}

	public function add($name, $ent) {
		if (isset($this->dirent[$name]))
			return FALSE;
		$this->dirent[$name] = $ent;
		return $ent;
	}

	public function mkdirent($name) {
		$d = $this->get($name);
		if ($d) {
			if ($d['type'] == '5')
				return $d['dirent'];
			return FALSE;
		}
		$d = $this->add($name,
			array('mode' => 0755, 'uid' => 0, 'gid' => 0,
				'mtime' => time(), 'type' => '5',
				'link' => '', 'ustar' => 'ustar',
				'uver' => '00', 'owner' => 'root',
				'group' => 'root', 'major' => 0,
				'minor' => 0, 'nameprefix' => '',
				'dirent' => new TarDirent()));
		return $d['dirent'];
	}

	public function mkdirent_p($name) {
		$ex = explode('/', $name, 1);

		$d = $this->mkdirent($ex[0]);
		if (count($ex) > 1)
			return $d->mkdirent_p($ex[1]);
		return $d;
	}

	public function find($name) {
		$ex = explode('/', $name, 1);

		$d = $this->get($ex[0]);
		if ($d === FALSE)
			return FALSE;

		if (count($ex) < 2)
			return $d;
		if ($d['type'] == '5')
			return $d['dirent']->find($ex[1]);
		return FALSE;
	}

	public function contents() {
		return $this->dirent;
	}

}

class Tar {

	public function __construct() {
		$this->tree = new TarDirent();
	}

	public function add_file($name, $mode, $data) {
		$tree = $this->tree->mkdirent_p(dirname($name));
		if ($tree === FALSE)
			return FALSE;
		return $tree->add(basename($name),
			array('mode' => $mode, 'uid' => 0, 'gid' => 0,
				'mtime' => time(), 'type' => '0',
				'link' => '', 'ustar' => 'ustar',
				'uver' => '00', 'owner' => 'root',
				'group' => 'root', 'major' => 0,
				'minor' => 0, 'nameprefix' => '',
				'data' => $data));
	}

	public function dirent($dirname=NULL) {
		$dir = $this->tree;
		if ($dirname != NULL) {
			$dir = $dir->find($dirname);
			if ($dir === FALSE || $dir['type'] != '5')
				return FALSE;
			$dir = $dir['dirent'];
		}
		return $dir;
	}

	public function children($dirname=NULL) {
		$dir = $this->dirent($dirname);
		if ($dir === FALSE)
			return array();
		return $dir->contents();
	}

	public function contents_real(&$c, $pfx, $dirent) {
		/* this is backwards but whatever */
		foreach ($dirent->contents() as $name => $ent) {
			if ($ent['type'] == '5')
				$this->contents_real($c, $pfx . $name . '/', $ent['dirent']);
			$c[$pfx . $name] = $ent;
		}
	}

	public function contents($dirname=NULL) {
		$c = array();
		$pfx = $dirname === NULL ? '' : $dirname . '/';
		$dirent = $this->dirent($dirname);
		$this->contents_real($c, $pfx, $dirent);
		return $c;
	}

	public function compress_normalize($compress) {
		switch ($compress) {
		case '.gz':
			return 'TarIOGzip';
		default:
			return 'TarIOPlain';
		}
	}

	public function load($filename=NULL, $compress='.gz') {
		$compress = $this->compress_normalize($compress);
		$f = new $compress($filename === NULL ? $this->filename : $filename);

		if (!$f->start_load()) {
			trigger_error("Tar::load: Could not open $f->filename for loading");
			return FALSE;
		}

		while (($c = $this->load_one($f)) > 0) {
			/* no-op */
		}

		if ($c < 0) {
			trigger_error("Tar::load: Error when unpacking");
			$f->end();
			return FALSE;
		}

		$f->end();
		return TRUE;
	}

	public function header_sum_check($data, $refsum) {
		$sum_unsigned = 0;
		$sum_signed = 0;

		$refsum = (int)$refsum;

		for ($i=0; $i<0x200; $i++) {
			$c = ord(substr($data, $i, 1));
			if ($i >= 148 && $i < 156)
				$c = 32; /* ASCII space */
			$sum_unsigned += $c;
			$sum_signed += ($c > 127 ? $c - 256 : $c); /* XXX? */
		}

		$ok = (($refsum == $sum_unsigned) || ($refsum == $sum_signed));

		return $ok;
	}

	public function header_sum_calc(&$data) {
		$sum = 0;

		for ($i=0; $i<0x200; $i++) {
			$c = ord(substr($data, $i, 1));
			if ($i >= 148 && $i < 156)
				$c = 32; /* ASCII space */
			$sum += $c;
		}

		$data = substr($data, 0, 148) . sprintf("%06o\0 ", $sum) . substr($data, 156, 356);
	}

	public function header_read($hdr_data) {
		$hdr_info = array('ustar' => '', 'uver' => '00',
				'owner' => '', 'group' => '', 'major' => 0,
				'minor' => 0, 'nameprefix' => '');

		$hdr_data = str_pad($hdr_data, 512, "\0");

		$hdr_unpacked = unpack(TAR_HDR_UNPACK_FORMAT, $hdr_data);

		$hdr_info['checksum'] = octdec(trim($hdr_unpacked['checksum'], "\0 "));
		if (!$this->header_sum_check($hdr_data, $hdr_info['checksum'])) {
			trigger_error("Bad checksum, file maybe named {$hdr_unpacked['name']}");
			return FALSE;
		}

		/* TODO: array_map() n stuff */
		$hdr_info['name'] = trim($hdr_unpacked['name'], "\0");
		$hdr_info['mode'] = octdec($hdr_unpacked['mode']);
		$hdr_info['uid'] = octdec($hdr_unpacked['uid']);
		$hdr_info['gid'] = octdec($hdr_unpacked['gid']);
		$hdr_info['size'] = octdec($hdr_unpacked['size']);
		$hdr_info['mtime'] = octdec($hdr_unpacked['mtime']);
		/*$hdr_info['checksum'] = (we already did this) */
		$hdr_info['type'] = trim($hdr_unpacked['type'], "\0");
		$hdr_info['link'] = trim($hdr_unpacked['link'], "\0");
		$hdr_info['ustar'] = trim($hdr_unpacked['ustar'], "\0");

		if ($hdr_info['ustar'] == 'ustar') {
			$hdr_info['uver'] = trim($hdr_unpacked['uver'], "\0");
			$hdr_info['owner'] = trim($hdr_unpacked['owner'], "\0");
			$hdr_info['group'] = trim($hdr_unpacked['group'], "\0");
			$hdr_info['major'] = octdec($hdr_unpacked['major']);
			$hdr_info['minor'] = octdec($hdr_unpacked['minor']);
			$hdr_info['nameprefix'] = trim($hdr_unpacked['nameprefix'], "\0");
		}

		return $hdr_info;
	}

	public function header_pack($hdr_info) {
		$hdr_data = str_pad(pack(TAR_HDR_PACK_FORMAT,
				$hdr_info['name'],
				sprintf('%07o', $hdr_info['mode']),
				sprintf('%07o', $hdr_info['uid']),
				sprintf('%07o', $hdr_info['gid']),
				sprintf('%011o', $hdr_info['size']),
				sprintf('%011o', $hdr_info['mtime']),
				'        ',
				$hdr_info['type'],
				$hdr_info['link'],
				$hdr_info['ustar'],
				$hdr_info['uver'],
				$hdr_info['owner'],
				$hdr_info['group'],
				sprintf('%08o', $hdr_info['major']),
				sprintf('%08o', $hdr_info['minor']),
				$hdr_info['nameprefix']),
			512, "\0");

		$this->header_sum_calc($hdr_data);

		return $hdr_data;
	}

	public function load_data($f, $size) {
		$s = '';

		while ($size > 0) {
			$rec = $f->record_load();
			if ($size < 512)
				$s .= substr($rec, 0, $size);
			else
				$s .= $rec;
			$size -= 512;
		}

		return $s;
	}

	/* 0 indicates finish, <0 indicates error, >0 indicates continue */
	public function load_one($f) {
		$hdr_data = $f->record_load();
		if (strlen($hdr_data) < 0x200 || $hdr_data == str_repeat("\0\0\0\0\0\0\0\0", 64))
			return 0;

		$file = $this->header_read($hdr_data);
		if ($file === FALSE)
			return -1;

		if ($file['type'] != 0)
			return 1;

		$file['data'] = $this->load_data($f, $file['size']);
		$name = $file['name'];
		unset($file['size']); /* size is implicit in data */
		unset($file['name']); /* name is array index */
		$dir = $this->tree;
		if (dirname($name) != '.')
			$dir = $dir->mkdirent_p(dirname($name));
		if ($dir === FALSE)
			return -1;
		$dir->add(basename($name), $file);

		return 1;
	}

	public function save_data($f) {
		if (!$f->start_save()) {
			trigger_error("Tar::save: Could not open $f->filename for saving");
			return FALSE;
		}
	
		foreach ($this->tree->contents() as $name => $ent) {
			$c = $this->save_one($f, $name, $ent);

			if ($c < 0) {
				trigger_error("Tar::save: Error when saving. $f->filename is probably jacked up");
				$f->end();
				return FALSE;
			}
		}

		$f->record_save('');
		$f->record_save('');
	}

	/* nonzero indicates error */
	public function save_file($f, $name, $file) {
		$file['size'] = strlen($file['data']);
		$file['name'] = $name;
		$hdr_data = $this->header_pack($file);
		if ($hdr_data === FALSE)
			return -1;
		unset($file['size']); /* size is implicit in data */
		unset($file['name']);

		$f->record_save($hdr_data);
		$f->record_save($file['data']);

		return 0;
	}

	public function save_dir($f, $name, $dir) {
		$dir['name'] = $name;
		$dir['size'] = 0;
		$hdr_data = $this->header_pack($dir);
		if ($hdr_data === FALSE)
			return -1;
		unset($dir['size']);
		unset($dir['name']);

		$f->record_save($hdr_data);

		foreach($dir['dirent']->contents() as $fname => $file) {
			$c = $this->save_one($f, $name . '/' . $fname, $file);
			if ($c < 0)
				return $c;
		}

		return 0;
	}

	public function save_one($f, $name, $ent) {
		if ($ent['type'] == '5')
			$this->save_dir($f, $name, $ent);
		else
			$this->save_file($f, $name, $ent);
	}

	public function save($filename, $compress='.gz') {
		$compress = $this->compress_normalize($compress);
		$f = new $compress($filename === NULL ? $this->filename : $filename);
		$this->save_data($f);
		$f->end();

		return TRUE;
	}

	public function to_s() {
		$f = new TarIOString('');
		$this->save_data($f);
		return $f->report();
	}

}

class phpAr {
	private $file;
	private $init;
	public function __construct($file)
	{
		if (!empty($file) AND file_exists($file)) {
			$this->file = $file;
		}
		else {
			return false;
		}
	}
	function listfiles() {
		$handle = fopen($this->file,"rb");
		if (fread($handle, 7) == "!<arch>") {
			fread($handle, 1);
			$filesize = filesize($this->file);
			$list_files = array();
			while (ftell($handle) < $filesize-1) {
				$list_files[] = trim(fread($handle, 16));
				fread($handle, 32);
				$size = trim(fread($handle, 10));
				fread($handle, 2);
				fseek($handle, ftell($handle) + $size);
			}
			return $list_files;
		}
		else {
			return false;
		}
		fclose($handle);
	}
	function replace($name,$new){
		$handle = fopen($this->file,"r+b");
		if (fread($handle, 7) == "!<arch>") {
			fread($handle, 1);
			$filesize = filesize($this->file);
			while (ftell($handle) < $filesize-1) {
				$filename = trim(fread($handle, 16));
				if ($filename == $name) {
					$timestamp = strtotime(date('Y-m-d H:i:s'));
					$w_timestamp = sprintf('%-12s', $timestamp);
					fwrite($handle,$w_timestamp,12);
					fread($handle, 20);
					$raw_size = trim(fread($handle, 10));
					fseek($handle,ftell($handle)-10);
					$new_size = filesize($new);
					$w_new_size = sprintf('%-10s', $new_size);
					fwrite($handle,$w_new_size,10);
					fread($handle, 2);
					$start = ftell($handle);
					$end = $start + $raw_size;
					fclose($handle);
					$new_contents = file_get_contents($new);
					if ($end % 2 != 0) {
						$end = $end + 1;
					}
					if (($start + $new_size) % 2 != 0) {
						$new_contents .= "\x0A";
					}
					$rawfile = file_get_contents($this->file);
					$rawfile = substr_replace($rawfile,$new_contents,$start,$end-$start);
					file_put_contents($this->file,$rawfile);
					return true;
				}
				else {
					fread($handle, 32);
					$size = trim(fread($handle, 10));
					fread($handle, 2);
					fseek($handle, ftell($handle)+$size);
					if (ftell($handle) % 2 != 0) {
						fread($handle, 1);
					}
				}
			}
			// return $file_output;
		}
		else {
			return false;
		}
		fclose($handle);
	}
	function getfile($name) {
		$handle = fopen($this->file,"rb");
		if (fread($handle, 7) == "!<arch>") {
			fread($handle, 1);
			$filesize = filesize($this->file);
			$file_output = array();
			while (ftell($handle) < $filesize-1) {
				$filename = trim(fread($handle, 16));
				if ($filename == $name) {
					$timestamp = trim(fread($handle, 12));
					$owner_id = trim(fread($handle, 6));
					$group_id = trim(fread($handle, 6));
					$file_mode = trim(fread($handle, 8));
					$size = trim(fread($handle, 10));
					fread($handle, 2);
					$content = fread($handle, $size);
					$file_output[] = array($name,$timestamp,$owner_id,$group_id,$file_mode,$size,$content);
				}
				else {
					fread($handle, 32);
					$size = trim(fread($handle, 10));
					fread($handle, 2);
					fseek($handle, ftell($handle)+$size);
				}
			}
			return $file_output;
		}
		else {
			return false;
		}
		fclose($handle);
	} 
}

?>
