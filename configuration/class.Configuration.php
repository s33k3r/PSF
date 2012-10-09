<?php

class Configuration {
    /// ------------------------------------------------------------------------ 
    /// author: David P Smith
    /// copyright: http://creativecommons.org/licenses/by-sa/3.0/us/
    /// repository: https://github.com/s33k3r/PSF/
    /// version: 1.0
    /// description: This stores the web applications configuration.
    /// ------------------------------------------------------------------------    
    
    // ------------------------------------------------------------------------
    //                     CLASS PROCESSING VARIABLES
    // ------------------------------------------------------------------------
    const PROGRAM_NAME = 'DOMAIN';
    const PROGRAM_VERSION = '1.0';
    const FORM_METHOD = 'post';
    const FORM_ACTION = 'http://DOMAIN/';

    const DOMAIN = 'DOMAIN';
    const URL = 'http://DOMAIN/';
    const DIRECTORY_JS = 'js/';
    const DIRECTORY_CSS = 'css/';
    const DIRECTORY_IMG = 'img/';
    const DIRECTORY_PATH = '/';

    // LDAP Configuration
    const ACTIVE_DIRECTORY_SERVER = 'DOMAIN.corp';
    const ACTIVE_DIRECTORY_DOMAIN = 'DOMAIN';

    // SMTP Server
    const SMTP_SERVER = 'smtp.DOMAIN.com';
    const SMTP_PORT = '';
    const SMTP_USER = '';
    const SMTP_PASS = '';

    // ------------------------------------------------------------------------
    //                     CLASS METHODS
    // ------------------------------------------------------------------------
    
    /**
     * returns database connection strings
     * @example $Configuration->Databases();
     * @return array result = new Database(array(
     *      'db'   => $Connectors['Database']['table']
     *          , 'host' => $Connectors['Database']['host']
     *          , 'user' => $Connectors['Database']['user']
     *          , 'pass' => $Connectors['Database']['pass']
     *          , 'type' => $Connectors['Database']['type']
     *          , 'verbose' => true
     *      ));
     */
    function Databases(){
        return array(
            'DatabaseAlias' => array(
                // Primary Database Configuration
                'name' => 'DatabaseName',
                'host' => 'ServerName',
                'user' => 'DatabaseUserName',
                'pass' => 'DatabasePassword',
                'type' => 'mysql',
            )
            ,'DatabaseAlias2' => array(
                // Primary Database Configuration
                'name' => 'DatabaseName',
                'host' => 'ServerName',
                'user' => 'DatabaseUserName',
                'pass' => 'DatabasePassword',
                'type' => 'mssql',
            )
        );
    }

    // ---------------------------------------------------------------
    //                     CLASS MECHANICS
    // ---------------------------------------------------------------
    
    /**
     * Class Constructor
     * @example $oConfiguration = new Configuration();
     * @example $oConfiguration = new Configuration(array('verbose' => true));
     * @return (object)instance
     */
    function Configuration(){
        $arguments = func_get_args();
        if( isset( $arguments ) && count($arguments) > 0 ){
            $this->_OptionsSet( $arguments[0] );
        }
    }

    /**
     * A reusable variable setting method that sets class options.
     * @param (array) = ( 'verbose' => true );
     * @example $this->_OptionsSet( array( 'verbose' => true ) );
     * @return array result
     */
    private function _OptionsSet() {
        $arguments = func_get_args();
        try {
            // _Verbose
            if( array_key_exists('verbose', $arguments[0]) && isset($arguments[0]['verbose']) ){
                $this->_Verbose = (boolean) $arguments[0]['verbose'];
            } else if(array_key_exists('debug', $arguments[0]) && isset($arguments[0]['debug']) ){
                $this->_Verbose = (boolean) $arguments[0]['debug'];
            }else{}
            
        } catch (Exception $Exception) {
            $this->_Log(array(
                'method' => '_OptionsSet'
                ,'severity' => 'error'
                ,'test' => '' . $oException
            ));
        }
    }
    
    /**
     * Used for managing logging
     * @param none
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
                    $arguments[0]['class'] = 'Configuration';
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
    
}
?>