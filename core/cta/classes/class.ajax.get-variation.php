<?php

/**
 * This class pulls the database logon information out of wp-config.
 * It then evals the settings it finds into PHP and then makes the
 * database connection.
 *
 * Acts as a Singleton.
 *
 * @package WP_DB_Connect
 * @author Mark Flint
 * @link www.bluecubeinteractive.com
 * @copyright Please just leave this PHPDoc header in place.
 */
Class WP_DB_Connect {
	/**
	* @var object $_singleton This is either null in the case that this class has not been
	* called yet, or an instance of the class object if the class has been called.
	*
	* @access public
	*/
	private static $_singleton;

	/**
	* @var resource $_con The connection.
	* @access public
	*/
	public $_con;

	/**
	* The wp-config.php file var
	* @var string $str The string that the file is brought into.
	* @access private
	*/
	private $str;

	/**
	* @var $filePath Path to wp-config.php file
	* @access private
	*/
	private $filePath;

	/**
	* @var array Array of constant names used by wp-config.php for the
	* logon details
	* @access private
	*/
	private $paramA=array('DB_NAME','DB_USER','DB_PASSWORD','DB_HOST');

	/**
	* @var bool $database Can check this var to see if your database was connected successfully
	*/
	public $_database;

	/**
	* Constructor. This function pulls everything together and makes it happen.
	* This could be unraveled to make the whole thing more flexible later.
	*
	* @param string $filePath Path to wp-config.php file
	* @access private
	*/
	function __construct($type=1,$filePath='./wp-config.php'){
		$this->filePath=$filePath;
		$this->getFile();
		//$this->serverBasedCondition();
		/**
		* eval the WP contants into PHP
		*/
		foreach ($this->paramA as $p) {

			$this->evalParam('define(\''.$p.'\'','\');');
		}

		$this->createConstant('$table_prefix=\'',"';");

		switch ($type) {
			default:
			case 1:
				$this->conMySQL_Connect();
				break;
			case 2:
				$this->conPDO();
				break;
			case 3:
				$this->conMySQLi();
				break;
		}
	}

	/**
	* Make the connection using mysql_connect
	*/
	private function conMySQL_Connect(){
		try {
			if (($this->_con = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)) == false){
				throw new Exception ('Could not connect to mySQL. ' . mysql_error());
			}
		} catch (Exception $e){
			exit ('Error on line	' . $e->getLine(). ' in ' . $e->getFile() . ': ' . $e->getMessage());
		}
		try {
			if (($this->_database = mysql_select_db(DB_NAME, $this->_con)) == false){
				throw new Exception ('Could not select database. ' . mysql_error());
			}
		} catch (Exception $e){
			exit ('Error on line	' . $e->getLine(). ' in ' . $e->getFile() . ': ' . $e->getMessage());
		}
	}

	/**
	* Make the connection using mySQLi
	*/
	private function conMySQLi(){
		$this->_con = @new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		if (mysqli_connect_errno()){
			exit ('MySQLi connection failed: ' . mysqli_connect_error());
		}
	}

	/**
	* Make the connection using PDO
	*/
	private function conPDO(){
		try {
			$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
			$this->_con = @new PDO($dsn, DB_USER, DB_PASSWORD);
		} catch (PDOException $e) {
			exit ('Error on line	' . $e->getLine(). ' in ' . $e->getFile() . ': ' . $e->getMessage() );
		}
	}

	/**
	* Read the wp-config.php file into a string
	*
	* @access private
	*/
	private function getFile() {
		try {
			$this->str = @file_get_contents($this->filePath);
			
			if ($this->str == false){
				throw new Exception ('Failed to read file (' . $this->filePath . ') into string.');
			}
			$this->str = str_replace('"', "'", $this->str);
			$this->str = str_replace(' ', "", $this->str);
		} catch (Exception $e) {
			exit ('Error on line	' . $e->getLine(). ' in ' . $e->getFile() . ': ' . $e->getMessage() );
		}
	}

	/**
	* Get the logon parameter and evaluate it into PHP.
	* Eg, eval("define('DB_NAME', 'm4j3lub3_wordpress');");
	*
	* @param string $pre This defines what to look for at the start of a logon parameter
	* definition. Eg, if you are looking for	"define('DB_NAME', 'm4j3lub3_wordpress');"
	* then the $pre bit would be "define('DB_NAME'".
	*
	* @param string $post Like $pre, this defines what to look for at the end of the logon
	* parameter definition. In the case of WordPress it is always going to be "');"
	*
	* @access private
	*/
	private function evalParam($pre,$post){
		$str=$this->str;
		$str1=substr($str,strpos($str,$pre));
		$str1=substr($str1,0,strpos($str1,$post) + strlen($post));

		//echo $str;
		//echo $str1;
		//echo '<br>';
		eval($str1);
	}

	private function createConstant($pre,$post){
		$str=$this->str;
		$str = str_replace( '"', "'", $str);
		$str = str_replace( '	', " ", $str);

		$str1=substr($str,strpos($str,$pre));

		$str1=substr($str1,0,strpos($str1,$post) + strlen($post));
		$table_prefix = explode("'" , $str1);
		$table_prefix = $table_prefix[1];

		$str = "define('TABLE_PREFIX','".$table_prefix."');";

		//echo '<br>';
		eval($str);
	}

	/**
	* Grab the right code block if there are more than one set of definitions
	*
	* Sets $this->str to be the right code block
	*
	* Used for when there are conditional settings based on local or remote configuration,
	* using the condition: if ($_SERVER['HTTP_HOST']=='localhost') { ...
	*
	* @access private
	*/
	private function serverBasedCondition(){
		if(strpos($this->str, '$_SERVER["HTTP_HOST"]') || strpos($this->str, '$_SERVER[\'HTTP_HOST\']')){
			if(strpos($this->str, '$_SERVER["HTTP_HOST"]')){
				// case of double quotes - get a substring
				$this->str = substr($this->str, strpos($this->str,'$_SERVER["HTTP_HOST"]'));
			} elseif(strpos($this->str, '$_SERVER[\'HTTP_HOST\']')){
				// case of single quotes - get a substring
				$this->str = substr($this->str, strpos($this->str,'$_SERVER[\'HTTP_HOST\']'));
			}

			// substring from 1st occurance of {
			$this->str = substr($this->str, strpos($this->str,'{')+1);

			if ($_SERVER['HTTP_HOST']=='local.dev') {
				// local - substring from start to 1st occurance of } - this is now the block
				$this->str = substr($this->str, 0, strpos($this->str, '}') - 1);
			} else {
				// remote - substring from the else condition
				$this->str = substr($this->str, strpos($this->str, '{')+1);
				$this->str = substr($this->str, 0, strpos($this->str, '}') - 1);
			}
			// replace all double quote with single to make it easier to find the param definitions
			$this->str=str_replace('"','\'',$this->str);
		}
	}

	/**
	* Return an instance of the class based on type of connection passed
	*
	* $types are:
	* 1 = Procedural connection using mysql_connect()
	* 2 = OOP connection using PHP Data Objects (PDO)
	* 3 = OOP connection using mySQLi
	*
	* @return resource Database connection
	* @access private
	*/
	private static function returnInstance($type) {
		if (is_null(self::$_singleton)){
			self::$_singleton = new WP_DB_Connect($type);
		}
		return self::$_singleton;
	}

	/**
	* Action the return of the instance based on Procedural connection using mysql_connect()
	*
	* @access public
	* @return resource Procedural connection using mysql_connect()
	*/
	public static function getInstance(){
		return self::returnInstance(1);
	}

	/**
	* Action the return of the instance based on OOP connection using PDO
	*
	* @access public
	* @return resource OOP connection using PHP Data Objects (PDO)
	*/
	public static function getPDOInstance(){
		return self::returnInstance(2);
	}

	/**
	* Action the return of the instance based on OOP connection using mySQLi
	*
	* @access public
	* @return resource OOP connection using mySQLi
	*/
	public static function getMySQLiInstance(){
		return self::returnInstance(3);
	}
}

$cta_varation = new CTA_AJAX_Return_Variation;
class CTA_AJAX_Return_Variation {

	private $cta_id;
	private $db;
	private $variations;
	private $variation_marker;
	private $live_variations;
	private $debug;

	function __construct()
	{
		/* Connect to MYSQL Datababase */
		$this->connect_to_db();

		/* setup variables */
		$this->setup_variables();

		/* return correct variation */
		$this->return_variation();
	}

	function connect_to_db()
	{
		/* Connect to MYSQL Datababase */
		if ( file_exists ( './../../../../wp-config.php' ) ){
			$this->db = new WP_DB_Connect( 1 , './../../../../wp-config.php' );
		} else if ( './../../../../../../wp-config.php' ){
			$this->db = new WP_DB_Connect( 1 , './../../../../../../wp-config.php' );
		} else if ( './../../../../../../../wp-config.php' ) {
			$this->db = new WP_DB_Connect( 1 , './../../../../../../../wp-config.php' );
		} else {
			echo 'wp-config.php cannot be found.';
			echo 0; // default
			exit;
		}
	}

	function setup_variables()	{
		$this->debug = 0;

		/* Make Sure the right GET param is attached to continue */
		if ( !isset($_GET['cta_id']) || !is_numeric($_GET['cta_id']) )	{

			echo 0;
			exit;
		}
		else
		{
			$this->cta_id = $_GET['cta_id'];
		}

		$this->variations = $this->get_post_meta('wp-cta-variations');

		$this->variations_array = json_decode( $this->variations , true );
		$this->variation_marker = $this->get_post_meta('_cta_ab_variation_marker');

		if (!is_numeric($this->variation_marker)) {
			$this->variation_marker = 0;
		}

		if ($this->variations_array) {
			foreach ($this->variations_array as $vid => $variation ){				
				if (!isset($variation['status']) || $variation['status'] == 'active'  ){
					$this->live_variations[] = $vid;
				}
			}
		}
	}

	/** 
	*  Return variations
	*/
	function return_variation() {

		if (!isset($this->live_variations))	{
			echo 'x';
			exit;
		}

		if (count($this->live_variations)==1) {
			echo $this->live_variations[0];
			exit;
		}

		if ($this->debug) {
			print_r($this->live_variations);
			echo '<br>';
		}

		$keys_as_values = array_flip($this->live_variations);
		reset($keys_as_values);



		if (!isset($keys_as_values[$this->variation_marker])){
			$this->variation_marker = reset($keys_as_values);
		}

		if ($this->debug){
			echo "Marker Start:".$this->variation_marker;
			echo "<br>";
			echo "This Variation:".$keys_as_values[$this->variation_marker];
			echo "<br>";
		}

		//echo key($this->live_variations);exit;
		$i = 0;
		if (key($keys_as_values)!=$this->variation_marker)
		{
			while ((next($keys_as_values) != $this->variation_marker ))
			{
				if ($i>100)
					break;
				$i++;
			}
		}


		$key = next($keys_as_values);
		$this->variation_marker = $this->live_variations[$key];
		if ($this->debug)
		{

			echo "next marker: ".$this->live_variations[$key];
			echo "<br>";

		}

		if (!$this->variation_marker)
		{
			$this->variation_marker = reset($keys_as_values);
		}


		$this->update_post_meta( '_cta_ab_variation_marker', $this->variation_marker);
		echo $this->variation_marker;
		exit;

	}

	function get_post_meta( $meta_key )
	{
		$query = 'SELECT meta_value FROM '.TABLE_PREFIX.'postmeta where post_id = "'.$this->cta_id.'" and meta_key = "'.$meta_key.'"';
		$result = mysql_query($query);
		if (!$result) { echo $query; echo mysql_error(); exit; }

		$data = mysql_fetch_object($result);

		if (isset($data->meta_value)){
			return $data->meta_value;
		}else{
			return '';
		}
	}


	function update_post_meta( $meta_key , $meta_value )
	{
		$query = 'SELECT meta_value FROM '.TABLE_PREFIX.'postmeta where post_id = "'.$this->cta_id.'" and meta_key = "'.$meta_key.'"';
		$result = mysql_query($query);
		if (!$result) { echo $query; echo mysql_error(); exit; }

		if (mysql_num_rows($result)<1)
		{
			$query = 'INSERT INTO '.TABLE_PREFIX.'postmeta (post_id,meta_key,meta_value) VALUES ("'.$this->cta_id.'","'.$meta_key.'","'.$meta_value.'")';
			$result = mysql_query($query);
			if (!$result) {
				//echo $query; echo mysql_error();
				echo 0; // default
				exit;
			}
		}
		else
		{
			$query = 'UPDATE '.TABLE_PREFIX.'postmeta	set meta_value="'.$meta_value.'" WHERE post_id = "'.$this->cta_id.'" and meta_key = "'.$meta_key.'"';
			$result = mysql_query($query);
			if (!$result) {
				//echo $query; echo mysql_error();
				echo 0; // default
				exit;
			}
		}

	}

}
?>
