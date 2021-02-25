<?php
/**
 * Created by PhpStorm.
 * User: kiran
 * Date: 9/12/17
 * Time: 11:06 AM
 */

class XlCore_file {
	private $upload_dir;
	private static $ins = null;
	private $core_dir = 'xlplugins';
	private $component = 'dealpages';

	public function __construct( $componet = '' ) {
		$upload            = wp_upload_dir();
		$this->upload_dir  = $upload['basedir'];
		$this->xl_core_dir = $this->upload_dir . "/" . $this->core_dir;
		$this->set_component( $componet );
		if ( self::$ins == null ) {
			$this->makedirs();
			self::$ins = 1;
		}
	}

	public function is_writable() {
		if ( is_writable( $this->upload_dir ) ) {
			return true;
		}

		return false;
	}

	public function set_component( $componet ) {

		if ( $componet != '' && in_array( $componet, apply_filters( "xlplugins_upload_dir", array( 'dealpages' ) ) ) ) {
			$this->component = $componet;
		}
	}

	public function get_component_dir() {
		return $this->xl_core_dir . "/" . $this->component;
	}

	public function file_path( $file ) {
		$dir       = $this->get_component_dir();
		$file_path = $dir . "/" . $file;

		return $file_path;
	}

	public function makedirs() {
		$add_on_plugins = apply_filters( "xlplugins_upload_dir", array( 'dealpages' ) );
		if ( $this->is_writable() ) {
			$this->create_dir( $this->xl_core_dir );
			if ( is_array( $add_on_plugins ) && count( $add_on_plugins ) > 0 ) {
				foreach ( $add_on_plugins as $plugins ) {
					$dir = $this->xl_core_dir . "/" . $plugins;
					$this->create_dir( $dir );
				}
			}
		}
	}

	private function create_dir( $file_dir ) {
		if ( $file_dir != "" && ! file_exists( $file_dir ) ) {
			return mkdir( $file_dir );
		}
		if ( $file_dir != "" && file_exists( $file_dir ) ) {
			return true;
		}

		return false;
	}


	public function create_file( $file ) {
		$file_path = $this->file_path( $file );
		if ( $file_path != '' ) {
			if ( @touch( $file_path ) ) {
				return array( "path" => $file_path, 'file' => $file );
			}
		}

		return false;
	}

	public function put_data( $file, $data ) {
		if ( $file != '' && ! empty( $data ) ) {
			$file = $this->create_file( $file );
			if ( is_array( $file ) ) {
				$path = $file['path'];

				return file_put_contents( $path, json_encode( $data ) );
			}
		}

		return false;
	}

	public function get_data( $file ) {
		if ( $file != '' ) {
			$file_path = $this->file_path( $file );
			if ( file_exists( $file_path ) ) {
				$data = file_get_contents( $file_path );
				if ( $data != "" ) {
					return json_decode( $data, true );
				}
			} else {
				@touch( $file_path );
			}
		}

		return array();
	}

	public function delete( $file ) {
		if ( $file != '' ) {
			$file_path = $this->file_path( $file );
			if ( file_exists( $file_path ) ) {
				return @unlink( $file_path );
			}
		}

		return false;
	}
}
