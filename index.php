<?php
    /*
        OpenSource Software , designed by skygr
        Homepage : skygr.org
    */
    /*
        we should close all warnings or errors in working state
    */
    //error_reporting( 0 ) ;
    /*
        the filename of option file
        when acAsl is installing , this program will write the option file
        when acAsl is running , it will check if the option file exist

        to successfully install acAsl , we should be sure that the folder of option-file exists and 
        this program has power to write option-file
    */
    const acAsl_OPTION_FILENAME = "core/option/option.php" ;
    /*
        the filename of sql-file
        when acAsl is installing , this program will load this file to build database structure
        before installing , we should be sure that this file exists and the program could read it
        after installing , you can delete it at any time
    */
    const acAsl_SQL_FILENAME = "core/struct.sql" ;
    /*
        the folder name of all views
        it is used in any time
    */
    const acAsl_VIEW_PATH = "core/view/" ;
    /*
        the path of acAsl
        hint : it is the path into browser's address bar
    */
    const acAsl_PATH = "/acAsl" ;
    /*
        the symbol of debuging
        once this variable is true
            this program will write exception info into all HTTP-500 pages
    */
    const acAsl_DEBUG = true ;
    /*
        a global variable to be defined as the page-size of all querys which are related to paging
    */
    const acAsl_PAGE_SIZE = 10 ;

    /*
        this class is designed to load and save global options from option-file
    */
    class acAsl_Option {
        /*
            it seems easy to understand the function of this variable , isn't it -_-
        */
        private $option_variables ;
        /*
            contruct
            load from option-file
            if the return type of the option-file is not array 
            the storage will be empty in runtime
        */
        function __construct() {
            /*
                HIT :: if there is any syntax error in option-file ,
                the program will be stop
            */
            $this->option_variables = ( @include acAsl_OPTION_FILENAME ) ;
            if ( gettype( $this->option_variables ) != "array" ) {
                $this->option_variables = array() ;
            }
        }
        /*
            magic function
            get option-variable by its name
            if the option-variable is not set or the option-variable equals to false ,
            it returns false
        */
        public function __get( $name ) {
            return isset( $this->option_variables[ $name ] ) ? $this->option_variables[ $name ] : false ;
        }
        /*
            magic function
            set option-variable by its name
            and return it
        */
        public function __set( $name , $value ) {
            return $this->option_variables[ $name ] = $value ;
        }
        /*
            check if all option-variables of which the name is in $names exist
        */
        public function exist( $names ) {
            for( $i = 0 ; $i < count( $names ) ; $i++ ) {
                if( !isset( $this->option_variables[ $names[ $i ] ] ) ) {
                    return false ;
                }
            }
            return true ;
        }
        /*
            save all options into option-file
            return a boolean variable wheather this event is success
            HIT :: if the program cannot write option-file , it will return false
        */
        public function save() {
            $save_string  = "" ;
            foreach( $this->option_variables as $name => $value ) {
                $save_string .= $save_string == '' ? "'$name'=>'$value'" : ",'$name'=>'$value'" ;
            }
            $save_string = "<?php\nreturn array(" . $save_string . ");" ;
            return @file_put_contents( acAsl_OPTION_FILENAME , $save_string ) ;
        }
    }
    /*
        this class is used to deal with the Sesion state
        it uses php session simply now
        We can overwrite it in the future to obtain higher security
    */
    class acAsl_Session {
        function __construct() {
            session_start() ;
        }
        public function __set( $name , $value ) {
            return $_SESSION[ $name ] = $value ;
        }
        public function __get( $name ) {
            return isset( $_SESSION[ $name ] ) ? $_SESSION[ $name ] : false ;
        }
        public function clear() {
            session_destroy();
        }
    }
    /*
        this class provides a unified inteface to obtain HTTP-GET or HTTP-POST parameters
        for more security , we can construct a filter layer in this class to
        prevent XSS
    */
    class acAsl_PostGet {
        public function isPost() {
            return $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ;
        }
        public function __get( $name ) {
            return isset( $_POST[ $name ] ) ? $_POST[ $name ] : ( isset( $_GET[ $name ] ) ? $_GET[ $name ] : false ) ;
        }
    }
    /*
        load a external php file in acAsl_VIEW_PATH
        to build HTML Content
        and you can carry arguments to the view layer
        HIT :: if there is any syntax error , this program will stop here
        after excute the external php file 
        this program will die
    */
    class acAsl_View {
        private $arguments ;
        function __construct( $view , $arguments = false ) {
            $this->arguments = $arguments ;
            @include acAsl_VIEW_PATH . $view . ".php" ;
            die() ;
        }
    }
    /*
        model layer based on Mysqli
        this class is designed to deal with any interactions with database
        this class is used to have a better logical absraction
        and prevent any SQL-Injections
    */
    class acAsl_Model extends Mysqli {
        /*
            construct mysqli handle
            throw a exception when it could not connect to database 
        */
        function __construct( $host, $user, $db, $pswd ) {
            parent :: __construct( $host, $user, $db, $pswd ) ;
            if ( $this->connect_error ) {
                throw new Exception( "unable to connect mysql server!" ) ;
            }
        }
        /*
            mysqli query
            overwrite it from parent class to throw
            an exception once there is something wrong
        */
        public function query( $query_string ) {
            $res = parent :: query( $query_string ) ;
            if( $this->error ) {
                throw new Exception( "error when Mysql query!" ) ;
            }
            return $res ;
        }
        /*
            query more than one statement
            overwrite this function from parent class to throw
            an exception once there is something wrong
        */
        public function multi_query( $query_string ) {
            $res = parent :: multi_query( $query_string ) ;
            if( $this->error ) {
                throw new Exception( "error when Mysql query!" ) ;
            }
            return $res ;
        }
        /*
            get all tags from database
        */
        public function tags() {
            return $this->query( "SELECT * FROM `tag` ;" ) ;
        }
        /*
            check if there is a tag of which the id is $id
            hint:
                this is a private function
                and you must be sure that the type of $id is integer
        */
        private function tag_exist( $id ) {
            $res = $this->query( "SELECT COUNT(*) AS CNT FROM `tag` WHERE `id`='$id' ;" ) ;
            $row = $res->fetch_array() ;
            return $row[ "CNT" ] > 0 ;
        }
        /*
            check if there is a post of which the id is $id
            hint:
                this is a private function
                and you must be sure that the type of $id is integer
        */
        private function post_exist( $id ) {
            $res = $this->query( "SELECT COUNT(*) AS CNT FROM `post` WHERE `id`='$id' ;" ) ;
            $row = $res->fetch_array() ;
            return $row[ "CNT" ] > 0 ;
        }
        /*
            get a row of tag of which the id is $id
            when the type of $id is not 'integer' or the tag we that we found does not exists
            just return false 
        */
        public function tag( $id , $name = false ) {
            if ( !settype( $id , "integer") || !$this->tag_exist( $id ) ) {
                return false ;
            }
            if ( gettype( $name ) == "string" ) {
                $name = $this->real_escape_string( $name ) ;
                $this->query( "UPDATE `tag` SET `name`='$name' WHERE `id`='$id' ;" ) ;
            }
            $res = $this->query( "SELECT * FROM `tag` WHERE `id`='$id' ;" ) ;
            return $res->fetch_array() ;
        }
        /*
            get serveral posts from databse of which the order number is 
            bigger than or equal to (>=) $page*acAsl_PAGE_SIZE and 
            smaller than (<) ($page+1)*acAsl_PAGE_SIZE
            the retrun value is a key-value array :
                result : mysqli-result ,
                prev : if there is a previous page , it is the previous page-ord , or it is false
                next : if there is a next page , it is the next page-ord , or it is false      
        */
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
        /*
            get the count of all posts
            it seems to be used only in other public function in acAsl_Model ,
            but it is security , so i set it to public
        */
        public function posts_cnt() {
            $res = $this->query( "SELECT COUNT(*) AS CNT FROM `post` ; " ) ;
            $row = $res->fetch_array() ;
            return $row[ "CNT" ] ;
        }
        public function post( $id , $title = false , $content = false ) {

        }
    }
    /*
        this is the adheisive layer to manage option & session & postget & model
        every function in this class corresponds a logical function to users
    */
    class acAsl_Controller {
        private $acAsl_Option ;
        private $acAsl_Session ;
        private $acAsl_PostGet ;
        private $acAsl_Model ;
        function __construct() {
            $this->acAsl_Session = new acAsl_Session() ;
            $this->acAsl_PostGet = new acAsl_PostGet() ;
            $this->acAsl_Option = new acAsl_Option() ;
            if ( $this->acAsl_Option->exist( [ "host", "user", "db", "pswd", "admin" ] ) ) {
                try {
                    $this->acAsl_Model = new acAsl_Model( $this->acAsl_Option->host , $this->acAsl_Option->user , $this->acAsl_Option->pswd , $this->acAsl_Option->db ) ;
                } catch ( Exception $err ) {
                    new acAsl_View( "500" , acAsl_DEBUG ? $err->getMessage() : false ) ;
                }

            } else {
                $this->install() ;
            }
        }
        private function redirect( $method = "" ) {
            header( "location:" . acAsl_PATH . "/?" . $method ) ;
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
            echo "post" ;
        }
        public function login() {
            if ( $this->acAsl_PostGet->isPost() ) {
                $this->acAsl_Session->is_admin = $this->acAsl_PostGet->admin === $this->acAsl_Option->admin ;
            }
            if ( $this->acAsl_Session->is_admin ) {
                $this->redirect( "admin" ) ;
            }
            new acAsl_View( "login" , $this->acAsl_PostGet->isPost() ? "wrong!" : false ) ;
        }
        public function logout() {
            $this->acAsl_Session->clear() ;
            $this->redirect( "index" ) ;
        }
        public function admin() {
            if ( !$this->acAsl_Session->is_admin ) {
                header( "location" . acAsl_PATH . "/?login" ) ;
            }
            echo "admin.." ;
        }
        public function admin_tags() {
            new acAsl_View( "admin_tags" , $this->acAsl_Model->tags() ) ;
        }
        public function admin_tag( $id ) {
            ( $tmp = $this->acAsl_Model->tag( $id , $this->acAsl_PostGet->name ) )
                ? ( new acAsl_View( "admin_tag" , $tmp ) )
                : ( new acAsl_View( "404" , $tmp ) ) ;
        }
        public function admin_posts( $page = 0 ) {
            ( $tmp = $this->acAsl_Model->posts( $page ) )
                ? ( new acAsl_View( "admin_posts" , $tmp ) )
                : ( new acAsl_View( "404" ) ) ;
        }
        public function admin_post( $id = false ) {

        }
        public function admin_index() {

        }
    }
    /*
        this is class is used to analysis of the path, 
        and mapped to the corresponding controller
    */
    class acAsl_Router {
        private $arguments ;
        function __construct() {
            $this->arguments = explode( '/', $_SERVER[ "QUERY_STRING" ] ) ;
            echo json_encode( $this->arguments ) ;
        }
        function select( $route ) {
            $arguments = $this->arguments ;
            while ( !is_callable( $route ) ) {
                if ( count( $arguments ) > 0 && isset( $route[ $arguments[ 0 ] ] ) ) {
                    $route = $route[ $arguments[ 0 ] ] ;
                    array_shift( $arguments ) ;
                } else {
                    if ( isset( $route[ "" ] ) && is_callable( $route[ "" ] ) ) {
                        call_user_func( $route[ "" ] ) ;
                    } else {
                        new acAsl_View( "404" ) ;    
                    }
                    return ;
                }
            }
            call_user_func_array( $route , $arguments ) ;
        }
    }
    /*
        main class , load router and controller
        config the mapped relation into route-class and call it 
    */
    class acAsl_Main {
        private $acAsl_Router ;
        private $acAsl_Controller ;
        function __construct() {
            $this->acAsl_Router = new acAsl_Router() ;
            $this->acAsl_Controller = new acAsl_Controller() ;
        }
        function run() {
            $this->acAsl_Router->select(
                array(
                    "" => array( $this->acAsl_Controller , "index" ) ,
                    "index" => array( $this->acAsl_Controller , "index" ) ,
                    "tag" => array( $this->acAsl_Controller ,"tag" ) ,
                    "post" => array( $this->acAsl_Controller , "post" ) ,
                    "login" => array( $this->acAsl_Controller , "login" ) ,
                    "logout" => array( $this->acAsl_Controller , "logout" ) ,
                    "admin" => array(
                        "" => array( $this->acAsl_Controller , "admin" ) ,
                        "tags" => array( $this->acAsl_Controller , "admin_tags" ) ,
                        "tag" => array( $this->acAsl_Controller , "admin_tag" ) ,
                        "posts" => array( $this->acAsl_Controller , "admin_posts" )
                    )
                )
            ) ;
        }
    }
    /*
        handle ^_-
    */
    $acAsl_Handle = new acAsl_Main() ;
    $acAsl_Handle->run() ;

