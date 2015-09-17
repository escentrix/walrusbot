<?php
//TURN OFF STDIN (/dev/null)
//SEND STDOUT TO FILE (output.log)
//SEND STDERR TO FILE (error.log)
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen('output.log', 'wb');
$STDERR = fopen('error.log', 'wb');

//TURN OFF SCRIPT EXECUTION TIME LIMIT (SCRIPT RUNS FOREVER)
set_time_limit(0);
//OUTPUT SCRIPT ERRORS (DEBUG)
ini_set('display_errors', 'on');

require('include/config.php'); //IRC CONFIG
require('include/functions.php'); //USER FUNCTIONS
require('include/walrusbot.class'); //IRC BOT CLASS

$bot=new WalrusBot($_CONFIG);
$irc_channels=array();

//CONNECT TO CHANNEL
$bot->join_channel($_CONFIG['server']['channel']);

//$bot->query_channels();
while(1){
   usleep(10); //Idle
   $irc_rx=$bot->get_data();
   if($irc_rx!=NULL){
      $irc_rx=str_replace(array(chr(10), chr(13)), '', $irc_rx);//REPLACE NEWLINES AND CARRIAGE RETURNS WITH NOTHING
      $irc_rx_array=explode(' ', $irc_rx);//BREAKOUT IRC DATA WITH INTO ARRAYS FOR EASY 
      switch(get_index_value(0, $irc_rx_array, null)){
         case 'PING'://Play PING-PONG with server
            $bot->send_data('PONG', get_index_value(1, $irc_rx_array));
            break;
         default://General IRC server functions
            switch(get_index_value(1, $irc_rx_array, null)){
               case '322'://Server /LIST queries for indexing channels
                  //if(substr(get_index_value(3, $irc_rx_array, ""), 0, 1)=="#"){
                     //if(!in_array($irc_rx_array[3], $channels)){
                        //$channels[].=$irc_rx_array[3];
                        //$bot->join_channel($irc_rx_array[3]); //JOIN ALL CHANNELS FROM /LIST !DANGEROUS!
                        //$bot->send_data('NAMES');
                     //}
                  //}
                  break;
               case '332'://TOPIC
                  break;
               case '353'://Server /NAMES queries for indexing users
                  break;
               case 'JOIN'://Users joining channels
                  if(trim(strstr(get_index_value(0, $irc_rx_array, ""), "!", true), ":")!=$_CONFIG['bot']['nick']){//IF NICK JOINING IN IS NOT THIS BOT...
                     //$bot->send_data('PRIVMSG', trim($irc_rx_array[2], ":")." :Hello, ".trim(strstr($irc_rx_array[0], "!", true), ":")."!"); //SEND HELLO MESSAGE TO NICK
                  }
                  break;
               case 'PART'://Users leaving channels
                  break;
               case 'MODE'://Users changing modes
                  break;
               case 'TOPIC'://Channel topic changes
                  break;
               case 'KICK';//Users getting kicked
                  break;
               case 'BAN'://Users getting banned
                  break;
               case 'QUIT'://Users quitting
                  break;
               case 'NOTICE'://Channel notices
               case 'PRIVMSG'://Channel messages
                  switch(get_index_value(2, $irc_rx_array, null)){//FIND WHERE WE RECIEVED A MESSAGE FROM
                     case $_CONFIG['bot']['nick']://RECIEVED A DIRECT PRIVATE MESSAGE
                        if(strpos(strtolower($irc_rx), strtolower("hello ".$_CONFIG['bot']['nick']))!==false){//IF WE RECIEVED A HELLO MESSAGE
                           $bot->send_data('PRIVMSG', trim(strstr($irc_rx_array[0], "!", true), ":")." :Hello, ".trim(strstr($irc_rx_array[0], "!", true), ":")."!"); //SEND MESSAGE TO USER
                        }
                        break;
                     default://OTHERWISE WE RECIVED A CHANNEL MESSAGE
                        if(strpos(strtolower($irc_rx), strtolower("hello ".$_CONFIG['bot']['nick']))!==false){//IF WE RECIEVED A HELLO MESSAGE
                           $bot->send_data('PRIVMSG', $irc_rx_array[2]." :Hello, ".trim(strstr($irc_rx_array[0], "!", true), ":")."!"); //SEND MESSAGE TO CHANNEL
                        }
                        break;
                  }
                  break;
               default: //Everything else
            }
      }
      unset($irc_rx_array);
   }
   unset($irc_rx);
}
?>
