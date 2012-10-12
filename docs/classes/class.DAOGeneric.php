<?php
class DAOGeneric {
    /// ------------------------------------------------------------------------
    /// author: David P Smith
    /// copyright: http://creativecommons.org/licenses/by-sa/3.0/us/
    /// repository: https://github.com/s33k3r/PSF/
    /// version: 1.0
    /// description:    Generic Data Access Object for any table
    ///			This is used to access the database anonomously.
    ///			The Data Transfer Object (DTO) contains both the (VO)  
    ///                 Value Objects table structure and Domain Object (DO) 
    ///                 data definitions. Inserted data is escaped via the 
    /// 		Database Object Provided based on datatype.
    /// ------------------------------------------------------------------------
    
    private $_Verbose = false; 	// Turns verbose mode on (true) or off (false)
    private $_Ready = false;    // Determines if this class is ready
    private $_Interface;        // Empty associative array interface for 1 row of the tables data
    private $_TableName;        // Name of the table to use
    private $_DatabaseName;     // Name of the table to use
            
    // ---------------------------------------------------------------
    //                     CLASS CONFIGURATION
    // ---------------------------------------------------------------

    private $_UseAcid = true;   // Use acid compliant transaction processing verbose mode on (true) or off (false)
    private $_oDatabase;        // This is an instance of the database object
    private $_oDTO;             // This is the Data Transfer Object

    // ---------------------------------------------------------------
    //                     CLASS FUNCTIONS / METHODS
    // ---------------------------------------------------------------
    
    /**
     * Retrives Primary Keys from the database
     * @param void
     * @example $DAOGeneric->PrimaryKeysGet();
     * @return array result
     */
    public function PrimaryKeysGet(){
        $result = array();
        try{
            if($this->_Ready){
                foreach($this->_oDTO->Columns as $dto_key => $dto_value){
                    // check field name matches
                    if($key === $dto_key){
                        if( $dto_value['is_key'] === 'PRI' ){
                            $result[ count($result) ] = $key;
                        }
                    }
                }
            }else{
                $this->_Log(array(
                    'method' => 'PrimaryKeysGet'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
                $this->_Log(array(
                    'method' => 'PrimaryKeysGet'
                    , 'severity' => 'error'
                    , 'text' => '' . $oException
                ));
        }	
        return $result;
    }
    
    /**
     * Returns an empty array that matches the table structure
     * @param void
     * @example $DAOGeneric->InterfaceGet();
     * @return array result
     */
    public function InterfaceGet() {
        $result = array();
        try	{
            if(isset($this->_oDTO)){
                if( count( $this->_Interface ) > 0 ){
                        $result = $this->_Interface;
                }else{
                        foreach( $this->_oDTO->Columns as $dto_property => $dto_value ){
                                $result[ $dto_property ] = '';
                        }
                        $this->_Interface = $result;
                }
            }else{
                $this->_Log(array(
                    'method' => 'InterfaceGet'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'InterfaceGet'
                , 'severity' => 'error'
                , 'text' => '' . $oException
            ));
        }
        return $result;
    }
    
    /**
     * Encapsulates starting a transaction for a database transaction for an 
     * ACID compliant database
     * @param void
     * @example $DAOGeneric->TransactionBegin();
     * @return boolean result
     */
    public function TransactionBegin(){
        $result = false;
        try{
            if($this->_Ready){
                if($this->_UseAcid){
                        $this->_oDatabase->SessionGet()->beginTransaction();
                        $result = true;			
                }
            }else{
                $this->_Log(array(
                    'method' => 'TransactionBegin'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'TransactionBegin'
                , 'severity' => 'error'
                , 'text' => '' . $oException
            ));
        }	
        return $result;
    }
    
    /**
     * Encapsulates commiting a transaction for a database transaction for an 
     * ACID compliant database
     * @param void
     * @example $DAOGeneric->TransactionCommit();
     * @return boolean result
     */
    public function TransactionCommit(){
        $result = false;
        try{
            if($this->_Ready){
                if($this->_UseAcid){
                        $this->_oDatabase->SessionGet()->commit();
                        $result = true;
                }
            }else{
                $this->_Log(array(
                    'method' => 'TransactionCommit '
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
                $this->_Log(array(
                    'method' => 'TransactionCommit'
                    , 'severity' => 'error'
                    , 'text' => ''.$oException
                ));
        }
        return $result;
    }
    
    /**
     * Encapsulates rolling back a transaction for a database transaction for an 
     * ACID compliant database
     * @param void
     * @example $DAOGeneric->TransactionRollback();
     * @return boolean result
     */
    public function TransactionRollback(){
        $result = false;
        try{
            if($this->_Ready){
                if($this->_UseAcid){
                        $this->_oDatabase->SessionGet()->rollBack();
                        $result = true;
                }
            }else{
                $this->_Log(array(
                    'method' => 'TransactionRollback'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
                $this->_Log(array(
                    'method' => 'TransactionRollback'
                    , 'severity' => 'error'
                    , 'text' => ''.$oException
                ));
        }

        return $result;
    }	

    /**
     * Run SQL with a expected result set (such as SELECT)
     * @param (string)$SQL
     * @param (array)$Params [optional] 2D array; 
     *     i.e. array(array('name'=>'id','value'=>1),array(array('name'=>'firstname','value'=>'Bob')), ... )
     * @example $DAOGeneric->Query('SELECT * FROM db.tblname');
     * @example $DAOGeneric->Query('SELECT * FROM db.tblname WHERE id = :id', array(array('name'=>'id','value'=>1)) ); 
     * @return array result
     */
    public function Query( $SQL ){
        
        $result = array(); // null result
        $index = 0;        
        
        try{
            if($this->_Ready){   
                
                $oDataSet; // call here for garbage collection reasons
                
                try{
                    $oDataSet = $this->_oDatabase->SessionGet()->prepare($SQL);

                    // Emulate Overload to get the Parameter's 2D array
                    if(func_num_args() > 1){
                        $arguments = func_get_args();
                        $Params = $arguments[0];

                        for($a = 0; $a < count($Params); $a++){
                            $_param_type;
                            if( array_key_exists('name', $Params[$a]) && array_key_exists('value', $Params[$a]) ){

                                // Is the Param data type supplied or must we infer it?
                                if( array_key_exists('name', $Params[$a]) ){
                                    $param_type = $this->_oDTO->Columns[$_dto_column]['pdo_param'];

                                }else{
                                    $param_type = $this->_InferPDOParam($_param_value);

                                }

                                $oDataSet->bindValue(
                                        (':' . $Params[a]['name'])
                                        , $Params[a]['value']
                                        , $this->PDO_PARAM_Get( $_param_type )
                                );

                            }else{

                                $this->_Log(array(
                                    'method' => 'Query'
                                    , 'severity' => 'warning'
                                    , 'text' => 'Insufficent data to set Parameter.'
                                ));
                            }
                        }
                    }

                    $oDataSet->execute();
                    $oDataSet->setFetchMode(PDO::FETCH_ASSOC);
                    
                    // Fetch resulting data
                    while ($row = $oDataSet->fetch()) {
                            $result[$index] = $row;
                            $index++;
                    }
                    
                } catch (Exception $oException) {
                    $this->_Log(array(
                        'method' => 'Query'
                        , 'severity' => 'warning'
                        , 'text' => 'Error running SQL Query or retrieving data.'
                    ));
                }
                
                // Garbage collection outside try catch for safety.
                $oDataSet->closeCursor();
                
            }else{
                $this->_Log(array(
                    'method' => 'Query'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'Query'
                , 'severity' => 'error'
                , 'text' => '' . $oException
            ));
        }
        return $result;
    }
    
    /**
     * Run SQL with no expected result set (INSERT, UPDATE, DELETE, etc.)
     * @param (string)$SQL
     * @param (array)$Params [optional]
     * @example $DAOGeneric->Execute();
     * @return (int)result: 1 = success, 0 = failure
     */
    public function Execute( $SQL ){
        
        $result = 0; // null result
        try{
            if($this->_Ready){
                
                $oDataSet; // call here for garbage collection reasons
                
                try{
                    $oDataSet = $this->_oDatabase->SessionGet()->prepare($SQL);

                    // Emulate Overload to get the Parameter's 2D array
                    if(func_num_args() > 1){
                        $arguments = func_get_args();
                        $Params = $arguments[0];

                        for($a = 0; $a < count($Params); $a++){
                            $_param_type;
                            if( array_key_exists('name', $Params[$a]) && array_key_exists('value', $Params[$a]) ){

                                // Is the Param data type supplied or must we infer it?
                                if( array_key_exists('name', $Params[$a]) ){
                                    $param_type = $this->_oDTO->Columns[$_dto_column]['pdo_param'];

                                }else{
                                    $param_type = $this->_InferPDOParam($_param_value);

                                }

                                $oDataSet->bindValue(
                                        (':' . $Params[a]['name'])
                                        , $Params[a]['value']
                                        , $this->PDO_PARAM_Get( $_param_type )
                                );

                            }else{

                                $this->_Log(array(
                                    'method' => 'Query'
                                    , 'severity' => 'warning'
                                    , 'text' => 'Insufficent data to set Parameter.'
                                ));
                            }
                        }
                    }

                    $oDataSet->execute();

                } catch (Exception $oException) {
                    $this->_Log(array(
                        'method' => 'Execute'
                        , 'severity' => 'warning'
                        , 'text' => 'Error running SQL Query.'
                    ));
                }
                
                // Garbage collection outside try catch for safety.
                unset($oDataSet);
                $result = 1;
           
            }else{
                $this->_Log(array(
                    'method' => 'Execute'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'Execute'
                , 'severity' => 'error'
                , 'text' => '' . $oException
            ));
        }
        return $result;
    }

    /**
     * Counts records in the current Database Table
     * @param
     * @example $DAOGeneric->CountAll();
     * @return int result 
     */
    public function CountAll() {
        $result = 0; // null result
        try{
            if($this->_Ready){
                            
                // TODO: work starts here
            
            }else{
                $this->_Log(array(
                    'method' => 'CountAll'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'CountAll'
                , 'severity' => 'error'
                , 'text' => '' . $oException
            ));
        }
        return $result;
    }
    
    /**
     * Gets all records in the current Database Table
     * @param
     * @example $DAOGeneric->GetAll();
     * @return array result
     */
    public function GetAll() {
        $result = 0; // null result
        try{
            if($this->_Ready){
                $sql = 'SELECT * FROM ' . $this->_DatabaseName . '.' . $this->_TableName;
            
                // TODO: work starts here
            
            }else{
                $this->_Log(array(
                    'method' => 'GetAll'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'GetAll'
                , 'severity' => 'error'
                , 'text' => '' . $oException
            ));
        }
        return $result;
    }
    
    /**
     * Gets all records in the current Database Table
     * @param
     * @example $DAOGeneric->Insert();
     * @return array result
     */
    public function Insert() {
        $result = 0; // null result
        try{
            if($this->_Ready){
                $sql = 'SELECT * FROM ' . $this->_DatabaseName . '.' . $this->_TableName;
            
                // TODO: work starts here
            
            }else{
                $this->_Log(array(
                    'method' => 'Insert'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'Insert'
                , 'severity' => 'error'
                , 'text' => '' . $oException
            ));
        }
        return $result;
    }
    
    /**
     * Gets all records in the current Database Table
     * @param
     * @example $DAOGeneric->Update();
     * @return array result
     */
    public function Update() {
        $result = 0; // null result
        try{
            if($this->_Ready){
                $sql = 'UPDATE ' . $this->_DatabaseName . '.' . $this->_TableName 
                        .' SET ' ; 
                // something to something 
                // where something is something
            
                // TODO: work starts here
            
            }else{
                $this->_Log(array(
                    'method' => 'Update'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'Update'
                , 'severity' => 'error'
                , 'text' => '' . $oException
            ));
        }
        return $result;
    }
    
    /**
     * Deletes all records in the current Database Table matching ID
     * @param
     * @example $DAOGeneric->Delete();
     * @return array result
     */
    public function Delete() {
        $result = 0; // null result
        try{
            if($this->_Ready){
                $sql = 'DELETE * FROM ' 
                    . $this->_DatabaseName . '.' . $this->_TableName
                    . ' WHERE ' . $ID . ' = ' . $Value;
            
                // TODO: work starts here
            
            }else{
                $this->_Log(array(
                    'method' => 'Delete'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'Delete'
                , 'severity' => 'error'
                , 'text' => '' . $oException
            ));
        }
        return $result;
    }
    
    /**
     * Deletes ALL record in Table
     * @example $DAOGeneric->Truncate();
     * @return array result
     */
    public function Truncate(){
        try {
            if($this->_Ready){
                $_t = $this->TransactionBegin();
                $sql = "TRUNCATE " . $this->_DatabaseName .'.'. $this->_TableName;
                $oDataSet = $this->_oDatabase->SessionGet()->prepare($sql);
                $oDataSet->execute();
                $oDataSet->closeCursor();
                $_t = $this->TransactionCommit();
                $result = true;
            }else{
                $this->_Log(array(
                    'method' => 'Truncate'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
                $_t = $this->TransactionRollback();
                $this->_Log(array(
                    'method' => 'Truncate'
                    , 'severity' => 'error'
                    , 'text' => '' . $oException
                ));
        }
    }
    
    /**
     * Checks to see if a fields name is in the list of columns
     * @param (string)$Name: name of column
     * @example $DAOGeneric->IsColumn( $Name );
     * @return (boolean)result, default: false
     */
    public function IsColumn( $Name ){
            $result = false;
            try{
                if($this->_Ready){
                    if( isset($Name) && !is_null($Name) ){
                        foreach($this->_oDTO->Columns as $dto_key => $dto_value){
                            // check field name matches
                            if($Name === $dto_key){
                                    $result = true;
                                    break;
                            }
                        }
                    }else{
                        $this->_Log(array(
                            'method' => 'IsColumn'
                            , 'severity' => 'error'
                            , 'text' => 'FieldName is Null or not a value'
                        ));
                    }
                }else{
                    $this->_Log(array(
                        'method' => 'IsColumn'
                        , 'severity' => 'warning'
                        , 'text' => 'DAOGeneric is not yet configured.'
                    ));
                }
            } catch (Exception $oException) {
                    $this->_Log(array(
                        'method' => 'IsColumn'
                        , 'severity' => 'error'
                        , 'text' => '' . $oException
                    ));
            }	
            return $result;
    }

    /**
     * Attempts to infer PDO Param data type based on value
     * @param (mixed)$Value
     * @example $DAOGeneric->InferPDOParam($Value);
     * @return (string)PDO:PARAM, default: 'PDO::PARAM_STR'
     */
    public function InferPDOParam( $Value ){
        $result = '';
        try{
            if(is_null($Value)){
                $result = 'PDO::PARAM_NULL';
            }else if(is_bool($Value)){
                $result = 'PDO::PARAM_BOOL';
            }else if(is_int($Value)){
                $result = 'PDO::PARAM_INT';
            }else{
                $result = 'PDO::PARAM_STR';
            }
        } catch (Exception $oException) {
                $this->_Log(array(
                    'method' => 'InferPDOParam'
                    , 'severity' => 'error'
                    , 'text' => '' . $oException
                ));
        }	
        return $result;
    }
    
    /**
     * Evaluates the pdo type the function call gets the PDO::PARAM_*
     * of the matching type. if unknown assumes string
     * @param (string)$PDO_Type
     * @example $DAOGeneric->PDO_PARAM_Get($Value);
     * @return (string)PDO:PARAM, default: 'PDO::PARAM_STR'
     * 
     */
    public function PDO_PARAM_Get($PDO_Type) {
        $result;
        try{
            switch($PDO_Type){
                case 'PDO::PARAM_BOOL': $result = PDO::PARAM_BOOL;
                        break;
                case 'PDO::PARAM_NULL': $result = PDO::PARAM_NULL;
                        break;
                case 'PDO::PARAM_INT': $result = PDO::PARAM_INT;
                        break;
                case 'PDO::PARAM_LOB': $result = PDO::PARAM_LOB;
                        break;
                case 'PDO::PARAM_STR': $result = PDO::PARAM_STR;
                        break;
                default: $result = PDO::PARAM_STR;
                        break;
            }
        }catch(Exception $oException){
            $this->_Log(array(
                'method' => 'PDO_PARAM_Get'
                , 'severity' => 'error'
                , 'text' => '' . $oException
            ));
        }
        return $result;
    }
    // ---------------------------------------------------------------
    //                     CLASS MECHANICS
    // ---------------------------------------------------------------

    /**
     * Class Constructor
     * @param none
     * @example $X = new DAOGeneric();
     * @return array result
     */
    function DAOGeneric() {
        try {
            $arguments = func_get_args();
            if (isset($arguments) && count($arguments) > 0) {
                $this->_OptionsSet($arguments[0]);
            }
        
            if( isset($this->_oDTO) ){
                $this->_TableName = $this->_oDTO->Table;
                $this->_DatabaseName = $this->_oDTO->Database;
                $this->_Interface = $this->InterfaceGet();
                $this->_Ready = $this->_IsReady();
                
            }else{
                $this->_Log(array(
                    'method' => '__constructor'
                    , 'severity' => 'warning'
                    , 'text' => 'DAOGeneric is not yet configured.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => '__constructor'
                , 'severity' => 'error'
                , 'text' => 'DatabaseObject not supplied in the argument array: ' . $oException
            ));
        }
    }

    /**
     * A reusable variable setting method that sets class options.
     * @param (array) = ( 'verbose' => true );
     * @example $this->_OptionsSet( array( 'verbose' => true ) );
     * @return void
     */
    private function _OptionsSet() {
        try {
            $arguments = func_get_args();
        
            // _Verbose
            if( array_key_exists('verbose', $arguments[0]) && isset($arguments[0]['verbose']) ){
                    $this->_Verbose = (boolean) $arguments[0]['verbose'];
            } else if(array_key_exists('debug', $arguments[0]) && isset($arguments[0]['debug']) ){
                    $this->_Verbose = (boolean) $arguments[0]['debug'];
            }else{}

            // _oDatabase
            if( array_key_exists('DatabaseObject', $arguments[0]) && isset($arguments[0]['DatabaseObject']) ){
                    $this->_oDatabase = $arguments[0]['DatabaseObject'];
            } else if( array_key_exists('database', $arguments[0]) && isset($arguments[0]['database'])  ){
                    $this->_oDatabase = $arguments[0]['database'];
            } else if( array_key_exists('db', $arguments[0]) && isset($arguments[0]['db'])  ){
                    $this->_oDatabase = $arguments[0]['db'];
            }else{}
            
            // _oDTO
            if( array_key_exists('dto', $arguments[0]) && isset($arguments[0]['dto']) ){
                    $this->_oDTO = $arguments[0]['dto'];
            } else if(array_key_exists('datatransferobject', $arguments[0]) && isset($arguments[0]['datatransferobject']) ){
                    $this->_oDTO = $arguments[0]['datatransferobject'];
            }else{}         

            // _UseAcid
            if (array_key_exists('acid', $arguments[0]) && isset($arguments[0]['acid'])) {
                $this->_UseAcid = (boolean) $arguments[0]['acid'];
            } else if (array_key_exists('use_acid', $arguments[0]) && isset($arguments[0]['use_acid'])) {
                $this->_UseAcid = (boolean) $arguments[0]['use_acid'];
            } else {
            }
            
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => '_OptionsSet'
                , 'severity' => 'error'
                , 'text' => $oException
            ));
        }
    }

    /**
     * A reusable flyweight variable setting method that sets class options.
     * @param (array) = ( 'verbose' => true );
     * @example $DAOGeneric->OptionsSet( array( 'verbose' => true ) );
     * @return void
     */
    public function OptionsSet() {
        try {
            $arguments = func_get_args();
            if (isset($arguments) && count($arguments) > 0) {
                $this->_OptionsSet($arguments[0]);
            }            
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => '_OptionsSet'
                , 'severity' => 'error'
                , 'text' => $oException
            ));
        }
    }

    /**
     * Used for managing logging
     * @param (array)LogInformation
     * @example $this->_Log(array(
     *       'method' => '_Log'
     *      ,'severity' => 'error'
     *      ,'test' => '' . $oException
     * ));
     * @return array result
     */
    private function _Log(){
        $arguments = func_get_args();
        try {
            if ($this->_Verbose && count($arguments) > 0) {
                if( isset( $this->_oLog ) ){
                    $this->_oLog->Add( $arguments[0] );
                }else{
                    $content = '';
                    $arguments[0]['class'] = 'DAOGeneric';
                    foreach ($arguments[0] as $key => $value){
                        $content .= "\n\t<li>" . $key. ': ' . $value . '</li>';
                    }
                    $content = "\n<ul>" . $content . "\n</ul>\n";
                    echo $content;
                }
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => '_Log'
                ,'severity' => 'error'
                ,'test' => '' . $oException
            ));
        }
    }

    /**
     * Checks to see if the class is ready to use
     * @example $this->_IsReady();
     * @return (boolean)result
     */
    private function _IsReady(){
            $result = false;
            try{
                if( isset( $this->_oDatabase )
                        && $this->_oDatabase->IsConnected()
                        && isset( $this->_oDTO )
                        && isset( $this->_Interface )
                        && isset( $this->_TableName )
                        && isset( $this->_DatabaseName )
                ){
                    $result = true;            
                }
            } catch (Exception $oException) {
                $this->_Log(array(
                    'method' => '_IsReady'
                    , 'severity' => 'error'
                    , 'text' => '' . $oException
                ));
            }	
            return $result;
    }    
   
}
?>