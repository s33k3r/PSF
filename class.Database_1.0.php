<?php
class Database {
    /// ------------------------------------------------------------------------    
    /// author: David P Smith
    /// version: 1.0
    /// copyright http://creativecommons.org/licenses/by-sa/3.0/us/
    /// description: Database Class that Simplifies Database Connectivity
    /// ------------------------------------------------------------------------    
    // ------------------------------------------------------------------------
    //                     CLASS CONFIGURATION
    // ------------------------------------------------------------------------
    private $_Verbose = false;
    private $_oLog; // log object
    // ------------------------------------------------------------------------
    //                     CLASS PROCESSING VARIABLES
    // ------------------------------------------------------------------------
    private $_DataSanitize;
    private $_DataValidation;    
    private $_Session;
    private $_IsConnected = false;
    private $_Database = '';
    private $_Server = '';
    private $_Username = '';
    private $_Password = '';
    private $_Type = '';
    private $_Port = '';
    private $_Type_Default = 'mysql';
    private $_Database_Current = '';
    private $_Server_Current = '';
    private $_Port_Current = '';
    private $_Port_Default = '';
    private $_Driver = '';

    // ------------------------------------------------------------------------
    //                     CLASS METHODS
    // ------------------------------------------------------------------------
    public function Connect(){
        ///	description: connects to database
        ///	arguments:	 See _OptionsSet()
        ///	returns: 	 Optional array _OptionsSet

        try{
            $arguments = func_get_args(); // standard options parsing construction
            if( isset( $arguments ) && count($arguments) > 0 ){
                    $this->_OptionsSet( $arguments[0] );
            }

            $session;
            $database_driver;
        
            if( !($this->_Session) ){
                if( $this->_IsReady() ){

                    $this->_Load_Driver();

                    $_dbh = $this->_Driver
                            . $this->_Server_Current
                            . $this->_Port_Current
                            . $this->_Database_Current;

                    $session = new PDO(
                            $_dbh
                            , $this->_Username
                            , $this->_Password
                            , array( PDO::ATTR_PERSISTENT => false )
                    );

                    if( !($session) ){
                        $this->_Log(array(
                            'method' => 'Connect'
                            , 'severity' => 'error'
                            , 'test' => 'Could not create connection.'
                        ));
                        $this->_Session = null;
                    }else{
                        // success
                        $this->_Session = $session; 
                    }
                    
                }else{
                    $this->_Log(array(
                        'method' => 'Connect'
                        , 'severity' => 'error'
                        , 'test' => 'Incomplete Connection Credentials Supplied'
                    ));
                }
            }else{
                $this->_Log(array(
                    'method' => 'Connect'
                    , 'severity' => 'error'
                    , 'test' => 'Database already connected.'
                ));
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'Connect'
                , 'severity' => 'error'
                , 'test' => 'Error Disconnecting: ' . $oException
            ));
        }
    }

    public function Disconnect(){
            ///	description: disconnects from database
            ///	arguments:	 See _OptionsSet()
            ///	returns: 	 Optional array _OptionsSet

        try{

            if( $this->IsConnected() ){

                $this->_Session = null;
                $this->_IsConnected = false;

            }else{
                $this->_Log(array(
                    'method' => 'Disconnect'
                    , 'severity' => 'warning'
                    , 'test' => 'Unable to Disconnect; Not Connected to a Database.'
                ));
            }

        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'Disconnect'
                , 'severity' => 'error'
                , 'test' => 'Error Disconnecting: ' . $oException
            ));
        }
    }

    public function SessionGet(){
            ///	description: Session information
            ///	arguments:	 void
            ///	returns: 	 Null or Session

            return $this->_Session;
    }

    public function IsConnected(){
            ///	description: used to determine if the database is connected
            ///	arguments:	 void
            ///	returns: 	 boolean is session valid

            if( !($this->_Session) ){
                    $this->_IsConnected = false;
            }else{
                    $this->_IsConnected = true;
            }
            return ($this->_IsConnected);
    }

    public function DataSanitizeGet(){
            ///	description: Returns the global DataSanitizeGet Class
            ///	arguments:	 void
            ///	returns: 	 boolean is session valid

            return $this->_DataSanitize;
    }

    public function DataValidationGet(){
            ///	description: Returns the global DataValidation Class
            ///	arguments:	 void
            ///	returns: 	 boolean is session valid

            return $this->_DataValidation;
    }

    private function _IsReady(){
        ///	description: Checks to see if it is ready to connect
        ///	arguments:   void
        ///	returns:     boolean $IS_Ready
        
        $result = false;
        try {
            if(    $this->_Database != ''
                && $this->_Server   != ''
                && $this->_Username != ''
                && $this->_Password != ''
                && !$this->_IsConnected
            ){ 
                $result = true;
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => '_IsReady'
                , 'severity' => 'error'
                , 'test' => 'Error Disconnecting: ' . $oException
            ));
        }
        return $result;
    }
    
    private function _Load_Driver(){
        ///	description: Checks to see if it is ready to connect
        ///	arguments:   void
        ///	returns:     void
        
        try {
            
            switch( $this->_Type ){
                    case 'sqlite':
                        $this->_Driver = 'sqlite'; 
                        $this->_Port_Default = '5432';
                        break;
                    case 'ocacle':
                        $this->_Driver = 'oci'; 
                        $this->_Port_Default = '1521';
                        break;
                    case 'mssql':
                        $this->_Driver = 'mssql'; 
                        $this->_Port_Default = '1433';
                        break;
                    case 'mssql':
                        $this->_Driver = 'odbc'; 
                        $this->_Port_Default = '';
                        break;
                    default:
                        $this->_Driver = 'mysql'; 
                        $this->_Port_Default = '3306';
                        break;
            }
            
            if($this->_Port === ''){
                if( !is_null($this->_Port_Default)){
                    $this->_Port_Current = ';port=' . $this->_Port_Default;
                }else{
                     $this->_Port_Current = '';
                }
            }else{
                $this->_Port_Current = ';port=' . $this->_Port;
            }
            
            if( !is_null($this->_Server) ){
                $this->_Server_Current = ':host=' . $this->_Server;
            }else{
                $this->_Server_Current = '';
            }

            if( !is_null($this->_Database) ){
                if($this->_Driver === 'odbc'){
                    $this->_Database_Current = ':' . $this->_Database; 
                }else{
                    $this->_Database_Current = ';dbname=' . $this->_Database;  
                }
            }else{
                $this->_Database_Current = '';
            }

        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => '_Load_Driver'
                , 'severity' => 'error'
                , 'test' => 'Error Disconnecting: ' . $oException
            ));
        }
    }
    
    // ------------------------------------------------------------------------
    //                     CLASS MECHANICS (3.1)
    // ------------------------------------------------------------------------
    public function Database(){
            ///	description: Class Constructor
            ///	arguments:	 See _OptionsSet()
            ///	returns: 	 Optional array _OptionsSet

            $arguments = func_get_args(); // standard options parsing construction
            if( isset( $arguments ) && count($arguments) > 0 ){
                    $this->_OptionsSet( $arguments[0] );
            }
    }
    
    public function OptionSet() {
        /// description: Class options setter
        /// arguments:	 See _OptionsSet()
        /// returns: 	 Optional array _OptionsSet 

        try {
            $arguments = func_get_args(); // options parsing
            if (isset($arguments) && count($arguments) > 0) {
                $this->_OptionsSet($arguments[0]);
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'OptionSet'
                , 'severity' => 'error'
                , 'test' => '' . $oException
            ));
        }
    }

    public function OptionGet($VariableName) {
        /// description: Class options setter
        /// arguments:	 See _OptionsSet()
        /// returns: 	 Optional array _OptionsSet 

        $_name = trim($VariableName);
        $_value;
        try {
            
            // _Verbose
            if ($_name === 'verbose') {
                $_value = $this->$_Verbose;
            }
            
            // _DataSanitize;
            if( $_name === 'data_sanitize' ) {
                    $_value = $this->_DataSanitize;
            }

            // _DataValidation;
            if( $_name === 'data_validation' ) {
                    $_value = $this->_DataValidation; 
            }

            // _Database
            if( $_name === 'db' 
                || $_name === 'database' 
                || $_name === 'name' 
                || $_name === 'schema' 
            ){
                    $_value = $this->_Database;
            }

            // _Server
            if( $_name === 'host' 
                || $_name === 'server' 
            ){
                    $_value = $this->_Server;
            }

            // _Username
            if( $_name === 'user' 
                || $_name === 'username' 
                || $_name === 'usr' 
            ){
                    $_value = $this->_Username;
            } 

            // _Type
            if( $_name === 'type' ){
                    $_value = $this->_Type;
            } 
               
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => 'OptionGet'
                , 'severity' => 'error'
                , 'test' => '' . $oException
            ));
        }
        return $_value;
    }
    
    private function _OptionsSet() {
        ///	description: A reusable variable setting method that sets class options.
        ///	arguments: 	 optional boolean verbose: turns verbose mode on (true) or off (false),
        ///				 required string x: description
        ///	returns: 	 void

        try {
                $arguments = func_get_args();
            
                // _Verbose
                if( array_key_exists('verbose', $arguments[0]) && isset($arguments[0]['verbose']) )	{
                        $this->_Verbose = (boolean) $arguments[0]['verbose']; // casting forces datatype to boolean
                }
                
                // _oLog
                if( array_key_exists('log_object', $arguments[0]) && isset($arguments[0]['log_object']) )	{
                        $this->_oLog = $arguments[0]['log_object']; // casting forces datatype to boolean
                }

                // _DataSanitize;
                if( array_key_exists('data_sanitize', $arguments[0]) && isset($arguments[0]['data_sanitize']) )	{
                        $this->_DataSanitize = $arguments[0]['data_sanitize'];
                }

                // _DataValidation;
                if( array_key_exists('data_validation', $arguments[0]) && isset($arguments[0]['data_validation']) )	{
                        $this->_DataValidation = $arguments[0]['data_validation']; 
                }

                // _Database
                if( array_key_exists('db', $arguments[0]) && isset( $arguments[0]['db']   ) ){
                        $this->_Database = $arguments[0]['db'];
                } else if( array_key_exists('database', $arguments[0]) && isset( $arguments[0]['database']   ) ){
                        $this->_Database = $arguments[0]['database'];
                } else if( array_key_exists('name', $arguments[0]) && isset( $arguments[0]['name']   ) ){
                        $this->_Database = $arguments[0]['name'];
                } else if( array_key_exists('schema', $arguments[0]) && isset( $arguments[0]['schema']   ) ){
                        $this->_Database = $arguments[0]['schema'];
                } else { }

                // _Server
                if( array_key_exists('host', $arguments[0]) && isset( $arguments[0]['host']   ) ){
                        $this->_Server = $arguments[0]['host'];
                } else if( array_key_exists('server', $arguments[0]) && isset( $arguments[0]['server']   ) ){
                        $this->_Server = $arguments[0]['server'];
                } else { }


                if( array_key_exists('user', $arguments[0]) && isset( $arguments[0]['user']   ) ){
                        $this->_Username = $arguments[0]['user'];
                } else if( array_key_exists('username', $arguments[0]) && isset( $arguments[0]['username']   ) ){
                        $this->_Username = $arguments[0]['username'];
                } else if( array_key_exists('usr', $arguments[0]) && isset( $arguments[0]['usr']   ) ){
                        $this->_Username = $arguments[0]['usr'];
                } else { }

                // _Password
                if( array_key_exists('pass', $arguments[0]) && isset( $arguments[0]['pass']   ) ){
                        $this->_Password = $arguments[0]['pass'];
                } else if( array_key_exists('password', $arguments[0]) && isset( $arguments[0]['password']   ) ){
                        $this->_Password = $arguments[0]['password'];
                } else if( array_key_exists('pwd', $arguments[0]) && isset( $arguments[0]['pwd']   ) ){
                        $this->_Password = $arguments[0]['pwd'];
                } else { }

                // _Type
                if( array_key_exists('type', $arguments[0]) && isset( $arguments[0]['type']   ) ){
                    $_tmp_type = strtolower( $arguments[0]['type'] );
                    switch($_tmp_type){
                        case 'oracle'; break;
                        case 'mysql'; break;
                        case 'mssql'; break;
                        case 'sqlite'; break;
                        case 'odbc'; break;
                        default: $_tmp_type = $this->_Type_Default;  break;
                    }
                    $this->_Type = $_tmp_type;
                }
                
                // _Port
                if( array_key_exists('type', $arguments[0]) && isset( $arguments[0]['type']   ) ){
                        $this->_Port = strtolower( $arguments[0]['type'] );
                }

        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => '_OptionsSet'
                , 'severity' => 'error'
                , 'test' => '' . $oException
            ));
        }
    }

    private function _Log() {
        /// description: A reusable variable setting method for logging errors
        /// arguments: 	 void
        /// returns: 	 void

        $arguments = func_get_args();
        try {
            if ($this->_Verbose && count($arguments) > 0) {
                if (isset($this->_oLog)) {
                    $this->_oLog->Add($arguments[0]);
                } else {
                    $content = '';
                    $arguments[0]['class'] = 'Database';
                    foreach ($arguments[0] as $key => $value) {
                        $content .= "\n\t<li>" . $key . ': ' . $value . '</li>';
                    }
                    $content = "\n<ul>" . $content . "\n</ul>\n";
                    echo $content;
                }
            }
        } catch (Exception $oException) {
            $this->_Log(array(
                'method' => '_Log'
                , 'severity' => 'error'
                , 'test' => '' . $oException
            ));
        }
    }
    
}
?>