<?php
    //error_reporting( 0 ) ;

    const acAsl_OPTION_FILENAME = "core/option/option.php" ;
    const acAsl_SQL_FILENAME = "core/struct.sql" ;
    const acAsl_VIEW_PATH = "core/view/" ;
    const acAsl_PATH = "/acAsl" ;
    const acAsl_DEBUG = true ;
    const acAsl_PAGE_SIZE = 10 ;

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
            $this->arguments = explode( '/', $_SERVER[ "QUERY_STRING" ] ) ;
            echo json_encode( $this->arguments ) ;
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
        public function tags() {
            return $this->query( "SELECT * FROM `tag` ;" ) ;
        }
        public function tag_exist( $id ) {
            $id = settype( $id , "integer" ) ? $id : 0 ;
            $res = $this->query( "SELECT COUNT(*) AS CNT FROM `tag` WHERE `id`='$id' ;" ) ;
            $row = $res->fetch_array() ;
            return $row[ "CNT" ] > 0 ;
        }
        public function tag( $id , $name = false  ) {
            $id = settype( $id , "integer" ) ? $id : 0 ;
            if ( gettype( $name ) == "string" ) {
                $name = $this->real_escape_string( $name ) ;
                $this->query( "UPDATE `tag` SET `name`='$name' WHERE `id`='$id' ;" ) ;
            }
            $res = $this->query( "SELECT * FROM `tag` WHERE `id`='$id' ;" ) ;
            return $res->fetch_array() ;
        }
        public function posts( $page ) {
            $cnt = $this->posts_cnt() ;
            if ( !settype( $page , "integer" ) || $page < 0 || $page * acAsl_PAGE_SIZE >= $cnt ) {
                return false ;
            }
            return array(
                "result" => $this->query( "SELECT * FROM `post` LIMIT " . ( $page * acAsl_PAGE_SIZE ) . "," . acAsl_PAGE_SIZE . " ; " ) ,
                "prev" => ( $page > 0 ) ? ( $page - 1 ) : false,
                "next" => ( ( $page + 1 ) * acAsl_PAGE_SIZE < $cnt ) ? ( $page + 1 ) : false 
            ) ;
        }
        public function posts_cnt() {
            $res = $this->query( "SELECT COUNT(*) AS CNT FROM `post` ; " ) ;
            $row = $res->fetch_array() ;
            return $row[ "CNT" ] ;
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
                            break ;
                        case "p" :
                            $this->post() ;
                            break ;
                        case "login" :
                            $this->login() ;
                            break ;
                        case "admin" :
                            $this->admin() ;
                            break ;
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
            echo "tag" ;
        }
        public function post() {

        }
        public function login() {
            if ( $this->acAsl_PostGet->isPost() ) {
                $this->acAsl_Session->is_admin = $this->acAsl_PostGet->admin === $this->acAsl_Option->admin ;
            }
            if ( $this->acAsl_Session->is_admin ) {
                header( "location:" . acAsl_PATH . "/?admin" ) ;
            }
            new acAsl_View( "login" , $this->acAsl_PostGet->isPost() ? "wrong!" : false ) ;
        }
        public function admin() {
            if ( !$this->acAsl_Session->is_admin ) {
                header( "location" . acAsl_PATH . "/?login" ) ;
            }
            switch( $this->acAsl_Argument->get( 1 ) ) {
                case "tags" :
                    new acAsl_View( "admin_tags" , $this->acAsl_Model->tags() ) ;
                    break ;
                case "tag" :
                    if ( $this->acAsl_Model->tag_exist( $this->acAsl_Argument->get(2) ) ) {
                        new acAsl_View( "admin_tag", $this->acAsl_Model->tag( $this->acAsl_Argument->get(2) , $this->acAsl_PostGet->name ) ) ;
                    } else {
                        new acAsl_View( "404" ) ;
                    }
                    break ;
                case "posts" :
                    ( $tmp = $this->acAsl_Model->posts( $this->acAsl_Argument->get( 2 ) ) ) ? ( new acAsl_View( "admin_posts" , $tmp ) ) : (new acAsl_View( "404" ) ) ;
                case "post" :
                    $this->admin_post() ;
                    break ;
                default :
                    $this->admin_index() ;
            } 
        }
        public function admin_tags() {
            
        }
        public function admin_post() {

        }
        public function admin_index() {

        }
    }

    new acAsl_Controller() ;

