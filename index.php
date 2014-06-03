<?php
    //error_reporting( 0 ) ;

    const acAsl_OPTION_FILENAME = "core/option/option.php" ;
    const acAsl_SQL_FILENAME = "core/struct.sql" ;
    const acAsl_VIEW_PATH = "core/view/" ;
    const acAsl_PATH = "/acAsl" ;
    const acAsl_DEBUG = true ;

    class acAsl_Option {
        private $option_variables ;
        function __construct() {
            $this->option_variables = ( @include acAsl_OPTION_FILENAME ) ;
            if ( gettype( $this->option_variables ) != "array" ) {
                $this->option_variables = array() ;
            }
        }
        public function __get( $name ) {
            return isset( $this->option_variables[ $name ] ) ? $this->option_variables[ $name ] : false ;
        }
        public function __set( $name , $value ) {
            return $this->option_variables[ $name ] = $value ;
        }
        public function exist( $names ) {
            for( $i = 0 ; $i < count( $names ) ; $i++ ) {
                if( !isset( $this->option_variables[ $names[ $i ] ] ) ) {
                    return false ;
                }
            }
            return true ;
        }
        public function save() {
            $save_string  = "" ;
            foreach( $this->option_variables as $name => $value ) {
                $save_string .= $save_string == '' ? "'$name'=>'$value'" : ",'$name'=>'$value'" ;
            }
            $save_string = "<?php\nreturn array(" . $save_string . ");" ;
            return @file_put_contents( acAsl_OPTION_FILENAME , $save_string ) ;
        }
    }

    class acAsl_Argument {
        private $arguments ;
        function __construct() {
            $this->arguments = strpos( $_SERVER[ 'REQUEST_URI' ] , $_SERVER[ 'SCRIPT_NAME' ] ) === false ? array() : explode( '/', str_replace( $_SERVER[ 'SCRIPT_NAME' ], '', $_SERVER[ 'REQUEST_URI' ] ) ) ;
            array_shift( $this->arguments );
        }
        function get( $th ) {
            return isset( $this->arguments[ $th ] ) ? $this->arguments[ $th ] : false ;
        }
    }

    class acAsl_Session {
        function __construct() {
            session_start() ;
        }
        public function __set( $name , $value ) {
            $_SESSION[ $name ] = $value ;
        }
        public function __get( $name ) {
            return isset( $_SESSION[ $name ] ) ? $_SESSION[ $name ] : false ;
        }
        public function clear() {
            session_destroy();
        }
    }

    class acAsl_PostGet {
        public function isPost() {
            return $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ;
        }
        public function __get( $name ) {
            return isset( $_POST[ $name ] ) ? $_POST[ $name ] : ( isset( $_GET[ $name ] ) ? $_GET[ $name ] : false ) ;
        }
    }

    class acAsl_View {
        private $arguments ;
        function __construct( $view , $arguments = false ) {
            $this->arguments = $arguments ;
            return ( @include acAsl_VIEW_PATH . $view . ".php" ) ;
        }
    }

    class acAsl_Model extends Mysqli {
        function __construct( $host, $user, $db, $pswd ) {
            parent :: __construct( $host, $user, $db, $pswd ) ;
            if ( $this->connect_error ) {
                throw new Exception( "unable to connect mysql server!" ) ;
            }
        }
        public function query( $query_string ) {
            $res = parent :: query( $query_string ) ;
            if( $this->error ) {
                throw new Exception( "error when Mysql query!" ) ;
            }
            return $res ;
        }
        public function multi_query( $query_string ) {
            $res = parent :: multi_query( $query_string ) ;
            if( $this->error ) {
                throw new Exception( "error when Mysql query!" ) ;
            }
            return $res ;
        }

    }

    class acAsl_Controller {
        private $acAsl_Option ;
        private $acAsl_Argument ;
        private $acAsl_Session ;
        private $acAsl_PostGet ;
        private $acAsl_Model ;
        function __construct() {
            $this->acAsl_Argument = new acAsl_Argument() ;
            $this->acAsl_Session = new acAsl_Session() ;
            $this->acAsl_PostGet = new acAsl_PostGet() ;
            $this->acAsl_Option = new acAsl_Option() ;
            if ( $this->acAsl_Option->exist( [ "host", "user", "db", "pswd", "admin" ] ) ) {
                try {
                    $this->acAsl_Model = new acAsl_Model( $this->acAsl_Option->host , $this->acAsl_Option->user , $this->acAsl_Option->pswd , $this->acAsl_Option->db ) ;
                    switch( $this->acAsl_Argument->get( 0 ) ) {
                        case "" :
                        case "i" :
                            $this->index() ;
                            break ;
                        case "t" :
                            $this->tag() ;
                        case "p" :
                            $this->post() ;
                        default :
                            new acAsl_View( "404" ) ;

                    }
                } catch ( Exception $err ) {
                    new acAsl_View( "500" , acAsl_DEBUG ? $err->getMessage() : false ) ;
                }
            } else {
                $this->install() ;
            }
        }
        public function install() {
            if ( $this->acAsl_PostGet->isPost() ) {
                try {
                    $this->acAsl_Model = new acAsl_Model( $this->acAsl_PostGet->host , $this->acAsl_PostGet->user , $this->acAsl_PostGet->pswd , $this->acAsl_PostGet->db ) ;
                    $build_sql = @file_get_contents( acAsl_SQL_FILENAME ) ;
                    if( !$build_sql ) {
                        throw Exception( "unable to load " . acAsl_SQL_FILENAME ) ;
                    }
                    $this->acAsl_Model->multi_query( $build_sql ) ;
                    $this->acAsl_Option->host = $this->acAsl_PostGet->host ;
                    $this->acAsl_Option->user = $this->acAsl_PostGet->user ;
                    $this->acAsl_Option->pswd = $this->acAsl_PostGet->pswd ;
                    $this->acAsl_Option->db = $this->acAsl_PostGet->db ;
                    $this->acAsl_Option->admin = $this->acAsl_PostGet->admin ;
                    if ( !$this->acAsl_Option->save() ) {
                        throw new Exception( "unable to write " . acAsl_OPTION_FILENAME ) ;
                    }
                    new acAsl_View( "install" , array( "success" => true ) ) ;
                } catch ( Exception $err ) {
                    new acAsl_View( "install" , array( "success" => false , "message" => $err->getMessage() ) ) ;
                }
            } else {
                new acAsl_View( "install" , false ) ;
            }
        }
        public function index() {
            echo "index" ;
        }
        public function tag() {

        }
        public function post() {

        }
    }

    new acAsl_Controller() ;

