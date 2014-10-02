<?php

namespace Pearman;

class Server {

	const HEADER_REQUEST  = "\0REQ";
	const HEADER_RESPONSE = "\0RES";

	const COMMAND_TEXT               = 0;
	const COMMAND_CAN_DO             = 1;
	const COMMAND_CANT_DO            = 2;
	const COMMAND_RESET_ABILITIES    = 3;
	const COMMAND_PRE_SLEEP          = 4;
	const COMMAND_UNUSED             = 5;
	const COMMAND_NOOP               = 6;
	const COMMAND_SUBMIT_JOB         = 7;
	const COMMAND_JOB_CREATED        = 8;
	const COMMAND_GRAB_JOB           = 9;
	const COMMAND_NO_JOB             = 10;
	const COMMAND_JOB_ASSIGN         = 11;
	const COMMAND_WORK_STATUS        = 12;
	const COMMAND_WORK_COMPLETE      = 13;
	const COMMAND_WORK_FAIL          = 14;
	const COMMAND_GET_STATUS         = 15;
	const COMMAND_ECHO_REQ           = 16;
	const COMMAND_ECHO_RES           = 17;
	const COMMAND_SUBMIT_JOB_BG      = 18;
	const COMMAND_ERROR              = 19;
	const COMMAND_STATUS_RES         = 20;
	const COMMAND_SUBMIT_JOB_HIGH    = 21;
	const COMMAND_SET_CLIENT_ID      = 22;
	const COMMAND_CAN_DO_TIMEOUT     = 23;
	const COMMAND_ALL_YOURS          = 24;
	const COMMAND_WORK_EXCEPTION     = 25;
	const COMMAND_OPTION_REQ         = 26;
	const COMMAND_OPTION_RES         = 27;
	const COMMAND_WORK_DATA          = 28;
	const COMMAND_WORK_WARNING       = 29;
	const COMMAND_GRAB_JOB_UNIQ      = 30;
	const COMMAND_JOB_ASSIGN_UNIQ    = 31;
	const COMMAND_SUBMIT_JOB_HIGH_BG = 32;
	const COMMAND_SUBMIT_JOB_LOW     = 33;
	const COMMAND_SUBMIT_JOB_LOW_BG  = 34;
	const COMMAND_SUBMIT_JOB_SCHED   = 35;
	const COMMAND_SUBMIT_JOB_EPOCH   = 36;

	const COMMAND_SUBMIT_REDUCE_JOB            = 37;
	const COMMAND_SUBMIT_REDUCE_JOB_BACKGROUND = 38;
	const COMMAND_GRAB_JOB_ALL                 = 39;
	const COMMAND_JOB_ASSIGN_ALL               = 40;

	protected $cando = [ ];

	function __construct( $port ) {

		$sock = socket_create_listen($port);
		socket_getsockname($sock, $addr, $port);
		echo "Server Listening on $addr:$port\n";

		while( $currentSocket = @socket_accept($sock) ) {
			if( $currentSocket === false ) {
				sleep(100);
				continue;
			}

			socket_getpeername($currentSocket, $raddr, $rport);
			echo "Received Connection from $raddr:$rport\n";

			$cmd  = socket_read($currentSocket, 4);
			$type = socket_read($currentSocket, 4);
			$size = socket_read($currentSocket, 4);

			$final_type = $this->fourByteStringToInt($type);
			$final_size = $this->fourByteStringToInt($size);

			$data = false;
			if( $final_size > 0 ) {
				$data = socket_read($currentSocket, $final_size);
			}


			see(
				var_export($cmd, true),
				$this->fourByteStringToInt($type),
//				ord($type[0]) . ' ' . ord($type[1]) . ' ' . ord($type[2]) . ' ' . ord($type[3]),
				$this->fourByteStringToInt($size),
//				ord($size[0]) . ' ' . ord($size[1]) . ' ' . ord($size[2]) . ' ' . ord($size[3]),
				$data
			);


			switch( $cmd ) {
				case self::HEADER_REQUEST:
					echo "REQUEST\n";

					switch( $final_type ) {
						case self::COMMAND_CAN_DO:
							break;
						case self::COMMAND_OPTION_REQ:
							sleep(5);

                            //Lie to it
							$this->sendPacket($currentSocket, self::HEADER_RESPONSE, self::COMMAND_OPTION_RES, $data);

							break;
						default:
							echo "Unhandled Command!\n";
							break;
					}

					break;
				case self::HEADER_RESPONSE:
					echo "RESPONSE\n";
					break;
			}


			socket_close($currentSocket);
			sleep(1);
		}

		die('x');

	}

	protected function sendPacket( &$socket, $cmd, $type, $data = "" ) {
		$final_type   = $this->intToFourByteString($type);
		$final_length = $this->intToFourByteString(strlen($data));

		socket_write($socket, $cmd . $final_type . $final_length . $data);
	}

	/**
	 * @param $int
	 * @return string
	 */
	public function intToFourByteString( $int ) {
		//@TODO this is totally wrong and just a mock up
		return (string)$int;
	}

	private final function fourByteStringToInt( $string ) {
		if( strlen($string) != 4 ) {
			throw new \InvalidArgumentException('String to parse must be 4 bytes exactly');
		}

		return (ord($string[0]) << 24) + (ord($string[1]) << 16) + (ord($string[2]) << 8) + ord($string[3]);
	}

}
