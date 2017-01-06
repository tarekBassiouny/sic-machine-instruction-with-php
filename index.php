<html>
<head>
	<title>Sic Assembler</title>
</head>
<body>
	<form action="index.php" method="POST">
    	Enter the name of the file: <br><br>
    	<input type="text" name="filename" id="fileToUpload">
    	<input type="submit" value="assemly" name="submit">
	</form>
    <p style="color:red" id="error"></p>
</body>
</html>			

<?php

if(isset($_POST["submit"])) {
    $fileName = $_POST['filename']; 
    if (isset($fileName) && !empty($fileName)) {
        require "functions.php";
        $asmFile = fopen(strtoupper($fileName).'.asm',"r") or die("Unable to open file!");
        $aCode = fopen("tmp_files/aCODE.txt", "w") or die("Unable to open file!");
        $symFile = fopen("tmp_files/SYMTAB.txt", "w") or die("Unable to open file!");

        $start = fgets($asmFile);         #assumption: first line of the code is the start statement
        while($start[0]=='.')
            $start = fgets($asmFile);

        $firstLine = preg_split('~\t~', $start);
// $prog_name = $firstLine[0];
// echo'<pre>';echo $prog_name;
        $Addf = trim($firstLine[2]); #value of address in hex
        $Add = hexdec($Addf);  #converted the value in decimal
        $Add1= $Addf;       # string containing hex value

        if ($asmFile){
            while (($line = fgets($asmFile)) !== false) {
                $spLine = preg_split('~\s+~', ltrim($line, ' '));
                $count = count($spLine)-1;
                if($line[0] != '.'){
                    if ($spLine[1] == 'END') break;
                    if ($spLine[$count] == '' && empty($spLine[$count])) unset($spLine[$count]);
                    $noOfBytes = 0;

                    if(count($spLine) == 3){
                        $noOfBytes = cal_bytes($spLine[1], $spLine[2]);
                    }else{
                        $noOfBytes = cal_bytes($spLine[1], 0);  
                    }
                    if($noOfBytes == -1){
                        $error = "Error: Invalid mnemonic " . $spLine[1];
                        echo $error;
                        exit;
                    }
                    # ENTRY INTO SYMTAB
                    if($spLine[0] != ''){
                        if(notExists($spLine[0])){
                            $symbol = $spLine[0] . "\t" . $Add1.PHP_EOL;
                            fwrite($symFile, $symbol);
                            flush();
                        }else{
                            $error= "Error: " . $spLine[0] . " - Multiple declaration";
                            echo $error;
                            exit;
                        }       
                    }
                    #WRITING INSTRUCTIONS ALONG WITH ASSIGNED ADDRESSES
                    $writeLine = $Add1 . "\t" . $line;  
                    fwrite($aCode, $writeLine);          
                    flush();
                        
                    #CALCULATION OF NEXT ADDRESS
                    $Add = $Add + $noOfBytes;
                    $Add1 = dechex($Add);
                }
            }
            fclose($symFile);
            fclose($asmFile);
            fclose($aCode);
        }
//===============================================================================
        $aCodeI = fopen("tmp_files/aCODE.txt", "r") or die("Unable to open file!");  #assembly code file with addresses
        $objCode = fopen("objCODE.txt","a+") or die("Unable to open file!");  #to store the assembly file with object code
        $obj = fopen("sic.o","a+") or die("Unable to open file!");

        if ($aCodeI){
            while (($line = fgets($aCodeI))!== false) {
          
                $lineSp = preg_split('~\t~', $line);
                $label = $lineSp[1];
                $mnemonic = $lineSp[2];

                if(count($lineSp) == 4)
                    $operand = $lineSp[3];

                if($mnemonic!="RESW" && $mnemonic!="RESB"){        

                    if($mnemonic == "BYTE"){
                        $arr = preg_split('~\'~', $operand);
                        if($arr[0] == "X"){
                            $objLine = $arr[1];
                        }elseif($arr[0] == "C"){
                            $chars = str_split($arr[1]); //          
                            $objLine = "";
                            foreach ($chars as $char) {
                                $asciiCode = retAscii($char);
                                if($asciiCode == -1){
                                    echo "Error: Invalid character in BYTE";
                                    exit;
                                }
                                $objLine = $objLine . $asciiCode;
                            }
                        }
                    }elseif($mnemonic == "WORD"){
                        $operand = (int)$operand;
                        $objLine = hexdec($operand);
                    }elseif($mnemonic=="RSUB"){
                        $opcode = retOpcode($mnemonic);
                        if($opcode == -1){
                            echo "Error: Opcode for RSUB could not be found";
                            exit;
                        }
                        $objLine = $opcode."0000";
                    }else{

                        $opcode = retOpcode($mnemonic);
                        if($opcode == -1){
                            $error = "Error: Opcode for " . $mnemonic . " could not be found";
                            echo $error;
                            exit;
                        }
                        $operandSp = preg_split('~,~', $operand);
                        $length = count($operandSp);                     
                        $targetAdd = retAddress($operandSp[0]);

                        if(trim($targetAdd) == -1){

                            $error = "Error: Target address of " . $operandSp[0] . " could not be found";
                            echo $error;
                            exit;
                        }

                        if($length == 2 && $operandSp[1] == "X"){
                            $string = $targetAdd;
                            $part1 = substr($string, 0, 1);//string[:1];//stsub
                            $part2 = substr($string, 1);//string[1:];//stsub
                            
                            $part1 = (int)$part1;
                            $part1 = $part1 . 8;
                            $part1 = hexdec($part1);
                            
                            $targetAdd = $part1 + $part2; 
                        }                    
                        
                        $objLine = $opcode . $targetAdd;
                    }   
                    if(trim($mnemonic) == "RSUB")
                        $writeLine = trim($line) . "\t\t". trim($objLine).PHP_EOL;     
                    else
                        $writeLine = trim($line) . "\t" . trim($objLine).PHP_EOL; 

                    fwrite($objCode, $writeLine);       
                    fwrite($objCode, "\n");
                    fwrite($obj, $objLine);                      
                    fwrite($obj, "\n");       
                    flush();
              
                }else{
                    fwrite($objCode, $line);          
                    fwrite($objCode, "\n");
                    flush();
                }
            }

            fclose($aCodeI);
            fclose($objCode);
            fclose($obj);
        }
        #object code written in appropriate files
        #################################################################################################

        $objCode = fopen("objCODE.txt","r") or die("Unable to open file!");
        if ($objCode){
            while (($line = fgets($objCode)) !== false) {
                echo'<pre>';echo $line;
            }
            fclose($objCode);
        }
    }else{
        echo "
            <script>
                var errortxt = 'Please enter file name';
                document.getElementById('error').innerHTML = errortxt;
            </script>
        ";
    }
}

?>

