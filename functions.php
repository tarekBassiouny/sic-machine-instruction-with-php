<?php
//returns no of bytes (in decimal)
function cal_bytes($mnemonic, $operand)
{
	$found = 0;
	$noOfBytes = -1;
	if($mnemonic == "RESW" || $mnemonic == "RESB" || $mnemonic == "WORD" || $mnemonic == "BYTE")
	{
		$found = 1;
		switch ($mnemonic) {
			case 'RESW':
				$operand = (int)$operand;
				$noOfBytes = $operand * 3;
				break;

			case 'RESB':
				$operand = (int)$operand;
				$noOfBytes = $operand;
				break;

			case 'WORD':
				$noOfBytes = 3;
				break;

			case 'BYTE':
				$length = strlen($operand) - 3 ;
				if($operand[0] == 'X'){
					if($length % 2 == 0) {
						$noOfBytes = (int)($length / 2);
					}else $noOfBytes = ($length / 2) + 1;
				}elseif($operand[0]=='C')	

					$noOfBytes = $length;
				break;
		}
	}
	if($found == 0){
		$opcode = fopen("source/OPCODE.txt", "r") or die("Unable to open file!");
		if ($opcode){

			while (($line = fgets($opcode)) !== false) {
        		$op = preg_split('~\t~', trim($line));

				if($mnemonic == $op[0]){
					$found = 1;
					$noOfBytes = $op[1];
					break;
				}
    		}
    		fclose($opcode);
		}
	}
	if($found == 0) $noOfBytes = -1;

	$noOfBytes = (int)$noOfBytes;
	return $noOfBytes;
}
 // echo(cal_bytes("FLOAT", 3));

//function to check if the symbol already exists or not
function notExists($symbol)
{
	$symFileR = fopen("tmp_files/SYMTAB.txt","r") or die("Unable to open file!");
	$found = 0;
	if ($symFileR){
		while (($line = fgets($symFileR)) !== false) {
        	$spLine = preg_split('~\t~', trim($line));
			if($spLine[0] == $symbol){
				$found = 1;
				break;
			}
    	}
    	fclose($symFileR);
	}
	if($found == 1)	return 0;
	else return 1;
}
// echo notExists("ADDF");

//function returns the opcode of the operand
function retOpcode($mnemonic)
{
	$opcodeF = fopen("source/OPCODE.txt", "r") or die("Unable to open file!");
	$found = 0;
		if ($opcodeF){
			while (($line = fgets($opcodeF)) !== false) {
        		$op = preg_split('~\t~', trim($line));
				if(trim($mnemonic) == trim($op[0])){
					$found = 1;
					$opcode = $op[2];
					break;
				}
    		}
    		fclose($opcodeF);
		}
	if($found == 1)	return $opcode;
	else return -1;
}
// echo retOpcode("ADD");

// function returns address of label
function retAddress($label)
{
	$symFileR = fopen("tmp_files/SYMTAB.txt","r") or die("Unable to open file!");
	$found = 0;
	if ($symFileR){
		while (($line = fgets($symFileR)) !== false) {
        	$spLine = preg_split('~\t~', trim($line));
			if(trim($spLine[0]) == trim($label)){
				$found = 1;
				$tAdd = $spLine[1];
				break;
			}
    	}
    	fclose($symFileR);
	}
	if($found == 1)	return $tAdd;
	else return -1;
}
// echo retAddress("ADD");

// function that returns ascii code of a character
function retAscii($char)
{
	$ascii = fopen("source/ASCII.txt","r") or die("Unable to open file!");
	$found = 0;
	if ($ascii){
		while (($line = fgets($ascii)) !== false) {
        	$spLine = preg_split('~\t~', $line);
			if(trim($spLine[1]) == trim($char)){
				$found = 1;
				$asciiCode = $spLine[0];
				break;
			}
    	}
    	fclose($ascii);
	}
	if($found == 1)	return $asciiCode;
	else return -1;
}
// echo retAscii("b");
?>