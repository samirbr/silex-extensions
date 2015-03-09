<?php
namespace Samir\File;

class Streamer
{
	public $_destination;
	public $_filename;

	public function setDestination($destination, $filename)
	{
		$this->_destination = $destination;
		$this->_filename = $filename;
	}
	
	public function receive()
	{
		$reader = fopen('php://input', "r");
		$writer = fopen($this->_destination . $this->_filename, "w+");

		while (true) {
			$buffer = fgets($reader, 4096);
			
			if (strlen($buffer) == 0) {
				fclose($reader);
				fclose($writer);
				return true;
			}

			fwrite($writer, $buffer);
		}

		return false;
	}
}
