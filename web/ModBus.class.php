<?php
//*******************************************************************************//
//               This class allow to connect to ModBus bus                       //
//            It has been design for an RS 485 bus with a USR-TCP-24 converter   //
//                                                                               //
//	Licence :  Creative Commons  Attribution - Pas d’Utilisation Commerciale     //
//              Partage dans les Mêmes Conditions 3.0 France (CC BY-NC-SA 3.0 FR)//
//                                                                               //
//                                                                               //
//   Date     | Version |    Auteur     | Nature de la modification              //
// 14/11/2015 |  1.0.0  | Domip         | Creation                               //
//            |         |               |                                        //
//*******************************************************************************//


class ModBus {
	const FRAME_EMPTY=1;
	const SOCKET_ERROR=2;
	const FRAME_ERROR=3;
	const FRAME_LENGTH_ERROR=4;
	const FRAME_TOO_SHORT=5;
	const NOT_ADRESSED_TO_ME=6;
	const NOT_SUPPORTED_FC=7;
	const FRAME_TOO_LONG=8;
	const CRC_ERROR=9;
	const NO_ACK=10;
	const ADDR_ERROR=11;
	const FC_ERROR=12;
	
	const READ_ANALOG_HOLDING_REGISTERS=0x03;
	const WRITE_MULTIPLE_REGISTERS=0x10;
	
	const FRAME_MIN_LENGTH=0x04;
	const FRAME_MAX_LENGTH=0x100;
	const FRAME_EXTRA_BYTE_ALLOWED=0x03;		//number of extra byte allowed at the end of a frame
	
	const REQUEST_TO_ANSWER_DELAY_STEP=20000;	// delay step between request and answer in uS
	const REQUEST_TO_ANSWER_MAX_STEP=50;		// nb max of delay step 
	const SOCKET_MAX_BYTE_READ=0x200;			// nb max of bytes read on a socket
	

// status to remember if the regulator is connected
public $status=FALSE;

// constructor create the object and connect it to the regulator	
function __construct($modBusAddr,$ipAddr,$port) {
	$this->modBusAddr=$modBusAddr;
	$this->ipAddr=$ipAddr;
	$this->port=$port;
	$this->socket=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
	if ($this->socket != FALSE) {
		$this->status=socket_connect($this->socket,$this->ipAddr,$this->port=$port);
		socket_set_nonblock($this->socket);
	}
return($this->status);
}


//function used to receive data in (slave mode)
function slaveRx() {

	//init
	$this->rxReg=array();
	$this->rxBuf="";
	$this->status=0;
	
	$this->rxBuf = socket_read($this->socket, self::SOCKET_MAX_BYTE_READ);
	//log
	$this->log="Slave RX (".strlen($this->rxBuf)."):";
	$this->log.=bin2hex($this->rxBuf). " : " ;

	//check socket result
	if ($this->rxBuf===FALSE) {
		$this->log.="SOCKET_ERROR\n";
		return($this->status=self::SOCKET_ERROR);
	}
	
	//check if buffer is empty
	if ($this->rxBuf=="") {
		$this->log.="FRAME_EMPTY\n";
		return($this->status=self::FRAME_EMPTY);
	}
	
	//check rough length
	if ((strlen($this->rxBuf) > self::FRAME_MAX_LENGTH)|| (strlen($this->rxBuf) < self::FRAME_MIN_LENGTH )) {
		$this->log.="FRAME_LENGTH_ERROR\n";
		return($this->status=self::FRAME_LENGTH_ERROR);
	}
	
	//check modBus addr, if slave address is not 0
	$addr=ord($this->rxBuf[0]);
	if ( ($this->modBusAddr!=0)&& ($addr!=$this->modBusAddr)) {
		$this->log.="NOT_ADRESSED_TO_ME\n";
		return($this->status=self::NOT_ADRESSED_TO_ME);
	}
	
	//check modbus feature
	$fc=ord($this->rxBuf[1]);
	switch($fc) {
		case self::WRITE_MULTIPLE_REGISTERS;
			//decode WRITE_MULTIPLE_REGISTERS frame
			//decode register number
			if (strlen($this->rxBuf) < 9 ) {
				$this->log.="FRAME_ERROR\n";
				return($this->status=self::FRAME_ERROR);
			}
			$regNumber=ord($this->rxBuf[4])*0x100 + ord($this->rxBuf[5]);

			//check that byte nb is twice register number
			if (2*$regNumber != ord($this->rxBuf[6])) {
				$this->log.="FRAME_ERROR\n";
				return($this->status=self::FRAME_ERROR);
			}

			//calculate waited frame length
			$frameLength=2*$regNumber+9;

			//check frame size is not too short according data volume
			if ($frameLength > strlen($this->rxBuf)) {
				$this->log.="FRAME_TOO_SHORT\n";
				return($this->status=self::FRAME_TOO_SHORT);
			}

			//check frame size is not too long according data volume (3 extra byte tolerated)
			if (($frameLength +self::FRAME_EXTRA_BYTE_ALLOWED) < strlen($this->rxBuf)) {
				$this->log.="FRAME_TOO_LONG\n";
				return($this->status=self::FRAME_TOO_LONG);
			}

			//check CRC
			$crcCalc=self::calcCRC16($this->rxBuf,$frameLength-2);
			$crc=0x100*ord($this->rxBuf[$frameLength-1])+ord($this->rxBuf[$frameLength-2]);
			if ($crc!= $crcCalc) {
				$this->log.="CRC_ERROR\n";
				return($this->status=self::CRC_ERROR);
			}

			//The frame is correct : save values and send an ack
			$regAddr=0x100*ord($this->rxBuf[2])+ord($this->rxBuf[3]);
			for ($i=0;$i<$regNumber;$i++) {
				$this->rxReg[$regAddr+$i]=0x100*ord($this->rxBuf[2*$i+7])+ord($this->rxBuf[2*$i+8]);
			}
			$this->log.="FRAME_OK\n";
			$this->log.=print_r($this->rxReg,TRUE);

			// set the ack content
			$tx=substr($this->rxBuf,0,6);

			// calculate and add the checksum
			$crCalc=self::calcCRC16($tx,6);
			$tx.=chr($crCalc & 0xFF);
			$tx.=chr(($crCalc>>8) & 0xFF);
			$tx.=chr(0).chr(0).chr(0);

			//send the ack if slave address is not 0
			if ($this->modBusAddr!=0) {
					$this->log.="Ack:".bin2hex($tx)."\n";
					$result=socket_write($this->socket, $tx);
			}

			return($this->status=0);
		break;
		
		case self::READ_ANALOG_HOLDING_REGISTERS;
			//decode READ_ANALOG_HOLDING_REGISTERS frame

			//calculate waited frame length
			$frameLength=8;

			//check frame size is not too short according data volume
			if ($frameLength > strlen($this->rxBuf)) {
				$this->log.="FRAME_TOO_SHORT\n";
				return($this->status=self::FRAME_TOO_SHORT);
			}
			
			//check frame size is not too long according data volume (3 extra byte tolerated)
			if (($frameLength +self::FRAME_EXTRA_BYTE_ALLOWED) < strlen($this->rxBuf)) {
				$this->log.="FRAME_TOO_LONG\n";
				return($this->status=self::FRAME_TOO_LONG);
			}

			//check CRC
			$crcCalc=self::calcCRC16($this->rxBuf,$frameLength-2);
			$crc=0x100*ord($this->rxBuf[$frameLength-1])+ord($this->rxBuf[$frameLength-2]);
			if ($crc!= $crcCalc) {
				$this->log.="CRC_ERROR\n";
				return($this->status=self::CRC_ERROR);
			}

			//The frame is correct : save values and send an ack
			$regAddr=0x100*ord($this->rxBuf[2])+ord($this->rxBuf[3]);
			$regNb=0x100*ord($this->rxBuf[4])+ord($this->rxBuf[5]);

			$this->log.="FRAME_OK Read :".$regAddr.":".$regNb."\n";

			//do not send ack as there is not data to provide
			return($this->status=0);
		break;
		default:
			$this->log.="NOT_SUPPORTED_FC :".$fc."\n";
			return($this->status=self::NOT_SUPPORTED_FC);
	}

}


//function used to get data in master mode
function masterRx($modBusAddr,$regAddr,$regNb) {
	//init
	$this->rxReg=array();
	
	//Init log
	$this->log="Master RX:".$modBusAddr.":".$regAddr.":".$regNb."\n";
	
	//prepare request frame
	$tx=chr($modBusAddr).chr(self::READ_ANALOG_HOLDING_REGISTERS).self::int2str($regAddr).self::int2str($regNb);
	$tx.=self::int2str(self::switchEndian(self::calcCRC16($tx))).chr(0);
	
	//send it
	$result=socket_write($this->socket, $tx);
	//log
	$this->log.="Request: ".bin2hex($tx)."\n";
	
	//wait  for answer frame
	$buf = "";
	$i=0;
	do {
		usleep(self::REQUEST_TO_ANSWER_DELAY_STEP);
		$this->rxBuf = socket_read($this->socket, self::SOCKET_MAX_BYTE_READ);
		$i++;
	} while((($this->rxBuf===FALSE) || ($this->rxBuf=="")) && ($i< self::REQUEST_TO_ANSWER_MAX_STEP));
	
	$this->log.="Answer(".strlen($this->rxBuf)."): ".bin2hex($this->rxBuf)."\n";
	
	//check socket result
	if ($this->rxBuf===FALSE) {
		$this->log.="SOCKET_ERROR\n";
		return($this->status=self::SOCKET_ERROR);
	}
	
	//check rough length
	if ((strlen($this->rxBuf) > self::FRAME_MAX_LENGTH)|| (strlen($this->rxBuf) < self::FRAME_MIN_LENGTH )) {
		$this->log.="FRAME_ERROR\n";
		return($this->status=self::FRAME_ERROR);
	}
	
	//check modBus addr
	$addr=ord($this->rxBuf[0]);
	if ( $addr!=$modBusAddr) {
		$this->log.="NOT_ADRESSED_TO_ME\n";
		return($this->status=self::NOT_ADRESSED_TO_ME);
	}
	
	//check modbus feature
	$fc=ord($this->rxBuf[1]);
	if ($fc!=self::READ_ANALOG_HOLDING_REGISTERS) {
		$this->log.="NOT_SUPPORTED_FC\n";
		return($this->status=self::NOT_SUPPORTED_FC);
	}
	
	//check answer number of bytes, if the received frame is too small or too long
	$byteNb=ord($this->rxBuf[2]);
	$frameLength=5+$byteNb;
	if (($byteNb!=2*$regNb) || (strlen($this->rxBuf)<$frameLength) ||( $frameLength +self::FRAME_EXTRA_BYTE_ALLOWED < strlen($this->rxBuf))){
		$this->log.="FRAME_ERROR\n";
		return($this->status=self::FRAME_ERROR);
	}
		
	//check CRC
	$crcCalc=self::calcCRC16($this->rxBuf,$frameLength-2);
	$crc=0x100*ord($this->rxBuf[$frameLength-1])+ord($this->rxBuf[$frameLength-2]);
	if ($crc!= $crcCalc) {
		$this->log.="CRC_ERROR\n";
		return($this->status=self::CRC_ERROR);
	}
	
	//The frame is correct : save values
	for ($i=0;$i<$regNb;$i++) {
		$this->rxReg[$regAddr+$i]=0x100*ord($this->rxBuf[2*$i+3])+ord($this->rxBuf[2*$i+4]);
		if ($this->rxReg[$regAddr+$i] >= 0x8000) $this->rxReg[$regAddr+$i]=-($this->rxReg[$regAddr+$i] & 0x7FFF);
	}
	$this->log.=print_r($this->rxReg,TRUE);
	return($this->status=0);
}

//function used to set 1 data register in master mode
function masterTx($modBusAddr,$data) {
	$register[0]=$data;
	return($this->masterTxN($modBusAddr,$register));
}

//function used to set data in master mode for several consecutive registers (with consecutive numeric index)
function masterTxN($modBusAddr,$register) {

	//Init log
	$this->log="Master TXN:".$modBusAddr.":".print_r($register,TRUE);

	//get number of register
	$nb=count($register);
	//prepare request frame
	$tx=chr($modBusAddr).chr(self::WRITE_MULTIPLE_REGISTERS).self::int2str($register[0]->addr).self::int2str($nb).chr(2*$nb);
	for ($index=0;$index < $nb;$index++) $tx.=self::int2str($register[$index]->set);
	$tx.=self::int2str(self::switchEndian(self::calcCRC16($tx))).chr(0); 
	
	//log
	$this->log.="Request: ".bin2hex($tx)."\n";	
	
	//send it
	$result=socket_write($this->socket, $tx);

	//wait 100 ms max for answer frame
	$buf = "";
	$i=0;
	do {
		usleep(self::REQUEST_TO_ANSWER_DELAY_STEP);
		$buf = socket_read($this->socket, self::SOCKET_MAX_BYTE_READ);
		$i++;
	} while((($buf===FALSE) || ($buf=="")) && ($i< self::REQUEST_TO_ANSWER_MAX_STEP));
	
	//log
	$this->log.="Answer: ".bin2hex($buf)."\n";
	
	//check socket result
	if ($buf===FALSE) {
		$this->log.="NO_ACK\n";
		return($this->status=self::NO_ACK);
	}
	
	//check rough length
	if ((strlen($buf) > self::FRAME_MAX_LENGTH)|| (strlen($buf) < self::FRAME_MIN_LENGTH )) {
		$this->log.="FRAME_ERROR\n";
		return($this->status=self::FRAME_ERROR);
	}
	
	//check modBus addr
	$addr=ord($buf[0]);
	if ( $addr!=$modBusAddr) {
		$this->log.="ADDR_ERROR\n";
		return($this->status=self::ADDR_ERROR);
	}
	
	//check modbus feature
	$fc=ord($buf[1]);
	if ($fc!=self::WRITE_MULTIPLE_REGISTERS) {
		$this->log.="FC_ERROR\n";
		return($this->status=self::FC_ERROR);
	}
	
	//check answer number of bytes, if the received frame is too small or too long
	$regNb=ord($buf[5]);
	$frameLength=8;
	if (($regNb!=$nb) || (strlen($buf)<$frameLength) ||( $frameLength +self::FRAME_EXTRA_BYTE_ALLOWED < strlen($buf))) {
		$this->log.="FRAME_ERROR\n";
		return($this->status=self::FRAME_ERROR);
	}
		
	//check CRC
	$crcCalc=self::calcCRC16($buf,$frameLength-2);
	$crc=0x100*ord($buf[$frameLength-1])+ord($buf[$frameLength-2]);
	if ($crc!= $crcCalc) {
		$this->log.="CRC_ERROR\n";
		return($this->status=self::CRC_ERROR);
	}
	
	return($this->status=0);
}

// function used to calculate ModBus CRC16 of array $buf
// $buf shall be a string
function calcCRC16($buf,$length=0) {
	//variable initialization
	$crc=0xFFFF;
	$b=$i=$n=0;
	if ($length==0) $length=strlen($buf);
	
	//crc calculation
	for($i=0;$i<$length;$i++) {
		$b= ord($buf[$i]);
		$crc= $crc^$b;
		
		for($n= 1; $n<=8; $n++) {
			if(($crc & 1)== 1)
				$crc= ($crc >> 1)^0xA001;
			else
				$crc=$crc >> 1;
		}
	}
	return($crc);
}

// function modBus integer to string
function int2str($int) {
	return(chr(($int>>8) & 0xFF).chr($int & 0xFF));
}

function switchEndian($int) {
	return((($int>>8) & 0xFF)|(($int & 0xFF)<<8));
}

}
?>
