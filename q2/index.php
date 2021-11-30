<?php
for($x=1; $x<=100; $x++){
    if( ( ($x % 3)==0 ) && ($x % 5)==0){
        echo "foobar, ";
    } else if (($x % 3)==0){
        echo "foo, ";
    } else if (($x % 5)==0){
        echo "bar,";
    } else {
        echo $x . ", ";
    }
}
?>