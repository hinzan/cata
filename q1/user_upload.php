<?php
error_reporting(0);
include_once 'config.php';
class import_csv
{
   public $cn;
   public $commands;
   public $csv;
   public function __construct($options){
      $this->commands = $options; 
   }

   private function mysql_init(){        
            mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL);
            try{
                $this->cn = mysqli_connect(HOST,USERNAME,PASSWORD,DATABASE);
            } catch(mysqli_sql_exception  $e){
                echo ($e->getMessage());
                exit;
            }
   }

   public function main(){
      if (array_key_exists('help', $this->commands)) {
         $this->help();
      }
      if (array_key_exists('file', $this->commands) && !array_key_exists('dry_run', $this->commands)) {
         $this->mysql_init();  
         $this->read_csv();
         $this->insert_records();
      }
      if (array_key_exists('create_table', $this->commands) && !array_key_exists('dry_run', $this->commands)) {
         $this->mysql_init(); 
         $this->create_table();
      }
      if (array_key_exists('u', $this->commands) || array_key_exists('p', $this->commands) || array_key_exists('h', $this->commands)) {
         $this->show_mysql();
      }
      if (array_key_exists('dry_run', $this->commands)) {
            $this->read_csv();
            $this->dry_run();
      }
   }

   private function read_csv(){
      if (file_exists($this->commands['file']) && isset($this->commands['file'])) {
         $this->csv = array_map('str_getcsv', file($this->commands['file']));
      } else {
         echo ($this->commands['file'] . "<- File Not Exists. \n");
         exit;
      }
   }

   public function create_table(){
            $table = "CREATE TABLE `users`(
                            `id` bigint primary key not null auto_increment,
                            `name` varchar(300),
                            `surname` varchar(300),
                            `email` varchar(300),
                            UNIQUE KEY unique_email (email));";
            try {
                $this->cn->query($table);
                echo "Table Created. \n";
            } catch (mysqli_sql_exception  $e) {
                echo ($e->getMessage()."\n");
                exit;
            }
   }

   private function insert_records(){
      unset($this->csv[0]);
      $insert_count = 0;
      foreach ($this->csv as $c) {
         if (isset($c[0])) {
            $name = ucwords($c[0]);
            $surname = ucwords($c[1]);
            $email = $c[2];

            if(!isset($this->commands['create_table'])){
                $table = "users";
            } else {
                $table = $this->commands['create_table'];
            }

            if($this->validate_email($email)){
                $sql = 'INSERT INTO `' . $table . '`  (`name`, `surname`, `email`)  
                                        VALUES ("' . $name . '", "' . $surname . '", "' . $email . '");';
                try {
                $this->cn->query($sql);
                $insert_count++;
                } catch (mysqli_sql_exception  $e) {
                    echo($e->getMessage() . "\n");
                }
            }
         }
      }
      echo "\nRecord Insert ($insert_count) \n";
   }

   public function dry_run(){
      if(sizeof($this->csv) == 0){
          exit ( "Please insert csv file. \n" );
      } 
      echo "Dry Run \n\n";     
      $csv = $this->csv;
     
      echo "Status\t" . $csv[0][0] . "\t" . $csv[0][1] . "\t" . $csv[0][2] . "\n";
      unset($csv[0]);

      $total_records = 0;
      foreach($csv as $c){
          if($c[0] != ""){
            $is_email = ($this->validate_email($c[2]))? 'true': 'false';
            echo  $is_email . "\t". ucwords($c[0]). "\t" . ucwords($c[1]) . "\t" . $c[2] . "\n";
            $total_records++;
          }
      }

      echo "CSV Total Records : " . $total_records . "\n";
      exit;
   }

   private function validate_email($email){
      if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
         return TRUE;
      } else {
         return FALSE;
      }
   }

   public function show_mysql(){
       if (array_key_exists('u', $this->commands)){
           echo "MySQL User : " . USERNAME . "\n";
       }

       if (array_key_exists('p', $this->commands)){
            echo "MySQL Password : " . PASSWORD . "\n";
       }

       if (array_key_exists('h', $this->commands)){
            echo "MySQL Host : " . HOST . "\n";
       }
       exit();
   }

   public function help(){
      echo "Help Manual\n\n" .
         "--file [csv file name] - this the name of the CSV to be parsed \n\n" .
         "--create_table - this will cause the MySQL users table to be build (and no further action will be taken)  \n\n" .
         "--dry_run - this will be used with --file directive in case we want to run the script but not insert into the DB. All other functions will be exceuted, but the databse wan't be alterd \n\n" .
         "-u - MySQL username \n\n" .
         "-p - MySQL password \n\n" .
         "-h - MySQL host \n\n" .
         "--help -wich will be output the above list of directives twith details. \n\n";
      exit;
   }
}

$short = "u::" . "p::" . "h::";
$detail = array('file:', 'create_table::', 'dry_run::', 'help::');

$options = getopt($short, $detail);

$cs = new import_csv($options);
$cs->main();

//php user_upload.php --help
//php user_upload.php --dry_run --file users.csv
//php user_upload.php --create_table
//php user_upload.php --file users.csv
//php user_upload.php -u
//php user_upload.php -p
//php user_upload.php -h


?>